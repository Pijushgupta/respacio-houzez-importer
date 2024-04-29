<?php

namespace RespacioHouzezImport;
use RespacioHouzezImport\remote;
use RespacioHouzezImport\option;
use RespacioHouzezImport\forms;


class gravity{

	public static $gravityForm = null;
	public static $gravityEntry = null;
	public static $formEntryMeta = null;

	public static function list(){
		if ( class_exists( 'GFAPI' ) ) {
		   // Get all Gravity Forms forms
		   $forms = \GFAPI::get_forms();

		   if($forms){
			   $formList = [];
			   foreach($forms as $form){
				   $formList[] = [
						'id'=>esc_html($form['id']),
						'title'=>esc_html($form['title']),
						'type' => pathinfo(basename(__FILE__), PATHINFO_FILENAME) // 'gravity.php' to 'gravity'
						];
			   }
				return $formList;
		   }
		   return false;
	   }
   }

	public static function getFormFields($formId){
		if ( class_exists( 'GFAPI' ) ) {
			// Get the Gravity Forms form object
			$form = \GFAPI::get_form( $formId );
			// Count the number of fields in the form
			return $form['fields'];
		}
	}

	public static function onSubmit(){
		/**
		 * has_action to prevent multiple execution since 
		 * action can be called multiple times in a life cycle 
		 * DON'T REMOVE
		 */
		if(!has_action('gform_after_submission','\\'.__CLASS__.'::beforeHandleData')){
			add_action( 'gform_after_submission','\\'.__CLASS__.'::beforeHandleData', 10, 2 );
		}
		
	}

	public static function beforeHandleData($entry,$form){
		$formId = $entry['form_id'];

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
		self::$gravityForm = $form;
		self::$gravityEntry = $entry;
		self::handleData();
	}

	public static function handleData(){
		
		
		$mother_entry_id = self::$gravityEntry['id']; // Assuming the mother entry ID is needed

		// Initialize an array to store concatenated values
		$concatenated_values = array();

		foreach ( self::$gravityForm['fields'] as $field ) {
			$inputs = $field->get_entry_inputs();
			if ( is_array( $inputs ) ) {
				foreach ( $inputs as $input ) {
					$field_label = $field->label;
					$field_value = rgar( self::$gravityEntry, (string) $input['id'] );

					// Concatenate values if the label already exists in the array
					if ( isset( $concatenated_values[ $field_label ] ) ) {
						$concatenated_values[ $field_label ] .= ' ' . $field_value;
					} else { // Otherwise, add the label and value as new entry
						$concatenated_values[ $field_label ] = $field_value;
					}
				}
			} else {
				$field_label = $field->label;
				$field_value = rgar( self::$gravityEntry, (string) $field->id );

				// Concatenate values if the label already exists in the array
				if ( isset( $concatenated_values[ $field_label ] ) ) {
					$concatenated_values[ $field_label ] .= ' ' . $field_value;
				} else { // Otherwise, add the label and value as new entry
					$concatenated_values[ $field_label ] = $field_value;
				}
			}
		}

		//trimming extra spaces 
		foreach($concatenated_values as &$value){
			$value = trim($value);
			$value = str_replace('  ',' ',$value);
		}
		
		$formFields = self::$formEntryMeta['form_fields'];
		$crmFields = self::$formEntryMeta['crm_fields'];

		$dataToCrm = [];
		foreach($formFields as $k => $v){
			$dataToCrm[$crmFields[$k]['parameter_key']] = $concatenated_values[$v['label']];	
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