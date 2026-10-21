<?php
/**
 * Gerador de Relatórios Executivos de Segurança do SiteShield
 * Suporte Multilíngue (Português, Inglês, Espanhol, Francês)
 *
 * @package Mauriciokaleya by SiteShield
 * @author Mauriciokaleya
 * @subpackage Reports
 */

namespace SiteShield\Reports;

use SiteShield\Core\Security_Score;
use SiteShield\Core\Settings;
use SiteShield\Core\Database;
use SiteShield\Visitors\Visitor_Tracker;
use SiteShield\I18n\I18n;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Classe Report_Generator
 */
class Report_Generator {

	/**
	 * Gera o relatório estruturado de segurança do site
	 *
	 * @return array
	 */
	public static function generate_report_data() {
		global $wpdb;
		$tables = Database::get_table_names();

		$score_data = Security_Score::calculate();
		$settings   = Settings::get();
		$visitor_st = Visitor_Tracker::get_stats( '30d' );

		// Achados do scanner
		$findings = array();
		if ( $wpdb->get_var( "SHOW TABLES LIKE '{$tables['scan_findings']}'" ) === $tables['scan_findings'] ) {
			$findings = $wpdb->get_results( "SELECT * FROM {$tables['scan_findings']} WHERE status = 'active' ORDER BY id DESC LIMIT 10" );
		}

		// Eventos recentes de firewall
		$events = array();
		if ( $wpdb->get_var( "SHOW TABLES LIKE '{$tables['events']}'" ) === $tables['events'] ) {
			$events = $wpdb->get_results( "SELECT * FROM {$tables['events']} ORDER BY id DESC LIMIT 10" );
		}

		return array(
			'website_url'    => get_site_url(),
			'generated_at'   => current_time( 'mysql' ),
			'score'          => $score_data['score'],
			'score_label'    => $score_data['label'],
			'score_factors'  => $score_data['factors'],
			'settings'       => $settings,
			'findings'       => $findings,
			'events'         => $events,
			'visitor_stats'  => $visitor_st,
			'total_scanned'  => get_option( 'siteshield_last_scan_total_files', 0 ),
			'last_scan_time' => get_option( 'siteshield_last_scan_time', 0 ),
		);
	}

