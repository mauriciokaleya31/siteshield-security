<?php
/**
 * View do Log de Atividades e Auditoria do SiteShield
 *
 * @package Mauriciokaleya by SiteShield
 * @author Mauriciokaleya
 * @subpackage Admin\Views
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

$logs = \SiteShield\Audit\Audit_Logger::get_logs( 100, 0 );
?>
<div class="ss-content-header">
	<div class="content-header-title">
		<h2><?php esc_html_e( 'Activity & Audit Logs', 'siteshield' ); ?></h2>
		<p><?php esc_html_e( 'Histórico cronológico e imutável de ações administrativas, autenticação e eventos do sistema.', 'siteshield' ); ?></p>
	</div>
</div>

<div class="ss-card">
	<div class="card-header">
		<h3><?php esc_html_e( 'Audit Trail Records', 'siteshield' ); ?> (<?php echo count( $logs ); ?>)</h3>
	</div>
	<div class="card-body">
		<?php if ( empty( $logs ) ) : ?>
			<p class="empty-text"><?php esc_html_e( 'Nenhum registro de auditoria disponível.', 'siteshield' ); ?></p>
		<?php else : ?>
			<div class="table-responsive">
				<table class="ss-table">
					<thead>
						<tr>
							<th><?php esc_html_e( 'Data/Hora', 'siteshield' ); ?></th>
							<th><?php esc_html_e( 'User', 'siteshield' ); ?></th>
							<th><?php esc_html_e( 'Action', 'siteshield' ); ?></th>
							<th><?php esc_html_e( 'Details', 'siteshield' ); ?></th>
							<th><?php esc_html_e( 'IP Address', 'siteshield' ); ?></th>
						</tr>
					</thead>
					<tbody>
						<?php foreach ( $logs as $log ) : ?>
							<tr>
								<td><?php echo esc_html( date( 'd/m H:i', strtotime( $log->created_at ) ) ); ?></td>
								<td><strong><?php echo esc_html( $log->user_login ); ?></strong></td>
								<td><code><?php echo esc_html( $log->action_type ); ?></code></td>
								<td><?php echo esc_html( $log->details ); ?></td>
								<td><code><?php echo esc_html( $log->ip_address ); ?></code></td>
							</tr>
						<?php endforeach; ?>
					</tbody>
				</table>
			</div>
		<?php endif; ?>
	</div>
</div>
