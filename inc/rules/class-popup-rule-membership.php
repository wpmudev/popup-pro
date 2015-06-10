<?php
/*
Name:        Membership
Plugin URI:  http://premium.wpmudev.org/project/the-pop-over-plugin/
Description: Conditions based on the users Membership details. <a href="http://premium.wpmudev.org/project/membership/" target="_blank">Learn more &raquo;</a>
Author:      JJ (Incsub)
Author URI:  http://premium.wpmudev.org
Type:        Rule
Rules:       By Membership Level, By Membership Subscription
Limit:       pro
Version:     1.0

NOTE: DON'T RENAME THIS FILE!!
This filename is saved as metadata with each popup that uses these rules.
Renaming the file will DISABLE the rules, which is very bad!
*/

class IncPopupRule_Membership extends IncPopupRule {

	/**
	 * Initialize the rule object.
	 *
	 * @since  4.6
	 */
	protected function init() {
		global $wpdb;
		$this->filename = basename( __FILE__ );

		// 'membership_lvl' rule.
		$this->add_rule(
			'membership_lvl',
			__( 'By Membership Level', PO_LANG ),
			__( 'Shows the PopUp if the user has a certain Membership Level.', PO_LANG ),
			'',
			25
		);

		// 'membership_sub' rule.
		$this->add_rule(
			'membership_sub',
			__( 'By Membership Subscription', PO_LANG ),
			__( 'Shows the PopUp if the user does not have a certain Membership Level.', PO_LANG ),
			'membership',
			25
		);

		// -- Initialize rule.

		$this->is_active = (
			function_exists( 'M_get_membership_active' ) && M_get_membership_active()
		);

		if ( ! $this->is_active ) { return; }

		$prefix = $wpdb->get_blog_prefix();

		$table_lvl = defined( 'MEMBERSHIP_TABLE_LEVELS' ) ? MEMBERSHIP_TABLE_LEVELS : $prefix . 'm_membership_levels';
		$sql = "
			SELECT *
			FROM {$table_lvl}
		";
		$this->levels = $wpdb->get_results( $sql );

		$table_sub = defined( 'MEMBERSHIP_TABLE_SUBSCRIPTIONS' ) ? MEMBERSHIP_TABLE_SUBSCRIPTIONS : $prefix . 'm_subscriptions';
		$sql = "
			SELECT *
			FROM {$table_sub}
		";
		$this->subscriptions = $wpdb->get_results( $sql );
	}



