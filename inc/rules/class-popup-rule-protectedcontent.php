<?php
/*
Name:        Membership 2
Plugin URI:  http://premium.wpmudev.org/project/the-pop-over-plugin/
Description: Conditions based on the users Membership2 subscriptions. (Former "Protected Content") <a href="http://premium.wpmudev.org/project/membership/" target="_blank">Learn more &raquo;</a>
Author:      Philipp Stracker
Author URI:  http://premium.wpmudev.org
Type:        Rule
Rules:       For Members (Membership 2), For Non-Members (Membership 2)
Limit:       pro
Version:     1.0

NOTE: DON'T RENAME THIS FILE!!
This filename is saved as metadata with each popup that uses these rules.
Renaming the file will DISABLE the rules, which is very bad!

This rule is called PROTECTED CONTENT but it actually handles the MEMBERSHIP 2
integration since the plugin was re-branded as M2.
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
			__( 'For Members (Membership 2)', PO_LANG ),
			__( 'Only shows the PopUp if the user has subscribed to a certain Membership (Membership2 plugin).', PO_LANG ),
			'pc_unsubscription',
			25
		);

		// 'pc_unsubscription' rule.
		$this->add_rule(
			'pc_unsubscription',
			__( 'For Non-Members (Membership 2)', PO_LANG ),
			__( 'Only shows the PopUp if the user has not yet subscribed to a certain Membership (Membership2 plugin).', PO_LANG ),
			'pc_subscription',
			25
		);

		// -- Initialize rule.

		/**
		 * Note we're not using the M2 API yet, because it was introduced only
		 * a few releases back and some people that use older version of M2/PC
		 * will have problems if we do.
		 *
		 * @todo replace with official API function anytime in 2016
		 *
		 * $this->is_active = false;
		 * if ( apply_filters( 'ms_active', false ) ) {
		 *   $this->is_active = true;
		 *   $this->memberships = MS_Plugin::$api->list_memberships( true );
		 * }
		 *
		 */
		$this->is_active = class_exists( 'MS_Plugin' );

		if ( ! $this->is_active ) { return; }

		$args = array(
			'include_base' => false,
			'include_guest' => true,
		);
		$list = MS_Model_Membership::get_memberships( $args );
		$this->memberships = $list;
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
	 * @param  array $data The contents of $_POST['po_rule_data'].
	 * @return mixed Data collection of this rule.
	 */
	protected function save_pc_subscription( $data ) {
		lib2()->array->equip( $data, 'pc_subscription' );
		return $data['pc_subscription'];
	}


	/*====================================*\
	========================================
	==                                    ==
	==           NON-MEMBERSHIP           ==
	==                                    ==
	========================================
	\*====================================*/


	/**
	 * Apply the rule-logic to the specified popup
	 *
	 * @since  1.0
	 * @param  mixed $data Rule-data which was saved via the save_() handler.
	 * @return bool Decission to display popup or not.
	 */
	protected function apply_pc_unsubscription( $data ) {
		return ! $this->user_has_membership( $data );
	}

	/**
	 * Output the Admin-Form for the active rule.
	 *
	 * @since  1.0
	 * @param  mixed $data Rule-data which was saved via the save_() handler.
	 */
	protected function form_pc_unsubscription( $data ) {
		$this->render_subscription_form(
			'pc_unsubscription',
			__( 'Show to users that do not belong to any of the following Memberships:', PO_LANG ),
			$data
		);
	}

	/**
	 * Update and return the $settings array to save the form values.
	 *
	 * @since  1.0
	 * @param  array $data The contents of $_POST['po_rule_data'].
	 * @return mixed Data collection of this rule.
	 */
	protected function save_pc_unsubscription( $data ) {
		lib2()->array->equip( $data, 'pc_unsubscription' );
		return $data['pc_unsubscription'];
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
					'Membership2 Plugin</a> is installed and activated.', PO_LANG
				),
				'http://premium.wpmudev.org/project/membership/'
			);
			?>
		</p></div>
		<?php
	}

	/**
	 * Tests if the current user has a specific membership subscription.
	 *
	 * @since  1.0.0
	 * @param  array $data Contains the element ['pc_subscription']
	 * @return boolean
	 */
	protected function user_has_membership( $data ) {
		$result = false;
		$data = lib2()->array->get( $data );
		$data['pc_subscription'] = lib2()->array->get( $data['pc_subscription'] );

		$member = MS_Plugin::$api->get_current_member();

		foreach ( $data['pc_subscription'] as $membership_id ) {
			if ( $member->has_membership( $membership_id ) ) {
				$result = true;
				break;
			}
		}

		return $result;
	}

};

IncPopupRules::register( 'IncPopupRule_ProtectedContent' );