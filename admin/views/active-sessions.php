<?php
/**
 * View de Sessões Administrativas Ativas e Logout Remoto
 *
 * @package Mauriciokaleya by SiteShield
 * @author Mauriciokaleya
 * @subpackage Admin\Views
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

$sessions = \SiteShield\Login\Session_Manager::get_active_admin_sessions();
?>
<div class="ss-content-header">
	<div class="content-header-title">
		<h2><?php esc_html_e( 'Active Administrator Sessions', 'siteshield' ); ?></h2>
		<p><?php esc_html_e( 'Monitorização em tempo real de contas de administrador conectadas ao painel.', 'siteshield' ); ?></p>
	</div>
	<div class="content-header-actions">
		<button type="button" class="ss-btn ss-btn-danger" id="btn-destroy-other-sessions">
			<svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M18.36 6.64a9 9 0 1 1-12.73 0"></path><line x1="12" y1="2" x2="12" y2="12"></line></svg>
			<span><?php esc_html_e( 'Encerrar Outras Sessões', 'siteshield' ); ?></span>
		</button>
	</div>
</div>

<div class="ss-card">
	<div class="card-header">
		<h3><?php esc_html_e( 'Sessões Ativas no Sistema', 'siteshield' ); ?> (<?php echo count( $sessions ); ?>)</h3>
	</div>
	<div class="card-body">
		<?php if ( empty( $sessions ) ) : ?>
			<p class="empty-text"><?php esc_html_e( 'Nenhuma sessão ativa encontrada.', 'siteshield' ); ?></p>
		<?php else : ?>
			<div class="table-responsive">
				<table class="ss-table">
					<thead>
						<tr>
							<th><?php esc_html_e( 'Utilizador', 'siteshield' ); ?></th>
							<th><?php esc_html_e( 'Endereço IP', 'siteshield' ); ?></th>
							<th><?php esc_html_e( 'Navegador', 'siteshield' ); ?></th>
							<th><?php esc_html_e( 'Sistema Operativo', 'siteshield' ); ?></th>
							<th><?php esc_html_e( 'Data de Login', 'siteshield' ); ?></th>
							<th><?php esc_html_e( 'Status', 'siteshield' ); ?></th>
						</tr>
					</thead>
					<tbody>
						<?php foreach ( $sessions as $sess ) : ?>
							<tr>
								<td><strong><?php echo esc_html( $sess['user_login'] ); ?></strong></td>
								<td><code><?php echo esc_html( $sess['ip_address'] ); ?></code></td>
								<td><?php echo esc_html( $sess['browser'] ); ?></td>
								<td><?php echo esc_html( $sess['os'] ); ?></td>
								<td><?php echo esc_html( $sess['login_time'] ); ?></td>
								<td>
									<?php if ( $sess['is_current'] ) : ?>
										<span class="ss-badge ss-badge-primary"><?php esc_html_e( 'Sessão Atual', 'siteshield' ); ?></span>
									<?php else : ?>
										<span class="ss-badge ss-badge-neutral"><?php esc_html_e( 'Remota', 'siteshield' ); ?></span>
									<?php endif; ?>
								</td>
							</tr>
						<?php endforeach; ?>
					</tbody>
				</table>
			</div>
		<?php endif; ?>
	</div>
</div>