	/*===========================*\
	===============================
	==                           ==
	==           LEVEL           ==
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
	protected function apply_membership_lvl( $data ) {
		return $this->user_has_level( $data );
	}

	/**
	 * Output the Admin-Form for the active rule.
	 *
	 * @since  4.6
	 * @param  mixed $data Rule-data which was saved via the save_() handler.
	 */
	protected function form_membership_lvl( $data ) {
		$this->render_level_form(
			'membership_lvl',
			__( 'Show to users that have one of these Membership Levels:', PO_LANG ),
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
	protected function save_membership_lvl( $data ) {
		lib2()->array->equip( $data, 'membership_lvl' );
		return $data['membership_lvl'];
	}


	/*==================================*\
	======================================
	==                                  ==
	==           SUBSCRIPTION           ==
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
	protected function apply_membership_sub( $data ) {
		return $this->user_has_subscription( $data );
	}

	/**
	 * Output the Admin-Form for the active rule.
	 *
	 * @since  4.6
	 * @param  mixed $data Rule-data which was saved via the save_() handler.
	 */
	protected function form_membership_sub( $data ) {
		$this->render_subscription_form(
			'membership_sub',
			__( 'Show to users that do not have one of these memberships:', PO_LANG ),
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
	protected function save_membership_sub( $data ) {
		lib2()->array->equip( $data, 'membership_sub' );
		return $data['membership_sub'];
	}


	/*======================================*\
	==========================================
	==                                      ==
	==           HELPER FUNCTIONS           ==
	==                                      ==
	==========================================
	\*======================================*/


	/**
	 * Renders the options-form to select membership levels.
	 *
	 * @since  1.0.0
	 * @param  string $name
	 * @param  string $label
	 * @param  array $data
	 */
	protected function render_level_form( $name, $label, $data ) {
		$data = lib2()->array->get( $data );
		lib2()->array->equip( $data, 'membership_lvl' );
		$data['membership_lvl'] = lib2()->array->get( $data['membership_lvl'] );

		if ( ! $this->is_active ) {
			$this->render_plugin_inactive();
			return;
		}

		?>
		<fieldset>
			<legend><?php echo esc_html( $label ) ?></legend>
			<select name="po_rule_data[<?php echo esc_attr( $name ); ?>][membership_lvl][]" multiple="multiple">
			<?php foreach ( $this->levels as $level ) : ?>
			<option value="<?php echo esc_attr( $level->id ); ?>"
				<?php selected( in_array( $level->id, $data['membership_lvl'] ) ); ?>>
				<?php echo esc_html( $level->level_title ); ?>
			</option>
			<?php endforeach; ?>
			</select>
		</fieldset>
		<?php
	}

	/**
	 * Renders the options-form to select Subscriptions.
	 *
	 * @since  1.0.0
	 * @param  string $name
	 * @param  string $label
	 * @param  array $data
	 */
	protected function render_subscription_form( $name, $label, $data ) {
		$data = lib2()->array->get( $data );
		lib2()->array->equip( $data, 'membership_sub' );
		$data['membership_sub'] = lib2()->array->get( $data['membership_sub'] );

		if ( ! $this->is_active ) {
			$this->render_plugin_inactive();
			return;
		}

		?>
		<fieldset>
			<legend><?php echo esc_html( $label ) ?></legend>
			<select name="po_rule_data[<?php echo esc_attr( $name ); ?>][membership_sub][]" multiple="multiple">
			<?php foreach ( $this->subscriptions as $subscription ) : ?>
			<option value="<?php echo esc_attr( $subscription->id ); ?>"
				<?php selected( in_array( $subscription->id, $data['membership_sub'] ) ); ?>>
				<?php echo esc_html( $subscription->sub_name ); ?>
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
					'Membership Plugin</a> is installed and activated.', PO_LANG
				),
				'http://premium.wpmudev.org/project/membership/'
			);
			?>
		</p></div>
		<?php
	}

	/**
	 * Tests if the current user has a specific membership level.
	 *
	 * @since  1.0.0
	 * @param  array $data Contains the element ['membership_lvl']
	 * @return boolean
	 */
	protected function user_has_level( $data ) {
		$result = false;

		if ( $this->is_active ) {
			$data = lib2()->array->get( $data );
			lib2()->array->equip( $data, 'membership_lvl' );
			$data['membership_lvl'] = lib2()->array->get( $data['membership_lvl'] );

			foreach ( $data['membership_lvl'] as $level ) {
				if ( current_user_on_level( $level ) ) {
					$result = true;
					break;
				}
			}
		}

		return $result;
	}

	/**
	 * Tests if the current user has a specific membership subscription.
	 *
	 * @since  1.0.0
	 * @param  array $data Contains the element ['membership_sub']
	 * @return boolean
	 */
	protected function user_has_subscription( $data ) {
		$result = false;

		if ( $this->is_active ) {
			$data = lib2()->array->get( $data );
			lib2()->array->equip( $data, 'membership_sub' );
			$data['membership_sub'] = lib2()->array->get( $data['membership_sub'] );

			foreach ( $data['membership_sub'] as $subscription ) {
				if ( current_user_on_subscription( $subscription ) ) {
					$result = true;
					break;
				}
			}
		}

		return $result;
	}

};

IncPopupRules::register( 'IncPopupRule_Membership' );