<?php
/**
 * Display the popup settings page.
 */

$loading_methods = array(
	(object) array(
		'id'    => 'footer',
		'label' => 'Page Footer',
		'info'  => 'The Pop Up is included as part of the page html.',
	),
	(object) array(
		'id'    => 'ajax',
		'label' => 'External Load',
		'info'  =>
			'The Pop Up is loaded separately from the page ' .
			'(via a normal WordPress Ajax call). ' .
			'This is the best option if you are running a caching system',
	),
	(object) array(
		'id'    => 'front',
		'label' => 'Custom Load',
		'info'  =>
			'The Pop Up is loaded separately from the page '. // ,
			'(via custom front end Ajax call)',
	),
);

/**
 * Allow addons to register additional loading methods.
 *
 * @var array
 */
$loading_methods = apply_filters( 'popup-settings-loading-method', $loading_methods );

$settings = IncPopupDatabase::get_settings();
$cur_method = @$settings['loadingmethod'];

$form_url = remove_query_arg( array( 'message', 'action', '_wpnonce' ) );

?>
<div class="wrap nosubsub">

	<h2><?php _e( 'Pop Up Settings', PO_LANG ); ?></h2>

	<div id="poststuff" class="metabox-holder m-settings">
	<form method="post" action="<?php echo esc_url( $form_url ); ?>">

		<input type="hidden" name="action" value="updatesettings" />

		<?php wp_nonce_field( 'update-popup-settings' ); ?>

		<div class="postbox">
			<h3 class="hndle" style="cursor:auto;">
				<span><?php _e( 'Pop Up loading method', PO_LANG ); ?></span>
			</h3>

			<div class="inside">
				<p><?php _e(
					'Select the loading method you want to use for your ' .
					'Pop Ups.', PO_LANG
				); ?></p>

				<table class="form-table">
				<tbody>
					<tr valign="top">
						<th scope="row">
							<?php _e( 'Pop Up loaded using', PO_LANG ); ?>
						</th>
						<td>
							<select name="loadingmethod" id="loadingmethod">
								<?php foreach ( $loading_methods as $item ) : ?>
									<option
										value="<?php echo esc_attr( $item->id ); ?>"
										<?php selected( $cur_method, $item->id ); ?>>
										<?php _e( $item->label, PO_LANG ); ?>
									</option>
								<?php endforeach; ?>
							</select>

							<ul>
								<?php foreach ( $loading_methods as $item ) : ?>
									<li>
										<?php _e( $item->label, PO_LANG ); ?>:
										<em><?php _e( $item->info, PO_LANG ); ?>.
									</em></li>
								<?php endforeach; ?>
							</ul>
						</td>
					</tr>
				</tbody>
				</table>
			</div>
		</div>

		<p class="submit">
			<button class="button-primary">
				<?php _e( 'Save Changes', PO_LANG ) ?>
			</button>
		</p>

	</form>
	</div>
</div> <!-- wrap -->
