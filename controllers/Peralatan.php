<?php
defined('BASEPATH') or exit('No direct script access allowed');

use modules\peralatan\services\peralatan\PeralatanPipeline;


class Peralatan extends AdminController
{

    public function __construct()
    {
        parent::__construct();
        $this->load->model('peralatan_model');
        $this->load->model('clients_model');
        $this->load->model('jenis_pesawat_model');
        $this->load->model('currencies_model');
        include_once(module_libs_path('peralatan') . 'mails/Peralatan_mail_template.php');
        //$this->load->library('module_name/library_name');
        $this->load->library('peralatan_mail_template');
        //include_once(module_libs_path(PERALATAN_MODULE_NAME) . 'mails/Peralatan_send_to_customer.php');
        //$this->load->library('module_name/library_name');
        //$this->load->library('peralatan_send_to_customer');


    }

    public function index($peralatan_id = '')
    {
        $this->list_peralatan($peralatan_id);
    }

    public function list_peralatan($peralatan_id = '')
    {
        //close_setup_menu();

        if (!has_permission('peralatan', '', 'view') && !has_permission('peralatan', '', 'view_own') && get_option('allow_staff_view_peralatan_assigned') == 0) {
            access_denied('peralatan');
        }

        $data['peralatan_statuses'] = $this->peralatan_model->get_statuses();
        $isPipeline = $this->session->userdata('peralatan_pipeline') == 'true';

        if ($isPipeline && !$this->input->get('status')) {
            $data['title']           = _l('peralatan_pipeline');
            $data['bodyclass']       = 'peralatan-pipeline';
            $data['switch_pipeline'] = false;
            // Direct access
            if (is_numeric($peralatan_id)) {
                $data['peralatan_id'] = $peralatan_id;
            } else {
                $data['peralatan_id'] = $this->session->flashdata('peralatan_id');
            }

            $this->load->view('admin/peralatan/pipeline/manage', $data);
        } else {

            // Pipeline was initiated but user click from home page and need to show table only to filter
            if ($this->input->get('status') && $isPipeline) {
                $this->pipeline(0, true);
            }
            $data['peralatan_id']           = $peralatan_id;
            $data['switch_pipeline']       = true;
            $data['title']                 = _l('peralatan');
            $data['statuses']              = $this->peralatan_model->get_statuses();
            //$data['peralatan_sale_agents'] = $this->peralatan_model->get_sale_agents();
            $data['years']                 = $this->peralatan_model->get_peralatan_open_till();

            $this->load->view('admin/peralatan/manage_table', $data);
        }
    }

    public function table()
    {
        if (
            !has_permission('peralatan', '', 'view')
            && !has_permission('peralatan', '', 'view_own')
            && get_option('allow_staff_view_peralatan_assigned') == 0
        ) {
            ajax_access_denied();
        }
        $this->app->get_table_data(module_views_path('peralatan', 'tables/peralatan'));
    }

    public function small_table()
    {
        if (
            !has_permission('peralatan', '', 'view')
            && !has_permission('peralatan', '', 'view_own')
            && get_option('allow_staff_view_peralatan_assigned') == 0
        ) {
            ajax_access_denied();
        }
        $this->app->get_table_data(module_views_path('peralatan', 'tables/peralatan_small_table'));
    }

    public function peralatan_relations($clientid, $rel_type)
    {
        $this->app->get_table_data(module_views_path('peralatan', 'tables/peralatan_relations', [
            'clientid'   => $clientid,
            'rel_type' => $rel_type,
        ]));
    }

    public function delete_attachment($id)
    {
        $file = $this->misc_model->get_file($id);
        if ($file->staffid == get_staff_user_id() || is_admin()) {
            echo $this->peralatan_model->delete_attachment($id);
        } else {
            ajax_access_denied();
        }
    }

    public function clear_signature($id)
    {
        if (has_permission('peralatan', '', 'delete')) {
            $this->peralatan_model->clear_signature($id);
        }

        redirect(admin_url('peralatan/list_peralatan/' . $id . '#' . $id . '#' . $id));
    }

