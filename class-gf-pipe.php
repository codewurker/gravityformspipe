<?php

if ( ! class_exists( 'GFForms' ) ) {
	die();
}

GFForms::include_addon_framework();

/**
 * Gravity Forms Pipe Add-On.
 *
 * @since     1.0
 * @package   GravityForms
 * @author    Rocketgenius
 * @copyright Copyright (c) 2017, Rocketgenius
 */
class GF_Pipe extends GFAddOn {

	/**
	 * Contains an instance of this class, if available.
	 *
	 * @since  1.0
	 * @access private
	 * @var    object $_instance If available, contains an instance of this class.
	 */
	private static $_instance = null;

	/**
	 * Defines the version of the Pipe Add-On.
	 *
	 * @since  1.0
	 * @access protected
	 * @var    string $_version Contains the version, defined from pipe.php
	 */
	protected $_version = GF_PIPE_VERSION;

	/**
	 * Defines the minimum Gravity Forms version required.
	 *
	 * @since  1.0
	 * @access protected
	 * @var    string $_min_gravityforms_version The minimum version required.
	 */
	protected $_min_gravityforms_version = '2.0.7.14';

	/**
	 * Defines the plugin slug.
	 *
	 * @since  1.0
	 * @access protected
	 * @var    string $_slug The slug used for this plugin.
	 */
	protected $_slug = 'gravityformspipe';

	/**
	 * Defines the main plugin file.
	 *
	 * @since  1.0
	 * @access protected
	 * @var    string $_path The path to the main plugin file, relative to the plugins folder.
	 */
	protected $_path = 'gravityformspipe/pipe.php';

	/**
	 * Defines the full path to this class file.
	 *
	 * @since  1.0
	 * @access protected
	 * @var    string $_full_path The full path.
	 */
	protected $_full_path = __FILE__;

	/**
	 * Defines the URL where this Add-On can be found.
	 *
	 * @since  1.0
	 * @access protected
	 * @var    string The URL of the Add-On.
	 */
	protected $_url = 'http://www.gravityforms.com';

	/**
	 * Defines the title of this Add-On.
	 *
	 * @since  1.0
	 * @access protected
	 * @var    string $_title The title of the Add-On.
	 */
	protected $_title = 'Gravity Forms Pipe Add-On';

	/**
	 * Defines the short title of the Add-On.
	 *
	 * @since  1.0
	 * @access protected
	 * @var    string $_short_title The short title.
	 */
	protected $_short_title = 'Pipe';

	/**
	 * Defines if Add-On should use Gravity Forms servers for update data.
	 *
	 * @since  1.0
	 * @access protected
	 * @var    bool
	 */
	protected $_enable_rg_autoupgrade = true;

	/**
	 * Defines the capability needed to access the Add-On settings page.
	 *
	 * @since  1.0
	 * @access protected
	 * @var    string $_capabilities_settings_page The capability needed to access the Add-On settings page.
	 */
	protected $_capabilities_settings_page = 'gravityforms_pipe';

	/**
	 * Defines the capability needed to access the Add-On form settings page.
	 *
	 * @since  1.0
	 * @access protected
	 * @var    string $_capabilities_form_settings The capability needed to access the Add-On form settings page.
	 */
	protected $_capabilities_form_settings = 'gravityforms_pipe';

	/**
	 * Defines the capability needed to uninstall the Add-On.
	 *
	 * @since  1.0
	 * @access protected
	 * @var    string $_capabilities_uninstall The capability needed to uninstall the Add-On.
	 */
	protected $_capabilities_uninstall = 'gravityforms_pipe_uninstall';

	/**
	 * Defines the capabilities needed for the Pipe Add-On
	 *
	 * @since  1.0
	 * @access protected
	 * @var    array $_capabilities The capabilities needed for the Add-On
	 */
	protected $_capabilities = array( 'gravityforms_pipe', 'gravityforms_pipe_uninstall' );

	/**
	 * Contains an instance of the Pipe API library, if available.
	 *
	 * @since  1.0
	 * @access protected
	 * @var    object $api If available, contains an instance of the Pipe API library.
	 */
	protected $api = null;

