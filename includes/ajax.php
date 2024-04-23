<?php /** @noinspection PhpClassNamingConventionInspection */

namespace RespacioHouzezImport;
use RespacioHouzezImport\corn as corn;
use RespacioHouzezImport\post as raspost;
use RespacioHouzezImport\option;
use RespacioHouzezImport\account;
use RespacioHouzezImport\cf7;
use RespacioHouzezImport\gravity;
use RespacioHouzezImport\forms;
use RespacioHouzezImport\common;

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
		add_action('wp_ajax_ajaxGetLogPerPageOption', [ 'RespacioHouzezImport\ajax','ajaxGetLogPerPageOption' ] );
		add_action('wp_ajax_ajaxSetLogPerPageOption', [ 'RespacioHouzezImport\ajax','ajaxSetLogPerPageOption' ] );

		add_action('wp_ajax_ajaxVerifyEmail',['RespacioHouzezImport\ajax','ajaxVerifyEmail']);
		add_action('wp_ajax_ajaxCreateAccount',['RespacioHouzezImport\ajax','ajaxCreateAccount']);
		add_action('wp_ajax_ajaxAccountLogin',['RespacioHouzezImport\ajax','ajaxAccountLogin']);
        add_action('wp_ajax_ajaxIsApiKeyPresent',['RespacioHouzezImport\ajax','ajaxIsApiKeyPresent']);
        add_action('wp_ajax_ajaxGetForms',['RespacioHouzezImport\ajax','ajaxGetForms']);

        add_action('wp_ajax_ajaxGetFormEntries',['RespacioHouzezImport\ajax','ajaxGetFormEntries']);
        add_action('wp_ajax_ajaxSaveEntry',['RespacioHouzezImport\ajax','ajaxSaveEntry']);
        add_action('wp_ajax_ajaxDeleteEntry',['RespacioHouzezImport\ajax','ajaxDeleteEntry']);
        add_action('wp_ajax_ajaxToggleEntryStatus',['RespacioHouzezImport\ajax','ajaxToggleEntryStatus']);
        add_action('wp_ajax_ajaxGetCrmFormFields',['RespacioHouzezImport\ajax','ajaxGetCrmFormFields']);
        add_action('wp_ajax_ajaxGetFormFields',['RespacioHouzezImport\ajax','ajaxGetFormFields']);
        add_action('wp_ajax_ajaxGetEntryFormFieldMap',['RespacioHouzezImport\ajax','ajaxGetEntryFormFieldMap']);
        add_action('wp_ajax_ajaxSetFormMapFields',['RespacioHouzezImport\ajax','ajaxSetFormMapFields']);
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

		/** On invalid API Key */
		if( ! license::testApiKey( $key ) ) {
			delete_option( 'property_verification_api' );
			delete_option( 'verify_api' );
			echo json_encode(false);
			wp_die();
		}

		/** On valid API key */
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

	public static function ajaxGetLogPerPageOption(){
        /** checking the nonce*/
		if(!wp_verify_nonce($_POST['respacio_houzez_nonce'],'respacio_houzez_nonce'))  wp_die();
		echo json_encode(option::getLogPerPageOption());
		wp_die();
    }

	public static function ajaxSetLogPerPageOption(){
        /** checking the nonce*/
		if(!wp_verify_nonce($_POST['respacio_houzez_nonce'],'respacio_houzez_nonce'))  wp_die();
		if(!isset($_POST['perpage'])) wp_die();
		
		//sanitizing and converting int if its a string 
		$perpage = intval(sanitize_text_field($_POST['perpage']));
		// echo json_encode($perpage);
		// wp_die();
		echo json_encode(option::setLogPerPageOption($perpage));
		wp_die();
    }


	public static function ajaxVerifyEmail(){

		if(!wp_verify_nonce($_POST['respacio_houzez_nonce'],'respacio_houzez_nonce'))  wp_die();
		if(!isset($_POST['email'])){
			echo json_encode(['msg'=>'no email address provided']); 
			wp_die();
		} 

		$email = sanitize_email($_POST['email']);
		echo json_encode(account::generateCode($email));
		wp_die();
	}

	public static function ajaxCreateAccount(){
		
		
		if(!wp_verify_nonce($_POST['respacio_houzez_nonce'],'respacio_houzez_nonce')){
			echo json_encode(['Invalid nonce']); 
			wp_die();
		} 
		
		//fields to check
		$fields = ['name','surname','email_address','website','mobile','no_of_user','password','code'];
		
		//missing indicator
		$missingField = false;

		//store sanitize data
		$post = [];

		

		foreach($fields as $field){
			//checking for missing fields
			if(!isset($_POST[$field])){		
				$missingField = $field;
				break;
			}

			//sanitizing and storing $post - text field
			if(
				$field == 'name' || 
				$field == 'surname' || 
				$field == 'mobile' || 
				$field == 'no_of_user' ||
				$field == 'code'
			) {
				$post[$field] = sanitize_text_field($_POST[$field]);
			} 
			
			//sanitizing email
			if($field == 'email_address') $post[$field] = sanitize_email($_POST[$field]);

			//sanitizing url
			if($field == 'website') $post[$field] = esc_url_raw($_POST[$field]);

			//not sanitizing, just storing the password
			if($field == 'password') $post[$field] = $_POST[$field]; 
		}

		

		if($missingField != false){
			echo json_encode(['msg'=>$missingField . 'missing']); 
			wp_die();
		} 


		$response = account::createAccount($post);
		echo json_encode($response);
		
		wp_die();

	}

    /**
     * for user login - ajax method
     * @return void
     */
	public static function ajaxAccountLogin(){
		if(!wp_verify_nonce($_POST['respacio_houzez_nonce'],'respacio_houzez_nonce')){
			echo json_encode(['Invalid nonce']); 
			wp_die();
		} 

		//fields to check
		$fields = ['email_address','password','website'];
		//missing indicator
		$missingField = false;
		//store sanitize data
		$post = [];

		foreach($fields as $field){
			if(!isset($_POST[$field])){		
				$missingField = $field;
				break;
			}

			//sanitizing email
			if($field == 'email_address') $post[$field] = sanitize_email($_POST[$field]);

			//sanitizing url
			if($field == 'website') $post[$field] = esc_url_raw($_POST[$field]);

			//not sanitizing, just storing the password
			if($field == 'password') $post[$field] = $_POST[$field]; 
		}

		if($missingField != false){
			echo json_encode(['msg'=>$missingField . 'is missing']); 
			wp_die();
		}

		$response = account::login($post);
		echo json_encode($response);
		
		wp_die();
	}

    /**
     * to check if user having api key or not
     * @return void
     */
    public static function ajaxIsApiKeyPresent(){
        if(!wp_verify_nonce($_POST['respacio_houzez_nonce'],'respacio_houzez_nonce')){
            echo json_encode(['Invalid nonce']);
            wp_die();
        }

        $keyStatus = option::getApiKey();
        if(!$keyStatus){
            echo json_encode(false);
        }else{
            echo json_encode(true);
        }

        wp_die();
    }

    public static function ajaxGetForms(){
        if(!wp_verify_nonce($_POST['respacio_houzez_nonce'],'respacio_houzez_nonce')){
            echo json_encode(['Invalid nonce']);
            wp_die();
        }

        echo json_encode(forms::getAllForms([
            'cf7'=>'Contact 7',
            'gravity'=>'Gravity Forms',
            'forminator' => 'Forminator',
            'wpforms' => 'WPForms'
            ]));
        wp_die();
    }

    public static function ajaxGetFormEntries(){
        if(!wp_verify_nonce($_POST['respacio_houzez_nonce'],'respacio_houzez_nonce')){
            echo json_encode(['Invalid nonce']);
            wp_die();
        }

        echo json_encode(forms::getAllEntries());
        wp_die();
    }

    /**
     * @return false
     */
    public static function ajaxSaveEntry(){
        if(!wp_verify_nonce($_POST['respacio_houzez_nonce'],'respacio_houzez_nonce')){
            echo json_encode(['Invalid nonce']);
            wp_die();
        }

        if(
            !isset($_POST['form_type']) ||
            !isset($_POST['form_id']) ||
            !isset($_POST['form_title']) ||
            !isset($_POST['form_type_name'])
        ){
            return false;
        }

        $post['form_type'] = sanitize_text_field($_POST['form_type']);
        if($post['form_type'] == '') return false;
        $post['form_id'] = sanitize_text_field($_POST['form_id']);
        if($post['form_id'] == '') return false;
        $post['form_title'] = sanitize_text_field($_POST['form_title']);
        if($post['form_title'] == '') return false;
        $post['form_type_name'] = sanitize_text_field($_POST['form_type_name']);
        if($post['form_type_name'] == '') return false;

        //adding extra field
        $post['form_active'] = true;

        echo json_encode(forms::saveEntries($post));
        wp_die();
    }

    /**
     * function to remove entries
     * @return false|void
     */
    public static function ajaxDeleteEntry(){
        if(!wp_verify_nonce($_POST['respacio_houzez_nonce'],'respacio_houzez_nonce')){
            echo json_encode(['Invalid nonce']);
            wp_die();
        }

        if(!isset($_POST['id'])) return false;
        $id = sanitize_text_field($_POST['id']);

        wp_delete_post($id,true);
        echo json_encode(true);
        wp_die();
    }

    public static function ajaxToggleEntryStatus(){
        if(!wp_verify_nonce($_POST['respacio_houzez_nonce'],'respacio_houzez_nonce')){
            echo json_encode(['Invalid nonce']);
            wp_die();
        }
        if(!isset($_POST['id'])) return false;
        $id = sanitize_text_field($_POST['id']);

        echo json_encode(forms::toggleActiveStatus($id));
        wp_die();
    }

    public static function ajaxGetCrmFormFields(){
        if(!wp_verify_nonce($_POST['respacio_houzez_nonce'],'respacio_houzez_nonce')){
            echo json_encode(['Invalid nonce']);
            wp_die();
        }
        echo json_encode(option::getCrmFromFields());
        wp_die();
    }

    public static function ajaxGetFormFields(){
        if(!wp_verify_nonce($_POST['respacio_houzez_nonce'],'respacio_houzez_nonce')){
            echo json_encode(['Invalid nonce']);
            wp_die();
        }
        if(!isset($_POST['postId'])) return false;
        $postId = sanitize_text_field($_POST['postId']);
        echo json_encode(forms::getFormFields($postId));
        wp_die();
    }

	public static function ajaxGetEntryFormFieldMap(){
		if(!wp_verify_nonce($_POST['respacio_houzez_nonce'],'respacio_houzez_nonce')){
            echo json_encode(['Invalid nonce']);
            wp_die();
        }

		if(!isset($_POST['postId'])) return false;
		$postId = sanitize_text_field( $_POST['postId'] );
		echo json_encode(forms::getEntryFormFieldMap($postId));
		wp_die();
	}


	public static function ajaxSetFormMapFields(){
		if(!wp_verify_nonce($_POST['respacio_houzez_nonce'],'respacio_houzez_nonce')){
            echo json_encode(['Invalid nonce']);
            wp_die();
        }
		/**
		 * terminate in case one of the below field missing
		 */
		if(!isset($_POST['postId']) || !isset($_POST['form_fields']) || !isset($_POST['crm_fields'])) return false;
		
		$postId = sanitize_text_field($_POST['postId']);
		$formFields = common::sanitize_text_field_array(common::jsonToArray($_POST['form_fields']));
		$crmFields = common::sanitize_text_field_array(common::jsonToArray($_POST['crm_fields']));

		echo json_encode(forms::setEntryFormFieldMap($postId,$formFields,$crmFields));
		wp_die();
	}
}
