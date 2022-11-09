<?php

defined('BASEPATH') or exit('No direct script access allowed');

/**
 * Injects theme CSS
 * @return null
 */
function peralatan_head_component()
{
    echo '<link rel="stylesheet" type="text/css" id="peralatan-css" href="' . base_url('modules/peralatan/assets/css/peralatan.css') . '">';
    $CI = &get_instance();
    if (($CI->uri->segment(1) == 'admin' && $CI->uri->segment(2) == 'peralatan') ||
        $CI->uri->segment(1) == 'peralatan'
    ) {
    }
}


/**
 * Injects theme CSS
 * @return null
 */
function peralatan_footer_js_component()
{
    echo '<script src="' . base_url('modules/peralatan/assets/js/peralatan.js') . '"></script>';
    $CI = &get_instance();
    if (($CI->uri->segment(1) == 'admin' && $CI->uri->segment(2) == 'peralatan') ||
        ($CI->uri->segment(1) == 'admin' && $CI->uri->segment(2) == 'list_peralatan') ||
        $CI->uri->segment(1) == 'peralatan'
    ) {
    }
}


/**
 * Prepare general peralatan pdf
 * @since  Version 1.0.2
 * @param  object $peralatan peralatan as object with all necessary fields
 * @param  string $tag tag for bulk pdf exporter
 * @return mixed object
 */
function peralatan_pdf($peralatan, $tag = '')
{
    return app_pdf('peralatan',  module_libs_path(PERALATAN_MODULE_NAME) . 'pdf/Peralatan_pdf', $peralatan, $tag);
}


/**
 * Get peralatan short_url
 * @since  Version 2.7.3
 * @param  object $peralatan
 * @return string Url
 */
function get_peralatan_shortlink($peralatan)
{
    $long_url = site_url("peralatan/{$peralatan->id}/{$peralatan->hash}");
    if (!get_option('bitly_access_token')) {
        return $long_url;
    }

    // Check if peralatan has short link, if yes return short link
    if (!empty($peralatan->short_link)) {
        return $peralatan->short_link;
    }

    // Create short link and return the newly created short link
    $short_link = app_generate_short_link([
        'long_url' => $long_url,
        'title'    => format_peralatan_number($peralatan->id),
    ]);

    if ($short_link) {
        $CI = &get_instance();
        $CI->db->where('id', $peralatan->id);
        $CI->db->update(db_prefix() . 'peralatan', [
            'short_link' => $short_link,
        ]);

        return $short_link;
    }

    return $long_url;
}

/**
 * Check if peralatan hash is equal
 * @param  mixed $id   peralatan id
 * @param  string $hash peralatan hash
 * @return void
 */
function check_peralatan_restrictions($id, $hash)
{
    $CI = &get_instance();
    $CI->load->model('peralatan_model');
    if (!$hash || !$id) {
        show_404();
    }
    $peralatan = $CI->peralatan_model->get($id);
    if (!$peralatan || ($peralatan->hash != $hash)) {
        show_404();
    }
}

/**
 * Check if peralatan email template for expiry reminders is enabled
 * @return boolean
 */
function is_peralatan_email_expiry_reminder_enabled()
{
    return total_rows(db_prefix() . 'emailtemplates', ['slug' => 'peralatan-expiry-reminder', 'active' => 1]) > 0;
}

/**
 * Check if there are sources for sending peralatan expiry reminders
 * Will be either email or SMS
 * @return boolean
 */
function is_peralatan_expiry_reminders_enabled()
{
    return is_peralatan_email_expiry_reminder_enabled() || is_sms_trigger_active(SMS_TRIGGER_PROPOSAL_EXP_REMINDER);
}

/**
 * Return peralatan status color class based on twitter bootstrap
 * @param  mixed  $id
 * @param  boolean $replace_default_by_muted
 * @return string
 */
function peralatan_status_color_class($id, $replace_default_by_muted = false)
{
    if ($id == 1) {
        $class = 'default';
    } elseif ($id == 2) {
        $class = 'danger';
    } elseif ($id == 3) {
        $class = 'success';
    } elseif ($id == 4 || $id == 5) {
        // status sent and revised
        $class = 'info';
    } elseif ($id == 6) {
        $class = 'default';
    }
    if ($class == 'default') {
        if ($replace_default_by_muted == true) {
            $class = 'muted';
        }
    }

    return $class;
}
/**
 * Format peralatan status with label or not
 * @param  mixed  $status  peralatan status id
 * @param  string  $classes additional label classes
 * @param  boolean $label   to include the label or return just translated text
 * @return string
 */
