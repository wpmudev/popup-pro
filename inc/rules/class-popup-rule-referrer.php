<?php
/*
Name:        Referrer
Plugin URI:  http://premium.wpmudev.org/project/the-pop-over-plugin/
Description: Examine how the visitor arrived on the current page.
Author:      Philipp (Incsub)
Author URI:  http://premium.wpmudev.org
Type:        Rule
Rules:       From a specific referrer, Not from a specific referrer, Not from an internal link, From a search engine
Version:     1.0

NOTE: DON'T RENAME THIS FILE!!
This filename is saved as metadata with each popup that uses these rules.
Renaming the file will DISABLE the rules, which is very bad!
*/

class IncPopupRule_Referrer extends IncPopupRule {

	/**
	 * Initialize the rule object.
	 *
	 * @since  4.6
	 */
	protected function init() {
		$this->filename = basename( __FILE__ );

		// 'referrer' rule.
		$this->add_rule(
			'referrer',
			__( 'From a specific referrer', PO_LANG ),
			__( 'Shows the PopUp if the user arrived via a specific referrer.', PO_LANG ),
			'no_referrer',
			15
		);

		// 'no_referrer' rule.
		$this->add_rule(
			'no_referrer',
			__( 'Not from a specific referrer', PO_LANG ),
			__( 'Hides the PopUp if the user arrived via a specific referrer.', PO_LANG ),
			'referrer',
			15
		);

		// 'no_internal' rule.
		$this->add_rule(
			'no_internal',
			__( 'Not from an internal link', PO_LANG ),
			__( 'Shows the PopUp if the user did not arrive on this page via another page on your site.', PO_LANG ),
			'',
			15
		);

		// 'searchengine' rule.
		$this->add_rule(
			'searchengine',
			__( 'From a search engine', PO_LANG ),
			__( 'Shows the PopUp if the user arrived via a search engine.', PO_LANG ),
			'',
			15
		);
	}


	/*==============================*\
	==================================
	==                              ==
	==           REFERRER           ==
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
	protected function apply_referrer( $data ) {
		return $this->test_referrer( $data );
	}

	/**
	 * Output the Admin-Form for the active rule.
	 *
	 * @since  4.6
	 * @param  mixed $data Rule-data which was saved via the save_() handler.
	 */
	protected function form_referrer( $data ) {
		if ( is_string( $data ) ) { $referrer = $data; }
		else if ( is_array( $data ) ) { $referrer = implode( "\n", $data ); }
		else { $referrer = ''; }
		?>
		<label for="po-rule-data-referrer">
			<?php _e( 'Referrers. Can be full URL or a pattern like ".example.com" (one per line):', PO_LANG ); ?>
		</label>
		<textarea name="po_rule_data[referrer]" id="po-rule-data-referrer" class="block"><?php
			echo esc_attr( $referrer );
		?></textarea>
		<?php
	}

	/**
	 * Update and return the $settings array to save the form values.
	 *
	 * @since  4.6
	 * @return mixed Data collection of this rule.
	 */
	protected function save_referrer() {
		return explode( "\n", $_POST['po_rule_data']['referrer'] );
	}


	/*==============================*\
	==================================
	==                              ==
	==           NO_REFERRER           ==
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
	protected function apply_no_referrer( $data ) {
		return ! $this->test_referrer( $data );
	}

	/**
	 * Output the Admin-Form for the active rule.
	 *
	 * @since  4.6
	 * @param  mixed $data Rule-data which was saved via the save_() handler.
	 */
	protected function form_no_referrer( $data ) {
		if ( is_string( $data ) ) { $no_referrer = $data; }
		else if ( is_array( $data ) ) { $no_referrer = implode( "\n", $data ); }
		else { $no_referrer = ''; }
		?>
		<label for="po-rule-data-no_referrer">
			<?php _e( 'Referrers. Can be full URL or a pattern like ".example.com" (one per line):', PO_LANG ); ?>
		</label>
		<textarea name="po_rule_data[no_referrer]" id="po-rule-data-no_referrer" class="block"><?php
			echo esc_attr( $no_referrer );
		?></textarea>
		<?php
	}

