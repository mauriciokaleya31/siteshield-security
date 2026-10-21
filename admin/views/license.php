<?php
/**
 * View de Gestão de Licença e Sincronização Cloud (license.php)
 *
 * @package SiteShield Security
 * @author Mauriciokaleya
 */

use SiteShield\Cloud\Cloud_Client;
use SiteShield\Core\Feature_Manager;
use SiteShield\Core\Settings;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

$lic      = Cloud_Client::get_license_info();
$identity = Cloud_Client::get_identity();
$cloud_url= Cloud_Client::get_cloud_url();
?>
<div class="wrap siteshield-admin-wrap">
	<div class="siteshield-header">
		<div>
			<h1><?php esc_html_e( 'Licença & Conexão SiteShield Cloud', 'siteshield' ); ?></h1>
			<p class="description">
				<?php esc_html_e( 'Gerencie sua License Key, verifique os limites de proteção e sincronize com a Cloud.', 'siteshield' ); ?>
			</p>
		</div>
		<div>
			<span class="siteshield-plan-badge badge-<?php echo esc_attr( strtolower( $lic['plan'] ) ); ?>" style="font-size: 0.95rem; padding: 6px 14px; border-radius: 20px; font-weight: 700; background: #0066FF; color: #FFF;">
				Plano <?php echo esc_html( $lic['plan'] ); ?>
			</span>
		</div>
	</div>

	<!-- NOTIFICAÇÕES DE FEEDBACK APÓS REDIRECIONAMENTO -->
	<?php if ( isset( $_GET['activated'] ) ) : ?>
		<div class="notice notice-success is-dismissible" style="border-left-color: #10B981; padding: 12px 16px; margin: 15px 0;">
			<p style="font-size: 0.95rem; font-weight: 600; color: #065F46;">
				✓ <?php esc_html_e( 'Licença validada e ativada com sucesso! As funcionalidades premium foram desbloqueadas.', 'siteshield' ); ?>
			</p>
		</div>
	<?php elseif ( isset( $_GET['deactivated'] ) ) : ?>
		<div class="notice notice-info is-dismissible" style="padding: 12px 16px; margin: 15px 0;">
			<p style="font-size: 0.95rem; color: #1E40AF;">
				ℹ️ <?php esc_html_e( 'A licença foi desativada deste website. O sistema retornou para o plano FREE.', 'siteshield' ); ?>
			</p>
		</div>
	<?php elseif ( isset( $_GET['error'] ) ) : ?>
		<div class="notice notice-error is-dismissible" style="border-left-color: #DC2626; padding: 12px 16px; margin: 15px 0;">
			<p style="font-size: 0.95rem; font-weight: 600; color: #991B1B;">
				⚠️ <?php echo esc_html( sanitize_text_field( wp_unslash( $_GET['error'] ) ) ); ?>
			</p>
		</div>
	<?php endif; ?>

	<!-- AVISOS PROGRESSIVOS DE EXPIRAÇÃO -->
	<?php if ( ! $lic['is_free'] && ! empty( $lic['days_remaining'] ) ) : ?>
		<?php if ( $lic['in_grace'] ) : ?>
			<div class="notice notice-error" style="border-left-color: #DC2626; padding: 14px 18px; margin: 20px 0; border-radius: 6px; background: #FEF2F2;">
				<h3 style="margin-top: 0; color: #991B1B; font-size: 1.1rem;">⚠️ Período de Tolerância (Grace Period Ativo)</h3>
				<p style="color: #7F1D1D; font-size: 0.95rem; margin-bottom: 10px;">
					A sua licença expirou recentemente. O seu website possui <strong>7 dias de tolerância</strong> com as proteções mantidas antes de reverter para o plano FREE.
				</p>
				<a href="<?php echo esc_url( $cloud_url . '/payments' ); ?>" target="_blank" class="button button-primary" style="background: #DC2626; border-color: #DC2626;">
					Renovar Licença Agora &rarr;
				</a>
			</div>
		<?php elseif ( $lic['days_remaining'] <= 3 ) : ?>
			<div class="notice notice-error" style="padding: 12px 16px; margin: 20px 0;">
				<p style="font-weight: 600; font-size: 0.95rem;">
					⚠️ <strong>Atenção Crítica:</strong> A sua licença SiteShield expira em <strong><?php echo (int) $lic['days_remaining']; ?> dias</strong>. Renove para não perder os recursos premium.
					<a href="<?php echo esc_url( $cloud_url . '/payments' ); ?>" target="_blank" style="margin-left: 10px; font-weight: 700;">Renovar Agora &rarr;</a>
				</p>
			</div>
		<?php elseif ( $lic['days_remaining'] <= 7 ) : ?>
			<div class="notice notice-warning" style="padding: 12px 16px; margin: 20px 0;">
				<p style="font-size: 0.95rem;">
					⚠️ <strong>Aviso:</strong> A sua licença SiteShield expira em <strong>7 dias</strong>.
					<a href="<?php echo esc_url( $cloud_url . '/payments' ); ?>" target="_blank" style="margin-left: 10px; font-weight: 700;">Ver Faturas &rarr;</a>
				</p>
			</div>
		<?php elseif ( $lic['days_remaining'] <= 15 ) : ?>
			<div class="notice notice-warning" style="padding: 10px 15px; margin: 20px 0;">
				<p>A sua licença SiteShield expira em <strong><?php echo (int) $lic['days_remaining']; ?> dias</strong>.</p>
			</div>
		<?php elseif ( $lic['days_remaining'] <= 30 ) : ?>
			<div class="notice notice-info" style="padding: 10px 15px; margin: 20px 0;">
				<p>Lembrete: A sua licença SiteShield expira em <strong><?php echo (int) $lic['days_remaining']; ?> dias</strong>.</p>
			</div>
		<?php endif; ?>
	<?php endif; ?>

	<div class="siteshield-dashboard-grid" style="display: grid; grid-template-columns: 1fr 340px; gap: 24px; margin-top: 20px;">
		<!-- COLUNA PRINCIPAL: ESTADO DA LICENÇA -->
		<div>
			<div class="siteshield-card" style="background: #FFF; border: 1px solid #E2E8F0; border-radius: 10px; padding: 25px; margin-bottom: 24px;">
				<h3 style="margin-top: 0; border-bottom: 1px solid #E2E8F0; padding-bottom: 12px; font-size: 1.15rem;">
					<?php esc_html_e( 'Informações da Licença Atual', 'siteshield' ); ?>
				</h3>

				<table class="form-table" role="presentation">
					<tr>
						<th scope="row"><?php esc_html_e( 'Plano Atual:', 'siteshield' ); ?></th>
						<td>
							<strong style="font-size: 1.1rem; color: #0066FF;"><?php echo esc_html( $lic['plan'] ); ?></strong>
							<?php if ( $lic['is_free'] ) : ?>
								<span style="color: #64748B; font-size: 0.85rem; margin-left: 8px;">(Plano Comunitário Gratuito)</span>
							<?php endif; ?>
						</td>
					</tr>

					<tr>
						<th scope="row"><?php esc_html_e( 'Estado da Licença:', 'siteshield' ); ?></th>
						<td>
							<?php if ( $lic['is_active'] ) : ?>
								<span style="display: inline-flex; align-items: center; gap: 6px; color: #059669; font-weight: 700; background: #ECFDF5; padding: 4px 10px; border-radius: 6px; font-size: 0.85rem;">
									<span style="width: 8px; height: 8px; border-radius: 50%; background: #10B981;"></span>
									ATIVA & SINCRONIZADA
								</span>
							<?php elseif ( 'EXPIRED' === $lic['status'] ) : ?>
								<span style="color: #DC2626; font-weight: 700; background: #FEF2F2; padding: 4px 10px; border-radius: 6px; font-size: 0.85rem;">
									EXPIRADA (Proteção local FREE ativa)
								</span>
							<?php else : ?>
								<span style="color: #64748B; background: #F1F5F9; padding: 4px 10px; border-radius: 6px; font-size: 0.85rem;">
									Modo Local (FREE)
								</span>
							<?php endif; ?>
						</td>
					</tr>

					<tr>
						<th scope="row"><?php esc_html_e( 'License Key:', 'siteshield' ); ?></th>
						<td>
							<code style="font-size: 0.95rem; padding: 4px 10px; background: #F8FAFC; border: 1px solid #CBD5E1; border-radius: 4px;">
								<?php echo esc_html( $lic['license_key_masked'] ); ?>
							</code>
						</td>
					</tr>

					<?php if ( ! empty( $lic['expires_at'] ) ) : ?>
						<tr>
							<th scope="row"><?php esc_html_e( 'Data de Expiração:', 'siteshield' ); ?></th>
							<td>
								<strong><?php echo esc_html( date_i18n( 'd/m/Y', strtotime( $lic['expires_at'] ) ) ); ?></strong>
								<?php if ( null !== $lic['days_remaining'] ) : ?>
									<span style="color: #64748B; font-size: 0.85rem; margin-left: 8px;">
										(<?php echo (int) $lic['days_remaining']; ?> dias restantes)
									</span>
								<?php endif; ?>
							</td>
						</tr>
					<?php endif; ?>

					<tr>
						<th scope="row"><?php esc_html_e( 'Tolerância a Falhas:', 'siteshield' ); ?></th>
						<td>
							<span style="color: #059669; font-weight: 600;">✓ Local License Cache Ativo</span>
							<p class="description">
								<?php esc_html_e( 'O plugin mantém uma cópia de segurança local do estado da licença. Se a nuvem estiver temporariamente inacessível, o seu website continua 100% funcional e protegido.', 'siteshield' ); ?>
							</p>
						</td>
					</tr>
				</table>
			</div>

			<!-- FORMULÁRIO DE ATIVAÇÃO DE CHAVE -->
			<div class="siteshield-card" style="background: #FFF; border: 1px solid #E2E8F0; border-radius: 10px; padding: 25px;">
				<h3 style="margin-top: 0; border-bottom: 1px solid #E2E8F0; padding-bottom: 12px; font-size: 1.15rem;">
					<?php esc_html_e( 'Ativar Nova License Key', 'siteshield' ); ?>
				</h3>

				<form method="post" action="<?php echo esc_url( admin_url( 'admin-post.php' ) ); ?>" id="form-activate-license">
					<?php wp_nonce_field( 'siteshield_activate_license', 'siteshield_license_nonce' ); ?>
					<input type="hidden" name="action" value="siteshield_activate_license" />

					<p style="color: #64748B; margin-bottom: 16px;">
						<?php esc_html_e( 'Insira a chave recebida no SiteShield Cloud após a aquisição do plano PRO, Business ou Agency.', 'siteshield' ); ?>
					</p>

					<div style="display: flex; gap: 12px; max-width: 550px; flex-wrap: wrap;">
						<input type="text" id="input_license_key" name="license_key" placeholder="SS-PRO-XXXX-XXXX-XXXX" class="regular-text" style="flex: 1; font-family: monospace; font-size: 1rem; padding: 8px 12px; min-width: 260px;" required />
						<button type="button" id="btn-activate-license" class="button button-primary" style="padding: 6px 22px; height: auto; font-size: 0.95rem; font-weight: 700;">
							<?php esc_html_e( 'Ativar Licença', 'siteshield' ); ?>
						</button>
					</div>
				</form>

				<?php if ( ! $lic['is_free'] ) : ?>
					<form method="post" action="<?php echo esc_url( admin_url( 'admin-post.php' ) ); ?>" style="margin-top: 25px; border-top: 1px dashed #E2E8F0; padding-top: 18px;" onsubmit="return confirm('Deseja realmente desativar esta licença? O website reverterá para o plano FREE.');">
						<?php wp_nonce_field( 'siteshield_deactivate_license', 'siteshield_deactivate_nonce' ); ?>
						<input type="hidden" name="action" value="siteshield_deactivate_license" />
						<button type="submit" class="button" style="color: #DC2626; border-color: #FECACA; background: #FEF2F2;">
							<?php esc_html_e( 'Desativar Licença deste Website', 'siteshield' ); ?>
						</button>
					</form>
				<?php endif; ?>
			</div>
		</div>

		<!-- BARRA LATERAL: IDENTIDADE & CONEXÃO -->
		<div>
			<div class="siteshield-card" style="background: #F8FAFC; border: 1px solid #E2E8F0; border-radius: 10px; padding: 20px; margin-bottom: 20px;">
				<h4 style="margin-top: 0; font-size: 0.95rem; color: #0F172A;">Identificadores Desta Instalação</h4>
				<p style="font-size: 0.8rem; color: #64748B; margin-bottom: 6px;">
					<strong>Site ID:</strong><br>
					<code><?php echo esc_html( $identity['site_id'] ? $identity['site_id'] : 'Gerado na Conexão' ); ?></code>
				</p>
				<p style="font-size: 0.8rem; color: #64748B; margin-bottom: 6px; word-break: break-all;">
					<strong>Installation ID:</strong><br>
					<code><?php echo esc_html( substr( $identity['installation_id'], 0, 24 ) . '...' ); ?></code>
				</p>
				<p style="font-size: 0.8rem; color: #64748B; margin-bottom: 6px;">
					<strong>Domínio:</strong><br>
					<code><?php echo esc_html( $identity['domain'] ); ?></code>
				</p>
				<p style="font-size: 0.8rem; color: #64748B; margin-bottom: 0;">
					<strong>Endpoint Cloud:</strong><br>
					<code style="word-break: break-all;"><?php echo esc_html( $cloud_url ); ?></code>
				</p>
			</div>

			<div class="siteshield-card" style="background: linear-gradient(135deg, #0066FF 0%, #0047BA 100%); color: #FFF; border-radius: 10px; padding: 22px; text-align: center;">
				<h4 style="color: #FFF; margin-top: 0; font-size: 1.1rem;">Precisa de Mais Proteção?</h4>
				<p style="color: #BFDBFE; font-size: 0.85rem; line-height: 1.5; margin-bottom: 18px;">
					Desbloqueie o Selo SiteShield Verified, relatórios em PDF, WAF avançado e varredura heurística profunda.
				</p>
				<a href="<?php echo esc_url( $cloud_url . '/pricing' ); ?>" target="_blank" class="button button-primary" style="background: #FFF; color: #0066FF; border-color: #FFF; font-weight: 700; width: 100%;">
					Ver Planos na Cloud &rarr;
				</a>
			</div>
		</div>
	</div>
</div>
