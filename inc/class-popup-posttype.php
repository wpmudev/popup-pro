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
	 * The position of the PopUp main menu.
	 * We use a trick to avoid collissions with other menu-items
	 * @var int
	 */
	static public $menu_pos = 101;

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

		/**
		 * Allows users to avoid conflicts with other menu items by assigning a
		 * different menu position.
		 *
		 * @var int
		 */
		self::$menu_pos = apply_filters( 'popover-admin-menu-position', self::$menu_pos );

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
		$manage_popups = ( true == IncPopup::correct_level() );

		// Register Custom Post Type
		$labels = array(
			'name'                => _x( 'PopUps', 'Post Type General Name', PO_LANG ),
			'singular_name'       => _x( 'PopUp', 'Post Type Singular Name', PO_LANG ),
			'menu_name'           => __( 'PopUp', PO_LANG ),
			'parent_item_colon'   => __( 'Parent Item:', PO_LANG ),
			'all_items'           => __( 'PopUps', PO_LANG ),
			'view_item'           => __( 'View Item', PO_LANG ),
			'add_new_item'        => __( 'Add New PopUp', PO_LANG ),
			'add_new'             => __( 'Add New', PO_LANG ),
			'edit_item'           => __( 'Edit PopUp', PO_LANG ),
			'update_item'         => __( 'Update PopUp', PO_LANG ),
			'search_items'        => __( 'Search PopUp', PO_LANG ),
			'not_found'           => __( 'Not found', PO_LANG ),
			'not_found_in_trash'  => __( 'No PopUp found in Trash', PO_LANG ),
		);

		if ( IncPopup::use_global() ) {
			$labels['name']          = _x( 'Global PopUps', 'Post Type General Name', PO_LANG );
			$labels['singular_name'] = _x( 'Global PopUp', 'Post Type Singular Name', PO_LANG );
			$labels['all_items']     = __( 'Global PopUps', PO_LANG );
		}

		$args = array(
			'label'               => __( 'PopUp', PO_LANG ),
			'description'         => __( 'Display PopUp messages on your website!', PO_LANG ),
			'labels'              => $labels,
			'supports'            => array( '' ),
			'hierarchical'        => false,
			'public'              => false,
			'show_ui'             => $manage_popups,
			'show_in_menu'        => $manage_popups,
			'show_in_nav_menus'   => false,
			'show_in_admin_bar'   => $manage_popups,
			'menu_position'       => self::$menu_pos,
			'menu_icon'           => PO_IMG_URL . 'icon.png',
			'can_export'          => true,
			'has_archive'         => false,
			'exclude_from_search' => true,
			'publicly_queryable'  => false,
			'rewrite'             => false,
			'capabilities' => array(
				'edit_post'          => self::$perms,
				'read_post'          => self::$perms,
				'delete_posts'       => self::$perms,
				'edit_posts'         => self::$perms,
				'edit_others_posts'  => self::$perms,
				'publish_posts'      => self::$perms,
				'read_private_posts' => self::$perms,
			),
		);
		register_post_type( IncPopupItem::POST_TYPE, $args );
	}

};