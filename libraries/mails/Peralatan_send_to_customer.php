<?php

defined('BASEPATH') or exit('No direct script access allowed');

class Peralatan_send_to_customer extends App_mail_template
{
    protected $for = 'customer';

    protected $peralatan;

    protected $contact;

    public $slug = 'peralatan-send-to-client';

    public $rel_type = 'peralatan';

    public function __construct($peralatan, $contact, $cc = '')
    {
        parent::__construct();

        $this->peralatan = $peralatan;
        $this->contact = $contact;
        $this->cc      = $cc;
    }

    public function build()
    {
        if ($this->ci->input->post('email_attachments')) {
            $_other_attachments = $this->ci->input->post('email_attachments');
            foreach ($_other_attachments as $attachment) {
                $_attachment = $this->ci->peralatan_model->get_attachments($this->peralatan->id, $attachment);
                $this->add_attachment([
                                'attachment' => get_upload_path_by_type('peralatan') . $this->peralatan->id . '/' . $_attachment->file_name,
                                'filename'   => $_attachment->file_name,
                                'type'       => $_attachment->filetype,
                                'read'       => true,
                            ]);
            }
        }

        $this->to($this->contact->email)
        ->set_clientid($this->peralatan->id)
        ->set_merge_fields('client_merge_fields', $this->peralatan->clientid, $this->contact->id)
        ->set_merge_fields('peralatan_merge_fields', $this->peralatan->id);
    }
}