	/**
	 * Get instance of this class.
	 *
	 * @since  1.0
	 * @access public
	 * @static
	 *
	 * @return $_instance
	 */
	public static function get_instance() {

		if ( null === self::$_instance ) {
			self::$_instance = new self;
		}

		return self::$_instance;

	}

	/**
	 * Register needed pre-initialization hooks.
	 *
	 * @since  1.0
	 * @access public
	 *
	 * @uses GFAddOn::is_gravityforms_supported()
	 */
	public function pre_init() {

		parent::pre_init();

		if ( $this->is_gravityforms_supported() && class_exists( 'GF_Field' ) ) {

			// If account hash is setup, load Pipe Recorder field class.
			if ( $this->get_plugin_setting( 'accountHash' ) ) {
				require_once 'includes/class-gf-field-pipe-recorder.php';
			} else {
				require_once 'includes/class-gf-field-pipe-recorder-incompatible.php';
			}

		}

	}

	/**
	 * Register needed admin hooks.
	 *
	 * @since  1.0
	 * @access public
	 */
	public function init_admin() {

		parent::init_admin();

		add_action( 'gform_field_standard_settings', array( $this, 'add_field_settings_fields' ), 10, 2 );
		add_filter( 'gform_tooltips', array( $this, 'add_tooltips' ) );

	}

	/**
	 * Enqueue needed scripts.
	 *
	 * @since  1.0
	 * @access public
	 *
	 * @return array
	 */
	public function scripts() {
		$min = defined( 'SCRIPT_DEBUG' ) && SCRIPT_DEBUG || isset( $_GET['gform_debug'] ) ? '' : '.min';

		$scripts = array(
			array(
				'handle'  => $this->_slug . '_form_editor',
				'deps'    => array( 'jquery' ),
				'src'     => $this->get_base_url() . "/js/form_editor{$min}.js",
				'version' => $this->_version,
				'enqueue' => array( array( 'admin_page' => array( 'form_editor' ) ) ),
				'strings'   => array(
					'cannot_add' => esc_html__( 'Only one Pipe Recorder field can be added to the form.', 'gravityformspipe' ),
				),
			),
			array(
				'handle'    => $this->_slug . '_pipe',
				'src'       => '//s1.addpipe.com/1.3/pipe.js',
				'version'   => '1.3',
				'enqueue'   => array( array( 'field_types' => array( 'pipe_recorder' ) ) ),
				'in_footer' => true,
			),
			array(
				'handle'    => $this->_slug . '_frontend',
				'deps'      => array( 'jquery', $this->_slug . '_pipe' ),
				'src'       => $this->get_base_url() . "/js/frontend{$min}.js",
				'version'   => $this->_version,
				'enqueue'   => array( array( 'field_types' => array( 'pipe_recorder' ) ) ),
				'strings'   => array(
					'accountHash' => $this->get_plugin_setting( 'accountHash' ),
				),
				'in_footer' => true,
			),
		);

		return array_merge( parent::scripts(), $scripts );

	}

	/**
	 * Enqueue needed stylesheets.
	 *
	 * @since  1.0
	 * @access public
	 *
	 * @return array
	 */
	public function styles() {
		$min = defined( 'SCRIPT_DEBUG' ) && SCRIPT_DEBUG || isset( $_GET['gform_debug'] ) ? '' : '.min';

		$styles = array(
			array(
				'handle'  => $this->_slug . '_form_editor',
				'src'     => $this->get_base_url() . "/css/form_editor{$min}.css",
				'version' => $this->_version,
				'enqueue' => array(
					array(
						'admin_page' => array( 'form_editor' ),
					),
				),
			),
		);

		return array_merge( parent::styles(), $styles );

	}

	/**
	 * Return the plugin's icon for the plugin/form settings menu.
	 *
	 * @since 1.2
	 *
	 * @return string
	 */
	public function get_menu_icon() {

		return file_get_contents( $this->get_base_path() . '/images/menu-icon.svg' );

	}





	// # PLUGIN SETTINGS -----------------------------------------------------------------------------------------------

