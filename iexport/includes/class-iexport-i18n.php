<?php
/**
 * Define the internationalization functionality
 */
class Iexport_i18n {
public function load_plugin_textdomain(){
    load_plugin_textdomain(
     'iexport',false,dirname( dirname(plugin_basename(__FILE__))).'/languages/');
    }
}