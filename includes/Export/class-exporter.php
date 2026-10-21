<?php
/**
 * Exportador Multiformato (CSV, JSON, PDF) de Dados e Auditoria do SiteShield
 *
 * @package Mauriciokaleya by SiteShield
 * @author Mauriciokaleya
 * @subpackage Export
 */

namespace SiteShield\Export;

use SiteShield\Events\Event_Center;
use SiteShield\Visitors\Visitor_Tracker;
use SiteShield\Health\System_Health;
use SiteShield\Core\Settings;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Classe Exporter
 */
class Exporter {

	/**
	 * Exporta os eventos de segurança em formato CSV
	 */
	public static function export_events_csv() {
		$events = Event_Center::get_events( 'ALL', 500, 0 );

		header( 'Content-Type: text/csv; charset=UTF-8' );
		header( 'Content-Disposition: attachment; filename="siteshield-security-events-' . date( 'Y-m-d' ) . '.csv"' );

		$output = fopen( 'php://output', 'w' );
		// BOM UTF-8 para Excel
		fprintf( $output, chr(0xEF) . chr(0xBB) . chr(0xBF) );

		fputcsv( $output, array( 'ID', 'Data/Hora', 'Severidade', 'Tipo de Ação', 'Utilizador', 'Endereço IP', 'Detalhes' ) );

		foreach ( $events as $ev ) {
			fputcsv( $output, array(
				$ev->id,
				$ev->created_at,
				strtoupper( $ev->object_type ),
				$ev->action_type,
				$ev->user_login,
				$ev->ip_address,
				$ev->details,
			) );
		}

		fclose( $output );
		exit;
	}

	/**
	 * Exporta os logs de visitantes em formato CSV
	 */
	public static function export_visitors_csv() {
		$visitors = Visitor_Tracker::get_visitor_ips( 1000, 0 );

		header( 'Content-Type: text/csv; charset=UTF-8' );
		header( 'Content-Disposition: attachment; filename="siteshield-visitors-log-' . date( 'Y-m-d' ) . '.csv"' );

		$output = fopen( 'php://output', 'w' );
		fprintf( $output, chr(0xEF) . chr(0xBB) . chr(0xBF) );

		fputcsv( $output, array( 'Endereço IP', 'País', 'Requisições', 'Páginas Vistas', 'Primeiro Acesso', 'Último Acesso', 'Status' ) );

		foreach ( $visitors as $v ) {
			fputcsv( $output, array(
				$v->ip_address,
				$v->country,
				$v->requests_count,
				$v->page_views,
				$v->first_seen,
				$v->last_seen,
				$v->is_suspicious ? 'Suspeito' : 'Normal',
			) );
		}

		fclose( $output );
		exit;
	}

	/**
	 * Exporta o diagnóstico completo do System Health em JSON
	 *
	 * @return string JSON estruturado
	 */
	public static function export_health_json() {
		$health = System_Health::get_system_health();
		return wp_json_encode( array(
			'website'      => get_site_url(),
			'exported_at'  => current_time( 'mysql' ),
			'version'      => SITESHIELD_VERSION,
			'system_health'=> $health,
		), JSON_PRETTY_PRINT );
	}

	/**
	 * Exporta o relatório de Diagnósticos e Logs em CSV
	 */
	public static function export_diagnostics_csv() {
		$parsed = \SiteShield\Diagnostics\Debug_Log_Parser::parse( 500 );
		$groups = $parsed['groups'];

		header( 'Content-Type: text/csv; charset=UTF-8' );
		header( 'Content-Disposition: attachment; filename="siteshield-diagnostics-logs-' . date( 'Y-m-d' ) . '.csv"' );

		$output = fopen( 'php://output', 'w' );
		fprintf( $output, chr(0xEF) . chr(0xBB) . chr(0xBF) );

		fputcsv( $output, array( 'Gravidade', 'Tipo', 'Origem', 'Módulo', 'Ocorrências', 'Primeiro Registro', 'Último Registro', 'Arquivo', 'Linha', 'Mensagem', 'Explicação', 'Recomendação' ) );

		foreach ( $groups as $grp ) {
			$exp = $grp['explanation'];
			fputcsv( $output, array(
				$grp['severity'],
				$grp['type'],
				$grp['origin'],
				$grp['module'],
				$grp['occurrences'],
				$grp['first_seen'],
				$grp['last_seen'],
				$grp['file'],
				$grp['line'],
				$grp['message'],
				$exp['explanation'],
				$exp['recommendation'],
			) );
		}

		fclose( $output );
		exit;
	}

	/**
	 * Exporta o relatório de Diagnósticos e Logs em JSON
	 */
	public static function export_diagnostics_json() {
		$parsed        = \SiteShield\Diagnostics\Debug_Log_Parser::parse( 500 );
		$debug_status  = \SiteShield\Diagnostics\Debug_Log_Parser::get_debug_status();
		$system_status = \SiteShield\Diagnostics\Security_Diagnostics::get_overall_system_status();
		$health        = \SiteShield\Diagnostics\Security_Diagnostics::run_full_system_health_check();

		$data = array(
			'website'       => get_site_url(),
			'exported_at'   => current_time( 'mysql' ),
			'version'       => SITESHIELD_VERSION,
			'system_status' => $system_status,
			'debug_status'  => $debug_status,
			'health_matrix' => $health,
			'log_stats'     => $parsed['stats'],
			'error_groups'  => $parsed['groups'],
		);

		header( 'Content-Type: application/json; charset=UTF-8' );
		header( 'Content-Disposition: attachment; filename="siteshield-diagnostics-logs-' . date( 'Y-m-d' ) . '.json"' );

		echo wp_json_encode( $data, JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE );
		exit;
	}
}
