<?php
/**
 * Core rule: Referer / Internal / Search Engine
 *
 * NOTE: DON'T RENAME THIS FILE!!
 * This filename is saved as metadata with each popup that uses these rules.
 * Renaming the file will DISABLE the rules, which is very bad!
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
		$this->filename = basename( __FILE__ );

		// 'referer' rule.
		$this->add_rule(
			'referer',
			__( 'Visit via specific referer', PO_LANG ),
			__( 'Shows the Pop Up if the user arrived via a specific referrer.', PO_LANG ),
			'',
			15
		);

		// 'internal' rule.
		$this->add_rule(
			'internal',
			__( 'Visit not via an Internal link', PO_LANG ),
			__( 'Shows the Pop Up if the user did not arrive on this page via another page on your site.', PO_LANG ),
			'',
			15
		);

		// 'searchengine' rule.
		$this->add_rule(
			'searchengine',
			__( 'Visit via a search engine', PO_LANG ),
			__( 'Shows the Pop Up if the user arrived via a search engine.', PO_LANG ),
			'',
			15
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
		return $this->test_referer( $data );
	}

	/**
	 * Output the Admin-Form for the active rule.
	 *
	 * @since  4.6
	 * @param  mixed $data Rule-data which was saved via the save_() handler.
	 */
	protected function form_referer( $data ) {
		if ( is_string( $data ) ) { $referer = $data; }
		else if ( is_array( $data ) ) { $referer = implode( "\n", $data ); }
		else { $referer = ''; }
		?>
		<label for="po-rule-data-referer">
			<?php _e( 'Referers (one per line):', PO_LANG ); ?>
		</label>
		<textarea name="po_rule_data[referer]" id="po-rule-data-referer" class="block"><?php
			echo esc_attr( $referer );
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
		return explode( "\n", @$_POST['po_rule_data']['referer'] );
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
		$internal = preg_replace( '#^https?://#', '', get_option( 'home' ) );

		return $this->test_referer( $internal );
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
		return $this->test_searchengine();
	}


	/*======================================*\
	==========================================
	==                                      ==
	==           HELPER FUNCTIONS           ==
	==                                      ==
	==========================================
	\*======================================*/


	/**
	 * Tests if the current referer is one of the referers of the list.
	 * Current referer has to be specified in the URL param "thereferer".
	 *
	 * @since  4.6
	 * @param  array $list List of referers to check.
	 * @return bool
	 */
	protected function test_referer( $list ) {
		$response = false;
		if ( is_string( $list ) ) { $list = array( $list ); }
		if ( ! is_array( $list ) ) { return true; }

		$referer = @$_REQUEST['thereferrer'];

		if ( empty( $referer ) ) {
			$response = true;
		} else {
			foreach ( $list as $item ) {
				if ( preg_match( '#' . $item . '#i', $referer ) ) {
					$response = true;
					break;
				}
			}
		}
		return $response;
	}

	/**
	 * Tests if the current referer is a search engine.
	 * Current referer has to be specified in the URL param "thereferer".
	 *
	 * @since  4.6
	 * @return bool
	 */
	protected function test_searchengine() {
		$response = false;
		$referer = @$_REQUEST['thereferrer'];

		$patterns = array(
			'/search?',
			'.google.',
			'web.info.com',
			'search.',
			'del.icio.us/search',
			'soso.com',
			'/search/',
			'.yahoo.',
			'.bing.',
		);

		foreach ( $patterns as $url ) {
			if ( strpos( $referer, $url ) !== false ) {
				if ( $url == '.google.' ) {
					if ( $this->is_googlesearch( $referer ) ) {
						$response = true;
					} else {
						$response = false;
					}
				} else {
					$response = true;
				}
				break;
			}
		}
		return $response;
	}

	/**
	 * Checks if the referer is a google web-source.
	 *
	 * @since  4.6
	 * @param  string $referer
	 * @return bool
	 */
	protected function is_googlesearch( $referer = '' ) {
		$response = false;

		// Get the query strings and check its a web source.
		$qs = parse_url( $referer, PHP_URL_QUERY );
		$qget = array();

		foreach ( explode( '&', $qs ) as $keyval ) {
			list( $key, $value ) = explode( '=', $keyval );
			$qget[ trim( $key ) ] = trim( $value );
		}
		$response = @$qget['source'] == 'web';

		return $response;
	}


};

IncPopupRules::register( 'IncPopupRule_Referer' );