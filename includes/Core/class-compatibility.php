<?php
/**
 * Gerenciador de Compatibilidade do SiteShield
 *
 * @package Mauriciokaleya by SiteShield
 * @author Mauriciokaleya
 * @subpackage Core
 */

namespace SiteShield\Core;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Classe Compatibility
 */
class Compatibility {

	/**
	 * Verifica se o request atual é de um construtor de páginas legítimo (Elementor, Beaver, Divi)
	 *
	 * @return bool
	 */
	public static function is_page_builder_request() {
		// Elementor Editor
		if ( isset( $_GET['elementor-preview'] ) || isset( $_GET['elementor_library'] ) || ( isset( $_POST['action'] ) && false !== strpos( sanitize_text_field( wp_unslash( $_POST['action'] ) ), 'elementor' ) ) ) {
			return true;
		}

		// Gutenberg Block Editor
		if ( ( defined( 'REST_REQUEST' ) && REST_REQUEST ) && isset( $_GET['context'] ) && 'edit' === $_GET['context'] ) {
			return true;
		}

		// Customizer do WordPress
		if ( function_exists( 'is_customize_preview' ) && is_customize_preview() ) {
			return true;
		}

		return false;
	}

	/**
	 * Verifica se a requisição atual é de checkout / webhook seguro do WooCommerce
	 *
	 * @return bool
	 */
	public static function is_woocommerce_safe_endpoint() {
		if ( isset( $_GET['wc-ajax'] ) || isset( $_GET['wc-api'] ) || isset( $_GET['woocommerce_checkout'] ) ) {
			return true;
		}
		return false;
	}

	/**
	 * Identifica se a requisição veio de um IP confiável da Cloudflare
	 *
	 * @return bool
	 */
	public static function is_cloudflare() {
		return isset( $_SERVER['HTTP_CF_CONNECTING_IP'] ) || isset( $_SERVER['HTTP_CF_RAY'] );
	}

	/**
	 * Obtém o IP real do visitante mesmo atrás de proxies, Cloudflare ou balanceadores
	 *
	 * @return string
	 */
	public static function get_client_ip() {
		$headers = array(
			'HTTP_CF_CONNECTING_IP', // Cloudflare
			'HTTP_X_REAL_IP',        // Nginx proxy
			'HTTP_X_FORWARDED_FOR',  // Standard proxy
			'REMOTE_ADDR',           // Padrão do servidor
		);

		foreach ( $headers as $header ) {
			if ( ! empty( $_SERVER[ $header ] ) ) {
				$ip_list = explode( ',', sanitize_text_field( wp_unslash( $_SERVER[ $header ] ) ) );
				$ip      = trim( $ip_list[0] );
				if ( filter_var( $ip, FILTER_VALIDATE_IP ) ) {
					return $ip;
				}
			}
		}

		return isset( $_SERVER['REMOTE_ADDR'] ) ? sanitize_text_field( wp_unslash( $_SERVER['REMOTE_ADDR'] ) ) : '0.0.0.0';
	}
}
