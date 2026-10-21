<?php
/**
 * Registrador Estruturado de Atividades e Auditoria do SiteShield
 *
 * @package Mauriciokaleya by SiteShield
 * @author Mauriciokaleya
 * @subpackage Audit
 */

namespace SiteShield\Audit;

use SiteShield\Core\Database;
use SiteShield\Core\Compatibility;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Classe Audit_Logger
 */
class Audit_Logger {

	/**
	 * Inicializa os ganchos de auditoria automática
	 */
	public static function init() {
		// Log de logins e logouts
		add_action( 'wp_login', array( __CLASS__, 'log_user_login' ), 10, 2 );
		add_action( 'wp_logout', array( __CLASS__, 'log_user_logout' ) );

		// Log de alterações de plugins
		add_action( 'activated_plugin', array( __CLASS__, 'log_plugin_activation' ) );
		add_action( 'deactivated_plugin', array( __CLASS__, 'log_plugin_deactivation' ) );

		// Log de alterações de usuários
		add_action( 'user_register', array( __CLASS__, 'log_user_registration' ) );
		add_action( 'delete_user', array( __CLASS__, 'log_user_deletion' ) );
	}

	/**
	 * Registra um evento customizado no log de auditoria
	 *
	 * @param string $action_type Tipo de ação (ex: login, plugin_activated, settings_updated).
	 * @param string $object_type Objeto afetado (ex: user, plugin, system).
	 * @param string $object_id Identificador.
	 * @param string $details Detalhes da ação.
	 */
	public static function log( $action_type, $object_type, $object_id, $details ) {
		global $wpdb;
		$tables     = Database::get_table_names();
		$user_id      = function_exists( 'get_current_user_id' ) ? get_current_user_id() : 0;
		$current_user = function_exists( 'wp_get_current_user' ) ? wp_get_current_user() : null;
		$user_login   = ( is_object( $current_user ) && isset( $current_user->user_login ) ) ? $current_user->user_login : 'system';

		if ( $wpdb->get_var( "SHOW TABLES LIKE '{$tables['audit_log']}'" ) === $tables['audit_log'] ) {
			$wpdb->insert(
				$tables['audit_log'],
				array(
					'user_id'     => $user_id,
					'user_login'  => $user_login ? $user_login : 'system',
					'action_type' => $action_type,
					'object_type' => $object_type,
					'object_id'   => substr( $object_id, 0, 100 ),
					'details'     => is_array( $details ) ? wp_json_encode( $details ) : substr( $details, 0, 1000 ),
					'ip_address'  => Compatibility::get_client_ip(),
					'user_agent'  => isset( $_SERVER['HTTP_USER_AGENT'] ) ? sanitize_text_field( wp_unslash( $_SERVER['HTTP_USER_AGENT'] ) ) : '',
					'created_at'  => current_time( 'mysql' ),
				)
			);
		}
	}

	public static function log_user_login( $user_login, $user ) {
		self::log( 'user_login', 'user', $user_login, 'Login efetuado com sucesso no sistema' );
	}

	public static function log_user_logout() {
		$user  = function_exists( 'wp_get_current_user' ) ? wp_get_current_user() : null;
		$login = ( $user && isset( $user->user_login ) ) ? $user->user_login : 'unknown';
		self::log( 'user_logout', 'user', $login, 'Logout efetuado do sistema' );
	}

	public static function log_plugin_activation( $plugin ) {
		self::log( 'plugin_activated', 'plugin', $plugin, 'Plugin ativado no WordPress: ' . $plugin );
	}

	public static function log_plugin_deactivation( $plugin ) {
		self::log( 'plugin_deactivated', 'plugin', $plugin, 'Plugin desativado no WordPress: ' . $plugin );
	}

	public static function log_user_registration( $user_id ) {
		$user  = function_exists( 'get_userdata' ) ? get_userdata( $user_id ) : null;
		$login = ( $user && isset( $user->user_login ) ) ? $user->user_login : (string) $user_id;
		self::log( 'user_created', 'user', $login, 'Novo usuário cadastrado' );
	}

	public static function log_user_deletion( $user_id ) {
		self::log( 'user_deleted', 'user', (string) $user_id, 'Usuário removido do sistema' );
	}

	/**
	 * Retorna os logs de auditoria com paginação
	 *
	 * @param int $limit Limite de registros.
	 * @param int $offset Offset.
	 * @return array
	 */
	public static function get_logs( $limit = 50, $offset = 0 ) {
		global $wpdb;
		$tables = Database::get_table_names();

		if ( $wpdb->get_var( "SHOW TABLES LIKE '{$tables['audit_log']}'" ) !== $tables['audit_log'] ) {
			return array();
		}

		return $wpdb->get_results(
			$wpdb->prepare( "SELECT * FROM {$tables['audit_log']} ORDER BY id DESC LIMIT %d OFFSET %d", $limit, $offset ) // phpcs:ignore WordPress.DB.PreparedSQL.InterpolatedNotPrepared
		);
	}
}
