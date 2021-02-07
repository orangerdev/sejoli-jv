<?php
namespace SejoliJV;

Class JSON
{
    /**
     * Construction
     */
    public function __construct() {

    }

    /**
     * Set filter args
     * @since  1.0.0
     * @param  array $args
     * @return array
     */
    protected function set_filter_args($args) {

        $filter = [];

        if(is_array($args) && 0 < count($args)) :
            foreach($args as $_filter) :
                if(
                    !empty($_filter['val']) &&
                    'sejoli-nonce' != $_filter['name'] &&
                    '_wp_http_referer' != $_filter['name']
                ) :
                    if('ID' == $_filter['name']) :
                        $filter[$_filter['name']] = explode(',', $_filter['val']);
                    else :
                        $filter[$_filter['name']] = $_filter['val'];
                    endif;
                endif;
            endforeach;
        endif;


        return $filter;
    }

    /**
     * Set table args
     * @since   1.0.0
     * @param   array $args
     * @return  array
     */
    protected function set_table_args(array $args) {

        $filter = NULL;
        $args   = wp_parse_args($args,[
			'start'  => 0,
			'length' => 10,
			'draw'	 => 1,
            'filter' => [],
            'search' => []
        ]);

        $search = [[
            'name' => 'users',
            'val'  => isset($args['search']['value']) ? $args['search']['value'] : NULL,
        ]];

        $order = array(
            0 => [
            'column'=> 'ID',
            'sort'	=> 'desc'
        ]);

        $columns = [];

        if(isset($args['columns'])) :
            foreach( $args['columns'] as $i => $_column ) :
                $columns[$i] = $_column['data'];
            endforeach;
        else :

            $columns['ID'] = 'desc';

        endif;

        if ( isset( $args['order'] ) && 0 < count( $args['order'] ) ) :
			$i = 0;
			foreach( $args['order'] as $_order ) :
				$order[$i]['sort']   = $_order['dir'];
				$order[$i]['column'] = $columns[$_order['column']];
				$i++;
			endforeach;
		endif;

        $filter = $this->set_filter_args($args['filter']);

        return [
            'start'  => $args['start'],
            'length' => $args['length'],
			'draw'	 => $args['draw'],
            'search' => $search,
            'order'  => $order,
            'filter' => $filter
        ];
    }

    /**
     * adjust brightness RGB color
     * @param  string   $hexCode
     * @param  integer  $steo
     * @return string
     */
    private function adjust_brightness($hex, $steps) {
        // Steps should be between -255 and 255. Negative = darker, positive = lighter
        $steps = max(-255, min(255, $steps));

        // Normalize into a six character long hex string
        $hex = str_replace('#', '', $hex);
        if (strlen($hex) == 3) :
            $hex = str_repeat(substr($hex,0,1), 2).str_repeat(substr($hex,1,1), 2).str_repeat(substr($hex,2,1), 2);
        endif;

        // Split into three parts: R, G and B
        $color_parts = str_split($hex, 2);
        $return = '#';

        foreach ($color_parts as $color) :
            $color   = hexdec($color); // Convert to decimal
            $color   = max(0,min(255,$color + $steps)); // Adjust color
            $return .= str_pad(dechex($color), 2, '0', STR_PAD_LEFT); // Make two char hex code
        endforeach;

        return $return;
    }

    /**
     * Set color based on text
     * @param   string $text
     * @return  string
     */
    private function set_color($text) {

        $code = sejolisa_get_text_color($text);

        if(false === $code) :
            $code = dechex(crc32($text));
            $code = '#'.substr($code, 0, 6);
        endif;

        return $code;
    }

    protected function set_chart_data($all_data,array $date_args, $data_label = NULL) {

        $data_temp = $labels = $datasets = [];
        $type   = $date_args['type'];

        foreach($all_data as $data) :

            $status = (property_exists($data, 'status')) ? $data->status : 'all';
            $date   = $data->$type;
            $total  = $data->total;
            $datatemp[$date][$status] = $total;

        endforeach;

        $start = new \DateTime($date_args['start_date']);
        $end   = new \DateTime($date_args['end_date']);

        if('year' === $type) :
            $format = 'Y';
            $start->modify('first day of this year');
            $end->modify('last day of this year');
            $interval = \DateInterval::createFromDateString('1 year');
        elseif('month' === $type) :
            $format = 'Y-m';
            $start->modify('first day of this year');
            $end->modify('last day of this year');
            $interval = \DateInterval::createFromDateString('1 month');
        else :
            $format = 'Y-m-d';
            $end->modify('+1 day');
            $interval = \DateInterval::createFromDateString('1 day');
        endif;


        $period = new \DatePeriod($start, $interval, $end);

        foreach($period as $per) :
            $labels[] = $per->format($format);
        endforeach;

        if(!is_null($data_label) && is_array($data_label)) :
            foreach($data_label as $status => $label) :
                $color = $this->set_color($status);
                $datasets[$status] = [
                    'label'           => $label,
                    'data'            => array_fill(0, count($labels), 0),
                    'borderColor'     => $color,
                    'backgroundColor' => sejolisa_set_rgba_from_hex($color, '0.3'),
                    'fill'            => true,
                ];
            endforeach;
        else :
            $datasets['all'] = [
                'data' => array_fill(0, count($labels), 0)
            ];
        endif;

        if(isset($datatemp)) :
            foreach($datatemp as $date => $_detail) :
                $position = array_search($date, $labels);
                foreach($_detail as $status => $total) :
                    $datasets[$status]['data'][$position] = $total;
                endforeach;
            endforeach;
        endif;

        return [
            'labels'   => $labels,
            'datasets' => array_values($datasets)
        ];
    }

    /**
     * Set user options
     * @since   1.0.0
     * @return  json
     */
    public function set_for_options() {

    }

    /**
     * Set table data
     * @since   1.0.0
     * @return  json
     */
    public function set_for_table() {

    }
}
