<?php
/**
 * Desinstalador Limpo do SiteShield Security
 *
 * @package Mauriciokaleya by SiteShield
 * @author Mauriciokaleya
 */

if ( ! defined( 'WP_UNINSTALL_PLUGIN' ) ) {
	exit;
}

// Só remove as tabelas e dados se a opção de excluso estiver ativada pelo administrador
$settings = get_option( 'siteshield_settings', array() );

if ( ! empty( $settings['delete_data_on_uninstall'] ) ) {
	global $wpdb;

	$tables = array(
		$wpdb->prefix . 'siteshield_events',
		$wpdb->prefix . 'siteshield_ips',
		$wpdb->prefix . 'siteshield_scan_findings',
		$wpdb->prefix . 'siteshield_integrity',
		$wpdb->prefix . 'siteshield_quarantine',
		$wpdb->prefix . 'siteshield_audit_log',
	);

	foreach ( $tables as $table ) {
		$wpdb->query( "DROP TABLE IF EXISTS {$table};" ); // phpcs:ignore WordPress.DB.PreparedSQL.InterpolatedNotPrepared
	}

	// Remove opções
	delete_option( 'siteshield_settings' );
	delete_option( 'siteshield_stats' );
	delete_option( 'siteshield_db_version' );
	delete_option( 'siteshield_last_scan_time' );
	delete_option( 'siteshield_last_scan_session' );
	delete_option( 'siteshield_last_scan_total_files' );
	delete_option( 'siteshield_integrity_baseline_time' );
	delete_option( 'siteshield_integrity_baseline_count' );
	delete_option( 'siteshield_last_integrity_results' );
	delete_option( 'siteshield_last_vuln_audit' );
	delete_option( 'siteshield_last_vuln_time' );
	delete_option( 'siteshield_emergency_recovery_token' );
	delete_transient( 'siteshield_activation_redirect' );

	// Remove diretório de quarentena
	$upload_dir     = wp_upload_dir();
	$quarantine_dir = $upload_dir['basedir'] . '/siteshield-quarantine';
	if ( is_dir( $quarantine_dir ) ) {
		$files = glob( $quarantine_dir . '/*' );
		foreach ( $files as $f ) {
			if ( is_file( $f ) ) {
				@unlink( $f );
			}
		}
		@rmdir( $quarantine_dir );
	}
}
