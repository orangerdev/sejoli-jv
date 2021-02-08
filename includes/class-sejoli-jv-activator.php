<?php

use Illuminate\Database\Capsule\Manager as Capsule;

/**
 * Fired during plugin activation
 *
 * @link       https://sejoli.co.id
 * @since      1.0.0
 *
 * @package    Sejoli_JV
 * @subpackage Sejoli_JV/includes
 */

/**
 * Fired during plugin activation.
 *
 * This class defines all code necessary to run during the plugin's activation.
 *
 * @since      1.0.0
 * @package    Sejoli_JV
 * @subpackage Sejoli_JV/includes
 * @author     Sejoli <orangerdigiart@gmail.com>
 */
class Sejoli_JV_Activator {

	/**
	 * Short Description. (use period)
	 *
	 * Long Description.
	 *
	 * @since    1.0.0
	 */
	public static function activate() {

		global $wpdb;

		$table = $wpdb->prefix . 'sejolisa_jv';

		if(!Capsule::schema()->hasTable( $table )):

            Capsule::schema()->create( $table, function($table){

				$table->increments  ('ID');
                $table->datetime    ('created_at');
                $table->datetime    ('updated_at')->default('0000-00-00 00:00:00');
                $table->datetime    ('deleted_at')->default('0000-00-00 00:00:00');
                $table->integer     ('product_id');
                $table->integer     ('user_id'); // Means JV ID
                $table->enum        ('type', array('in', 'out'));
                $table->float       ('value', 12, 2);
                $table->string      ('status', 100)->default('pending');
                $table->text        ('meta_data');

            });
        endif;

	}

}
