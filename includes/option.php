<?php

namespace RespacioHouzezImport;
use RespacioHouzezImport\remote;
class option{
    //this to get option value of log per page
    public static function getLogPerPageOption(){
        if(get_option( 'RespacioHouzezImportLogPerPage', false) == false){
            self::setLogPerPageOption(10);
        }
        return get_option( 'RespacioHouzezImportLogPerPage', false);
    }

    //this to set option value of log per page
    public static function setLogPerPageOption($value){
        
        return update_option( 'RespacioHouzezImportLogPerPage', $value);
    }
    
    //This to return crm base path
    public static function getCrmBasePath(){
        //checking if the option value empty or not 
        $optionValue = get_option('RespacioHouzezImportCrmBasePath',false);
        //if not empty return it
        if($optionValue != false) {
            return $optionValue;
        }
        //set it
        self::setCrmBasePath();

        //returning the option value 
        return get_option('RespacioHouzezImportCrmBasePath',false);
    }

    //This to set crm base path
    public static function setCrmBasePath( $path = false){
        //if no path provided use default path
        if($path == false) $path = 'crm.respacio.com';

        //sanitize before insertion 
        $path = sanitize_text_field($path);

        //inserting to the option table 
        //returning bool
        return update_option('RespacioHouzezImportCrmBasePath',$path);
    }

    //getting email controller path
    public static function getEmailControllerPath(){
        $optionValue = get_option('RespacioHouzezImportEmailControllerPath',false);
        if($optionValue != false){
            return $optionValue;
        }
        //setting it if not found
        self::setEmailControllerPath();

        //no need to unserialize 
        return get_option('RespacioHouzezImportEmailControllerPath',false);

    }

    //setting email controller path
    public static function setEmailControllerPath(array $paths = []){
        
        //checking if provided $paths are empty or not 
        if(empty($paths)) {
            $paths = array(
                'website_control',
                'send_verification_code'
            );
        }
        //sanitizing 
        foreach($paths as &$path){
            $path = sanitize_text_field($path);
        }
        //no need to be serialized, it will do by itself
        return update_option('RespacioHouzezImportEmailControllerPath',$paths);
    }


    public static function getCreateAccountPath(){
        $optionValue = get_option('RespacioHouzezImportCreateAccountPath',false);
        if($optionValue != false){
            return $optionValue;
        }
        //setting it if not found
        self::setCreateAccountPath();
        //no need to unserialize 
        return get_option('RespacioHouzezImportCreateAccountPath',false);

    }


    public static function setCreateAccountPath(array $paths = []){
        //checking if provided $paths are empty or not 
        if(empty($paths)) {
            $paths = array(
                'website_control',
                'user_signup'
            );
        }
        //sanitizing 
        foreach($paths as &$path){
            $path = sanitize_text_field($path);
        }
        //no need to be serialized, it will do by itself
        return update_option('RespacioHouzezImportCreateAccountPath',$paths);
    }

    public static function getRespacioSignature(){
        $optionValue = get_option('RespacioHouzezImportSignature',false);
        if($optionValue != false){
            return $optionValue;
        }
        //setting it if not found
        self::setRespacioSignature();
        return get_option('RespacioHouzezImportSignature',false);
    }

    public static function setRespacioSignature(){
        //generating
        $random  = wp_generate_password(32, false);
        return update_option('RespacioHouzezImportSignature',$random);
    }


    public static function getAccountLoginPath(){
        $optionValue = get_option('RespacioHouzezImportAccountLoginPath',false);
        if($optionValue != false){
            return $optionValue;
        }
        //setting it if not found
        self::setAccountLoginPath();

        //no need to unserialize 
        return get_option('RespacioHouzezImportAccountLoginPath',false);
    }

    public static function setAccountLoginPath(){
        //checking if provided $paths are empty or not 
        if(empty($paths)) {
            $paths = array(
                'website_control',
                'login'
            );
        }
        //sanitizing 
        foreach($paths as &$path){
            $path = sanitize_text_field($path);
        }
        //no need to be serialized, it will do by itself
        return update_option('RespacioHouzezImportAccountLoginPath',$paths);
    }

    /**
     * getting api key
     * @param void
     * @return false|mixed|null
     */
    public static function getApiKey(){
        return get_option('property_verification_api',false);
    }

    /**
     * setting new api key
     * @param string $key
     * @return bool
     */
    public static function setApiKey(string $key=''){
        if($key == '') return false;
        update_option('verify_api',true,true);
        return update_option('property_verification_api',$key);
    }

    /**
     * this to sync the form fields with crm. which include custom and default
     * @param void
     * @return boolean
     */
    public static function syncCrmFormFields(){
        $default = [
            ['name'=>'First Name', 'parameter_key' => 'name'],
            ['name'=>'Last Name', 'parameter_key' => 'surname'],
            ['name'=>'Email','parameter_key' => 'email_address'],
            ['name'=>'Phone','parameter_key' => 'phone_number'],
            ['name'=>'Notes','parameter_key' => 'notes'],
            ['name'=>'Url','parameter_key' => 'cla_detecturl'],
            ['name'=>'Prop ref number', 'parameter_key' => 'property_reference_number'],
            ['name'=>'Address line', 'parameter_key' => 'address'],
            ['name'=>'City', 'parameter_key' => 'cla_city']
        ];
        $customFields = remote::get(
            'https://crm.respacio.com/ws/properties/getcustomfield',
            ['x-api-key' => self::getApiKey()]
        );

        if(is_wp_error($customFields)) return $customFields->get_error_message();
        $customFields = json_decode($customFields,true);
        $customFields = $customFields['data']['customfield'];
        //removing id
        $newCustomFields = [];
        foreach($customFields as $customField){
            $newCustomFields[] = ['name' => $customField['name'],'parameter_key' => $customField['parameter_key']];
        }
        $newCustomFields = array_merge($default,$newCustomFields);

        return update_option('respacio_houzez_custom_fields',$newCustomFields);
    }

    public static function getCrmFromFields(){
        $value = get_option('respacio_houzez_custom_fields',false);
        if($value){
            return $value;
        }
        self::syncCrmFormFields();
        return get_option('respacio_houzez_custom_fields',false);
    }
}