    public function sync_data()
    {
        if (has_permission('peralatan', '', 'create') || has_permission('peralatan', '', 'edit')) {
            $has_permission_view = has_permission('peralatan', '', 'view');

            $this->db->where('clientid', $this->input->post('clientid'));
            $this->db->where('rel_type', $this->input->post('rel_type'));

            if (!$has_permission_view) {
                $this->db->where('addedfrom', get_staff_user_id());
            }

            $address = trim($this->input->post('address'));
            $address = nl2br($address);
            $this->db->update(db_prefix() . 'peralatan', [
                'phone'   => $this->input->post('phone'),
                'zip'     => $this->input->post('zip'),
                'country' => $this->input->post('country'),
                'state'   => $this->input->post('state'),
                'address' => $address,
                'city'    => $this->input->post('city'),
            ]);

            if ($this->db->affected_rows() > 0) {
                echo json_encode([
                    'message' => _l('all_data_synced_successfully'),
                ]);
            } else {
                echo json_encode([
                    'message' => _l('sync_peralatan_up_to_date'),
                ]);
            }
        }
    }
    /*
    public function peralatan($id = '')
    {
        if ($this->input->post()) {
            $peralatan_data = $this->input->post();
            if ($id == '') {
                if (!has_permission('peralatan', '', 'create')) {
                    access_denied('peralatan');
                }
                $id = $this->peralatan_model->add($peralatan_data);
                if ($id) {
                    set_alert('success', _l('added_successfully', _l('peralatan')));
                    if ($this->set_peralatan_pipeline_autoload($id)) {
                        redirect(admin_url('peralatan'));
                    } else {
                        redirect(admin_url('peralatan/list_peralatan/' . $id .'#' . $id));
                    }
                }
            } else {
                if (!has_permission('peralatan', '', 'edit')) {
                    access_denied('peralatan');
                }
                $success = $this->peralatan_model->update($peralatan_data, $id);
                if ($success) {
                    set_alert('success', _l('updated_successfully', _l('peralatan')));
                }
                if ($this->set_peralatan_pipeline_autoload($id)) {
                    redirect(admin_url('peralatan'));
                } else {
                    redirect(admin_url('peralatan/list_peralatan/' . $id .'#' . $id));
                }
            }
        }
        if ($id == '') {
            $title = _l('add_new', _l('peralatan_lowercase'));
        } else {
            $data['peralatan'] = $this->peralatan_model->get($id);

            if (!$data['peralatan'] || !user_can_view_peralatan($id)) {
                blank_page(_l('peralatan_not_found'));
            }

            $data['peralatan']    = $data['peralatan'];
            $data['is_peralatan'] = true;
            $title               = _l('edit', _l('peralatan_lowercase'));
        }

        $this->load->model('taxes_model');
        $data['taxes'] = $this->taxes_model->get();
        $this->load->model('jenis_pesawat_model');
        $data['ajaxItems'] = false;
        if (total_rows(db_prefix() . 'items') <= ajax_on_total_items()) {
            $data['items'] = $this->jenis_pesawat_model->get_grouped();
        } else {
            $data['items']     = [];
            $data['ajaxItems'] = true;
        }
        $data['kelompok_alat'] = $this->jenis_pesawat_model->get_groups();

        $data['statuses']      = $this->peralatan_model->get_statuses();
        $data['staff']         = $this->staff_model->get('', ['active' => 1]);
        $data['currencies']    = $this->currencies_model->get();
        $data['base_currency'] = $this->currencies_model->get_base_currency();

        $data['title'] = $title;
        $this->load->view('admin/peralatan/peralatan', $data);
    }
    */


