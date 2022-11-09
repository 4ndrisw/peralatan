<?php

use app\services\AbstractKanban;
use app\services\peralatan\PeralatanPipeline;

defined('BASEPATH') or exit('No direct script access allowed');

class Peralatan_model extends App_Model
{
    private $statuses;

    private $copy = false;

    public function __construct()
    {
        parent::__construct();
        $this->statuses = hooks()->apply_filters('before_set_peralatan_statuses', [
            6,
            4,
            1,
            5,
            2,
            3,
        ]);
    }

    public function get_statuses()
    {
        return $this->statuses;
    }

    public function get_sale_agents()
    {
        return $this->db->query('SELECT DISTINCT(assigned) as sale_agent FROM ' . db_prefix() . 'peralatan WHERE assigned != 0')->result_array();
    }

    public function get_peralatan_years()
    {
        return $this->db->query('SELECT DISTINCT(YEAR(date)) as year FROM ' . db_prefix() . 'peralatan')->result_array();
    }


    /**
     * Performs peralatan totals status
     * @param array $data
     * @return array
     */
    public function get_peralatan_total($data)
    {
        $statuses            = $this->get_statuses();
        $has_permission_view = has_permission('peralatan', '', 'view');
        $this->load->model('currencies_model');
        if (isset($data['currency'])) {
            $currencyid = $data['currency'];
        } elseif (isset($data['customer_id']) && $data['customer_id'] != '') {
            $currencyid = $this->clients_model->get_customer_default_currency($data['customer_id']);
            if ($currencyid == 0) {
                $currencyid = $this->currencies_model->get_base_currency()->id;
            }
        } elseif (isset($data['project_id']) && $data['project_id'] != '') {
            $this->load->model('projects_model');
            $currencyid = $this->projects_model->get_currency($data['project_id'])->id;
        } else {
            $currencyid = $this->currencies_model->get_base_currency()->id;
        }

        $currency = get_currency($currencyid);
        $where    = '';
        if (isset($data['customer_id']) && $data['customer_id'] != '') {
            $where = ' AND clientid=' . $data['customer_id'];
        }

        if (isset($data['project_id']) && $data['project_id'] != '') {
            $where .= ' AND project_id=' . $data['project_id'];
        }

        if (!$has_permission_view) {
            $where .= ' AND ' . get_peralatan_where_sql_for_staff(get_staff_user_id());
        }

        $sql = 'SELECT';
        foreach ($statuses as $equipment_status) {
            $sql .= '(SELECT SUM(total) FROM ' . db_prefix() . 'peralatan WHERE status=' . $equipment_status;
            $sql .= ' AND currency =' . $this->db->escape_str($currencyid);
            if (isset($data['years']) && count($data['years']) > 0) {
                $sql .= ' AND YEAR(date) IN (' . implode(', ', array_map(function ($year) {
                    return get_instance()->db->escape_str($year);
                }, $data['years'])) . ')';
            } else {
                $sql .= ' AND YEAR(date) = ' . date('Y');
            }
            $sql .= $where;
            $sql .= ') as "' . $equipment_status . '",';
        }

        $sql     = substr($sql, 0, -1);
        $result  = $this->db->query($sql)->result_array();
        $_result = [];
        $i       = 1;
        foreach ($result as $key => $val) {
            foreach ($val as $status => $total) {
                $_result[$i]['total']         = $total;
                $_result[$i]['symbol']        = $currency->symbol;
                $_result[$i]['currency_name'] = $currency->name;
                $_result[$i]['status']        = $status;
                $i++;
            }
        }
        $_result['currencyid'] = $currencyid;

        return $_result;
    }

