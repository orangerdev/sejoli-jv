<?php

namespace Sejoli_JV\Admin;

use Carbon_Fields\Container;
use Carbon_Fields\Field;

/**
 * The admin-specific functionality of the plugin.
 *
 * @link       https://sejoli.co.id
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
 * @author     Sejoli <orangerdigiart@gmail.com>
 */
class User {

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
	 * Initialize the class and set its properties.
	 *
	 * @since    1.0.0
	 * @param      string    $plugin_name       The name of this plugin.
	 * @param      string    $version    The version of this plugin.
	 */
	public function __construct( $plugin_name, $version ) {

		$this->plugin_name = $plugin_name;
		$this->version = $version;

	}

    /**
     * Register custom role that sejoli-jv
     * Hooked via action init, priority 10
     * @since   1.0.0
     * @return  void
     */
    public function register_role() {

        global $wp_roles;

		if( !isset( $wp_roles) ) :
			$wp_roles = new WP_Roles();
		endif;

		/**
		 * Update administrator member role
		 */
		$administrator_role = $wp_roles->get_role('administrator');

		$wp_roles->add_role('administrator', 'manage_sejoli_jv_data');


        /**
		 * Create member role
		 */
		$member_role = $wp_roles->get_role('sejoli-member');

		$wp_roles->add_role('sejoli-jv', 'JV Partner', $member_role->capabilities);

		$wp_roles->add_cap('sejoli-jv',  'manage_sejoli_jv_data');

    }

    /**
     * Setup jv data for user profile
     * Hooked via filter sejoli/user/fields, priority 300
     * @since   1.0.0
     * @param   array  $fields
     * @return  array
     */
    public function setup_user_fields( array $fields ) {

        ob_start();

        require( plugin_dir_path( __FILE__ ) . '/partials/user/jv-data.php' );

        $jv_data = ob_get_contents();

        ob_end_clean();

        $fields['jv-data'] = array(
            'title'     => 'JV',
            'fields'    => array(
                Field::make('html', 'sejoli_jv_data')
				->set_html( $jv_data )
            )
        );

        return $fields;

    }

	/**
	 * Set JS code in user profile editor footer
	 * Hooked vi action admin_footer, priority 100
	 * @since 1.0.0
	 */
	public function set_js_footer() {

		global $pagenow;

		if( in_array($pagenow, array('user-edit.php', 'profile.php'))) :

			if('user-edit.php' === $pagenow) :
				$user_id = $_GET['user_id'];
			else :
				$user_id = get_current_user_id();
			endif;

			?>
			<script type="text/javascript">
			(function($){

				'use strict';

				$(document).ready(function(){
					$.ajax({
						url:	'<?php echo site_url('sejoli-ajax/set-for-userdata'); ?>',
						data:	{
							nonce:		'<?php echo wp_create_nonce('sejoli-set-for-userdata'); ?>',
							user_id:	'<?php echo $user_id; ?>'
						},
						type:     'GET',
						dataType: 'json',
						success: function(response) {

							if( 0 < Object.keys(response).length) {

								let ul = $('<ul></ul>');

								$.each(response, function(i, p){
									console.log(i, p);
									ul.append($('<li></li>').html(p));
								});

								$('#sejoli-jv-user-data .content').append(ul);

							} else {
								$('#sejoli-jv-user-data .content').html('<p><?php _e('User tidak memiliki produk JV', 'sejoli'); ?></p>')
							}
						}
					});
				});

			})(jQuery);
			</script>
			<?php
		endif;

	}

}