function format_peralatan_status($status, $classes = '', $label = true)
{
    $id = $status;
    $label_class = 'default';

    if ($status == 1) {
        $status      = _l('peralatan_status_draft');
        $label_class = 'default';
    } elseif ($status == 2) {
        $status      = _l('peralatan_status_sent');
        $label_class = 'info';
    } elseif ($status == 3) {
        $status      = _l('peralatan_status_open');
        $label_class = 'warning';
    } elseif ($status == 4) {
        $status      = _l('peralatan_status_revised');
        $label_class = 'info';
    } elseif ($status == 5) {
        $status      = _l('peralatan_status_declined');
        $label_class = 'danger';
    } elseif ($status == 6) {
        $status      = _l('peralatan_status_accepted');
        $label_class = 'success';
    }

    if ($label == true) {
        return '<span class="label label-' . $label_class . ' ' . $classes . ' s-status peralatan-status-' . $id . '">' . $status . '</span>';
    }

    return $status;
}

/**
 * Function that format peralatan number based on the prefix option and the peralatan id
 * @param  mixed $id peralatan id
 * @return string
 */
function format_peralatan_number($id)
{
    $format = get_option('peralatan_number_prefix') . str_pad($id, get_option('number_padding_prefixes'), '0', STR_PAD_LEFT);

    return hooks()->apply_filters('peralatan_number_format', $format, $id);
}


/**
 * Function that return peralatan item taxes based on passed item id
 * @param  mixed $itemid
 * @return array
 */
function get_peralatan_item_taxes($itemid)
{
    $CI = &get_instance();
    $CI->db->where('itemid', $itemid);
    $CI->db->where('rel_type', 'peralatan');
    $taxes = $CI->db->get(db_prefix() . 'item_tax')->result_array();
    $i     = 0;
    foreach ($taxes as $tax) {
        $taxes[$i]['taxname'] = $tax['taxname'] . '|' . $tax['taxrate'];
        $i++;
    }

    return $taxes;
}


/**
 * Calculate peralatan percent by status
 * @param  mixed $status          peralatan status
 * @param  mixed $total_peralatan in case the total is calculated in other place
 * @return array
 */
function get_peralatan_percent_by_status($status, $total_peralatan = '')
{
    $has_permission_view                 = has_permission('peralatan', '', 'view');
    $has_permission_view_own             = has_permission('peralatan', '', 'view_own');
    $allow_staff_view_peralatan_assigned = get_option('allow_staff_view_peralatan_assigned');
    $staffId                             = get_staff_user_id();

    $whereUser = '';
    if (!$has_permission_view) {
        if ($has_permission_view_own) {
            $whereUser = '(addedfrom=' . $staffId;
            if ($allow_staff_view_peralatan_assigned == 1) {
                $whereUser .= ' OR assigned=' . $staffId;
            }
            $whereUser .= ')';
        } else {
            $whereUser .= 'assigned=' . $staffId;
        }
    }

    if (!is_numeric($total_peralatan)) {
        $total_peralatan = total_rows(db_prefix() . 'peralatan', $whereUser);
    }

    $data            = [];
    $total_by_status = 0;
    $where           = 'status=' . get_instance()->db->escape_str($status);
    if (!$has_permission_view) {
        $where .= ' AND (' . $whereUser . ')';
    }

    $total_by_status = total_rows(db_prefix() . 'peralatan', $where);
    $percent         = ($total_peralatan > 0 ? number_format(($total_by_status * 100) / $total_peralatan, 2) : 0);

    $data['total_by_status'] = $total_by_status;
    $data['percent']         = $percent;
    $data['total']           = $total_peralatan;

    return $data;
}

/**
 * Function that will search possible peralatan templates in applicaion/views/admin/peralatan/templates
 * Will return any found files and user will be able to add new template
 * @return array
 */
function get_peralatan_templates()
{
    $peralatan_templates = [];
    if (is_dir(VIEWPATH . 'admin/peralatan/templates')) {
        foreach (list_files(VIEWPATH . 'admin/peralatan/templates') as $template) {
            $peralatan_templates[] = $template;
        }
    }

    return $peralatan_templates;
}
/**
 * Check if staff member can view peralatan
 * @param  mixed $id peralatan id
 * @param  mixed $staff_id
 * @return boolean
 */
