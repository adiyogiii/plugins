<?php /**
 * The plugin bootstrap file
 * Generate plugin information in the plugin admin area ,includes all of the dependencies , register the activation and deactivation functions and defines a fnction that start the plugin
 * @wordpress-plugin 
 * Plugin Name: iexport
 * PLugin URI: localhost/graphql
 * Description: Import and Export posts from wordpress
 * Version: 1.0.0
 * Author: yogesh
*/
if(! defined('WPINC')){
    die();

}

/**
 * Currently plugin version
 * 
 */
define( 'IEXPORT_VERSION','1.0.0');

/**
 * The code that run during plugin activation
 */
function activate_iexport(){
    require_once plugin_dir_path(__FILE__).'includes/class-iexport-activator.php';
    
    Iexport_Activator::activate();
}

/**
 * The code that run during the plugin deactivation
 */
function deactivate_iexport(){
    require_once plugin_dir_path(__FILE__).'includes/class-iexport-deactivator.php';
    Iexport_Deactivator::deactivate();
}

register_activation_hook(__FILE__,'activate_iexport');



register_deactivation_hook(__FILE__,'deactivate_iexport');

require_once plugin_dir_path(__FILE__).'includes/class-iexport.php';
function run_iexport(){
    $plugin = new Iexport();
    $plugin->run();
}
run_iexport();




