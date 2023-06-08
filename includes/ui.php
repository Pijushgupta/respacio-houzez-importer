<?php 
namespace RespacioHouzezImport;
if(!defined('ABSPATH')) exit;

class Ui{
    public static function activate(){
        if(!function_exists('wp_get_current_user')) { include(ABSPATH . "wp-includes/pluggable.php"); }

        if (current_user_can('manage_options')) {
            /**
             * calling addMenu Method 
             */
			add_action('admin_menu', array('RespacioHouzezImport\Ui', 'addMenu'));
            add_action('admin_enqueue_scripts', array('RespacioHouzezImport\Ui', 'addAssets'));
		}
    }

    /**
     * Adding menu items
     *
     * @return void
     */
    public static function addMenu(){
        add_menu_page(__(RHIMO_PLUGIN_NAME), __(RHIMO_PLUGIN_NAME), 'manage_options', 'respacio_houzez_import', 'respacio_import', '', 6);
		add_submenu_page('respacio_houzez_import','Import','Import','manage_options', 'respacio_houzez_import', 'respacio_import');
		add_submenu_page('respacio_houzez_import','Export','Export','manage_options', 'respacio_houzez_export', 'respacio_export');
    }
}