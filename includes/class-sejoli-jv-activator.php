<?php

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

        $table_exists = $wpdb->get_var("SHOW TABLES LIKE '$table'");

        if (!$table_exists) :

            $charset_collate = $wpdb->get_charset_collate();

            $sql = "
                CREATE TABLE $table (
                    ID INT(11) NOT NULL AUTO_INCREMENT,
                    created_at DATETIME NOT NULL,
                    updated_at DATETIME DEFAULT '0000-00-00 00:00:00',
                    deleted_at DATETIME DEFAULT '0000-00-00 00:00:00',
                    order_id INT(11) NOT NULL,
                    expend_id INT(11) DEFAULT NULL,
                    product_id INT(11) NOT NULL,
                    user_id INT(11) NOT NULL,
                    type ENUM('in', 'out') NOT NULL,
                    value FLOAT(12,2) NOT NULL,
                    status VARCHAR(100) DEFAULT 'pending',
                    meta_data TEXT NOT NULL,
                    PRIMARY KEY (ID)
                ) $charset_collate;
            ";

            require_once( ABSPATH . 'wp-admin/includes/upgrade.php' );
            dbDelta( $sql );

        endif;

        $column_exists = $wpdb->get_results("SHOW COLUMNS FROM $table LIKE 'paid_status'");

        if (!$column_exists) :
            $wpdb->query("ALTER TABLE $table ADD COLUMN paid_status TINYINT(1) DEFAULT 0 AFTER meta_data");
        endif;

    }

}
