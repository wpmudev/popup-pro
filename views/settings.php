<?php
/**
 * Display the popup settings page.
 */

$loading_methods = array();

$loading_methods[] = (object) array(
	'id'    => 'footer',
	'label' => __( 'Page Footer', PO_LANG ),
	'info'  => __(
		'The Pop Up is included as part of the page html (no Ajax call).',
		PO_LANG
		),
);

$loading_methods[] = (object) array(
	'id'    => 'ajax',
	'label' => __( 'WordPress Ajax', PO_LANG ),
	'info'  => __(
		'The Pop Up is loaded separately from the page ' .
		'(via a normal WordPress Ajax call). ' .
		'This is the best option if you are running a caching system',
		PO_LANG
	),
);

$loading_methods[] = (object) array(
	'id'    => 'front',
	'label' => __( 'Custom Ajax', PO_LANG ),
	'info'  => __(
		'The Pop Up is loaded separately from the page '. // ,
		'(via custom front end Ajax call)',
		PO_LANG
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

// Theme compatibility.
$theme_compat = IncPopupAddon_HeaderFooter::check();
$theme_class = $theme_compat->okay ? 'msg-ok' : 'msg-err';

// Add-Ons
if ( IncPopupAddon_GeoDB::table_exists() ) {
	$geo_class = '';
	$geo_readonly = '';
	$geo_msg = '';
} else {
	$geo_class = 'inactive';
	$geo_readonly = 'readonly="readonly"';
	$settings['geo_db'] = false;
	$geo_msg = '<p>' . __(
		'<strong>Note</strong>: Cannot be used, because no geo-data table ' .
		'was found in local database!', PO_LANG
	) . '</p>';
}

$rules = IncPopup::get_rules();
$rule_headers = array(
	'name'  => 'Name',
	'desc'  => 'Description',
	'rules' => 'Rules',
);
$ordered_rules = array();

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

					<?php /* === LOADING METHOD === */ ?>
					<tr valign="top">
						<th scope="row">
							<?php _e( 'Pop Up loaded using', PO_LANG ); ?>
						</th>
						<td>
							<select name="po_option[loadingmethod]" id="loadingmethod">
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
										<em><?php echo '' . $item->info; ?>.
									</em></li>
								<?php endforeach; ?>
							</ul>
						</td>
					</tr>

					<?php /* === GEO DB SETTING === */ ?>
					<tr>
						<th><?php _e( 'Country Lookup', PO_LANG ); ?></th>
						<td>

							<label class="<?php echo esc_attr( $geo_class );?>" >
								<input type="checkbox"
									name="po_option[geo_db]"
									<?php checked( $settings['geo_db'] ); ?>
									<?php echo '' . $geo_readonly; ?> />
								<?php _e(
									'Use a local IP Cache table instead of a ' .
									'webservice to resolve IP Addresses to a ' .
									'country-code.', PO_LANG
								); ?>
							</label>
							<?php echo '' . $geo_msg; ?>
							<p><em><?php _e(
								'This option is relevant for the Pop Up ' .
								'conditions "Visitor Location" (see below).',
								PO_LANG
							); ?></em></p>
						</td>
					</tr>
				</tbody>
				</table>
			</div>
		</div>

		<?php if ( 'footer' == $cur_method ) : ?>
		<div class="postbox">
			<h3 class="hndle" style="cursor:auto;">
				<span><?php _e( 'Theme compatibility', PO_LANG ); ?></span>
			</h3>

			<div class="inside">
				<?php _e(
					'Here you can see if your theme is compatible with the ' .
					'"Page Footer" loading method.', PO_LANG
				); ?>
				<div class="<?php echo esc_attr( $theme_class ); ?>">
					<?php foreach ( $theme_compat->msg as $row ) {
						echo '<p>' . $row . '</p>';
					} ?>
				</div>
			</div>
		</div>
		<?php endif; ?>

		<p class="submit">
			<button class="button-primary">
				<?php _e( 'Save All Changes', PO_LANG ) ?>
			</button>
		</p>

		<h2><?php _e( 'Available Conditions', PO_LANG ); ?></h2>

		<?php /* === ACTIVE RULES === */ ?>
		<table class="widefat tbl-addons">
			<?php foreach ( array( 'thead', 'tfoot' ) as $tag ) : ?>
				<<?php echo esc_attr( $tag ); ?>>
				<tr>
					<th class="manage-column column-cb check-column" id="cb" scope="col">
						<input type="checkbox" />
					</th>
					<th class="manage-column column-name" scope="col">
						<?php _e( 'Name', PO_LANG ); ?>
					</th>
					<th class="manage-column column-items" scope="col">
						<?php _e( 'Activated Rules', PO_LANG ); ?>
					</th>
				</tr>
				</<?php echo esc_attr( $tag ); ?>>
			<?php endforeach; ?>

			<?php foreach ( $rules as $rule ) {
				$data = get_file_data(
					PO_INC_DIR . 'rules/' . $rule,
					$rule_headers,
					'popup-rule'
				);
				$is_active = ( in_array( $rule, $settings['rules'] ) );
				if ( empty( $data['name'] ) ) { continue; }

				$name = __( trim( $data['name'] ), PO_LANG );

				$ordered_rules[ $name ] = $data;
				$ordered_rules[ $name ]['key'] = $rule;
				$ordered_rules[ $name ]['name'] = $name;
				$ordered_rules[ $name ]['desc'] = __( trim( $data['desc'] ), PO_LANG );
			} ?>
			<?php ksort( $ordered_rules ); ?>

			<?php foreach ( $ordered_rules as $data ) {
				// Ignore Addons that have no name.
				$data['rules'] = explode( ',', $data['rules'] );
				$rule_id = 'po-rule-' . sanitize_html_class( $data['key'] );
				?>
				<tr valign="top">
					<th class="check-column" scope="row">
						<input type="checkbox"
							id="<?php echo esc_attr( $rule_id ); ?>"
							name="po_option[rules][<?php echo esc_attr( $data['key'] ); ?>]"
							<?php checked( $is_active ); ?>/>
					</th>
					<td class="column-name">
						<label for="<?php echo esc_attr( $rule_id ); ?>">
							<strong><?php echo esc_html( $data['name'] ); ?></strong>
						</label>
						<div><em><?php echo '' . $data['desc']; ?></em></div>
					</td>
					<td class="column-items">
					<?php foreach ( $data['rules'] as $rule_name ) : ?>
						<code><?php _e( trim( $rule_name ), PO_LANG ); ?></code><br />
					<?php endforeach; ?>
					</td>
				</tr>
			<?php } ?>
		</table>

		<p class="submit">
			<button class="button-primary">
				<?php _e( 'Save All Changes', PO_LANG ) ?>
			</button>
		</p>

	</form>
	</div>

</div> <!-- wrap -->
