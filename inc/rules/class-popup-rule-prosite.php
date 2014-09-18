<?php
/*
Name:        Pro Sites
Plugin URI:  http://premium.wpmudev.org/project/the-pop-over-plugin/
Description: Conditions based on the blogs Pro Sites details (only available for Global PopUps). <a href="http://premium.wpmudev.org/project/pro-sites/" target="_blank">Learn more &raquo;</a>
Author:      Philipp (Incsub)
Author URI:  http://premium.wpmudev.org
Type:        Rule
Rules:       Site is not a Pro Site
Limit:       global, pro
Version:     1.0

NOTE: DON'T RENAME THIS FILE!!
This filename is saved as metadata with each popup that uses these rules.
Renaming the file will DISABLE the rules, which is very bad!
*/

class IncPopupRule_Prosite extends IncPopupRule {

	/**
	 * Initialize the rule object.
	 *
	 * @since  4.6
	 */
	protected function init() {
		$this->filename = basename( __FILE__ );

		// 'no_prosite' rule.
		$this->add_rule(
			'no_prosite',
			__( 'Site is not a Pro Site', PO_LANG ),
			__( 'Shows the PopUp if the site is not a Pro Site.', PO_LANG ),
			'',
			20
		);

		// -- Initialize rule.

		$this->is_active = function_exists( 'is_pro_site' );
	}


	/*================================*\
	====================================
	==                                ==
	==           NO_PROSITE           ==
	==                                ==
	====================================
	\*================================*/


	/**
	 * Apply the rule-logic to the specified popup
	 *
	 * @since  4.6
	 * @param  mixed $data Rule-data which was saved via the save_() handler.
	 * @return bool Decission to display popup or not.
	 */
	protected function apply_no_prosite( $data ) {
		$prosite = function_exists( 'is_pro_site' ) && is_pro_site();
		return ! $prosite;
	}

	/**
	 * Output the Admin-Form for the active rule.
	 *
	 * @since  4.6
	 * @param  mixed $data Rule-data which was saved via the save_() handler.
	 */
	protected function form_no_prosite( $data ) {
		if ( ! $this->is_active ) {
			$this->render_plugin_inactive();
		}
	}


	/*======================================*\
	==========================================
	==                                      ==
	==           HELPER FUNCTIONS           ==
	==                                      ==
	==========================================
	\*======================================*/


	/**
	 * Displays a warning message in case the Membership plugin is not active.
	 *
	 * @since  1.0.0
	 */
	protected function render_plugin_inactive() {
		?>
		<div class="error below-h2"><p>
			<?php printf(
				__(
					'This condition requires that the <a href="%s" target="_blank">' .
					'Pro Sites Plugin</a> is installed and activated.', PO_LANG
				),
				'http://premium.wpmudev.org/project/pro-sites/'
			);?>
		</p></div>
		<?php
	}

};

IncPopupRules::register( 'IncPopupRule_Prosite' );