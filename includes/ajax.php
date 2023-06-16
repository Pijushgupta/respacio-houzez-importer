<?php
namespace RespacioHouzezImport;
if (!defined('ABSPATH')) exit; // Exit if accessed directly

use RespacioHouzezImport\export;
class ajax{
	public static function activate(){
		add_action('wp_ajax_exportAndDownload',array('RespacioHouzezImport\ajax','exportAndDownload'));
	}

	public static function exportAndDownload(){

		/**
		 * checking nonce
		 */
		if(!wp_verify_nonce($_POST['respacio_houzez_nonce'],'respacio_houzez_nonce'))  wp_die();
		/**
		 * checking if file type is set or not
		 * if not then die
		 */
		$fileType = isset($_POST['fileType']) ? sanitize_text_field($_POST['fileType']): '';
		if($fileType == '') wp_die();

		if($fileType == 1 || $fileType == '1') $fileType = 'XML';
		if($fileType == 2 || $fileType == '2') $fileType = 'XLS';

		$outPut = export::handleSubmit($fileType);

		echo json_encode($outPut);
		wp_die();
	}
}
