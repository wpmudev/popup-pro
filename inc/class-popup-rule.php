<?php

/**
 * Base class for Pop Up conditions
 */
abstract class IncPopupRule {

	/**
	 * Rule ID.
	 * @var string
	 */
	protected $_id;

	/**
	 * Default settings.
	 * @var array
	 */
	protected $_defaults = array();

	/**
	 * Label and description.
	 * @var array
	 */
	protected $_info = array(
		'title'   => '',
		'message' => '',
	);


	/*---------------  ABSTRACT Functions  ----------------*/

	abstract public function apply_rule( $show, $popover );

	abstract public static function add();


	/*---------------  PUBLIC Functions  ----------------*/

	public function get_admin_interface( $data ) {
		return '';
	}

	public function save_settings( $settings ) {
		if ( empty( $_POST[$this->_id] ) ) {
			return $settings;
		}

		$data = stripslashes_deep( $_POST[$this->_id] );
		$result = array();
		$keys = array_keys( $this->_defaults );
		foreach ( $keys as $key ) {
			if ( empty( $data[$key] ) ) {
				continue;
			}

			$result[$key] = array_filter( array_map( 'wp_strip_all_tags', $data[$key] ) );
		}
		$settings[$this->_id] = $result;
		return $settings;
	}

	public function rule_name( $rule, $key ) {
		if ( $key != $this->_id ) {
			return $rule;
		}

		return $this->_info['message'];
	}

	public function add_main_active_rule( $popover, $check ) {
		$in_use = ! empty( $check[$this->_id] ) ? $check[$this->_id] : false;
		if ( ! $in_use ) {
			return false;
		}
		$this->add_active_rule( $popover, $check );
	}

	public function add_active_rule( $popover, $check ) {
		$data = ! empty( $popover->popover_settings ) ? $popover->popover_settings : false;
		?>
		<div class="popover-operation" id="main-<?php echo esc_attr( $this->_id ); ?>">
			<h2 class="sidebar-name">
				<?php echo esc_html( $this->_info['title'] ); ?>
				<span>
					<a href="#remove"
						class="removelink"
						id="remove-<?php echo esc_attr( $this->_id ); ?>"
						title="<?php printf(
							__( 'Remove %s tag from this rules area.', PO_LANG ),
							esc_html( $this->_info['title'] )
						); ?>">
						<?php _e( 'Remove', PO_LANG ); ?>
					</a>
				</span>
			</h2>
			<div class="inner-operation">
				<p><?php echo esc_html( $this->_info['message'] ); ?></p>
				<?php echo $this->get_admin_interface( $data ); ?>
				<input type="hidden" name="popovercheck[<?php echo esc_attr( $this->_id ); ?>]" value="yes" />
			</div>
		</div>
		<?php
	}

	public function add_draggable_rule( $check ) {
		if ( isset( $check[$this->_id] ) ) {
			return false;
		}

		?>
		<li class="popover-draggable" id="<?php echo esc_attr( $this->_id ); ?>">
			<div class="action action-draggable">
				<div class="action-top closed">
					<a href="#available-actions" class="action-button hide-if-no-js"></a>
					<?php echo esc_html( $this->_info['title'] ); ?>
				</div>
				<div class="action-body closed">
					<?php if ( ! empty( $this->_info['message'] ) ) : ?>
						<p>
							<?php echo esc_html( $this->_info['message'] ); ?>
						</p>
					<?php endif; ?>
					<p>
						<a href="#addtopopover"
							class="action-to-popover"
							title="<?php _e( 'Add this rule to the popover.', PO_LANG ); ?>">
							<?php _e( 'Add this rule to the popover.', PO_LANG ); ?>
						</a>
					</p>
				</div>
			</div>
		</li>
		<?php
	}


	/*---------------  PROTECTED Functions  ----------------*/

	protected function __construct() {
		$this->_add_hooks();
	}

	protected function _get_field_name() {
		$args = func_get_args();
		return esc_attr( $this->_id . '[' . join( '][', $args ) . ']' );
	}

	protected function _get_field_id() {
		$args = func_get_args();
		return esc_attr( $this->_id . join( '-', $args ) );
	}

	protected function _add_hooks() {
		if ( ! $this->_id ) {
			return false;
		}

		add_filter(
			'popup-rule-label',
			array( $this, 'rule_name' ),
			10, 2
		);

		add_action(
			'popover_active_rule_' . $this->_id,
			array( $this, 'add_main_active_rule' ),
			10, 2
		); // Shown

		add_action(
			'popover_additional_rules_main',
			array( $this, 'add_active_rule' ),
			10, 2
		); // Hidden

		add_action(
			'popover_additional_rules_sidebar',
			array( $this, 'add_draggable_rule' )
		);

		add_filter(
			'popup-data-save',
			array( $this, 'save_settings' )
		);

		add_filter(
			'popover_process_rule_' . $this->_id,
			array( $this, 'apply_rule' ),
			10, 2
		);
	}

};