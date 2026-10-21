<?php
/**
 * Gerenciador de Endereços IP (Allowlist e Blocklist) do SiteShield
 *
 * @package Mauriciokaleya by SiteShield
 * @author Mauriciokaleya
 * @subpackage Firewall
 */

namespace SiteShield\Firewall;

use SiteShield\Core\Database;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Classe IP_Manager
 */
class IP_Manager {

	/**
	 * Verifica se o IP está explicitamente liberado na lista branca (Allowlist)
	 *
	 * @param string $ip Endereço IP.
	 * @return bool
	 */
	public static function is_ip_allowed( $ip ) {
		global $wpdb;
		$tables = Database::get_table_names();

		if ( $wpdb->get_var( "SHOW TABLES LIKE '{$tables['ips']}'" ) !== $tables['ips'] ) {
			return false;
		}

		$allowed = $wpdb->get_var(
			$wpdb->prepare(
				"SELECT id FROM {$tables['ips']} WHERE ip_address = %s AND list_type = 'allowed' LIMIT 1", // phpcs:ignore WordPress.DB.PreparedSQL.InterpolatedNotPrepared
				$ip
			)
		);

		return ! empty( $allowed );
	}

	/**
	 * Verifica se o IP está bloqueado temporariamente ou permanentemente
	 *
	 * @param string $ip Endereço IP.
	 * @return bool
	 */
	public static function is_ip_blocked( $ip ) {
		global $wpdb;
		$tables = Database::get_table_names();

		if ( $wpdb->get_var( "SHOW TABLES LIKE '{$tables['ips']}'" ) !== $tables['ips'] ) {
			return false;
		}

		$blocked = $wpdb->get_row(
			$wpdb->prepare(
				"SELECT is_permanent, expires_at FROM {$tables['ips']} WHERE ip_address = %s AND list_type = 'blocked' LIMIT 1", // phpcs:ignore WordPress.DB.PreparedSQL.InterpolatedNotPrepared
				$ip
			)
		);

		if ( ! $blocked ) {
			return false;
		}

		if ( ! empty( $blocked->is_permanent ) ) {
			return true;
		}

		// Se expirou, desbloqueia automaticamente
		if ( ! empty( $blocked->expires_at ) && strtotime( $blocked->expires_at ) < time() ) {
			self::unblock_ip( $ip );
			return false;
		}

		return true;
	}

	/**
	 * Bloqueia um IP temporariamente por um número definido de minutos
	 *
	 * @param string $ip Endereço IP.
	 * @param string $reason Motivo do bloqueio.
	 * @param int    $minutes Minutos de duração.
	 */
	public static function block_ip_temporary( $ip, $reason, $minutes = 15 ) {
		global $wpdb;
		$tables     = Database::get_table_names();
		$expires_at = gmdate( 'Y-m-d H:i:s', time() + ( intval( $minutes ) * MINUTE_IN_SECONDS ) );

		$wpdb->query(
			$wpdb->prepare(
				"INSERT INTO {$tables['ips']} (ip_address, list_type, reason, attempts, is_permanent, expires_at, created_at, updated_at) 
				VALUES (%s, 'blocked', %s, 1, 0, %s, %s, %s)
				ON DUPLICATE KEY UPDATE attempts = attempts + 1, reason = %s, expires_at = %s, updated_at = %s", // phpcs:ignore WordPress.DB.PreparedSQL.InterpolatedNotPrepared
				$ip, $reason, $expires_at, current_time( 'mysql', 1 ), current_time( 'mysql', 1 ),
				$reason, $expires_at, current_time( 'mysql', 1 )
			)
		);
	}

	/**
	 * Bloqueia um IP permanentemente
	 *
	 * @param string $ip Endereço IP.
	 * @param string $reason Motivo do bloqueio.
	 */
	public static function block_ip_permanent( $ip, $reason ) {
		global $wpdb;
		$tables = Database::get_table_names();

		$wpdb->query(
			$wpdb->prepare(
				"INSERT INTO {$tables['ips']} (ip_address, list_type, reason, attempts, is_permanent, expires_at, created_at, updated_at) 
				VALUES (%s, 'blocked', %s, 1, 1, NULL, %s, %s)
				ON DUPLICATE KEY UPDATE is_permanent = 1, expires_at = NULL, reason = %s, updated_at = %s", // phpcs:ignore WordPress.DB.PreparedSQL.InterpolatedNotPrepared
				$ip, $reason, current_time( 'mysql', 1 ), current_time( 'mysql', 1 ),
				$reason, current_time( 'mysql', 1 )
			)
		);
	}

	/**
	 * Adiciona um IP à lista de permitidos (Allowlist)
	 *
	 * @param string $ip Endereço IP.
	 * @param string $reason Motivo.
	 */
	public static function allow_ip( $ip, $reason = 'IP Confiável' ) {
		global $wpdb;
		$tables = Database::get_table_names();

		// Remove de bloqueios se existir
		self::unblock_ip( $ip );

		$wpdb->query(
			$wpdb->prepare(
				"INSERT INTO {$tables['ips']} (ip_address, list_type, reason, is_permanent, created_at, updated_at) 
				VALUES (%s, 'allowed', %s, 1, %s, %s)
				ON DUPLICATE KEY UPDATE reason = %s, updated_at = %s", // phpcs:ignore WordPress.DB.PreparedSQL.InterpolatedNotPrepared
				$ip, $reason, current_time( 'mysql', 1 ), current_time( 'mysql', 1 ),
				$reason, current_time( 'mysql', 1 )
			)
		);
	}

	/**
	 * Desbloqueia ou remove um IP da lista
	 *
	 * @param string $ip Endereço IP.
	 */
	public static function unblock_ip( $ip ) {
		global $wpdb;
		$tables = Database::get_table_names();

		$wpdb->delete(
			$tables['ips'],
			array( 'ip_address' => $ip ),
			array( '%s' )
		);
	}

	/**
	 * Incrementa contador de tentativas com falha para um IP
	 */
	public static function record_failed_attempt( $ip, $reason ) {
		self::block_ip_temporary( $ip, $reason, 15 );
	}

	/**
	 * Retorna a listagem de IPs cadastrados
	 *
	 * @param string $type Tipo de lista (all, blocked, allowed).
	 * @return array
	 */
	public static function get_all_ips( $type = 'all' ) {
		global $wpdb;
		$tables = Database::get_table_names();

		if ( $wpdb->get_var( "SHOW TABLES LIKE '{$tables['ips']}'" ) !== $tables['ips'] ) {
			return array();
		}

		if ( 'all' !== $type ) {
			return $wpdb->get_results( $wpdb->prepare( "SELECT * FROM {$tables['ips']} WHERE list_type = %s ORDER BY id DESC LIMIT 100", $type ) ); // phpcs:ignore WordPress.DB.PreparedSQL.InterpolatedNotPrepared
		}

		return $wpdb->get_results( "SELECT * FROM {$tables['ips']} ORDER BY id DESC LIMIT 100" ); // phpcs:ignore WordPress.DB.PreparedSQL.InterpolatedNotPrepared
	}
}
