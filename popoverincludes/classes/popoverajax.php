<?php
/*
* The processing part of the popover plugin
* - was previously in popoverpublic.php
*/

if(!class_exists('popoverajax')) {

	class popoverajax {

		private $_data;

		function __construct() {

			add_action('init', array(&$this, 'initialise_ajax'), 99);

			$this->_data = Popover_Data::get_instance();
		}

		function popoverajax() {
			$this->__construct();
		}

		function initialise_ajax() {

			$settings = get_popover_option('popover-settings', array( 'loadingmethod' => 'external'));

			switch( $settings['loadingmethod'] ) {

				case 'external':		add_action( 'wp_ajax_popover_selective_ajax', array(&$this,'ajax_selective_message_display') );
										add_action( 'wp_ajax_nopriv_popover_selective_ajax', array(&$this,'ajax_selective_message_display') );
										break;

				case 'frontloading':	if( isset( $_GET['popoverajaxaction']) && $_GET['popoverajaxaction'] == 'popover_selective_ajax' ) {
											$this->ajax_selective_message_display();
										}
										break;
				default:
					do_action('popover-ajax-loading_method', $settings['loadingmethod'], $this);
			}

		}

		function ajax_selective_message_display() {
			$callback = !empty($_GET['callback']) && preg_match('/^po_[a-z]+$/i', $_GET['callback'])
				? $_GET['callback']
				: false
			;
			if (!$callback) return false;
			
			$data = json_encode($this->_data->get_applicable_popover_messages());
			echo "{$callback}({$data})";
			exit;
		}

	}

}