    /**
     * Inserting new peralatan function
     * @param mixed $data $_POST data
     */
    public function add($data)
    {
        $data['allow_comments'] = isset($data['allow_comments']) ? 1 : 0;

        $save_and_send = isset($data['save_and_send']);

        $tags = isset($data['tags']) ? $data['tags'] : '';

        if (isset($data['custom_fields'])) {
            $custom_fields = $data['custom_fields'];
            unset($data['custom_fields']);
        }

        $peralatanRequestID = false;
        if (isset($data['peralatan_request_id'])) {
            $peralatanRequestID = $data['peralatan_request_id'];
            unset($data['peralatan_request_id']);
        }

        $data['address'] = trim($data['address']);
        $data['address'] = nl2br($data['address']);

        $data['datecreated'] = date('Y-m-d H:i:s');
        $data['addedfrom']   = get_staff_user_id();
        $data['hash']        = app_generate_hash();
        /*
        if (empty($data['rel_type'])) {
            unset($data['rel_type']);
            unset($data['clientid']);
        } else {
            if (empty($data['clientid'])) {
                unset($data['rel_type']);
                unset($data['clientid']);
            }
        }
        */

        $items = [];
        if (isset($data['newitems'])) {
            $items = $data['newitems'];
            unset($data['newitems']);
        }

        if ($this->copy == false) {
            $data['content'] = '{peralatan_items}';
        }

        $hook = hooks()->apply_filters('before_create_peralatan', [
            'data'  => $data,
            'items' => $items,
        ]);

        $data  = $hook['data'];
        $items = $hook['items'];
        unset($data['tags'],$data['item_select'], $data['description'], $data['long_description'],
              $data['quantity'], $data['unit'],$data['rate']
             );
        $this->db->insert(db_prefix() . 'peralatan', $data);
        $insert_id = $this->db->insert_id();

        if ($insert_id) {
            if ($peralatanRequestID !== false && $peralatanRequestID != '') {
                $this->load->model('peralatan_request_model');
                $completedStatus = $this->peralatan_request_model->get_status_by_flag('completed');
                $this->peralatan_request_model->update_request_status([
                    'requestid' => $peralatanRequestID,
                    'status'    => $completedStatus->id,
                ]);
            }

            if (isset($custom_fields)) {
                handle_custom_fields_post($insert_id, $custom_fields);
            }

            handle_tags_save($tags, $insert_id, 'peralatan');

            foreach ($items as $key => $item) {
                if ($itemid = add_new_sales_item_post($item, $insert_id, 'peralatan')) {
                    _maybe_insert_post_item_tax($itemid, $item, $insert_id, 'peralatan');
                }
            }

            $peralatan = $this->get($insert_id);
            if ($peralatan->assigned != 0) {
                if ($peralatan->assigned != get_staff_user_id()) {
                    $notified = add_notification([
                        'description'     => 'not_peralatan_assigned_to_you',
                        'touserid'        => $peralatan->assigned,
                        'fromuserid'      => get_staff_user_id(),
                        'link'            => 'peralatan/list_peralatan/' . $insert_id,
                        'additional_data' => serialize([
                            $peralatan->subject,
                        ]),
                    ]);
                    if ($notified) {
                        pusher_trigger_notification([$peralatan->assigned]);
                    }
                }
            }

            if ($data['rel_type'] == 'lead') {
                $this->load->model('leads_model');
                $this->leads_model->log_lead_activity($data['clientid'], 'not_lead_activity_created_peralatan', false, serialize([
                    '<a href="' . admin_url('peralatan/list_peralatan/' . $insert_id) . '" target="_blank">' . $data['subject'] . '</a>',
                ]));
            }

            update_sales_total_tax_column($insert_id, 'peralatan', db_prefix() . 'peralatan');

            log_activity('New Peralatan Created [ID: ' . $insert_id . ']');

            if ($save_and_send === true) {
                $this->send_peralatan_to_email($insert_id);
            }

            hooks()->do_action('peralatan_created', $insert_id);

            return $insert_id;
        }

        return false;
    }