function user_can_view_peralatan($id, $staff_id = false)
{
    $CI = &get_instance();

    $staff_id = $staff_id ? $staff_id : get_staff_user_id();

    if (has_permission('peralatan', $staff_id, 'view')) {
        return true;
    }

    $CI->db->select('id, addedfrom, assigned');
    $CI->db->from(db_prefix() . 'peralatan');
    $CI->db->where('id', $id);
    $peralatan = $CI->db->get()->row();

    if ((has_permission('peralatan', $staff_id, 'view_own') && $peralatan->addedfrom == $staff_id)
        || ($peralatan->assigned == $staff_id && get_option('allow_staff_view_peralatan_assigned') == 1)
    ) {
        return true;
    }

    return false;
}
function parse_peralatan_content_merge_fields($peralatan)
{
    $id = is_array($peralatan) ? $peralatan['id'] : $peralatan->id;
    $CI = &get_instance();

    $CI->load->library('merge_fields/peralatan_merge_fields');
    $CI->load->library('merge_fields/other_merge_fields');

    $merge_fields = [];
    $merge_fields = array_merge($merge_fields, $CI->peralatan_merge_fields->format($id));
    $merge_fields = array_merge($merge_fields, $CI->other_merge_fields->format());
    foreach ($merge_fields as $key => $val) {
        $content = is_array($peralatan) ? $peralatan['content'] : $peralatan->content;

        if (stripos($content, $key) !== false) {
            if (is_array($peralatan)) {
                $peralatan['content'] = str_ireplace($key, $val, $content);
            } else {
                $peralatan->content = str_ireplace($key, $val, $content);
            }
        } else {
            if (is_array($peralatan)) {
                $peralatan['content'] = str_ireplace($key, '', $content);
            } else {
                $peralatan->content = str_ireplace($key, '', $content);
            }
        }
    }

    return $peralatan;
}

/**
 * Check if staff member have assigned peralatan / added as sale agent
 * @param  mixed $staff_id staff id to check
 * @return boolean
 */
function staff_has_assigned_peralatan($staff_id = '')
{
    $CI       = &get_instance();
    $staff_id = is_numeric($staff_id) ? $staff_id : get_staff_user_id();
    $cache    = $CI->app_object_cache->get('staff-total-assigned-peralatan-' . $staff_id);
    if (is_numeric($cache)) {
        $result = $cache;
    } else {
        $result = total_rows(db_prefix() . 'peralatan', ['assigned' => $staff_id]);
        $CI->app_object_cache->add('staff-total-assigned-peralatan-' . $staff_id, $result);
    }

    return $result > 0 ? true : false;
}

function get_peralatan_sql_where_staff($staff_id)
{
    $has_permission_view_own            = has_permission('peralatan', '', 'view_own');
    $allow_staff_view_invoices_assigned = get_option('allow_staff_view_peralatan_assigned');
    $CI                                 = &get_instance();

    $whereUser = '';
    if ($has_permission_view_own) {
        $whereUser = '((' . db_prefix() . 'peralatan.addedfrom=' . $CI->db->escape_str($staff_id) . ' AND ' . db_prefix() . 'peralatan.addedfrom IN (SELECT staff_id FROM ' . db_prefix() . 'staff_permissions WHERE feature = "peralatan" AND capability="view_own"))';
        if ($allow_staff_view_invoices_assigned == 1) {
            $whereUser .= ' OR assigned=' . $CI->db->escape_str($staff_id);
        }
        $whereUser .= ')';
    } else {
        $whereUser .= 'assigned=' . $CI->db->escape_str($staff_id);
    }

    return $whereUser;
}



