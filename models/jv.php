<?php
namespace SejoliJV\Model;

/**
 * @since   1.5.4
 * @var [type]
 */
Class JV extends \SejoliJV\Model
{
    static protected $ids            = [];
    static protected $table          = 'sejolisa_jv';
    static protected $value          = 0.0;
    static protected $status         = 'pending';
    static protected $type           = 'out';
    static protected $expend_id      = NULL;
    static protected $paid_status    = 0;
    static protected $paid_time      = '0000-00-00 00:00:00';
    // public static $product_id;

    /**
     * Set jv value ID
     * @since   1.5.4
     * @var string
     */
    static public function set_id($id) {
        self::$ids = !is_array($id) ? array($id) : $id;
        return new static;
    }

    /**
     * Set multiple IDS
     * @since   1.5.4
     * @var array
     */
    static public function set_multiple_id($ids) {
        self::$ids = (array) $ids;
        return new static;
    }

    /**
     * Set jv value value
     * @since   1.5.4
     * @var float
     */
    static public function set_value($value) {
        self::$value = floatval($value);
        return new static;
    }

    /**
     * Set status
     * @since   1.5.4
     * @var string
     */
    static public function set_status($status) {
        self::$status = (empty($status)) ? 'pending' : $status;
        return new static;
    }

    /**
     * Set paid status
     * @var bool
     */
    static public function set_paid_status($paid_status) {
        self::$paid_status = boolval($paid_status);
        return new static;
    }

    /**
     * Set paid time
     * @since   1.5.1
     * @var string
     */
    static public function set_paid_time($paid_time) {
        self::$paid_time = $paid_time;
        return new static;
    }

    /**
     * Set type
     * @since   1.5.4
     * @var string
     */
    static public function set_type($type) {
        self::$type = (empty($type)) ? 'out' : $type;
        return new static;
    }

    /**
     * Set expend id
     * @since   1.5.4
     * @var string
     */
    static public function set_expend($expend_id) {
        self::$expend_id = absint( $expend_id );
        return new static;
    }

    public static function setProductId($productId){
        self::$product_id = absint($productId);
        return new static;
    }

    /**
     * Reset properties
     * @since   1.5.4
     * @var [type]
     */
    static public function reset() {

        parent::reset();

        self::$value     = 0.0;
        self::$status    = 'pending';
        self::$ids       = NULL;
        self::$expend_id = NULL;
        self::$type      = 'out';


        return new static;
    }

    /**
     * Validate data
     * @since   1.5.4
     * @return void
     */
    static protected function validate() {

        if(in_array(self::$action, ['add-earning', 'update-status'])) :

            if(0 === self::$order_id) :
                self::set_valid(false);
                self::set_message( __('Order tidak valid', 'sejoli-jv'));
            endif;

        endif;

        if(in_array(self::$action, array('add-expend', 'delete-expend'))) :

            if(empty(self::$expend_id)) :
                self::set_valid(false);
                self::set_message( __('Expend ID tidak valid', 'sejoli-jv'));
            endif;

        endif;

        if(in_array(self::$action, ['add-earning', 'add-expend'])) :

            if(!is_a(self::$user, 'WP_User')) :
                self::set_valid(false);
                self::set_message( __('Affiliasi tidak valid', 'sejoli-jv'));
            endif;

            if(!is_a(self::$product, 'WP_Post') || 'sejoli-product' !== self::$product->post_type) :
                self::set_valid(false);
                self::set_message( __('Produk tidak valid', 'sejoli-jv'));
            endif;

            if(0 === self::$value) :
                self::set_valid(false);
                self::set_message( __('Nilai tidak boleh 0', 'sejoli-jv'));
            endif;

        endif;

        if( in_array(self::$action, array('add-earning', 'update-status')) ) :

            if(!in_array(self::$status, ['pending', 'added', 'cancelled'])) :
                self::set_valid(false);
                self::set_message( sprintf(__('Status nilai %s tidak valid', 'sejoli-jv'), self::$status));
            endif;

        endif;
    }

    /**
     * Get jv values by filter
     * @since   1.5.4
     * @return [type] [description]
     */
    static function get() {
        global $wpdb;

        $table_name = $wpdb->prefix . self::$table;

        $query = "SELECT JV.*, user.display_name AS affiliate_name, product.post_title AS product_name
                  FROM {$wpdb->prefix}your_table_name AS JV
                  LEFT JOIN {$wpdb->posts} AS product ON product.ID = JV.product_id
                  LEFT JOIN {$wpdb->users} AS user ON user.ID = JV.user_id
                  WHERE JV.deleted_at = %s";

        $query = $wpdb->prepare($query, '0000-00-00 00:00:00');

        $query = self::set_filter_query($query);

        $count_query = "SELECT COUNT(*) FROM {$table_name} AS JV
                        LEFT JOIN {$wpdb->posts} AS product ON product.ID = JV.product_id
                        LEFT JOIN {$wpdb->users} AS user ON user.ID = JV.user_id
                        WHERE JV.deleted_at = %s";
        $count_query = $wpdb->prepare($count_query, '0000-00-00 00:00:00');
        $recordsTotal = $wpdb->get_var($count_query);

        $query = self::set_length_query($query);

        $jv_values = $wpdb->get_results($query, ARRAY_A);

        if ($jv_values) {
            self::set_respond('valid', true);
            self::set_respond('jv_values', $jv_values);
            self::set_respond('recordsTotal', $recordsTotal);
            self::set_respond('recordsFiltered', $recordsTotal);
        } else {
            self::set_respond('valid', false);
            self::set_respond('jv_values', []);
            self::set_respond('recordsTotal', 0);
            self::set_respond('recordsFiltered', 0);
        }

        return new static;
    }

    /**
     * Get only first jv value
     * @since   1.5.4
     */
    static function first() {
        global $wpdb;

        $table_name = $wpdb->prefix . self::$table;

        $query = "SELECT JV.*
                  FROM {$table_name} AS JV
                  WHERE JV.ID IN (" . implode(',', array_fill(0, count(self::$ids), '%d')) . ")";

        $query = $wpdb->prepare($query, ...self::$ids);

        $data = $wpdb->get_row($query, ARRAY_A);

        if ($data) :
            self::set_valid(true);
            self::set_respond('jv_value', $data);
        else :
            self::set_valid(false);
        endif;

        return new static;
    }

    /**
     * Update order status
     * @since   1.5.4
     */
    static function update_status() {
        global $wpdb;

        self::set_action('update-status');
        self::validate();

        if (true === self::$valid) :
            $table_name = $wpdb->prefix . self::$table;

            // Prepare the update query
            $wpdb->update(
                "{$table_name}", 
                [
                    'updated_at' => current_time('mysql'),
                    'status'     => self::$status
                ],
                [
                    'order_id' => self::$order_id
                ],
                ['%s', '%s'], 
                ['%d']
            );

            self::set_valid(true);
            self::set_message(
                sprintf(
                    __('JV earning %s status updated to %s successfully', 'sejoli-jv'),
                    self::$order_id,
                    self::$status
                ),
                'success');
        endif;

        return new static;
    }

    /**
     * Add JV earning
     * @since   1.0.0
     */
    static public function add_earning() {
        global $wpdb;

        self::set_action('add-earning');
        self::validate();

        if (true === self::$valid) :
            $table_name = $wpdb->prefix . self::$table;

            // Prepare the insert data
            $earning = [
                'created_at' => current_time('mysql'),
                'order_id'   => self::$order_id,
                'product_id' => self::$product_id,
                'user_id'    => self::$user_id,
                'type'       => 'in',
                'value'      => self::$value,
                'status'     => self::$status,
                'meta_data'  => serialize(self::$meta_data)
            ];

            // Insert the earning record
            $wpdb->insert("{$table_name}", $earning);

            // Get the ID of the inserted row
            $earning['ID'] = $wpdb->insert_id;

            self::set_valid(true);
            self::set_respond('earning', $earning);
        endif;

        return new static;
    }

    /**
     * Add JV expend
     * @since   1.0.0
     */
    static public function add_expend() {
        global $wpdb;

        self::set_action('add-expend');
        self::validate();

        if (true === self::$valid) :
            $table_name = $wpdb->prefix . self::$table;

            // Prepare the insert data
            $expend = [
                'created_at' => current_time('mysql'),
                'expend_id'  => self::$expend_id,
                'product_id' => self::$product_id,
                'user_id'    => self::$user_id,
                'type'       => 'out',
                'value'      => self::$value,
                'status'     => self::$status,
                'meta_data'  => serialize(self::$meta_data)
            ];

            // Insert the expend record
            $wpdb->insert("{$table_name}", $expend);

            // Get the ID of the inserted row
            $expend['ID'] = $wpdb->insert_id;

            self::set_valid(true);
            self::set_respond('expend', $expend);
        endif;

        return new static;
    }

    /**
     * delete JV expend
     * @since   1.0.0
     */
    static public function delete_expend() {
        global $wpdb;

        self::set_action('delete-expend');
        self::validate();

        if (true === self::$valid) :
            $table_name = $wpdb->prefix . self::$table;

            $wpdb->update(
                "{$table_name}",
                [
                    'deleted_at' => current_time('mysql'),
                    'status'     => 'cancelled'
                ],
                [
                    'expend_id' => self::$expend_id
                ],
                ['%s', '%s'],
                ['%d']
            );

            self::set_valid(true);
            self::set_message(
                sprintf(
                    __('JV expend %s deleted successfully', 'sejoli-jv'),
                    self::$expend_id
                ),
                'success');
        endif;

        return new static;
    }

    /**
     * Get all JV earning
     * @since   1.0.0
     */
    static public function get_all_earning($start, $end) {
        global $wpdb;

        if (strtotime($end) && date('H:i:s', strtotime($end)) === '00:00:00') {
            $end = date('Y-m-d 23:59:59', strtotime($end));
        }

        $table_name = $wpdb->prefix . self::$table;

        $product_id_condition = '';
        if (!empty(self::$product_id)) {
            $product_id_condition = "AND JV.product_id = %d";
        }

        $query = "
            SELECT 
                JV.user_id,
                user.display_name,
                user.user_email,
                SUM(CASE WHEN type = 'in' THEN value ELSE 0 END) AS earning_value,
                SUM(CASE WHEN type = 'out' THEN value ELSE 0 END) AS expenditure_value,
                SUM(CASE WHEN type = 'in' AND status = 'added' AND paid_status = 0 THEN JV.value ELSE 0 END) AS unpaid_commission,
                SUM(CASE WHEN type = 'in' AND status = 'added' AND paid_status = 1 THEN JV.value ELSE 0 END) AS paid_commission,
                SUM(CASE WHEN type = 'in' THEN value ELSE -value END) AS total_value
            FROM {$table_name} AS JV
            JOIN {$wpdb->users} AS user ON user.ID = JV.user_id
            WHERE JV.status = 'added'
            AND JV.deleted_at = '0000-00-00 00:00:00'
            AND (
                JV.updated_at BETWEEN %s AND %s
                OR JV.updated_at = '0000-00-00 00:00:00'
            )
            $product_id_condition
            GROUP BY JV.user_id
            ORDER BY total_value DESC
        ";

        // Prepare and execute the query, passing the product_id if provided
        if (!empty(self::$product_id)) {
            $query = $wpdb->prepare($query, $start, $end, self::$product_id);
        } else {
            $query = $wpdb->prepare($query, $start, $end);
        }

        $result = $wpdb->get_results($query);

        if ($result) :
            self::set_valid(true);
            self::set_respond('jv', $result);
        else :
            self::set_valid(false);
            self::set_message(__('No JV data', 'sejoli-jv'));
        endif;

        return new static;
    }

    /**
     * Get single user data
     * @since   1.0.0
     */
    static public function get_single_user($start, $end) {
        global $wpdb;

        if (strtotime($end) && date('H:i:s', strtotime($end)) === '00:00:00') {
            $end = date('Y-m-d 23:59:59', strtotime($end));
        }

        $table_name = $wpdb->prefix . self::$table;

        $product_id_condition = '';
        $prepare_values = array(self::$user_id, $start, $end);

        if (!empty(self::$product_id)) {
            $product_id_condition = "AND JV.product_id = %d";
            $prepare_values[] = self::$product_id;
        }

        $query = "
            SELECT * 
            FROM {$table_name} AS JV
            WHERE JV.status = 'added'
            AND JV.user_id = %d
            AND JV.deleted_at = '0000-00-00 00:00:00'
            AND (
                JV.updated_at BETWEEN %s AND %s
                OR JV.updated_at = '0000-00-00 00:00:00'
            )
            $product_id_condition
        ";

        $query = $wpdb->prepare($query, ...$prepare_values);

        // Prepare and execute the query
        // $query = $wpdb->prepare($query, self::$user_id, $start, $end);
        $result = $wpdb->get_results($query);

        error_log(print_r($query, true));

        if ($result) :
            self::set_valid(true);
            self::set_respond('jv', $result);
        else :
            self::set_valid(false);
            self::set_message(__('No JV data', 'sejoli-jv'));
        endif;

        return new static;
    }

    /**
     * Get all JV related orders
     * @since   1.0.0
     */
    static public function get_orders() {
        global $wpdb;

        $table_name = $wpdb->prefix . self::$table;
        
        $query = "
            SELECT
                data_order.*, 
                user.display_name AS user_name, 
                user.user_email AS user_email, 
                product.post_title AS product_name, 
                coupon.code AS coupon_code, 
                affiliate.display_name AS affiliate_name, 
                JV.value AS earning
            FROM {$wpdb->prefix}sejolisa_orders AS data_order
            JOIN {$table_name} AS JV ON JV.order_id = data_order.ID
            JOIN {$wpdb->users} AS user ON user.ID = data_order.user_id
            JOIN {$wpdb->posts} AS product ON product.ID = data_order.product_id
            LEFT JOIN {$wpdb->prefix}sejolisa_coupons AS coupon ON coupon.ID = data_order.coupon_id
            LEFT JOIN {$wpdb->users} AS affiliate ON affiliate.ID = data_order.affiliate_id
            WHERE JV.user_id = %d
            AND JV.deleted_at = '0000-00-00 00:00:00'
        ";

        // Applying filters
        $query = self::set_filter_query($query);

        $query = $wpdb->prepare($query, self::$user_id);

        $recordsTotal = $wpdb->get_var(str_replace('SELECT data_order.*', 'SELECT COUNT(*)', $query));

        $query = self::set_length_query($query);

        // Get the filtered results
        $orders = $wpdb->get_results($query);

        if ($orders) :
            self::set_respond('valid', true);
            self::set_respond('orders', $orders);
            self::set_respond('recordsTotal', $recordsTotal);
            self::set_respond('recordsFiltered', $recordsTotal);
        else :
            self::set_respond('valid', false);
            self::set_respond('orders', []);
            self::set_respond('recordsTotal', 0);
            self::set_respond('recordsFiltered', 0);
        endif;

        return new static;
    }

    /**
     * Update all single jv profit paid status
     * @since   1.1.3
     * @since   1.5.2   Add added status to jv profit that has been paid
     */
    static public function update_single_jv_profit_paid_status() {
        global $wpdb;

        self::set_action('update-single-jv-profit');
        self::validate();

        if (true === self::$valid) :

            $table_name = $wpdb->prefix . self::$table;

            $query = "
                UPDATE {$table_name}
                SET paid_status = %d
                WHERE user_id = %d
                AND updated_at <= %s
                AND type = 'in'
                AND status = 'added'
            ";

            // Prepare and execute the update query
            $query = $wpdb->prepare($query, self::$paid_status, self::$user_id, self::$paid_time);
            $result = $wpdb->query($query);

            if ($result) :
                self::set_valid(true);
            else :
                self::set_valid(false);
            endif;

        endif;

        return new static;
    }

}