    /**
     * Update peralatan
     * @param  mixed $data $_POST data
     * @param  mixed $id   peralatan id
     * @return boolean
     */
    public function update($data, $id)
    {
        $affectedRows = 0;

        $data['allow_comments'] = isset($data['allow_comments']) ? 1 : 0;

        $current_peralatan = $this->get($id);

        $save_and_send = isset($data['save_and_send']);

        /*
        if (empty($data['rel_type'])) {
            $data['clientid']   = null;
            $data['rel_type'] = '';
        } else {
            if (empty($data['clientid'])) {
                $data['clientid']   = null;
                $data['rel_type'] = '';
            }
        }
        */

        if (isset($data['custom_fields'])) {
            $custom_fields = $data['custom_fields'];
            if (handle_custom_fields_post($id, $custom_fields)) {
                $affectedRows++;
            }
            unset($data['custom_fields']);
        }

        $items = [];
        if (isset($data['items'])) {
            $items = $data['items'];
            unset($data['items']);
        }

        $newitems = [];
        if (isset($data['newitems'])) {
            $newitems = $data['newitems'];
            unset($data['newitems']);
        }

        if (isset($data['tags'])) {
            if (handle_tags_save($data['tags'], $id, 'peralatan')) {
                $affectedRows++;
            }
        }

        $data['address'] = trim($data['address']);
        $data['address'] = nl2br($data['address']);

        $hook = hooks()->apply_filters('before_peralatan_updated', [
            'data'          => $data,
            'items'         => $items,
            'newitems'      => $newitems,
            'removed_items' => isset($data['removed_items']) ? $data['removed_items'] : [],
        ], $id);

        $data                  = $hook['data'];
        $data['removed_items'] = $hook['removed_items'];
        $newitems              = $hook['newitems'];
        $items                 = $hook['items'];

        // Delete items checked to be removed from database
        foreach ($data['removed_items'] as $remove_item_id) {
            if (handle_removed_sales_item_post($remove_item_id, 'peralatan')) {
                $affectedRows++;
            }
        }

        unset($data['removed_items']);
        unset($data['tags']);
        unset($data['item_select']);
        unset($data['description']);
        unset($data['long_description']);
        unset($data['quantity']);
        unset($data['unit']);
        unset($data['rate']);
        unset($data['taxname']);




        $this->db->where('id', $id);
        $this->db->update(db_prefix() . 'peralatan', $data);
        if ($this->db->affected_rows() > 0) {
            $affectedRows++;
            $peralatan_now = $this->get($id);
            if ($current_peralatan->assigned != $peralatan_now->assigned) {
                if ($peralatan_now->assigned != get_staff_user_id()) {
                    $notified = add_notification([
                        'description'     => 'not_peralatan_assigned_to_you',
                        'touserid'        => $peralatan_now->assigned,
                        'fromuserid'      => get_staff_user_id(),
                        'link'            => 'peralatan/list_peralatan/' . $id,
                        'additional_data' => serialize([
                            $peralatan_now->subject,
                        ]),
                    ]);
                    if ($notified) {
                        pusher_trigger_notification([$peralatan_now->assigned]);
                    }
                }
            }
        }

        foreach ($items as $key => $item) {
            if (update_sales_item_post($item['itemid'], $item)) {
                $affectedRows++;
            }

            if (isset($item['custom_fields'])) {
                if (handle_custom_fields_post($item['itemid'], $item['custom_fields'])) {
                    $affectedRows++;
                }
            }

            if (!isset($item['taxname']) || (isset($item['taxname']) && count($item['taxname']) == 0)) {
                if (delete_taxes_from_item($item['itemid'], 'peralatan')) {
                    $affectedRows++;
                }
            } else {
                $item_taxes        = get_peralatan_item_taxes($item['itemid']);
                $_item_taxes_names = [];
                foreach ($item_taxes as $_item_tax) {
                    array_push($_item_taxes_names, $_item_tax['taxname']);
                }
                $i = 0;
                foreach ($_item_taxes_names as $_item_tax) {
                    if (!in_array($_item_tax, $item['taxname'])) {
                        $this->db->where('id', $item_taxes[$i]['id'])
                        ->delete(db_prefix() . 'item_tax');
                        if ($this->db->affected_rows() > 0) {
                            $affectedRows++;
                        }
                    }
                    $i++;
                }
                if (_maybe_insert_post_item_tax($item['itemid'], $item, $id, 'peralatan')) {
                    $affectedRows++;
                }
            }
        }

        foreach ($newitems as $key => $item) {
            if ($new_item_added = add_new_sales_item_post($item, $id, 'peralatan')) {
                _maybe_insert_post_item_tax($new_item_added, $item, $id, 'peralatan');
                $affectedRows++;
            }
        }

        if ($affectedRows > 0) {
            update_sales_total_tax_column($id, 'peralatan', db_prefix() . 'peralatan');
            log_activity('Peralatan Updated [ID:' . $id . ']');
        }

        if ($save_and_send === true) {
            $this->send_peralatan_to_email($id);
        }

        if ($affectedRows > 0) {
            hooks()->do_action('after_peralatan_updated', $id);

            return true;
        }

        return false;
    }

