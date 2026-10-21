<?php
/**
 * View do Centro de Eventos de Segurança (Security Event Center)
 *
 * @package Mauriciokaleya by SiteShield
 * @author Mauriciokaleya
 * @subpackage Admin\Views
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

$severity_filter = isset( $_GET['sev'] ) ? sanitize_key( wp_unslash( $_GET['sev'] ) ) : 'ALL';
$events = \SiteShield\Events\Event_Center::get_events( $severity_filter, 100, 0 );
$export_csv_url = wp_nonce_url( admin_url( 'admin.php?siteshield_action=export_events_csv' ), 'siteshield_export_nonce' );
?>
<div class="ss-content-header">
	<div class="content-header-title">
		<h2><?php esc_html_e( 'Security Event Center & Severity Engine', 'siteshield' ); ?></h2>
		<p><?php esc_html_e( 'Registo cronológico e imutável de eventos classificados por severidade de risco.', 'siteshield' ); ?></p>
	</div>
	<div class="content-header-actions">
		<a href="<?php echo esc_url( $export_csv_url ); ?>" class="ss-btn ss-btn-secondary">
			<svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M21 15v4a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2v-4"></path><polyline points="7 10 12 15 17 10"></polyline><line x1="12" y1="15" x2="12" y2="3"></line></svg>
			<span><?php esc_html_e( 'Export Events (CSV)', 'siteshield' ); ?></span>
		</a>
	</div>
</div>

<!-- FILTRO POR SEVERIDADE (CRITICAL, HIGH, MEDIUM, LOW, INFO) -->
<div class="severity-filter-row" style="margin-bottom: 20px;">
	<div class="period-filter-group">
		<a href="?page=siteshield-dashboard&tab=security-events&sev=ALL" class="filter-btn <?php echo 'ALL' === $severity_filter ? 'active' : ''; ?>">TODOS</a>
		<a href="?page=siteshield-dashboard&tab=security-events&sev=CRITICAL" class="filter-btn <?php echo 'CRITICAL' === $severity_filter ? 'active' : ''; ?>">CRITICAL</a>
		<a href="?page=siteshield-dashboard&tab=security-events&sev=HIGH" class="filter-btn <?php echo 'HIGH' === $severity_filter ? 'active' : ''; ?>">HIGH</a>
		<a href="?page=siteshield-dashboard&tab=security-events&sev=MEDIUM" class="filter-btn <?php echo 'MEDIUM' === $severity_filter ? 'active' : ''; ?>">MEDIUM</a>
		<a href="?page=siteshield-dashboard&tab=security-events&sev=LOW" class="filter-btn <?php echo 'LOW' === $severity_filter ? 'active' : ''; ?>">LOW</a>
		<a href="?page=siteshield-dashboard&tab=security-events&sev=INFO" class="filter-btn <?php echo 'INFO' === $severity_filter ? 'active' : ''; ?>">INFO</a>
	</div>
</div>

<!-- TABELA DE EVENTOS -->
<div class="ss-card">
	<div class="card-header">
		<h3><?php esc_html_e( 'Security Events Log', 'siteshield' ); ?> (<?php echo count( $events ); ?>)</h3>
	</div>
	<div class="card-body">
		<?php if ( empty( $events ) ) : ?>
			<p class="empty-text"><?php esc_html_e( 'Nenhum evento registrado nesta categoria de severidade.', 'siteshield' ); ?></p>
		<?php else : ?>
			<div class="table-responsive">
				<table class="ss-table">
					<thead>
						<tr>
							<th><?php esc_html_e( 'Data/Hora', 'siteshield' ); ?></th>
							<th><?php esc_html_e( 'Severidade', 'siteshield' ); ?></th>
							<th><?php esc_html_e( 'Tipo de Evento', 'siteshield' ); ?></th>
							<th><?php esc_html_e( 'Utilizador', 'siteshield' ); ?></th>
							<th><?php esc_html_e( 'IP Address', 'siteshield' ); ?></th>
							<th><?php esc_html_e( 'Detalhes', 'siteshield' ); ?></th>
						</tr>
					</thead>
					<tbody>
						<?php foreach ( $events as $ev ) : ?>
							<?php
							$sev_label = strtoupper( $ev->object_type );
							$sev_badge = 'ss-badge-neutral';
							if ( in_array( $sev_label, array( 'CRITICAL', 'HIGH' ), true ) ) {
								$sev_badge = 'ss-badge-primary';
							}
							?>
							<tr>
								<td><?php echo esc_html( date( 'd/m/Y H:i', strtotime( $ev->created_at ) ) ); ?></td>
								<td><span class="ss-badge <?php echo esc_attr( $sev_badge ); ?>"><?php echo esc_html( $sev_label ); ?></span></td>
								<td><code><?php echo esc_html( $ev->action_type ); ?></code></td>
								<td><strong><?php echo esc_html( $ev->user_login ); ?></strong></td>
								<td><code><?php echo esc_html( $ev->ip_address ); ?></code></td>
								<td><?php echo esc_html( $ev->details ); ?></td>
							</tr>
						<?php endforeach; ?>
					</tbody>
				</table>
			</div>
		<?php endif; ?>
	</div>
</div>
