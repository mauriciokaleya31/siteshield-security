<?php
/**
 * View do Centro de Saúde do Sistema (System Health Center)
 * Em conformidade com o Princípio "No False Security"
 *
 * @package Mauriciokaleya by SiteShield
 * @author Mauriciokaleya
 * @subpackage Admin\Views
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

$health = \SiteShield\Health\System_Health::get_system_health();
?>
<div class="ss-content-header">
	<div class="content-header-title">
		<h2><?php esc_html_e( 'System Health Center', 'siteshield' ); ?></h2>
		<p><?php esc_html_e( 'Diagnóstico do estado real e operacional de todos os 15 componentes do ecossistema de proteção.', 'siteshield' ); ?></p>
	</div>
	<div class="content-header-actions">
		<button type="button" class="ss-btn ss-btn-primary" id="btn-run-self-test">
			<svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><polyline points="22 12 18 12 15 21 9 3 6 12 2 12"></polyline></svg>
			<span><?php esc_html_e( 'Run Protection Test', 'siteshield' ); ?></span>
		</button>
	</div>
</div>

<!-- GRADE DOS 15 COMPONENTES DO SYSTEM HEALTH -->
<div class="ss-grid-2">
	<?php foreach ( $health as $key => $item ) : ?>
		<?php
		$status_class = 'ss-badge-neutral';
		if ( in_array( $item['status'], array( 'PASS', 'ACTIVE', 'CONNECTED', 'PRO', 'BUSINESS', 'AGENCY' ), true ) ) {
			$status_class = 'ss-badge-primary';
		} elseif ( in_array( $item['status'], array( 'WARNING', 'LEARNING' ), true ) ) {
			$status_class = 'ss-badge-warning';
		} elseif ( in_array( $item['status'], array( 'FAIL', 'FAILED' ), true ) ) {
			$status_class = 'ss-badge-danger';
		}
		?>
		<div class="ss-card">
			<div class="card-header" style="display: flex; justify-content: space-between; align-items: center; padding: 14px 18px;">
				<strong style="font-size: 14px; color: #0F172A;"><?php echo esc_html( $item['name'] ); ?></strong>
				<span class="ss-badge <?php echo esc_attr( $status_class ); ?>">
					<?php echo esc_html( $item['status'] ); ?>
				</span>
			</div>
			<div class="card-body" style="padding: 14px 18px;">
				<p style="margin: 0; font-size: 12px; color: #64748B;"><?php echo esc_html( $item['details'] ); ?></p>
			</div>
		</div>
	<?php endforeach; ?>
</div>

<!-- ÁREA DE RESULTADOS DO AUTOTESTE EM TEMPO REAL -->
<div class="ss-card" id="self-test-results-box" style="display: none; margin-top: 25px;">
	<div class="card-header">
		<h3><?php esc_html_e( 'Protection Real-Time Verification Results', 'siteshield' ); ?></h3>
	</div>
	<div class="card-body" id="self-test-results-body">
		<!-- Preenchido via AJAX -->
	</div>
</div>
