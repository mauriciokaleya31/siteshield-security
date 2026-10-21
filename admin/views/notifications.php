<?php
/**
 * View de Notificações, Comunicados Cloud e Alertas
 *
 * @package SiteShield Security
 * @author Mauriciokaleya
 */

use SiteShield\Cloud\Cloud_Client;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

$cloud_notifications = get_option( 'siteshield_cloud_notifications', array() );
?>
<div class="ss-tab-container">
	<!-- 1. COMUNICADOS & NOTIFICAÇÕES DO SITESHIELD CLOUD -->
	<div class="ss-panel" style="margin-bottom: 25px; border-left: 4px solid #0066FF;">
		<div class="panel-header" style="display: flex; justify-content: space-between; align-items: center;">
			<h3 style="margin: 0;">📢 <?php esc_html_e( 'Comunicados & Alertas do SiteShield Cloud', 'siteshield' ); ?></h3>
			<span style="font-size: 0.8rem; color: #64748B;">Sincronizado via Heartbeat</span>
		</div>
		<div class="panel-body">
			<?php if ( empty( $cloud_notifications ) || ! is_array( $cloud_notifications ) ) : ?>
				<p style="color: #64748B; margin: 10px 0;">
					<?php esc_html_e( 'Nenhuma notificação pendente no momento. O seu website está sincronizado e operando normalmente.', 'siteshield' ); ?>
				</p>
			<?php else : ?>
				<div style="display: flex; flex-direction: column; gap: 12px; margin-top: 10px;">
					<?php foreach ( $cloud_notifications as $notif ) : ?>
						<?php
						$is_read = ! empty( $notif['is_read'] );
						$type = isset( $notif['type'] ) ? strtolower( $notif['type'] ) : 'info';
						$badge_style = 'background: #EFF6FF; color: #1E40AF;';
						if ( 'alert' === $type || 'security' === $type || 'expiration' === $type ) {
							$badge_style = 'background: #FEF2F2; color: #991B1B; border: 1px solid #FECACA;';
						} elseif ( 'warning' === $type || 'maintenance' === $type ) {
							$badge_style = 'background: #FFFBEB; color: #92400E; border: 1px solid #FDE68A;';
						}
						?>
						<div class="siteshield-notif-card" id="notif-item-<?php echo esc_attr( $notif['id'] ); ?>" style="display: flex; justify-content: space-between; align-items: flex-start; gap: 15px; padding: 16px 20px; border-radius: 8px; border: 1px solid #E2E8F0; <?php echo $is_read ? 'opacity: 0.65; background: #F8FAFC;' : 'background: #FFFFFF; box-shadow: 0 2px 4px rgba(0,0,0,0.04);'; ?>">
							<div style="flex: 1;">
								<div style="display: flex; align-items: center; gap: 10px; margin-bottom: 6px;">
									<span class="badge" style="font-size: 0.72rem; font-weight: 700; padding: 2px 8px; border-radius: 12px; text-transform: uppercase; <?php echo esc_attr( $badge_style ); ?>">
										<?php echo esc_html( strtoupper( $type ) ); ?>
									</span>
									<strong style="color: #0F172A; font-size: 0.95rem;"><?php echo esc_html( $notif['title'] ); ?></strong>
									<span style="color: #94A3B8; font-size: 0.75rem; margin-left: auto;">
										<?php echo esc_html( isset( $notif['created_at'] ) ? date_i18n( 'd/m/Y H:i', strtotime( $notif['created_at'] ) ) : '' ); ?>
									</span>
								</div>
								<p style="color: #475569; font-size: 0.88rem; line-height: 1.5; margin: 0;">
									<?php echo nl2br( esc_html( $notif['message'] ) ); ?>
								</p>
							</div>

							<?php if ( ! $is_read ) : ?>
								<button type="button" class="button button-small btn-mark-notif-read" data-notif-id="<?php echo esc_attr( $notif['id'] ); ?>" style="flex-shrink: 0;">
									<?php esc_html_e( 'Marcar como Lida', 'siteshield' ); ?>
								</button>
							<?php endif; ?>
						</div>
					<?php endforeach; ?>
				</div>
			<?php endif; ?>
		</div>
	</div>

	<!-- 2. CONFIGURAÇÕES DE ALERTAS LOCAIS POR E-MAIL E WEBHOOK -->
	<form id="form-notifications-settings" class="ss-settings-form">
		<?php wp_nonce_field( 'siteshield_admin_nonce', 'nonce' ); ?>

		<!-- ALERTAS POR E-MAIL -->
		<div class="ss-panel">
			<div class="panel-header">
				<h3>📬 <?php esc_html_e( 'Alertas Imediatos por E-mail', 'siteshield' ); ?></h3>
			</div>
			<div class="panel-body">
				<label class="ss-toggle-card" style="margin-bottom: 20px;">
					<input type="checkbox" name="notify_email_enabled" value="1" <?php checked( $settings['notify_email_enabled'] ); ?> />
					<span class="card-info">
						<strong><?php esc_html_e( 'Ativar Notificações de Segurança por E-mail', 'siteshield' ); ?></strong>
						<small><?php esc_html_e( 'Envia e-mails imediatos em caso de detecção de malware crítico ou ataques de força bruta.', 'siteshield' ); ?></small>
					</span>
				</label>

				<div class="grid-2-cols">
					<div class="ss-input-group">
						<label><?php esc_html_e( 'E-mail de Destino:', 'siteshield' ); ?></label>
						<input type="email" name="admin_email" value="<?php echo esc_attr( $settings['admin_email'] ); ?>" required />
					</div>
					<label class="ss-toggle-card" style="align-self: flex-end;">
						<input type="checkbox" name="notify_email_critical_only" value="1" <?php checked( $settings['notify_email_critical_only'] ); ?> />
						<span class="card-info">
							<strong><?php esc_html_e( 'Apenas Ameaças Críticas (Alta Severidade)', 'siteshield' ); ?></strong>
							<small><?php esc_html_e( 'Reduz a quantidade de e-mails recebidos.', 'siteshield' ); ?></small>
						</span>
					</label>
				</div>
			</div>
		</div>

		<!-- ALERTAS POR WEBHOOK -->
		<div class="ss-panel" style="margin-top: 25px;">
			<div class="panel-header">
				<h3>🔗 <?php esc_html_e( 'Integração por Webhook (Discord / Slack / Telegram)', 'siteshield' ); ?></h3>
			</div>
			<div class="panel-body">
				<label class="ss-toggle-card" style="margin-bottom: 20px;">
					<input type="checkbox" name="notify_webhook_enabled" value="1" <?php checked( $settings['notify_webhook_enabled'] ); ?> />
					<span class="card-info">
						<strong><?php esc_html_e( 'Disparar Webhook em Eventos de Segurança', 'siteshield' ); ?></strong>
						<small><?php esc_html_e( 'Envia payload JSON via POST para sua URL de integração.', 'siteshield' ); ?></small>
					</span>
				</label>

				<div class="ss-input-group">
					<label><?php esc_html_e( 'URL do Webhook:', 'siteshield' ); ?></label>
					<input type="url" name="notify_webhook_url" value="<?php echo esc_attr( $settings['notify_webhook_url'] ); ?>" placeholder="https://discord.com/api/webhooks/..." />
				</div>
			</div>
		</div>

		<div class="ss-form-footer">
			<button type="submit" class="ss-btn ss-btn-primary"><?php esc_html_e( 'Salvar Configurações de Notificações', 'siteshield' ); ?></button>
		</div>
	</form>
</div>

<script>
jQuery(document).ready(function($){
	$('.btn-mark-notif-read').on('click', function(e){
		e.preventDefault();
		var btn = $(this);
		var notifId = btn.data('notif-id');
		btn.prop('disabled', true).text('...');

		$.post(SiteShieldAdmin.ajaxUrl, {
			action: 'siteshield_mark_notification_read',
			nonce: SiteShieldAdmin.nonce,
			notif_id: notifId
		}, function(res){
			if (res.success) {
				$('#notif-item-' + notifId).css('opacity', '0.5');
				btn.remove();
			}
		});
	});
});
</script>
