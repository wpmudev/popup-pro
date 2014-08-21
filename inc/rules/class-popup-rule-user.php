<?php
/*
Name:        User status
Plugin URI:  http://premium.wpmudev.org/project/the-pop-over-plugin/
Description: Conditions based on the current user.
Author:      Philipp (Incsub)
Author URI:  http://premium.wpmudev.org
Type:        Rule
Rules:       Visitor is logged in, Visitor is not logged in, Visitor has commented before, Visitor has never commented, Visitor has role, Visitor does not have role
Version:     1.0

NOTE: DON'T RENAME THIS FILE!!
This filename is saved as metadata with each popup that uses these rules.
Renaming the file will DISABLE the rules, which is very bad!
*/

class IncPopupRule_User extends IncPopupRule {

	/**
	 * Initialize the rule object.
	 *
	 * @since  4.6
	 */
	protected function init() {
		$this->filename = basename( __FILE__ );

		// 'login' rule.
		$this->add_rule(
			'login',
			__( 'Visitor is logged in', PO_LANG ),
			__( 'Shows the PopUp if the user is logged in to your site.', PO_LANG ),
			'no_login',
			1
		);

		// 'no_login' rule.
		$this->add_rule(
			'no_login',
			__( 'Visitor is not logged in', PO_LANG ),
			__( 'Shows the PopUp if the user is not logged in to your site.', PO_LANG ),
			'login',
			1
		);

		// 'comment' rule.
		$this->add_rule(
			'comment',
			__( 'Visitor has commented before', PO_LANG ),
			__(
				'Shows the PopUp if the user has already left a comment. ' .
				'You may want to combine this condition with either "Visitor ' .
				'is logged in" or "Visitor is not logged in".', PO_LANG
			),
			'no_comment',
			20
		);

		// 'no_comment' rule.
		$this->add_rule(
			'no_comment',
			__( 'Visitor has never commented', PO_LANG ),
			__(
				'Shows the PopUp if the user has never left a comment. ' .
				'You may want to combine this condition with either "Visitor ' .
				'is logged in" or "Visitor is not logged in".', PO_LANG
			),
			'comment',
			20
		);

		// 'role' rule.
		$this->add_rule(
			'role',
			__( 'Visitor has role', PO_LANG ),
			__( 'Shows the PopUp if the user is logged in and is assigned to a certain role.', PO_LANG ),
			'no_role',
			1
		);

		// 'no_role' rule.
		$this->add_rule(
			'no_role',
			__( 'Visitor does not have role', PO_LANG ),
			__( 'Shows the PopUp if the user is logged in and is not assigned to a certain role.', PO_LANG ),
			'role',
			1
		);

		// -- Initialize rule.

		global $wp_roles;
        $this->roles = $wp_roles->get_names();
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
		return is_user_logged_in();
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
		return ! is_user_logged_in();
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
		return ! $this->did_user_comment();
	}


	/*=============================*\
	=================================
	==                             ==
	==           COMMENT           ==
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
	protected function apply_comment( $data ) {
		return $this->did_user_comment();
	}

	/*==========================*\
	==============================
	==                          ==
	==           ROLE           ==
	==                          ==
	==============================
	\*==========================*/


	/**
	 * Apply the rule-logic to the specified popup
	 *
	 * @since  4.6
	 * @param  mixed $data Rule-data which was saved via the save_() handler.
	 * @return bool Decission to display popup or not.
	 */
	protected function apply_role( $data ) {
		return is_user_logged_in() && $this->user_has_role( $data );
	}

	/**
	 * Output the Admin-Form for the active rule.
	 *
	 * @since  4.6
	 * @param  mixed $data Rule-data which was saved via the save_() handler.
	 */
	protected function form_role( $data ) {
		$this->render_role_form(
			'role',
			__( 'Show to users that have one of these roles:', PO_LANG ),
			$data
		);
	}

	/**
	 * Update and return the $settings array to save the form values.
	 *
	 * @since  4.6
	 * @return mixed Data collection of this rule.
	 */
	protected function save_role() {
		return @$_POST['po_rule_data']['role'];
	}


	/*=============================*\
	=================================
	==                             ==
	==           NO_ROLE           ==
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
	protected function apply_no_role( $data ) {
		return is_user_logged_in() && ! $this->user_has_role( $data );
	}

	/**
	 * Output the Admin-Form for the active rule.
	 *
	 * @since  4.6
	 * @param  mixed $data Rule-data which was saved via the save_() handler.
	 */
	protected function form_no_role( $data ) {
		$this->render_role_form(
			'no_role',
			__( 'Show to users that do not have one of these roles:', PO_LANG ),
			$data
		);
	}

	/**
	 * Update and return the $settings array to save the form values.
	 *
	 * @since  4.6
	 * @return mixed Data collection of this rule.
	 */
	protected function save_no_role() {
		return @$_POST['po_rule_data']['no_role'];
	}


	/*======================================*\
	==========================================
	==                                      ==
	==           HELPER FUNCTIONS           ==
	==                                      ==
	==========================================
	\*======================================*/


	/**
	 * Checks if the user did already post any comments.
	 *
	 * @since  4.6
	 * @return bool
	 */
	protected function did_user_comment() {
		global $wpdb;
		static $Comment = null;

		if ( null === $Comment ) {
			// Guests (and maybe logged in users) are tracked via a cookie.
			$Comment = isset( $_COOKIE['comment_author_' . COOKIEHASH] ) ? 1 : 0;

			if ( ! $Comment && is_user_logged_in() ) {
				// For logged-in users we can also check the database.
				$sql = "
					SELECT COUNT(1)
					FROM {$wpdb->comments}
					WHERE user_id = %s
				";
				$sql = $wpdb->prepare( $sql, get_current_user_id() );
				$count = absint( $wpdb->get_var( $sql ) );
				$Comment = $count > 0;
			}
		}
		return $Comment;
	}

	/**
	 * Renders the roles options-form
	 *
	 * @since  1.0.0
	 * @param  string $name
	 * @param  string $label
	 * @param  array $data
	 */
	protected function render_role_form( $name, $label, $data ) {
		if ( ! is_array( $data ) ) { $data = array(); }
		if ( ! is_array( @$data['roles'] ) ) { $data['roles'] = array(); }

		?>
		<fieldset>
			<legend><?php echo esc_html( $label ) ?></legend>
			<select name="po_rule_data[<?php echo esc_attr( $name ); ?>][roles][]" multiple="multiple">
			<?php foreach ( $this->roles as $role_key => $role_label ) : ?>
			<option value="<?php echo esc_attr( $role_key ); ?>"
				<?php selected( in_array( $role_key, $data['roles'] ) ); ?>>
				<?php echo esc_html( $role_label ); ?>
			</option>
			<?php endforeach; ?>
			</select>
		</fieldset>
		<?php
	}

	/**
	 * Tests if the current user belongs to one of the specified roles.
	 *
	 * @since  1.0.0
	 * @param  array $data Contains the element ['roles']
	 * @return boolean
	 */
	protected function user_has_role( $data ) {
		$result = false;
		if ( ! is_array( $data ) ) { $data = array(); }
		if ( ! is_array( @$data['roles'] ) ) { $data['roles'] = array(); }
		$role_list = $data['roles'];

		$user = wp_get_current_user();
        $user_roles = $user->roles;

        // Can a user have more than one Role? Better be sure and use a loop...
        foreach ( $user_roles as $key ) {
            if ( in_array( $key, $role_list ) ) {
                $result = true;
                break;
            }
        }
        return $result;
	}
};

IncPopupRules::register( 'IncPopupRule_User' );