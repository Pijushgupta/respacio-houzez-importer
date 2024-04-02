<?php

namespace RespacioHouzezImport;
class forms{

    public static function removeActive($forms = false){

        if(!$forms) return false;

        $args = [
            'post_type' => 'respacio_forms',
            'posts_per_page' => -1,
            'status' => 'published'
        ];

        $query = new \WP_Query($args);

        $newForms = [];

        if($query->have_posts()){
            while($query->have_posts()) {
                $query->the_post();
                /**
                 * get post meta with key form_id
                 */
                $formID = get_post_meta(get_the_ID(),'form_id',true);
                foreach($forms as $form){
                    if($formID != $form['id']){
                        $newForms[] = $form;
                    }
                }

            }
            return $newForms;
        }
        return $forms;
    }

    public static function getAll(){
        $args = [
            'post_type' => 'respacio_forms',
            'posts_per_page' => -1,
            'status' => 'published'
        ];
        $query = new \WP_Query($args);
        $posts = [];
        if($query->have_posts()){
            while($query->have_posts()){
                $query->the_post();

                $id = get_the_ID();

                $posts[] = [
                    'title'=>esc_html(get_the_title()),
                    'id'=>esc_html($id),
                    'form_type'=>esc_html(get_post_meta($id,'form_type',true)),
                    'form_id'=>esc_html(get_post_meta($id,'form_id',true))
                ];
            }
            return $posts;
        }
        return false;

    }

}