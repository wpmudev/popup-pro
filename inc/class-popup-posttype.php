<?php

/**
 * Defines the custom posttype details for popups.
 */
class IncPopupPosttype {

	/**
	 * Capability required to use admin interface of the plugin.
	 * Defined in constructor.
	 * @var string
	 */
	static public $perms = '';

	/**
	 * Returns the singleton instance of the popup database class.
	 *
	 * @since  4.6
	 */
	static public function instance() {
		static $Inst = null;

		if ( null === $Inst ) {
			$Inst = new IncPopupPosttype();
		}

		return $Inst;
	}

	/**
	 * Singleton constructor
	 */
	private function __construct() {
		/**
		 * Allows users to change the required permission for the popup plugin.
		 * Default requirement: manage_options
		 *
		 * @var string
		 */
		self::$perms = apply_filters( 'popover-admin-access-capability', 'manage_options' );

		// Legacy filter (with underscore)
		self::$perms = apply_filters( 'popover-admin-access_capability', self::$perms );

		// Register the posttype
		self::setup_posttype();
	}

	/**
	 * Register the custom post-type details.
	 *
	 * @since  4.6
	 */
	static private function setup_posttype() {
		// Code generated at http://generatewp.com/post-type/

		// Register Custom Post Type
		$labels = array(
			'name'                => _x( 'Pop Ups', 'Post Type General Name', PO_LANG ),
			'singular_name'       => _x( 'Pop Up', 'Post Type Singular Name', PO_LANG ),
			'menu_name'           => __( 'Pop Up', PO_LANG ),
			'parent_item_colon'   => __( 'Parent Item:', PO_LANG ),
			'all_items'           => __( 'Pop Ups', PO_LANG ),
			'view_item'           => __( 'View Item', PO_LANG ),
			'add_new_item'        => __( 'Add New Pop Up', PO_LANG ),
			'add_new'             => __( 'Add New', PO_LANG ),
			'edit_item'           => __( 'Edit Pop Up', PO_LANG ),
			'update_item'         => __( 'Update Pop Up', PO_LANG ),
			'search_items'        => __( 'Search Pop Up', PO_LANG ),
			'not_found'           => __( 'Not found', PO_LANG ),
			'not_found_in_trash'  => __( 'No Pop Up found in Trash', PO_LANG ),
		);
		$args = array(
			'label'               => __( 'Pop Up', PO_LANG ),
			'description'         => __( 'Display Pop Up messages on your website!', PO_LANG ),
			'labels'              => $labels,
			'supports'            => array( '' ),
			'hierarchical'        => false,
			'public'              => false,
			'show_ui'             => true,
			'show_in_menu'        => true,
			'show_in_nav_menus'   => false,
			'show_in_admin_bar'   => false,
			'menu_position'       => 100,
			'menu_icon'           => PO_IMG_URL . 'window.png',
			'can_export'          => true,
			'has_archive'         => false,
			'exclude_from_search' => true,
			'publicly_queryable'  => false,
			'rewrite'             => false,
			'capability_type'     => self::$perms,
		);
		register_post_type( IncPopupItem::POST_TYPE, $args );
	}

};