    public function add_peralatan($id = '')
    {

        $staff_id = get_staff_user_id();
        $current_user = get_client_type($staff_id);
        $company_id = $current_user->client_id;
        $company_name = get_company_name($current_user->client_id);
        
        $client = get_client($company_id);
        if(!is_admin() && (is_null($client->institution_id) || is_null($client->inspector_id) || is_null($client->inspector_staff_id))){
            access_denied('peralatan');
        }

        if ($this->input->post()) {
            $peralatan_data = $this->input->post();

            if($current_user->client_type == 'Company' || $current_user->client_type == 'company'){
                $peralatan_data['clientid'] = $company_id;
                $peralatan_data['peralatan_to'] = $company_name;
            }

            if ($id == '') {
                if (!has_permission('peralatan', '', 'create')) {
                    access_denied('peralatan');
                }
                
                $jenis_pesawat = $this->jenis_pesawat_model->get($peralatan_data['jenis_pesawat_id']);
                //$peralatan_data['jenis_pesawat_id'] = $jenis_pesawat->id;
                $peralatan_data['jenis_pesawat'] = $jenis_pesawat->description;
                $peralatan_data['kelompok_alat_id'] = $jenis_pesawat->group_id;
                
                $id = $this->peralatan_model->add($peralatan_data);
                
                if ($id) {
                    set_alert('success', _l('added_successfully', _l('peralatan')));
                    if ($this->set_peralatan_pipeline_autoload($id)) {
                        redirect(admin_url('peralatan'));
                    } else {
                        redirect(admin_url('peralatan/list_peralatan/' . $id . '#' . $id));
                    }
                }
            }
        }

        $title = _l('add_new', _l('peralatan_lowercase'));
        //$this->load->model('taxes_model');
        //$data['taxes'] = $this->taxes_model->get();
        $this->load->model('jenis_pesawat_model');
        $data['jenis_pesawat'] = $this->peralatan_model->get_jenis_pesawat();

        $data['kelompok_alat'] = $this->jenis_pesawat_model->get_groups();

        $data['statuses']      = $this->peralatan_model->get_statuses();
        $data['staff']         = $this->staff_model->get('', ['active' => 1]);

        $data['client_type']         = isset($current_user->client_type) ? $current_user->client_type : '';

        $data['title'] = $title;
        $this->load->view('admin/peralatan/add_peralatan', $data);
    }

    public function edit_peralatan($id)
    {
        if ($this->input->post()) {

            $peralatan_data = $this->input->post();
            if (!has_permission('peralatan', '', 'edit')) {
                access_denied('peralatan');
            }
            $jenis_pesawat = $this->jenis_pesawat_model->get($peralatan_data['jenis_pesawat_id']);
            $peralatan_data['jenis_pesawat_id'] = $jenis_pesawat->id;
            $peralatan_data['jenis_pesawat'] = $jenis_pesawat->description;
            $peralatan_data['kelompok_alat_id'] = $jenis_pesawat->group_id;

            $success = $this->peralatan_model->update($peralatan_data, $id);
            if ($success) {
                set_alert('success', _l('updated_successfully', _l('peralatan')));
            }
            if ($this->set_peralatan_pipeline_autoload($id)) {
                redirect(admin_url('peralatan'));
            } else {
                redirect(admin_url('peralatan/list_peralatan/' . $id . '#' . $id));
            }
        }

        $data['peralatan'] = $this->peralatan_model->get($id);

        if (!$data['peralatan'] || !user_can_view_peralatan($id)) {
            blank_page(_l('peralatan_not_found'));
        }

        $data['peralatan']    = $data['peralatan'];
        $data['is_peralatan'] = true;
        $title               = _l('edit', _l('peralatan_lowercase'));

        $data['jenis_pesawat'] = $this->peralatan_model->get_jenis_pesawat();

        //$this->load->model('taxes_model');
        //$data['taxes'] = $this->taxes_model->get();
        //$this->load->model('jenis_pesawat_model');
        //$data['ajaxItems'] = false;
        //if (total_rows(db_prefix() . 'items') <= ajax_on_total_items()) {
        //    $data['items'] = $this->jenis_pesawat_model->get_grouped();
        //} else {
        //    $data['items']     = [];
        //    $data['ajaxItems'] = true;
        //}


        //$data['kelompok_alat'] = $this->jenis_pesawat_model->get_groups();
        $data['statuses']      = $this->peralatan_model->get_statuses();
        $data['staff']         = $this->staff_model->get('', ['active' => 1]);
        //$data['currencies']    = $this->currencies_model->get();
        //$data['base_currency'] = $this->currencies_model->get_base_currency();

        $data['title'] = $title;
        $this->load->view('admin/peralatan/edit_peralatan', $data);
    }

