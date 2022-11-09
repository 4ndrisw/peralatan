<?php

defined('BASEPATH') or exit('No direct script access allowed');

class Myperalatan extends ClientsController
{
    public function __construct()
    {
        parent::__construct();
        $this->load->model('peralatan_model');
        $this->load->model('currencies_model');
        //include_once(module_libs_path(PERALATAN_MODULE_NAME) . 'mails/Peralatan_mail_template.php');
        //$this->load->library('module_name/library_name');
        //$this->load->library('peralatan_mail_template');
        //include_once(module_libs_path(PERALATAN_MODULE_NAME) . 'mails/Peralatan_send_to_customer.php');
        //$this->load->library('module_name/library_name');
        //$this->load->library('peralatan_send_to_customer');


    }

    /* Get all peralatan in case user go on index page */
    public function list($id = '')
    {
        
        if ($this->input->is_ajax_request()) {
            $this->app->get_table_data(module_views_path('peralatan', 'admin/tables/table'));
        }
        $contact_id = get_contact_user_id();
        $user_id = get_user_id_by_contact_id($contact_id);
        $client = $this->clients_model->get($user_id);
        
        $data['daftar_peralatan'] = $this->peralatan_model->get_client_peralatan($client);
        $data['client'] = $client;
        $data['peralatan_statuses'] = $this->peralatan_model->get_statuses();
        $data['peralatanid']            = $id;
        $data['title']                 = _l('peralatan_tracking');
        
        $data['bodyclass'] = 'peralatan';
        $this->data($data);
        $this->view('themes/'. active_clients_theme() .'/views/peralatan/peralatan');
        $this->layout();

    }

    public function show($id, $hash)
    {
        check_peralatan_restrictions($id, $hash);
        $peralatan = $this->peralatan_model->get($id);

        if ($peralatan->rel_type == 'customer' && !is_client_logged_in()) {
            load_client_language($peralatan->clientid);
        } else if ($peralatan->rel_type == 'lead') {
            load_lead_language($peralatan->clientid);
        }

        $identity_confirmation_enabled = get_option('peralatan_accept_identity_confirmation');
        if ($this->input->post()) {
            $action = $this->input->post('action');
            switch ($action) {
                case 'peralatan_comment':
                    // comment is blank
                    if (!$this->input->post('content')) {
                        redirect($this->uri->uri_string());
                    }
                    $data               = $this->input->post();
                    $data['peralatan_id'] = $id;
                    $this->peralatan_model->add_comment($data, true);
                    redirect($this->uri->uri_string() . '?tab=discussion');

                    break;
                case 'accept_peralatan':
                    $success = $this->peralatan_model->mark_action_status(3, $id, true);
                    if ($success) {
                        process_digital_signature_image($this->input->post('signature', false), PROPOSAL_ATTACHMENTS_FOLDER . $id);

                        $this->db->where('id', $id);
                        $this->db->update(db_prefix() . 'peralatan', get_acceptance_info_array());
                        redirect($this->uri->uri_string(), 'refresh');
                    }

                    break;
                case 'decline_peralatan':
                    $success = $this->peralatan_model->mark_action_status(2, $id, true);
                    if ($success) {
                        redirect($this->uri->uri_string(), 'refresh');
                    }

                    break;
            }
        }

        $number_word_lang_clientid = 'unknown';
        if ($peralatan->rel_type == 'customer') {
            $number_word_lang_clientid = $peralatan->clientid;
        }
        $this->load->library('app_number_to_word', [
            'clientid' => $number_word_lang_clientid,
        ], 'numberword');

        $this->disableNavigation();
        $this->disableSubMenu();

        $data['title']     = $peralatan->subject;
        $data['can_be_accepted']               = false;
        $data['peralatan']  = hooks()->apply_filters('peralatan_html_pdf_data', $peralatan);
        $data['bodyclass'] = 'peralatan peralatan-view';

        $data['identity_confirmation_enabled'] = $identity_confirmation_enabled;
        if ($identity_confirmation_enabled == '1') {
            $data['bodyclass'] .= ' identity-confirmation';
        }

        $this->app_scripts->theme('sticky-js', 'assets/plugins/sticky/sticky.js');

        $data['comments'] = $this->peralatan_model->get_comments($id);
        add_views_tracking('peralatan', $id);
        hooks()->do_action('peralatan_html_viewed', $id);
        hooks()->add_action('app_admin_head', 'peralatan_head_component');

        $this->app_css->remove('reset-css', 'customers-area-default');

        $data                      = hooks()->apply_filters('peralatan_customers_area_view_data', $data);
        no_index_customers_area();
        $this->data($data);

        $this->view('themes/' . active_clients_theme() . '/views/peralatan/peralatan_html');

        $this->layout();
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
        $peralatan_number = format_peralatan_number($id);
        /*
        echo '<pre>';
        var_dump($peralatan);
        echo '</pre>';
        die();
        */

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

        $pdf->Output($peralatan_number . '.pdf', $type);
    }
}
