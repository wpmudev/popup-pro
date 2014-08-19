<?php
/*
Name:        Browser type
Plugin URI:  http://premium.wpmudev.org/project/the-pop-over-plugin/
Description: Conditions that check browser details.
Author:      Philipp (Incsub)
Author URI:  http://premium.wpmudev.org
Type:        Rule
Rules:       Only on mobile devices, Not on mobile devices
Version:     1.0

NOTE: DON'T RENAME THIS FILE!!
This filename is saved as metadata with each popup that uses these rules.
Renaming the file will DISABLE the rules, which is very bad!
*/

class IncPopupRule_Browser extends IncPopupRule {

	/**
	 * Initialize the rule object.
	 *
	 * @since  4.6
	 */
	protected function init() {
		$this->filename = basename( __FILE__ );

		// 'mobile' rule.
		$this->add_rule(
			'mobile',
			__( 'Only on mobile devices', PO_LANG ),
			__( 'Shows the PopUp to visitors that are using a mobile device (Phone or Tablet).', PO_LANG ),
			'no_mobile',
			6
		);

		// 'no_mobile' rule.
		$this->add_rule(
			'no_mobile',
			__( 'Not on mobile devices', PO_LANG ),
			__( 'Shows the PopUp to visitors that are using a normal computer or laptop (i.e. not a Phone or Tablet).', PO_LANG ),
			'mobile',
			6
		);
	}


	/*============================*\
	================================
	==                            ==
	==           MOBILE           ==
	==                            ==
	================================
	\*============================*/


	/**
	 * Apply the rule-logic to the specified popup
	 *
	 * @since  4.6
	 * @param  mixed $data Rule-data which was saved via the save_() handler.
	 * @return bool Decission to display popup or not.
	 */
	protected function apply_mobile( $data ) {
		return wp_is_mobile();
	}


	/*===============================*\
	===================================
	==                               ==
	==           NO_MOBILE           ==
	==                               ==
	===================================
	\*===============================*/


	/**
	 * Apply the rule-logic to the specified popup
	 *
	 * @since  4.6
	 * @param  mixed $data Rule-data which was saved via the save_() handler.
	 * @return bool Decission to display popup or not.
	 */
	protected function apply_no_mobile( $data ) {
		return ! wp_is_mobile();
	}


};

IncPopupRules::register( 'IncPopupRule_Browser' );