<?php
namespace DropHtml;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
} // Exit if accessed directly

/**
 * Main class plugin
 */
class Plugin {

    /**
	 * Current plugin object.
	 *
	 * @since 1.0.0
	 *
	 * @var object
	 */
	public static $instance = null;

	/** Magic Methods *********************************************************/

	/**
	 * A dummy constructor to prevent Qazana from being loaded more than once.
	 *
	 * @since 1.0.0
	 * @see drophtml::instance()
	 * @see drophtml();
	 */
	private function __construct() {}

	/**
	 * A dummy magic method to prevent Qazana from being cloned.
	 *
	 * @since 1.0.0
	 */
	public function __clone() {
		_doing_it_wrong( __FUNCTION__, __( 'Cheatin&#8217; huh?', 'drophtml' ), '1.0.0' );
	}

	/**
	 * A dummy magic method to prevent Qazana from being unserialized.
	 *
	 * @since 1.0.0
	 */
	public function __wakeup() {
		_doing_it_wrong( __FUNCTION__, __( 'Cheatin&#8217; huh?', 'drophtml' ), '1.0.0' );
	}

	/**
	 * Magic method for checking the existence of a certain custom field.
	 *
	 * @since 1.0.0
	 */
	public function __isset( $key ) {
		return isset( $this->data[ $key ] );
	}

	/**
	 * Magic method for getting Qazana variables.
	 *
	 * @since 1.0.0
	 */
	public function __get( $key ) {
		return isset( $this->data[ $key ] ) ? $this->data[ $key ] : null;
	}

	/**
	 * Magic method for setting Qazana variables.
	 *
	 * @since 1.0.0
	 */
	public function __set( $key, $value ) {
		$this->data[ $key ] = $value;
	}

	/**
	 * Magic method for unsetting Qazana variables.
	 *
	 * @since 1.0.0
	 */
	public function __unset( $key ) {
		if ( isset( $this->data[ $key ] ) ) {
			unset( $this->data[ $key ] );
		}
	}

	/**
	 * Magic method to prevent notices and errors from invalid method calls.
	 *
	 * @since 1.0.0
	 */
	public function __call( $name = '', $args = [] ) {
		unset( $name, $args );
    }

    /** Private Methods *******************************************************/

	/**
	 * Set some smart defaults to class variables. Allow some of them to be
	 * filtered to allow for early overriding.
	 *
	 * @since 1.0.0
	 *
	 * @uses plugin_dir_path() To generate Qazana plugin path
	 * @uses plugin_dir_url() To generate Qazana plugin url
	 * @uses apply_filters() Calls various filters
	 */
	private function setup_globals() {
		/* Versions **********************************************************/
		$this->db_version = '100'; // Bumped up on api changes that need a db update for compatibility

		/* Paths *************************************************************/

		// Setup some base path and URL information
		$this->file = DROPHTML__FILE__;
		$this->slug = 'wp-drophtml';

		$this->basename     = apply_filters( 'drophtml_plugin_basename', plugin_basename( $this->file ) );
		$this->plugin_dir   = apply_filters( 'drophtml_plugin_dir_path', plugin_dir_path( $this->file ) );
        $this->plugin_url   = apply_filters( 'drophtml_plugin_dir_url', plugins_url( '/', $this->file ) );
        
		// Includes
		$this->includes_dir = apply_filters( 'drophtml_includes_dir', trailingslashit( $this->plugin_dir . 'includes' ) );
		$this->includes_url = apply_filters( 'drophtml_includes_url', trailingslashit( $this->plugin_url . 'includes' ) );

    }

    private function includes() {
		require_once $this->includes_dir . 'post-types/contentsView.php';
		require_once $this->includes_dir . 'post-types/upload.php';
		require_once $this->includes_dir . 'post-types/drop.php';
	}

	/**
	 * Activation Actions
	 *
	 * @since 1.0.0
	 */
	public function activation()
	{
		flush_rewrite_rules();
	}

	/**
	 * Deactivation Actions
	 *
	 * @since 1.0.0
	 */
	public function deactivation()
	{
		flush_rewrite_rules();
	}

	/**
	 * Constructor. Hooks all interactions into correct areas to start
	 * the class.
	 *
	 * @since 1.0.0
	 */
	public function setup_init_actions()
	{
		// Add actions to plugin activation and deactivation hooks
		add_action('activate_' . $this->basename, [$this, 'activation']);
		add_action('deactivate_' . $this->basename, [$this, 'deactivation']);
	}
    
    /**
	 * Getter method for retrieving the object instance.
	 *
	 * @since 1.0.0
	 */
    static public function instance() {
        // Store the instance locally to avoid private static replication
        static $instance = null;

        // Only run these methods if they haven't been ran previously
        if ( null === $instance ) {
            $instance = new self();
            $instance->setup_globals();
			$instance->includes();
			$instance->init_classes();
			$instance->setup_init_actions();
        }

        // Always return the instance
        return $instance;
	}
	
	/**
	 * Loads the plugin classes.
	 *
	 * @since 1.0.0
	 */
	public function init_classes() {
		new Admin\Upload();
	}

}