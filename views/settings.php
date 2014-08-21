<?php
/**
 * Display the popup settings page.
 */

global $shortcode_tags;

$loading_methods = array();

$loading_methods[] = (object) array(
	'id'    => 'footer',
	'label' => __( 'Page Footer', PO_LANG ),
	'info'  => __(
		'Include PopUp as part of your site\'s HTML (no AJAX call).',
		PO_LANG
		),
);

$loading_methods[] = (object) array(
	'id'    => 'ajax',
	'label' => __( 'WordPress AJAX', PO_LANG ),
	'info'  => __(
		'Load PopUp separately from the page via a WordPress AJAX call. ' .
		'This is the best option if you use caching.',
		PO_LANG
	),
);

$loading_methods[] = (object) array(
	'id'    => 'front',
	'label' => __( 'Custom AJAX', PO_LANG ),
	'info'  => __(
		'Load PopUp separately from the page via a custom front-end AJAX call.',
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
$shortcodes = array();
// Add Admin-Shortcodes to the list.
foreach ( $shortcode_tags as $code => $handler ) {
	@$shortcodes[ $code ] .= 'sc-admin ';
}
// Add Front-End Shortcodes to the list.
foreach ( $theme_compat->shortcodes as $code ) {
	@$shortcodes[ $code ] .= 'sc-front ';
}


// Add-Ons
if ( IncPopupAddon_GeoDB::table_exists() ) {
	$geo_readonly = '';
	$geo_msg = '';
	$no_geo = false;
} else {
	$no_geo = true;
	$geo_readonly = 'disabled="disabled"'; // Checkboxes cannot be "readonly"...
	$settings['geo_db'] = false;
	$geo_msg = '<p class="locked-msg">' .
		sprintf(
			__(
				'<strong>Note</strong>: This option is unavailable because a ' .
				'geo-data table was not found in your database. For details, ' .
				'read the "Using a Local Geo-Database" in the ' .
				'<a href="%1$s" target="_blank">PopUp usage guide</a>.',
				PO_LANG
			),
			'http://premium.wpmudev.org/project/the-pop-over-plugin/#usage'
		).
	'</p>';
}

$rules = IncPopup::get_rules();
$rule_headers = array(
	'name'  => 'Name',
	'desc'  => 'Description',
	'rules' => 'Rules',
	'limit' => 'Limit',
);
$ordered_rules = array();

?>
<div class="wrap nosubsub">

	<h2><?php _e( 'PopUp Settings', PO_LANG ); ?></h2>

	<div id="poststuff" class="metabox-holder m-settings">
	<form method="post" action="<?php echo esc_url( $form_url ); ?>">

		<input type="hidden" name="action" value="updatesettings" />

		<?php wp_nonce_field( 'update-popup-settings' ); ?>

		<div class="wpmui-box static">
			<h3>
				<span><?php _e( 'PopUp Loading Method', PO_LANG ); ?></span>
			</h3>

			<div class="inside">
				<p><?php _e(
					'Select how you would like to load PopUp.', PO_LANG
				); ?></p>

				<table class="form-table">
				<tbody>

					<?php /* === LOADING METHOD === */ ?>
					<tr valign="top">
						<th scope="row">
							<?php _e( 'Load PopUp using', PO_LANG ); ?>
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
										<?php if ( $cur_method == $item->id ) : ?>
											<strong><i class="dashicons dashicons-yes"
											style="margin-left:-20px">
											</i><?php _e( $item->label, PO_LANG ); ?></strong>:
										<?php else : ?>
											<?php _e( $item->label, PO_LANG ); ?>:
										<?php endif; ?>
										<em><?php echo '' . $item->info; ?>
									</em></li>
								<?php endforeach; ?>
							</ul>
						</td>
					</tr>

					<?php /* === GEO DB SETTING === */ ?>
					<tr class="<?php echo esc_attr( $no_geo ? 'locked' : '' ); ?>">
						<th><?php _e( 'Country Lookup', PO_LANG ); ?></th>
						<td>
							<label>
								<input type="checkbox"
									name="po_option[geo_db]"
									<?php checked( $settings['geo_db'] ); ?>
									<?php echo '' . $geo_readonly; ?> />
								<?php _e(
									'Use a local IP cache table instead of a web ' .
									'service to resolve IP addresses to a ' .
									'country code.', PO_LANG
								); ?>
							</label>
							<p><em><?php _e(
								'This option is relevant for the ' .
								'"Visitor Location" condition.',
								PO_LANG
							); ?></em></p>
							<?php echo '' . $geo_msg; ?>
						</td>
					</tr>
				</tbody>
				</table>
			</div>
		</div>

		<?php if ( 'footer' == $cur_method ) : ?>
		<div class="wpmui-box <?php echo esc_attr( $theme_compat->okay ? 'closed' : '' ); ?>">
			<h3>
				<a href="#" class="toggle" title="<?php _e( 'Click to toggle' ); ?>"><br></a>
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

		<div class="wpmui-box closed">
			<h3>
				<a href="#" class="toggle" title="<?php _e( 'Click to toggle' ); ?>"><br></a>
				<span><?php _e( 'Supported Shortcodes', PO_LANG ); ?></span>
			</h3>

			<div class="inside">
				<?php _e(
					'You can use all your shortcodes inside the PopUp contents, ' .
					'however some Plugins or Themes might provide shortcodes that ' .
					'only work with the loading method "Page Footer".<br /> ' .
					'This list explains which shortcodes can be used with each ' .
					'loading method:', PO_LANG
				); ?>
				<?php if ( IncPopup::use_global() ) : ?>
					<p><em>
					<?php _e(
					'Important notice for shortcodes in <strong>Global ' .
					'PopUps</strong>:<br />' .
					'Shortcodes can be provided by a plugin or theme, so ' .
					'each blog can have a different list of shortcodes. The ' .
					'following list is valid for the current blog only!', PO_LANG
					); ?>
					</em></p>
				<?php endif; ?>
				<table class="widefat tbl-shortcodes load-<?php echo esc_attr( $cur_method ); ?>">
					<thead>
						<tr>
							<th width="40%">
								<div>
								<?php _e( 'Shortcode', PO_LANG ); ?>
								</div>
							</th>
							<th class="flag load-footer">
								<div data-tooltip="<?php _e( 'Loading method \'Page Footer\'', PO_LANG ); ?>">
								<?php _e( 'Page Footer', PO_LANG ); ?>
								</div>
							</th>
							<th class="flag load-ajax load-front">
								<div data-tooltip="<?php _e( 'Loading method \'WordPress AJAX\' and \'Custom AJAX\'', PO_LANG ); ?>">
								<?php _e( 'AJAX', PO_LANG ); ?>
								</div>
							</th>
							<th class="flag load-anonymous">
								<div data-tooltip="<?php _e( 'Loading method \'Anonymous Script\'', PO_LANG ); ?>">
								<?php _e( 'Script', PO_LANG ); ?>
								</div>
							</th>
							<th class="flag">
								<div data-tooltip="<?php _e( 'When opening a PopUp-Preview in the Editor', PO_LANG ); ?>">
								<?php _e( 'Preview', PO_LANG ); ?>
								</div>
							</th>
						</tr>
					</thead>
					<tbody>
						<?php foreach ( $shortcodes as $code => $classes ) : ?>
							<tr class="shortcode <?php echo esc_attr( $classes ); ?>">
								<td><code>[<?php echo esc_html( $code ); ?>]</code></td>
								<td class="flag sc-front load-footer"><i class="icon dashicons"></i></td>
								<td class="flag sc-admin load-ajax load-front"><i class="icon dashicons"></i></td>
								<td class="flag sc-admin load-anonymous"><i class="icon dashicons"></i></td>
								<td class="flag sc-admin"><i class="icon dashicons"></i></td>
							</tr>
						<?php endforeach; ?>
					</tbody>
				</table>
			</div>
		</div>

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
				$data['limit'] = explode( ',', @$data['limit'] );
				$data['limit'] = array_map( 'trim', $data['limit'] );

				$name = __( trim( $data['name'] ), PO_LANG );

				$ordered_rules[ $name ] = $data;
				$ordered_rules[ $name ]['key'] = $rule;
				$ordered_rules[ $name ]['name'] = $name;
				$ordered_rules[ $name ]['active'] = $is_active;
				$ordered_rules[ $name ]['desc'] = __( trim( $data['desc'] ), PO_LANG );

				if ( PO_VERSION != 'pro' && in_array( 'pro', $data['limit'] ) ) {
					$ordered_rules[ $name ]['disabled'] = __( 'Available in the PRO version', PO_LANG );
				} else if ( IncPopup::use_global() && in_array( 'no global', $data['limit'] ) ) {
					$ordered_rules[ $name ]['disabled'] = __( 'Not available for global PopUps', PO_LANG );
				} else if ( ! IncPopup::use_global() && in_array( 'global', $data['limit'] ) ) {
					$ordered_rules[ $name ]['disabled'] = true;
				} else {
					$ordered_rules[ $name ]['disabled'] = false;
				}
			} ?>
			<?php ksort( $ordered_rules ); ?>

			<?php foreach ( $ordered_rules as $data ) {
				// Ignore Addons that have no name.
				$data['rules'] = explode( ',', $data['rules'] );
				$rule_id = 'po-rule-' . sanitize_html_class( $data['key'] );
				if ( true === $data['disabled'] ) { continue; }
				?>
				<tr valign="top" class="<?php echo esc_attr( $data['disabled'] ? 'locked' : '' ); ?>">
					<th class="check-column" scope="row">
						<?php if ( false == $data['disabled'] ) : ?>
						<input type="checkbox"
							id="<?php echo esc_attr( $rule_id ); ?>"
							name="po_option[rules][<?php echo esc_attr( $data['key'] ); ?>]"
							<?php checked( $data['active'] ); ?>
							/>
						<?php endif; ?>
					</th>
					<td class="column-name">
						<label for="<?php echo esc_attr( $rule_id ); ?>">
							<strong><?php echo esc_html( $data['name'] ); ?></strong>
						</label>
						<div><em><?php echo '' . $data['desc']; ?></em></div>
						<?php if ( $data['disabled'] ) : ?>
							<div class="locked-msg">
								<?php echo '' . $data['disabled']; ?>
							</div>
						<?php endif; ?>
					</td>
					<td class="column-items">
					<?php foreach ( $data['rules'] as $rule_name ) : ?>
						<?php $rule_name = trim( $rule_name ); ?>
						<?php if ( empty( $rule_name ) ) { continue; } ?>
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