    /**
     * Get peralatan
     * @param  mixed $id peralatan id OPTIONAL
     * @return mixed
     */
    public function get($id = '', $where = [], $for_editor = false)
    {
        $this->db->where($where);

        if (is_client_logged_in()) {
            $this->db->where('status !=', 0);
        }

        $this->db->select('*,' . db_prefix() . 'currencies.id as currencyid, ' . db_prefix() . 'peralatan.id as id, ' . db_prefix() . 'currencies.name as currency_name');
        $this->db->from(db_prefix() . 'peralatan');
        $this->db->join(db_prefix() . 'currencies', db_prefix() . 'currencies.id = ' . db_prefix() . 'peralatan.currency', 'left');

        if (is_numeric($id)) {
            $this->db->where(db_prefix() . 'peralatan.id', $id);
            $peralatan = $this->db->get()->row();
            if ($peralatan) {
                $peralatan->attachments                           = $this->get_attachments($id);
                //$peralatan->items                                 = get_items_by_type('peralatan', $id);
                $peralatan->visible_attachments_to_customer_found = false;
                foreach ($peralatan->attachments as $attachment) {
                    if ($attachment['visible_to_customer'] == 1) {
                        $peralatan->visible_attachments_to_customer_found = true;

                        break;
                    }
                }
                /*
                 *next_feature
                if ($for_editor == false) {
                    $peralatan = parse_peralatan_content_merge_fields($peralatan);
                }
                */
            }

            $peralatan->client = $this->clients_model->get($peralatan->clientid);

            if (!$peralatan->client) {
                $peralatan->client          = new stdClass();
                $peralatan->client->company = $peralatan->deleted_customer_name;
            }
            
            return $peralatan;
        }

        return $this->db->get()->result_array();
    }


    /**
     * Get jenis_pesawat
     * @param  mixed $id peralatan id OPTIONAL
     * @return mixed
     */
    public function get_jenis_pesawat($id = '', $where = [], $for_editor = false)
    {
        $this->db->where($where);

        if (is_client_logged_in()) {
            $this->db->where('status !=', 0);
        }

        $this->db->select(['id', 'nama']);
        $this->db->from(db_prefix() . 'jenis_pesawat');
        //$this->db->join(db_prefix() . 'currencies', db_prefix() . 'currencies.id = ' . db_prefix() . 'peralatan.currency', 'left');

        if (is_numeric($id)) {
            $this->db->where(db_prefix() . 'peralatan.id', $id);
            $jenis_pesawat = $this->db->get()->row();
            $jenis_pesawat->category = $this->peralatan_model->get_category($peralatan->clientid);
            
            return $jenis_pesawat;
        }


        return $this->db->get()->result_array();
    }

    public function clear_signature($id)
    {
        $this->db->select('signature');
        $this->db->where('id', $id);
        $peralatan = $this->db->get(db_prefix() . 'peralatan')->row();

        if ($peralatan) {
            $this->db->where('id', $id);
            $this->db->update(db_prefix() . 'peralatan', ['signature' => null]);

            if (!empty($peralatan->signature)) {
                unlink(get_upload_path_by_type('peralatan') . $id . '/' . $peralatan->signature);
            }

            return true;
        }

        return false;
    }

    public function update_pipeline($data)
    {
        $this->mark_action_status($data['status'], $data['peralatan_id']);
        AbstractKanban::updateOrder($data['order'], 'pipeline_order', 'peralatan', $data['status']);
    }

    public function get_attachments($peralatan_id, $id = '')
    {
        // If is passed id get return only 1 attachment
        if (is_numeric($id)) {
            $this->db->where('id', $id);
        } else {
            $this->db->where('rel_id', $peralatan_id);
        }
        $this->db->where('rel_type', 'peralatan');
        $result = $this->db->get(db_prefix() . 'files');
        if (is_numeric($id)) {
            return $result->row();
        }

        return $result->result_array();
    }

    /**
     *  Delete peralatan attachment
     * @param   mixed $id  attachmentid
     * @return  boolean
     */
    public function delete_attachment($id)
    {
        $attachment = $this->get_attachments('', $id);
        $deleted    = false;
        if ($attachment) {
            if (empty($attachment->external)) {
                unlink(get_upload_path_by_type('peralatan') . $attachment->clientid . '/' . $attachment->file_name);
            }
            $this->db->where('id', $attachment->id);
            $this->db->delete(db_prefix() . 'files');
            if ($this->db->affected_rows() > 0) {
                $deleted = true;
                log_activity('Peralatan Attachment Deleted [ID: ' . $attachment->clientid . ']');
            }
            if (is_dir(get_upload_path_by_type('peralatan') . $attachment->clientid)) {
                // Check if no attachments left, so we can delete the folder also
                $other_attachments = list_files(get_upload_path_by_type('peralatan') . $attachment->clientid);
                if (count($other_attachments) == 0) {
                    // okey only index.html so we can delete the folder also
                    delete_dir(get_upload_path_by_type('peralatan') . $attachment->clientid);
                }
            }
        }

        return $deleted;
    }

