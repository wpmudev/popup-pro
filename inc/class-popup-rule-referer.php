<?php
/**
 * Core rule: Referer / Internal / Search Engine
 *
 * @since  4.6
 */
class IncPopupRule_Referer extends IncPopupRule {

	/**
	 * Initialize the rule object.
	 *
	 * @since  4.6
	 */
	protected function init() {
		// 'referer' rule.
		$this->add_info(
			'referer',
			__( 'Visit via specific referer', PO_LANG ),
			__( 'Shows the Pop Up if the user arrived via a specific referrer.', PO_LANG )
		);

		// 'internal' rule.
		$this->add_info(
			'internal',
			__( 'Visit not via an Internal link', PO_LANG ),
			__( 'Shows the Pop Up if the user did not arrive on this page via another page on your site.', PO_LANG )
		);

		// 'searchengine' rule.
		$this->add_info(
			'searchengine',
			__( 'Visit via a search engine', PO_LANG ),
			__( 'Shows the Pop Up if the user arrived via a search engine.', PO_LANG )
		);
	}


	/*=============================*\
	=================================
	==                             ==
	==           REFERER           ==
	==                             ==
	=================================
	\*=============================*/


	/**
	 * Apply the rule-logic to the specified popup
	 *
	 * @since  4.6
	 * @param  mixed $data Rule-data which was saved via the save_() handler.
	 * @return bool Decission to display popup or not.
	 */
	protected function apply_referer( $data ) {
		return true;
	}

	/**
	 * Output the Admin-Form for the active rule.
	 *
	 * @since  4.6
	 * @param  mixed $data Rule-data which was saved via the save_() handler.
	 */
	protected function form_referer( $data ) {
		?>
		<label for="po-rule-data-referer">
			<?php _e( 'Referers (one per line):', PO_LANG ); ?>
		</label>
		<textarea name="po_rule_data[referer]" id="po-rule-data-referer" class="block"><?php
			echo esc_attr( $data );
		?></textarea>
		<?php
	}

	/**
	 * Update and return the $settings array to save the form values.
	 *
	 * @since  4.6
	 * @return mixed Data collection of this rule.
	 */
	protected function save_referer() {
		return @$_POST['po_rule_data']['referer'];
	}


	/*==============================*\
	==================================
	==                              ==
	==           INTERNAL           ==
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
	protected function apply_internal( $data ) {
		return true;
	}


	/*==================================*\
	======================================
	==                                  ==
	==           SEARCHENGINE           ==
	==                                  ==
	======================================
	\*==================================*/


	/**
	 * Apply the rule-logic to the specified popup
	 *
	 * @since  4.6
	 * @param  mixed $data Rule-data which was saved via the save_() handler.
	 * @return bool Decission to display popup or not.
	 */
	protected function apply_searchengine( $data ) {
		return true;
	}

};

IncPopupRules::register( 'IncPopupRule_Referer' );