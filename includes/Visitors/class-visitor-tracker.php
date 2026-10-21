<?php
/**
 * Rastreador Leve de Estatísticas de Visitantes e Gestão de IPs do SiteShield
 *
 * @package Mauriciokaleya by SiteShield
 * @author Mauriciokaleya
 * @subpackage Visitors
 */

namespace SiteShield\Visitors;

use SiteShield\Core\Database;
use SiteShield\Core\Settings;
use SiteShield\Core\Compatibility;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Classe Visitor_Tracker
 */
class Visitor_Tracker {

	/**
	 * Inicializa o rastreador
	 */
	public static function init() {
		if ( ! Settings::get( 'visitor_tracking_enabled', true ) ) {
			return;
		}

		add_action( 'template_redirect', array( __CLASS__, 'track_visit' ), 99 );
	}

	/**
	 * Rastreia a visita no frontend de forma assíncrona/leve
	 */
	public static function track_visit() {
		// Não rastreia requisições administrativas, cron, REST ou robôs conhecidos de busca
		$is_cron = function_exists( 'wp_doing_cron' ) ? wp_doing_cron() : ( defined( 'DOING_CRON' ) && DOING_CRON );
		$is_ajax = function_exists( 'wp_doing_ajax' ) ? wp_doing_ajax() : ( defined( 'DOING_AJAX' ) && DOING_AJAX );
		if ( is_admin() || $is_cron || ( defined( 'REST_REQUEST' ) && REST_REQUEST ) || $is_ajax ) {
			return;
		}

		$raw_ip     = Compatibility::get_client_ip();
		$user_agent = isset( $_SERVER['HTTP_USER_AGENT'] ) ? sanitize_text_field( wp_unslash( $_SERVER['HTTP_USER_AGENT'] ) ) : '';

		// Aplica anonimização se configurado
		$stored_ip = self::anonymize_ip( $raw_ip );

		global $wpdb;
		$tables = Database::get_table_names();

		if ( $wpdb->get_var( "SHOW TABLES LIKE '{$tables['visitors']}'" ) !== $tables['visitors'] ) {
			return;
		}

		$country = self::detect_country();

		$wpdb->query(
			$wpdb->prepare(
				"INSERT INTO {$tables['visitors']} (ip_address, country, user_agent, requests_count, page_views, is_suspicious, first_seen, last_seen)
				VALUES (%s, %s, %s, 1, 1, 0, %s, %s)
				ON DUPLICATE KEY UPDATE requests_count = requests_count + 1, page_views = page_views + 1, last_seen = %s", // phpcs:ignore WordPress.DB.PreparedSQL.InterpolatedNotPrepared
				$stored_ip, $country, substr( $user_agent, 0, 255 ), current_time( 'mysql' ), current_time( 'mysql' ), current_time( 'mysql' )
			)
		);
	}

	/**
	 * Anonimiza o endereço IP para conformidade com privacidade (GDPR / LGPD)
	 *
	 * @param string $ip Endereço IP original.
	 * @return string IP com o último octeto mascarado.
	 */
	public static function anonymize_ip( $ip ) {
		if ( ! Settings::get( 'ip_anonymization_enabled', true ) ) {
			return $ip;
		}

		if ( filter_var( $ip, FILTER_VALIDATE_IP, FILTER_FLAG_IPV4 ) ) {
			$parts = explode( '.', $ip );
			if ( count( $parts ) === 4 ) {
				return $parts[0] . '.' . $parts[1] . '.' . $parts[2] . '.xxx';
			}
		} elseif ( filter_var( $ip, FILTER_VALIDATE_IP, FILTER_FLAG_IPV6 ) ) {
			$parts = explode( ':', $ip );
			if ( count( $parts ) >= 4 ) {
				return $parts[0] . ':' . $parts[1] . ':' . $parts[2] . ':xxxx:xxxx';
			}
		}

		return $ip;
	}

	/**
	 * Detecta país via cabeçalhos Cloudflare ou GeoIP padrão
	 *
	 * @return string
	 */
	private static function detect_country() {
		if ( isset( $_SERVER['HTTP_CF_IPCOUNTRY'] ) ) {
			$code = sanitize_text_field( wp_unslash( $_SERVER['HTTP_CF_IPCOUNTRY'] ) );
			return ! empty( $code ) && 'XX' !== $code ? strtoupper( $code ) : 'Global';
		}
		return 'Global';
	}

