<?php
namespace SejoliJV;

Class Model
{
    static protected $ids          = NULL; // multiple $ids
    static protected $id           = NULL;
    static protected $user         = NULL;
    static protected $user_id      = NULL;
    static protected $affiliate    = NULL;
    static protected $affiliate_id = NULL;
    static protected $order        = NULL;
    static protected $order_id     = NULL;
    static protected $product      = NULL;
    static protected $product_id   = NULL;
    static protected $coupon_id    = NULL;
    static protected $valid        = true;
    static protected $respond      = [];
    static protected $action       = '';
    static protected $messages     = [];
    static protected $table        = NULL;
    static protected $status       = NULL;
    static protected $meta_data    = [];
    static protected $filter       = [
        'start'     => NULL,
        'length'    => NULL,
        'order'     => NULL,
        'search'    => NULL,
    ];

    static protected $chart = [
        'start_date' => NULL,
        'end_date'   => NULL,
        'type'       => 'daily'
    ];


    /**
     * Table define
     * @return [type] [description]
     */
    static protected function table()
    {
        global $wpdb;

        $prefix = $wpdb->prefix;

        return $prefix.self::$table;
    }

    /**
     * Reset data
     * @var [type]
     */
    static public function reset()
    {
        self::$table        = NULL;
        self::$ids          = NULL;
        self::$id           = NULL;
        self::$user         = NULL;
        self::$user_id      = NULL;
        self::$affiliate    = NULL;
        self::$affiliate_id = NULL;
        self::$order        = NULL;
        self::$order_id     = NULL;
        self::$product      = NULL;
        self::$product_id   = NULL;
        self::$status       = NULL;
        self::$valid        = true;
        self::$meta_data    = [];
        self::$respond      = [];
        self::$messages     = [];
        self::$filter       = [
            'start'     => NULL,
            'length'    => NULL,
            'order'     => NULL,
            'search'    => NULL
        ];

        self::$chart = [
            'start_date' => NULL,
            'end_date'   => NULL,
            'type'       => 'daily'
        ];


        self::$action       = '';

        return new static;
    }

    /**
     * Set ID to be integer
     */
    static public function set_id($id)
    {
        self::$id = absint($id);
        return new static;
    }

    /**
     * Set multiple IDS
     * @since   1.1.10
     */
    static public function set_multiple_id($ids) {
        self::$ids = array_map('intval', (array) $ids);
        return new static;
    }

    /**
     * Set user
     * @var [type]
     */
    static public function set_user($user)
    {
        if(is_a($user,'WP_User')) :
            self::$user_id = $user->ID;
            self::$user    = $user;
        else :
            self::set_user_id($user);
            self::$user  = get_user_by('id', $user);
        endif;

        return new static;
    }

    /**
     * set user id into vaiable
     * @param [type] $id [description]
     */
    static public function set_user_id( $user_id )
    {
        self::$user_id = absint($user_id);
        self::$user    = get_userdata(self::$user_id);
        return new static;
    }

    /**
     * set user id into vaiable
     * @param [type] $id [description]
     */
    static public function set_affiliate_id( $affiliate_id )
    {
        self::$affiliate_id = absint($affiliate_id);
        self::$affiliate    = get_userdata(self::$affiliate_id);
        return new static;
    }

    /**
     * set order id to variable
     * @param [type] $order_id [description]
     */
    static public function set_order_id( $order )
    {
        self::$order_id = absint($order);
        return new static;
    }

    /**
     * set product id to variable
     * @param [type] $product_id [description]
     */
    static public function set_product_id( $product )
    {
        self::$product_id = absint($product);
        self::$product    = get_post($product);
        return new static;
    }

    /**
     * set product id to variable
     * @param [type] $product_id [description]
     */
    static public function set_product($product )
    {
        self::$product = $product;
        return new static;
    }

    /**
     * set coupon id and force it to be integer value
     * @param mixed $product_id
     */
    static public function set_coupon_id( $coupon )
    {
        self::$coupon_id = absint($coupon);
        return new static;
    }

    /**
     * set meta data value
     * @param mixed
     * @var   serialize
     */
    static public function set_meta_data( $meta_data )
    {
        self::$meta_data = $meta_data;
        return new static;
    }

    /**
     * Set filter data
     * @param string        $key
     * @param string|array  $value
     * @param string        $compare
     */
    static public function set_filter(string $key, $value, $compare = NULL) {
        self::$filter['search'][] = [
            'name'    => $key,
            'val'     => $value,
            'compare' => $compare
        ];

        return new static;
    }

    /**
     * Set filters from array
     * @param array $filter
     */
    static public function set_filter_from_array(array $filters) {

        foreach($filters as $key => $value) :
            if(!is_null($value)) :
                self::$filter['search'][] = [
                    'name' => $key,
                    'val'  => $value
                ];
            endif;
        endforeach;

        return new static;
    }

    /**
     * Set length and order query
     * @since   1.0.0
     */
    static protected function set_length_query($query) {

        if ( 0 < self::$filter['start'] ) :
            $start = intval( self::$filter['start'] );
            $query->offset( $start );
        endif;

        if ( 0 < self::$filter['length']) :
            $length = intval( self::$filter['length'] );
            $query->limit( $length );
        endif;

        if(!is_null(self::$filter['order']) && is_array(self::$filter['order'])) :
            foreach(self::$filter['order'] as $column => $sort) :
                $query->orderBy($column, $sort);
            endforeach;
        else :
            $query->orderBy( 'ID', 'desc' );
        endif;

        return $query;
    }

    /**
     * Set filter data to query
     */
    static protected function set_filter_query($query)
    {
        if ( !is_null( self::$filter['search'] ) && is_array( self::$filter['search'] ) ) :

            foreach ( self::$filter['search'] as $key => $value ) :

                if( 'product_id' === $value['name'] ) :
                    $value['name'] = 'JV.product_id';
                elseif( 'created_at' === $value['name'] ) :
                    $value['name'] = 'JV.created_at';
                elseif( 'type' === $value['name'] ) :
                    $value['name'] = 'JV.type';
                elseif( 'status' === $value['name'] ) :
                    $value['name'] = 'data_order.status';
                endif;

                if ( !empty( $value['val'] ) ) :

                    if(is_array($value['val'])) :

                        if(isset($value['compare']) && 'NOT IN' === $value['compare'] ) :
                            $query->whereNotIn($value['name'], $value['val']);
                        else :
                            $query->whereIn( $value['name'],$value['val'] );
                        endif;

                    elseif(isset($value['compare']) && !is_null($value['compare'])) :

                        if('NOT IN' === $value['compare'] ) :
                            error_log(print_r($value, true));
                            $query->whereNotIn($value['name'], $value['val']);
                        else :
                            $query->where( $value['name'], $value['compare'], $value['val']);
                        endif;

                    else :

                        $query->where( $value['name'],$value['val'] );

                    endif;

                elseif( false === boolval($value['val']) ) :
                    $query->where( $value['name'],$value['val'] );
                endif;
            endforeach;
        endif;

        return $query;
    }

    /**
     * Set data start
     */
    static public function set_data_start($start) {
        self::$filter['start'] = absint($start);
        return new static;
    }

    /**
     * Set data length
     */
    static public function set_data_length($length) {
        self::$filter['length'] = absint($length);
        return new static;
    }

    /**
     * Set order data
     */
    static public function set_data_order($column, $sort) {
        self::$filter['order'][$column] = $sort;
        return new static;
    }

    /**
     * Set action value
     * @var [type]
     */
    static public function set_action($action = '')
    {
        self::$action = $action;
        return new static;
    }

    /**
     * Set chart start date
     * @since   1.0.0
     */
    static public function set_chart_start_date($start_date = '') {
        self::$chart['start_date'] = (empty($start_date)) ? date('Y-m-d', strtotime('-30 days')) : $start_date;
        self::$filter['search'][]  = [
            'name'    => 'created_at',
            'val'     => self::$chart['start_date'].' 00:00:00',
            'compare' => '>='
        ];

        return new static;
    }

    /**
     * Set chart start date
     * @since   1.0.0
     */
    static public function set_chart_end_date($end_date = '') {
        self::$chart['end_date']  = (empty($end_date)) ? date('Y-m-d') : $end_date;
        self::$filter['search'][] = [
            'name'    => 'created_at',
            'val'     => self::$chart['end_date'].' 23:59:59',
            'compare' => '<='
        ];
        return new static;
    }

    /**
     * Calculate to choose what chart type based on range date
     */
    static protected function calculate_chart_range_date() {

        if(!empty(self::$chart['end_date']) && !empty(self::$chart['start_date'])) :

            $end_time   = strtotime(self::$chart['end_date']);
            $start_time = strtotime(self::$chart['start_date']);
            $day_range  = ($end_time - $start_time) / DAY_IN_SECONDS;

            if(31 >= $day_range ) :
                self::$chart['type'] = 'date';
            elseif(480 < $day_range) :
                self::$chart['type'] = 'year';
            else :
                self::$chart['type'] = 'month';
            endif;
        endif;

    }

    /**
     * Set valid
     * @var bool
     */
    static public function set_valid($valid)
    {
        self::$respond['valid'] = self::$valid = $valid;
        return new static;
    }

    /**
     * Set respond messages
     * @param string $message
     */
    static protected function set_message($message = '', $type = 'error')
    {
        self::$messages[$type][] = $message;
        self::set_respond('messages',self::$messages);
    }

    /**
     * Clean respond
     * @var [type]
     */
    static protected function clear_respond()
    {
        self::$respond = [];
    }

    /**
     * Set respond meta
     * @var [type]
     */
    static protected function set_respond($key,$value)
    {
        self::$respond[$key] = $value;
    }

    /**
     * Return the respond
     * @return mixed
     */
    public function respond()
    {
        $respond = self::$respond;
        self::$respond = [];
        return $respond;
    }
}
