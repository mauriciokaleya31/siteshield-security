<?php
/**
 * Motor de Rate Limiting do SiteShield
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
 * Classe Rate_Limiter
 */
class Rate_Limiter {

	/**
	 * Inicializa os ganchos do Rate Limiting
	 */
	public static function init() {
		if ( ! Settings::get( 'rate_limit_enabled', true ) ) {
			return;
		}

		add_action( 'init', array( __CLASS__, 'check_general_rate_limit' ), 1 );
		add_action( 'wp_authenticate_user', array( __CLASS__, 'check_login_rate_limit' ), 1, 2 );
		add_action( 'wp_login_failed', array( __CLASS__, 'record_login_failure' ), 10, 1 );
	}

	/**
	 * Verifica limite de requisições gerais por minuto
	 */
	public static function check_general_rate_limit() {
		if ( is_admin() || ( defined( 'DOING_CRON' ) && DOING_CRON ) ) {
			return;
		}

		$client_ip = Compatibility::get_client_ip();
		if ( IP_Manager::is_ip_allowed( $client_ip ) ) {
			return;
		}

		$max_req = intval( Settings::get( 'rate_limit_general_max_req', 120 ) );
		$key     = 'ss_rate_' . md5( $client_ip );
		$count   = intval( get_transient( $key ) );

		if ( $count >= $max_req ) {
			IP_Manager::record_failed_attempt( $client_ip, 'Limite de requisições por minuto excedido (Rate Limit)' );
			status_header( 429 );
			nocache_headers();
			wp_die( '<h1>429 Muitas Requisições</h1><p>Você excedeu a taxa de requisições permitida. Aguarde 1 minuto.</p>', '429 Rate Limit Exceeded', array( 'response' => 429 ) );
			exit;
		}

		set_transient( $key, $count + 1, 60 );
	}

	/**
	 * Verifica se o IP do usuário está bloqueado por tentativas excessivas de login
	 */
	public static function check_login_rate_limit( $user, $password ) {
		$client_ip = Compatibility::get_client_ip();

		if ( IP_Manager::is_ip_allowed( $client_ip ) ) {
			return $user;
		}

		$max_attempts = intval( Settings::get( 'rate_limit_login_attempts', 5 ) );
		$key          = 'ss_login_fails_' . md5( $client_ip );
		$fails        = intval( get_transient( $key ) );

		if ( $fails >= $max_attempts ) {
			$lockout_min = intval( Settings::get( 'rate_limit_login_lockout_min', 15 ) );
			return new \WP_Error(
				'siteshield_lockout',
				sprintf(
					/* translators: %d: minutos de bloqueio */
					__( '<strong>SiteShield:</strong> Muitas tentativas de login incorretas. Seu IP foi bloqueado temporariamente por %d minutos.', 'siteshield' ),
					$lockout_min
				)
			);
		}

		return $user;
	}

	/**
	 * Registra falha de login e incrementa contador do IP
	 */
	public static function record_login_failure( $username ) {
		$client_ip    = Compatibility::get_client_ip();
		$max_attempts = intval( Settings::get( 'rate_limit_login_attempts', 5 ) );
		$lockout_min  = intval( Settings::get( 'rate_limit_login_lockout_min', 15 ) );
		$key          = 'ss_login_fails_' . md5( $client_ip );

		$fails = intval( get_transient( $key ) ) + 1;
		set_transient( $key, $fails, $lockout_min * MINUTE_IN_SECONDS );

		if ( $fails >= $max_attempts ) {
			IP_Manager::block_ip_temporary( $client_ip, 'Tentativas excessivas de login com falha', $lockout_min );
		}
	}
}
