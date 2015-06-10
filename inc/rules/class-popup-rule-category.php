<?php
/*
Name:        Post Categories
Plugin URI:  http://premium.wpmudev.org/project/the-pop-over-plugin/
Description: Adds post category related rules.
Author:      Ve (Incsub)
Author URI:  http://premium.wpmudev.org
Type:        Rule
Rules:       On post category, Not on post category
Limit:       no global, pro
Version:     1.0

NOTE: DON'T RENAME THIS FILE!!
This filename is saved as metadata with each popup that uses these rules.
Renaming the file will DISABLE the rules, which is very bad!
*/

class IncPopupRule_Category extends IncPopupRule {

	/**
	 * Initialize the rule object.
	 *
	 * @since  4.6
	 */
	protected function init() {
		$this->filename = basename( __FILE__ );

		if ( IncPopup::use_global() ) { return; }

		// 'category' rule.
		$this->add_rule(
			'category',
			__( 'On post category', PO_LANG ),
			__( 'Shows the PopUp on pages that match any of the specified categories.', PO_LANG ),
			'no_category',
			30
		);

		// 'no_category' rule.
		$this->add_rule(
			'no_category',
			__( 'Not on post category', PO_LANG ),
			__( 'Shows the PopUp on pages that do not match any of the specified categories.', PO_LANG ),
			'category',
			30
		);

		// -- Initialize rule.

		add_filter(
			'popup-ajax-data',
			array( $this, 'inject_ajax_category' )
		);

		$this->categories = get_terms(
			'category',
			array(
				'hide_empty' => false,
			),
			'objects'
		);

		$this->url_types = array(
			'singular' => __( 'Singular', PO_LANG ),
			'plural'   => __( 'Archive', PO_LANG ),
		);
	}

	/**
	 * Injects category details into the ajax-data collection.
	 * (Required for any ajax loading method)
	 *
	 * @since  4.6
	 */
	public function inject_ajax_category( $data ) {
		$categories = json_encode( wp_list_pluck( get_the_category(), 'term_id' ) );
		$is_singular = is_singular() ? 1 : 0;

		if ( ! is_array( @$data['ajax_data'] ) ) {
			$data['ajax_data'] = array();
		}
		$data['ajax_data']['categories'] = $categories;
		$data['ajax_data']['is_single'] = $is_singular;

		return $data;
	}


	/*==============================*\
	==================================
	==                              ==
	==           CATEGORY           ==
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
	protected function apply_category( $data ) {
		if ( ! is_array( $data ) ) { $data = array(); }

		return $this->check_category( @$data['categories'], @$data['urls'] );
	}

	/**
	 * Output the Admin-Form for the active rule.
	 *
	 * @since  4.6
	 * @param  mixed $data Rule-data which was saved via the save_() handler.
	 */
	protected function form_category( $data ) {
		$this->render_form(
			'category',
			__( 'Show on these post categories:', PO_LANG ),
			__( 'Show on these category type URLs:', PO_LANG ),
			$data
		);
	}

	/**
	 * Update and return the $settings array to save the form values.
	 *
	 * @since  4.6
	 * @param  array $data The contents of $_POST['po_rule_data'].
	 * @return mixed Data collection of this rule.
	 */
	protected function save_category( $data ) {
		lib2()->array->equip( $data, 'category' );
		return $data['category'];
	}


	/*=================================*\
	=====================================
	==                                 ==
	==           NO_CATEGORY           ==
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
	protected function apply_no_category( $data ) {
		if ( ! is_array( $data ) ) { $data = array(); }

		return ! $this->check_category( @$data['categories'], @$data['urls'] );
	}

	/**
	 * Output the Admin-Form for the active rule.
	 *
	 * @since  4.6
	 * @param  mixed $data Rule-data which was saved via the save_() handler.
	 */
	protected function form_no_category( $data ) {
		$this->render_form(
			'no_category',
			__( 'Hide on these post categories:', PO_LANG ),
			__( 'Hide on these category type URLs:', PO_LANG ),
			$data
		);
	}