	/**
	 * Update and return the $settings array to save the form values.
	 *
	 * @since  4.6
	 * @return mixed Data collection of this rule.
	 */
	protected function save_no_referrer() {
		return explode( "\n", $_POST['po_rule_data']['no_referrer'] );
	}


	/*=================================*\
	=====================================
	==                                 ==
	==           NO_INTERNAL           ==
	==                                 ==
	=====================================
	\*=================================*/


	/**
	 * Apply the rule-logic to the specified popup
	 *
	 * @since  4.6
	 * @param  mixed $data Rule-data which was saved via the save_() handler.
	 * @return bool Decission to display popup or not.
	 */
	protected function apply_no_internal( $data ) {
		if ( ! empty( $_POST['_po_method_'] ) && 'raw' == $_POST['_po_method_'] ) {
			// This indicates a form-submit from inside a popup. Naturally the
			// referrer will always be internal, so ignore this rule here.
			return true;
		} else {
			$internal = preg_replace( '#^https?://#', '', get_option( 'home' ) );

			return ! $this->test_referrer( $internal );
		}
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
	 * Tests if the current referrer is one of the referers of the list.
	 * Current referrer has to be specified in the URL param "thereferer".
	 *
	 * @since  4.6
	 * @param  array $list List of referers to check.
	 * @return bool
	 */
	public function test_referrer( $list ) {
		$response = false;
		if ( is_string( $list ) ) { $list = array( $list ); }
		if ( ! is_array( $list ) ) { return true; }

		$referrer = $this->get_referrer();

		if ( empty( $referrer ) ) {
			$response = false;
		} else {
			foreach ( $list as $item ) {
				$item = trim( $item );
				$res = stripos( $referrer, $item );
				if ( false !== $res ) {
					$response = true;
					break;
				}
			}
		}

		return $response;
	}

	/**
	 * Tests if the current referrer is a search engine.
	 * Current referrer has to be specified in the URL param "thereferer".
	 *
	 * @since  4.6
	 * @return bool
	 */
	public function test_searchengine() {
		$response = false;
		$referrer = $this->get_referrer();

		$patterns = array(
			'/search?',
			'.google.',
			'web.info.com',
			'search.',
			'del.icio.us/search',
			'delicious.com/search',
			'soso.com',
			'/search/',
			'.yahoo.',
			'.bing.',
		);

		foreach ( $patterns as $url ) {
			if ( false !== stripos( $referrer, $url ) ) {
				if ( $url == '.google.' ) {
					if ( $this->is_googlesearch( $referrer ) ) {
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
	 * Checks if the referrer is a google web-source.
	 *
	 * @since  4.6
	 * @param  string $referrer
	 * @return bool
	 */
	public function is_googlesearch( $referrer = '' ) {
		$response = true;

		// Get the query strings and check its a web source.
		$qs = parse_url( $referrer, PHP_URL_QUERY );
		$qget = array();

		foreach ( explode( '&', $qs ) as $keyval ) {
			$kv = explode( '=', $keyval );
			if ( 2 == count( $kv ) ) {
				$qget[ trim( $kv[0] ) ] = trim( $kv[1] );
			}
		}

		if ( isset( $qget['source'] ) ) {
			$response = $qget['source'] == 'web';
		}

		return $response;
	}

	/**
	 * Returns the referrer.
	 *
	 * @since  4.6
	 * @return string
	 */
	public function get_referrer() {
		$referrer = '';

		$is_ajax = (defined( 'DOING_AJAX' ) && DOING_AJAX)
			|| ( ! empty( $_POST['_po_method_'] ) && 'raw' == $_POST['_po_method_'] );

		if ( isset( $_REQUEST['thereferrer'] ) ) {
			$referrer = $_REQUEST['thereferrer'];
		} else if ( ! $is_ajax && isset( $_SERVER['HTTP_REFERER'] ) ) {
			// When doing Ajax request we NEVER use the HTTP_REFERER!
			$referrer = $_SERVER['HTTP_REFERER'];
		}

		return $referrer;
	}


};

IncPopupRules::register( 'IncPopupRule_Referrer' );