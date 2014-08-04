<?php
/**
 * Display the addon page.
 */

// Columns displayed in the Addon table.
$columns = array(
	'name'   => __( 'Add-on Name', PO_LANG ),
	'type'   => __( 'Type', PO_LANG ),
	'desc'   => __( 'Description', PO_LANG ),
);

// Fields extracted from each Addon.
$default_headers = array(
	'Name'        => 'Addon Name',
	'Author'      => 'Author',
	'Description' => 'Description',
	'AuthorURI'   => 'Author URI',
	'Type'        => 'Type',
);

// List of all Addons.
$addons = IncPopup::get_addons();

// List of active Addons.
$active = IncPopupDatabase::get_active_addons();

$has_addons = false;
$columncount = count( $columns ) + 1;
$form_url = remove_query_arg( array( 'message', 'action', 'addon', '_wpnonce', 'count' ) );

?>
<div class="wrap">
	<h2><?php _e( 'Edit Add-ons', PO_LANG  ); ?></h2>

	<form method="post" id="posts-filter" action="<?php echo esc_url( $form_url ); ?>">

		<div class="tablenav">
			<div class="alignleft actions bulkactions">
				<select name="action_1">
					<option value="">
						<?php _e( 'Bulk Actions', PO_LANG  ); ?>
					</option>
					<option value="activate">
						<?php _e( 'Activate', PO_LANG ); ?>
					</option>
					<option value="deactivate">
						<?php _e( 'Deactivate', PO_LANG ); ?>
					</option>
					<option value="toggle">
						<?php _e( 'Toggle activation', PO_LANG ); ?>
					</option>
				</select>
				<button class="button-secondary action" name="do_action_1">
					<?php _e( 'Apply', PO_LANG ); ?>
				</button>
			</div>

			<div class="alignright actions"></div>
			<br class="clear" />
		</div>


		<div class="clear"></div>

		<?php wp_original_referer_field( true, 'previous' );  ?>
		<?php wp_nonce_field( 'popup-addon' ); ?>

		<table cellspacing="0" class="widefat fixed tbl-addons">
			<?php foreach ( array( 'thead', 'tfoot' ) as $tag ) : ?>
				<<?php echo esc_attr( $tag ); ?>>
					<tr>
						<th class="manage-column column-cb check-column" id="cb" scope="col">
							<input type="checkbox">
						</th>
						<?php foreach ( $columns as $key => $col ) : ?>
							<th
								class="manage-column column-<?php echo esc_attr( $key ); ?>"
								id="<?php echo esc_attr( $key ); ?>"
								scope="col"
								>
								<?php echo esc_html( $col ); ?>
							</th>
						<?php endforeach; ?>
					</tr>
				</<?php echo esc_attr( $tag ); ?>>
			<?php endforeach; ?>

			<tbody>
				<?php foreach ( $addons as $key => $addon ) : ?>
					<?php
					$addon_data = get_file_data(
						PO_INC_DIR . 'addons/' . $addon,
						$default_headers,
						'popup-addon'
					);
					$is_active = ( in_array( $addon, $active ) );

					// Ignore Addons that have no name.
					if ( empty( $addon_data['Name'] ) ) {
						continue;
					}
					$has_addons = true;

					$row_class = ($is_active ? 'active ' : '');

					?>
					<tr valign="middle"
						class="<?php echo esc_attr( $row_class ); ?>"
						id="addon-<?php echo esc_attr( $addon ); ?>"
						>

						<th class="check-column" scope="row">
							<input type="checkbox" value="<?php echo esc_attr( $addon ); ?>" name="addon[]" />
						</th>

						<td class="column-name">
							<span class="the-name">
								<?php echo esc_html( $addon_data['Name'] ); ?>
							</span>
							<div class="row-actions visible">
								<?php
								$args = array(
									'addon'  => $addon,
								);
								if ( $is_active ) {
									$args['action'] = 'deactivate';
									$label = 'Deactivate';
								} else {
									$args['action'] = 'activate';
									$label = 'Activate';
								}
								$url = add_query_arg( $args );
								$nonce_url = wp_nonce_url( $url, 'popup-addon' );
								?>
								<span class="edit <?php echo esc_attr( $args['action'] ); ?>">
									<a href="<?php echo esc_url( $nonce_url ); ?>">
										<?php _e( $label, PO_LANG ); ?>
									</a>
								</span>
							</div>
						</td>

						<td class="column-type">
							<?php _e( @$addon_data['Type'], PO_LANG ); ?>
						</td>

						<td class="column-desc">
							<p>
								<?php echo esc_html( @$addon_data['Description'] ); ?>
							</p>
							<div class="secondary">
								<?php _e( ' by ', PO_LANG ); ?>
								<a href="<?php echo esc_attr( @$addon_data['AuthorURI'] ); ?>">
									<?php echo esc_html( $addon_data['Author'] ); ?>
								</a>
							</div>
						</td>
					</tr>
				<?php endforeach; ?>

				<?php if ( ! $has_addons ) : ?>
					<tr valign="middle" class="alternate">
						<td colspan="<?php echo esc_attr( $columncount ); ?>" scope="row">
							<?php _e( 'No Add-ons where found for this installation', PO_LANG ); ?>
						</td>
					</tr>
				<?php endif; ?>
			</tbody>
		</table>


		<div class="tablenav">
			<div class="alignleft actions">
				<select name="action_2">
					<option value="">
						<?php _e( 'Bulk Actions', PO_LANG  ); ?>
					</option>
					<option value="activate">
						<?php _e( 'Activate', PO_LANG ); ?>
					</option>
					<option value="deactivate">
						<?php _e( 'Deactivate', PO_LANG ); ?>
					</option>
					<option value="toggle">
						<?php _e( 'Toggle activation', PO_LANG ); ?>
					</option>
				</select>
				<button class="button-secondary action" name="do_action_2">
					<?php _e( 'Apply', PO_LANG ); ?>
				</button>
			</div>

			<div class="alignright actions"></div>
			<br class="clear" />
		</div>

	</form>

</div> <!-- wrap -->