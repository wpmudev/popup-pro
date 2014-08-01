<?php

/**
 * Rule-Collection
 */
class IncPopupRules {
	static public $rules = array();

	/**
	 * Register a new rule class.
	 *
	 * @since  4.6
	 * @param  string $classname Class-name (not object!)
	 */
	static public function register( $classname ) {
		self::$rules[] = new $classname();
	}

	/**
	 * Checks which php file defines the specified rule-ID
	 *
	 * @since  4.6
	 * @param  string $key Rule-ID.
	 * @return string filename of the rule-file.
	 */
	static public function file_for_rule( $key ) {
		$file = '';

		foreach ( self::$rules as $obj ) {
			if ( $obj->has_rule( $key ) ) {
				$file = $obj->filename;
				break;
			}
		}

		return $file;
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
	protected $infos = array();

	/**
	 * Name of the file (set by the child class)
	 * @var string
	 */
	public $filename = '';

	/*---------------  OVERWRITABLE functions  ----------------*/

	/**
	 * Initialize the rule object.
	 * Overwrite this function!
	 *
	 * @since  4.6
	 */
	protected function init() {
		/*
		// Register new rule
		$this->add_rule( 'id', 'label', 'description' );

		// Add custom hooks for the rule
		add_action( 'wp_footer', array( $this, 'footer_code' ) );
		*/
	}

	/**
	 * Apply the rule-logic to the specified popup:
	 * Create a function with name "apply_" + rule-ID
	 *
	 * Example:
	 * protected function apply_url( $data ) {
	 *   return true; // Condition for rule "url"
	 * }
	 *
	 * @since  4.6
	 * @param  mixed $data Rule-data which was saved via the save_() handler.
	 * @return bool Decission to display popup or not.
	 */
	protected function apply_( $data ) {
		return true;
	}

	/**
	 * Output the Admin-Form for a single rule:
	 * Create a function with name "form_" + rule-ID
	 *
	 * Example:
	 * protected function form_url( $data ) {
	 *   echo 'Form for rule "url"...';
	 * }
	 *
	 * @since  4.6
	 * @param  mixed $data Rule-data which was saved via the save_() handler.
	 */
	protected function form_( $data ) {
		echo '';
	}

	/**
	 * Update and return rule-config which should be saved in DB.
	 * Create a function with name "save_" + rule-ID
	 *
	 * Example:
	 * protected function save_url() {
	 *   return array();
	 * }
	 *
	 * @since  4.6
	 * @return mixed Data collection of this rule.
	 */
	protected function save_() {
		return false;
	}

	/*---------------  PUBLIC functions  ----------------*/

	/**
	 * Checks if this object defines a rule with the specified ID.
	 *
	 * @since  4.6
	 * @param  string $key Rule-ID
	 * @return bool
	 */
	public function has_rule( $key ) {
		return isset( $this->infos[ $key ] );
	}

	/**
	 * Adds rule-details to the objects infos-collection
	 *
	 * @since 4.6
	 * @param string $id
	 * @param string $label
	 * @param string $description
	 * @param string $exclude Optional. Rule-ID that is excluded when this
	 *                rule is activated.
	 */
	protected function add_info( $id, $label, $description, $exclude = '' ) {
		$this->infos[ $id ] = (object) array(
			'label' => $label,
			'description' => $description,
			'exclude' => $exclude,
		);
	}

	/*---------------  INTERNAL functions  ----------------*/

	/**
	 * Create the rule object.
	 * This is _only_ done by IncPopupRules::register() above!
	 *
	 * @since 1.0.0
	 */
	public function __construct() {
		$this->init();
		$this->_add_hooks();
	}

	/**
	 * Filter that returns the rule name if $key is the current rule-ID.
	 * Handles filter `popup-rule-label`
	 *
	 * @since  1.0.0
	 */
	public function _label( $rule, $key ) {
		if ( isset( $this->infos[ $key ] ) ) {
			return $this->infos[ $key ]->label;
		}
		return $rule;
	}

	/**
	 * Apply the rule-logic to the specified popup
	 *
	 * @since  1.0.0
	 * @param  bool $show Current decission whether popup should be displayed.
	 * @param  Object $popup The popup that is evaluated.
	 * @return bool Updated decission to display popup or not.
	 */
	public function _apply( $show, $popup ) {
		if ( ! $show ) { return $show; }

		foreach ( $this->infos as $key => $rule ) {
			// Skip the rule if the popup does not use it.
			if ( ! in_array( $key, $popup->rule ) ) { continue; }

			$method = 'apply_' . $key;
			if ( method_exists( $this, $method ) ) {
				if ( ! $this->$method( @$popup->rule_data[$key] ) ) {
					$show = false;
					break;
				}
			}
		}

		return $show;
	}

	/**
	 * Output the Admin-Form for the active rule.
	 *
	 * @since  1.0.0
	 * @param  Object $popup The popup that is edited.
	 * @param  string $key Rule-ID.
	 */
	public function _form( $popup, $key ) {
		$method = 'form_' . $key;
		if ( method_exists( $this, $method ) ) {
			echo '<div class="rule-form">';
			$this->$method( @$popup->rule_data[$key] );
			echo '</div>';
		}
	}

	/**
	 * Update and return the $settings array to save the form values.
	 *
	 * @since  1.0.0
	 * @param  array $data Collection of rule-settings.
	 * @return array The updated rule-settings collection.
	 */
	public function _save( $data ) {
		foreach ( $this->infos as $key => $rule ) {
			$method = 'save_' . $key;
			if ( method_exists( $this, $method ) ) {
				$data[$key] = $this->$method();
			}
		}

		return $data;
	}

	/**
	 * Display the rule form inside the "active rules" list
	 *
	 * @since  1.0.0
	 * @param  InPopupItem $popup
	 * @param  string|false $show_key If false: Render the inactive forms
	 *                If string: Render the form for this rule-ID.
	 */
	public function _admin_active_rule( $popup, $show_key ) {
		if ( ! empty( $show_key ) && ! $this->has_rule( $show_key ) ) { return; }

		foreach ( $this->infos as $key => $data ) {
			$active = $popup->uses_rule( $key );
			$class = $active ? 'on' : 'off';

			if ( $active && empty( $show_key ) ) { continue; }
			if ( ! $active && ! empty( $show_key ) ) { continue; }
			if ( $active && $show_key !== $key ) { continue; }

			?>
			<li id="po-rule-<?php echo esc_attr( $key ); ?>"
				class="rule <?php echo esc_attr( $class )?>"
				data-key="<?php echo esc_attr( $key ); ?>">
				<div class="rule-title">
					<?php echo esc_html( $data->label ); ?>
				</div>
				<span class="rule-toggle dashicons"></span>
				<div class="rule-inner">
					<div class="rule-description">
						<em><?php echo esc_html( $data->description ); ?></em>
					</div>
					<?php $this->_form( $popup, $key ); ?>
				</div>
			</li>
			<?php
		}
	}

	/**
	 * Display the rule-switch in the "all rules" list (no options, only a
	 * function to activate/deactivate the rule)
	 *
	 * @since  1.0.0
	 */
	public function _admin_rule_list( $popup ) {
		foreach ( $this->infos as $key => $data ) {
			$active = $popup->uses_rule( $key );
			$class = $active ? 'on' : 'off';
			?>
			<li class="rule rule-<?php echo esc_attr( $key ); ?> <?php echo esc_attr( $class ); ?>">
				<div class="wpmui-toggle">
					<input type="checkbox"
						class="wpmui-toggle-checkbox"
						id="rule-<?php echo esc_attr( $key ); ?>"
						data-form="#po-rule-<?php echo esc_attr( $key ); ?>"
						data-exclude="<?php echo esc_attr( @$data->exclude ); ?>"
						<?php checked( $active ); ?> />
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

	/**
	 * Register hooks.
	 *
	 * @since 1.0.0
	 */
	protected function _add_hooks() {
		add_filter(
			'popup-rule-label',
			array( $this, '_label' ),
			10, 2
		);

		add_action(
			'popup-rule-forms',
			array( $this, '_admin_active_rule' ),
			10, 2
		);

		add_action(
			'popup-all-rules',
			array( $this, '_admin_rule_list' ),
			10, 1
		);

		add_filter(
			'popup-save-rules',
			array( $this, '_save' ),
			10, 1
		);

		add_filter(
			'popup-apply-rules',
			array( $this, '_apply' ),
			10, 2
		);
	}

};