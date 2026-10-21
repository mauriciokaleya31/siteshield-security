<?php
/**
 * Instalador e Gerenciador de Banco de Dados do SiteShield
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
 * Classe Database
 */
class Database {

	/**
	 * Versão atual do esquema do banco
	 */
	const DB_VERSION = '2.1.0';

	/**
	 * Retorna os nomes das tabelas com prefixo do WordPress
	 *
	 * @return array
	 */
	public static function get_table_names() {
		global $wpdb;
		return array(
			'events'        => $wpdb->prefix . 'siteshield_events',
			'ips'           => $wpdb->prefix . 'siteshield_ips',
			'scan_findings' => $wpdb->prefix . 'siteshield_scan_findings',
			'integrity'     => $wpdb->prefix . 'siteshield_integrity',
			'quarantine'    => $wpdb->prefix . 'siteshield_quarantine',
			'audit_log'     => $wpdb->prefix . 'siteshield_audit_log',
			'visitors'      => $wpdb->prefix . 'siteshield_visitors',
		);
	}

	/**
	 * Cria ou atualiza as tabelas do plugin via dbDelta
	 */
	public static function install_tables() {
		global $wpdb;
		if ( ! function_exists( 'dbDelta' ) && file_exists( ABSPATH . 'wp-admin/includes/upgrade.php' ) ) {
			require_once ABSPATH . 'wp-admin/includes/upgrade.php';
		}
		if ( ! function_exists( 'dbDelta' ) ) {
			return;
		}

		$charset_collate = $wpdb->get_charset_collate();
		$tables          = self::get_table_names();

		// 1. Tabela de Eventos e Ataques de Firewall (WAF)
		$sql_events = "CREATE TABLE {$tables['events']} (
			id bigint(20) unsigned NOT NULL AUTO_INCREMENT,
			ip_address varchar(45) NOT NULL DEFAULT '',
			event_type varchar(50) NOT NULL DEFAULT 'waf_block',
			rule_triggered varchar(100) NOT NULL DEFAULT '',
			severity varchar(20) NOT NULL DEFAULT 'medium',
			action_taken varchar(20) NOT NULL DEFAULT 'BLOCK',
			request_method varchar(10) NOT NULL DEFAULT 'GET',
			request_uri text NOT NULL,
			payload text NOT NULL,
			user_agent text NOT NULL,
			country_code varchar(3) NOT NULL DEFAULT '',
			created_at datetime NOT NULL DEFAULT '0000-00-00 00:00:00',
			PRIMARY KEY  (id),
			KEY ip_address (ip_address),
			KEY event_type (event_type),
			KEY severity (severity),
			KEY created_at (created_at)
		) $charset_collate;";

