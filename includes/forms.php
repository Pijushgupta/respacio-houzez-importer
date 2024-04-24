<?php

namespace RespacioHouzezImport;
class forms{

    public static function removeActive($forms = false){

        if(!$forms) return false;

        $newForms = $forms;

        $allEntries = self::getAllEntries();
        if(!empty($allEntries)){
            foreach($allEntries as $entry){
                foreach ($forms as $k => $form){
                    if($entry['form_id'] == $form['id'] && $entry['form_type'] == $form['type']){
                        //continue;
                        unset($newForms[$k]);
                    }
                    //$newForms[] = $form;
                }
            }
            //it's needed to re-index the array,
            // otherwise based on index number json_encode will convert the array into js object
            return array_values($newForms);
        }

        return $forms;

    }

    /**
     * returns entry based post meta with the key form_id
     */
    public static function getEntryByFromIdMeta($id = false){
        if($id == false) return;
        $arr = [
            'post_type' => 'respacio_forms',
            'posts_per_page' => 1,
            'meta_query' => array(
                array(
                    'key' => 'form_id',
                    'value' => $id,
                    'compare' => '='
                )
            )
        ];

        return get_posts($arr);
    }

    public static function getAllEntries(){
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
                    'form_type_name'=>esc_html(get_post_meta($id,'form_type_name',true)),
                    'form_id'=>esc_html(get_post_meta($id,'form_id',true)),
                    'form_title'=> esc_html(get_post_meta($id,'form_title',true)),
                    'form_active' => boolval(esc_html(get_post_meta($id,'form_active',true)))
                ];
            }
            return $posts;
        }
        return false;
    }

    /**
     * @param $types
     * @return array|false
     * get all forms based on class names, removing existing entries.
     */
    public static function getAllForms($types = []){
        if(empty($types)) return false;
        $allForms = [];
        foreach($types as $k => $type){
            $forms = self::getForms($k);
            if($forms){
                $forms = self::removeActive($forms);
                if($forms){
                    $allForms[] = [
                        'type'=>$k,
                        'name'=>$type,
                        'data'=>$forms
                    ];
                }
            }
        }
        if(!empty($allForms)) return $allForms;
        return false;
    }

    /**
     * @param $className
     * @return false|mixed
     * get froms based on class name
     */
    public static function getForms($className = false){
        if($className == false) return false;
        $fullClassName = 'RespacioHouzezImport\\'.$className;
        if(!class_exists($fullClassName)) return false;
        $forms = $fullClassName::list();
        return $forms;
    }

    /**
     * @param $post
     * @return bool
     */
    public static function saveEntries($post = []){
        if(empty($post)) return false;
        $postId = wp_insert_post([
            'post_type' => 'respacio_forms',
            'post_status' => 'publish',
            'post_title' => $post['form_type_name'].' - '.$post['form_id']
        ],true);

        if(is_wp_error($postId)) return $postId->get_error_message();

        foreach($post as $key => $field){
            add_post_meta($postId,$key,$field);
        }
        return true;
        
    }

    /**
     * @param int $postId
     * @return boolean|void
     * This to change status of a form entry to active and inactive
     */
    public static function toggleActiveStatus(int $postId = 0){
        if($postId == 0) return false;
        $form_active = boolval(get_post_meta($postId,'form_active',true));
        if($form_active == false){
            return update_post_meta($postId,'form_active',true);
        }else{
            return update_post_meta($postId,'form_active',false);
        }
    }

    //for drag n drop area
    public static function getEntryFormFieldMap($id = false){
        if(!$id) return false;
        $value = get_post_meta($id,'form_field_map',true);
        if(!empty($value)){
            return unserialize($value);
        }

        return false;
    }

    public static function setEntryFormFieldMap($id = false, $formFields = [], $crmFields = []){
        if($id == false || empty($formFields) || empty($crmFields)) return false;
        $meta = array(
			'form_fields' => $formFields,
			'crm_fields'  => $crmFields
        );
        return update_post_meta($id,'form_field_map',serialize($meta));
    }

    //for left side search area
    public static function getFormFields($id = false){
        if(!$id) return false;
        //formType is similar to the class name
        $formType = esc_html(get_post_meta($id,'form_type',true));
        $formId = esc_html(get_post_meta($id,'form_id',true));
        if(empty($formType) || empty($formId)) return false;

        $fullClassName = 'RespacioHouzezImport\\'.$formType;
        if(!class_exists($fullClassName)) return false;

        return $fullClassName::getFormFields($formId);
    }

    
}