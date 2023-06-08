<?php
/*
Plugin Name: Respacio Houzez Import
Plugin URI: respacio.com
Description: Import properties from the Respacio CRM.
Version: 1.0
Author: Respacio
Author URI: https://respacio.com
*/

/**
 * loading composer autoload first
 */
$autoloadFile = __DIR__ . DIRECTORY_SEPARATOR. 'vendor'. DIRECTORY_SEPARATOR .'autoload.php';
if(file_exists($autoloadFile)) require_once $autoloadFile;

$mfwp_prefix = 'mfwp_';
$mfwp_plugin_name = 'Respacio Houzez Import';



include('includes/admin-page.php');