	/**
	 * Renderiza a visualização HTML/PDF executiva para impressão ou download
	 */
	public static function render_printable_report() {
		$data = self::generate_report_data();
		?>
		<!DOCTYPE html>
		<html lang="<?php echo esc_attr( I18n::get_current_locale() ); ?>">
		<head>
			<meta charset="utf-8">
			<title><?php esc_html_e( 'Executive Security Reports', 'siteshield' ); ?> — SiteShield Security</title>
			<style>
				body { font-family: -apple-system, BlinkMacSystemFont, "Segoe UI", Roboto, sans-serif; background: #FFF; color: #0F172A; margin: 0; padding: 40px; }
				.report-header { display: flex; justify-content: space-between; align-items: center; border-bottom: 2px solid #0066FF; padding-bottom: 20px; margin-bottom: 30px; }
				.report-logo { font-size: 24px; font-weight: 700; color: #0F172A; }
				.report-logo span { color: #0066FF; }
				.report-meta { text-align: right; font-size: 13px; color: #64748B; }
				.score-banner { background: #F8FAFC; border: 1px solid #E2E8F0; padding: 24px; border-radius: 12px; display: flex; align-items: center; justify-content: space-between; margin-bottom: 30px; }
				.score-val { font-size: 42px; font-weight: 800; color: #0066FF; }
				.score-val small { font-size: 20px; color: #64748B; }
				.grid-cards { display: grid; grid-template-columns: repeat(4, 1fr); gap: 15px; margin-bottom: 30px; }
				.card { background: #FFFFFF; border: 1px solid #E2E8F0; padding: 16px; border-radius: 8px; text-align: center; }
				.card strong { font-size: 22px; display: block; margin-top: 6px; color: #0F172A; }
				.card span { font-size: 12px; color: #64748B; }
				table { width: 100%; border-collapse: collapse; margin-bottom: 30px; font-size: 12px; }
				th { background: #F1F5F9; padding: 10px; text-align: left; font-weight: 600; border: 1px solid #CBD5E1; }
				td { padding: 10px; border: 1px solid #E2E8F0; }
				h2 { font-size: 16px; color: #0F172A; margin: 25px 0 10px; border-left: 4px solid #0066FF; padding-left: 10px; }
				.print-btn { position: fixed; bottom: 20px; right: 20px; background: #0066FF; color: #fff; border: none; padding: 12px 24px; border-radius: 8px; font-weight: 600; cursor: pointer; box-shadow: 0 4px 15px rgba(0,102,255,0.3); }
				@media print { .print-btn { display: none; } body { padding: 0; } }
			</style>
		</head>
		<body>
			<button class="print-btn" onclick="window.print()">📥 <?php esc_html_e( 'Download PDF Report', 'siteshield' ); ?></button>

			<div class="report-header">
				<div class="report-logo">SiteShield <span>Security Center</span></div>
				<div class="report-meta">
					<strong>Website:</strong> <?php echo esc_html( $data['website_url'] ); ?><br>
					<strong>Data / Time:</strong> <?php echo esc_html( $data['generated_at'] ); ?><br>
					<strong>Engine Version:</strong> v<?php echo esc_html( SITESHIELD_VERSION ); ?>
				</div>
			</div>

			<div class="score-banner">
				<div>
					<h3 style="margin:0 0 6px;"><?php esc_html_e( 'Security Score', 'siteshield' ); ?></h3>
					<p style="margin:0; color:#64748B; font-size:14px;"><?php echo esc_html( $data['score_label'] ); ?></p>
				</div>
				<div class="score-val">
					<?php echo esc_html( $data['score'] ); ?><small>/100</small>
				</div>
			</div>

			<div class="grid-cards">
				<div class="card">
					<span><?php esc_html_e( 'Threats Detected', 'siteshield' ); ?></span>
					<strong><?php echo count( $data['findings'] ); ?></strong>
				</div>
				<div class="card">
					<span><?php esc_html_e( 'Threats Blocked', 'siteshield' ); ?></span>
					<strong><?php echo esc_html( $data['visitor_stats']['blocked_requests'] ); ?></strong>
				</div>
				<div class="card">
					<span><?php esc_html_e( 'Files Scanned', 'siteshield' ); ?></span>
					<strong><?php echo esc_html( $data['total_scanned'] ); ?></strong>
				</div>
				<div class="card">
					<span><?php esc_html_e( 'Unique Visitors', 'siteshield' ); ?></span>
					<strong><?php echo esc_html( $data['visitor_stats']['unique_visitors'] ); ?></strong>
				</div>
			</div>

			<h2><?php esc_html_e( 'Security Score Breakdown', 'siteshield' ); ?></h2>
			<table>
				<thead>
					<tr>
						<th><?php esc_html_e( 'Critério Auditado', 'siteshield' ); ?></th>
						<th><?php esc_html_e( 'Pontuação', 'siteshield' ); ?></th>
						<th><?php esc_html_e( 'Status', 'siteshield' ); ?></th>
					</tr>
				</thead>
				<tbody>
					<?php foreach ( $data['score_factors'] as $factor ) : ?>
						<tr>
							<td><?php echo esc_html( $factor['name'] ); ?></td>
							<td>+<?php echo esc_html( $factor['score'] ); ?> pts</td>
							<td><strong><?php echo 'pass' === $factor['status'] ? esc_html__( 'Passed', 'siteshield' ) : esc_html__( 'Warning', 'siteshield' ); ?></strong></td>
						</tr>
					<?php endforeach; ?>
				</tbody>
			</table>

			<h2><?php esc_html_e( 'Recent Firewall Interceptions', 'siteshield' ); ?></h2>
			<?php if ( empty( $data['events'] ) ) : ?>
				<p style="font-size: 13px; color: #64748B;"><?php esc_html_e( 'Nenhum evento de ataque registrado até o momento.', 'siteshield' ); ?></p>
			<?php else : ?>
				<table>
					<thead>
						<tr>
							<th><?php esc_html_e( 'Data/Hora', 'siteshield' ); ?></th>
							<th><?php esc_html_e( 'IP Address', 'siteshield' ); ?></th>
							<th><?php esc_html_e( 'Regra', 'siteshield' ); ?></th>
							<th><?php esc_html_e( 'Severidade', 'siteshield' ); ?></th>
							<th><?php esc_html_e( 'Ação', 'siteshield' ); ?></th>
						</tr>
					</thead>
					<tbody>
						<?php foreach ( $data['events'] as $ev ) : ?>
							<tr>
								<td><?php echo esc_html( $ev->created_at ); ?></td>
								<td><?php echo esc_html( $ev->ip_address ); ?></td>
								<td><?php echo esc_html( $ev->rule_triggered ); ?></td>
								<td><?php echo esc_html( strtoupper( $ev->severity ) ); ?></td>
								<td><?php echo esc_html( $ev->action_taken ); ?></td>
							</tr>
						<?php endforeach; ?>
					</tbody>
				</table>
			<?php endif; ?>

			<div style="margin-top: 40px; padding-top: 20px; border-top: 1px solid #E2E8F0; font-size: 11px; color: #94A3B8; text-align: center;">
				<?php esc_html_e( 'Documento gerado automaticamente pelo SiteShield Security Enterprise Suite. Confidencial.', 'siteshield' ); ?>
			</div>
		</body>
		</html>
		<?php
		exit;
	}
}