    public function get_template()
    {
        $name = $this->input->get('name');
        echo $this->load->view('admin/peralatan/templates/' . $name, [], true);
    }

    public function send_expiry_reminder($id)
    {
        $canView = user_can_view_peralatan($id);
        if (!$canView) {
            access_denied('peralatan');
        } else {
            if (!has_permission('peralatan', '', 'view') && !has_permission('peralatan', '', 'view_own') && $canView == false) {
                access_denied('peralatan');
            }
        }

        $success = $this->peralatan_model->send_expiry_reminder($id);
        if ($success) {
            set_alert('success', _l('sent_expiry_reminder_success'));
        } else {
            set_alert('danger', _l('sent_expiry_reminder_fail'));
        }
        if ($this->set_peralatan_pipeline_autoload($id)) {
            redirect($_SERVER['HTTP_REFERER']);
        } else {
            redirect(admin_url('peralatan/list_peralatan/' . $id . '#' . $id));
        }
    }

    public function clear_acceptance_info($id)
    {
        if (is_admin()) {
            $this->db->where('id', $id);
            $this->db->update(db_prefix() . 'peralatan', get_acceptance_info_array(true));
        }

        redirect(admin_url('peralatan/list_peralatan/' . $id . '#' . $id));
    }

    public function pdf($id)
    {
        if (!$id) {
            redirect(admin_url('peralatan'));
        }

        $canView = user_can_view_peralatan($id);
        if (!$canView) {
            access_denied('peralatan');
        } else {
            if (!has_permission('peralatan', '', 'view') && !has_permission('peralatan', '', 'view_own') && $canView == false) {
                access_denied('peralatan');
            }
        }

        $peralatan = $this->peralatan_model->get($id);

        try {
            $pdf = peralatan_pdf($peralatan);
        } catch (Exception $e) {
            $message = $e->getMessage();
            echo $message;
            if (strpos($message, 'Unable to get the size of the image') !== false) {
                show_pdf_unable_to_get_image_size_error();
            }
            die;
        }

        $type = 'D';

        if ($this->input->get('output_type')) {
            $type = $this->input->get('output_type');
        }

        if ($this->input->get('print')) {
            $type = 'I';
        }

        $peralatan_number = format_peralatan_number($id);
        $pdf->Output($peralatan_number . '.pdf', $type);
    }

    public function get_peralatan_data_ajax($id, $to_return = false)
    {
        if (!has_permission('peralatan', '', 'view') && !has_permission('peralatan', '', 'view_own') && get_option('allow_staff_view_peralatan_assigned') == 0) {
            echo _l('access_denied');
            die;
        }

        $peralatan = $this->peralatan_model->get($id, [], true);

        if (!$peralatan || !user_can_view_peralatan($id)) {
            echo _l('peralatan_not_found');
            die;
        }


        //$this->peralatan_mail_template->set_clientid($peralatan->id);
        include_once(module_libs_path(PERALATAN_MODULE_NAME) . 'mails/Peralatan_send_to_customer.php');

        //$data = peralatan_prepare_mail_preview_data('peralatan_send_to_customer', $peralatan->email);

        $merge_fields = [];

        $merge_fields[] = [
            [
                'name' => 'Items Table',
                'key'  => '{peralatan_items}',
            ],
        ];

        $merge_fields = array_merge($merge_fields, $this->app_merge_fields->get_flat('peralatan', 'other', '{email_signature}'));

        $data['activity']          = $this->peralatan_model->get_peralatan_activity($id);
        $data['peralatan_statuses']     = $this->peralatan_model->get_statuses();
        $data['members']               = $this->staff_model->get('', ['active' => 1]);
        $data['peralatan_merge_fields'] = $merge_fields;
        $data['peralatan']              = $peralatan;
        $data['company']              = $this->clients_model->get($peralatan->clientid);

        $data['totalNotes']            = total_rows(db_prefix() . 'notes', ['rel_id' => $id, 'rel_type' => 'peralatan']);

        if ($to_return == false) {
            $this->load->view('admin/peralatan/peralatan_preview_template', $data);
        } else {
            return $this->load->view('admin/peralatan/peralatan_preview_template', $data, true);
        }
    }

