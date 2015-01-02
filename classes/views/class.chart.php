<?php
/**
 * Name       : MW WP Form Chart View
 * Version    : 1.0.0
 * Author     : Takashi Kitajima
 * Author URI : http://2inc.org
 * Created    : January 2, 2015
 * Modified   : 
 * License    : GPLv2
 * License URI: http://www.gnu.org/licenses/gpl-2.0.html
 */
class MW_WP_Form_Chart_View extends MW_WP_Form_View {
	
	/**
	 * admin_print_styles
	 */
	public function admin_print_styles() {
		?>
		<style>
		#menu-posts-mw-wp-form .wp-submenu li a[href$="-chart"] {
			display: none;
		}
		</style>
		<?php
	}
	
	/**
	 * index
	 */
	public function index() {
		if ( !$this->get( 'is_chart' ) ) {
			return;
		}

		$post_type    = $this->get( 'post_type' );
		$option_group = $this->get( 'option_group' );
		$form_posts = get_posts( array(
			'post_type'      => $post_type,
			'posts_per_page' => -1,
		) );

		$custom_keys = array();
		foreach ( $form_posts as $post ) {
			$post_custom_keys = get_post_custom_keys( $post->ID );
			if ( is_array( $post_custom_keys ) ) {
				foreach ( $post_custom_keys as $post_custom_key ) {
					if ( preg_match( '/^_/', $post_custom_key ) ) {
						continue;
					}
					$post_meta = get_post_meta( $post->ID, $post_custom_key, true );
					$custom_keys[$post_custom_key][$post_meta][] = $post->ID;
				}
			}
		}

		$postdata = array();
		$option   = get_option( MWF_Config::NAME . '-chart-' . $post_type );
		if ( is_array( $option ) && isset( $option['chart'] ) && is_array( $option['chart'] ) ) {
			$postdata = $option['chart'];
		}

		$default_keys = array(
			'target'    => '',
			'separator' => '',
			'chart'     => '',
		);
		// 空の隠れフィールド（コピー元）を挿入
		array_unshift( $postdata, $default_keys );
		?>
<div class="wrap">
	<?php $post_id = preg_replace( '/^(.+_)(\d+)$/', '$2', $post_type ); ?>
	<h2>
		<?php esc_html_e( 'Chart', MWF_Config::DOMAIN ); ?>
		:
		<?php echo esc_html( get_the_title( $post_id ) ); ?>
	</h2>
	<form method="post" action="options.php">
		<?php
		settings_fields( $option_group );
		do_settings_sections( $option_group );
		?>
		<div id="<?php echo esc_attr( MWF_Config::NAME . '_chart' ); ?>" class="postbox">
			<div class="inside">
				<b class="add-btn"><?php esc_html_e( 'Add Chart', MWF_Config::DOMAIN ); ?></b>
				<div class="repeatable-boxes">
					<?php foreach ( $postdata as $key => $value ) :  ?>
					<div class="repeatable-box" <?php if ( $key === 0 ) : ?>style="display:none"<?php endif; ?>>
						<div class="remove-btn"><b>×</b></div>
						<div class="open-btn"><span><?php echo esc_html( $value['target'] ); ?></span><b>▼</b></div>
						<div class="repeatable-box-content">
							<?php esc_html_e( 'Item that create chart', MWF_Config::DOMAIN ); ?>
							<select class="targetKey" name="<?php echo esc_attr( sprintf( '%s-chart-%s[chart][%s][target]', MWF_Config::NAME, $post_type, $key ) ); ?>">
								<option value=""><?php esc_html_e( 'Select this.', MWF_Config::DOMAIN ); ?></option>
								<?php foreach ( $custom_keys as $custom_key_name => $custom_key_value ) : ?>
								<option value="<?php echo esc_attr( $custom_key_name ); ?>" <?php selected( $value['target'], $custom_key_name ); ?>><?php echo esc_html( $custom_key_name ); ?></option>
								<?php endforeach; ?>
							</select>
							<br />
							<?php esc_html_e( 'Chart type', MWF_Config::DOMAIN ); ?>
							<select name="<?php echo esc_attr( sprintf( '%s-chart-%s[chart][%s][chart]', MWF_Config::NAME, $post_type, $key ) ); ?>">
								<?php
								$chart_options = array(
									'pie' => esc_html__( 'Pie chart', MWF_Config::DOMAIN ),
									'bar' => esc_html__( 'Bar chart', MWF_Config::DOMAIN ),
								);
								foreach ( $chart_options as $chart_option_key => $chart_option ) {
									printf(
										'<option value="%s" %s>%s</option>',
										esc_attr( $chart_option_key ),
										selected( $value['chart'], $chart_option_key, false ),
										esc_html( $chart_option )
									);
								}
								?>
							</select>
							<br />
							<?php esc_html_e( 'Separator string (If the check box. If the separator attribute is not set to ",")', MWF_Config::DOMAIN ); ?>
							<input type="text" name="<?php echo esc_attr( sprintf( '%s-chart-%s[chart][%s][separator]', MWF_Config::NAME, $post_type, $key ) ); ?>" value="<?php echo esc_attr( $value['separator'] ); ?>" size="5" />
						<!-- end .repeatable-box-content --></div>
					<!-- end .repeatable-box --></div>
					<?php endforeach; ?>
				<!-- end .repeatable-boxes --></div>
				<input type="hidden" name="<?php echo esc_attr( sprintf( '%s-formkey', MWF_Config::NAME ) ); ?>" value="<?php echo esc_attr( $post_type ); ?>" />
				<?php submit_button(); ?>
			<!-- end .inside --></div>
		<!-- end #mw-wp-form_chart --></div>
	</form>

	<?php
	foreach ( $postdata as $postdata_key => $chart ) {
		if ( !isset( $custom_keys[$chart['target']] ) ) {
			unset( $postdata[$postdata_key] );
			continue;
		}
		printf(
			'<h3>%s <span style="font-weight:normal;font-size:14px">( %s: %d )</span></h3>
			<div class="%s" style="width: 100%%; max-width: 800px"></div>',
			esc_html( $chart['target'] ),
			esc_html__( 'The number of inquiries', MWF_Config::DOMAIN ),
			count( $form_posts ),
			esc_attr( MWF_Config::NAME . '-chart-div-' . $postdata_key )
		);
	}

	$chart_data = array();
	foreach ( $postdata as $postdata_key => $chart ) {
		$data = array();
		$raw_data = array();
		foreach ( $custom_keys[$chart['target']] as $item => $values ) {
			if ( $chart['separator'] && strstr( $item, $chart['separator'] ) ) {
				$item = explode( $chart['separator'] , $item );
			}
			if ( is_array( $item ) ) {
				foreach ( $item as $_item ) {
					if ( $_item === '' ) {
						$_item = '(Empty)';
					}
					if ( empty( $raw_data[$_item] ) ) {
						$raw_data[$_item] = count( $values );
					} else {
						$raw_data[$_item] += count( $values );
					}
				}
			} else {
				if ( $item === '' ) {
					$item = '(Empty)';
				}
				if ( empty( $raw_data[$item] ) ) {
					$raw_data[$item] = count( $values );
				} else {
					$raw_data[$item] += count( $values );
				}
			}
		}
		$data[] = array( '', '' );
		foreach ( $raw_data as $raw_data_key => $raw_data_value ) {
			if ( $chart['chart'] === 'bar' ) {
				$value = $raw_data_value / count( $form_posts );
			} else {
				$value = $raw_data_value;
			}
			$data[] = array(
				$raw_data_key,
				$value,
			);
		}
		$chart_data[$postdata_key] = array(
			'chart' => $chart['chart'],
			'data'  => $data,
		);
	}
	?>
	<script>
	google.load( 'visualization', 1, { packages:['corechart'] } );
	google.setOnLoadCallback( mwformDrawCharts );
	function mwformDrawCharts() {
		jQuery( function( $ ) {
			<?php foreach ( $chart_data as $postdata_key => $chart ) : ?>
			$( '.<?php echo esc_js( MWF_Config::NAME . '-chart-div-' . $postdata_key ); ?>' )
				.mw_wp_form_google_chart( {
					chart: <?php echo json_encode( $chart['chart'] ); ?>,
					data : <?php echo json_encode( $chart['data'] ); ?>
				} );
			<?php endforeach; ?>
		} );
	}
	</script>
<!-- end .wrap --></div>
		<?php
	}
}