<?php 

namespace RespacioHouzezImport;

class remote{

    public static function fetch(
        string $url = '',
        array $body = [],
        array $headers = [
            'Content-Type' => 'application/x-www-form-urlencoded',
            'authorization'=> 'Basic YWRtaW46MTIzNA==',
        ]
    ){
        if($url == '') return false;
        if(empty($body)) return false;

        $response = wp_remote_post($url, ['body'=>$body,'headers'=>$headers]);

        if(is_wp_error($response) ) return $response;

        return wp_remote_retrieve_body($response);

    }
}