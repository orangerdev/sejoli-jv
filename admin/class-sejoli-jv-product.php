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
class Product {

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
	 * Set user options
	 * @since	1.0.0
	 * @var 	false|array
	 */
	protected $options = false;

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
     * Get JV user as dropdown options
     * @since   1.0.0
     * @return  array
     */
    public function get_jv_options() {

		if(false === $this->options) :

	        $this->options = array(
	            ''  => __('Pilih JV partner', 'sejoli')
	        );

	        $users = get_users(array(
	            'role__in'    => array('sejoli-jv', 'administrator'),
	            'count_total' => false,
	            'fields'      => array( 'ID', 'display_name' )
	        ));

	        foreach( $users as $user ) :

	            $this->options[$user->ID] = sprintf( '%s (#%s)', $user->display_name, $user->ID );

	        endforeach;

		endif;

        return $this->options;
    }

    /**
     * Set JV-related fields for product editor
     * Hooked via filter sejoli/product/fields, priority 25
     * @since   1.0.0
     * @param   array  $fields
     * @return  array
     */
    public function setup_product_fields( array $fields ) {


        $fields['jv']   = array(
            'title'     => 'JV',
            'fields'    => array(

                Field::make('separator', 'sep_sejoli_jv', __('Pengaturan Joint Venture', 'sejoli'))
                    ->set_classes('sejoli-with-help'),

                Field::make('complex', 'jv_users', __('List JV', 'sejoli'))
                    ->add_fields(array(

                        Field::make('select', 'user', __('User', 'sejoli') )
                            ->set_required( true )
                            ->set_options( array($this, 'get_jv_options') ),

                        Field::make('text', 'value_portion', __('Nilai Bagian', 'sejoli') )
                            ->set_attribute('type', 'number')
                            ->set_required( true )
                            ->set_default_value( 1 )
                            ->set_width( 50 ),

                        Field::make('select', 'value_type', __('Tipe Bagian', 'sejoli') )
                            ->set_options(array(
                                'fixed'         => __('Tetap', 'sejoli'),
                                'percentage'    => __('Persentase', 'sejoli')
                            ))
                            ->set_width( 50 )
                    ))
                    ->set_layout('tabbed-vertical')
                    ->set_header_template("
                        <% if ( user ) { %>
                            #<%- user %> -
                            <% if ( 'fixed' === value_type  ) { %>
                                Rp. <%- value_portion %>
                            <% } else { %>
                                <%- value_portion %>%
                            <% } %>
                        <% } %>
                    ")
            )
        );

        return $fields;
    }

    /**
     * Remove JV data from previous user to disable future calculation
     * Hooked via action pre_post_update, priority 10
     * @since   1.0.0
     * @param   int    $post_id
     * @param   array  $data
     * @return  void
     */
    public function clear_jv_data_in_user( int $post_id, array $data) {

        $current_jv_data = $previous_jv_data = array();

        // get previous JV
        $previous_jvs    = carbon_get_post_meta($post_id, 'jv_users' );

        foreach( (array) $previous_jvs as $previous_jv ) :

            $previous_jv_data[] = intval( $previous_jv['user'] );

        endforeach;

        // get current JV
        if( 0 < count($previous_jv_data) ) :

            if(
                isset($_POST['carbon_fields_compact_input']['_jv_users']) &&
                0 < count($_POST['carbon_fields_compact_input']['_jv_users'])
            ) :

                foreach( $_POST['carbon_fields_compact_input']['_jv_users'] as $jv_data ) :

                    $current_jv_data[] = intval( $jv_data['_user'] );

                endforeach;

            endif;

            $remove_jv_data = array_diff($previous_jv_data, $current_jv_data);

            foreach( (array) $remove_jv_data as $jv_user_id ) :

                $jv_data = get_user_meta( $jv_user_id, 'sejoli_jv_data', true);

                if( isset($jv_data[$post_id] ) ) :

                    unset($jv_data[$post_id]);

                    update_user_meta($jv_user_id, 'sejoli_jv_data', $jv_data);

                endif;

            endforeach;

        endif;

    }

    /**
     * Set JV data from post to user meta
     * Hooked via action save_post_{SEJOLI_PRODUCT_CPT}, priority 10
     * @since   1.0.0
     * @param   int     $post_id
     * @param   WP_Post $post
     * @param   bool    $update
     */
    public function set_jv_data_to_user( int $post_id, \WP_Post $post, bool $update ) {

        if( SEJOLI_PRODUCT_CPT !== $post->post_type ) :
            return;
        endif;

        if( wp_is_post_autosave( $post ) || wp_is_post_revision( $post )) :
            return;
        endif;

		$jv_users = $prev_users = $del_users  = array();
		$prev_data = carbon_get_post_meta($post_id, 'jv_users');

		if( is_array($prev_data) ) :

			foreach( $prev_data as $data ) :

				$prev_users[] = intval($data['user']);

			endforeach;

			$del_users = $prev_users;

		endif;

		// Set JV data to selected user
        if(
            isset($_POST['carbon_fields_compact_input']['_jv_users']) &&
            0 < count($_POST['carbon_fields_compact_input']['_jv_users'])
        ) :

            foreach( $_POST['carbon_fields_compact_input']['_jv_users'] as $jv_data ) :

                $user_id = intval( $jv_data['_user'] );

                $jv_exist_data = (array) get_user_meta( $user_id, 'sejoli_jv_data', true);

                $jv_exist_data[$post_id] = array(
                    'product_id'   => $post_id,
                    'product_name' => $_POST['post_title'],
                    'value'        => absint( $jv_data['_value_portion'] ),
                    'type'         => $jv_data['_value_type']
                );

                if( isset($jv_exist_data[0]) ) :
                    unset($jv_exist_data[0]);
                endif;

				if(in_array($user_id, $prev_users)) :
					$jv_users[] = $user_id;
				endif;

                update_user_meta( $user_id, 'sejoli_jv_data', $jv_exist_data);

            endforeach;

			$del_users = array_diff($del_users, $jv_users);

        endif;

		// Remove JV data from any related users
		if( is_array($del_users) && 0 < count($del_users)) :

			foreach($del_users as $user_id) :

				$jv_data = (array) get_user_meta( $user_id, 'sejoli_jv_data', true);

				if(array_key_exists($post_id, $jv_data)) :
					unset($jv_data);
				endif;

				update_user_meta( $user_id, 'sejoli_jv_data', $jv_data);

			endforeach;
		endif;

    }

}
