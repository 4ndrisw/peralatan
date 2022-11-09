<?php

defined('BASEPATH') or exit('No direct script access allowed');

$baseCurrency = get_base_currency();

$aColumns = [
    '' . db_prefix() . 'peralatan.id as id',
    'subject',
    'total',
    'date',
    'open_till',
    '(SELECT GROUP_CONCAT(name SEPARATOR ",") FROM ' . db_prefix() . 'taggables JOIN ' . db_prefix() . 'tags ON ' . db_prefix() . 'taggables.tag_id = ' . db_prefix() . 'tags.id WHERE clientid = ' . db_prefix() . 'peralatan.id and rel_type="peralatan" ORDER by tag_order ASC) as tags',
    'datecreated',
    'status',
    ];

$sIndexColumn = 'id';
$sTable       = db_prefix() . 'peralatan';
$join         = [];

$custom_fields = get_table_custom_fields('peralatan');

foreach ($custom_fields as $key => $field) {
    $selectAs = (is_cf_date($field) ? 'date_picker_cvalue_' . $key : 'cvalue_' . $key);

    array_push($customFieldsColumns, $selectAs);
    array_push($aColumns, 'ctable_' . $key . '.value as ' . $selectAs);
    array_push($join, 'LEFT JOIN ' . db_prefix() . 'customfieldsvalues as ctable_' . $key . ' ON ' . db_prefix() . 'peralatan.id = ctable_' . $key . '.relid AND ctable_' . $key . '.fieldto="' . $field['fieldto'] . '" AND ctable_' . $key . '.fieldid=' . $field['id']);
}

$where = 'AND clientid = ' . $clientid . ' AND rel_type = "' . $rel_type . '"';

if ($rel_type == 'customer') {
    $this->ci->db->where('userid', $clientid);
    $customer = $this->ci->db->get(db_prefix() . 'clients')->row();
    if ($customer) {
        if (!is_null($customer->leadid)) {
            $where .= ' OR rel_type="lead" AND clientid=' . $customer->leadid;
        }
    }
}

$where = [$where];

if (!has_permission('peralatan', '', 'view')) {
    array_push($where, 'AND ' . get_peralatan_sql_where_staff(get_staff_user_id()));
}

$aColumns = hooks()->apply_filters('peralatan_relation_table_sql_columns', $aColumns);

// Fix for big queries. Some hosting have max_join_limit
if (count($custom_fields) > 4) {
    @$this->ci->db->query('SET SQL_BIG_SELECTS=1');
}

$result = data_tables_init($aColumns, $sIndexColumn, $sTable, $join, $where, [
    'currency',
    'invoice_id',
    'hash',
    ]);

$output  = $result['output'];
$rResult = $result['rResult'];

foreach ($rResult as $aRow) {
    $row = [];

    $numberOutput = '<a href="' . admin_url('peralatan/list_peralatan/' . $aRow['id']) . '">' . format_peralatan_number($aRow['id']) . '</a>';

    $numberOutput .= '<div class="row-options">';

    $numberOutput .= '<a href="' . site_url('peralatan/' . $aRow['id'] . '/' . $aRow['hash']) . '" target="_blank">' . _l('view') . '</a>';
    if (has_permission('peralatan', '', 'edit')) {
        $numberOutput .= ' | <a href="' . admin_url('peralatan/peralatan/' . $aRow['id']) . '">' . _l('edit') . '</a>';
    }
    $numberOutput .= '</div>';

    $row[] = $numberOutput;

    $row[] = '<a href="' . admin_url('peralatan/list_peralatan/' . $aRow['id']) . '">' . $aRow['subject'] . '</a>';

    $amount = app_format_money($aRow['total'], ($aRow['currency'] != 0 ? get_currency($aRow['currency']) : $baseCurrency));

    if ($aRow['invoice_id']) {
        $amount .= '<br /> <span class="hide"> - </span><span class="text-success">' . _l('peralatan_invoiced') . '</span>';
    }

    $row[] = $amount;


    $row[] = _d($aRow['date']);

    $row[] = _d($aRow['open_till']);

    $row[] = render_tags($aRow['tags']);

    $row[] = _d($aRow['datecreated']);

    $row[] = format_peralatan_status($aRow['status']);

    // Custom fields add values
    foreach ($customFieldsColumns as $customFieldColumn) {
        $row[] = (strpos($customFieldColumn, 'date_picker_') !== false ? _d($aRow[$customFieldColumn]) : $aRow[$customFieldColumn]);
    }

    $output['aaData'][] = $row;
}
