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
            $status = self::checkNewLicense(sanitize_text_field($_POST["property_verification_api"]));

            if(array_key_exists('message',$status)) $message = $status['message'];
            if(array_key_exists('error',$status)) $error = $status['error'];

        } else if( array_key_exists('remove_licence_key',$_POST) && $_POST["remove_licence_key"] != ''){
            /**
             * starting license key removal process
             */
            $status = self::removeExistingKey();
            if(array_key_exists('message',$status)) $message = $status['message'];

        } else if(array_key_exists('save_changes',$_POST) && $_POST["save_changes"] != ''){
            /**
             * save changes
             */
            delete_option('sync_type');
            add_option('sync_type',sanitize_text_field($_POST["sync_type"]),'','yes');
        }
        $template_path = plugin_dir_path( __FILE__ ) . "template/api-varification.php";
        require_once ($template_path);

    }

    /**
     * @param $licenseKey
     * @return array error or success message. response['error'] or response['error']
     */
    public static function checkNewLicense($licenseKey = null){
        if($licenseKey == null) return;

        /**
         * Test firing Http request to check if it is successful with the provided API key
         */
        $sampleDataFromCrm = wp_remote_post(RHIMO_API_BASE_URL, array(
            'method'      => 'POST',
            'timeout'     => 45,
            'redirection' => 5,
            'httpversion' => '1.0',
            'blocking'    => true,
            'headers'     => array(
                "authorization"=> "Basic YWRtaW46MTIzNA==",
                "x-api-key"=>$licenseKey,
                "Content-Type"=>"application/x-www-form-urlencoded"
            ),
            'cookies' => array()
        ));

        $sampleDataFromCrm = json_decode($sampleDataFromCrm['body'],true);
        /**
         * http fetching is done
         */

        /**
         * checking for error first,
         * for cleaner approach
         */
        if(!array_key_exists('status',$sampleDataFromCrm) || $sampleDataFromCrm["status"] != "success"){
            delete_option( 'property_verification_api' );
            delete_option( 'verify_api' );
            return $status['error'] = 'Your license key is not valid, please check and try again.';
        }

        /**
         * if the key was okay and request/response was successfull
         * handle the new license key properly
         */
        if(array_key_exists('status',$sampleDataFromCrm) && $sampleDataFromCrm["status"] == "success"){
            delete_option( 'property_verification_api' );
            delete_option( 'verify_api' );
            add_option('property_verification_api',$licenseKey,'','yes');
            add_option('verify_api',true,'','yes');
            delete_option('sync_type');
            add_option('sync_type',1,'','yes');
            return $status['message'] = "Your license key is verified successfully. Your properties will start to import in batches.";
        }

    }

    public static function removeExistingKey(){
        delete_option( 'property_verification_api' );
        delete_option( 'verify_api' );
        $status['message'] = "Your license key is removed successfully.";
        return $status;
    }

}