<?php /** @noinspection PhpClassNamingConventionInspection */

namespace RespacioHouzezImport;
use RespacioHouzezImport\corn as corn;
use RespacioHouzezImport\post as raspost;

if (!defined('ABSPATH')) exit; // Exit if accessed directly

class ajax{
	/**
	 * Initializing all ajax methods
	 * @return void
	 */
	public static function activate() {
		add_action('wp_ajax_exportAndDownload', [ 'RespacioHouzezImport\ajax','exportAndDownload' ] );
		add_action('wp_ajax_checkApiKey', [ 'RespacioHouzezImport\ajax','checkApiKey' ] );
		add_action('wp_ajax_isActivated', [ 'RespacioHouzezImport\ajax','isActivated' ] );
		add_action('wp_ajax_removeKey', [ 'RespacioHouzezImport\ajax','removeKey' ] );
		add_action('wp_ajax_getApiKeyMasked', [ 'RespacioHouzezImport\ajax','getApiKeyMasked' ] );
		add_action('wp_ajax_ajaxSyncProperties', [ 'RespacioHouzezImport\ajax','ajaxSyncProperties' ] );
		add_action('wp_ajax_ajaxGetTotalNumberOfPropertyLog', [ 'RespacioHouzezImport\ajax','ajaxGetTotalNumberOfPropertyLog' ] );
		add_action('wp_ajax_ajaxGetPropertyLogs', [ 'RespacioHouzezImport\ajax','ajaxGetPropertyLogs' ] );
	}

	/** @noinspection PhpNoReturnAttributeCanBeAddedInspection
	 * @noinspection PhpMethodNamingConventionInspection
	 */
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

	/**
	 * checking api key and based on the api key sending true or false as json string
	 * @return void
	 *
	 */
	public static function checkApiKey(){
		/* checking nonce*/
		if(!wp_verify_nonce($_POST['respacio_houzez_nonce'],'respacio_houzez_nonce'))  wp_die();
		if(!array_key_exists('key',$_POST)) wp_die();
		$key = sanitize_text_field($_POST['key']);

		/** On valid API Key */
		if( ! license::testApiKey( $key ) ) {
			delete_option( 'property_verification_api' );
			delete_option( 'verify_api' );
			echo json_encode(false);
			wp_die();
		}

		/** On invalid API key */
		if( license::testApiKey( $key ) ){
			update_option('property_verification_api',$key,true);
			update_option('verify_api',true,true);
			update_option('sync_type',1,true);
			echo json_encode(true);
			wp_die();
		}
	}

	/**
	 * removeing api key
	 * @noinspection PhpNoReturnAttributeCanBeAddedInspection
	 */
	public static function removeKey() {

		if(!wp_verify_nonce($_POST['respacio_houzez_nonce'],'respacio_houzez_nonce'))  wp_die();
		if(delete_option( 'property_verification_api' ) && delete_option( 'verify_api' )){
			echo json_encode(true);
		}else{
			echo json_encode(false);
		}
		wp_die();
	}

	/**
	 * checking if plugin activated or not
	 * @noinspection PhpNoReturnAttributeCanBeAddedInspection
	 */
	public static function isActivated() {
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

	/**
	 * masking the API key and returning
	 * @noinspection PhpNoReturnAttributeCanBeAddedInspection
	 */
	public static function getApiKeyMasked() {
		/** checking the nonce*/
		if(!wp_verify_nonce($_POST['respacio_houzez_nonce'],'respacio_houzez_nonce'))  wp_die();
		$key = get_option('property_verification_api',false);

		/**
		 * in case no API key found in option table in database
		 */
		if($key === false){
			echo json_encode(false);
			wp_die();
		}

		$key_length = strlen($key);
		$masking_length = $key_length - 12;
		$maskedKey = substr($key,0,6). str_repeat( '*',$masking_length) . substr($key, -6);

		echo json_encode($maskedKey);
		wp_die();

	}

	public static function ajaxSyncProperties(){
		/** checking the nonce*/
		if(!wp_verify_nonce($_POST['respacio_houzez_nonce'],'respacio_houzez_nonce'))  wp_die();
		corn::respacio_sync_properties();

		echo json_encode(true);
		wp_die();
	}

	//This is just to return number of property log not the actual data
	public static function ajaxGetTotalNumberOfPropertyLog(){
		/** checking the nonce*/
		if(!wp_verify_nonce($_POST['respacio_houzez_nonce'],'respacio_houzez_nonce'))  wp_die();

		echo json_encode(raspost::getTotalNumberOfLog());
		wp_die();
	}

	//this to return property logs with data. also it can handle offset 
	public static function ajaxGetPropertyLogs(){
		/** checking the nonce*/
		if(!wp_verify_nonce($_POST['respacio_houzez_nonce'],'respacio_houzez_nonce'))  wp_die();
		// echo json_encode(array('a'=>'1'));
		// wp_die();
		$offset = 0;
		$numposts = 10;

		if(isset($_POST['offset'])) $offset = sanitize_text_field($_POST['offset']);
		if(isset($_POST['numposts'])) $numposts = sanitize_text_field($_POST['numposts']);
		
		
		echo json_encode(raspost::getPropertyLog($offset,$numposts));
		wp_die();
	}
}
