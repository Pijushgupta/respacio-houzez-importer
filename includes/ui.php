<?php 
namespace RespacioHouzezImport;
if(!defined('ABSPATH')) exit;

class ui{
    public static function activate(){
        if(!function_exists('wp_get_current_user')) { include(ABSPATH . "wp-includes/pluggable.php"); }

        if (current_user_can('manage_options')) {
            /**
             * calling addMenu Method 
             */
			add_action('admin_menu', array('RespacioHouzezImport\Ui', 'addMenu'));

            /**
             * calling addAssets method.
             */
            add_action('admin_enqueue_scripts', array('RespacioHouzezImport\Ui', 'addAssets'));
		}
    }

    /**
     * Adding menu items
     *
     * @return void
     */
    public static function addMenu(){
        add_menu_page(__(RHIMO_PLUGIN_NAME), __(RHIMO_PLUGIN_NAME), 'manage_options', 'respacio_houzez_import', array('\RespacioHouzezImport\license','verify'), '', 6);
		add_submenu_page('respacio_houzez_import','Import','Import','manage_options', 'respacio_houzez_import', array('\RespacioHouzezImport\license','verify'));
		add_submenu_page('respacio_houzez_import','Export','Export','manage_options', 'respacio_houzez_export', array('\RespacioHouzezImport\export','init'));
    }

    /**
     * adding assets for plugin backend 
     *
     * @return void
     */
    public static function addAssets(){
        wp_enqueue_style( 'custom-style', plugins_url( '/css/style.css', __FILE__ ) );
		wp_enqueue_script('my-script', plugins_url('/js/my-script.js',__FILE__ ));
    }
}