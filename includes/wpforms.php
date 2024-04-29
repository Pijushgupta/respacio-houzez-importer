<?php


namespace RespacioHouzezImport;
use RespacioHouzezImport\remote;
use RespacioHouzezImport\option;
use RespacioHouzezImport\forms;

class wpforms
{   
    public static $formEntryMeta = null;
    public static $wpfForm = null;
    public static $wpfData = null;

    public static function list()
    {
        if (class_exists( 'WPForms' ) ) {
            // Get all Forms
            $forms = WPForms()->form->get( null, 999 );

            if ($forms ) {
                $formList = [];
                foreach($forms as $form){
                    $formList[] = [
                        'id'=>esc_html($form->ID),
                        'title'=>esc_html($form->post_title),
                        'type'=>pathinfo(basename(__FILE__), PATHINFO_FILENAME) // 'wpforms.php' to 'wpforms'
                    ];
                }
                return $formList;
            }
            return false;
        }
    }

    public static function getFormFields($formId){

        if (class_exists( 'WPForms' ) ) {
            $form = WPForms()->form->get($formId);
            $decodedJson = json_decode($form->post_content);
            return $decodedJson->fields;
        }
    }

    public static function onSubmit(){
		/**
		 * has_action to prevent multiple execution since 
		 * action can be called multiple times in a life cycle 
		 * DON'T REMOVE
		 */
		if(!has_action('wpforms_process_complete','\\'.__CLASS__.'::beforeHandleData')){
			add_action( 'wpforms_process_complete','\\'.__CLASS__.'::beforeHandleData', 10, 4 );
		}
		
	}

    public static function beforeHandleData( $fields, $entry, $form_data, $entry_id  ){
        
        $formId = $form_data['id'];

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
        self::$wpfForm = $fields;
        self::$wpfData = $form_data;
        self::handleData();        
    }

    public static function handleData(){
        $formFields = self::$formEntryMeta['form_fields'];
        $crmFields = self::$formEntryMeta['crm_fields'];
        $wpfForm = self::$wpfForm;
        $dataToCrm = [];
        foreach( $formFields as $k => $field){
            $fieldId = $field['id'];
            foreach($wpfForm as $data){
                if($data['id'] == $fieldId){
                    $dataToCrm[$crmFields[$k]['parameter_key']] = $data['value'];
                }
            }
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