	/**
	 * Update and return the $settings array to save the form values.
	 *
	 * @since  4.6
	 * @param  array $data The contents of $_POST['po_rule_data'].
	 * @return mixed Data collection of this rule.
	 */
	protected function save_no_category( $data ) {
		lib2()->array->equip( $data, 'no_category' );
		return $data['no_category'];
	}


	/*======================================*\
	==========================================
	==                                      ==
	==           HELPER FUNCTIONS           ==
	==                                      ==
	==========================================
	\*======================================*/


	/**
	 * Renders the category options-form
	 *
	 * @since  1.0.0
	 * @param  string $name
	 * @param  string $label_category
	 * @param  string $label_urls
	 * @param  array $data
	 */
	protected function render_form( $name, $label_category, $label_urls, $data ) {
		if ( ! is_array( $data ) ) { $data = array(); }
		if ( ! is_array( @$data['categories'] ) ) { $data['categories'] = array(); }
		if ( ! is_array( @$data['urls'] ) ) { $data['urls'] = array(); }

		?>
		<fieldset>
			<legend><?php echo esc_html( $label_category ) ?></legend>
			<select name="po_rule_data[<?php echo esc_attr( $name ); ?>][categories][]" multiple="multiple">
			<?php foreach ( $this->categories as $term ) : ?>
			<option value="<?php echo esc_attr( $term->term_id ); ?>"
				<?php selected( in_array( $term->term_id, $data['categories'] ) ); ?>>
				<?php echo esc_html( $term->name ); ?>
			</option>
			<?php endforeach; ?>
			</select>
		</fieldset>

		<fieldset>
			<legend><?php echo esc_html( $label_urls ); ?></legend>
			<?php foreach ( $this->url_types as $key => $label ) : ?>
			<label>
				<input type="checkbox"
					name="po_rule_data[<?php echo esc_attr( $name ); ?>][urls][]"
					value="<?php echo esc_attr( $key ); ?>"
					<?php checked( in_array( $key, $data['urls'] ) ); ?> />
				<?php echo esc_html( $label ); ?>
			</label><br />
			<?php endforeach; ?>
		</fieldset>
		<?php
	}

	/**
	 * Tests if the $test_url matches any pattern defined in the $list.
	 *
	 * @since  4.6
	 * @param  string $posttype
	 * @param  array $url_types
	 * @return bool
	 */
	protected function check_category( $categories, $url_types ) {
		global $post;
		$response = false;
		if ( ! is_array( $categories ) ) { $categories = array(); }
		if ( ! is_array( $url_types ) ) { $url_types = array(); }

		if ( isset( $_REQUEST['categories'] ) ) {
			// Via URL/AJAX
			$cur_cats = json_decode( $_REQUEST['categories'] );
			$cur_single = ( 0 != absint( @$_REQUEST['is_single'] ) );
		} else {
			// Via wp_footer
			$cur_cats = wp_list_pluck( get_the_category( $post->ID ), 'term_id' );
			$cur_single = is_singular();
		}

		if ( $cur_single && in_array( 'singular', $url_types ) ) {
			if ( empty( $categories ) ) {
				$response = true; // Any cat, singular.
			} else {
				foreach ( $cur_cats as $term_id ) {
					if ( in_array( $term_id, $categories ) ) {
						$response = true; // We have a cat.
						break;
					}
				}
			}
		}
		else if ( ! $cur_single && in_array( 'plural', $url_types ) ) {
			if ( empty( $categories ) ) {
				$response = true; // Any cat, archive
			} else {
				foreach ( $cur_cats as $term_id ) {
					if ( in_array( $term_id, $categories ) ) {
						$response = true; // We have a cat.
						break;
					}
				}
			}
		}

		return $response;
	}

};

IncPopupRules::register( 'IncPopupRule_Category' );