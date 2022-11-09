<?php

defined('BASEPATH') or exit('No direct script access allowed');

include_once(LIBSPATH . 'pdf/App_pdf.php');

class Peralatan_pdf extends App_pdf
{
    protected $peralatan;

    private $peralatan_number;

    public function __construct($peralatan, $tag = '')
    {
        if ($peralatan->clientid != null && $peralatan->rel_type == 'customer') {
            $this->load_language($peralatan->clientid);
        } else if ($peralatan->clientid != null && $peralatan->rel_type == 'lead') {
            $CI = &get_instance();

            $this->load_language($peralatan->clientid);
            $CI->db->select('default_language')->where('id', $peralatan->clientid);
            $language = $CI->db->get('leads')->row()->default_language;

            load_pdf_language($language);
        }

        $peralatan                = hooks()->apply_filters('peralatan_html_pdf_data', $peralatan);
        $GLOBALS['peralatan_pdf'] = $peralatan;

        parent::__construct();

        $this->tag      = $tag;
        $this->peralatan = $peralatan;

        $this->peralatan_number = format_peralatan_number($this->peralatan->id);

        $this->SetTitle($this->peralatan_number);
        $this->SetDisplayMode('default', 'OneColumn');

        # Don't remove these lines - important for the PDF layout
        $this->peralatan->content = $this->fix_editor_html($this->peralatan->content);
    }

    public function prepare()
    {
        $number_word_lang_clientid = 'unknown';

        if ($this->peralatan->rel_type == 'customer') {
            $number_word_lang_clientid = $this->peralatan->clientid;
        }

        $this->with_number_to_word($number_word_lang_clientid);

        $total = '';
        if ($this->peralatan->total != 0) {
            $total = app_format_money($this->peralatan->total, get_currency($this->peralatan->currency));
            $total = _l('peralatan_total') . ': ' . $total;
        }

        $this->set_view_vars([
            'number'       => $this->peralatan_number,
            'peralatan'     => $this->peralatan,
            'total'        => $total,
            'peralatan_url' => site_url('peralatan/' . $this->peralatan->id . '/' . $this->peralatan->hash),
        ]);

        return $this->build();
    }

    protected function type()
    {
        return 'peralatan';
    }

    protected function file_path()
    {
        $filePath = 'my_peralatanpdf.php';
        $customPath = module_views_path('peralatan','themes/' . active_clients_theme() . '/views/peralatan/' . $filePath);
        $actualPath = module_views_path('peralatan','themes/' . active_clients_theme() . '/views/peralatan/peralatanpdf.php');

        if (file_exists($customPath)) {
            $actualPath = $customPath;
        }

        return $actualPath;
    }
}
