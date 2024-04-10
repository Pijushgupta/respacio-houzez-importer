<?php


namespace RespacioHouzezImport;

class wpforms
{
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
                        'type'=>'wpforms'
                    ];
                }
                return $formList;
            }
            return false;
        }
    }
}