	/**
	 * Prepare plugin settings fields.
	 *
	 * @since  1.0
	 * @access public
	 *
	 * @return array
	 */
	public function plugin_settings_fields() {

		return array(
			array(
				'description' => sprintf(
					'<p>%s</p><p>%s</p>',
					sprintf(
						esc_html__( "Pipe makes it easy to record videos on your website. Using the Gravity Forms Pipe Add-On, you can add Pipe video recording to your forms. The recorded video content will be available with each form submission. If you don't have a Pipe account you can %ssign up for one here%s.", 'gravityformspipe' ),
						'<a href="https://addpipe.com/signup?trial">',
						'</a>'
					),
					sprintf(
						esc_html__( "You can find your account hash and API key at the top of your Pipe account's %ssettings page%s.", 'gravityformspipe' ),
						'<a href="https://addpipe.com/account">',
						'</a>'
					)
				),
				'fields'      => array(
					array(
						'name'                => 'accountHash',
						'label'               => esc_html__( 'Account Hash', 'gravityformspipe' ),
						'type'                => 'text',
						'class'               => 'medium',
						'error_message'       => esc_html__( 'Invalid Account Hash', 'gravityformspipe' ),
						'feedback_callback'   => array( $this, 'validate_account_hash' ),
					),
					array(
						'name'                => 'apiKey',
						'label'               => esc_html__( 'API Key', 'gravityformspipe' ),
						'type'                => 'text',
						'class'               => 'large',
						'error_message'       => esc_html__( 'Invalid API Key', 'gravityformspipe' ),
						'feedback_callback'   => array( $this, 'initialize_api' ),
					),
				),
			),
		);

	}





	// # FORM SETTINGS -------------------------------------------------------------------------------------------------

	/**
	 * Add Pipe Recorder settings fields to the field settings tab.
	 *
	 * @since  1.0
	 * @access public
	 *
	 * @param int $position The position that the settings will be displayed.
	 * @param int $form_id  The ID of the form being edited.
	 */
	public function add_field_settings_fields( $position, $form_id ) {

		// If this is not the end of the advanced settings, exit.
		if ( 20 !== $position ) {
			return;
		}

		// If API cannot be initialized, exit.
		if ( ! $this->initialize_api() ) {
			return;
		}

		// Prepare available resolutions.
		$resolutions = array( '240p', '300p', '480p', '720p' );

		try {

			// Get environments.
			$environments = $this->api->get_environments();

		} catch ( Exception $e ) {

			// Log that we were unable to retrieve the environments.
			$this->log_error( __METHOD__ . '(): Unable to retrieve environments; ' . $e->getMessage() . ' (' . $e->getCode() . ')' );

			return;

		}

		// Initialize environment options.
		$environment_options = array();

		// Loop through environments.
		foreach ( $environments as $environment ) {

			// Add as option.
			$environment_options[] = '<option value="' . esc_attr( $environment['env_id'] ) . '">' . esc_html( $environment['env_name'] ) . '</option>';

		}

		?>

		<li class="piperecorder_resolution_setting field_setting">
			<label for="piperecorder_resolution" class="section_label">
				<?php esc_html_e( 'Video Resolution', 'gravityformspipe' ); ?>
				<?php gform_tooltip( $this->_slug . '_resolution' ); ?>
			</label>
			<div>
				<?php foreach ( $resolutions as $resolution ) { ?>
				<input type="radio" name="piperecorder_resolution" id="piperecorder_resolution_<?php echo $resolution; ?>" size="10" value="<?php echo $resolution; ?>" />
				<label for="piperecorder_resolution_<?php echo $resolution; ?>" class="inline">
					<?php echo esc_html( $resolution ); ?>
				</label>
				&nbsp;&nbsp;
				<?php } ?>
			</div>
		</li>

		<li class="piperecorder_width_setting field_setting">
			<label for="piperecorder_width" class="section_label">
				<?php esc_html_e( 'Width', 'gravityformspipe' ); ?>
				<?php gform_tooltip( $this->_slug . '_width' ); ?>
			</label>
			<input type="number" id="piperecorder_width" size="35" />
		</li>

		<li class="piperecorder_height_setting field_setting">
			<label for="piperecorder_height" class="section_label">
				<?php esc_html_e( 'Height', 'gravityformspipe' ); ?>
				<?php gform_tooltip( $this->_slug . '_height' ); ?>
			</label>
			<input type="number" id="piperecorder_height" size="35" />
		</li>

		<li class="piperecorder_recording_time_setting field_setting">
			<label for="piperecorder_recording_time" class="section_label">
				<?php esc_html_e( 'Max Recording Time', 'gravityformspipe' ); ?>
				<?php gform_tooltip( $this->_slug . '_recording_time' ); ?>
			</label>
			<input type="number" id="piperecorder_recording_time" size="35" />
		</li>

		<li class="piperecorder_environment_setting field_setting">
			<label for="piperecorder_environment" class="section_label">
				<?php esc_html_e( 'Environment', 'gravityformspipe' ); ?>
				<?php gform_tooltip( $this->_slug . '_environment' ); ?>
			</label>
			<select name="environment" id="piperecorder_environment">
				<?php echo implode( '', $environment_options ); ?>
			</select>
		</li>

		<li class="piperecorder_options_setting field_setting">
			<ul>
				<li>
					<input type="checkbox" id="piperecorder_bottom_menu" />
					<label for="piperecorder_bottom_menu" class="inline">
						<?php esc_html_e( 'Add bottom menu (+30px height)', 'gravityformspipe' ); ?>
						<?php gform_tooltip( $this->_slug . '_bottom_menu' ); ?>
					</label>
				</li>
				<li>
					<input type="checkbox" id="piperecorder_autosave" />
					<label for="piperecorder_autosave" class="inline">
						<?php esc_html_e( 'Autosave videos', 'gravityformspipe' ); ?>
						<?php gform_tooltip( $this->_slug . '_autosave' ); ?>
					</label>
				</li>
                <li>
                    <input type="checkbox" id="piperecorder_audio_only" />
                    <label for="piperecorder_audio_only" class="inline">
						<?php esc_html_e( 'Record audio only', 'gravityformspipe' ); ?>
						<?php gform_tooltip( $this->_slug . '_audio_only' ); ?>
                    </label>
                </li>
				<li>
					<input type="checkbox" id="piperecorder_mirror" />
					<label for="piperecorder_mirror" class="inline">
						<?php esc_html_e( 'Mirror image while recording (unreadable text)', 'gravityformspipe' ); ?>
						<?php gform_tooltip( $this->_slug . '_mirror' ); ?>
					</label>
				</li>
			</ul>
		</li>

		<?php
	}

