<?php
namespace Sejoli_JV\Admin;

/**
 * The admin-specific functionality of the plugin.
 *
 * @link       https://ridwan-arifandi.com
 * @since      1.0.0
 *
 * @package    Sejoli_JV
 * @subpackage Sejoli_JV/admin
 */

/**
 * The admin-specific functionality of the plugin.
 *
 * Defines the plugin name, version, and two examples hooks for how to
 * enqueue the admin-specific stylesheet and JavaScript.
 *
 * @package    Sejoli_JV
 * @subpackage Sejoli_JV/admin
 * @author     Ridwan Arifandi <orangerdigiart@gmail.com>
 */
class Notification {

	/**
	 * The ID of this plugin.
	 *
	 * @since    1.0.0
	 * @access   private
	 * @var      string    $plugin_name    The ID of this plugin.
	 */
	private $plugin_name;

	/**
	 * The version of this plugin.
	 *
	 * @since    1.0.0
	 * @access   private
	 * @var      string    $version    The current version of this plugin.
	 */
	private $version;

	/**
	 * Series of notification files
	 * @since	1.0.0
	 * @var 	array
	 */
	protected $notification_files = array(
		'jv-profit-user'
	);

	/**
	 * Notification libraries
	 * @since	1.0.0
	 * @var 	array
	 */
	protected $libraries = array();

	/**
	 * Initialize the class and set its properties.
	 *
	 * @since   1.0.0
	 * @param   string    $plugin_name  The name of this plugin.
	 * @param   string    $version      The version of this plugin.
	 */
	public function __construct( $plugin_name, $version ) {

		$this->plugin_name = $plugin_name;
		$this->version     = $version;

	}

	/**
	 * Modification notification directory
	 *
	 * Hooked via filter sejoli/email/template-directory, 	 priority 12
	 * Hooked via filter sejoli/sms/template-directory, 	 priority 12
	 * Hooked via filter sejoli/whatsapp/template-directory, priority 12
	 *
	 * @since 	1.0.0
	 * @param 	string 	$directory_path
	 * @param 	string 	$filename
	 * @param 	string 	$media
	 * @param 	array 	$vars
	 * @return 	string
	 */
	public function set_notification_directory( $directory_path, $filename, $media, $vars ) {

		if( in_array( $filename, $this->notification_files ) ) :
		
			$directory_path = SEJOLI_JV_DIR . 'template/' . $media . '/';
		
		endif;

		return $directory_path;

	}

    /**
     * Add custom notification libraries
     * Hooked via filter sejoli/notification/libraries, priority 12
     * @since   1.0.0
     * @param   $libraries [description]
     */
    public function add_libraries( $libraries ) {

        require_once( SEJOLI_JV_DIR . 'notification/jv-profit.php' );

        $libraries['jv-profit'] = new \Sejoli_JV\Notification\JVProfitNotification;

		$this->libraries = $libraries;

        return $libraries;
    
    }

	/**
	 * Send jv profit notification
	 * Hooked via action sejoli/notification/jv/profit, priority 12
	 * @since 	1.0.0
	 * @param  	array $profit_data
	 * @return 	void
	 */
	public function send_jv_profit_notification( $profit_data ) {

		$profit_data                = (array) $profit_data;
		$unpaid_commission          = sejolisa_price_format( $profit_data['unpaid_commission'] );
		$user                       = sejolisa_get_user( $profit_data['user_id'] );

		$this->libraries['jv-profit']->trigger(
			(array) $profit_data,
			array(
				'unpaid_commission' => sejolisa_price_format( $unpaid_commission ),
				'user_name'         => $user->display_name,
				'user_email'        => $user->user_email,
				'user_phone'        => $user->meta->phone
			));

	}

}