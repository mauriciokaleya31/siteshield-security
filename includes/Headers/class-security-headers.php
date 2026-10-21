<?php
/**
 * Gerenciador de Cabeçalhos HTTP de Segurança do SiteShield
 *
 * @package Mauriciokaleya by SiteShield
 * @author Mauriciokaleya
 * @subpackage Headers
 */

namespace SiteShield\Headers;

use SiteShield\Core\Settings;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Classe Security_Headers
 */
class Security_Headers {

	/**
	 * Inicializa o envio de cabeçalhos
	 */
	public static function init() {
		if ( ! Settings::get( 'headers_enabled', true ) ) {
			return;
		}

		add_action( 'send_headers', array( __CLASS__, 'inject_headers' ) );
	}

	/**
	 * Injeta cabeçalhos HTTP de segurança de nível corporateivo
	 */
	public static function inject_headers() {
		if ( headers_sent() ) {
			return;
		}

		// 1. HSTS (Strict-Transport-Security) se HTTPS estiver ativo
		if ( is_ssl() && Settings::get( 'headers_hsts', false ) ) {
			$hsts_val = 'max-age=31536000; includeSubDomains';
			if ( Settings::get( 'headers_hsts_preload', false ) ) {
				$hsts_val .= '; preload';
			}
			header( 'Strict-Transport-Security: ' . $hsts_val );
		}

		// 2. X-Frame-Options
		$x_frame = Settings::get( 'headers_x_frame_options', 'SAMEORIGIN' );
		if ( 'OFF' !== $x_frame ) {
			header( 'X-Frame-Options: ' . esc_attr( $x_frame ) );
		}

		// 3. X-Content-Type-Options
		if ( Settings::get( 'headers_x_content_type', true ) ) {
			header( 'X-Content-Type-Options: nosniff' );
		}

		// 4. X-XSS-Protection
		if ( Settings::get( 'headers_x_xss_protection', true ) ) {
			header( 'X-XSS-Protection: 1; mode=block' );
		}

		// 5. Referrer-Policy
		$referrer_policy = Settings::get( 'headers_referrer_policy', 'strict-origin-when-cross-origin' );
		if ( ! empty( $referrer_policy ) ) {
			header( 'Referrer-Policy: ' . esc_attr( $referrer_policy ) );
		}

		// 6. Permissions-Policy
		$perm_policy = Settings::get( 'headers_permissions_policy', 'camera=(), microphone=(), geolocation=()' );
		if ( ! empty( $perm_policy ) ) {
			header( 'Permissions-Policy: ' . esc_attr( $perm_policy ) );
		}

		// 7. COOP & CORP (Cross-Origin Protection)
		header( 'Cross-Origin-Opener-Policy: same-origin-allow-popups' );
		header( 'Cross-Origin-Resource-Policy: same-origin' );

		// 8. Content-Security-Policy (CSP)
		$csp_mode = Settings::get( 'headers_csp_mode', 'compatible' );
		if ( 'compatible' === $csp_mode ) {
			header( "Content-Security-Policy: frame-ancestors 'self';" );
		} elseif ( 'strict' === $csp_mode ) {
			header( "Content-Security-Policy: default-src 'self' https: data: 'unsafe-inline' 'unsafe-eval'; frame-ancestors 'self';" );
		} elseif ( 'custom' === $csp_mode ) {
			$custom_csp = Settings::get( 'headers_csp_custom', '' );
			if ( ! empty( $custom_csp ) ) {
				header( 'Content-Security-Policy: ' . esc_attr( $custom_csp ) );
			}
		}
	}
}