    public function get_peralatan_total()
    {
        if ($this->input->post()) {
            $data['totals'] = $this->peralatan_model->get_peralatan_total($this->input->post());

            $this->load->model('currencies_model');

            if (!$this->input->post('customer_id')) {
                $multiple_currencies = call_user_func('is_using_multiple_currencies', db_prefix() . 'peralatan');
            } else {
                $multiple_currencies = call_user_func('is_client_using_multiple_currencies', $this->input->post('customer_id'), db_prefix() . 'peralatan');
            }

            if ($multiple_currencies) {
                $data['currencies'] = $this->currencies_model->get();
            }

            $data['peralatan_years'] = $this->peralatan_model->get_peralatan_years();

            if (
                count($data['peralatan_years']) >= 1
                && !\app\services\utilities\Arr::inMultidimensional($data['peralatan_years'], 'year', date('Y'))
            ) {
                array_unshift($data['peralatan_years'], ['year' => date('Y')]);
            }

            $data['_currency'] = $data['totals']['currencyid'];
            unset($data['totals']['currencyid']);
            $this->load->view('admin/peralatan/peralatan_total_template', $data);
        }
    }

    public function add_note($clientid)
    {
        if ($this->input->post() && user_can_view_peralatan($clientid)) {
            $this->misc_model->add_note($this->input->post(), 'peralatan', $clientid);
            echo $clientid;
        }
    }

    public function get_notes($id)
    {
        if (user_can_view_peralatan($id)) {
            $data['notes'] = $this->misc_model->get_notes($id, 'peralatan');
            $this->load->view('admin/includes/sales_notes_template', $data);
        }
    }

    public function convert_to_peralatan($id)
    {
        if (!has_permission('peralatan', '', 'create')) {
            access_denied('peralatan');
        }
        if ($this->input->post()) {
            $this->load->model('peralatan_model');
            $peralatan_id = $this->peralatan_model->add($this->input->post());
            if ($peralatan_id) {
                set_alert('success', _l('peralatan_converted_to_peralatan_success'));
                $this->db->where('id', $id);
                $this->db->update(db_prefix() . 'peralatan', [
                    'peralatan_id' => $peralatan_id,
                    'status'      => 3,
                ]);
                log_activity('Peralatan Converted to Estimate [EstimateID: ' . $peralatan_id . ', PeralatanID: ' . $id . ']');

                hooks()->do_action('peralatan_converted_to_peralatan', ['peralatan_id' => $id, 'peralatan_id' => $peralatan_id]);

                redirect(admin_url('peralatan/peralatan/' . $peralatan_id));
            } else {
                set_alert('danger', _l('peralatan_converted_to_peralatan_fail'));
            }
            if ($this->set_peralatan_pipeline_autoload($id)) {
                redirect(admin_url('peralatan'));
            } else {
                redirect(admin_url('peralatan/list_peralatan/' . $id . '#' . $id));
            }
        }
    }

    public function convert_to_invoice($id)
    {
        if (!has_permission('invoices', '', 'create')) {
            access_denied('invoices');
        }
        if ($this->input->post()) {
            $this->load->model('invoices_model');
            $invoice_id = $this->invoices_model->add($this->input->post());
            if ($invoice_id) {
                set_alert('success', _l('peralatan_converted_to_invoice_success'));
                $this->db->where('id', $id);
                $this->db->update(db_prefix() . 'peralatan', [
                    'invoice_id' => $invoice_id,
                    'status'     => 3,
                ]);
                log_activity('Peralatan Converted to Invoice [InvoiceID: ' . $invoice_id . ', PeralatanID: ' . $id . ']');
                hooks()->do_action('peralatan_converted_to_invoice', ['peralatan_id' => $id, 'invoice_id' => $invoice_id]);
                redirect(admin_url('invoices/invoice/' . $invoice_id));
            } else {
                set_alert('danger', _l('peralatan_converted_to_invoice_fail'));
            }
            if ($this->set_peralatan_pipeline_autoload($id)) {
                redirect(admin_url('peralatan'));
            } else {
                redirect(admin_url('peralatan/list_peralatan/' . $id . '#' . $id));
            }
        }
    }

