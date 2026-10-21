<?php
/**
 * View de Gestão de IPs (Allowlist & Blocklist) do SiteShield
 *
 * @package Mauriciokaleya by SiteShield
 * @author Mauriciokaleya
 * @subpackage Admin\Views
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

$ips = \SiteShield\Firewall\IP_Manager::get_all_ips();
?>
<div class="ss-tab-container">
	<!-- ADICIONAR IP -->
	<div class="ss-panel">
		<div class="panel-header">
			<h3>🎯 <?php esc_html_e( 'Adicionar ou Bloquear Endereço IP', 'siteshield' ); ?></h3>
		</div>
		<div class="panel-body">
			<form id="form-add-ip" class="grid-3-cols" style="align-items: flex-end;">
				<?php wp_nonce_field( 'siteshield_admin_nonce', 'nonce' ); ?>
				<div class="ss-input-group">
					<label><?php esc_html_e( 'Endereço IP (IPv4 ou IPv6):', 'siteshield' ); ?></label>
					<input type="text" id="input_ip_address" placeholder="Ex: 192.168.1.100" required />
				</div>
				<div class="ss-input-group">
					<label><?php esc_html_e( 'Motivo:', 'siteshield' ); ?></label>
					<input type="text" id="input_ip_reason" placeholder="Ex: Tentativa suspeita" required />
				</div>
				<div class="ss-input-group" style="display: flex; gap: 8px;">
					<button type="button" class="ss-btn ss-btn-danger" id="btn-block-ip">🛑 <?php esc_html_e( 'Bloquear IP', 'siteshield' ); ?></button>
					<button type="button" class="ss-btn ss-btn-secondary" id="btn-allow-ip">✓ <?php esc_html_e( 'Permitir (Allowlist)', 'siteshield' ); ?></button>
				</div>
			</form>
		</div>
	</div>

	<!-- TABELA DE IPS CADASTRADOS -->
	<div class="ss-panel" style="margin-top: 25px;">
		<div class="panel-header">
			<h3>📋 <?php esc_html_e( 'Lista de Endereços IP Cadastrados', 'siteshield' ); ?> (<?php echo count( $ips ); ?>)</h3>
		</div>
		<div class="panel-body">
			<?php if ( empty( $ips ) ) : ?>
				<p class="empty-msg"><?php esc_html_e( 'Nenhum IP bloqueado ou na lista permitida.', 'siteshield' ); ?></p>
			<?php else : ?>
				<div class="table-responsive">
					<table class="ss-table">
						<thead>
							<tr>
								<th><?php esc_html_e( 'IP', 'siteshield' ); ?></th>
								<th><?php esc_html_e( 'Tipo', 'siteshield' ); ?></th>
								<th><?php esc_html_e( 'Motivo', 'siteshield' ); ?></th>
								<th><?php esc_html_e( 'Tentativas', 'siteshield' ); ?></th>
								<th><?php esc_html_e( 'Expira em', 'siteshield' ); ?></th>
								<th><?php esc_html_e( 'Ações', 'siteshield' ); ?></th>
							</tr>
						</thead>
						<tbody>
							<?php foreach ( $ips as $row ) : ?>
								<tr>
									<td><code><?php echo esc_html( $row->ip_address ); ?></code></td>
									<td>
										<span class="badge badge-<?php echo 'allowed' === $row->list_type ? 'success' : 'danger'; ?>">
											<?php echo 'allowed' === $row->list_type ? esc_html__( 'Permitido (Allow)', 'siteshield' ) : esc_html__( 'Bloqueado', 'siteshield' ); ?>
										</span>
									</td>
									<td><?php echo esc_html( $row->reason ); ?></td>
									<td><?php echo esc_html( $row->attempts ); ?></td>
									<td><?php echo $row->is_permanent ? esc_html__( 'Permanente', 'siteshield' ) : esc_html( $row->expires_at ); ?></td>
									<td>
										<button type="button" class="ss-btn ss-btn-sm ss-btn-outline-danger btn-unblock-ip" data-ip="<?php echo esc_attr( $row->ip_address ); ?>">
											✕ <?php esc_html_e( 'Remover', 'siteshield' ); ?>
										</button>
									</td>
								</tr>
							<?php endforeach; ?>
						</tbody>
					</table>
				</div>
			<?php endif; ?>
		</div>
	</div>
</div>
