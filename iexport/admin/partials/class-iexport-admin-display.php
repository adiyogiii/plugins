<?php /**
 * Provide a admin area view for the plugin 
 * 
 * This file is used to markup the admin-facing side of the plugin
 */

class Iexport_admin_display{

    public function __construct(){
        $this->option = new Iexport_admin_options();
    }
        
public function settings_page(){
    include_once plugin_dir_path( dirname(__FILE__) ).'partials/class-iexport-admin-display.php';
          
            
    }
            

    public function export_page(){
        ?>

    <div class="wrap">
    <button class="export-btn">export</button>
    
            
            <form  method="POST" action="options.php">
                <?php 
                settings_fields('iexport-page');
                do_settings_sections( 'iexport-page' );
                submit_button('Save');
                ?>
            </form>
    </div>
    <div class="import-btn-wrap">
    </div>
        
        <?php
         //  $this->option->import_p();
    }   
    
    public function import_page(){
        ?>
            <button class="import-btn"  >Import</button>
<form  > 
            <div class="form-group">
        <label><?php _e('Choose File:'); ?></label>
        <input type="file" id="file"  accept="application/JSON" />
    </div>
    </form>
        <?php
          // $this->option->export_p();
    }

}
?>
