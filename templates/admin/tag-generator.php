<?php
/**
 * @package mw-wp-form
 * @author inc2734
 * @license GPL-2.0+
 */

do_action( 'mwform_tag_generator_dialog' );

$types = array(
	'input'        => 'input',
	'select'       => 'select',
	'button'       => 'button',
	'input_button' => 'input_button',
	'error'        => 'error',
	'other'        => 'other',
);
$group = apply_filters( 'mwform_tag_generator_group', $types );

$labels = array(
	'input'        => __( 'Input fields', 'mw-wp-form' ),
	'select'       => __( 'Select fields', 'mw-wp-form' ),
	'button'       => __( 'Button fields (button)', 'mw-wp-form' ),
	'input_button' => __( 'Button fields (input)', 'mw-wp-form' ),
	'error'        => __( 'Error fields', 'mw-wp-form' ),
	'other'        => __( 'Other fields', 'mw-wp-form' ),
);
$labels = apply_filters( 'mwform_tag_generator_labels', $labels );
?>
<div class="add-mwform-btn">
	<select>
		<option value=""><?php echo esc_html_e( 'Select this.', 'mw-wp-form' ); ?></option>
		<?php foreach ( $group as $type ) : ?>
			<?php
			$label = isset( $labels[ $type ] ) ? $labels[ $type ] : $type;
			$tag   = 'other' === $type ? 'mwform_tag_generator_option' : 'mwform_tag_generator_' . $type . '_option';
			?>
			<optgroup label="<?php echo esc_attr( $label ); ?>">
				<?php do_action( $tag ); ?>
			</optgroup>
		<?php endforeach; ?>
	</select>
	<span class="button"><?php esc_html_e( 'Add form tag', 'mw-wp-form' ); ?></span>
</div>
