<?php
/**
 * View do Monitor de Integridade de Arquivos
 *
 * @package Mauriciokaleya by SiteShield
 * @author Mauriciokaleya
 * @subpackage Admin\Views
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

$last_results = get_option( 'siteshield_last_integrity_results', array() );
$baseline_time = get_option( 'siteshield_integrity_baseline_time', 0 );
$baseline_count= get_option( 'siteshield_integrity_baseline_count', 0 );
?>
<div class="ss-content-header">
	<div class="content-header-title">
		<h2><?php esc_html_e( 'File Integrity Monitoring (SHA-256)', 'siteshield' ); ?></h2>
		<p><?php esc_html_e( 'Snapshot criptográfico dos arquivos críticos do WordPress para detectar modificações não autorizadas.', 'siteshield' ); ?></p>
	</div>
</div>

<div class="ss-card">
	<div class="card-header">
		<h3><?php esc_html_e( 'Baseline Snapshot Status', 'siteshield' ); ?></h3>
	</div>
	<div class="card-body">
		<div class="integrity-status-card">
			<div class="state-item">
				<span class="state-label"><?php esc_html_e( 'Monitored Files in Baseline:', 'siteshield' ); ?></span>
				<strong class="state-value"><?php echo esc_html( $baseline_count ); ?></strong>
			</div>
			<div class="state-item">
				<span class="state-label"><?php esc_html_e( 'Last Baseline Created:', 'siteshield' ); ?></span>
				<strong class="state-value"><?php echo $baseline_time ? esc_html( date( 'd/m/Y H:i', $baseline_time ) ) : esc_html__( 'None', 'siteshield' ); ?></strong>
			</div>
		</div>

		<div class="scanner-actions-row" style="margin-top: 20px;">
			<button type="button" class="ss-btn ss-btn-primary" id="btn-check-integrity">
				<svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><circle cx="11" cy="11" r="8"></circle><line x1="21" y1="21" x2="16.65" y2="16.65"></line></svg>
				<span><?php esc_html_e( 'Check Integrity Now', 'siteshield' ); ?></span>
			</button>
			<button type="button" class="ss-btn ss-btn-secondary" id="btn-build-baseline">
				<svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M23 19a2 2 0 0 1-2 2H3a2 2 0 0 1-2-2V8a2 2 0 0 1 2-2h4l2-3h6l2 3h4a2 2 0 0 1 2 2z"></path><circle cx="12" cy="13" r="4"></circle></svg>
				<span><?php esc_html_e( 'Recreate Baseline (Snapshot)', 'siteshield' ); ?></span>
			</button>
		</div>
	</div>
</div>

<!-- RESULTADOS DA AUDITORIA -->
<div class="ss-card" style="margin-top: 25px;">
	<div class="card-header">
		<h3><?php esc_html_e( 'Integrity Audit Results', 'siteshield' ); ?></h3>
	</div>
	<div class="card-body">
		<?php if ( empty( $last_results ) || empty( $last_results['total'] ) ) : ?>
			<div class="empty-statee-card text-center">
				<svg width="32" height="32" viewBox="0 0 24 24" fill="none" stroke="#0066FF" stroke-width="2" style="margin: 0 auto 10px;"><polyline points="20 6 9 17 4 12"></polyline></svg>
				<h4><?php esc_html_e( 'All Files are Clean & Unmodified', 'siteshield' ); ?></h4>
				<p><?php esc_html_e( 'Nenhuma modificação não autorizada detectada nos arquivos auditados.', 'siteshield' ); ?></p>
			</div>
		<?php else : ?>
			<div class="table-responsive">
				<table class="ss-table">
					<thead>
						<tr>
							<th><?php esc_html_e( 'Status', 'siteshield' ); ?></th>
							<th><?php esc_html_e( 'Arquivo', 'siteshield' ); ?></th>
							<th><?php esc_html_e( 'Detalhes', 'siteshield' ); ?></th>
						</tr>
					</thead>
					<tbody>
						<?php if ( ! empty( $last_results['modified'] ) ) : ?>
							<?php foreach ( $last_results['modified'] as $item ) : ?>
								<tr>
									<td><span class="ss-badge ss-badge-primary"><?php esc_html_e( 'Modified', 'siteshield' ); ?></span></td>
									<td><code><?php echo esc_html( $item['file'] ); ?></code></td>
									<td><small>Alterado em: <?php echo esc_html( $item['modified_at'] ); ?></small></td>
								</tr>
							<?php endforeach; ?>
						<?php endif; ?>

						<?php if ( ! empty( $last_results['created'] ) ) : ?>
							<?php foreach ( $last_results['created'] as $item ) : ?>
								<tr>
									<td><span class="ss-badge ss-badge-neutral"><?php esc_html_e( 'New File', 'siteshield' ); ?></span></td>
									<td><code><?php echo esc_html( $item['file'] ); ?></code></td>
									<td><small>Criado em: <?php echo esc_html( $item['created_at'] ); ?></small></td>
								</tr>
							<?php endforeach; ?>
						<?php endif; ?>
					</tbody>
				</table>
			</div>
		<?php endif; ?>
	</div>
</div>
