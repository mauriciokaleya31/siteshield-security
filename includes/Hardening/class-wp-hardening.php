<?php
/**
 * Motor de Hardening e Blindagem do WordPress
 *
 * @package Mauriciokaleya by SiteShield
 * @author Mauriciokaleya
 * @subpackage Hardening
 */

namespace SiteShield\Hardening;

use SiteShield\Core\Settings;
use SiteShield\Events\Event_Center;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Classe WP_Hardening
 */
class WP_Hardening {

	/**
	 * Inicializa os ganchos de hardening
	 */
	public static function init() {
		// Oculta a versão do WordPress do cabeçalho HTML e feeds
		add_filter( 'the_generator', '__return_empty_string' );

		// Remove links desnecessários do header
		remove_action( 'wp_head', 'rsd_link' );
		remove_action( 'wp_head', 'wlwmanifest_link' );
		remove_action( 'wp_head', 'wp_shortlink_wp_head' );

		// Bloqueia pingbacks do XML-RPC para evitar ataques DDoS
		add_filter( 'xmlrpc_methods', array( __CLASS__, 'filter_xmlrpc_methods' ) );
	}

	/**
	 * Remove métodos perigosos de pingback do XML-RPC
	 *
	 * @param array $methods Métodos XML-RPC disponíveis.
	 * @return array
	 */
	public static function filter_xmlrpc_methods( $methods ) {
		unset( $methods['pingback.ping'] );
		unset( $methods['pingback.extensions.getPingbacks'] );
		return $methods;
	}

	/**
	 * Retorna a lista de itens de hardening e seu estado atual
	 *
	 * @return array
	 */
	public static function get_hardening_checklist() {
		$checks = array();

		// 1. Edição de Ficheiros no Painel
		$file_edit_disabled = defined( 'DISALLOW_FILE_EDIT' ) && DISALLOW_FILE_EDIT;
		$checks['file_edit'] = array(
			'title'       => __( 'Desativação do Editor de Ficheiros no Painel', 'siteshield' ),
			'description' => __( 'Impede a edição de código PHP de plugins e temas diretamente pelo wp-admin.', 'siteshield' ),
			'risk'        => __( 'Alto se um administrador for comprometido.', 'siteshield' ),
			'status'      => $file_edit_disabled ? 'PASS' : 'WARNING',
			'applied'     => $file_edit_disabled,
			'recommend'   => "Adicione define('DISALLOW_FILE_EDIT', true); no seu wp-config.php",
		);

		// 2. Ocultação da Versão do WordPress
		$checks['hide_version'] = array(
			'title'       => __( 'Ocultação da Tag Geradora (wp_generator)', 'siteshield' ),
			'description' => __( 'Oculta a versão exata do WordPress no código-fonte das páginas.', 'siteshield' ),
			'risk'        => __( 'Baixo / Reconhecimento de vulnerabilidades conhecidas.', 'siteshield' ),
			'status'      => 'PASS',
			'applied'     => true,
		);

		// 3. Bloqueio de Execução de PHP na pasta Uploads
		$upload_info      = function_exists( 'wp_upload_dir' ) ? wp_upload_dir() : array();
		$uploads_dir      = isset( $upload_info['basedir'] ) ? $upload_info['basedir'] : '';
		$htaccess_uploads = $uploads_dir ? $uploads_dir . '/.htaccess' : '';
		$uploads_protected= ! empty( $htaccess_uploads ) && file_exists( $htaccess_uploads );
		$checks['uploads_execution'] = array(
			'title'       => __( 'Bloqueio de Execução de Scripts na Pasta Uploads', 'siteshield' ),
			'description' => __( 'Garante que ficheiros .php enviados para wp-content/uploads não possam ser executados diretamente.', 'siteshield' ),
			'risk'        => __( 'Crítico / Upload de Web Shells.', 'siteshield' ),
			'status'      => $uploads_protected ? 'PASS' : 'WARNING',
			'applied'     => $uploads_protected,
		);

		// 4. Proteção de Pingbacks XML-RPC
		$checks['xmlrpc_pingback'] = array(
			'title'       => __( 'Desativação de Pingbacks XML-RPC', 'siteshield' ),
			'description' => __( 'Bloqueia o vetor de amplificação de ataques DDoS via pingback.ping.', 'siteshield' ),
			'risk'        => __( 'Médio / Sobrecarga de servidor.', 'siteshield' ),
			'status'      => 'PASS',
			'applied'     => true,
		);

		return $checks;
	}

	/**
	 * Cria a proteção .htaccess na pasta de uploads
	 *
	 * @return bool
	 */
	public static function protect_uploads_directory() {
		$upload_dir = function_exists( 'wp_upload_dir' ) ? wp_upload_dir() : array();
		if ( empty( $upload_dir['basedir'] ) || ! is_dir( $upload_dir['basedir'] ) ) {
			return false;
		}

		$path   = $upload_dir['basedir'] . '/.htaccess';
		$rule   = "# SiteShield - Bloqueio de Execução de PHP em Uploads\n<Files *.php>\nDeny from all\n</Files>\n";
		$result = (bool) @file_put_contents( $path, $rule );

		if ( $result ) {
			Event_Center::record_event( 'hardening_uploads_protected', Event_Center::SEV_INFO, 'Proteção .htaccess criada em wp-content/uploads' );
		}
		return $result;
	}
}
