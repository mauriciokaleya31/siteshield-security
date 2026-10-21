<?php
/**
 * View de Visitantes & Endereços IP do SiteShield
 *
 * @package Mauriciokaleya by SiteShield
 * @author Mauriciokaleya
 * @subpackage Admin\Views
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

$period    = isset( $_GET['period'] ) ? sanitize_key( wp_unslash( $_GET['period'] ) ) : '7d';
$stats     = \SiteShield\Visitors\Visitor_Tracker::get_stats( $period );
$ip_records= \SiteShield\Visitors\Visitor_Tracker::get_visitor_ips( 50, 0 );
?>
<div class="ss-content-header">
	<div class="content-header-title">
		<h2><?php esc_html_e( 'Visitors & Traffic Intelligence', 'siteshield' ); ?></h2>
		<p><?php esc_html_e( 'Monitoramento de tráfego, requisições por IP e proteção contra acessos anormais.', 'siteshield' ); ?></p>
	</div>
	<div class="content-header-actions">
		<div class="period-filter-group">
			<a href="?page=siteshield-dashboard&tab=visitors&period=24h" class="filter-btn <?php echo '24h' === $period ? 'active' : ''; ?>">24H</a>
			<a href="?page=siteshield-dashboard&tab=visitors&period=7d" class="filter-btn <?php echo '7d' === $period ? 'active' : ''; ?>">7D</a>
			<a href="?page=siteshield-dashboard&tab=visitors&period=30d" class="filter-btn <?php echo '30d' === $period ? 'active' : ''; ?>">30D</a>
			<a href="?page=siteshield-dashboard&tab=visitors&period=90d" class="filter-btn <?php echo '90d' === $period ? 'active' : ''; ?>">90D</a>
		</div>
	</div>
</div>

<!-- 4 CARTÕES DE VISITANTES -->
<div class="ss-metrics-grid">
	<div class="ss-metric-box">
		<span class="m-label"><?php esc_html_e( 'Total Requests', 'siteshield' ); ?></span>
		<strong class="m-value"><?php echo number_format_i18n( $stats['total_visitors'] ); ?></strong>
		<span class="m-sub"><?php esc_html_e( 'Total de requisições no período', 'siteshield' ); ?></span>
	</div>
	<div class="ss-metric-box">
		<span class="m-label"><?php esc_html_e( 'Unique Visitors', 'siteshield' ); ?></span>
		<strong class="m-value"><?php echo number_format_i18n( $stats['unique_visitors'] ); ?></strong>
		<span class="m-sub"><?php esc_html_e( 'Visitantes únicos distintos', 'siteshield' ); ?></span>
	</div>
	<div class="ss-metric-box">
		<span class="m-label"><?php esc_html_e( 'Page Views', 'siteshield' ); ?></span>
		<strong class="m-value"><?php echo number_format_i18n( $stats['page_views'] ); ?></strong>
		<span class="m-sub"><?php esc_html_e( 'Páginas visualizadas', 'siteshield' ); ?></span>
	</div>
	<div class="ss-metric-box">
		<span class="m-label"><?php esc_html_e( 'Blocked Requests', 'siteshield' ); ?></span>
		<strong class="m-value" style="color: #0066FF;"><?php echo number_format_i18n( $stats['blocked_requests'] ); ?></strong>
		<span class="m-sub"><?php esc_html_e( 'Ataques/bots barrados', 'siteshield' ); ?></span>
	</div>
</div>

<!-- GRÁFICO VETORIAL SVG DE VISITANTES (PERFORMANCE PURA SEM LIBS PESADAS) -->
<div class="ss-card" style="margin-top: 20px;">
	<div class="card-header">
		<h3><?php esc_html_e( 'Visitor Activity Trend', 'siteshield' ); ?></h3>
	</div>
	<div class="card-body">
		<div class="svg-chart-container">
			<svg class="visitor-trend-chart" viewBox="0 0 800 200" preserveAspectRatio="none">
				<defs>
					<linearGradient id="chartBlueGrad" x1="0" y1="0" x2="0" y2="1">
						<stop offset="0%" stop-color="#0066FF" stop-opacity="0.25"/>
						<stop offset="100%" stop-color="#0066FF" stop-opacity="0.0"/>
					</linearGradient>
				</defs>
				<!-- Linha base de grade -->
				<line x1="0" y1="50" x2="800" y2="50" stroke="#F1F5F9" stroke-width="1"/>
				<line x1="0" y1="100" x2="800" y2="100" stroke="#F1F5F9" stroke-width="1"/>
				<line x1="0" y1="150" x2="800" y2="150" stroke="#F1F5F9" stroke-width="1"/>

				<?php
				$points = $stats['chart_points'];
				$count  = count( $points );
				$max_val= 1;
				foreach ( $points as $p ) {
					if ( $p['count'] > $max_val ) {
						$max_val = $p['count'];
					}
				}

				$polyline_pts = array();
				$polygon_pts  = array( '0,180' );

				foreach ( $points as $idx => $pt ) {
					$x = $count > 1 ? ( $idx / ( $count - 1 ) ) * 800 : 400;
					$y = 180 - ( ( $pt['count'] / $max_val ) * 140 );
					$polyline_pts[] = "{$x},{$y}";
					$polygon_pts[]  = "{$x},{$y}";
				}
				$polygon_pts[] = '800,180';
				?>

				<!-- Área preenchida -->
				<polygon points="<?php echo esc_attr( implode( ' ', $polygon_pts ) ); ?>" fill="url(#chartBlueGrad)" />
				<!-- Linha do gráfico -->
				<polyline points="<?php echo esc_attr( implode( ' ', $polyline_pts ) ); ?>" fill="none" stroke="#0066FF" stroke-width="3" stroke-linecap="round" stroke-linejoin="round" />
			</svg>
			<div class="chart-x-labels">
				<?php foreach ( $points as $pt ) : ?>
					<span><?php echo esc_html( $pt['date'] ); ?></span>
				<?php endforeach; ?>
			</div>
		</div>
	</div>
</div>

<!-- TABELA DE ENDEREÇOS IP E PRIVACIDADE -->
<div class="ss-card" style="margin-top: 20px;">
	<div class="card-header">
		<h3><?php esc_html_e( 'IP Addresses & Visitor Logs', 'siteshield' ); ?></h3>
	</div>
	<div class="card-body">
		<?php if ( empty( $ip_records ) ) : ?>
			<p class="empty-text"><?php esc_html_e( 'Nenhum registro de visita no período.', 'siteshield' ); ?></p>
		<?php else : ?>
			<div class="table-responsive">
				<table class="ss-table">
					<thead>
						<tr>
							<th><?php esc_html_e( 'IP Address', 'siteshield' ); ?></th>
							<th><?php esc_html_e( 'Country', 'siteshield' ); ?></th>
							<th><?php esc_html_e( 'Requests', 'siteshield' ); ?></th>
							<th><?php esc_html_e( 'Page Views', 'siteshield' ); ?></th>
							<th><?php esc_html_e( 'First Seen', 'siteshield' ); ?></th>
							<th><?php esc_html_e( 'Last Seen', 'siteshield' ); ?></th>
							<th><?php esc_html_e( 'Status', 'siteshield' ); ?></th>
						</tr>
					</thead>
					<tbody>
						<?php foreach ( $ip_records as $rec ) : ?>
							<tr>
								<td><code><?php echo esc_html( $rec->ip_address ); ?></code></td>
								<td><?php echo esc_html( $rec->country ); ?></td>
								<td><?php echo esc_html( $rec->requests_count ); ?></td>
								<td><?php echo esc_html( $rec->page_views ); ?></td>
								<td><?php echo esc_html( date( 'd/m H:i', strtotime( $rec->first_seen ) ) ); ?></td>
								<td><?php echo esc_html( date( 'd/m H:i', strtotime( $rec->last_seen ) ) ); ?></td>
								<td>
									<span class="ss-badge ss-badge-neutral"><?php echo $rec->is_suspicious ? esc_html__( 'Suspeito', 'siteshield' ) : esc_html__( 'Normal', 'siteshield' ); ?></span>
								</td>
							</tr>
						<?php endforeach; ?>
					</tbody>
				</table>
			</div>
		<?php endif; ?>
	</div>
</div>
