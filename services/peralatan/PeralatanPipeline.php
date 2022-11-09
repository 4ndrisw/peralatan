<?php

namespace modules\peralatan\services\peralatan;

use app\services\AbstractKanban;

class PeralatanPipeline extends AbstractKanban
{
    protected function table()
    {
        return 'peralatan';
    }

    public function defaultSortDirection()
    {
        return get_option('default_peralatan_pipeline_sort_type');
    }

    public function defaultSortColumn()
    {
        return get_option('default_peralatan_pipeline_sort');
    }

    public function limit()
    {
        return get_option('peralatan_pipeline_limit');
    }

    protected function applySearchQuery($q)
    {
        if (!startsWith($q, '#')) {
            $q = $this->ci->db->escape_like_str($q);
            $this->ci->db->where('(
                phone LIKE "%' . $q . '%" ESCAPE \'!\'
                OR
                zip LIKE "%' . $q . '%" ESCAPE \'!\'
                OR
                content LIKE "%' . $q . '%" ESCAPE \'!\'
                OR
                state LIKE "%' . $q . '%" ESCAPE \'!\'
                OR
                city LIKE "%' . $q . '%" ESCAPE \'!\'
                OR
                email LIKE "%' . $q . '%" ESCAPE \'!\'
                OR
                address LIKE "%' . $q . '%" ESCAPE \'!\'
                OR
                peralatan_to LIKE "%' . $q . '%" ESCAPE \'!\'
                OR
                total LIKE "%' . $q . '%" ESCAPE \'!\'
                OR
                subject LIKE "%' . $q . '%" ESCAPE \'!\')');
        } else {
            $this->ci->db->where(db_prefix() . 'peralatan.id IN
                (SELECT clientid FROM ' . db_prefix() . 'taggables WHERE tag_id IN
                (SELECT id FROM ' . db_prefix() . 'tags WHERE name="' . $this->ci->db->escape_str(strafter($q, '#')) . '")
                AND ' . db_prefix() . 'taggables.rel_type=\'peralatan\' GROUP BY clientid HAVING COUNT(tag_id) = 1)
                ');
        }

        return $this;
    }

    protected function initiateQuery()
    {
        $has_permission_view = has_permission('peralatan', '', 'view');
        $noPermissionQuery   = get_peralatan_sql_where_staff(get_staff_user_id());

        $this->ci->db->select('id,invoice_id,peralatan_id,subject,rel_type,clientid,total,date,open_till,currency,peralatan_to,status');
        $this->ci->db->from('peralatan');
        $this->ci->db->where('status', $this->status);

        if (!$has_permission_view) {
            $this->ci->db->where($noPermissionQuery);
        }

        return $this;
    }
}
