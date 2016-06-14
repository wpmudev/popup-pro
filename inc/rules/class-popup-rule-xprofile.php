<?php
/*
Name:        XProfile Fields
Plugin URI:  http://premium.wpmudev.org/project/the-pop-over-plugin/
Description: BuddyPress: Examine values in the users extended profile.
Author:      Ve (Incsub)
Author URI:  http://premium.wpmudev.org
Type:        Rule
Rules:       On XProfile match, Not on XProfile match
Limit:       pro
Version:     1.0

NOTE: DON'T RENAME THIS FILE!!
This filename is saved as metadata with each popup that uses these rules.
Renaming the file will DISABLE the rules, which is very bad!
*/

/* start:pro */
class IncPopupRule_XProfile extends IncPopupRule {

	/**
	 * Initialize the rule object.
	 *
	 * @since  4.6
	 */
	protected function init() {
		$this->filename = basename( __FILE__ );

		// 'xprofile' rule.
		$this->add_rule(
			'xprofile',
			__( 'On XProfile match', 'popover' ),
			__( 'Shows the PopUp if the users XProfile field matches the condition.', 'popover' ),
			'no_xprofile',
			10
		);

		// 'no_xprofile' rule.
		$this->add_rule(
			'no_xprofile',
			__( 'Not on XProfile match', 'popover' ),
			__( 'Shows the PopUp if the users XProfile field does not match the condition.', 'popover' ),
			'xprofile',
			10
		);
	}


	/*==============================*\
	==================================
	==                              ==
	==           XPROFILE           ==
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
	protected function apply_xprofile( $data ) {
		$data = $this->sanitize_values( $data );

		return $this->check_xprofile(
			'match',
			$data['field'],
			$data['correlation'],
			$data['value']
		);
	}

	/**
	 * Output the Admin-Form for the active rule.
	 *
	 * @since  4.6
	 * @param  mixed $data Rule-data which was saved via the save_() handler.
	 */
	protected function form_xprofile( $data ) {
		$data = $this->sanitize_values( $data );
		$this->render_form( 'xprofile', $data );
	}

	/**
	 * Update and return the $settings array to save the form values.
	 *
	 * @since  4.6
	 * @param  array $data The contents of $_POST['po_rule_data'].
	 * @return mixed Data collection of this rule.
	 */
	protected function save_xprofile( $data ) {
		lib3()->array->equip( $data, 'xprofile' );
		return $this->sanitize_values( $data['xprofile'] );
	}


	/*=================================*\
	=====================================
	==                                 ==
	==           NO_XPROFILE           ==
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
	protected function apply_no_xprofile( $data ) {
		$data = $this->sanitize_values( $data );

		return $this->check_xprofile(
			'fail',
			$data['field'],
			$data['correlation'],
			$data['value']
		);
	}

	/**
	 * Output the Admin-Form for the active rule.
	 *
	 * @since  4.6
	 * @param  mixed $data Rule-data which was saved via the save_() handler.
	 */
	protected function form_no_xprofile( $data ) {
		$data = $this->sanitize_values( $data );
		$this->render_form( 'no_xprofile', $data );
	}

	/**
	 * Update and return the $settings array to save the form values.
	 *
	 * @since  4.6
	 * @param  array $data The contents of $_POST['po_rule_data'].
	 * @return mixed Data collection of this rule.
	 */
	protected function save_no_xprofile( $data ) {
		lib3()->array->equip( $data, 'no_xprofile' );
		return $this->sanitize_values( $data['no_xprofile'] );
	}


	/*======================================*\
	==========================================
	==                                      ==
	==           HELPER FUNCTIONS           ==
	==                                      ==
	==========================================
	\*======================================*/


	/**
	 * Sanitizes the data parameter so it can be savely used by other functions.
	 *
	 * @since  4.6
	 * @param  mixed $data
	 * @return array
	 */
	protected function sanitize_values( $data ) {
		$data = lib3()->array->get( $data );
		if ( ! isset( $data['field'] ) ) { $data['field'] = ''; }
		if ( ! isset( $data['correlation'] ) ) { $data['correlation'] = ''; }
		if ( ! isset( $data['value'] ) ) { $data['value'] = ''; }

		return $data;
	}

