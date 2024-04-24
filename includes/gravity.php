<?php

namespace RespacioHouzezImport;
use RespacioHouzezImport\remote;
use RespacioHouzezImport\option;

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
						'type' => 'gravity'
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
		add_action( 'gform_after_submission', function($entry, $form){

			$formId = $entry['form_id'];

			//get entry(posttype - respacio_forms) with meta key(form_id) - we have use fully qualified name since its not RespacioHouzezImport scope
			$formEntry = \RespacioHouzezImport\forms::getEntryByFromIdMeta($formId);
			if(empty($formEntry)) return false;

			$formEntryMeta = get_post_meta($formEntry[0]->ID,'form_field_map',true);
			//if mapping not present
			if($formEntryMeta == false || $formEntryMeta == '') return false;

			$formEntryMeta = unserialize($formEntryMeta);
			//if mapping not present
			if( !isset($formEntryMeta['form_fields']) || !isset($formEntryMeta['crm_fields']) ) return false;
				


			\RespacioHouzezImport\gravity::$formEntryMeta = $formEntryMeta;
			\RespacioHouzezImport\gravity::$gravityForm = $form;
			\RespacioHouzezImport\gravity::$gravityEntry = $entry;
			\RespacioHouzezImport\gravity::handleData();
			
		}, 10, 2 );
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
		var_dump($dataToCrm);
		echo "\n\r";
		$response = remote::post(
			'https://crm.respacio.com/ws/contacts/add_enquiry',
			$dataToCrm,
			['x-api-key'=>option::getApiKey()]
		);

		if(is_wp_error($response)) error_log($response->get_error_message()) ; 

       return true;
	}

}