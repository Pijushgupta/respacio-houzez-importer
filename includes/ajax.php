<?php
namespace RespacioHouzezImport;
if (!defined('ABSPATH')) exit; // Exit if accessed directly

use RespacioHouzezImport\export;
class ajax{
	public static function activate(){
		add_action('wp_ajax_exportAndDownload',array('RespacioHouzezImport\ajax','exportAndDownload'));
	}

	public static function exportAndDownload(){

		
	}
}
