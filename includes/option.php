<?php

namespace RespacioHouzezImport;

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
}