    public function get_invoice_convert_data($id)
    {
        $this->load->model('payment_modes_model');
        $data['payment_modes'] = $this->payment_modes_model->get('', [
            'expenses_only !=' => 1,
        ]);
        $this->load->model('taxes_model');
        $data['taxes']         = $this->taxes_model->get();
        $data['currencies']    = $this->currencies_model->get();
        $data['base_currency'] = $this->currencies_model->get_base_currency();
        $this->load->model('jenis_pesawat_model');
        $data['ajaxItems'] = false;
        if (total_rows(db_prefix() . 'items') <= ajax_on_total_items()) {
            $data['items'] = $this->jenis_pesawat_model->get_grouped();
        } else {
            $data['items']     = [];
            $data['ajaxItems'] = true;
        }
        $data['kelompok_alat'] = $this->jenis_pesawat_model->get_groups();

        $data['staff']          = $this->staff_model->get('', ['active' => 1]);
        $data['peralatan']       = $this->peralatan_model->get($id);
        $data['billable_tasks'] = [];
        $data['add_items']      = $this->_parse_items($data['peralatan']);

        if ($data['peralatan']->rel_type == 'lead') {
            $this->db->where('leadid', $data['peralatan']->clientid);
            $data['customer_id'] = $this->db->get(db_prefix() . 'clients')->row()->userid;
        } else {
            $data['customer_id'] = $data['peralatan']->clientid;
        }
        $data['custom_fields_rel_transfer'] = [
            'belongs_to' => 'peralatan',
            'clientid'     => $id,
        ];
        $this->load->view('admin/peralatan/invoice_convert_template', $data);
    }

    public function get_peralatan_convert_data($id)
    {
        $this->load->model('taxes_model');
        $data['taxes']         = $this->taxes_model->get();
        $data['currencies']    = $this->currencies_model->get();
        $data['base_currency'] = $this->currencies_model->get_base_currency();
        $this->load->model('jenis_pesawat_model');
        $data['ajaxItems'] = false;
        if (total_rows(db_prefix() . 'items') <= ajax_on_total_items()) {
            $data['items'] = $this->jenis_pesawat_model->get_grouped();
        } else {
            $data['items']     = [];
            $data['ajaxItems'] = true;
        }
        $data['kelompok_alat'] = $this->jenis_pesawat_model->get_groups();

        $data['staff']     = $this->staff_model->get('', ['active' => 1]);
        $data['peralatan']  = $this->peralatan_model->get($id);
        $data['add_items'] = $this->_parse_items($data['peralatan']);

        $this->load->model('peralatan_model');
        $data['peralatan_statuses'] = $this->peralatan_model->get_statuses();
        if ($data['peralatan']->rel_type == 'lead') {
            $this->db->where('leadid', $data['peralatan']->clientid);
            $data['customer_id'] = $this->db->get(db_prefix() . 'clients')->row()->userid;
        } else {
            $data['customer_id'] = $data['peralatan']->clientid;
        }

        $data['custom_fields_rel_transfer'] = [
            'belongs_to' => 'peralatan',
            'clientid'     => $id,
        ];

        $this->load->view('admin/peralatan/peralatan_convert_template', $data);
    }

    private function _parse_items($peralatan)
    {
        $items = [];
        foreach ($peralatan->items as $item) {
            $taxnames = [];
            $taxes    = get_peralatan_item_taxes($item['id']);
            foreach ($taxes as $tax) {
                array_push($taxnames, $tax['taxname']);
            }
            $item['taxname']        = $taxnames;
            $item['parent_item_id'] = $item['id'];
            $item['id']             = 0;
            $items[]                = $item;
        }

        return $items;
    }

