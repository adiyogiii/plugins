<?php
/**
 * The file that defines the core plugin class that include attributes and function across plugin
 */

 class Iexport {
     /**
      * The loader that's responsible for maintaining and registering all the hooks that power the plugin
      
      */

      protected $loader;

      protected $plugin_name;

      protected $version;

      public function __construct(){
            $this->plugin_name = 'iexportPlugin';
              $this->version = '1.0.0';
          
          $this->load_dependencies();
          $this->set_locale();
          $this->define_admin_hooks();
      }

private function load_dependencies(){
    require_once plugin_dir_path(dirname(__FILE__)).'includes/class-iexport-loader.php';
    require_once plugin_dir_path(dirname(__FILE__)).'admin/class-iexport-admin.php';
    require_once plugin_dir_path(dirname(__FILE__)). 'includes/class-iexport-i18n.php';
    $this->loader = new Iexport_loader();

}
private function set_locale(){
    $plugin_i18n = new Iexport_i18n();

    $this->loader->add_action('plugin_loaded',$plugin_i18n,'load_plugin_textdomain');
}
private function define_admin_hooks(){
    $plugin_admin = new Iexport_admin($this->get_plugin_name(),$this->get_version());
     $this->loader->add_action('admin_enqueue_scripts',$plugin_admin,'enqueue_styles');
    
    $this->loader->add_action('admin_enqueue_scripts',$plugin_admin,'enqueue_scripts');
    $this->loader->add_action('admin_init',$plugin_admin,'settings_options');
  //  $this->loader->add_action( 'rest_api_init', $plugin_admin,'settings_options' );
    $this->loader->add_action('admin_menu',$plugin_admin,'display_page');
    $this->loader->add_action('wp_ajax_import_action',$plugin_admin,'import_p');
    $this->loader->add_action('wp_ajax_export_action',$plugin_admin,'export_p');
 
}
public function run(){
    $this->loader->run();
}

public function get_plugin_name(){

    return $this->plugin_name;
}

public function get_loader(){
    return $this->loader;
}

public function get_version(){
    return $this->version;
}

 }