    /**
     * Add peralatan comment
     * @param mixed  $data   $_POST comment data
     * @param boolean $client is request coming from the client side
     */
    public function add_comment($data, $client = false)
    {
        if (is_staff_logged_in()) {
            $client = false;
        }

        if (isset($data['action'])) {
            unset($data['action']);
        }
        $data['dateadded'] = date('Y-m-d H:i:s');
        if ($client == false) {
            $data['staffid'] = get_staff_user_id();
        }
        $data['content'] = nl2br($data['content']);
        $this->db->insert(db_prefix() . 'peralatan_comments', $data);
        $insert_id = $this->db->insert_id();
        if ($insert_id) {
            $peralatan = $this->get($data['peralatan_id']);

            // No notifications client when peralatan is with draft status
            if ($peralatan->status == '6' && $client == false) {
                return true;
            }

            if ($client == true) {
                // Get creator and assigned
                $this->db->select('staffid,email,phonenumber');
                $this->db->where('staffid', $peralatan->addedfrom);
                $this->db->or_where('staffid', $peralatan->assigned);
                $staff_peralatan = $this->db->get(db_prefix() . 'staff')->result_array();
                $notifiedUsers  = [];
                foreach ($staff_peralatan as $member) {
                    $notified = add_notification([
                        'description'     => 'not_peralatan_comment_from_client',
                        'touserid'        => $member['staffid'],
                        'fromcompany'     => 1,
                        'fromuserid'      => 0,
                        'link'            => 'peralatan/list_peralatan/' . $data['peralatan_id'],
                        'additional_data' => serialize([
                            $peralatan->subject,
                        ]),
                    ]);

                    if ($notified) {
                        array_push($notifiedUsers, $member['staffid']);
                    }

                    $template     = mail_template('peralatan_comment_to_staff', $peralatan->id, $member['email']);
                    $merge_fields = $template->get_merge_fields();
                    $template->send();
                    // Send email/sms to admin that client commented
                    $this->app_sms->trigger(SMS_TRIGGER_PROPOSAL_NEW_COMMENT_TO_STAFF, $member['phonenumber'], $merge_fields);
                }
                pusher_trigger_notification($notifiedUsers);
            } else {
                // Send email/sms to client that admin commented
                $template     = mail_template('peralatan_comment_to_customer', $peralatan);
                $merge_fields = $template->get_merge_fields();
                $template->send();
                $this->app_sms->trigger(SMS_TRIGGER_PROPOSAL_NEW_COMMENT_TO_CUSTOMER, $peralatan->phone, $merge_fields);
            }

            return true;
        }

        return false;
    }

    public function edit_comment($data, $id)
    {
        $this->db->where('id', $id);
        $this->db->update(db_prefix() . 'peralatan_comments', [
            'content' => nl2br($data['content']),
        ]);
        if ($this->db->affected_rows() > 0) {
            return true;
        }

        return false;
    }

    /**
     * Get peralatan comments
     * @param  mixed $id peralatan id
     * @return array
     */
    public function get_comments($id)
    {
        $this->db->where('peralatan_id', $id);
        $this->db->order_by('dateadded', 'ASC');

        return $this->db->get(db_prefix() . 'peralatan_comments')->result_array();
    }

    /**
     * Get peralatan single comment
     * @param  mixed $id  comment id
     * @return object
     */
    public function get_comment($id)
    {
        $this->db->where('id', $id);

        return $this->db->get(db_prefix() . 'peralatan_comments')->row();
    }

    /**
     * Remove peralatan comment
     * @param  mixed $id comment id
     * @return boolean
     */
    public function remove_comment($id)
    {
        $comment = $this->get_comment($id);
        $this->db->where('id', $id);
        $this->db->delete(db_prefix() . 'peralatan_comments');
        if ($this->db->affected_rows() > 0) {
            log_activity('Peralatan Comment Removed [PeralatanID:' . $comment->peralatan_id . ', Comment Content: ' . $comment->content . ']');

            return true;
        }

        return false;
    }

