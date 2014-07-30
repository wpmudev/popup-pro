<?php

/**
 * Rule-Collection
 */
class IncPopupRules {
	static public $rules = array();

	static public function register( $classname ) {
		self::$rules[] = new $classname();
	}
}


/**
 * Base class for Pop Up conditions
 */
abstract class IncPopupRule {

	/**
	 * Rule details (ID, Label, Description)
	 * @var array
	 */
	public $infos = array();

	/*---------------  OVERWRITABLE Functions  ----------------*/

	/**
	 * Initialize the rule object.
	 * Overwrite this function
	 *
	 * @since  4.6
	 */
	public function init() {
	}

	/**
	 * Apply the rule-logic to the specified popup
	 * Overwrite this function
	 *
	 * @since  1.0.0
	 * @param  bool $show Current decission whether popup should be displayed.
	 * @param  Object $popup The popup that is evaluated.
	 * @return bool Updated decission to display popup or not.
	 */
	public function apply( $show, $popup ) {
		return $show;
	}

	/**
	 * Output the Admin-Form for the active rule.
	 * Overwrite this function
	 *
	 * @since  1.0.0
	 * @param  Object $popup The popup that is edited.
	 * @param  string $key Rule-ID.
	 */
	public function form( $popup, $key ) {
		echo '';
	}

	/**
	 * Update and return the $settings array to save the form values.
	 * Overwrite this function
	 *
	 * @since  1.0.0
	 * @param  array $settings Collection of rule-settings.
	 * @param  string $key Rule-ID.
	 * @return array The updated rule-settings collection.
	 */
	public function save( $settings, $key ) {
		return $settings;
	}

	/*---------------  PUBLIC Functions  ----------------*/

	/**
	 * Create the rule object.
	 * This is _only_ done by IncPopupRules::register() above!
	 *
	 * @since 1.0.0
	 */
	public function __construct() {
		$this->init();
		$this->add_hooks();
	}

	/**
	 * Filter that returns the rule name if $key is the current rule-ID.
	 * Handles filter `popup-rule-label`
	 *
	 * @since  1.0.0
	 */
	public function label( $rule, $key ) {
		if ( isset( $this->infos[ $key ] ) ) {
			return $this->infos[ $key ]->label;
		}
		return $rule;
	}

	/**
	 * Display the rule form inside the "active rules" list
	 *
	 * @since  1.0.0
	 */
	public function admin_active_rule( $popup, $key, $index ) {
		foreach ( $this->infos as $key => $data ) {
			if ( $popup->uses_rule( $key ) ) {
				?>
				<li id="po-rule-<?php echo esc_attr( $index ); ?>" class="rule">
					<div>
						<strong><?php echo esc_html( $data->label ); ?></strong>
					</div>
					<div>
						<?php $this->form( $popup, $key ); ?>
					</div>
				</li>
				<?php
			}
		}
	}

	/**
	 * Display the rule-switch in the "all rules" list (no options, only a
	 * function to activate/deactivate the rule)
	 *
	 * @since  1.0.0
	 */
	public function admin_rule_list( $popup ) {
		foreach ( $this->infos as $key => $data ) {
			$active = $popup->uses_rule( $key );
			$class = $active ? 'on' : 'off';
			?>
			<li class="rule <?php echo esc_attr( $class ); ?>">
				<div class="wpmui-toggle">
					<input type="checkbox" name="po_check[]" class="wpmui-toggle-checkbox" id="rule-<?php echo esc_attr( $key ); ?>" checked>
					<label class="wpmui-toggle-label" for="rule-<?php echo esc_attr( $key ); ?>">
						<span class="wpmui-toggle-inner"></span>
						<span class="wpmui-toggle-switch"></span>
					</label>
				</div>
				<?php echo esc_html( $data->label ); ?>
			</li>
			<?php
		}
	}

	/*---------------  PROTECTED Functions  ----------------*/

	/**
	 * Adds rule-details to the objects infos-collection
	 *
	 * @since 4.6
	 * @param string $id
	 * @param string $label
	 * @param string $description
	 */
	protected function add_info( $id, $label, $description ) {
		$this->infos[ $id ] = (object) array(
			'label' => $label,
			'description' => $description,
		);
	}

	/**
	 * Register hooks.
	 *
	 * @since 1.0.0
	 */
	protected function add_hooks() {
		add_filter(
			'popup-rule-label',
			array( $this, 'label' ),
			10, 2
		);

		add_action(
			'popup-active-rule',
			array( $this, 'admin_active_rule' ),
			10, 3
		);

		add_action(
			'popup-all-rules',
			array( $this, 'admin_rule_list' ),
			10, 1
		);

		add_filter(
			'popup-data-save',
			array( $this, 'save' ),
			10, 2
		);

		add_filter(
			'popup-apply-rules',
			array( $this, 'apply' ),
			10, 2
		);
	}

};