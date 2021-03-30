<?php
    sejoli_header();
?>
<h2 class="ui header"><?php _e('Data Pendapatan Anda', 'sejoli'); ?></h2>

<button class="ui primary button show-filter-form"><i class="filter icon"></i> <?php _e( 'Filter Data', 'sejoli' ); ?></button>
<button class="ui button export-csv"><i class="file alternate icon"></i> <?php _e( 'Export to CSV', 'sejoli' ); ?></button>
<table id="jv-orders" class="ui striped single line table" style="width:100%;word-break: break-word;white-space: normal;">
    <thead>
        <tr>
            <th><?php _e('Detil',       'sejoli'); ?></th>
            <th><?php _e('Pembeli',     'sejoli'); ?></th>
            <th><?php _e('Total',       'sejoli'); ?></th>
            <th><?php _e('Pendapatan',  'sejoli'); ?></th>
            <th><?php _e('Status',      'sejoli'); ?></th>
        </tr>
    </thead>
    <tbody>
        <tr>
            <td colspan="4">Tidak ada data yang bisa ditampilkan</td>
        </tr>
    </tbody>
    <tfoot>
        <tr>
            <th><?php _e('Detil',       'sejoli'); ?></th>
            <th><?php _e('Pembeli',     'sejoli'); ?></th>
            <th><?php _e('Total',       'sejoli'); ?></th>
            <th><?php _e('Pendapatan',  'sejoli'); ?></th>
            <th><?php _e('Status',      'sejoli'); ?></th>
        </tr>
    </tfoot>
</table>
<?php
    sejoli_footer();