    /**
     * Copy peralatan
     * @param  mixed $id peralatan id
     * @return mixed
     */
    public function copy($id)
    {
        $this->copy      = true;
        $peralatan        = $this->get($id, [], true);
        $not_copy_fields = [
            'addedfrom',
            'id',
            'datecreated',
            'hash',
            'status',
            'invoice_id',
            'peralatan_id',
            'is_expiry_notified',
            'date_converted',
            'signature',
            'acceptance_firstname',
            'acceptance_lastname',
            'acceptance_email',
            'acceptance_date',
            'acceptance_ip',
        ];
        $fields      = $this->db->list_fields(db_prefix() . 'peralatan');
        $insert_data = [];
        foreach ($fields as $field) {
            if (!in_array($field, $not_copy_fields)) {
                $insert_data[$field] = $peralatan->$field;
            }
        }

        $insert_data['addedfrom']   = get_staff_user_id();
        $insert_data['datecreated'] = date('Y-m-d H:i:s');
        $insert_data['date']        = _d(date('Y-m-d'));
        $insert_data['status']      = 6;
        $insert_data['hash']        = app_generate_hash();

        // in case open till is expired set new 7 days starting from current date
        if ($insert_data['open_till'] && get_option('peralatan_due_after') != 0) {
            $insert_data['open_till'] = _d(date('Y-m-d', strtotime('+' . get_option('peralatan_due_after') . ' DAY', strtotime(date('Y-m-d')))));
        }

        $insert_data['newitems'] = [];
        $custom_fields_items     = get_custom_fields('items');
        $key                     = 1;
        foreach ($peralatan->items as $item) {
            $insert_data['newitems'][$key]['description']      = $item['description'];
            $insert_data['newitems'][$key]['long_description'] = clear_textarea_breaks($item['long_description']);
            $insert_data['newitems'][$key]['qty']              = $item['qty'];
            $insert_data['newitems'][$key]['unit']             = $item['unit'];
            $insert_data['newitems'][$key]['taxname']          = [];
            $taxes                                             = get_peralatan_item_taxes($item['id']);
            foreach ($taxes as $tax) {
                // tax name is in format TAX1|10.00
                array_push($insert_data['newitems'][$key]['taxname'], $tax['taxname']);
            }
            $insert_data['newitems'][$key]['rate']  = $item['rate'];
            $insert_data['newitems'][$key]['order'] = $item['item_order'];
            foreach ($custom_fields_items as $cf) {
                $insert_data['newitems'][$key]['custom_fields']['items'][$cf['id']] = get_custom_field_value($item['id'], $cf['id'], 'items', false);

                if (!defined('COPY_CUSTOM_FIELDS_LIKE_HANDLE_POST')) {
                    define('COPY_CUSTOM_FIELDS_LIKE_HANDLE_POST', true);
                }
            }
            $key++;
        }

        $id = $this->add($insert_data);

        if ($id) {
            $custom_fields = get_custom_fields('peralatan');
            foreach ($custom_fields as $field) {
                $value = get_custom_field_value($peralatan->id, $field['id'], 'peralatan', false);
                if ($value == '') {
                    continue;
                }
                $this->db->insert(db_prefix() . 'customfieldsvalues', [
                    'relid'   => $id,
                    'fieldid' => $field['id'],
                    'fieldto' => 'peralatan',
                    'value'   => $value,
                ]);
            }

            $tags = get_tags_in($peralatan->id, 'peralatan');
            handle_tags_save($tags, $id, 'peralatan');

            log_activity('Copied Peralatan ' . format_peralatan_number($peralatan->id));

            return $id;
        }

        return false;
    }

