<?php
/**
 * View de Proteção Global de Conteúdo, Anti-Clone e Anti-Hotlink
 *
 * @package Mauriciokaleya by SiteShield
 * @author Mauriciokaleya
 * @subpackage Admin\Views
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

$is_active = ! empty( $settings['content_protection_enabled'] );
?>
<div class="ss-content-header">
	<div class="content-header-title">
		<h2><?php esc_html_e( 'Proteção Global de Conteúdo & Anti-Clone', 'siteshield' ); ?></h2>
		<p><?php esc_html_e( 'Proteção universal e automática para todas as páginas públicas do WordPress contra cópia, atalhos de desenvolvedor e roubo de mídia.', 'siteshield' ); ?></p>
	</div>
</div>

<form id="form-content-protection" class="ss-settings-form">
	<?php wp_nonce_field( 'siteshield_admin_nonce', 'nonce' ); ?>

	<!-- 1. INTERRUPTOR MESTRE GLOBAL ANTI-CLONE -->
	<div class="ss-card" style="margin-bottom: 25px; border-left: 4px solid <?php echo $is_active ? '#0066FF' : '#94A3B8'; ?>;">
		<div class="card-header" style="display: flex; justify-content: space-between; align-items: center; flex-wrap: wrap; gap: 15px;">
			<div>
				<h3 style="font-size: 16px; margin: 0; color: #0F172A;"><?php esc_html_e( 'Estado da Proteção Global Anti-Clone', 'siteshield' ); ?></h3>
				<p style="margin: 4px 0 0; font-size: 13px; color: #64748B;">
					<?php esc_html_e( 'Aplica automaticamente a blindagem em 100% das páginas públicas do site (Página Inicial, Artigos, WooCommerce, Elementor, Landing Pages, etc.).', 'siteshield' ); ?>
				</p>
			</div>
			<span class="ss-badge <?php echo $is_active ? 'ss-badge-primary' : 'ss-badge-neutral'; ?>" style="font-size: 13px; padding: 6px 14px;">
				<?php echo $is_active ? esc_html__( 'ANTI-CLONE ATIVADO', 'siteshield' ) : esc_html__( 'ANTI-CLONE DESATIVADO', 'siteshield' ); ?>
			</span>
		</div>
		<div class="card-body">
			<label class="ss-toggle-card" style="margin: 0; background: #F8FAFC; border: 1px solid #E2E8F0; padding: 16px;">
				<input type="checkbox" name="content_protection_enabled" id="master-toggle-anti-clone" value="1" <?php checked( $is_active ); ?> />
				<span class="card-info">
					<strong style="font-size: 15px; color: #0F172A;"><?php esc_html_e( 'Ativar Proteção Global Anti-Clone em Todo o Frontend', 'siteshield' ); ?></strong>
					<small style="color: #475569; font-size: 13px; display: block; margin-top: 4px;">
						<?php esc_html_e( 'Quando ativado, os visitantes públicos recebem a proteção automaticamente em todas as URLs. Administradores logados continuam com acesso livre para inspecionar, copiar e editar normalmente.', 'siteshield' ); ?>
					</small>
				</span>
			</label>
		</div>
	</div>

	<!-- 2. REGRAS DETALHADAS DE DISSUASÃO E BLOQUEIO -->
	<div class="ss-card">
		<div class="card-header">
			<h3><?php esc_html_e( 'Mecanismos de Bloqueio e Dissuasão', 'siteshield' ); ?></h3>
		</div>
		<div class="card-body">
			<div class="ss-grid-2">
				<label class="ss-toggle-card">
					<input type="checkbox" name="protect_disable_right_click" value="1" <?php checked( ! empty( $settings['protect_disable_right_click'] ) ); ?> />
					<span class="card-info">
						<strong><?php esc_html_e( 'Bloquear Botão Direito (Menu de Contexto)', 'siteshield' ); ?></strong>
						<small><?php esc_html_e( 'Impede a abertura do menu de contexto e opções de cópia para visitantes.', 'siteshield' ); ?></small>
					</span>
				</label>

				<label class="ss-toggle-card">
					<input type="checkbox" name="protect_disable_devtools" value="1" <?php checked( ! empty( $settings['protect_disable_devtools'] ) ); ?> />
					<span class="card-info">
						<strong><?php esc_html_e( 'Bloquear Atalhos DevTools (F12, Ctrl+U, Ctrl+Shift+I/J/C, Ctrl+S)', 'siteshield' ); ?></strong>
						<small><?php esc_html_e( 'Bloqueia atalhos de teclado de inspeção e visualização do código-fonte.', 'siteshield' ); ?></small>
					</span>
				</label>

				<label class="ss-toggle-card">
					<input type="checkbox" name="protect_disable_copy_select" value="1" <?php checked( ! empty( $settings['protect_disable_copy_select'] ) ); ?> />
					<span class="card-info">
						<strong><?php esc_html_e( 'Bloquear Seleção de Texto e Cópia (Ctrl+C, Ctrl+A)', 'siteshield' ); ?></strong>
						<small><?php esc_html_e( 'Aplica user-select: none e bloqueia eventos de seleção e transferência.', 'siteshield' ); ?></small>
					</span>
				</label>

				<label class="ss-toggle-card">
					<input type="checkbox" name="protect_disable_drag_images" value="1" <?php checked( ! empty( $settings['protect_disable_drag_images'] ) ); ?> />
					<span class="card-info">
						<strong><?php esc_html_e( 'Bloquear Arrasto de Imagens & Mídias (Drag & Drop)', 'siteshield' ); ?></strong>
						<small><?php esc_html_e( 'Impede que visitantes arrastem fotos, vídeos e artes para fora da página.', 'siteshield' ); ?></small>
					</span>
				</label>

				<label class="ss-toggle-card">
					<input type="checkbox" name="protect_frame_busting" value="1" <?php checked( ! empty( $settings['protect_frame_busting'] ) ); ?> />
					<span class="card-info">
						<strong><?php esc_html_e( 'Anti-Frame Busting & Anti-Clickjacking', 'siteshield' ); ?></strong>
						<small><?php esc_html_e( 'Impede que o site seja embutido em iframes por domínios terceiros.', 'siteshield' ); ?></small>
					</span>
				</label>

				<label class="ss-toggle-card">
					<input type="checkbox" name="protect_disable_print" value="1" <?php checked( ! empty( $settings['protect_disable_print'] ) ); ?> />
					<span class="card-info">
						<strong><?php esc_html_e( 'Bloquear Impressão de Página (Ctrl+P / @media print)', 'siteshield' ); ?></strong>
						<small><?php esc_html_e( 'Oculta o conteúdo do site caso o visitante tente imprimir em PDF ou papel.', 'siteshield' ); ?></small>
					</span>
				</label>
			</div>
		</div>
	</div>

	<!-- 3. PROTEÇÃO ANTI-HOTLINK -->
	<div class="ss-card" style="margin-top: 25px;">
		<div class="card-header">
			<h3><?php esc_html_e( 'Proteção Anti-Hotlink de Mídias', 'siteshield' ); ?></h3>
		</div>
		<div class="card-body">
			<label class="ss-toggle-card" style="margin-bottom: 20px;">
				<input type="checkbox" name="protect_anti_hotlink" value="1" <?php checked( ! empty( $settings['protect_anti_hotlink'] ) ); ?> />
				<span class="card-info">
					<strong><?php esc_html_e( 'Ativar Bloqueio de Hotlink Direto', 'siteshield' ); ?></strong>
					<small><?php esc_html_e( 'Bloqueia requisições diretas de imagens feitas por outros sites não autorizados.', 'siteshield' ); ?></small>
				</span>
			</label>

			<div class="ss-input-group">
				<label><?php esc_html_e( 'Domínios Permitidos para Hotlink (1 por linha):', 'siteshield' ); ?></label>
				<textarea name="protect_hotlink_allowed_domains" rows="3" placeholder="exemplo.com&#10;cdn.exemplo.com"><?php echo esc_textarea( isset( $settings['protect_hotlink_allowed_domains'] ) ? $settings['protect_hotlink_allowed_domains'] : '' ); ?></textarea>
			</div>

			<div style="margin-top: 20px; text-align: right;">
				<button type="submit" class="ss-btn ss-btn-primary"><?php esc_html_e( 'Salvar Configurações Anti-Clone', 'siteshield' ); ?></button>
			</div>
		</div>
	</div>
</form>
