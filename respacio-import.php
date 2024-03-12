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
$autoloadFile = __DIR__ . DIRECTORY_SEPARATOR . 'vendor' . DIRECTORY_SEPARATOR . 'autoload.php';
if (!file_exists($autoloadFile)) return;
require_once $autoloadFile;

use RespacioHouzezImport\ui;
use RespacioHouzezImport\ajax;
use RespacioHouzezImport\corn;
use RespacioHouzezImport\image;
use RespacioHouzezImport\cpt;

/**
 * making sure to execute the plugin after `plugin_loaded` hook
 */
add_action('plugin_loaded', function () {

	/**
	 * always define your constant as early as possible
	 * so from a single point we can control dynamic variable data
	 */
	if (!defined('RHIMO_PLUGIN_NAME')) define( 'RHIMO_PLUGIN_NAME', 'Respacio Houzez Import' );
	if (!defined('RHIMO_THEME_TYPE')) define( 'RHIMO_THEME_TYPE', '1' );
	if (!defined('RHIMO_API_BASE_URL')) define('RHIMO_API_BASE_URL', 'https://crm.respacio.com/ws/properties' );
	if (!defined('RHIMO_FEED_URL')) define('RHIMO_FEED_URL', 'https://crm.respacio.com/ws/properties/sync_properties_json' );
	if (!defined('RHIMO_PROPERTY_WEB_URL')) define('RHIMO_PROPERTY_WEB_URL', 'https://crm.respacio.com/ws/properties/sync_property_web_url' );
	if (!defined('MODE_OLD')) define('MODE_OLD', false);

	ui::activate();
	ajax::activate();
	image::activate();
	corn::activate();
	cpt::configure();

	include('includes/admin-page.php');
});


/**
 * removing keys
 */
register_uninstall_hook(__FILE__, [ 'RespacioHouzezImport\license','removeExistingKey' ] );