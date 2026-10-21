<?php
/**
 * Centro Unificado de Eventos de Segurança e Motor de Severidade (Severity Engine)
 *
 * @package Mauriciokaleya by SiteShield
 * @author Mauriciokaleya
 * @subpackage Events
 */

namespace SiteShield\Events;

use SiteShield\Core\Database;
use SiteShield\Core\Compatibility;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Classe Event_Center
 */
class Event_Center {

	/**
	 * Níveis de severidade padronizados
	 */
	const SEV_CRITICAL = 'CRITICAL';
	const SEV_HIGH     = 'HIGH';
	const SEV_MEDIUM   = 'MEDIUM';
	const SEV_LOW      = 'LOW';
	const SEV_INFO     = 'INFO';

	/**
	 * Registra um evento de segurança estruturado
	 *
	 * @param string $action_type Tipo de ação (ex: 'waf_block', 'failed_login', 'malware_detected', 'emergency_mode').
	 * @param string $severity Nível de severidade ('CRITICAL', 'HIGH', 'MEDIUM', 'LOW', 'INFO').
	 * @param string $details Detalhes da ocorrência.
	 * @param string $ip_address Endereço IP do originador.
	 * @param int    $user_id ID do utilizador (se autenticado).
	 * @return bool
	 */
	public static function record_event( $action_type, $severity = self::SEV_INFO, $details = '', $ip_address = '', $user_id = 0 ) {
		global $wpdb;
		$tables = Database::get_table_names();

		if ( empty( $ip_address ) ) {
			$ip_address = Compatibility::get_client_ip();
		}

		$user_login = 'Guest';
		if ( $user_id > 0 ) {
			$user = get_userdata( $user_id );
			if ( $user ) {
				$user_login = $user->user_login;
			}
		} elseif ( is_user_logged_in() ) {
			$current_user = wp_get_current_user();
			$user_id      = $current_user->ID;
			$user_login   = $current_user->user_login;
		}

		$user_agent = isset( $_SERVER['HTTP_USER_AGENT'] ) ? sanitize_text_field( wp_unslash( $_SERVER['HTTP_USER_AGENT'] ) ) : '';

		// Insere na tabela wp_siteshield_audit_log
		return (bool) $wpdb->insert(
			$tables['audit_log'],
			array(
				'user_id'    => $user_id,
				'user_login' => $user_login,
				'action_type'=> sanitize_key( $action_type ),
				'object_type'=> sanitize_key( $severity ),
				'object_id'  => uniqid( 'ev_' ),
				'details'    => sanitize_textarea_field( $details ),
				'ip_address' => sanitize_text_field( $ip_address ),
				'user_agent' => substr( $user_agent, 0, 255 ),
				'created_at' => current_time( 'mysql' ),
			),
			array( '%d', '%s', '%s', '%s', '%s', '%s', '%s', '%s', '%s' )
		);
	}

	/**
	 * Retorna os eventos filtrados por severidade e paginação
	 *
	 * @param string $severity_filter Filtro de severidade ('ALL', 'CRITICAL', 'HIGH', 'MEDIUM', 'LOW', 'INFO').
	 * @param int    $limit Limite de resultados.
	 * @param int    $offset Deslocamento.
	 * @return array
	 */
	public static function get_events( $severity_filter = 'ALL', $limit = 50, $offset = 0 ) {
		global $wpdb;
		$tables = Database::get_table_names();

		if ( $wpdb->get_var( "SHOW TABLES LIKE '{$tables['audit_log']}'" ) !== $tables['audit_log'] ) {
			return array();
		}

		if ( 'ALL' !== $severity_filter && in_array( strtoupper( $severity_filter ), array( self::SEV_CRITICAL, self::SEV_HIGH, self::SEV_MEDIUM, self::SEV_LOW, self::SEV_INFO ), true ) ) {
			return $wpdb->get_results(
				$wpdb->prepare(
					"SELECT * FROM {$tables['audit_log']} WHERE object_type = %s ORDER BY id DESC LIMIT %d OFFSET %d", // phpcs:ignore WordPress.DB.PreparedSQL.InterpolatedNotPrepared
					strtoupper( $severity_filter ),
					$limit,
					$offset
				)
			);
		}

		return $wpdb->get_results(
			$wpdb->prepare(
				"SELECT * FROM {$tables['audit_log']} ORDER BY id DESC LIMIT %d OFFSET %d", // phpcs:ignore WordPress.DB.PreparedSQL.InterpolatedNotPrepared
				$limit,
				$offset
			)
		);
	}
}
