<?php


namespace RespacioHouzezImport;

class forminator
{
    public static function list()
    {
        if ( class_exists( 'Forminator_API' ) ) {
            $forms = \Forminator_API::get_forms();
            if($forms){
                $formList = [];
                foreach($forms as $form){
                    $formList[] = [
                        'id'=>esc_html($form->id),
                        'title'=>esc_html($form->name),
                        'type'=>'forminator'
                    ];
                }
                return $formList;
            }
            return false;
        }
    }
}