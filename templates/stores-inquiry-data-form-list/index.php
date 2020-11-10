<?php
/**
 * @package mw-wp-form
 * @author inc2734
 * @license GPL-2.0+
 */
?>

<div class="wrap">
	<h2><?php esc_html_e( 'Inquiry data', 'mw-wp-form' ); ?></h2>
	<p>
		<?php esc_html_e( 'You can see the inquiry data that are saved in the database by clicking on the link below.', 'mw-wp-form' ); ?>
	</p>
	<table class="wp-list-table widefat fixed" cellspacing="0">
		<thead>
			<th class="<?php echo MWF_Config::NAME; ?>-table-title"><?php esc_html_e( 'Form title', 'mw-wp-form' ); ?></th>
			<th class="<?php echo MWF_Config::NAME; ?>-table-chart"><?php esc_html_e( 'Display Chart', 'mw-wp-form' ); ?></th>
			<th class="<?php echo MWF_Config::NAME; ?>-table-count"><?php esc_html_e( 'The number of inquiries', 'mw-wp-form' ); ?></th>
			<th class="<?php echo MWF_Config::NAME; ?>-table-date"><?php esc_html_e( 'Updated date', 'mw-wp-form' ); ?></th>
			<th class="<?php echo MWF_Config::NAME; ?>-table-date"><?php esc_html_e( 'Created date', 'mw-wp-form' ); ?></th>
		</thead>
		<tbody>
			<?php
			$i = 0;
			foreach ( $form_list as $post_type => $values ) :
				$i ++;
				?>
			<tr
				<?php
				if ( 1 === $i % 2 ) {
					echo 'class="alternate"';}
				?>
			>
				<td class="<?php echo MWF_Config::NAME; ?>-table-title"><?php echo esc_html( $values['title'] ); ?></td>
				<td class="<?php echo MWF_Config::NAME; ?>-table-chart"><a href="<?php echo admin_url( 'edit.php?post_type=' . MWF_Config::NAME . '&page=' . MWF_Config::NAME . '-chart&formkey=' . $post_type ); ?>"><?php esc_html_e( 'Display Chart', 'mw-wp-form' ); ?></td>
				<td class="<?php echo MWF_Config::NAME; ?>-table-count"><a href="<?php echo admin_url( 'edit.php?post_type=' . $post_type ); ?>"><?php echo esc_html( $values['count'] ); ?> <?php esc_html_e( 'cases', 'mw-wp-form' ); ?></a></td>
				<td class="<?php echo MWF_Config::NAME; ?>-table-date"><?php echo esc_html( $values['modified_datetime'] ); ?></td>
				<td class="<?php echo MWF_Config::NAME; ?>-table-date"><?php echo esc_html( $values['created_datetime'] ); ?></td>
			</tr>
		<?php endforeach; ?>
		</tbody>
	</table>
<!-- end .wrap --></div>
