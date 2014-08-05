<?php
/*
Name:        Installation
Plugin URI:  http://premium.wpmudev.org/project/the-pop-over-plugin/
Description: WordPress Multisite: Condition based on the WordPress installation.
Author:      Philipp (Incsub)
Author URI:  http://premium.wpmudev.org
Type:        Rule
Rules:       Site is not a Pro-Site
Version:     1.0

NOTE: DON'T RENAME THIS FILE!!
This filename is saved as metadata with each popup that uses these rules.
Renaming the file will DISABLE the rules, which is very bad!
*/

class IncPopupRule_Installation extends IncPopupRule {

	/**
	 * Initialize the rule object.
	 *
	 * @since  4.6
	 */
	protected function init() {
		$this->filename = basename( __FILE__ );

		if ( function_exists( 'is_pro_site' ) ) {
			// 'no_prosite' rule.
			$this->add_rule(
				'no_prosite',
				__( 'Site is not a Pro-Site', PO_LANG ),
				__( 'Shows the Pop Up if the site is not a Pro-Site.', PO_LANG ),
				'',
				20
			);
		}
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


};

IncPopupRules::register( 'IncPopupRule_Installation' );