		// 2. Tabela de Gestão de IPs (Blocklist / Allowlist)
		$sql_ips = "CREATE TABLE {$tables['ips']} (
			id bigint(20) unsigned NOT NULL AUTO_INCREMENT,
			ip_address varchar(45) NOT NULL DEFAULT '',
			list_type varchar(20) NOT NULL DEFAULT 'blocked',
			reason varchar(255) NOT NULL DEFAULT '',
			attempts int(11) unsigned NOT NULL DEFAULT 1,
			is_permanent tinyint(1) NOT NULL DEFAULT 0,
			expires_at datetime DEFAULT NULL,
			created_at datetime NOT NULL DEFAULT '0000-00-00 00:00:00',
			updated_at datetime NOT NULL DEFAULT '0000-00-00 00:00:00',
			PRIMARY KEY  (id),
			UNIQUE KEY ip_list (ip_address, list_type),
			KEY list_type (list_type),
			KEY expires_at (expires_at)
		) $charset_collate;";

		// 3. Tabela de Achados e Detecções do Scanner de Malware
		$sql_findings = "CREATE TABLE {$tables['scan_findings']} (
			id bigint(20) unsigned NOT NULL AUTO_INCREMENT,
			scan_session varchar(64) NOT NULL DEFAULT '',
			file_path varchar(500) NOT NULL DEFAULT '',
			line_number int(11) unsigned NOT NULL DEFAULT 0,
			rule_id varchar(100) NOT NULL DEFAULT '',
			severity varchar(20) NOT NULL DEFAULT 'high',
			signature_name varchar(255) NOT NULL DEFAULT '',
			code_snippet text NOT NULL,
			file_hash varchar(64) NOT NULL DEFAULT '',
			status varchar(20) NOT NULL DEFAULT 'active',
			created_at datetime NOT NULL DEFAULT '0000-00-00 00:00:00',
			PRIMARY KEY  (id),
			KEY scan_session (scan_session),
			KEY severity (severity),
			KEY status (status)
		) $charset_collate;";

		// 4. Tabela de Linha de Base de Integridade de Arquivos
		$sql_integrity = "CREATE TABLE {$tables['integrity']} (
			id bigint(20) unsigned NOT NULL AUTO_INCREMENT,
			file_path varchar(500) NOT NULL DEFAULT '',
			file_hash varchar(64) NOT NULL DEFAULT '',
			file_size bigint(20) unsigned NOT NULL DEFAULT 0,
			status varchar(20) NOT NULL DEFAULT 'clean',
			last_checked datetime NOT NULL DEFAULT '0000-00-00 00:00:00',
			PRIMARY KEY  (id),
			UNIQUE KEY file_path_idx (file_path(190)),
			KEY status (status)
		) $charset_collate;";

		// 5. Tabela de Quarentena Segura de Arquivos
		$sql_quarantine = "CREATE TABLE {$tables['quarantine']} (
			id bigint(20) unsigned NOT NULL AUTO_INCREMENT,
			original_path varchar(500) NOT NULL DEFAULT '',
			quarantine_path varchar(500) NOT NULL DEFAULT '',
			file_hash varchar(64) NOT NULL DEFAULT '',
			reason varchar(255) NOT NULL DEFAULT '',
			user_id bigint(20) unsigned NOT NULL DEFAULT 0,
			restored_at datetime DEFAULT NULL,
			created_at datetime NOT NULL DEFAULT '0000-00-00 00:00:00',
			PRIMARY KEY  (id),
			KEY original_path_idx (original_path(190)),
			KEY created_at (created_at)
		) $charset_collate;";

		// 6. Tabela de Log Estruturado de Atividades e Auditoria
		$sql_audit = "CREATE TABLE {$tables['audit_log']} (
			id bigint(20) unsigned NOT NULL AUTO_INCREMENT,
			user_id bigint(20) unsigned NOT NULL DEFAULT 0,
			user_login varchar(60) NOT NULL DEFAULT '',
			action_type varchar(50) NOT NULL DEFAULT '',
			object_type varchar(50) NOT NULL DEFAULT '',
			object_id varchar(100) NOT NULL DEFAULT '',
			details text NOT NULL,
			ip_address varchar(45) NOT NULL DEFAULT '',
			user_agent text NOT NULL,
			created_at datetime NOT NULL DEFAULT '0000-00-00 00:00:00',
			PRIMARY KEY  (id),
			KEY user_id (user_id),
			KEY action_type (action_type),
			KEY created_at (created_at)
		) $charset_collate;";

		// 7. Tabela de Estatísticas de Visitantes & Requisições (Leve & Otimizada)
		$sql_visitors = "CREATE TABLE {$tables['visitors']} (
			id bigint(20) unsigned NOT NULL AUTO_INCREMENT,
			ip_address varchar(45) NOT NULL DEFAULT '',
			country varchar(100) NOT NULL DEFAULT 'Global',
			country_code varchar(3) NOT NULL DEFAULT 'XX',
			user_agent text NOT NULL,
			requests_count int(11) unsigned NOT NULL DEFAULT 1,
			page_views int(11) unsigned NOT NULL DEFAULT 1,
			is_suspicious tinyint(1) NOT NULL DEFAULT 0,
			first_seen datetime NOT NULL DEFAULT '0000-00-00 00:00:00',
			last_seen datetime NOT NULL DEFAULT '0000-00-00 00:00:00',
			PRIMARY KEY  (id),
			UNIQUE KEY ip_addr_idx (ip_address),
			KEY last_seen (last_seen),
			KEY is_suspicious (is_suspicious)
		) $charset_collate;";

		dbDelta( $sql_events );
		dbDelta( $sql_ips );
		dbDelta( $sql_findings );
		dbDelta( $sql_integrity );
		dbDelta( $sql_quarantine );
		dbDelta( $sql_audit );
		dbDelta( $sql_visitors );

		update_option( 'siteshield_db_version', self::DB_VERSION );
	}

	/**
	 * Remove todas as tabelas em caso de desinstalação com limpeza explícita
	 */
	public static function drop_tables() {
		global $wpdb;
		$tables = self::get_table_names();
		foreach ( $tables as $table ) {
			$wpdb->query( "DROP TABLE IF EXISTS {$table};" ); // phpcs:ignore WordPress.DB.PreparedSQL.InterpolatedNotPrepared
		}
		delete_option( 'siteshield_db_version' );
	}

	/**
	 * Limpeza periódica de logs e visitantes antigos com base na política de privacidade
	 *
	 * @param int $days_to_keep Quantidade de dias de retenção.
	 */
	public static function cleanup_old_logs( $days_to_keep = 30 ) {
		global $wpdb;
		$tables      = self::get_table_names();
		$cutoff_date = gmdate( 'Y-m-d H:i:s', time() - ( intval( $days_to_keep ) * DAY_IN_SECONDS ) );

		$wpdb->query( $wpdb->prepare( "DELETE FROM {$tables['events']} WHERE created_at < %s", $cutoff_date ) ); // phpcs:ignore WordPress.DB.PreparedSQL.InterpolatedNotPrepared
		$wpdb->query( $wpdb->prepare( "DELETE FROM {$tables['audit_log']} WHERE created_at < %s", $cutoff_date ) ); // phpcs:ignore WordPress.DB.PreparedSQL.InterpolatedNotPrepared
		$wpdb->query( $wpdb->prepare( "DELETE FROM {$tables['visitors']} WHERE last_seen < %s", $cutoff_date ) ); // phpcs:ignore WordPress.DB.PreparedSQL.InterpolatedNotPrepared
		$wpdb->query( $wpdb->prepare( "DELETE FROM {$tables['ips']} WHERE is_permanent = 0 AND expires_at < %s", current_time( 'mysql', 1 ) ) ); // phpcs:ignore WordPress.DB.PreparedSQL.InterpolatedNotPrepared
	}
}
