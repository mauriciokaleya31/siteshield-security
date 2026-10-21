<?php
/**
 * Gestor de Exceções e Regras Personalizadas do WAF (Firewall Allowlist / Bypass)
 *
 * @package Mauriciokaleya by SiteShield
 * @author Mauriciokaleya
 * @subpackage Firewall
 */

namespace SiteShield\Firewall;

use SiteShield\Core\Settings;
use SiteShield\Core\Compatibility;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Classe WAF_Exceptions
 */
class WAF_Exceptions {

	/**
	 * Verifica se a requisição atual deve ignorar a inspeção do WAF com base em exceções
	 *
	 * @return bool True se a requisição for isenta.
	 */
	public static function is_request_exempt() {
		$client_ip = Compatibility::get_client_ip();

		// 1. Exceção por IP na Lista Branca
		if ( IP_Manager::is_ip_allowed( $client_ip ) ) {
			return true;
		}

		$request_uri = isset( $_SERVER['REQUEST_URI'] ) ? sanitize_text_field( wp_unslash( $_SERVER['REQUEST_URI'] ) ) : '';
		$user_agent  = isset( $_SERVER['HTTP_USER_AGENT'] ) ? sanitize_text_field( wp_unslash( $_SERVER['HTTP_USER_AGENT'] ) ) : '';

		// 2. Exceções de URLs Padrão Seguras (Webhooks do WooCommerce, REST API de pagamentos, callbacks WP-Cron)
		$default_whitelisted_routes = array(
			'/wp-json/wc/',
			'/wc-api/',
			'/wp-cron.php',
		);

		foreach ( $default_whitelisted_routes as $route ) {
			if ( false !== strpos( $request_uri, $route ) ) {
				return true;
			}
		}

		// 3. Exceções configuradas manualmente pelo usuário
		$custom_exceptions = Settings::get( 'waf_custom_exceptions', array() );
		if ( is_array( $custom_exceptions ) ) {
			foreach ( $custom_exceptions as $exc ) {
				if ( ! empty( $exc['url'] ) && false !== strpos( $request_uri, $exc['url'] ) ) {
					return true;
				}
				if ( ! empty( $exc['ip'] ) && $client_ip === $exc['ip'] ) {
					return true;
				}
			}
		}

		return false;
	}

	/**
	 * Adiciona uma nova exceção manual ação WAF
	 *
	 * @param string $type 'url', 'ip', 'param'.
	 * @param string $value Valor correspondente.
	 * @param string $reason Motivo da exceção.
	 * @return bool
	 */
	public static function add_exception( $type, $value, $reason = '' ) {
		$exceptions = Settings::get( 'waf_custom_exceptions', array() );
		if ( ! is_array( $exceptions ) ) {
			$exceptions = array();
		}

		$exceptions[] = array(
			'id'         => uniqid( 'exc_' ),
			'type'       => sanitize_key( $type ),
			'value'      => sanitize_text_field( $value ),
			'reason'     => sanitize_text_field( $reason ),
			'created_at' => current_time( 'mysql' ),
		);

		return Settings::update( array( 'waf_custom_exceptions' => $exceptions ) );
	}

	/**
	 * Remove uma exceção existente
	 *
	 * @param string $exception_id ID único da exceção.
	 * @return bool
	 */
	public static function remove_exception( $exception_id ) {
		$exceptions = Settings::get( 'waf_custom_exceptions', array() );
		if ( ! is_array( $exceptions ) ) {
			return false;
		}

		$filtered = array();
		foreach ( $exceptions as $exc ) {
			if ( isset( $exc['id'] ) && $exc['id'] === $exception_id ) {
				continue; // Ignora o excluído
			}
			$filtered[] = $exc;
		}

		return Settings::update( array( 'waf_custom_exceptions' => $filtered ) );
	}
}
