<?php
/**
 * Gerenciador Central de Funcionalidades e Feature Flags por Plano
 *
 * @package SiteShield Security
 * @author Mauriciokaleya
 */

namespace SiteShield\Core;

use SiteShield\Cloud\Cloud_Client;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

class Feature_Manager {

	/**
	 * Definição da hierarquia de planos
	 */
	const PLAN_FREE     = 'FREE';
	const PLAN_PRO      = 'PRO';
	const PLAN_BUSINESS = 'BUSINESS';
	const PLAN_AGENCY   = 'AGENCY';

	/**
	 * Retorna o nível numérico do plano para comparação
	 *
	 * @param string $plan
	 * @return int
	 */
	public static function get_plan_level( $plan ) {
		$levels = array(
			self::PLAN_FREE     => 1,
			self::PLAN_PRO      => 2,
			self::PLAN_BUSINESS => 3,
			self::PLAN_AGENCY   => 4,
		);
		$p = strtoupper( trim( (string) $plan ) );
		return isset( $levels[ $p ] ) ? $levels[ $p ] : 1;
	}

	/**
	 * Retorna o slug do plano atualmente ativo
	 *
	 * @return string (FREE, PRO, BUSINESS, AGENCY)
	 */
	public static function get_current_plan() {
		$license = Cloud_Client::get_license_info();
		$status  = strtoupper( isset( $license['status'] ) ? $license['status'] : 'INACTIVE' );

		// Se a licença estiver ativa ou no período de tolerância (grace period), usa o plano da licença
		if ( ( 'ACTIVE' === $status || 'IN_GRACE' === $status ) && ! empty( $license['plan'] ) ) {
			return strtoupper( $license['plan'] );
		}

		return self::PLAN_FREE;
	}

	/**
	 * Verifica se o website tem permissão para utilizar uma funcionalidade
	 *
	 * @param string $feature_slug
	 * @return bool
	 */
	public static function can_use( $feature_slug ) {
		$current_plan  = self::get_current_plan();
		$current_level = self::get_plan_level( $current_plan );
		$required_plan = self::get_required_plan( $feature_slug );
		$req_level     = self::get_plan_level( $required_plan );

		return $current_level >= $req_level;
	}

	/**
	 * Retorna o plano mínimo exigido para cada funcionalidade
	 *
	 * @param string $feature_slug
	 * @return string
	 */
	public static function get_required_plan( $feature_slug ) {
		$map = array(
			// Funcionalidades FREE (Básicas)
			'basic_waf'            => self::PLAN_FREE,
			'login_protection'     => self::PLAN_FREE,
			'basic_scanner'        => self::PLAN_FREE,
			'security_score'       => self::PLAN_FREE,
			'basic_diagnostics'    => self::PLAN_FREE,
			'basic_anticlone'      => self::PLAN_FREE,

			// Funcionalidades PRO (Avançadas)
			'verification_badge'   => self::PLAN_PRO,
			'advanced_waf'         => self::PLAN_PRO,
			'malware_deep_scan'    => self::PLAN_PRO,
			'pdf_reports'          => self::PLAN_PRO,
			'cloud_monitoring'     => self::PLAN_PRO,
			'quarantine_manager'   => self::PLAN_PRO,
			'custom_waf_rules'     => self::PLAN_PRO,
			'geo_blocking'         => self::PLAN_PRO,

			// Funcionalidades BUSINESS & AGENCY
			'threat_intelligence'  => self::PLAN_BUSINESS,
			'audit_export'         => self::PLAN_BUSINESS,
			'multi_site_sync'      => self::PLAN_AGENCY,
		);

		return isset( $map[ $feature_slug ] ) ? $map[ $feature_slug ] : self::PLAN_PRO;
	}

	/**
	 * Renderiza um banner/card profissional de upgrade quando o recurso for bloqueado
	 *
	 * @param string $feature_slug
	 * @param string $feature_title
	 * @param string $description
	 */
	public static function render_premium_lock_box( $feature_slug, $feature_title = '', $description = '' ) {
		$req_plan = self::get_required_plan( $feature_slug );
		$plans_url = Cloud_Client::get_cloud_url() . '/pricing';
		$license_url = admin_url( 'admin.php?page=siteshield-license' );
		?>
		<div class="siteshield-premium-lock-box" style="background: #FFFFFF; border: 1px solid #E2E8F0; border-radius: 12px; padding: 35px 25px; text-align: center; max-width: 680px; margin: 30px auto; box-shadow: 0 4px 6px -1px rgba(0, 0, 0, 0.05);">
			<div style="width: 56px; height: 56px; border-radius: 50%; background: #EFF6FF; color: #0066FF; display: flex; align-items: center; justify-content: center; margin: 0 auto 16px;">
				<svg width="28" height="28" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 15v2m-6 4h12a2 2 0 002-2v-6a2 2 0 00-2-2H6a2 2 0 00-2 2v6a2 2 0 002 2zm10-10V7a4 4 0 00-8 0v4h8z"></path></svg>
			</div>

			<span class="badge" style="background: #0066FF; color: #FFFFFF; font-size: 0.75rem; font-weight: 700; padding: 4px 10px; border-radius: 20px; text-transform: uppercase; letter-spacing: 0.05em; display: inline-block; margin-bottom: 10px;">
				RECURSO <?php echo esc_html( $req_plan ); ?>
			</span>

			<h3 style="font-size: 1.35rem; font-weight: 700; color: #0F172A; margin: 0 0 10px;">
				<?php echo esc_html( ! empty( $feature_title ) ? $feature_title : 'Funcionalidade Premium' ); ?>
			</h3>

			<p style="color: #64748B; font-size: 0.95rem; line-height: 1.6; margin: 0 auto 24px; max-width: 520px;">
				<?php echo esc_html( ! empty( $description ) ? $description : sprintf( 'Esta funcionalidade está disponível a partir do plano %s ou superior. Faça upgrade para desbloquear este recurso no seu website.', $req_plan ) ); ?>
			</p>

			<div style="display: flex; justify-content: center; align-items: center; gap: 14px; flex-wrap: wrap;">
				<a href="<?php echo esc_url( $plans_url ); ?>" target="_blank" class="button button-primary" style="background: #0066FF; border-color: #0066FF; padding: 6px 20px; height: auto; font-size: 0.95rem; font-weight: 600; border-radius: 6px;">
					Ver Planos & Upgrade &rarr;
				</a>
				<a href="<?php echo esc_url( $license_url ); ?>" class="button button-secondary" style="padding: 6px 18px; height: auto; font-size: 0.95rem; border-radius: 6px;">
					Ativar License Key
				</a>
			</div>
		</div>
		<?php
	}
}
