<?php class Iexport_admin_options{
   

  


function iexport_setting_section_callback(){
    
   // echo '<p>iexport setting section</p>';
}

 function iexport_setting_field_callback() {
        $args=array(
            'public'   => true,
            ); 
        $output = 'names';
         $operator = 'and';
        $selected_ops =array();
            if( is_array(get_option("iexport_setting_name"))){
            $selected_ops = get_option("iexport_setting_name")['clusters'];
            }
            $post_types=get_post_types($args,$output,$operator); 
            echo '<select name="iexport_setting_name[clusters][]" multiple>';

            foreach ($post_types  as $post_type ) {
              
               if(in_array($post_type, $selected_ops)){
                echo '<option value="'. $post_type.'" selected>'. $post_type. '</option>';
               }else {
                echo '<option value="'. $post_type.'">'. $post_type. '</option>';
               }
            }
            echo '</select>';
      $setting = get_option('iexport_setting_name');
    ?>
    <?php
}

  
   
}
?>