<?php

namespace RespacioHouzezImport;
use RespacioHouzezImport\remote;
use RespacioHouzezImport\option;
use RespacioHouzezImport\forms;

class cf7{

    public static $cf7Form = null;
	public static $formEntryMeta = null;

    public static function list(){
        //checking if contact 7 form present or not
        if(class_exists('WPCF7_ContactForm')){
            // Get all Contact Form 7 forms
            $forms = \WPCF7_ContactForm::find();

            if($forms){
                $formList = [];
                foreach($forms as $form){
                    $formList[] = [
                        'id'=>esc_html($form->id()),
                        'title'=>esc_html($form->title()),
                        'type'=> pathinfo(basename(__FILE__), PATHINFO_FILENAME) // 'cf7.php' to 'cf7'
                        ];
                }
                return $formList;
            }
            return false;
        }
    }

    public static function getFormFields($formId){

        if(!function_exists('wpcf7_contact_form')) return false;
        
        $form = wpcf7_contact_form( $formId );
        if ( $form ) {
            // Get form properties
            $form_properties = $form->get_properties();

            // Get form tags (fields)
            return $form->scan_form_tags();

        }
        return false;
    }

    public static function onSubmit(){
        /**
		 * has_action to prevent multiple execution since 
		 * action can be called multiple times in a life cycle
		 * DON'T REMOVE
		 */
        if(!has_action('wpcf7_mail_sent','\\'.__CLASS__.'::beforeHandleData')){
            add_action('wpcf7_mail_sent','\\'.__CLASS__.'::beforeHandleData', 10, 1 );
        }
    }
    public static function beforeHandleData($contact_form){

        $formId = $contact_form->id();

        //checking if entry exists or not 
        $formEntry = forms::getEntryByFromIdMeta($formId);
        if(empty($formEntry)) return false;

        //Checking if toggle button is on or off 
		$isActive = esc_html(get_post_meta($formEntry[0]->ID,'form_active',true));
		if($isActive != true) return false;

        //getting form and crm fields map
        $formEntryMeta = forms::getEntryFormFieldMap($formEntry[0]->ID);
		
		//if mapping not present
		if($formEntryMeta == false) return false;

		//if mapping not present
		if( !isset($formEntryMeta['form_fields']) || !isset($formEntryMeta['crm_fields']) ) return false;

        self::$formEntryMeta = $formEntryMeta;
		self::$cf7Form = $contact_form;
		self::handleData();
    }


    public static function handleData(){
        // Get the submission object
        $submission = \WPCF7_Submission::get_instance();
        $formEntry = $submission->get_posted_data();

        $formFields = self::$formEntryMeta['form_fields'];
		$crmFields = self::$formEntryMeta['crm_fields'];
        

        $dataToCrm = [];
        
        foreach($formFields as $k => $v){
			$dataToCrm[$crmFields[$k]['parameter_key']] = $formEntry[$v['name']];	
		}

        $response = remote::post(
			'https://crm.respacio.com/ws/contacts/add_enquiry',
			$dataToCrm,
			['x-api-key'=>option::getApiKey()]
		);

        if(is_wp_error($response)) error_log($response->get_error_message()) ; 

        return true;
    }

}