    /**
     * Take peralatan action (change status) manually
     * @param  mixed $status status id
     * @param  mixed  $id     peralatan id
     * @param  boolean $client is request coming from client side or not
     * @return boolean
     */
    public function mark_action_status($status, $id, $client = false)
    {
        $original_peralatan = $this->get($id);
        $this->db->where('id', $id);
        $this->db->update(db_prefix() . 'peralatan', [
            'status' => $status,
        ]);

        if ($this->db->affected_rows() > 0) {
            // Client take action
            if ($client == true) {
                $revert = false;
                // Declined
                if ($status == 2) {
                    $message = 'not_peralatan_peralatan_declined';
                } elseif ($status == 3) {
                    $message = 'not_peralatan_peralatan_accepted';
                // Accepted
                } else {
                    $revert = true;
                }
                // This is protection that only 3 and 4 statuses can be taken as action from the client side
                if ($revert == true) {
                    $this->db->where('id', $id);
                    $this->db->update(db_prefix() . 'peralatan', [
                        'status' => $original_peralatan->status,
                    ]);

                    return false;
                }

                // Get creator and assigned;
                $this->db->where('staffid', $original_peralatan->addedfrom);
                $this->db->or_where('staffid', $original_peralatan->assigned);
                $staff_peralatan = $this->db->get(db_prefix() . 'staff')->result_array();
                $notifiedUsers  = [];
                foreach ($staff_peralatan as $member) {
                    $notified = add_notification([
                            'fromcompany'     => true,
                            'touserid'        => $member['staffid'],
                            'description'     => $message,
                            'link'            => 'peralatan/list_peralatan/' . $id,
                            'additional_data' => serialize([
                                format_peralatan_number($id),
                            ]),
                        ]);
                    if ($notified) {
                        array_push($notifiedUsers, $member['staffid']);
                    }
                }

                pusher_trigger_notification($notifiedUsers);

                // Send thank you to the customer email template
                if ($status == 3) {
                    foreach ($staff_peralatan as $member) {
                        send_mail_template('peralatan_accepted_to_staff', $original_peralatan, $member['email']);
                    }

                    send_mail_template('peralatan_accepted_to_customer', $original_peralatan);

                    hooks()->do_action('peralatan_accepted', $id);
                } else {

                    // Client declined send template to admin
                    foreach ($staff_peralatan as $member) {
                        send_mail_template('peralatan_declined_to_staff', $original_peralatan, $member['email']);
                    }

                    hooks()->do_action('peralatan_declined', $id);
                }
            } else {
                // in case admin mark as open the the open till date is smaller then current date set open till date 7 days more
                if ((date('Y-m-d', strtotime($original_peralatan->open_till)) < date('Y-m-d')) && $status == 1) {
                    $open_till = date('Y-m-d', strtotime('+7 DAY', strtotime(date('Y-m-d'))));
                    $this->db->where('id', $id);
                    $this->db->update(db_prefix() . 'peralatan', [
                        'open_till' => $open_till,
                    ]);
                }
            }

            log_activity('Peralatan Status Changes [PeralatanID:' . $id . ', Status:' . format_peralatan_status($status, '', false) . ',Client Action: ' . (int) $client . ']');

            return true;
        }

        return false;
    }

    /**
     * Delete peralatan
     * @param  mixed $id peralatan id
     * @return boolean
     */
    public function delete($id)
    {
        $this->clear_signature($id);
        $peralatan = $this->get($id);

        $this->db->where('id', $id);
        $this->db->delete(db_prefix() . 'peralatan');
        if ($this->db->affected_rows() > 0) {
            if (!is_null($peralatan->short_link)) {
                app_archive_short_link($peralatan->short_link);
            }

            delete_tracked_emails($id, 'peralatan');

            $this->db->where('peralatan_id', $id);
            $this->db->delete(db_prefix() . 'peralatan_comments');
            // Get related tasks
            $this->db->where('rel_type', 'peralatan');
            $this->db->where('clientid', $id);

            $tasks = $this->db->get(db_prefix() . 'tasks')->result_array();
            foreach ($tasks as $task) {
                $this->tasks_model->delete_task($task['id']);
            }

            $attachments = $this->get_attachments($id);
            foreach ($attachments as $attachment) {
                $this->delete_attachment($attachment['id']);
            }

            $this->db->where('clientid', $id);
            $this->db->where('rel_type', 'peralatan');
            $this->db->delete(db_prefix() . 'notes');

            $this->db->where('relid IN (SELECT id from ' . db_prefix() . 'itemable WHERE rel_type="peralatan" AND clientid="' . $this->db->escape_str($id) . '")');
            $this->db->where('fieldto', 'items');
            $this->db->delete(db_prefix() . 'customfieldsvalues');

            $this->db->where('clientid', $id);
            $this->db->where('rel_type', 'peralatan');
            $this->db->delete(db_prefix() . 'itemable');


            $this->db->where('clientid', $id);
            $this->db->where('rel_type', 'peralatan');
            $this->db->delete(db_prefix() . 'item_tax');

            $this->db->where('clientid', $id);
            $this->db->where('rel_type', 'peralatan');
            $this->db->delete(db_prefix() . 'taggables');

            // Delete the custom field values
            $this->db->where('relid', $id);
            $this->db->where('fieldto', 'peralatan');
            $this->db->delete(db_prefix() . 'customfieldsvalues');

            $this->db->where('rel_type', 'peralatan');
            $this->db->where('clientid', $id);
            $this->db->delete(db_prefix() . 'reminders');

            $this->db->where('rel_type', 'peralatan');
            $this->db->where('clientid', $id);
            $this->db->delete(db_prefix() . 'views_tracking');

            log_activity('Peralatan Deleted [PeralatanID:' . $id . ']');

            return true;
        }

        return false;
    }

