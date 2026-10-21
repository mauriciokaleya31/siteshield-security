<?php
/**
 * Motor Global de Proteção de Conteúdo, Anti-Clone, Anti-Inspeção e Anti-Hotlink do SiteShield
 * Aplicação universal e automática em 100% do frontend do WordPress.
 *
 * @package Mauriciokaleya by SiteShield
 * @author Mauriciokaleya
 * @subpackage AntiClone
 */

namespace SiteShield\AntiClone;

use SiteShield\Core\Settings;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Classe Content_Protection
 */
class Content_Protection {

	/**
	 * Inicializa os ganchos globais de proteção frontend
	 */
	public static function init() {
		// Registra os ganchos globais do ciclo de vida do WordPress
		add_action( 'wp_enqueue_scripts', array( __CLASS__, 'enqueue_protection_script' ), 1 );
		add_action( 'wp_head', array( __CLASS__, 'inject_protection_css' ), 9999 );
		add_action( 'wp_footer', array( __CLASS__, 'inject_protection_inline_fallback' ), 9999 );
		add_action( 'init', array( __CLASS__, 'handle_anti_hotlink' ) );
	}

	/**
	 * Determina com precisão se a proteção deve estar ativa para a requisição atual
	 * Administradores autenticados e editores de páginas são isentos por padrão.
	 *
	 * @return bool
	 */
	public static function is_protection_active_for_current_request() {
		// 1. Verifica se a proteção global Anti-Clone está ativada no SiteShield
		if ( ! Settings::get( 'content_protection_enabled', true ) ) {
			return false;
		}

		// 2. Nunca bloqueia o painel administrativo /wp-admin/
		if ( is_admin() ) {
			return false;
		}

		// 3. Isenção para Administradores e Editores Logados
		if ( function_exists( 'is_user_logged_in' ) && is_user_logged_in() ) {
			if ( function_exists( 'current_user_can' ) ) {
				if ( current_user_can( 'manage_options' ) || current_user_can( 'edit_posts' ) || current_user_can( 'edit_pages' ) || current_user_can( 'administrator' ) ) {
					return false; // Permite trabalho normal do administrador
				}
			}
		}

		// 4. Modo de Pré-visualização e Edição de Page Builders (Elementor, Customizer, Divi, Beaver, Gutenberg)
		if ( function_exists( 'is_customize_preview' ) && is_customize_preview() ) {
			return false;
		}
		if ( isset( $_GET['elementor-preview'] ) || isset( $_GET['preview'] ) || isset( $_GET['et_fb'] ) || isset( $_GET['fl_builder'] ) ) {
			return false;
		}
		if ( class_exists( '\Elementor\Plugin' ) && isset( \Elementor\Plugin::$instance->editor ) && \Elementor\Plugin::$instance->editor->is_edit_mode() ) {
			return false;
		}
		if ( defined( 'REST_REQUEST' ) && REST_REQUEST ) {
			return false;
		}

		// Ativa a proteção global para todos os visitantes em todas as páginas públicas
		return true;
	}

	/**
	 * Enfileira o script JavaScript de proteção global no frontend
	 */
	public static function enqueue_protection_script() {
		if ( ! self::is_protection_active_for_current_request() ) {
			return;
		}

		wp_enqueue_script(
			'siteshield-protection',
			SITESHIELD_URL . 'assets/js/protection.js',
			array(),
			SITESHIELD_VERSION,
			true
		);

		wp_localize_script(
			'siteshield-protection',
			'SiteShieldAntiCloneConfig',
			array(
				'enabled'            => true,
				'blockRightClick'    => (bool) Settings::get( 'protect_disable_right_click', true ),
				'blockDevTools'      => (bool) Settings::get( 'protect_disable_devtools', true ),
				'blockCopySelect'    => (bool) Settings::get( 'protect_disable_copy_select', true ),
				'blockDragImages'    => (bool) Settings::get( 'protect_disable_drag_images', true ),
				'blockPrint'         => (bool) Settings::get( 'protect_disable_print', false ),
				'enableFrameBusting' => (bool) Settings::get( 'protect_frame_busting', true ),
			)
		);
	}

