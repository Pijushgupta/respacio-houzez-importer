<?php

namespace RespacioHouzezImport;

class gravity{
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
}