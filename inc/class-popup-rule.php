<?php

/**
 * Rule-Collection
 */
class IncPopupRules {
	// List of classes.
	static public $classes = array();

	// List of all rules.
	static public $rules = array();

	/**
	 * Register a new rule class.
	 *
	 * @since  4.6
	 * @param  string $classname Class-name (not object!)
	 */
	static public function register( $classname ) {
		self::$classes[$classname] = new $classname();
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

		foreach ( self::$rules as $prio => $list ) {
			if ( isset( $list[ $key ] ) ) {
				$file = $list[ $key ]->filename;
				break;
			}
		}

		return $file;
	}

	/**
	 * Registers a rule.
	 *
	 * @since 4.6
	 * @param IncPopupRule $obj
	 * @param string $filename
	 * @param string $id
	 * @param string $label
	 * @param string $description
	 * @param string $exclude Optional. Rule-ID that is excluded when this
	 *                rule is activated.
	 * @param  int $priority Defines if the rule is displayed in the top of
	 *                the list (0) or the bottom (100)
	 */
	static public function add_rule( $obj, $filename, $id, $label, $description, $exclude = '', $priority = 10 ) {
		if ( ! isset( self::$rules[ $priority ] ) ) {
			self::$rules[ $priority ] = array();
		}

		self::$rules[$priority][$id] = (object) array(
			'obj' => $obj,
			'filename' => $filename,
			'label' => $label,
			'description' => $description,
			'exclude' => $exclude,
		);

		ksort( self::$rules, SORT_NUMERIC );
	}

	/**
	 * Register hooks.
	 *
	 * @since 1.0.0
	 */
	static public function init() {
		add_filter(
			'popup-rule-label',
			array( __CLASS__, '_label' ),
			10, 2
		);

		add_action(
			'popup-rule-forms',
			array( __CLASS__, '_admin_rule_form' ),
			10, 1
		);

		add_action(
			'popup-rule-switch',
			array( __CLASS__, '_admin_rule_list' ),
			10, 1
		);

		add_filter(
			'popup-save-rules',
			array( __CLASS__, '_save' ),
			10, 1
		);

		add_filter(
			'popup-apply-rules',
			array( __CLASS__, '_apply' ),
			10, 2
		);
	}


	/*-----  INTERNAL FUNCTIONS (handlers)  ------*/


	/**
	 * Filter that returns the rule name if $key is the current rule-ID.
	 * Handles filter `popup-rule-label`
	 *
	 * @since  1.0.0
	 */
	static public function _label( $rule, $key ) {
		foreach ( self::$rules as $prio => $list ) {
			if ( isset( $list[ $key ] ) ) {
				return $list[ $key ]->label;
			}
		}
		return '';
	}

	/**
	 * Apply the rule-logic to the specified popup
	 *
	 * @since  1.0.0
	 * @param  bool $show Current decission whether popup should be displayed.
	 * @param  Object $popup The popup that is evaluated.
	 * @return bool Updated decission to display popup or not.
	 */
	static public function _apply( $show, $popup ) {
		if ( ! $show ) { return false; }

		foreach ( self::$rules as $prio => $list ) {
			foreach ( $list as $key => $rule ) {
				if ( ! $popup->uses_rule( $key ) ) { continue; }

				if ( ! $rule->obj->_apply( $key, $show, $popup ) ) {
					return false;
				}
			}
		}
		return true;
	}

	/**
	 * Update and return the $settings array to save the form values.
	 *
	 * @since  1.0.0
	 * @param  array $data Collection of rule-settings.
	 * @return array The updated rule-settings collection.
	 */
	static public function _save( $data ) {
		foreach ( self::$rules as $prio => $list ) {
			foreach ( $list as $key => $rule ) {
				$data = $rule->obj->_save( $key, $data );
			}
		}
		return $data;
	}

