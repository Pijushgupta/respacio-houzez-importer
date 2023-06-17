<?php
namespace RespacioHouzezImport;
if (!defined('ABSPATH')) exit; // Exit if accessed directly

use RespacioHouzezImport\export;
use RespacioHouzezImport\license;


class ajax{
	public static function activate(){
		add_action('wp_ajax_exportAndDownload',array('RespacioHouzezImport\ajax','exportAndDownload'));
		add_action('wp_ajax_checkApiKey',array('RespacioHouzezImport\ajax','checkApiKey'));
		add_action('wp_ajax_isActivated',array('RespacioHouzezImport\ajax','isActivated'));
		add_action('wp_ajax_removeKey',array('RespacioHouzezImport\ajax','removeKey'));
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

	public static function checkApiKey(){
		/* checking nonce*/
		if(!wp_verify_nonce($_POST['respacio_houzez_nonce'],'respacio_houzez_nonce'))  wp_die();
		if(!array_key_exists('key',$_POST)) wp_die();
		$key = sanitize_text_field($_POST['key']);

		/** On valid API Key */
		if(license::testApiKey($key) == false) {
			delete_option( 'property_verification_api' );
			delete_option( 'verify_api' );
			echo json_encode(false);
			wp_die();
		}

		/** On invalid API key */
		if(license::testApiKey($key) == true){
			update_option('property_verification_api',$key,true);
			update_option('verify_api',true,true);
			update_option('sync_type',1,true);
			echo json_encode(true);
			wp_die();
		}
	}

	/**
	 * @return strin json/boolean
	 */
	public static function removeKey(){

		if(!wp_verify_nonce($_POST['respacio_houzez_nonce'],'respacio_houzez_nonce'))  wp_die();
		if(delete_option( 'property_verification_api' ) && delete_option( 'verify_api' )){
			echo json_encode(true);
		}else{
			echo json_encode(false);
		}
		wp_die();
	}

	/**
	 * @return string json encoded boolean value
	 */
	public static function isActivated(){
		/**
		 * checking the nonce
		 */
		if(!wp_verify_nonce($_POST['respacio_houzez_nonce'],'respacio_houzez_nonce'))  wp_die();
		/**
		 * the default of get_option is false in case of lac of existance of value
		 * if the value is false then it also return false.
		 * in case of positive value, it returns true.
		 */

		echo json_encode(boolval(get_option('verify_api')));
		wp_die();

	}
}
