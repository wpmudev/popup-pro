<?php
global $shortcode_tags;


// Theme compatibility.
$theme_compat = IncPopupAddon_HeaderFooter::check();
$settings = IncPopupDatabase::get_settings();
$cur_method = $settings['loadingmethod'];


// Shortcodes with restrictions.
$limited = array(
	'app_.*',
	'.*-form.*',
	'.*_form.*',
	'embed',
);

$shortcodes = array();
// Add Admin-Shortcodes to the list.
foreach ( $shortcode_tags as $code => $handler ) {
	if ( ! isset( $shortcodes[ $code ] ) ) {
		$shortcodes[ $code ] = '';
	}

	$shortcodes[ $code ] .= 'sc-admin ';
}

// Add Front-End Shortcodes to the list.
foreach ( $theme_compat->shortcodes as $code ) {
	if ( ! isset( $shortcodes[ $code ] ) ) {
		$shortcodes[ $code ] = '';
	}

	$shortcodes[ $code ] .= 'sc-front ';
}

foreach ( $shortcodes as $code => $compat ) {
	foreach ( $limited as $pattern ) {
		if ( preg_match( '/^' . $pattern . '$/i', $code ) ) {
			$shortcodes[ $code ] = $compat . 'sc-limited ';
		}
	}
}


echo '<p>';
_e(
	'You can use all your shortcodes inside the PopUp contents, ' .
	'however some Plugins or Themes might provide shortcodes that ' .
	'only work with the loading method "Page Footer".<br /> ' .
	'This list explains which shortcodes can be used with each ' .
	'loading method:', PO_LANG
);
echo '</p>';

if ( IncPopup::use_global() ) :
	?>
	<p><em><?php
	_e(
		'Important notice for shortcodes in <strong>Global ' .
		'PopUps</strong>:<br />' .
		'Shortcodes can be provided by a plugin or theme, so ' .
		'each blog can have a different list of shortcodes. The ' .
		'following list is valid for the current blog only!', PO_LANG
	);
	?></em></p>
	<?php

endif;

?>
<div class="tbl-shortcodes">
<table class="widefat load-<?php echo esc_attr( $cur_method ); ?>">
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
			<th class="flag load-ajax">
				<div data-tooltip="<?php _e( 'Loading method \'WordPress AJAX\'', PO_LANG ); ?>">
				<?php _e( 'WP AJAX', PO_LANG ); ?>
				</div>
			</th>
			<th class="flag load-front">
				<div data-tooltip="<?php _e( 'Loading method \'Custom AJAX\'', PO_LANG ); ?>">
				<?php _e( 'Cust AJAX', PO_LANG ); ?>
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
				<td class="flag sc-admin load-ajax"><i class="icon dashicons"></i></td>
				<td class="flag sc-front load-front"><i class="icon dashicons"></i></td>
				<td class="flag sc-admin load-anonymous"><i class="icon dashicons"></i></td>
				<td class="flag sc-admin"><i class="icon dashicons"></i></td>
			</tr>
		<?php endforeach; ?>
	</tbody>
</table>
<div class="legend shortcode sc-admin">
	<span class="sc-admin load-ajax"><i class="icon dashicons"></i></span>
	<?php _e( 'Shortcode supported', PO_LANG ); ?>
</div>
<div class="legend shortcode sc-admin sc-limited">
	<span class="sc-admin load-ajax"><i class="icon dashicons"></i></span>
	<?php _e( 'Might have issues', PO_LANG ); ?>
</div>
<div class="legend shortcode sc-admin">
	<span class="sc-front load-footer"><i class="icon dashicons"></i></span>
	<?php _e( 'Shortcode does not work', PO_LANG ); ?>
</div>
</div>