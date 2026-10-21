<?php
/**
 * View de Configuração do Selo Oficial SiteShield Verified
 *
 * @package SiteShield Security
 * @author Mauriciokaleya
 */

use SiteShield\Core\Settings;
use SiteShield\Core\Feature_Manager;
use SiteShield\Cloud\Cloud_Client;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

$can_use = Feature_Manager::can_use( 'verification_badge' );
$lic     = Cloud_Client::get_license_info();

$enabled    = Settings::get( 'badge_enabled', false );
$position   = Settings::get( 'badge_position', 'bottom-right' );
$size       = Settings::get( 'badge_size', 'medium' );
$theme      = Settings::get( 'badge_theme', 'light' );
$margin     = (int) Settings::get( 'badge_margin', 20 );
$opacity    = (float) Settings::get( 'badge_opacity', 1.0 );
$display_on = Settings::get( 'badge_display_on', array( 'all' ) );
$exclusions = Settings::get( 'badge_exclusions', '' );

if ( ! is_array( $display_on ) ) {
	$display_on = array( 'all' );
}
?>
<div class="wrap siteshield-admin-wrap">
	<div class="siteshield-header">
		<div>
			<h1><?php esc_html_e( 'SiteShield Verified — Selo de Confiança', 'siteshield' ); ?></h1>
			<p class="description">
				<?php esc_html_e( 'Exiba um selo oficial de segurança em tempo real nas páginas públicas do seu website.', 'siteshield' ); ?>
			</p>
		</div>
		<div>
			<span class="siteshield-plan-badge badge-<?php echo esc_attr( strtolower( $lic['plan'] ) ); ?>">
				Plano <?php echo esc_html( $lic['plan'] ); ?>
			</span>
		</div>
	</div>

	<?php if ( ! $can_use ) : ?>
		<?php
		Feature_Manager::render_premium_lock_box(
			'verification_badge',
			__( 'Selo de Segurança SiteShield Verified', 'siteshield' ),
			__( 'O selo de segurança com verificação em tempo real é uma funcionalidade exclusiva dos planos PRO, Business e Agency. Faça upgrade para exibir aos seus visitantes que o site é certificado e protegido por SiteShield.', 'siteshield' )
		);
		?>
	<?php else : ?>
		<form method="post" action="<?php echo esc_url( admin_url( 'admin-post.php' ) ); ?>">
			<?php wp_nonce_field( 'siteshield_save_badge_settings', 'siteshield_badge_nonce' ); ?>
			<input type="hidden" name="action" value="siteshield_save_badge_settings" />

			<div class="siteshield-dashboard-grid" style="display: grid; grid-template-columns: 1fr 340px; gap: 24px; margin-top: 20px;">
				<!-- COLUNA ESQUERDA: CONFIGURAÇÕES -->
				<div class="siteshield-card" style="background:#FFF; border:1px solid #E2E8F0; border-radius:10px; padding:25px;">
					<h3 style="margin-top:0; border-bottom:1px solid #E2E8F0; padding-bottom:12px; font-size:1.15rem;">
						<?php esc_html_e( 'Opções de Exibição do Selo', 'siteshield' ); ?>
					</h3>

					<table class="form-table" role="presentation">
						<tr>
							<th scope="row"><?php esc_html_e( 'Ativar Selo', 'siteshield' ); ?></th>
							<td>
								<label for="badge_enabled">
									<input name="badge_enabled" type="checkbox" id="badge_enabled" value="1" <?php checked( $enabled, true ); ?> />
									<strong><?php esc_html_e( 'Mostrar o selo SiteShield Verified no website', 'siteshield' ); ?></strong>
								</label>
								<p class="description">
									<?php esc_html_e( 'Quando ativado, o selo aparecerá de forma elegante e flutuante nas páginas públicas.', 'siteshield' ); ?>
								</p>
							</td>
						</tr>

						<tr>
							<th scope="row"><label for="badge_position"><?php esc_html_e( 'Posição na Tela', 'siteshield' ); ?></label></th>
							<td>
								<select name="badge_position" id="badge_position" style="width: 280px;">
									<option value="bottom-right" <?php selected( $position, 'bottom-right' ); ?>><?php esc_html_e( 'Canto inferior direito (Padrão)', 'siteshield' ); ?></option>
									<option value="bottom-left" <?php selected( $position, 'bottom-left' ); ?>><?php esc_html_e( 'Canto inferior esquerdo', 'siteshield' ); ?></option>
									<option value="top-right" <?php selected( $position, 'top-right' ); ?>><?php esc_html_e( 'Canto superior direito', 'siteshield' ); ?></option>
									<option value="top-left" <?php selected( $position, 'top-left' ); ?>><?php esc_html_e( 'Canto superior esquerdo', 'siteshield' ); ?></option>
									<option value="bottom-center" <?php selected( $position, 'bottom-center' ); ?>><?php esc_html_e( 'Centro inferior', 'siteshield' ); ?></option>
									<option value="top-center" <?php selected( $position, 'top-center' ); ?>><?php esc_html_e( 'Centro superior', 'siteshield' ); ?></option>
								</select>
							</td>
						</tr>

						<tr>
							<th scope="row"><label for="badge_size"><?php esc_html_e( 'Tamanho do Selo', 'siteshield' ); ?></label></th>
							<td>
								<select name="badge_size" id="badge_size" style="width: 280px;">
									<option value="small" <?php selected( $size, 'small' ); ?>><?php esc_html_e( 'Pequeno (Compacto)', 'siteshield' ); ?></option>
									<option value="medium" <?php selected( $size, 'medium' ); ?>><?php esc_html_e( 'Médio (Recomendado)', 'siteshield' ); ?></option>
									<option value="large" <?php selected( $size, 'large' ); ?>><?php esc_html_e( 'Grande (Destaque)', 'siteshield' ); ?></option>
								</select>
							</td>
						</tr>

						<tr>
							<th scope="row"><label for="badge_theme"><?php esc_html_e( 'Tema Visual', 'siteshield' ); ?></label></th>
							<td>
								<select name="badge_theme" id="badge_theme" style="width: 280px;">
									<option value="light" <?php selected( $theme, 'light' ); ?>><?php esc_html_e( 'Claro (Fundo Branco)', 'siteshield' ); ?></option>
									<option value="dark" <?php selected( $theme, 'dark' ); ?>><?php esc_html_e( 'Escuro (Fundo Grafite / Noturno)', 'siteshield' ); ?></option>
								</select>
							</td>
						</tr>

						<tr>
							<th scope="row"><label for="badge_margin"><?php esc_html_e( 'Margem da Borda (px)', 'siteshield' ); ?></label></th>
							<td>
								<input name="badge_margin" type="number" id="badge_margin" value="<?php echo esc_attr( $margin ); ?>" min="5" max="100" class="small-text" /> px
							</td>
						</tr>

						<tr>
							<th scope="row"><label for="badge_opacity"><?php esc_html_e( 'Opacidade Inicial', 'siteshield' ); ?></label></th>
							<td>
								<input name="badge_opacity" type="number" id="badge_opacity" value="<?php echo esc_attr( $opacity ); ?>" min="0.3" max="1.0" step="0.1" class="small-text" />
								<p class="description"><?php esc_html_e( 'De 0.3 a 1.0 (ao passar o mouse a opacidade fica sempre 100%).', 'siteshield' ); ?></p>
							</td>
						</tr>

						<tr>
							<th scope="row"><?php esc_html_e( 'Onde Exibir', 'siteshield' ); ?></th>
							<td>
								<fieldset>
									<label><input type="checkbox" name="badge_display_on[]" value="all" <?php checked( in_array( 'all', $display_on, true ) ); ?> /> <?php esc_html_e( 'Todas as páginas públicas', 'siteshield' ); ?></label><br>
									<label><input type="checkbox" name="badge_display_on[]" value="homepage" <?php checked( in_array( 'homepage', $display_on, true ) ); ?> /> <?php esc_html_e( 'Página Inicial (Home)', 'siteshield' ); ?></label><br>
									<label><input type="checkbox" name="badge_display_on[]" value="pages" <?php checked( in_array( 'pages', $display_on, true ) ); ?> /> <?php esc_html_e( 'Páginas internas', 'siteshield' ); ?></label><br>
									<label><input type="checkbox" name="badge_display_on[]" value="posts" <?php checked( in_array( 'posts', $display_on, true ) ); ?> /> <?php esc_html_e( 'Artigos / Posts do blog', 'siteshield' ); ?></label><br>
									<label><input type="checkbox" name="badge_display_on[]" value="products" <?php checked( in_array( 'products', $display_on, true ) ); ?> /> <?php esc_html_e( 'Produtos WooCommerce', 'siteshield' ); ?></label><br>
									<label><input type="checkbox" name="badge_display_on[]" value="archives" <?php checked( in_array( 'archives', $display_on, true ) ); ?> /> <?php esc_html_e( 'Arquivos, Categorias e Tags', 'siteshield' ); ?></label>
								</fieldset>
							</td>
						</tr>

						<tr>
							<th scope="row"><label for="badge_exclusions"><?php esc_html_e( 'Excluir Páginas por ID', 'siteshield' ); ?></label></th>
							<td>
								<input name="badge_exclusions" type="text" id="badge_exclusions" value="<?php echo esc_attr( $exclusions ); ?>" class="regular-text" placeholder="Ex: 12, 45, 108" />
								<p class="description"><?php esc_html_e( 'Insira os IDs das páginas onde o selo não deve ser exibido (separados por vírgula).', 'siteshield' ); ?></p>
							</td>
						</tr>
					</table>

					<p class="submit" style="margin-top:20px;">
						<button type="submit" class="button button-primary" style="padding: 6px 22px; height: auto; font-size: 0.95rem;">
							<?php esc_html_e( 'Salvar Configurações do Selo', 'siteshield' ); ?>
						</button>
					</p>
				</div>

				<!-- COLUNA DIREITA: PREVIEW INTERATIVO -->
				<div class="siteshield-card" style="background:#FFF; border:1px solid #E2E8F0; border-radius:10px; padding:25px; height:fit-content;">
					<h3 style="margin-top:0; border-bottom:1px solid #E2E8F0; padding-bottom:12px; font-size:1.1rem;">
						<?php esc_html_e( 'Pré-visualização do Selo', 'siteshield' ); ?>
					</h3>

					<div id="badge-live-preview" style="background: #F8FAFC; border: 1px dashed #CBD5E1; border-radius: 10px; padding: 25px 15px; text-align: center; margin-bottom: 20px;">
						<!-- SELO VETORIAL PREVIEW -->
						<svg width="200" height="63" viewBox="0 0 320 100" fill="none" xmlns="http://www.w3.org/2000/svg" style="filter: drop-shadow(0 4px 10px rgba(0,0,0,0.12)); margin: 0 auto; display: block;">
							<rect x="3" y="3" width="314" height="94" rx="47" fill="#FFFFFF" stroke="#0056D2" stroke-width="6"/>
							<path d="M75 22C60 22 45 28 45 28V58C45 74 75 88 75 88C75 88 105 74 105 58V28C105 28 90 22 75 22Z" fill="#0056D2"/>
							<path d="M68 47V42C68 38.134 71.134 35 75 35C78.866 35 82 38.134 82 42V47M65 47H85C86.6569 47 88 48.3431 88 50V62C88 63.6569 86.6569 65 85 65H65C63.3431 65 62 63.6569 62 62V50C62 48.3431 63.3431 47 65 47Z" stroke="#FFFFFF" stroke-width="3" stroke-linecap="round" stroke-linejoin="round"/>
							<circle cx="75" cy="56" r="2.5" fill="#FFFFFF"/>
							<rect x="115" y="24" width="185" height="52" rx="26" fill="#000000"/>
							<text x="207" y="52" fill="#FFFFFF" font-family="-apple-system, BlinkMacSystemFont, 'Segoe UI', Roboto, sans-serif" font-size="22" font-weight="900" letter-spacing="1.5" text-anchor="middle">SECURE</text>
							<text x="207" y="66" fill="#FFFFFF" font-family="-apple-system, BlinkMacSystemFont, 'Segoe UI', Roboto, sans-serif" font-size="10.5" font-weight="600" letter-spacing="0.5" text-anchor="middle">SiteShield</text>
							<path d="M192 73L193.2 76.5H196.8L193.9 78.6L195 82.1L192 80L189 82.1L190.1 78.6L187.2 76.5H190.8L192 73Z" fill="#0056D2"/>
							<path d="M207 73L208.2 76.5H211.8L208.9 78.6L210 82.1L207 80L204 82.1L205.1 78.6L202.2 76.5H205.8L207 73Z" fill="#0056D2"/>
							<path d="M222 73L223.2 76.5H226.8L223.9 78.6L225 82.1L222 80L219 82.1L220.1 78.6L217.2 76.5H220.8L222 73Z" fill="#0056D2"/>
						</svg>
					</div>

					<div style="font-size: 0.85rem; color: #64748B; line-height: 1.5;">
						<p style="margin-bottom: 8px;">
							<strong>ID de Verificação:</strong><br>
							<code><?php echo esc_html( ! empty( $lic['verification_id'] ) ? $lic['verification_id'] : 'SSV-SYNCHRONIZED' ); ?></code>
						</p>
						<p>
							<?php if ( ! empty( $lic['verification_id'] ) ) : ?>
								<a href="<?php echo esc_url( Cloud_Client::get_cloud_url() . '/verify/' . urlencode( $lic['verification_id'] ) ); ?>" target="_blank" class="button button-secondary" style="width: 100%; text-align: center;">
									<?php esc_html_e( 'Testar Certificado Público &rarr;', 'siteshield' ); ?>
								</a>
							<?php endif; ?>
						</p>
					</div>
				</div>
			</div>
		</form>
	<?php endif; ?>
</div>
