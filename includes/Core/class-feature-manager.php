<?php
/**
 * Gerenciador Central de Funcionalidades e Feature Flags por Plano
 * Edição Demo Gratuita (Primeira Versão) — 100% Desbloqueada
 *
 * @package SiteShield_Security
 * @author Maurício Kaleya <kaleyapt@gmail.com>
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
			self::PLAN_FREE     => 4, // Na Edição Demo (1ª Versão), o plano FREE tem acesso total (nível máximo)
			self::PLAN_PRO      => 4,
			self::PLAN_BUSINESS => 4,
			self::PLAN_AGENCY   => 4,
		);
		$p = strtoupper( trim( (string) $plan ) );
		return isset( $levels[ $p ] ) ? $levels[ $p ] : 4;
	}

	/**
	 * Retorna o slug do plano atualmente ativo
	 *
	 * @return string
	 */
	public static function get_current_plan() {
		$license = Cloud_Client::get_license_info();
		if ( ! empty( $license['plan'] ) ) {
			return strtoupper( $license['plan'] );
		}
		return self::PLAN_FREE;
	}

	/**
	 * Verifica se o website tem permissão para utilizar uma funcionalidade.
	 * Na Primeira Versão (Modelo Demo Gratuito), todas as funcionalidades estão 100% liberadas.
	 *
	 * @param string $feature_slug
	 * @return bool
	 */
	public static function can_use( $feature_slug ) {
		return true;
	}

	/**
	 * Retorna o plano mínimo exigido para cada funcionalidade (todas liberadas na versão Demo Free)
	 *
	 * @param string $feature_slug
	 * @return string
	 */
	public static function get_required_plan( $feature_slug ) {
		return self::PLAN_FREE;
	}

	/**
	 * Renderiza um banner informativo caso chamado diretamente
	 *
	 * @param string $feature_slug
	 * @param string $feature_title
	 * @param string $description
	 */
	public static function render_premium_lock_box( $feature_slug, $feature_title = '', $description = '' ) {
		// Na versão Demo Gratuita (1ª Versão), nenhum recurso é bloqueado.
		return;
	}
}
