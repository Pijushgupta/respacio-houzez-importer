<?php

namespace RespacioHouzezImport;

class cf7{

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
                        'type'=>'cf7'
                        ];
                }
                return $formList;
            }
            return false;
        }
    }
}