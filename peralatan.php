<?php

defined('BASEPATH') or exit('No direct script access allowed');

/*
Module Name: Peralatan
Description: Default module for defining peralatan
Version: 1.0.1
Requires at least: 2.3.*
*/

define('PERALATAN_MODULE_NAME', 'peralatan');
define('PERALATAN_ATTACHMENTS_FOLDER', FCPATH . 'uploads/peralatan/');

//hooks()->add_filter('before_peralatan_updated', '_format_data_peralatan_feature');
//hooks()->add_filter('before_peralatan_added', '_format_data_peralatan_feature');

//hooks()->add_action('after_custom_profile_tab_content', 'peralatan_content_tab_peralatan',10,1);
//hooks()->add_action('after_customer_admins_tab', 'peralatan_tab_peralatan',10,1);

hooks()->add_action('after_cron_run', 'peralatan_notification');
hooks()->add_action('admin_init', 'peralatan_module_init_menu_items');
hooks()->add_action('admin_init', 'peralatan_permissions');
hooks()->add_action('admin_init', 'peralatan_settings_tab');
hooks()->add_action('clients_init', 'peralatan_clients_area_menu_items');

//hooks()->add_action('app_admin_head', 'peralatan_head_component');
//hooks()->add_action('app_admin_footer', 'peralatan_footer_js_component');

hooks()->add_action('staff_member_deleted', 'peralatan_staff_member_deleted');

hooks()->add_filter('migration_tables_to_replace_old_links', 'peralatan_migration_tables_to_replace_old_links');
hooks()->add_filter('global_search_result_query', 'peralatan_global_search_result_query', 10, 3);
hooks()->add_filter('global_search_result_output', 'peralatan_global_search_result_output', 10, 2);
hooks()->add_filter('get_dashboard_widgets', 'peralatan_add_dashboard_widget');
hooks()->add_filter('module_peralatan_action_links', 'module_peralatan_action_links');


function peralatan_add_dashboard_widget($widgets)
{
    /*
    $widgets[] = [
        'path'      => 'peralatan/widgets/peralatan_this_week',
        'container' => 'left-8',
    ];
    $widgets[] = [
        'path'      => 'peralatan/widgets/project_not_peralatand',
        'container' => 'left-8',
    ];
    */

    return $widgets;
}


function peralatan_staff_member_deleted($data)
{
    $CI = &get_instance();
    $CI->db->where('staff_id', $data['id']);
    $CI->db->update(db_prefix() . 'peralatan', [
            'staff_id' => $data['transfer_data_to'],
        ]);
}

function peralatan_global_search_result_output($output, $data)
{
    if ($data['type'] == 'peralatan') {
        $output = '<a href="' . admin_url('peralatan/peralatan/' . $data['result']['id']) . '">' . format_peralatan_number($data['result']['id']) . '</a>';
    }

    return $output;
}

function peralatan_global_search_result_query($result, $q, $limit)
{
    $CI = &get_instance();
    if (has_permission('peralatan', '', 'view')) {

        // peralatan
        $CI->db->select()
           ->from(db_prefix() . 'peralatan')
           ->like(db_prefix() . 'peralatan.formatted_number', $q)->limit($limit);

        $result[] = [
                'result'         => $CI->db->get()->result_array(),
                'type'           => 'peralatan',
                'search_heading' => _l('peralatan'),
            ];

        if(isset($result[0]['result'][0]['id'])){
            return $result;
        }

        // peralatan
        $CI->db->select()->from(db_prefix() . 'peralatan')->like(db_prefix() . 'clients.company', $q)->or_like(db_prefix() . 'peralatan.formatted_number', $q)->limit($limit);
        $CI->db->join(db_prefix() . 'clients',db_prefix() . 'peralatan.clientid='.db_prefix() .'clients.userid', 'left');
        $CI->db->order_by(db_prefix() . 'clients.company', 'ASC');

        $result[] = [
                'result'         => $CI->db->get()->result_array(),
                'type'           => 'peralatan',
                'search_heading' => _l('peralatan'),
            ];
    }

    return $result;
}