	/**
	 * Renders the input form for PopUp editor.
	 *
	 * @since  4.6
	 * @param  string $name
	 * @param  array $data
	 */
	protected function render_form( $name, $data ) {
		if ( ! class_exists( 'BP_XProfile_Group' ) ) {
			echo '<div class="error below-h2"><p>' .
				__( 'This condition requires that the BuddyPress Extended Profile component is active.', 'popover' ) .
			'</p></div>';
			return;
		}

		$xfields = array();
		$xgroups = BP_XProfile_Group::get( array( 'fetch_fields' => true ) );

		if ( ! empty( $xgroups ) ) {
			foreach ( $xgroups as $xgroup ) {
				$xfields[ $xgroup->name ] = $xgroup->fields;
			}
		}

		if ( empty( $xfields ) ) {
			_e( 'No XProfile fields found.', 'popover' );
		}

		?>
		<label for="po-rule-data-<?php echo esc_attr( $name ); ?>-field">
			<?php _e( 'Field:', 'popover' ); ?>
		</label>

		<select name="po_rule_data[<?php echo esc_attr( $name ); ?>][field]"
			id="po-rule-data-<?php echo esc_attr( $name ); ?>-field">
		<?php foreach ( $xfields as $group => $fields ) : ?>
			<optgroup label="<?php echo esc_attr( $group ); ?>">
			<?php foreach ( $fields as $field ) : ?>
				<option value="<?php echo esc_attr( $field->id ); ?>"
					<?php selected( $field->id, $data['field'] ); ?>>
					<?php echo esc_html( $field->name ); ?>
				</option>
			<?php endforeach; ?>
			</optgroup>
		<?php endforeach; ?>
		</select>


		<select name="po_rule_data[<?php echo esc_attr( $name ); ?>][correlation]">
		<option value="" <?php selected( $data['correlation'], '' ); ?>>
			<?php _e( 'equals', 'popover' ); ?>
		</option>
		<option value="reverse" <?php selected( $data['correlation'], 'reverse' ); ?>>
			<?php _e( 'is not', 'popover' ); ?>
		</option>
		<option value="regex_is" <?php selected( $data['correlation'], 'regex_is' ); ?>>
			<?php _e( 'matches regex', 'popover' ); ?>
		</option>
		<option value="regex_not" <?php selected( $data['correlation'], 'regex_not' ); ?>>
			<?php _e( 'does not match regex', 'popover' ); ?>
		</option>
		</select>

		<input type="text"
			name="po_rule_data[<?php echo esc_attr( $name ); ?>][value]"
			value="<?php echo esc_attr( $data['value'] ); ?>" />
		<?php
	}

	/**
	 * Checks if the current user profile matches the xprofile rule.
	 *
	 * @since  4.6
	 * @param  string $type Type of rule: Either 'match' or 'fail'.
	 * @param  string $field
	 * @param  string $cond
	 * @param  string $value
	 * @return bool
	 */
	protected function check_xprofile( $type, $field, $cond, $po_value ) {
		if ( ! function_exists( 'xprofile_get_field_data' ) ) {
			return true;
		}

		if ( empty( $field ) ) {
			return true;
		}

		$user_value = xprofile_get_field_data(
			$field,
			get_current_user_id(),
			'comma'
		);
		$match = false;

		switch ( $cond ) {
			case 'regex_is':
				$match = preg_match( "#{$po_value}#i", $user_value );
				break;

			case 'regex_not':
				$match = ! preg_match( "#{$po_value}#i", $user_value );
				break;

			case 'reverse':
				$match = $po_value != $user_value;
				break;

			default:
				$match = $po_value == $user_value;
				break;
		}

		if ( $match ) {
			return ('match' == $type);
		} else {
			return ('fail' == $type);
		}
	}
};

IncPopupRules::register( 'IncPopupRule_XProfile' );
/* end:pro */
