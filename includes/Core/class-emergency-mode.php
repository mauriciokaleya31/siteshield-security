<?php
/**
 * Gerenciador de Modo de Emergência e Proteção Contra Lockout do SiteShield
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
 * Classe Emergency_Mode
 */
class Emergency_Mode {

	/**
	 * Chave de recuperação administrativa de emergência
	 */
	const RECOVERY_OPTION_KEY = 'siteshield_emergency_recovery_token';

	/**
	 * Verifica se o modo de emergência está ativado
	 *
	 * @return bool
	 */
	public static function is_active() {
		// Permite forçar desativação via constante no wp-config.php: define('SITESHIELD_DISABLE_ALL', true);
		if ( defined( 'SITESHIELD_DISABLE_ALL' ) && SITESHIELD_DISABLE_ALL ) {
			return false;
		}

		// Permite forçar ativação via constante no wp-config.php: define('SITESHIELD_EMERGENCY_MODE', true);
		if ( defined( 'SITESHIELD_EMERGENCY_MODE' ) && SITESHIELD_EMERGENCY_MODE ) {
			return true;
		}

		return (bool) Settings::get( 'emergency_mode_active', false );
	}

	/**
	 * Ativa o Modo de Emergência
	 *
	 * @param string $reason Motivo da ativação.
	 * @return bool
	 */
	public static function enable( $reason = 'Acionamento manual pelo administrador' ) {
		$updated = Settings::update( array(
			'emergency_mode_active'    => true,
			'waf_mode'                 => 'BLOCK',
			'rate_limit_enabled'       => true,
			'rate_limit_login_attempts'=> 3,
			'antibot_mode'             => 'BLOCK',
			'login_disable_user_enum'  => true,
			'login_disable_xmlrpc'     => true,
		) );

		self::log_emergency_event( 'Modo de Emergência ATIVADO: ' . $reason );
		return $updated;
	}

	/**
	 * Desativa o Modo de Emergência
	 *
	 * @return bool
	 */
	public static function disable() {
		$updated = Settings::update( array(
			'emergency_mode_active' => false,
			'waf_mode'              => 'MONITOR',
		) );

		self::log_emergency_event( 'Modo de Emergência DESATIVADO' );
		return $updated;
	}

	/**
	 * Gera ou obtém o token de recuperação de emergência para acesso em caso de lockout
	 *
	 * @return string
	 */
	public static function get_or_create_recovery_token() {
		$token = get_option( self::RECOVERY_OPTION_KEY );
		if ( empty( $token ) ) {
			try {
				$token = 'SS-UNLOCK-' . strtoupper( function_exists( 'random_bytes' ) ? bin2hex( random_bytes( 8 ) ) : wp_generate_password( 16, false, false ) );
			} catch ( \Throwable $e ) {
				$token = 'SS-UNLOCK-' . strtoupper( wp_generate_password( 16, false, false ) );
			}
			update_option( self::RECOVERY_OPTION_KEY, $token );
		}
		return $token;
	}

	/**
	 * Registra o evento de emergência no log de auditoria
	 *
	 * @param string $message Mensagem do evento.
	 */
	private static function log_emergency_event( $message ) {
		global $wpdb;
		$tables = Database::get_table_names();
		$user_id = function_exists( 'get_current_user_id' ) ? get_current_user_id() : 0;
		$user_login = function_exists( 'wp_get_current_user' ) ? wp_get_current_user()->user_login : 'system';

		if ( $wpdb->get_var( "SHOW TABLES LIKE '{$tables['audit_log']}'" ) === $tables['audit_log'] ) {
			$wpdb->insert(
				$tables['audit_log'],
				array(
					'user_id'     => $user_id,
					'user_login'  => $user_login ? $user_login : 'system',
					'action_type' => 'emergency_mode',
					'object_type' => 'system',
					'object_id'   => 'security_center',
					'details'     => $message,
					'ip_address'  => isset( $_SERVER['REMOTE_ADDR'] ) ? sanitize_text_field( wp_unslash( $_SERVER['REMOTE_ADDR'] ) ) : '',
					'user_agent'  => isset( $_SERVER['HTTP_USER_AGENT'] ) ? sanitize_text_field( wp_unslash( $_SERVER['HTTP_USER_AGENT'] ) ) : '',
					'created_at'  => current_time( 'mysql' ),
				)
			);
		}
	}
}
