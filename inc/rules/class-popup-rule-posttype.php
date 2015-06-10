<?php
/*
Name:        Post Types
Plugin URI:  http://premium.wpmudev.org/project/the-pop-over-plugin/
Description: Adds post type-related rules.
Author:      Ve (Incsub)
Author URI:  http://premium.wpmudev.org
Type:        Rule
Rules:       For specific Post Types, Not for specific Post Types
Limit:       no global, pro
Version:     1.0

NOTE: DON'T RENAME THIS FILE!!
This filename is saved as metadata with each popup that uses these rules.
Renaming the file will DISABLE the rules, which is very bad!
*/

class IncPopupRule_Posttype extends IncPopupRule {

	/**
	 * Initialize the rule object.
	 *
	 * @since  4.6
	 */
	protected function init() {
		$this->filename = basename( __FILE__ );

		if ( IncPopup::use_global() ) { return; }

		// 'posttype' rule.
		$this->add_rule(
			'posttype',
			__( 'For specific Post Types', PO_LANG ),
			__( 'Shows the PopUp on pages that match any of the specified Post Types.', PO_LANG ),
			'no_posttype',
			30
		);

		// 'no_posttype' rule.
		$this->add_rule(
			'no_posttype',
			__( 'Not for specific Post Types', PO_LANG ),
			__( 'Shows the PopUp on pages that do not match any of the specified Post Type.', PO_LANG ),
			'posttype',
			30
		);

		// -- Initialize rule.

		add_filter(
			'popup-ajax-data',
			array( $this, 'inject_ajax_posttype' )
		);

		$this->posttypes = get_post_types(
			array(
				'public' => true,
			),
			'objects'
		);

		$this->url_types = array(
			'singular' => __( 'Singular', PO_LANG ),
			'plural'   => __( 'Archive', PO_LANG ),
		);
	}

	/**
	 * Injects posttype details into the ajax-data collection.
	 * (Required for any ajax loading method)
	 *
	 * @since  4.6
	 */
	public function inject_ajax_posttype( $data ) {
		$posttype = get_post_type();
		$is_singular = is_singular() ? 1 : 0;

		if ( ! is_array( @$data['ajax_data'] ) ) {
			$data['ajax_data'] = array();
		}
		$data['ajax_data']['posttype'] = $posttype;
		$data['ajax_data']['is_single'] = $is_singular;

		return $data;
	}


	/*==============================*\
	==================================
	==                              ==
	==           POSTTYPE           ==
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
	protected function apply_posttype( $data ) {
		if ( ! is_array( $data ) ) { $data = array(); }

		return $this->check_posttype( @$data['posttypes'], @$data['urls'] );
	}

	/**
	 * Output the Admin-Form for the active rule.
	 *
	 * @since  4.6
	 * @param  mixed $data Rule-data which was saved via the save_() handler.
	 */
	protected function form_posttype( $data ) {
		$this->render_form(
			'posttype',
			__( 'Show for these Post Types:', PO_LANG ),
			__( 'Show on these Post Type URLs:', PO_LANG ),
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
	protected function save_posttype( $data ) {
		lib2()->array->equip( $data, 'posttype' );
		return $data['posttype'];
	}


	/*=================================*\
	=====================================
	==                                 ==
	==           NO_POSTTYPE           ==
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
	protected function apply_no_posttype( $data ) {
		if ( ! is_array( $data ) ) { $data = array(); }

		return ! $this->check_posttype( @$data['posttypes'], @$data['urls'] );
	}

	/**
	 * Output the Admin-Form for the active rule.
	 *
	 * @since  4.6
	 * @param  mixed $data Rule-data which was saved via the save_() handler.
	 */
	protected function form_no_posttype( $data ) {
		$this->render_form(
			'no_posttype',
			__( 'Hide for these Post Types:', PO_LANG ),
			__( 'Hide on these Post Type URLs:', PO_LANG ),
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
	protected function save_no_posttype( $data ) {
		lib2()->array->equip( $data, 'no_posttype' );
		return $data['no_posttype'];
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
	 * @param  string $label_posttype
	 * @param  string $label_urls
	 * @param  array $data
	 */
	protected function render_form( $name, $label_posttype, $label_urls, $data ) {
		if ( ! is_array( $data ) ) { $data = array(); }
		if ( ! is_array( @$data['posttypes'] ) ) { $data['posttypes'] = array(); }
		if ( ! is_array( @$data['urls'] ) ) { $data['urls'] = array(); }

		?>
		<fieldset>
			<legend><?php echo esc_html( $label_posttype ) ?></legend>
			<select name="po_rule_data[<?php echo esc_attr( $name ); ?>][posttypes][]" multiple="multiple">
			<?php foreach ( $this->posttypes as $key => $type ) : ?>
			<option value="<?php echo esc_attr( $key ); ?>"
				<?php selected( in_array( $key, $data['posttypes'] ) ); ?>>
				<?php echo esc_html( $type->labels->name ); ?>
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
	protected function check_posttype( $posttype, $url_types ) {
		global $post;
		$response = false;
		if ( ! is_array( $posttype ) ) { $posttype = array(); }
		if ( ! is_array( $url_types ) ) { $url_types = array(); }

		if ( isset( $_REQUEST['posttype'] ) ) {
			// Via URL/AJAX
			$cur_type = $_REQUEST['posttype'];
			$cur_single = ( 0 != absint( @$_REQUEST['is_single'] ) );
		} else {
			// Via wp_footer
			$cur_type = get_post_type();
			$cur_single = is_singular();
		}

		if ( $cur_single && in_array( 'singular', $url_types ) ) {
			if ( empty( $posttype ) ) {
				$response = true; // Any posttype, singular.
			} else {
				$response = in_array( $cur_type, $posttype ); // We have the post type!
			}
		}
		else if ( ! $cur_single && in_array( 'plural', $url_types ) ) {
			if ( empty( $posttype ) ) {
				$response = true; // Any posttype, archive
			} else {
				return in_array( $cur_type, $posttype ); // We have the post type!
			}
		}

		return $response;
	}

};

IncPopupRules::register( 'IncPopupRule_Posttype' );