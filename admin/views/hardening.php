<?php
/**
 * View de Hardening e Blindagem do WordPress
 *
 * @package Mauriciokaleya by SiteShield
 * @author Mauriciokaleya
 * @subpackage Admin\Views
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

$checklist = \SiteShield\Hardening\WP_Hardening::get_hardening_checklist();
?>
<div class="ss-content-header">
	<div class="content-header-title">
		<h2><?php esc_html_e( 'WordPress Hardening & System Shielding', 'siteshield' ); ?></h2>
		<p><?php esc_html_e( 'Blindagem recomendada contra vetores de exploração e execução de código não autorizado.', 'siteshield' ); ?></p>
	</div>
</div>

<div class="ss-grid-2">
	<?php foreach ( $checklist as $key => $item ) : ?>
		<div class="ss-card">
			<div class="card-header" style="display: flex; justify-content: space-between; align-items: center;">
				<h3 style="font-size: 14px;"><?php echo esc_html( $item['title'] ); ?></h3>
				<span class="ss-badge <?php echo 'PASS' === $item['status'] ? 'ss-badge-primary' : 'ss-badge-warning'; ?>">
					<?php echo esc_html( $item['status'] ); ?>
				</span>
			</div>
			<div class="card-body">
				<p style="font-size: 13px; color: #475569; margin: 0 0 10px;"><?php echo esc_html( $item['description'] ); ?></p>
				<div style="font-size: 12px; color: #64748B; margin-bottom: 12px;">
					<strong><?php esc_html_e( 'Risco Mitigado:', 'siteshield' ); ?></strong> <?php echo esc_html( $item['risk'] ); ?>
				</div>

				<?php if ( 'uploads_execution' === $key && ! $item['applied'] ) : ?>
					<button type="button" class="ss-btn ss-btn-sm ss-btn-primary" id="btn-protect-uploads">
						<?php esc_html_e( 'Aplicar Proteção em Uploads (.htaccess)', 'siteshield' ); ?>
					</button>
				<?php elseif ( isset( $item['recommend'] ) && ! $item['applied'] ) : ?>
					<div class="ss-alert ss-alert-warning" style="margin: 0; padding: 8px 12px; font-size: 11px;">
						<code><?php echo esc_html( $item['recommend'] ); ?></code>
					</div>
				<?php endif; ?>
			</div>
		</div>
	<?php endforeach; ?>
</div>
