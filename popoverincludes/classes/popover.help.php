<?php
if(!class_exists('Popover_Help')) {

	class Popover_Help {
		// The screen we want to access help for
		var $screen = false;

		function __construct( &$screen = false ) {

			$this->screen = $screen;

			//print_r($screen);

		}

		function Popover_Help( &$screen = false ) {
			$this->__construct( $screen );
		}

		function show() {



		}

		function attach() {

			switch($this->screen->id) {

				case 'toplevel_page_popover':						$this->main_help();
																	break;

				case 'pop-overs_page_popoveraddons':				$this->addons_help();
																	break;


			}

		}

		// Specific help content creation functions

		function main_help() {

			ob_start();
			include_once(popover_dir('popoverincludes/help/popover.help.php'));
			$help = ob_get_clean();

			ob_start();
			include_once(popover_dir('popoverincludes/help/popoveredit.help.php'));
			$helpedit = ob_get_clean();

			$this->screen->add_help_tab( array(
				'id'      => 'popover',
				'title'   => __( 'Overview' , 'popover' ),
				'content' => $help,
			) );

			$this->screen->add_help_tab( array(
				'id'      => 'edit',
				'title'   => __( 'Adding / Editing' , 'popover' ),
				'content' => $helpedit,
			) );

		}

		function addons_help() {

			ob_start();
			include_once(popover_dir('popoverincludes/help/popoveraddons.help.php'));
			$help = ob_get_clean();

			$this->screen->add_help_tab( array(
				'id'      => 'addons',
				'title'   => __( 'Add-ons', 'popover' ),
				'content' => $help,
			) );

		}


	}

}
?>