    /**
     * Get relation peralatan data. Ex lead or customer will return the necesary db fields
     * @param  mixed $clientid
     * @param  string $rel_type customer/lead
     * @return object
     */
    public function get_relation_data_values($clientid)
    {
        $data = new StdClass();

        $this->db->where('userid', $clientid);
        $_data = $this->db->get(db_prefix() . 'clients')->row();

        $primary_contact_id = get_primary_contact_user_id($clientid);

        if ($primary_contact_id) {
            $contact     = $this->clients_model->get_contact($primary_contact_id);
            $data->email = $contact->email;
        }

        $data->phone            = $_data->phonenumber;
        $data->is_using_company = false;
        if (isset($contact)) {
            $data->to = $contact->firstname . ' ' . $contact->lastname;
        } else {
            if (!empty($_data->company)) {
                $data->to               = $_data->company;
                $data->is_using_company = true;
            }
        }
        $data->company = $_data->company;
        $data->address = clear_textarea_breaks($_data->address);
        $data->zip     = $_data->zip;
        $data->country = $_data->country;
        $data->state   = $_data->state;
        $data->city    = $_data->city;

        $default_currency = $this->clients_model->get_customer_default_currency($clientid);
        if ($default_currency != 0) {
            $data->currency = $default_currency;
        }

        return $data;
    }

    /**
     * Sent peralatan to email
     * @param  mixed  $id        peralatan_id
     * @param  string  $template  email template to sent
     * @param  boolean $attachpdf attach peralatan pdf or not
     * @return boolean
     */
    public function send_expiry_reminder($id)
    {
        $peralatan = $this->get($id);

        // For all cases update this to prevent sending multiple reminders eq on fail
        $this->db->where('id', $peralatan->id);
        $this->db->update(db_prefix() . 'peralatan', [
            'is_expiry_notified' => 1,
        ]);

        $template     = mail_template('peralatan_expiration_reminder', $peralatan);
        $merge_fields = $template->get_merge_fields();

        $template->send();

        if (can_send_sms_based_on_creation_date($peralatan->datecreated)) {
            $sms_sent = $this->app_sms->trigger(SMS_TRIGGER_PROPOSAL_EXP_REMINDER, $peralatan->phone, $merge_fields);
        }

        return true;
    }

    public function send_peralatan_to_email($id, $attachpdf = true, $cc = '')
    {
        // Peralatan status is draft update to sent
        if (total_rows(db_prefix() . 'peralatan', ['id' => $id, 'status' => 6]) > 0) {
            $this->db->where('id', $id);
            $this->db->update(db_prefix() . 'peralatan', ['status' => 4]);
        }

        $peralatan = $this->get($id);

        $sent = send_mail_template('peralatan_send_to_customer', $peralatan, $attachpdf, $cc);

        if ($sent) {

            // Set to status sent
            $this->db->where('id', $id);
            $this->db->update(db_prefix() . 'peralatan', [
                'status' => 4,
            ]);

            hooks()->do_action('peralatan_sent', $id);

            return true;
        }

        return false;
    }

    public function do_kanban_query($status, $search = '', $page = 1, $sort = [], $count = false)
    {
        _deprecated_function('Peralatan_model::do_kanban_query', '2.9.2', 'PeralatanPipeline class');

        $kanBan = (new PeralatanPipeline($status))
            ->search($search)
            ->page($page)
            ->sortBy($sort['sort'] ?? null, $sort['sort_by'] ?? null);

        if ($count) {
            return $kanBan->countAll();
        }

        return $kanBan->get();
    }

    /**
     * Get the peralatan for the client given
     *
     * @param  integer|null $staffId
     * @param  integer $days
     *
     * @return array
     */
    public function get_client_peralatan($client = null)
    {
        /*
        if ($staffId && ! staff_can('view', 'peralatan', $staffId)) {
            $this->db->where('addedfrom', $staffId);
        }
        */

        $this->db->select( db_prefix() . 'clients.userid,' . db_prefix() . 'peralatan.hash,' . db_prefix() . 'peralatan.date');
        $this->db->join(db_prefix() . 'clients', db_prefix() . 'clients.userid = ' . db_prefix() . 'peralatan.clientid', 'left');
        
        $this->db->where(db_prefix() . 'peralatan.clientid =', $client->userid);

        return $this->db->get(db_prefix() . 'peralatan')->result_array();
    }
}
