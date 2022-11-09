<?php

defined('BASEPATH') or exit('No direct script access allowed');

$dimensions = $pdf->getPageDimensions();

$info_right_column = '';
$info_left_column  = '';

$info_right_column .= '<span style="font-weight:bold;font-size:27px;">' . _l('peralatan_pdf_heading') . '</span><br />';
$info_right_column .= '<b style="color:#4e4e4e;"># ' . format_peralatan_number($peralatan->id) . '</b>';

if (get_option('show_status_on_pdf_ei') == 1) {
    $info_right_column .= '<br /><span style="color:rgb(' . peralatan_status_color_pdf($peralatan->status) . ');text-transform:uppercase;">' . format_peralatan_status($peralatan->status, '', false) . '</span>';
}

// Add logo
$info_left_column .= pdf_logo_url();
// Write top left logo and right column info/text
pdf_multi_row($info_left_column, $info_right_column, $pdf, ($dimensions['wk'] / 2) - $dimensions['lm']);

$pdf->ln(10);

$organization_info = '<div style="color:#424242;">';
    $organization_info .= format_organization_info();
$organization_info .= '</div>';

// Estimate to
$peralatan_info = '<b>' . _l('peralatan_to') . '</b>';
$peralatan_info .= '<div style="color:#424242;">';
$peralatan_info .= format_peralatan_info($peralatan, 'peralatan');
$peralatan_info .= '</div>';

$peralatan_info .= '<br />' . _l('peralatan_data_date') . ': ' . _d($peralatan->date) . '<br />';

if (!empty($peralatan->open_till)) {
    $peralatan_info .= _l('peralatan_data_expiry_date') . ': ' . _d($peralatan->open_till) . '<br />';
}

if (!empty($peralatan->reference_no)) {
    $peralatan_info .= _l('reference_no') . ': ' . $peralatan->reference_no . '<br />';
}



foreach ($pdf_custom_fields as $field) {
    $value = get_custom_field_value($peralatan->id, $field['id'], 'peralatan');
    if ($value == '') {
        continue;
    }
    $peralatan_info .= $field['name'] . ': ' . $value . '<br />';
}

$left_info  = $swap == '1' ? $peralatan_info : $organization_info;
$right_info = $swap == '1' ? $organization_info : $peralatan_info;

pdf_multi_row($left_info, $right_info, $pdf, ($dimensions['wk'] / 2) - $dimensions['lm']);

// The Table
$pdf->Ln(hooks()->apply_filters('pdf_info_and_table_separator', 6));

// The items table
$items = get_items_table_data($peralatan, 'peralatan', 'pdf');

$tblhtml = $items->table();

$pdf->writeHTML($tblhtml, true, false, false, false, '');

$pdf->Ln(8);
$tbltotal = '';
$tbltotal .= '<table cellpadding="6" style="font-size:' . ($font_size + 4) . 'px">';
$tbltotal .= '
<tr>
    <td align="right" width="85%"><strong>' . _l('peralatan_subtotal') . '</strong></td>
    <td align="right" width="15%">' . app_format_money($peralatan->subtotal, $peralatan->currency_name) . '</td>
</tr>';

if (is_sale_discount_applied($peralatan)) {
    $tbltotal .= '
    <tr>
        <td align="right" width="85%"><strong>' . _l('peralatan_discount');
    if (is_sale_discount($peralatan, 'percent')) {
        $tbltotal .= ' (' . app_format_number($peralatan->discount_percent, true) . '%)';
    }
    $tbltotal .= '</strong>';
    $tbltotal .= '</td>';
    $tbltotal .= '<td align="right" width="15%">-' . app_format_money($peralatan->discount_total, $peralatan->currency_name) . '</td>
    </tr>';
}

foreach ($items->taxes() as $tax) {
    $tbltotal .= '<tr>
    <td align="right" width="85%"><strong>' . $tax['taxname'] . ' (' . app_format_number($tax['taxrate']) . '%)' . '</strong></td>
    <td align="right" width="15%">' . app_format_money($tax['total_tax'], $peralatan->currency_name) . '</td>
</tr>';
}

if ((int)$peralatan->adjustment != 0) {
    $tbltotal .= '<tr>
    <td align="right" width="85%"><strong>' . _l('peralatan_adjustment') . '</strong></td>
    <td align="right" width="15%">' . app_format_money($peralatan->adjustment, $peralatan->currency_name) . '</td>
</tr>';
}

$tbltotal .= '
<tr style="background-color:#f0f0f0;">
    <td align="right" width="85%"><strong>' . _l('peralatan_total') . '</strong></td>
    <td align="right" width="15%">' . app_format_money($peralatan->total, $peralatan->currency_name) . '</td>
</tr>';

$tbltotal .= '</table>';

$pdf->writeHTML($tbltotal, true, false, false, false, '');

if (get_option('peralatan_total_to_words_enabled') == 1) {
    // Set the font bold
    $pdf->SetFont($font_name, 'B', $font_size);
    $pdf->writeHTMLCell('', '', '', '', _l('num_word') . ': ' . $CI->numberword->convert($peralatan->total, $peralatan->currency_name), 0, 1, false, true, 'C', true);
    // Set the font again to normal like the rest of the pdf
    $pdf->SetFont($font_name, '', $font_size);
    $pdf->Ln(4);
}

if (!empty($peralatan->client_note)) {
    $pdf->Ln(4);
    $pdf->SetFont($font_name, 'B', $font_size);
    $pdf->Cell(0, 0, _l('peralatan_note'), 0, 1, 'L', 0, '', 0);
    $pdf->SetFont($font_name, '', $font_size);
    $pdf->Ln(2);
    $pdf->writeHTMLCell('', '', '', '', $peralatan->client_note, 0, 1, false, true, 'L', true);
}

if (!empty($peralatan->terms)) {
    $pdf->Ln(4);
    $pdf->SetFont($font_name, 'B', $font_size);
    $pdf->Cell(0, 0, _l('terms_and_conditions') . ":", 0, 1, 'L', 0, '', 0);
    $pdf->SetFont($font_name, '', $font_size);
    $pdf->Ln(2);
    $pdf->writeHTMLCell('', '', '', '', $peralatan->terms, 0, 1, false, true, 'L', true);
}