    /* Send peralatan to email */
    public function send_to_email($id)
    {
        $canView = user_can_view_peralatan($id);
        if (!$canView) {
            access_denied('peralatan');
        } else {
            if (!has_permission('peralatan', '', 'view') && !has_permission('peralatan', '', 'view_own') && $canView == false) {
                access_denied('peralatan');
            }
        }

        if ($this->input->post()) {
            try {
                $success = $this->peralatan_model->send_peralatan_to_email(
                    $id,
                    $this->input->post('attach_pdf'),
                    $this->input->post('cc')
                );
            } catch (Exception $e) {
                $message = $e->getMessage();
                echo $message;
                if (strpos($message, 'Unable to get the size of the image') !== false) {
                    show_pdf_unable_to_get_image_size_error();
                }
                die;
            }

            if ($success) {
                set_alert('success', _l('peralatan_sent_to_email_success'));
            } else {
                set_alert('danger', _l('peralatan_sent_to_email_fail'));
            }

            if ($this->set_peralatan_pipeline_autoload($id)) {
                redirect($_SERVER['HTTP_REFERER']);
            } else {
                redirect(admin_url('peralatan/list_peralatan/' . $id . '#' . $id));
            }
        }
    }

    public function copy($id)
    {
        if (!has_permission('peralatan', '', 'create')) {
            access_denied('peralatan');
        }
        $new_id = $this->peralatan_model->copy($id);
        if ($new_id) {
            set_alert('success', _l('peralatan_copy_success'));
            $this->set_peralatan_pipeline_autoload($new_id);
            redirect(admin_url('peralatan/peralatan/' . $new_id));
        } else {
            set_alert('success', _l('peralatan_copy_fail'));
        }
        if ($this->set_peralatan_pipeline_autoload($id)) {
            redirect(admin_url('peralatan'));
        } else {
            redirect(admin_url('peralatan/list_peralatan/' . $id . '#' . $id));
        }
    }

    public function mark_action_status($status, $id)
    {
        if (!has_permission('peralatan', '', 'edit')) {
            access_denied('peralatan');
        }
        $success = $this->peralatan_model->mark_action_status($status, $id);
        if ($success) {
            set_alert('success', _l('peralatan_status_changed_success'));
        } else {
            set_alert('danger', _l('peralatan_status_changed_fail'));
        }
        if ($this->set_peralatan_pipeline_autoload($id)) {
            redirect(admin_url('peralatan'));
        } else {
            redirect(admin_url('peralatan/list_peralatan/' . $id . '#' . $id));
        }
    }

    public function delete($id)
    {
        if (!has_permission('peralatan', '', 'delete')) {
            access_denied('peralatan');
        }
        $response = $this->peralatan_model->delete($id);
        if ($response == true) {
            set_alert('success', _l('deleted', _l('peralatan')));
        } else {
            set_alert('warning', _l('problem_deleting', _l('peralatan_lowercase')));
        }
        redirect(admin_url('peralatan'));
    }

    public function get_relation_data_values($clientid)
    {
        echo json_encode($this->peralatan_model->get_relation_data_values($clientid));
    }

    public function add_peralatan_comment()
    {
        if ($this->input->post()) {
            echo json_encode([
                'success' => $this->peralatan_model->add_comment($this->input->post()),
            ]);
        }
    }

    public function edit_comment($id)
    {
        if ($this->input->post()) {
            echo json_encode([
                'success' => $this->peralatan_model->edit_comment($this->input->post(), $id),
                'message' => _l('comment_updated_successfully'),
            ]);
        }
    }

    public function get_peralatan_comments($id)
    {
        $data['comments'] = $this->peralatan_model->get_comments($id);
        $this->load->view('admin/peralatan/comments_template', $data);
    }

    public function remove_comment($id)
    {
        $this->db->where('id', $id);
        $comment = $this->db->get(db_prefix() . 'peralatan_comments')->row();
        if ($comment) {
            if ($comment->staffid != get_staff_user_id() && !is_admin()) {
                echo json_encode([
                    'success' => false,
                ]);
                die;
            }
            echo json_encode([
                'success' => $this->peralatan_model->remove_comment($id),
            ]);
        } else {
            echo json_encode([
                'success' => false,
            ]);
        }
    }

