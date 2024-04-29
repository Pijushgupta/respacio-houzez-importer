<?php


namespace RespacioHouzezImport;
use RespacioHouzezImport\remote;
use RespacioHouzezImport\option;
use RespacioHouzezImport\forms;

class forminator
{
    public static function list()
    {
        if ( !class_exists( 'Forminator_API' ) ) return false;

        $forms = \Forminator_API::get_forms();
        if($forms){
            $formList = [];
            foreach($forms as $form){
                $formList[] = [
                    'id'=>esc_html($form->id),
                    'title'=>esc_html($form->name),
                    'type'=>pathinfo(basename(__FILE__), PATHINFO_FILENAME) // 'forminator.php' to 'forminator'
                ];
            }
            return $formList;
        }
        return false;
        
    }

    public static function getFormFields($formId){
        if(!class_exists('Forminator_API')) return false;

        $form = \Forminator_API::get_form( $formId );
        $fields = array();

        if ( ! is_wp_error( $form ) ) {
            $form_fields = $form->get_fields();
            
            foreach ( $form_fields as $field ) {
                // Add each field to the $fields array
                $fields[] = $field;
            }
            return $fields;
        }

        return false;

    }

    public static function onSubmit(){
        /**
		 * has_action to prevent multiple execution since 
		 * action can be called multiple times in a life cycle 
		 * DON'T REMOVE
		 */
		if(!has_action('forminator_custom_form_after_save_entry','\\'.__CLASS__.'::beforeHandleData')){
			add_action( 'forminator_custom_form_after_save_entry','\\'.__CLASS__.'::beforeHandleData');
		}
    }

    public static function beforeHandleData($entry, $form_id){
        error_log('Hook is working');
    }
}