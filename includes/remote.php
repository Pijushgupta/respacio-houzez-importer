<?php 

namespace RespacioHouzezImport;

class remote{

    public static function post(
        string $url = '',
        array $body = [],
        array $headers = []
    ){
        if($url == '') return false;
        if(empty($body)) return false;

        /**
         * default header
         */
        $defaults = [
            'Content-Type' => 'application/x-www-form-urlencoded',
            'authorization'=> 'Basic YWRtaW46MTIzNA==',
        ];

        $headers = wp_parse_args($headers, $defaults); //merging user defined header with default header
        $response = wp_remote_post($url, ['body'=>$body,'headers'=>$headers]);
        if(is_wp_error($response) ) return $response;
        return wp_remote_retrieve_body($response);
    }

    public static function get(
        string $url = '',
        array $headers = []
    ){
        if($url == '') return false;
        $defaults = [
            'Content-Type' => 'application/x-www-form-urlencoded',
            'authorization'=> 'Basic YWRtaW46MTIzNA==',
        ];
        $headers = wp_parse_args($headers, $defaults);
        $response = wp_remote_get($url, ['headers'=>$headers]);
        if(is_wp_error($response) ) return $response;
        return wp_remote_retrieve_body($response);
    }
}