<h2><?php _e( 'Adding / Editing a Pop Over', PO_LANG ); ?></h2>
<p><?php _e(
	'A Pop Over should have the following information.', PO_LANG
); ?></p>

<ul>
	<li><?php _e(
		'<strong>A Title</strong> - '.
		'The Pop Over title helps you to identify different Pop Overs in ' .
		'the main list.', PO_LANG
	); ?></li>
	<li><?php _e(
		'<strong>Pop Over content</strong> - This is the content that the ' .
		'Pop Over will display. You can upload any images you require by ' .
		'clicking on the Add Media button at the top of the edit area.', PO_LANG
	);
	echo IncPopup::help_img( 'popovercontent.png' );
	?></li>
	<li><?php _e(
		'<strong>Active Conditions</strong> - ' .
		'You can determine the conditions that need to be fulfilled in ' .
		'order for this Pop Over to display by dragging the relevant ' .
		'conditions in the the <strong>Drop Here</strong> box. All of ' .
		'the conditions must be true in order for the Pop Over to display.', PO_LANG
	);
	echo IncPopup::help_img( 'popoverdragconditions.png' );
	?></li>
	<li><?php _e(
		'<strong>Appearance Settings</strong> - ' .
		'You can set the size and position of the Pop Over by setting ' .
		'the Appearance options. If you want the Pop Over system to attempt ' .
		'to determine the size of the Pop Overs content and automatically ' .
		'resize the Pop Over and attempt to center it on your browser window.', PO_LANG
	);
	echo IncPopup::help_img( 'popoversettings.png' );
	?></li>
</ul>