	/**
	 * Retorna as estatísticas consolidadas de visitantes
	 *
	 * @param string $period '24h', '7d', '30d', '90d'.
	 * @return array
	 */
	public static function get_stats( $period = '7d' ) {
		global $wpdb;
		$tables = Database::get_table_names();

		$days = 7;
		if ( '24h' === $period ) {
			$days = 1;
		} elseif ( '30d' === $period ) {
			$days = 30;
		} elseif ( '90d' === $period ) {
			$days = 90;
		}

		$cutoff = gmdate( 'Y-m-d H:i:s', time() - ( $days * DAY_IN_SECONDS ) );

		if ( $wpdb->get_var( "SHOW TABLES LIKE '{$tables['visitors']}'" ) !== $tables['visitors'] ) {
			return array(
				'total_visitors'   => 0,
				'unique_visitors'  => 0,
				'page_views'       => 0,
				'blocked_requests' => 0,
				'chart_data'       => array(),
			);
		}

		$unique_visitors = intval( $wpdb->get_var( $wpdb->prepare( "SELECT COUNT(DISTINCT ip_address) FROM {$tables['visitors']} WHERE last_seen >= %s", $cutoff ) ) ); // phpcs:ignore WordPress.DB.PreparedSQL.InterpolatedNotPrepared
		$total_requests  = intval( $wpdb->get_var( $wpdb->prepare( "SELECT SUM(requests_count) FROM {$tables['visitors']} WHERE last_seen >= %s", $cutoff ) ) ); // phpcs:ignore WordPress.DB.PreparedSQL.InterpolatedNotPrepared
		$total_views     = intval( $wpdb->get_var( $wpdb->prepare( "SELECT SUM(page_views) FROM {$tables['visitors']} WHERE last_seen >= %s", $cutoff ) ) ); // phpcs:ignore WordPress.DB.PreparedSQL.InterpolatedNotPrepared

		// Ataques bloqueados no mesmo período
		$blocked_count = 0;
		if ( $wpdb->get_var( "SHOW TABLES LIKE '{$tables['events']}'" ) === $tables['events'] ) {
			$blocked_count = intval( $wpdb->get_var( $wpdb->prepare( "SELECT COUNT(*) FROM {$tables['events']} WHERE action_taken = 'BLOCK' AND created_at >= %s", $cutoff ) ) ); // phpcs:ignore WordPress.DB.PreparedSQL.InterpolatedNotPrepared
		}

		// Gera pontos para o gráfico de linhas
		$chart_points = array();
		for ( $i = $days; $i >= 0; $i-- ) {
			$day_date = date( 'Y-m-d', time() - ( $i * DAY_IN_SECONDS ) );
			$day_label = date( 'd/m', time() - ( $i * DAY_IN_SECONDS ) );
			$day_count = intval( $wpdb->get_var( $wpdb->prepare( "SELECT COUNT(*) FROM {$tables['visitors']} WHERE DATE(last_seen) = %s", $day_date ) ) ); // phpcs:ignore WordPress.DB.PreparedSQL.InterpolatedNotPrepared
			$chart_points[] = array(
				'date'  => $day_label,
				'count' => $day_count,
			);
		}

		return array(
			'period'           => $period,
			'total_visitors'   => $total_requests > 0 ? $total_requests : $unique_visitors,
			'unique_visitors'  => $unique_visitors,
			'page_views'       => $total_views,
			'blocked_requests' => $blocked_count,
			'chart_points'     => $chart_points,
		);
	}

	/**
	 * Retorna a listagem paginada de endereços IP de visitantes
	 *
	 * @param int $limit Limite de registros.
	 * @param int $offset Deslocamento.
	 * @return array
	 */
	public static function get_visitor_ips( $limit = 50, $offset = 0 ) {
		global $wpdb;
		$tables = Database::get_table_names();

		if ( $wpdb->get_var( "SHOW TABLES LIKE '{$tables['visitors']}'" ) !== $tables['visitors'] ) {
			return array();
		}

		return $wpdb->get_results(
			$wpdb->prepare( "SELECT * FROM {$tables['visitors']} ORDER BY last_seen DESC LIMIT %d OFFSET %d", $limit, $offset ) // phpcs:ignore WordPress.DB.PreparedSQL.InterpolatedNotPrepared
		);
	}
}