if (!function_exists('format_peralatan_info')) {
    /**
     * Format peralatan info format
     * @param  object $peralatan peralatan from database
     * @param  string $for      where this info will be used? Admin area, HTML preview?
     * @return string
     */
    function format_peralatan_info($peralatan, $for = '')
    {
        $format = get_option('company_info_format');
        $countryCode = '';
        $countryName = '';

        if ($country = get_country($peralatan->country)) {
            $countryCode = $country->iso2;
            $countryName = $country->short_name;
        }

        $peralatanTo = '<b>' . $peralatan->peralatan_to . '</b>';
        $phone      = $peralatan->phone;
        $email      = $peralatan->email;

        if ($for == 'admin') {
            $hrefAttrs = '';
            if ($peralatan->rel_type == 'lead') {
                $hrefAttrs = ' href="#" onclick="init_lead(' . $peralatan->clientid . '); return false;" data-toggle="tooltip" data-title="' . _l('lead') . '"';
            } else {
                $hrefAttrs = ' href="' . admin_url('clients/client/' . $peralatan->clientid) . '" data-toggle="tooltip" data-title="' . _l('client') . '"';
            }
            $peralatanTo = '<a' . $hrefAttrs . '>' . $peralatanTo . '</a>';
        }

        if ($for == 'html' || $for == 'admin') {
            $phone = '<a href="tel:' . $peralatan->phone . '">' . $peralatan->phone . '</a>';
            $email = '<a href="mailto:' . $peralatan->email . '">' . $peralatan->email . '</a>';
        }

        $format = _info_format_replace('company_name', $peralatanTo, $format);
        $format = _info_format_replace('address', $peralatan->address . ' ' . $peralatan->city, $format);

        $format = _info_format_replace('city', NULL, $format);
        $format = _info_format_replace('state', $peralatan->state . ' ' . $peralatan->zip, $format);

        $format = _info_format_replace('country_code', $countryCode, $format);
        $format = _info_format_replace('country_name', $countryName, $format);

        $format = _info_format_replace('zip_code', '', $format);
        $format = _info_format_replace('phone', $phone, $format);
        $format = _info_format_replace('email', $email, $format);
        $format = _info_format_replace('vat_number_with_label', NULL, $format);

        $whereCF = [];
        if (is_custom_fields_for_customers_portal()) {
            $whereCF['show_on_client_portal'] = 1;
        }
        $customFieldsProposals = get_custom_fields('peralatan', $whereCF);

        foreach ($customFieldsProposals as $field) {
            $value  = get_custom_field_value($peralatan->id, $field['id'], 'peralatan');
            $format = _info_format_custom_field($field['id'], $field['name'], $value, $format);
        }

        // If no custom fields found replace all custom fields merge fields to empty
        $format = _info_format_custom_fields_check($customFieldsProposals, $format);
        $format = _maybe_remove_first_and_last_br_tag($format);

        // Remove multiple white spaces
        $format = preg_replace('/\s+/', ' ', $format);
        $format = trim($format);

        return hooks()->apply_filters('peralatan_info_text', $format, ['peralatan' => $peralatan, 'for' => $for]);
    }
}


/**
 * Prepares email template preview $data for the view
 * @param  string $template    template class name
 * @param  mixed $customer_id_or_email customer ID to fetch the primary contact email or email
 * @return array
 */
function peralatan_prepare_mail_preview_data($template, $customer_id_or_email, $mailClassParams = [])
{
    $CI = &get_instance();

    if (is_numeric($customer_id_or_email)) {
        $contact = $CI->clients_model->get_contact(get_primary_contact_user_id($customer_id_or_email));
        $email   = $contact ? $contact->email : '';
    } else {
        $email = $customer_id_or_email;
    }

    $CI->load->model('emails_model');

    $data['template'] = $CI->peralatan_mail_template->prepare($email, $template);
    $slug             = $CI->peralatan_mail_template->get_default_property_value('slug', $template, $mailClassParams);

    $data['template_name'] = $slug;

    $template_result = $CI->emails_model->get(['slug' => $slug, 'language' => 'english'], 'row');

    $data['template_system_name'] = $template_result->name;
    $data['template_id']          = $template_result->emailtemplateid;

    $data['template_disabled'] = $template_result->active == 0;

    return $data;
}


function peralatan_get_mail_template_path($class, &$params)
{
    //log_activity('params get_mail_template_path 1 : ' .time() .' ' . json_encode($params));
    $CI  = &get_instance();

    $dir = module_libs_path(PERALATAN_MODULE_NAME, 'mails/');

    // Check if second parameter is module and is activated so we can get the class from the module path
    // Also check if the first value is not equal to '/' e.q. when import is performed we set
    // for some values which are blank to "/"
    if (isset($params[0]) && is_string($params[0]) && $params[0] !== '/' && is_dir(module_dir_path($params[0]))) {
        $module = $CI->app_modules->get($params[0]);

        if ($module['activated'] === 1) {
            $dir = module_libs_path($params[0]) . 'mails/';
        }

        unset($params[0]);
        $params = array_values($params);
        //log_activity('params get_mail_template_path 2 : ' .time() .' ' . json_encode($params));
        //log_activity('params get_mail_template_path 3 : ' .time() .' ' . json_encode($dir));
    }

    return $dir . ucfirst($class) . '.php';
}


/**
 * Return RGBa peralatan status color for PDF documents
 * @param  mixed $status_id current peralatan status
 * @return string
 */
function peralatan_status_color_pdf($status_id)
{
    if ($status_id == 1) {
        $statusColor = '119, 119, 119';
    } elseif ($status_id == 2) {
        // Sent
        $statusColor = '3, 169, 244';
    } elseif ($status_id == 3) {
        //Declines
        $statusColor = '252, 45, 66';
    } elseif ($status_id == 4) {
        //Accepted
        $statusColor = '0, 191, 54';
    } else {
        // Expired
        $statusColor = '255, 111, 0';
    }

    return hooks()->apply_filters('peralatan_status_pdf_color', $statusColor, $status_id);
}
