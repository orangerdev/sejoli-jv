<?php

/**
 * The file that defines the core plugin class
 *
 * A class definition that includes attributes and functions used across both the
 * public-facing side of the site and the admin area.
 *
 * @link       https://sejoli.co.id
 * @since      1.0.0
 *
 * @package    Sejoli_JV
 * @subpackage Sejoli_JV/includes
 */

/**
 * The core plugin class.
 *
 * This is used to define internationalization, admin-specific hooks, and
 * public-facing site hooks.
 *
 * Also maintains the unique identifier of this plugin as well as the current
 * version of the plugin.
 *
 * @since      1.0.0
 * @package    Sejoli_JV
 * @subpackage Sejoli_JV/includes
 * @author     Sejoli <orangerdigiart@gmail.com>
 */
class Sejoli_JV {

	/**
	 * The loader that's responsible for maintaining and registering all hooks that power
	 * the plugin.
	 *
	 * @since    1.0.0
	 * @access   protected
	 * @var      Sejoli_JV_Loader    $loader    Maintains and registers all hooks for the plugin.
	 */
	protected $loader;

	/**
	 * The unique identifier of this plugin.
	 *
	 * @since    1.0.0
	 * @access   protected
	 * @var      string    $plugin_name    The string used to uniquely identify this plugin.
	 */
	protected $plugin_name;

	/**
	 * The current version of the plugin.
	 *
	 * @since    1.0.0
	 * @access   protected
	 * @var      string    $version    The current version of the plugin.
	 */
	protected $version;

	/**
	 * Define the core functionality of the plugin.
	 *
	 * Set the plugin name and the plugin version that can be used throughout the plugin.
	 * Load the dependencies, define the locale, and set the hooks for the admin area and
	 * the public-facing side of the site.
	 *
	 * @since    1.0.0
	 */
	public function __construct() {
		if ( defined( 'SEJOLI_JV_VERSION' ) ) {
			$this->version = SEJOLI_JV_VERSION;
		} else {
			$this->version = '1.0.0';
		}
		$this->plugin_name = 'sejoli-jv';

		$this->load_dependencies();
		$this->set_locale();
		$this->register_cli();
		$this->define_admin_hooks();
		$this->define_public_hooks();
		$this->define_json_hooks();
	}

	/**
	 * Load the required dependencies for this plugin.
	 *
	 * Include the following files that make up the plugin:
	 *
	 * - Sejoli_JV_Loader. Orchestrates the hooks of the plugin.
	 * - Sejoli_JV_i18n. Defines internationalization functionality.
	 * - Sejoli_JV_Admin. Defines all hooks for the admin area.
	 * - Sejoli_JV_Public. Defines all hooks for the public side of the site.
	 *
	 * Create an instance of the loader which will be used to register the hooks
	 * with WordPress.
	 *
	 * @since    1.0.0
	 * @access   private
	 */
	private function load_dependencies() {

		/**
		 * The class responsible for orchestrating the actions and filters of the
		 * core plugin.
		 */
		require_once plugin_dir_path( dirname( __FILE__ ) ) . 'includes/class-sejoli-jv-loader.php';

		/**
		 * The class responsible for defining internationalization functionality
		 * of the plugin.
		 */
		require_once plugin_dir_path( dirname( __FILE__ ) ) . 'includes/class-sejoli-jv-i18n.php';

	   /*
		* The class responsible for defining all database model
		*/
	   require_once plugin_dir_path( dirname( __FILE__ ) ) . 'models/main.php';
	   require_once plugin_dir_path( dirname( __FILE__ ) ) . 'models/jv.php';

	   /*
		* The class responsible for defining all JSON methods
		*/
	   require_once plugin_dir_path( dirname( __FILE__ ) ) . 'json/main.php';
	   require_once plugin_dir_path( dirname( __FILE__ ) ) . 'json/jv.php';

	   /*
		* The functions
		*/
	   require_once plugin_dir_path( dirname( __FILE__ ) ) . 'functions/jv.php';

		/**
		 * The class responsible for defining all actions that occur in the admin area.
		 */
		require_once plugin_dir_path( dirname( __FILE__ ) ) . 'admin/class-sejoli-jv-admin.php';
		require_once plugin_dir_path( dirname( __FILE__ ) ) . 'admin/class-sejoli-jv-product.php';
		require_once plugin_dir_path( dirname( __FILE__ ) ) . 'admin/class-sejoli-jv-user.php';

		/**
		 * The class responsible for defining all actions that occur in the public-facing
		 * side of the site.
		 */
		require_once plugin_dir_path( dirname( __FILE__ ) ) . 'public/class-sejoli-jv-public.php';
		require_once plugin_dir_path( dirname( __FILE__ ) ) . 'public/class-sejoli-jv-member.php';

		/**
		 * The class responsible for defining CLI command
		 */
		if ( class_exists( 'WP_CLI' ) ) :
			require_once plugin_dir_path( dirname( __FILE__ ) ) . 'cli/jv.php';
		endif;

		$this->loader = new Sejoli_JV_Loader();

	}