	/**
	 * Display the rule form inside the "active rules" list
	 *
	 * @since  1.0.0
	 * @param  InPopupItem $popup
	 */
	static public function _admin_rule_form( $popup ) {
		foreach ( self::$rules as $prio => $list ) {
			foreach ( $list as $key => $rule ) {
				$rule->obj->_admin_rule_form( $key, $rule, $popup );
			}
		}
	}

	/**
	 * Display the rule-switch in the "all rules" list (no options, only a
	 * function to activate/deactivate the rule)
	 *
	 * @since  1.0.0
	 */
	static public function _admin_rule_list( $popup ) {
		foreach ( self::$rules as $prio => $list ) {
			foreach ( $list as $key => $rule ) {
				$rule->obj->_admin_rule_list( $key, $rule, $popup );
			}
		}
	}
}

IncPopupRules::init();


/*===============================*\
===================================
==                               ==
==           RULE BASE           ==
==                               ==
===================================
\*===============================*/


/**
 * Base class for PopUp conditions
 */
abstract class IncPopupRule {

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
	 * Registers a rule.
	 *
	 * @since 4.6
	 * @param string $id
	 * @param string $label
	 * @param string $description
	 * @param string $exclude
	 * @param int $priority
	 */
	protected function add_rule( $id, $label, $description, $exclude = '', $priority = 10 ) {
		IncPopupRules::add_rule(
			$this,
			$this->filename,
			$id, $label, $description, $exclude, $priority
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
	}

	/**
	 * Apply the rule-logic to the specified popup
	 *
	 * @since  1.0.0
	 * @param  bool $show Current decission whether popup should be displayed.
	 * @param  Object $popup The popup that is evaluated.
	 * @return bool Updated decission to display popup or not.
	 */
	public function _apply( $key, $show, $popup ) {
		if ( ! $show ) { return $show; }

		// Skip the rule if the popup does not use it.
		if ( ! in_array( $key, $popup->rule ) ) { return; }

		$method = 'apply_' . $key;
		if ( method_exists( $this, $method ) ) {
			if ( isset( $popup->rule_data[$key] ) ) {
				$data = $popup->rule_data[$key];
			} else {
				$data = '';
			}

			if ( ! $this->$method( $data, $popup ) ) {
				$show = false;
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
			if ( isset( $popup->rule_data[$key] ) ) {
				$data = $popup->rule_data[$key];
			} else {
				$data = '';
			}

			echo '<div class="rule-form">';
			$this->$method( $data );
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
	public function _save( $key, $data ) {
		$method = 'save_' . $key;
		if ( method_exists( $this, $method ) ) {
			$data[$key] = $this->$method();
		}

		return $data;
	}

	/**
	 * Display the rule form inside the "active rules" list
	 *
	 * @since  1.0.0
	 * @param  InPopupItem $popup
	 */
	public function _admin_rule_form( $key, $data, $popup ) {
		$active = $popup->uses_rule( $key );
		$class = $active ? 'on' : 'off';
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

	/**
	 * Display the rule-switch in the "all rules" list (no options, only a
	 * function to activate/deactivate the rule)
	 *
	 * @since  1.0.0
	 */
	public function _admin_rule_list( $key, $data, $popup ) {
		$active = $popup->uses_rule( $key );
		$class = $active ? 'on' : 'off';
		$exclude = isset( $data->exclude ) ? $data->exclude : '';
		?>
		<li class="rule rule-<?php echo esc_attr( $key ); ?> <?php echo esc_attr( $class ); ?>">
			<div class="wpmui-toggle">
				<input type="checkbox"
					class="wpmui-toggle-checkbox"
					id="rule-<?php echo esc_attr( $key ); ?>"
					data-form="#po-rule-<?php echo esc_attr( $key ); ?>"
					data-exclude="<?php echo esc_attr( $exclude ); ?>"
					name="po_rule[]"
					value="<?php echo esc_attr( $key ); ?>"
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

};