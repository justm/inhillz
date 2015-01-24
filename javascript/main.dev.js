
//** Direct file upload
$(document).on("change",'input[type=file]', function(){
    
    var $form  = $(this).parents("form");
    var $input = $(this);
    $input.addClass("hidden");
    $form.find(".progress").removeClass("hidden");
    
    var formData = new FormData($form);
    //loop for add $_FILES["upload"+i] to formData
    for (var i = 0; i < $input.context.files.length; i++) {
        formData.append("workout_files_" + i, $input.context.files[i]);
    }
    
    //send formData to server-side
    $.ajax({
        xhr: function() {
            var xhr = new window.XMLHttpRequest();
            //Upload progress
            xhr.upload.addEventListener("progress", function(evt){
                if ( evt.lengthComputable ) {
                    var pc = evt.loaded / evt.total;
                    $form.find( ".progress-bar" ).html(parseInt(pc*100)+"%");
                    $form.find( ".progress-bar" ).css({width: parseInt(pc*100)+"%"});
                }
            }, false);
            return xhr;
        },
        url : $form.attr("action"),
        type : "POST",
        data : formData,
        dataType : "xml",
        processData: false,  // tell jQuery not to process the data
        contentType: false,   // tell jQuery not to set contentType
        complete: function ( jqXHR ){

             if( jqXHR.status === 200 ){
                try {
                    $form.html(jqXHR.responseText);
                }
                catch( e ){
                    alert(upload_error);
                }
            }
            else{
                alert(upload_error);
            }
        }
    }); 
});


    