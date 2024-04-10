<?php 

namespace RespacioHouzezImport;
//this for user authentication between crm and plugin

use RespacioHouzezImport\option;
use RespacioHouzezImport\remote;

class account{

    public static function generateCode(string $email = ''){
        if($email == '') return false;

        $basePath = option::getCrmBasePath();
        $controller = option::getEmailControllerPath();

        $remoteAddress = 'https://' . $basePath . DIRECTORY_SEPARATOR . $controller[0] . DIRECTORY_SEPARATOR . $controller[1];

        $response = remote::post( $remoteAddress, ['email_address'=>$email]);
        if(is_wp_error($response)) return $response->get_error_message(); 

        $response = json_decode($response,true);

        if ($response && !empty($response['data'])) {
            // Get the OTP value from the data array
           $otp = $response['data']['otp'];
            
           //creating Transient to be verified after sing-up
           //using email as key to be more specific instead of generic name
           //3600 = 1 hr
           set_transient($email,$otp,3600 * 2);

           return 'success';
        }

    }

    //this to verify email verification code
    public static function verifyCode($code = false , $email = false){
        if($code == false || $email == false) return false;
        

        $transientValue = get_transient($email);
        if($transientValue == false) return false;

        if($transientValue == $code) return true;

        return false;
    }

    //to create account
    public static function createAccount(array $post = []){
        
        if(empty($post)) return false;

        $basePath = option::getCrmBasePath();
        $controllers = option::getCreateAccountPath();

        $remoteAddress = 'https://' . $basePath;

        foreach($controllers as $controller){
            $remoteAddress .= DIRECTORY_SEPARATOR . $controller;
        }

        //check if the otp valid or not 
        if(self::verifyCode($post['code'],$post['email_address']) == false) return 1;

        //unset the code field 
        unset($post['code']);

        //delete the transient value 
        delete_transient($post['email_address']);

        $post['uniq_code'] = option::getRespacioSignature();

        $response = remote::post( $remoteAddress, $post);
        if(is_wp_error($response)) return $response->get_error_message(); 

        $response = json_decode($response,true);

        return $response;
    }

    //login
    public static function login(array $post = []){
        if(empty($post)) return false;

        $basePath = option::getCrmBasePath();
        $controllers = option::getAccountLoginPath();

        $remoteAddress = 'https://' . $basePath;

        foreach($controllers as $controller){
            $remoteAddress .= DIRECTORY_SEPARATOR . $controller;
        }
        /**
         * sending email, password and site address to get api key
         */
        $response = remote::post($remoteAddress,$post);

        /**
         * converting the json to associated array
         */
        $response = json_decode($response,true);

        /**
         * returning status in case of failure
         */
        if($response['status'] != 'success') return false;

        /**
         * storing the api key to local variable
         */
        if($response['data']['api_key']) $apiKey = $response['data']['api_key'];

        /**
         * updating option table with api key
         */
        if(option::setApiKey($apiKey)) return true;

        /**
         * fallback
         */
        return false;
    }

   
}