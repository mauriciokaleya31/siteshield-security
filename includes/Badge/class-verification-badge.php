<?php
/**
 * Injetor e Gerenciador do Selo Oficial SiteShield Verified no Frontend
 *
 * @package SiteShield Security
 * @author Mauriciokaleya
 */

namespace SiteShield\Badge;

use SiteShield\Core\Settings;
use SiteShield\Core\Feature_Manager;
use SiteShield\Cloud\Cloud_Client;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

class Verification_Badge {

	/**
	 * Inicializa os hooks do selo
	 */
	public static function init() {
		add_action( 'wp_footer', array( __CLASS__, 'render_badge' ), 999 );
	}

	/**
	 * Verifica se o selo deve ser renderizado na página atual
	 *
	 * @return bool
	 */
	public static function should_render() {
		// Não renderiza em páginas administrativas, AJAX, feeds ou REST API
		if ( is_admin() || wp_doing_ajax() || is_feed() || ( defined( 'REST_REQUEST' ) && REST_REQUEST ) ) {
			return false;
		}

		// 1. Verificação de Permissão de Plano (Exclusivo PRO / BUSINESS / AGENCY)
		if ( ! Feature_Manager::can_use( 'verification_badge' ) ) {
			return false;
		}

		// 2. Verificação de Ativação nas Configurações
		$enabled = Settings::get( 'badge_enabled', false );
		if ( ! $enabled ) {
			return false;
		}

		// 3. Verificação de Exclusões por ID de Página
		$current_id  = get_queried_object_id();
		$exclusions  = Settings::get( 'badge_exclusions', '' );
		if ( ! empty( $exclusions ) && ! empty( $current_id ) ) {
			$excluded_ids = array_map( 'trim', explode( ',', $exclusions ) );
			if ( in_array( (string) $current_id, $excluded_ids, true ) ) {
				return false;
			}
		}

		// 4. Verificação de Escopo de Exibição
		$display_on = Settings::get( 'badge_display_on', array( 'all' ) );
		if ( ! is_array( $display_on ) ) {
			$display_on = array( 'all' );
		}

		if ( in_array( 'all', $display_on, true ) ) {
			return true;
		}

		if ( is_front_page() || is_home() ) {
			return in_array( 'homepage', $display_on, true );
		}

		if ( is_single() ) {
			if ( function_exists( 'is_product' ) && is_product() ) {
				return in_array( 'products', $display_on, true );
			}
			return in_array( 'posts', $display_on, true );
		}

		if ( is_page() ) {
			return in_array( 'pages', $display_on, true );
		}

		if ( is_archive() || is_category() || is_tag() ) {
			return in_array( 'archives', $display_on, true );
		}

		return true;
	}

