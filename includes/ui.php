<?php 
namespace RespacioHouzezImport;
if(!defined('ABSPATH')) exit;

class Ui{
    public static function activate(){
        if(!function_exists('wp_get_current_user')) { include(ABSPATH . "wp-includes/pluggable.php"); }

        if (current_user_can('manage_options')) {
			add_action('admin_menu', array('RespacioHouzezImport\Ui', 'addMenu'));
            add_action('admin_enqueue_scripts', array('RespacioHouzezImport\Ui', 'addAssets'));
		}
    }

    public static function addMenu(){
        
    }
}