<?php
/**
 * View de Suporte e Atendimento do Plugin (admin/views/support.php)
 *
 * @package SiteShield Security
 * @author Mauriciokaleya
 */

use SiteShield\Cloud\Cloud_Client;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

$lic      = Cloud_Client::get_license_info();
$identity = Cloud_Client::get_identity();

$support_url = Cloud_Client::get_cloud_url() . '/support/create?' . http_build_query( array(
	'domain'  => $identity['domain'],
	'site_id' => $identity['site_id'],
	'plan'    => $lic['plan'],
	'ver'     => $identity['plugin_version'],
) );

$chat_url = Cloud_Client::get_cloud_url() . '/support/chat';
?>

<div class="wrap siteshield-admin-wrap">
	<div class="siteshield-header">
		<div>
			<h1><?php esc_html_e( 'Suporte & Central de Ajuda SiteShield', 'siteshield' ); ?></h1>
			<p class="description">
				<?php esc_html_e( 'Precisa de ajuda técnica, esclarecimento sobre licenças ou reportar um incidente de segurança? A nossa equipa de engenharia está à sua disposição.', 'siteshield' ); ?>
			</p>
		</div>
		<div>
			<span class="siteshield-plan-badge badge-<?php echo esc_attr( strtolower( $lic['plan'] ) ); ?>" style="font-size: 0.95rem; padding: 6px 14px; border-radius: 20px; font-weight: 700; background: #0066FF; color: #FFF;">
				Plano <?php echo esc_html( $lic['plan'] ); ?>
			</span>
		</div>
	</div>

	<div class="siteshield-dashboard-grid" style="display: grid; grid-template-columns: 1fr 340px; gap: 24px; margin-top: 20px;">
		<!-- COLUNA PRINCIPAL: ACESSO AO SUPORTE -->
		<div>
			<!-- CARD PRINCIPAL DE ACESSO AO SUPORTE -->
			<div class="siteshield-card" style="background: #FFFFFF; border: 1px solid #E2E8F0; border-radius: 12px; padding: 30px; margin-bottom: 24px; box-shadow: 0 2px 4px rgba(0,0,0,0.02);">
				<div style="display: flex; gap: 18px; align-items: flex-start;">
					<div style="width: 52px; height: 52px; border-radius: 12px; background: #EFF6FF; color: #0066FF; display: flex; align-items: center; justify-content: center; flex-shrink: 0;">
						<svg width="28" height="28" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M18.364 5.636l-3.536 3.536m0 5.656l3.536 3.536M9.172 9.172L5.636 5.636m3.536 9.192l-3.536 3.536M21 12a9 9 0 11-18 0 9 9 0 0118 0zm-5 0a4 4 0 11-8 0 4 4 0 018 0z"></path></svg>
					</div>
					<div>
						<h2 style="font-size: 1.3rem; font-weight: 800; color: #0F172A; margin: 0 0 8px;">
							Central Oficial de Suporte SiteShield Cloud
						</h2>
						<p style="color: #475569; font-size: 0.95rem; line-height: 1.6; margin: 0 0 20px;">
							O suporte técnico especializado para o plugin e as integrações da nuvem é gerido centralizadamente através do portal <strong>SiteShield Cloud</strong>. Ao abrir uma solicitação, os detalhes de diagnóstico deste website são automaticamente vinculados para um atendimento mais ágil.
						</p>

						<div style="display: flex; gap: 12px; flex-wrap: wrap;">
							<a href="<?php echo esc_url( $support_url ); ?>" target="_blank" class="button button-primary" style="background: #0066FF; border-color: #0066FF; font-size: 0.95rem; padding: 6px 22px; height: auto; font-weight: 700;">
								Aceder à Central de Suporte &rarr;
							</a>
							<a href="<?php echo esc_url( $chat_url ); ?>" target="_blank" class="button button-secondary" style="font-size: 0.95rem; padding: 6px 18px; height: auto;">
								Iniciar Chat de Suporte
							</a>
						</div>
					</div>
				</div>
			</div>

			<!-- PERGUNTAS FREQUENTES & BASE DE CONHECIMENTO -->
			<div class="siteshield-card" style="background: #FFFFFF; border: 1px solid #E2E8F0; border-radius: 12px; padding: 25px;">
				<h3 style="font-size: 1.1rem; font-weight: 700; color: #0F172A; margin-top: 0; margin-bottom: 16px; border-bottom: 1px solid #E2E8F0; padding-bottom: 10px;">
					Base de Conhecimento Rápida
				</h3>

				<div style="display: grid; gap: 16px;">
					<div style="padding: 14px; background: #F8FAFC; border: 1px solid #E2E8F0; border-radius: 8px;">
						<strong style="color: #0F172A; font-size: 0.92rem; display: block; margin-bottom: 4px;">Como funciona a ativação da licença PRO/Business?</strong>
						<p style="color: #64748B; font-size: 0.85rem; line-height: 1.5; margin: 0;">
							Acesse o menu <a href="<?php echo esc_url( admin_url( 'admin.php?page=siteshield-license' ) ); ?>">Licença & Planos</a>, insira a chave recebida no painel SiteShield Cloud e clique em <em>Ativar Licença</em>. O sistema valida os limites e desbloqueia os recursos instantaneamente.
						</p>
					</div>

					<div style="padding: 14px; background: #F8FAFC; border: 1px solid #E2E8F0; border-radius: 8px;">
						<strong style="color: #0F172A; font-size: 0.92rem; display: block; margin-bottom: 4px;">O que acontece se a nuvem SiteShield Cloud estiver offline?</strong>
						<p style="color: #64748B; font-size: 0.85rem; line-height: 1.5; margin: 0;">
							O plugin possui o motor <strong>Local License Cache Fail-Safe</strong>. Todas as proteções locais continuam 100% operacionais e seu site nunca sai do ar.
						</p>
					</div>

					<div style="padding: 14px; background: #F8FAFC; border: 1px solid #E2E8F0; border-radius: 8px;">
						<strong style="color: #0F172A; font-size: 0.92rem; display: block; margin-bottom: 4px;">Como ativar o Selo SiteShield Verified no rodapé do meu tema?</strong>
						<p style="color: #64748B; font-size: 0.85rem; line-height: 1.5; margin: 0;">
							Com o plano PRO ativo, vá ao menu <a href="<?php echo esc_url( admin_url( 'admin.php?page=siteshield-verified-badge' ) ); ?>">Selo Verificado</a>, ative a exibição e escolha a posição flutuante ou o shortcode <code>[siteshield_verified_badge]</code>.
						</p>
					</div>
				</div>
			</div>
		</div>

		<!-- BARRA LATERAL: CONTEXTO SEGURO DE DIAGNÓSTICO -->
		<div>
			<div class="siteshield-card" style="background: #F8FAFC; border: 1px solid #E2E8F0; border-radius: 10px; padding: 20px; margin-bottom: 20px;">
				<h4 style="margin-top: 0; font-size: 0.95rem; color: #0F172A;">Contexto Técnico do Website</h4>
				<p style="font-size: 0.8rem; color: #64748B; margin-bottom: 8px;">
					<strong>Domínio:</strong><br>
					<code><?php echo esc_html( $identity['domain'] ); ?></code>
				</p>
				<p style="font-size: 0.8rem; color: #64748B; margin-bottom: 8px;">
					<strong>Plano Atual:</strong><br>
					<strong style="color: #0066FF;"><?php echo esc_html( $lic['plan'] ); ?></strong> (<?php echo esc_html( $lic['status'] ); ?>)
				</p>
				<p style="font-size: 0.8rem; color: #64748B; margin-bottom: 8px;">
					<strong>Versão do Plugin:</strong><br>
					<code>v<?php echo esc_html( $identity['plugin_version'] ); ?></code>
				</p>
				<p style="font-size: 0.8rem; color: #64748B; margin-bottom: 8px;">
					<strong>WordPress / PHP:</strong><br>
					<code>WP <?php echo esc_html( $identity['wordpress_ver'] ); ?> / PHP <?php echo esc_html( $identity['php_version'] ); ?></code>
				</p>
				<p style="font-size: 0.8rem; color: #64748B; margin-bottom: 0; word-break: break-all;">
					<strong>Installation ID:</strong><br>
					<code><?php echo esc_html( substr( $identity['installation_id'], 0, 24 ) . '...' ); ?></code>
				</p>
			</div>

			<div class="siteshield-card" style="background: #0B0F19; color: #FFF; border-radius: 10px; padding: 20px; text-align: center;">
				<h4 style="color: #FFF; margin-top: 0; font-size: 1.05rem;">Documentação Técnica</h4>
				<p style="color: #94A3B8; font-size: 0.82rem; line-height: 1.5; margin-bottom: 16px;">
					Consulte os manuais completos de blindagem, configuração de regras de WAF e integração de API.
				</p>
				<a href="<?php echo esc_url( Cloud_Client::get_cloud_url() . '/docs' ); ?>" target="_blank" class="button button-primary" style="background: #0066FF; color: #FFF; border-color: #0066FF; font-weight: 700; width: 100%;">
					Ver Documentação &rarr;
				</a>
			</div>
		</div>
	</div>
</div>
