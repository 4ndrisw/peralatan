<?php

defined('BASEPATH') or exit('No direct script access allowed');

$baseCurrency = get_base_currency();

$aColumns = [
    db_prefix() . 'peralatan.id',
    'subject',
    'peralatan_to',
    'nomor_seri',
    'nomor_unit',
    'open_till',
    'datecreated',
    'status',
];

$sIndexColumn = 'id';
$sTable       = db_prefix() . 'peralatan';

$where  = [];
$filter = [];

if ($this->ci->input->post('leads_related')) {
    array_push($filter, 'OR rel_type="lead"');
}
if ($this->ci->input->post('customers_related')) {
    array_push($filter, 'OR rel_type="customer"');
}
if ($this->ci->input->post('expired')) {
    array_push($filter, 'OR open_till IS NOT NULL AND open_till <"' . date('Y-m-d') . '" AND status NOT IN(2,3)');
}

$statuses  = $this->ci->peralatan_model->get_statuses();
$statusIds = [];

foreach ($statuses as $status) {
    if ($this->ci->input->post('peralatan_' . $status)) {
        array_push($statusIds, $status);
    }
}
if (count($statusIds) > 0) {
    array_push($filter, 'AND status IN (' . implode(', ', $statusIds) . ')');
}

$agents    = $this->ci->peralatan_model->get_sale_agents();
$agentsIds = [];
foreach ($agents as $agent) {
    if ($this->ci->input->post('sale_agent_' . $agent['sale_agent'])) {
        array_push($agentsIds, $agent['sale_agent']);
    }
}
if (count($agentsIds) > 0) {
    array_push($filter, 'AND assigned IN (' . implode(', ', $agentsIds) . ')');
}

$years      = $this->ci->peralatan_model->get_peralatan_years();
$yearsArray = [];
foreach ($years as $year) {
    if ($this->ci->input->post('year_' . $year['year'])) {
        array_push($yearsArray, $year['year']);
    }
}
if (count($yearsArray) > 0) {
    array_push($filter, 'AND YEAR(date) IN (' . implode(', ', $yearsArray) . ')');
}

if (count($filter) > 0) {
    array_push($where, 'AND (' . prepare_dt_filter($filter) . ')');
}

if (!has_permission('peralatan', '', 'view')) {
    array_push($where, 'AND ' . get_peralatan_sql_where_staff(get_staff_user_id()));
}

$join          = [];

$result = data_tables_init($aColumns, $sIndexColumn, $sTable, $join, $where, [
    'clientid',
    'hash',
]);

$output  = $result['output'];
$rResult = $result['rResult'];

foreach ($rResult as $aRow) {
    $row = [];

    //$numberOutput = '<a href="' . admin_url('peralatan/list_peralatan/' . $aRow[db_prefix() . 'peralatan.id']) . '" onclick="init_peralatan(' . $aRow[db_prefix() . 'peralatan.id'] . '); return false;">' . format_peralatan_number($aRow[db_prefix() . 'peralatan.id']) . '</a>';
    //$numberOutput = '<a href="' . admin_url('peralatan#' . $aRow[db_prefix() . 'peralatan.id']) . '" target="_blank">' . format_peralatan_number($aRow[db_prefix() . 'peralatan.id']) . ' AA</a>';
    //$numberOutput = '<a href="' . admin_url('peralatan/list_peralatan/' . $aRow[db_prefix() . 'peralatan.id']. '#' . $aRow[db_prefix() . 'peralatan.id']) . '" target="_blank">' . format_peralatan_number($aRow[db_prefix() . 'peralatan.id']) . '</a>';
    $numberOutput = '<a href="' . admin_url('peralatan/list_peralatan/' . $aRow[db_prefix() . 'peralatan.id']. '#' . $aRow[db_prefix() . 'peralatan.id']) . '">' . format_peralatan_number($aRow[db_prefix() . 'peralatan.id']) . '</a>';

    $numberOutput .= '<div class="row-options">';

    $numberOutput .= '<a href="' . site_url('peralatan/show/' . $aRow[db_prefix() . 'peralatan.id'] . '/' . $aRow['hash']) . '" target="_blank">' . _l('view') . '</a>';
    if (has_permission('peralatan', '', 'edit')) {
        $numberOutput .= ' | <a href="' . admin_url('peralatan/edit_peralatan/' . $aRow[db_prefix() . 'peralatan.id']) . '">' . _l('edit') . '</a>';
    }
    $numberOutput .= '</div>';

    $row[] = $numberOutput;

    $row[] = '<a href="' . admin_url('peralatan/list_peralatan/' . $aRow[db_prefix() . 'peralatan.id'] .'#/'. $aRow[db_prefix() . 'peralatan.id']) . '" onclick="init_peralatan(' . $aRow[db_prefix() . 'peralatan.id'] . '); return false;">' . $aRow['subject'] . ' bb</a>';
    $toOutput = $toOutput = '<a href="' . admin_url('clients/client/' . $aRow['clientid']) . '" target="_blank" data-toggle="tooltip" data-title="' . _l('client') . '">' . $aRow['peralatan_to'] . '</a>';

    $row[] = $toOutput;

    $row[] = $aRow['nomor_seri'];

    $row[] = $aRow['nomor_unit'];

    $row[] = _d($aRow['open_till']);


    $row[] = _d($aRow['datecreated']);
            $statuses = $this->ci->peralatan_model->get_statuses();
            

    $dropdown =        '<div class="btn-group btn-group-status">';
    $dropdown .=          format_peralatan_dropdown($aRow['status']);
    $dropdown .=          '<div class="dropdown-menu status">';
                            foreach ($statuses as $peralatanChangeStatus) {
                                if ($aRow['status'] != $peralatanChangeStatus) {
                                    $dropdown .= 
                                    '<li class="'. strtolower(format_peralatan_dropdown($peralatanChangeStatus,'',false)) .'">
                                        <a href="#" onclick="peralatan_mark_action_status(' . $peralatanChangeStatus . ',' . $aRow[db_prefix() . 'peralatan.id'] . '); return false;">
                                         ' . format_peralatan_dropdown($peralatanChangeStatus,'',false) . '
                                        </a>
                                    </li>';
                                }
                            }

    $dropdown .=          '</div>';
    $dropdown .=        '</div>';
    $row[] = $dropdown;

    // Custom fields add values
    foreach ($customFieldsColumns as $customFieldColumn) {
        $row[] = (strpos($customFieldColumn, 'date_picker_') !== false ? _d($aRow[$customFieldColumn]) : $aRow[$customFieldColumn]);
    }

    $row['DT_RowClass'] = 'has-row-options';

    $output['aaData'][] = $row;
}
