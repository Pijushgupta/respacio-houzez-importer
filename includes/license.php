<?php

namespace RespacioHouzezImport;

class license
{
    public static function verify(){

        /**
         * checking if new page loaded from a $_POST request or not
         * if not then the below code will exexute
         */
        if(empty($_POST)){
            $error = '';
            if(get_option( 'verify_api') !== false) $message = "Your license key is verified successfully. Your properties will start to import in batches.";

        } else if(array_key_exists('property_verification_api', $_POST) && $_POST["property_verification_api"] != ''){
            /**
             * This block to verify submitted API Key
             */
            $status = self::refreshLicense(sanitize_text_field($_POST["property_verification_api"]));

        }


    }

    /**
     * @param $licenseKey
     * @return array error or success message. response['error'] or response['error']
     */
    public static function refreshLicense($licenseKey){

    }

}