function peralatan_migration_tables_to_replace_old_links($tables)
{
    $tables[] = [
                'table' => db_prefix() . 'peralatan',
                'field' => 'description',
            ];

    return $tables;
}

function peralatan_permissions()
{
    $capabilities = [];

    $capabilities['capabilities'] = [
            'view'   => _l('permission_view') . '(' . _l('permission_global') . ')',
            'create' => _l('permission_create'),
            'edit'   => _l('permission_edit'),
            'delete' => _l('permission_delete'),
    ];

    register_staff_capabilities('peralatan', $capabilities, _l('peralatan'));
}


/**
* Register activation module hook
*/
register_activation_hook(PERALATAN_MODULE_NAME, 'peralatan_module_activation_hook');

function peralatan_module_activation_hook()
{
    $CI = &get_instance();
    require_once(__DIR__ . '/install.php');
}

/**
* Register deactivation module hook
*/
register_deactivation_hook(PERALATAN_MODULE_NAME, 'peralatan_module_deactivation_hook');

function peralatan_module_deactivation_hook()
{

     log_activity( 'Hello, world! . peralatan_module_deactivation_hook ' );
}

//hooks()->add_action('deactivate_' . $module . '_module', $function);

/**
* Register language files, must be registered if the module is using languages
*/
register_language_files(PERALATAN_MODULE_NAME, [PERALATAN_MODULE_NAME]);

/**
 * Init peralatan module menu items in setup in admin_init hook
 * @return null
 */
function peralatan_module_init_menu_items()
{
    $CI = &get_instance();

    $CI->app->add_quick_actions_link([
            'name'       => _l('peralatan'),
            'url'        => 'peralatan',
            'permission' => 'peralatan',
            'icon'     => 'fa-solid fa-screwdriver-wrench',
            'position'   => 57,
            ]);

    if (has_permission('peralatan', '', 'view')) {
        $CI->app_menu->add_sidebar_menu_item('peralatan', [
                'slug'     => 'peralatan-tracking',
                'name'     => _l('peralatan'),
                'icon'     => 'fa-solid fa-screwdriver-wrench',
                'href'     => admin_url('peralatan'),
                'position' => 12,
        ]);
    }
}
function module_peralatan_action_links($actions)
{
    $actions[] = '<a href="' . admin_url('settings?group=peralatan') . '">' . _l('settings') . '</a>';

    return $actions;
}

function peralatan_clients_area_menu_items()
{
    // Show menu item only if client is logged in
    if (is_client_logged_in() && has_contact_permission('peralatan')) {
        add_theme_menu_item('peralatan', [
                    'name'     => _l('peralatan'),
                    'href'     => site_url('peralatan/list'),
                    'icon'     => 'fa-solid fa-screwdriver-wrench',
                    'position' => 15,
        ]);
    }
}

/**
 * [perfex_dark_theme_settings_tab net menu item in setup->settings]
 * @return void
 */
function peralatan_settings_tab()
{
    $CI = &get_instance();
    $CI->app_tabs->add_settings_tab('peralatan', [
        'name'     => _l('settings_group_peralatan'),
        //'view'     => module_views_path(PERALATAN_MODULE_NAME, 'admin/settings/includes/peralatan'),
        'view'     => 'peralatan/peralatan_settings',
        'position' => 51,
    ]);
}

$CI = &get_instance();
$CI->load->helper(PERALATAN_MODULE_NAME . '/peralatan');

if(($CI->uri->segment(1)=='admin' && $CI->uri->segment(2)=='peralatan') || $CI->uri->segment(1)=='peralatan'){
    $CI->app_css->add(PERALATAN_MODULE_NAME.'-css', base_url('modules/'.PERALATAN_MODULE_NAME.'/assets/css/'.PERALATAN_MODULE_NAME.'.css'));
    $CI->app_scripts->add(PERALATAN_MODULE_NAME.'-js', base_url('modules/'.PERALATAN_MODULE_NAME.'/assets/js/'.PERALATAN_MODULE_NAME.'.js'));
}
