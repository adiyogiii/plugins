<?php
/**
 * Fired during plugin deactivation 
 */
class Iexport_Deactivator{

    public static function deactivate(){
        delete_option( 'iexport_setting_name' ); 
    }
}