	/**
	 * Injeta regras CSS universais no cabeçalho <head> com suporte a todos os navegadores
	 */
	public static function inject_protection_css() {
		if ( ! self::is_protection_active_for_current_request() ) {
			return;
		}

		$block_copy  = Settings::get( 'protect_disable_copy_select', true );
		$block_drag  = Settings::get( 'protect_disable_drag_images', true );
		$block_print = Settings::get( 'protect_disable_print', false );

		echo "\n<!-- SiteShield Security — Global Anti-Clone & Content Guard -->\n";
		echo "<style id=\"siteshield-anti-clone-guard\">\n";

		if ( $block_copy ) {
			echo "*:not(input):not(textarea):not(select):not(option):not([contenteditable=\"true\"]) {\n";
			echo "    -webkit-user-select: none !important;\n";
			echo "    -moz-user-select: none !important;\n";
			echo "    -ms-user-select: none !important;\n";
			echo "    user-select: none !important;\n";
			echo "    -webkit-touch-callout: none !important;\n";
			echo "}\n";
			echo "input, textarea, select, [contenteditable=\"true\"] {\n";
			echo "    -webkit-user-select: text !important;\n";
			echo "    -moz-user-select: text !important;\n";
			echo "    -ms-user-select: text !important;\n";
			echo "    user-select: text !important;\n";
			echo "}\n";
		}

		if ( $block_drag ) {
			echo "img, video, canvas, svg, picture {\n";
			echo "    -webkit-user-drag: none !important;\n";
			echo "    -khtml-user-drag: none !important;\n";
			echo "    -moz-user-drag: none !important;\n";
			echo "    user-drag: none !important;\n";
			echo "    pointer-events: auto;\n";
			echo "}\n";
		}

		if ( $block_print ) {
			echo "@media print {\n";
			echo "    html, body {\n";
			echo "        display: none !important;\n";
			echo "        visibility: hidden !important;\n";
			echo "    }\n";
			echo "}\n";
		}

		echo "</style>\n";
	}

	/**
	 * Injeta fallback inline no rodapé <footer> caso algum tema ou página omita wp_enqueue_scripts
	 */
	public static function inject_protection_inline_fallback() {
		if ( ! self::is_protection_active_for_current_request() ) {
			return;
		}

		// Se o script principal já foi impresso normalmente pelo WordPress, não duplica
		if ( wp_script_is( 'siteshield-protection', 'done' ) ) {
			return;
		}

		$config = array(
			'enabled'            => true,
			'blockRightClick'    => (bool) Settings::get( 'protect_disable_right_click', true ),
			'blockDevTools'      => (bool) Settings::get( 'protect_disable_devtools', true ),
			'blockCopySelect'    => (bool) Settings::get( 'protect_disable_copy_select', true ),
			'blockDragImages'    => (bool) Settings::get( 'protect_disable_drag_images', true ),
			'blockPrint'         => (bool) Settings::get( 'protect_disable_print', false ),
			'enableFrameBusting' => (bool) Settings::get( 'protect_frame_busting', true ),
		);

		$json_config = wp_json_encode( $config );
		echo "\n<script id=\"siteshield-anti-clone-inline-guard\">\n";
		echo "window.SiteShieldAntiCloneConfig = " . $json_config . ";\n";
		include SITESHIELD_PATH . 'assets/js/protection.js';
		echo "\n</script>\n";
	}

	/**
	 * Proteção Anti-Hotlink para mídias
	 */
	public static function handle_anti_hotlink() {
		if ( ! Settings::get( 'protect_anti_hotlink', false ) ) {
			return;
		}

		$referer = isset( $_SERVER['HTTP_REFERER'] ) ? sanitize_text_field( wp_unslash( $_SERVER['HTTP_REFERER'] ) ) : '';
		if ( empty( $referer ) ) {
			return;
		}

		$site_host = wp_parse_url( home_url(), PHP_URL_HOST );
		$ref_host  = wp_parse_url( $referer, PHP_URL_HOST );

		if ( $site_host && $ref_host && 0 !== strcasecmp( $site_host, $ref_host ) ) {
			$allowed_raw = Settings::get( 'protect_hotlink_allowed_domains', '' );
			$allowed     = array_filter( array_map( 'trim', explode( "\n", $allowed_raw ) ) );

			$is_allowed = false;
			foreach ( $allowed as $domain ) {
				if ( ! empty( $domain ) && false !== stripos( $ref_host, $domain ) ) {
					$is_allowed = true;
					break;
				}
			}

			if ( ! $is_allowed ) {
				$request_uri = isset( $_SERVER['REQUEST_URI'] ) ? sanitize_text_field( wp_unslash( $_SERVER['REQUEST_URI'] ) ) : '';
				if ( preg_match( '/\.(jpg|jpeg|png|gif|webp|svg|bmp|ico)$/i', $request_uri ) ) {
					status_header( 403 );
					nocache_headers();
					exit( 'Hotlinking disabled by SiteShield Security.' );
				}
			}
		}
	}
}
