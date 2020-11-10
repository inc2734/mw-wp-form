<?php
/**
 * @package mw-wp-form
 * @author inc2734
 * @license GPL-2.0+
 */
?>

<b class="add-btn"><?php esc_html_e( 'Add Validation rule', 'mw-wp-form' ); ?></b>
<div class="repeatable-boxes">
	<?php
	foreach ( $validation as $key => $value ) :
		$value = array_merge( $validation_keys, $value );
		?>
	<div class="repeatable-box"
		<?php
		if ( 0 === $key ) :
			?>
		style="display:none"<?php endif; ?>>
		<div class="sortable-icon-handle"></div>
		<div class="remove-btn"><b>×</b></div>
		<div class="open-btn"><span><?php echo esc_attr( $value['target'] ); ?></span><b>▼</b></div>
		<div class="repeatable-box-content">
			<?php esc_html_e( 'The key which applies validation', 'mw-wp-form' ); ?>：<input type="text" class="targetKey" value="<?php echo esc_attr( $value['target'] ); ?>" name="<?php echo MWF_Config::NAME; ?>[validation][<?php echo esc_attr( $key ); ?>][target]" />
			<table border="0" cellpadding="0" cellspacing="0">
				<tr>
					<td colspan="2">
						<?php foreach ( $validation_rules as $validation_rule => $instance ) : ?>
							<?php $instance->admin( $key, $value ); ?>
						<?php endforeach; ?>
					</td>
				</tr>
			</table>
		<!-- end .repeatable-box-content --></div>
	<!-- end .repeatable-box --></div>
	<?php endforeach; ?>
<!-- end .repeatable-boxes --></div>
