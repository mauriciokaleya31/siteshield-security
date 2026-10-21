<?php
/**
 * Gerenciador de Quarentena Segura e Restauração de Arquivos do SiteShield
 *
 * @package Mauriciokaleya by SiteShield
 * @author Mauriciokaleya
 * @subpackage Scanner
 */

namespace SiteShield\Scanner;

use SiteShield\Core\Database;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Classe Quarantine
 */
class Quarantine {

	/**
	 * Retorna o diretório seguro de quarentena com .htaccess e index.php de proteção
	 *
	 * @return string
	 */
	public static function get_quarantine_dir() {
		$upload_dir = wp_upload_dir();
		$dir        = $upload_dir['basedir'] . '/siteshield-quarantine';

		if ( ! is_dir( $dir ) ) {
			wp_mkdir_p( $dir );
			// Bloqueia qualquer execução direta de script no diretório de quarentena
			@file_put_contents( $dir . '/.htaccess', "Deny from all\n<Files *>\nDeny from all\n</Files>\n" );
			@file_put_contents( $dir . '/index.php', "<?php // Silêncio é ouro.\nexit;" );
		}

		return $dir;
	}

	/**
	 * Move um arquivo suspeito para a quarentena criando backup de segurança (.bak)
	 *
	 * @param string $relative_path Caminho relativo do arquivo.
	 * @param string $reason Motivo da quarentena.
	 * @return bool|\WP_Error
	 */
	public static function quarantine_file( $relative_path, $reason = 'Ameaça identificada pelo scanner' ) {
		$full_path = ABSPATH . ltrim( $relative_path, '/\\' );

		if ( ! file_exists( $full_path ) || ! is_readable( $full_path ) ) {
			return new \WP_Error( 'file_not_found', __( 'Arquivo não encontrado ou sem permissão de leitura.', 'siteshield' ) );
		}

		$quarantine_dir = self::get_quarantine_dir();
		$file_hash      = hash_file( 'sha256', $full_path );
		$safe_filename  = 'quarantine_' . time() . '_' . md5( $relative_path ) . '.quarantine';
		$destination    = $quarantine_dir . '/' . $safe_filename;

		// 1. Cria cópia de backup no local seguro
		if ( ! @copy( $full_path, $destination ) ) {
			return new \WP_Error( 'copy_failed', __( 'Falha ao copiar o arquivo para a quarentena.', 'siteshield' ) );
		}

		// 2. Remove ou esvazia o arquivo malicioso original
		@unlink( $full_path );

		// 3. Registra na tabela de quarentena
		global $wpdb;
		$tables  = Database::get_table_names();
		$user_id = function_exists( 'get_current_user_id' ) ? get_current_user_id() : 0;

		if ( $wpdb->get_var( "SHOW TABLES LIKE '{$tables['quarantine']}'" ) === $tables['quarantine'] ) {
			$wpdb->insert(
				$tables['quarantine'],
				array(
					'original_path'   => $relative_path,
					'quarantine_path' => $safe_filename,
					'file_hash'       => $file_hash,
					'reason'          => $reason,
					'user_id'         => $user_id,
					'created_at'      => current_time( 'mysql' ),
				)
			);
		}

		// 4. Atualiza o status na tabela de achados do scanner
		if ( $wpdb->get_var( "SHOW TABLES LIKE '{$tables['scan_findings']}'" ) === $tables['scan_findings'] ) {
			$wpdb->update(
				$tables['scan_findings'],
				array( 'status' => 'quarantined' ),
				array( 'file_path' => $relative_path )
			);
		}

		return true;
	}

	/**
	 * Restaura um arquivo da quarentena para o seu caminho original
	 *
	 * @param int $quarantine_id ID do registro na quarentena.
	 * @return bool|\WP_Error
	 */
	public static function restore_file( $quarantine_id ) {
		global $wpdb;
		$tables = Database::get_table_names();

		$record = $wpdb->get_row(
			$wpdb->prepare( "SELECT * FROM {$tables['quarantine']} WHERE id = %d LIMIT 1", $quarantine_id ) // phpcs:ignore WordPress.DB.PreparedSQL.InterpolatedNotPrepared
		);

		if ( ! $record ) {
			return new \WP_Error( 'not_found', __( 'Registro de quarentena não encontrado.', 'siteshield' ) );
		}

		$quarantine_dir = self::get_quarantine_dir();
		$stored_file    = $quarantine_dir . '/' . $record->quarantine_path;
		$target_file    = ABSPATH . ltrim( $record->original_path, '/\\' );

		if ( ! file_exists( $stored_file ) ) {
			return new \WP_Error( 'backup_missing', __( 'Arquivo de backup em quarentena não encontrado.', 'siteshield' ) );
		}

		// Cria diretórios pais se necessário
		$target_parent = dirname( $target_file );
		if ( ! is_dir( $target_parent ) ) {
			wp_mkdir_p( $target_parent );
		}

		// Copia de volta
		if ( ! @copy( $stored_file, $target_file ) ) {
			return new \WP_Error( 'restore_failed', __( 'Falha ao restaurar o arquivo.', 'siteshield' ) );
		}

		// Atualiza banco
		$wpdb->update(
			$tables['quarantine'],
			array( 'restored_at' => current_time( 'mysql' ) ),
			array( 'id' => $quarantine_id )
		);

		$wpdb->update(
			$tables['scan_findings'],
			array( 'status' => 'restored' ),
			array( 'file_path' => $record->original_path )
		);

		return true;
	}

	/**
	 * Exclui permanentemente um arquivo da quarentena
	 *
	 * @param int $quarantine_id ID do registro.
	 * @return bool
	 */
	public static function delete_permanently( $quarantine_id ) {
		global $wpdb;
		$tables = Database::get_table_names();

		$record = $wpdb->get_row(
			$wpdb->prepare( "SELECT * FROM {$tables['quarantine']} WHERE id = %d LIMIT 1", $quarantine_id ) // phpcs:ignore WordPress.DB.PreparedSQL.InterpolatedNotPrepared
		);

		if ( $record ) {
			$quarantine_dir = self::get_quarantine_dir();
			$stored_file    = $quarantine_dir . '/' . $record->quarantine_path;
			if ( file_exists( $stored_file ) ) {
				@unlink( $stored_file );
			}
			$wpdb->delete( $tables['quarantine'], array( 'id' => $quarantine_id ) );
		}

		return true;
	}

	/**
	 * Retorna todos os arquivos atualmente em quarentena
	 *
	 * @return array
	 */
	public static function get_all() {
		global $wpdb;
		$tables = Database::get_table_names();

		if ( $wpdb->get_var( "SHOW TABLES LIKE '{$tables['quarantine']}'" ) !== $tables['quarantine'] ) {
			return array();
		}

		return $wpdb->get_results( "SELECT * FROM {$tables['quarantine']} ORDER BY id DESC" ); // phpcs:ignore WordPress.DB.PreparedSQL.InterpolatedNotPrepared
	}
}
