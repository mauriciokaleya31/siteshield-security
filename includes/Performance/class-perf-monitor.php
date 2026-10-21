<?php
/**
 * Monitor de Performance, Consumo de Memória e Métricas do Servidor
 *
 * @package Mauriciokaleya by SiteShield
 * @author Mauriciokaleya
 * @subpackage Performance
 */

namespace SiteShield\Performance;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Classe Perf_Monitor
 */
class Perf_Monitor {

	/**
	 * Retorna as métricas de performance do ambiente em tempo real
	 *
	 * @return array
	 */
	public static function get_metrics() {
		global $wpdb;

		$memory_usage = memory_get_usage( true );
		$memory_peak  = memory_get_peak_usage( true );
		$memory_limit = ini_get( 'memory_limit' );

		$used_fmt = function_exists( 'size_format' ) ? size_format( $memory_usage ) : round( $memory_usage / 1048576, 2 ) . ' MB';
		$peak_fmt = function_exists( 'size_format' ) ? size_format( $memory_peak ) : round( $memory_peak / 1048576, 2 ) . ' MB';
		$queries  = function_exists( 'get_num_queries' ) ? get_num_queries() : 0;

		return array(
			'memory_used'     => $used_fmt,
			'memory_peak'     => $peak_fmt,
			'memory_limit'    => $memory_limit,
			'db_queries'      => $queries,
			'php_time_limit'  => ini_get( 'max_execution_time' ) . 's',
			'php_version'     => PHP_VERSION,
			'server_software' => isset( $_SERVER['SERVER_SOFTWARE'] ) ? sanitize_text_field( wp_unslash( $_SERVER['SERVER_SOFTWARE'] ) ) : 'N/A',
		);
	}
}
