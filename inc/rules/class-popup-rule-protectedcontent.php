<?php
/*
Name:        Protected Content
Plugin URI:  http://premium.wpmudev.org/project/the-pop-over-plugin/
Description: Conditions based on the users Protected Content subscriptions. <a href="http://premium.wpmudev.org/project/protected-content/" target="_blank">Learn more &raquo;</a>
Author:      Philipp Stracker
Author URI:  http://premium.wpmudev.org
Type:        Rule
Rules:       For Members (Protected Content)
Limit:       pro
Version:     1.0

NOTE: DON'T RENAME THIS FILE!!
This filename is saved as metadata with each popup that uses these rules.
Renaming the file will DISABLE the rules, which is very bad!
*/

class IncPopupRule_ProtectedContent extends IncPopupRule {

	/**
	 * A list of all available memberships, even inactive and private ones.
	 *
	 * @since 1.0
	 */
	protected $memberships = array();

	/**
	 * Initialize the rule object.
	 *
	 * @since  4.6
	 */
	protected function init() {
		global $wpdb;
		$this->filename = basename( __FILE__ );

		// 'pc_subscription' rule.
		$this->add_rule(
			'pc_subscription',
			__( 'For Members (Protected Content)', PO_LANG ),
			__( 'Shows the PopUp if the user subscribed to a certain Protected Content Membership.', PO_LANG ),
			'',
			25
		);

		// -- Initialize rule.

		$this->is_active = (
			class_exists( 'MS_Plugin' ) && MS_Plugin::is_enabled()
		);

		if ( ! $this->is_active ) { return; }

		$this->memberships = MS_Model_Membership::get_memberships();
	}


	/*================================*\
	====================================
	==                                ==
	==           MEMBERSHIP           ==
	==                                ==
	====================================
	\*================================*/


	/**
	 * Apply the rule-logic to the specified popup
	 *
	 * @since  1.0
	 * @param  mixed $data Rule-data which was saved via the save_() handler.
	 * @return bool Decission to display popup or not.
	 */
	protected function apply_pc_subscription( $data ) {
		return $this->user_has_membership( $data );
	}

	/**
	 * Output the Admin-Form for the active rule.
	 *
	 * @since  1.0
	 * @param  mixed $data Rule-data which was saved via the save_() handler.
	 */
	protected function form_pc_subscription( $data ) {
		$this->render_subscription_form(
			'pc_subscription',
			__( 'Show to users that belong to one of the following Memberships:', PO_LANG ),
			$data
		);
	}

	/**
	 * Update and return the $settings array to save the form values.
	 *
	 * @since  1.0
	 * @return mixed Data collection of this rule.
	 */
	protected function save_pc_subscription() {
		return $_POST['po_rule_data']['pc_subscription'];
	}


	/*======================================*\
	==========================================
	==                                      ==
	==           HELPER FUNCTIONS           ==
	==                                      ==
	==========================================
	\*======================================*/


	/**
	 * Renders the options-form to select Memberships.
	 *
	 * @since  1.0.0
	 * @param  string $name
	 * @param  string $label
	 * @param  array $data
	 */
	protected function render_subscription_form( $name, $label, $data ) {
		$data = lib2()->array->get( $data );
		$data['pc_subscription'] = lib2()->array->get( $data['pc_subscription'] );

		if ( ! $this->is_active ) {
			$this->render_plugin_inactive();
			return;
		}

		?>
		<fieldset>
			<legend><?php echo esc_html( $label ) ?></legend>
			<select name="po_rule_data[<?php echo esc_attr( $name ); ?>][pc_subscription][]" multiple="multiple">
			<?php foreach ( $this->memberships as $membership ) :
				$is_sel = in_array( $membership->id, $data['pc_subscription'] );
				$ext = '';
				if ( ! $membership->active ) {
					$ext = ' (' . __( 'inactive', PO_LANG ) . ')';
				}
				?>
			<option value="<?php echo esc_attr( $membership->id ); ?>"
				<?php selected( $is_sel ); ?>>
				<?php echo esc_html( $membership->name . $ext ); ?>
			</option>
			<?php endforeach; ?>
			</select>
		</fieldset>
		<?php
	}

	/**
	 * Displays a warning message in case the Membership plugin is not active.
	 *
	 * @since  1.0.0
	 */
	protected function render_plugin_inactive() {
		?>
		<div class="error below-h2"><p>
			<?php
			printf(
				__(
					'This condition requires that the <a href="%s" target="_blank">' .
					'Protected Content Plugin</a> is installed and activated.', PO_LANG
				),
				'http://premium.wpmudev.org/project/protected-content/'
			);
			?>
		</p></div>
		<?php
	}

	/**
	 * Tests if the current user has a specific membership subscription.
	 *
	 * @since  1.0.0
	 * @param  array $data Contains the element ['membership_sub']
	 * @return boolean
	 */
	protected function user_has_membership( $data ) {
		$result = false;
		$data = lib2()->array->get( $data );
		$data['pc_subscription'] = lib2()->array->get( $data['pc_subscription'] );

		$member = MS_Model_Member::get_current_member();

		foreach ( $member->subscriptions as $subscription ) {
			if ( in_array( $subscription->membership_id, $data['pc_subscription'] ) ) {
				$result = true;
				break;
			}
		}

		return $result;
	}

};

IncPopupRules::register( 'IncPopupRule_ProtectedContent' );