	/**
	 * Injeta o selo com posicionamento customizável e SVG vetorial de alta definição
	 */
	public static function render_badge() {
		if ( ! self::should_render() ) {
			return;
		}

		$lic_info = Cloud_Client::get_license_info();
		$ver_id   = ! empty( $lic_info['verification_id'] ) ? $lic_info['verification_id'] : 'SSV-VERIFIED';

		$position = Settings::get( 'badge_position', 'bottom-right' );
		$size     = Settings::get( 'badge_size', 'medium' );
		$opacity  = (float) Settings::get( 'badge_opacity', 1.0 );
		$margin   = (int) Settings::get( 'badge_margin', 20 );
		$theme    = Settings::get( 'badge_theme', 'light' );

		// Cálculo de dimensões
		$dimensions = array(
			'small'  => array( 'w' => 140, 'h' => 44 ),
			'medium' => array( 'w' => 175, 'h' => 55 ),
			'large'  => array( 'w' => 210, 'h' => 66 ),
		);
		$dim = isset( $dimensions[ $size ] ) ? $dimensions[ $size ] : $dimensions['medium'];

		// Estilos de Posicionamento CSS
		$pos_css = '';
		switch ( $position ) {
			case 'bottom-left':
				$pos_css = "bottom: {$margin}px; left: {$margin}px;";
				break;
			case 'top-right':
				$pos_css = "top: {$margin}px; right: {$margin}px;";
				break;
			case 'top-left':
				$pos_css = "top: {$margin}px; left: {$margin}px;";
				break;
			case 'bottom-center':
				$pos_css = "bottom: {$margin}px; left: 50%; transform: translateX(-50%);";
				break;
			case 'top-center':
				$pos_css = "top: {$margin}px; left: 50%; transform: translateX(-50%);";
				break;
			case 'bottom-right':
			default:
				$pos_css = "bottom: {$margin}px; right: {$margin}px;";
				break;
		}

		$verify_url = Cloud_Client::get_cloud_url() . '/verify/' . urlencode( $ver_id );
		$bg_color   = ( 'dark' === $theme ) ? '#0F172A' : '#FFFFFF';
		$border_col = ( 'dark' === $theme ) ? '#38BDF8' : '#0056D2';
		?>
		<!-- SiteShield Verified Badge -->
		<div id="siteshield-verified-badge" style="position: fixed; z-index: 999998; <?php echo esc_attr( $pos_css ); ?> opacity: <?php echo esc_attr( $opacity ); ?>; transition: all 0.3s cubic-bezier(0.4, 0, 0.2, 1); cursor: pointer;">
			<a href="<?php echo esc_url( $verify_url ); ?>" target="_blank" rel="noopener noreferrer" title="<?php echo esc_attr__( 'Website Protegido por SiteShield — Clique para verificar certificado de segurança', 'siteshield' ); ?>" style="display: block; text-decoration: none; outline: none;">
				<svg width="<?php echo esc_attr( $dim['w'] ); ?>" height="<?php echo esc_attr( $dim['h'] ); ?>" viewBox="0 0 320 100" fill="none" xmlns="http://www.w3.org/2000/svg" style="display: block; filter: drop-shadow(0 4px 12px rgba(0, 0, 0, 0.12));">
					<!-- Borda e Fundo da Pílula -->
					<rect x="3" y="3" width="314" height="94" rx="47" fill="<?php echo esc_attr( $bg_color ); ?>" stroke="<?php echo esc_attr( $border_col ); ?>" stroke-width="6"/>
					
					<!-- Escudo Azul à Esquerda com Cadeado Branco -->
					<path d="M75 22C60 22 45 28 45 28V58C45 74 75 88 75 88C75 88 105 74 105 58V28C105 28 90 22 75 22Z" fill="#0056D2"/>
					<path d="M68 47V42C68 38.134 71.134 35 75 35C78.866 35 82 38.134 82 42V47M65 47H85C86.6569 47 88 48.3431 88 50V62C88 63.6569 86.6569 65 85 65H65C63.3431 65 62 63.6569 62 62V50C62 48.3431 63.3431 47 65 47Z" stroke="#FFFFFF" stroke-width="3" stroke-linecap="round" stroke-linejoin="round"/>
					<circle cx="75" cy="56" r="2.5" fill="#FFFFFF"/>
					
					<!-- Cápsula Preta à Direita com Texto SECURE SiteShield e 3 Estrelas -->
					<rect x="115" y="24" width="185" height="52" rx="26" fill="#000000"/>
					<text x="207" y="52" fill="#FFFFFF" font-family="-apple-system, BlinkMacSystemFont, 'Segoe UI', Roboto, sans-serif" font-size="22" font-weight="900" letter-spacing="1.5" text-anchor="middle">SECURE</text>
					<text x="207" y="66" fill="#FFFFFF" font-family="-apple-system, BlinkMacSystemFont, 'Segoe UI', Roboto, sans-serif" font-size="10.5" font-weight="600" letter-spacing="0.5" text-anchor="middle">SiteShield</text>
					
					<!-- 3 Estrelas Azuis -->
					<path d="M192 73L193.2 76.5H196.8L193.9 78.6L195 82.1L192 80L189 82.1L190.1 78.6L187.2 76.5H190.8L192 73Z" fill="#0056D2"/>
					<path d="M207 73L208.2 76.5H211.8L208.9 78.6L210 82.1L207 80L204 82.1L205.1 78.6L202.2 76.5H205.8L207 73Z" fill="#0056D2"/>
					<path d="M222 73L223.2 76.5H226.8L223.9 78.6L225 82.1L222 80L219 82.1L220.1 78.6L217.2 76.5H220.8L222 73Z" fill="#0056D2"/>
				</svg>
			</a>
		</div>
		<style>
			#siteshield-verified-badge:hover {
				transform: translateY(-3px) scale(1.03);
				opacity: 1 !important;
			}
		</style>
		<?php
	}
}