	/**
	 * Register Pipe Recorder tooltips.
	 *
	 * @since  1.2
	 * @access public
	 *
	 * @param array $tooltips Gravity Forms tooltips.
	 *
	 * @return array
	 */
	public function add_tooltips( $tooltips = array() ) {

		$tooltips[ $this->_slug . '_resolution' ] = sprintf(
			'<h6>%s</h6>%s',
			esc_html__( 'Desktop Video Resolution', 'gravityformspipe' ),
			esc_html__( 'The desired video resolution when recording from desktop devices (older webcams do not support high resolutions). On mobile the video resolution depends on the device.', 'gravityformspipe' )
		);

		$tooltips[ $this->_slug . '_width' ] = sprintf(
			'<h6>%s</h6>%s',
			esc_html__( 'Width', 'gravityformspipe' ),
			esc_html__( 'The width of the video recorder on desktop devices. On mobile devices the width is 100%.', 'gravityformspipe' )
		);

		$tooltips[ $this->_slug . '_height' ] = sprintf(
			'<h6>%s</h6>%s',
			esc_html__( 'Height', 'gravityformspipe' ),
			esc_html__( 'The height of the video recorder (without the bottom menu) on desktop devices. On mobile devices the height is 120px.', 'gravityformspipe' )
		);

		$tooltips[ $this->_slug . '_recording_time' ] = sprintf(
			'<h6>%s</h6>%s',
			esc_html__( 'Maximum Recording Time', 'gravityformspipe' ),
			esc_html__( 'Maximum recording time in seconds.', 'gravityformspipe' )
		);

		$tooltips[ $this->_slug . '_autosave' ] = sprintf(
			'<h6>%s</h6>%s',
			esc_html__( 'Autosave Videos', 'gravityformspipe' ),
			esc_html__( "If you want to save only certain videos, you can disable the autosaving. A [Save] button will be shown in Pipe's desktop video recorder that when clicked will trigger the video saving, conversion and storing processes.", 'gravityformspipe' )
		);

		$tooltips[ $this->_slug . '_mirror' ] = sprintf(
			'<h6>%s</h6>%s',
			esc_html__( 'Mirror Image', 'gravityformspipe' ),
			esc_html__( 'If checked Pipe shows the webcam flipped horizontally, just like the iPhone camera, in a similar way you see yourself in a mirror. With the image flipped, text shown to the webcam can not be read. The final recording will not be flipped/mirrored regardless of this setting.', 'gravityformspipe' )
		);

		$tooltips[ $this->_slug . '_environment' ] = sprintf(
			'<h6>%s</h6>%s',
			esc_html__( 'Environment', 'gravityformspipe' ),
			esc_html__( 'The Pipe environment settings to use.', 'gravityformspipe' )
		);

		return $tooltips;

	}






