<?php
/**
 * Core rule: Login / No Login / No Comment / No ProSite
 *
 * @since  4.6
 */
class IncPopupRule_User extends IncPopupRule {

	/**
	 * Initialize the rule object.
	 *
	 * @since  4.6
	 */
	protected function init() {
		// 'login' rule.
		$this->add_info(
			'login',
			__( 'Visitor is logged in', PO_LANG ),
			__( 'Shows the Pop Up if the user is logged in to your site.', PO_LANG ),
			'no_login'
		);

		// 'no_login' rule.
		$this->add_info(
			'no_login',
			__( 'Visitor is not logged in', PO_LANG ),
			__( 'Shows the Pop Up if the user is not logged in to your site.', PO_LANG ),
			'login'
		);

		// 'no_comment' rule.
		$this->add_info(
			'no_comment',
			__( 'Visitor has never commented', PO_LANG ),
			__( 'Shows the Pop Up if the user has never left a comment.', PO_LANG )
		);

		// 'no_prosite' rule.
		$this->add_info(
			'no_prosite',
			__( 'Site is not a Pro-Site', PO_LANG ),
			__( 'Shows the Pop Up if the site is not a Pro-Site.', PO_LANG )
		);
	}


	/*===========================*\
	===============================
	==                           ==
	==           LOGIN           ==
	==                           ==
	===============================
	\*===========================*/


	/**
	 * Apply the rule-logic to the specified popup
	 *
	 * @since  4.6
	 * @param  mixed $data Rule-data which was saved via the save_() handler.
	 * @return bool Decission to display popup or not.
	 */
	protected function apply_login( $data ) {
		return true;
	}


	/*==============================*\
	==================================
	==                              ==
	==           NO_LOGIN           ==
	==                              ==
	==================================
	\*==============================*/


	/**
	 * Apply the rule-logic to the specified popup
	 *
	 * @since  4.6
	 * @param  mixed $data Rule-data which was saved via the save_() handler.
	 * @return bool Decission to display popup or not.
	 */
	protected function apply_no_login( $data ) {
		return true;
	}


	/*================================*\
	====================================
	==                                ==
	==           NO_COMMENT           ==
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
	protected function apply_no_comment( $data ) {
		return true;
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
		return true;
	}

};

IncPopupRules::register( 'IncPopupRule_User' );