	/**
	 * Define the locale for this plugin for internationalization.
	 *
	 * Uses the Sejoli_JV_i18n class in order to set the domain and to register the hook
	 * with WordPress.
	 *
	 * @since    1.0.0
	 * @access   private
	 */
	private function set_locale() {

		$plugin_i18n = new Sejoli_JV_i18n();

		$this->loader->add_action( 'plugins_loaded', $plugin_i18n, 'load_plugin_textdomain' );

	}

	/**
	 * Register CLI command
	 * @since 	1.0.0
	 * @return 	void
	 */
	private function register_cli() {

		if ( !class_exists( 'WP_CLI' ) ) :
			return;
		endif;

		$wallet 	= new Sejoli_JV\CLI\JV();

		WP_CLI::add_command('sejolisa jv', $wallet);
	}

	/**
	 * Register all of the hooks related to the admin area functionality
	 * of the plugin.
	 *
	 * @since    1.0.0
	 * @access   private
	 */
	private function define_admin_hooks() {

		$admin = new Sejoli_JV\Admin( $this->get_plugin_name(), $this->get_version() );

		$this->loader->add_action( 'sejoli/order/new',			$admin, 'set_jv_earning', 2999);

		$product = new Sejoli_JV\Admin\Product( $this->get_plugin_name(), $this->get_version() );

		$this->loader->add_filter( 'sejoli/product/fields', 			$product, 'setup_product_fields', 	25);
		$this->loader->add_action( 'pre_post_update',					$product, 'clear_jv_data_in_user',	10, 2);
		$this->loader->add_action( 'save_post',							$product, 'set_jv_data_to_user',	10, 3);

		$user = new Sejoli_JV\Admin\User( $this->get_plugin_name(), $this->get_version() );

		$this->loader->add_action( 'init',					$user, 'register_role', 		10);
		$this->loader->add_filter( 'sejoli/user/fields',	$user, 'setup_user_fields',		300);
		$this->loader->add_action( 'admin_footer',			$user, 'set_js_footer',			100);

	}

	/**
	 * Register all of the hooks related to the public-facing functionality
	 * of the plugin.
	 *
	 * @since    1.0.0
	 * @access   private
	 */
	private function define_public_hooks() {

		$front = new Sejoli_JV\Front( $this->get_plugin_name(), $this->get_version() );

		$member = new Sejoli_JV\Front\Member( $this->get_plugin_name(), $this->get_version() );

		$this->loader->add_action( 'wp_loaded',							$member, 'set_jv_products',				1010);
		$this->loader->add_action( 'wp_enqueue_scripts',				$member, 'set_localize_js_var',			1111);
		$this->loader->add_filter( 'sejoli/member-area/menu',			$member, 'register_menu', 				11);
		$this->loader->add_filter( 'sejoli/member-area/backend/menu',	$member, 'add_menu_in_backend', 		1111);
		$this->loader->add_filter( 'sejoli/member-area/menu-link',		$member, 'display_link_list_in_menu', 	11, 4);
		$this->loader->add_filter( 'sejoli/template-file',				$member, 'set_template_file', 			111, 2);

	}

	/**
	 * Register all of the hooks related to the JSON functionality
	 * of the plugin.
	 *
	 * @since    1.0.0
	 * @access   private
	 */
	private function define_json_hooks() {

		$jv = new Sejoli_JV\JSON\JV();

		$this->loader->add_action( 'sejoli_ajax_set-for-userdata',				$jv, 'set_for_userdata');
		$this->loader->add_action( 'wp_ajax_sejoli-jv-order-table',				$jv, 'set_for_table');
		$this->loader->add_action( 'wp_ajax_sejoli-jv-order-export-prepare',	$jv, 'prepare_export');
		$this->loader->add_action( 'sejoli_ajax_sejoli-jv-order-export',		$jv, 'export_order');

	}

	/**
	 * Run the loader to execute all of the hooks with WordPress.
	 *
	 * @since    1.0.0
	 */
	public function run() {
		$this->loader->run();
	}

	/**
	 * The name of the plugin used to uniquely identify it within the context of
	 * WordPress and to define internationalization functionality.
	 *
	 * @since     1.0.0
	 * @return    string    The name of the plugin.
	 */
	public function get_plugin_name() {
		return $this->plugin_name;
	}

	/**
	 * The reference to the class that orchestrates the hooks with the plugin.
	 *
	 * @since     1.0.0
	 * @return    Sejoli_JV_Loader    Orchestrates the hooks of the plugin.
	 */
	public function get_loader() {
		return $this->loader;
	}

	/**
	 * Retrieve the version number of the plugin.
	 *
	 * @since     1.0.0
	 * @return    string    The version number of the plugin.
	 */
	public function get_version() {
		return $this->version;
	}

}
