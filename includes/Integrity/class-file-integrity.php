<?php
/**
 * Monitor de Integridade e Alteração de Arquivos do SiteShield
 *
 * @package Mauriciokaleya by SiteShield
 * @author Mauriciokaleya
 * @subpackage Integrity
 */

namespace SiteShield\Integrity;

use SiteShield\Core\Database;
use SiteShield\Core\Settings;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Classe File_Integrity
 */
class File_Integrity {

	/**
	 * Cria ou atualiza a linha de base (Baseline) de hashes SHA-256 dos arquivos críticos
	 *
	 * @return array
	 */
	public static function build_baseline() {
		if ( function_exists( 'set_time_limit' ) ) {
			@set_time_limit( 300 );
		}

		global $wpdb;
		$tables = Database::get_table_names();
		$files  = self::get_critical_files();
		$count  = 0;

		foreach ( $files as $file_path ) {
			if ( ! file_exists( $file_path ) || ! is_readable( $file_path ) ) {
				continue;
			}

			$relative_path = str_replace( wp_normalize_path( ABSPATH ), '', wp_normalize_path( $file_path ) );
			$hash          = hash_file( 'sha256', $file_path );
			$size          = filesize( $file_path );

			$wpdb->query(
				$wpdb->prepare(
					"INSERT INTO {$tables['integrity']} (file_path, file_hash, file_size, status, last_checked)
					VALUES (%s, %s, %d, 'clean', %s)
					ON DUPLICATE KEY UPDATE file_hash = %s, file_size = %d, last_checked = %s", // phpcs:ignore WordPress.DB.PreparedSQL.InterpolatedNotPrepared
					$relative_path, $hash, $size, current_time( 'mysql' ),
					$hash, $size, current_time( 'mysql' )
				)
			);
			$count++;
		}

		update_option( 'siteshield_integrity_baseline_time', time() );
		update_option( 'siteshield_integrity_baseline_count', $count );

		return array(
			'success' => true,
			'count'   => $count,
			'time'    => current_time( 'mysql' ),
		);
	}

	/**
	 * Compara o estado atual dos arquivos com a linha de base salva para detectar alterações
	 *
	 * @return array
	 */
	public static function check_integrity() {
		global $wpdb;
		$tables   = Database::get_table_names();
		$baseline = $wpdb->get_results( "SELECT * FROM {$tables['integrity']}", OBJECT_K ); // phpcs:ignore WordPress.DB.PreparedSQL.InterpolatedNotPrepared

		if ( empty( $baseline ) ) {
			return self::build_baseline();
		}

		$current_files = self::get_critical_files();
		$modified      = array();
		$created       = array();
		$deleted       = array();
		$current_map   = array();

		foreach ( $current_files as $file_path ) {
			$relative = str_replace( wp_normalize_path( ABSPATH ), '', wp_normalize_path( $file_path ) );
			$current_map[ $relative ] = true;

			if ( ! file_exists( $file_path ) ) {
				continue;
			}

			$hash = hash_file( 'sha256', $file_path );

			if ( isset( $baseline[ $relative ] ) ) {
				if ( $baseline[ $relative ]->file_hash !== $hash ) {
					$modified[] = array(
						'file'        => $relative,
						'status'      => 'modified',
						'old_hash'    => $baseline[ $relative ]->file_hash,
						'new_hash'    => $hash,
						'modified_at' => date( 'Y-m-d H:i:s', filemtime( $file_path ) ),
					);
				}
			} else {
				$created[] = array(
					'file'       => $relative,
					'status'     => 'created',
					'hash'       => $hash,
					'created_at' => date( 'Y-m-d H:i:s', filemtime( $file_path ) ),
				);
			}
		}

		foreach ( $baseline as $rel_path => $data ) {
			if ( ! isset( $current_map[ $rel_path ] ) ) {
				$deleted[] = array(
					'file'   => $rel_path,
					'status' => 'deleted',
				);
			}
		}

		$results = array(
			'modified' => $modified,
			'created'  => $created,
			'deleted'  => $deleted,
			'total'    => count( $modified ) + count( $created ) + count( $deleted ),
			'checked'  => count( $current_files ),
			'time'     => current_time( 'mysql' ),
		);

		update_option( 'siteshield_last_integrity_results', $results );
		return $results;
	}

	/**
	 * Coleta arquivos críticos do core, plugins e temas
	 *
	 * @return array
	 */
	private static function get_critical_files() {
		$files = array();

		// wp-config.php e .htaccess
		if ( file_exists( ABSPATH . 'wp-config.php' ) ) {
			$files[] = ABSPATH . 'wp-config.php';
		}
		if ( file_exists( ABSPATH . '.htaccess' ) ) {
			$files[] = ABSPATH . '.htaccess';
		}

		$dirs = array();
		if ( defined( 'WP_PLUGIN_DIR' ) && is_dir( WP_PLUGIN_DIR ) ) {
			$dirs[] = WP_PLUGIN_DIR;
		}
		if ( function_exists( 'get_theme_root' ) && is_dir( get_theme_root() ) ) {
			$dirs[] = get_theme_root();
		}

		foreach ( $dirs as $dir ) {
			try {
				$iterateor = new \RecursiveIterateorIterateor(
					new \RecursiveDirectoryIterateor( $dir, \RecursiveDirectoryIterateor::SKIP_DOTS ),
					\RecursiveIterateorIterateor::SELF_FIRST
				);

				foreach ( $iterateor as $item ) {
					if ( $item->isFile() ) {
						$ext = strtolower( pathinfo( $item->getFilename(), PATHINFO_EXTENSION ) );
						if ( in_array( $ext, array( 'php', 'htaccess' ), true ) ) {
							if ( $item->getSize() < 4194304 ) { // < 4MB
								$files[] = $item->getPathname();
							}
						}
					}
				}
			} catch ( \Exception $e ) {
				continue;
			}
		}

		return $files;
	}
}