    public function save_peralatan_data()
    {
        if (!has_permission('peralatan', '', 'edit') && !has_permission('peralatan', '', 'create')) {
            header('HTTP/1.0 400 Bad error');
            echo json_encode([
                'success' => false,
                'message' => _l('access_denied'),
            ]);
            die;
        }
        $success = false;
        $message = '';

        $this->db->where('id', $this->input->post('peralatan_id'));
        $this->db->update(db_prefix() . 'peralatan', [
            'content' => html_purify($this->input->post('content', false)),
        ]);

        $success = $this->db->affected_rows() > 0;
        $message = _l('updated_successfully', _l('peralatan'));

        echo json_encode([
            'success' => $success,
            'message' => $message,
        ]);
    }

    // Pipeline
    public function pipeline($set = 0, $manual = false)
    {
        if ($set == 1) {
            $set = 'true';
        } else {
            $set = 'false';
        }
        $this->session->set_userdata([
            'peralatan_pipeline' => $set,
        ]);
        if ($manual == false) {
            redirect(admin_url('peralatan'));
        }
    }

    public function pipeline_open($id)
    {
        if (has_permission('peralatan', '', 'view') || has_permission('peralatan', '', 'view_own') || get_option('allow_staff_view_peralatan_assigned') == 1) {
            $data['peralatan']      = $this->get_peralatan_data_ajax($id, true);
            $data['peralatan_data'] = $this->peralatan_model->get($id);
            $this->load->view('admin/peralatan/pipeline/peralatan', $data);
        }
    }

    public function update_pipeline()
    {
        if (has_permission('peralatan', '', 'edit')) {
            $this->peralatan_model->update_pipeline($this->input->post());
        }
    }

    public function get_pipeline()
    {
        if (has_permission('peralatan', '', 'view') || has_permission('peralatan', '', 'view_own') || get_option('allow_staff_view_peralatan_assigned') == 1) {
            $data['statuses'] = $this->peralatan_model->get_statuses();
            $this->load->view('admin/peralatan/pipeline/pipeline', $data);
        }
    }

    public function pipeline_load_more()
    {
        $status = $this->input->get('status');
        $page   = $this->input->get('page');

        $daftar_peralatan = (new PeralatanPipeline($status))
            ->search($this->input->get('search'))
            ->sortBy(
                $this->input->get('sort_by'),
                $this->input->get('sort')
            )
            ->page($page)->get();

        foreach ($daftar_peralatan as $peralatan) {
            $this->load->view('admin/peralatan/pipeline/_kanban_card', [
                'peralatan' => $peralatan,
                'status'   => $status,
            ]);
        }
    }

    public function set_peralatan_pipeline_autoload($id)
    {
        if ($id == '') {
            return false;
        }

        if ($this->session->has_userdata('peralatan_pipeline') && $this->session->userdata('peralatan_pipeline') == 'true') {
            $this->session->set_flashdata('peralatan_id', $id);

            return true;
        }

        return false;
    }

    public function get_due_date()
    {
        if ($this->input->post()) {
            $date    = $this->input->post('date');
            $duedate = '';
            if (get_option('peralatan_due_after') != 0) {
                $date    = to_sql_date($date);
                $d       = date('Y-m-d', strtotime('+' . get_option('peralatan_due_after') . ' DAY', strtotime($date)));
                $duedate = _d($d);
                echo $duedate;
            }
        }
    }

/*
    public function get_relation_data()
    {        
        if ($this->input->post()) {
            $type = $this->input->post('type');
            $data = apps_get_relation_data($type, '', $this->input->post('extra'));
            if ($this->input->post('rel_id')) {
                $rel_id = $this->input->post('rel_id');
            } else {
                $rel_id = '';
            }
            $relOptions = apps_init_relation_options($data, $type, $rel_id);
            echo json_encode($relOptions);
            die;
        }
    }
*/ 
}
