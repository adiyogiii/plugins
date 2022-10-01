jQuery(document).ready(function() {



    jQuery('.import-btn').click(function() {

        var file_data = jQuery('#file').prop('files')[0];
        form_data = new FormData();
        form_data.append('file', file_data);
        form_data.append('action', 'import_action');
        console.log(form_data);
        jQuery.post(ajax_object.ajax_url, form_data, function(response) {

            console.log(response);
        })

    });

    jQuery('.export-btn').click(function() {



        var data = {
            'action': 'export_action'
        }

        jQuery.post(ajax_object.ajax_url, data, function(response) {

            jQuery('.import-btn-wrap').html(response);
            console.log(response);
        })

    })

});