	// # HELPER METHODS ------------------------------------------------------------------------------------------------

	/**
	 * Initializes Pipe API if credentials are valid.
	 *
	 * @since  1.0
	 * @access public
	 *
	 * @uses GFAddOn::get_plugin_settings()
	 * @uses GFAddOn::log_debug()
	 * @uses GFAddOn::log_error()
	 * @uses GF_Pipe_API::get_states()
	 *
	 * @return bool|null
	 */
	public function initialize_api() {

		// If API is alredy initialized, return.
		if ( ! is_null( $this->api ) ) {
			return true;
		}

		// Load the API library.
		if ( ! class_exists( 'GF_Pipe_API' ) ) {
			require_once( 'includes/class-gf-pipe-api.php' );
		}

		// Get the plugin settings.
		$settings = $this->get_plugin_settings();

		// If the API key is not set, return.
		if ( ! rgar( $settings, 'apiKey' ) ) {
			return null;
		}

		// Log validation step.
		$this->log_debug( __METHOD__ . '(): Validating API Info.' );

		try {

			// Setup a new Pipe object with the API credentials.
			$pipe = new GF_Pipe_API( $settings['apiKey'] );

			// Run an authentication test.
			$pipe->get_account();

			// Log that authentication test passed.
			$this->log_debug( __METHOD__ . '(): API credentials are valid.' );

			// Assign API instance to class.
			$this->api = $pipe;

			return true;

		} catch ( Exception $e ) {

			// Log that authentication test failed.
			$this->log_error( __METHOD__ . '(): API credentials are invalid; ' . $e->getMessage() );

			return false;

		}

	}

	/**
	 * Validate Pipe account hash.
	 *
	 * @since  1.0
	 * @access public
	 *
	 * @param string $account_hash Pipe account hash.
	 *
	 * @uses GFAddOn::get_plugin_setting()
	 * @uses WP_Error::get_error_message()
	 *
	 * @return null|bool
	 */
	public function validate_account_hash( $account_hash = false ) {

		// If no account hash was provided, get it from plugin settings.
		if ( ! $account_hash ) {
			$account_hash = $this->get_plugin_setting( 'accountHash' );
		}

		// If account hash is empty, return.
		if ( rgblank( $account_hash ) ) {

			// Log that we are not validating account hash.
			$this->log_debug( __METHOD__ . '(): Not validating account hash as no account hash was provided.' );

			return null;

		}

		// Validate account hash via Pipe API.
		$response = wp_remote_get( 'https://api.addpipe.com/hash/' . $account_hash );

		// If response threw an error, return.
		if ( is_wp_error( $response ) ) {

			// Log that account hash could not be validated.
			$this->log_error( __METHOD__ . '(): Could not validate account hash; ' . $response->get_error_message() );

			return false;

		}

		// Decode response.
		$response = wp_remote_retrieve_body( $response );
		$response = json_decode( $response, true );

		// Return validation state from API response.
		if ( 'ok' === strtolower( rgar( $response, 'status' ) ) ) {

			// Log that account hash is valid.
			$this->log_debug( __METHOD__ . '(): Account hash is valid.' );

			return true;

		} else {

			// Log that account hash is invalid.
			$this->log_error( __METHOD__ . '(): Account hash is invalid; ' . rgar( $response, 'message' ) );

			return false;

		}

		return false;

	}

}
