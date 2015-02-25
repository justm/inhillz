
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
                $form.html(jqXHR.responseText);
            }
            else if(jqXHR.status === 401){ //session expired
                location.reload();
            }
            else{
                $input.removeClass("hidden");
                $form.find(".progress").addClass("hidden");
                $form.trigger('reset');
                
                if(jqXHR.responseText.length > 0){
                    $form.prepend(jqXHR.responseText);
                }else{
                    alert(window["upload_error"]);
                }
            }
        }
    }); 
});

/**
 * @var boolean fSubmitInProgress Defines weather was the form submited and script is waiting for a response
 * spracovanie alebo nie
 */
var fSubmitInProgress = false;

//Potrvrdenie formulárov ajaxom
$(document).on( "submit", ".ajaxForm", function(event){
    
    event.preventDefault();
    var $btn = $(this).button('loading');
    
    if( !window["fSubmitInProgress"] ) {
        window["fSubmitInProgress"] = true;
        try{
            
            var $thisForm = $(this);
            var data = $thisForm.serializeArray();  
            var formData = {};
            
            for (var i = 0; i < data.length; i++) {
                var splitted = data[i].name.split("[");
                var model = splitted.shift();
                if( splitted[0] ){
                    var name = splitted[0].split("]").shift();
                    
                    if( formData.hasOwnProperty(model) ){
                        formData[model][name] = data[i].value;
                    }else{
                        formData[model] = {};
                        formData[model][name] = data[i].value;
                    }
                }else{
                    formData[model] = data[i].value;
                }
            }
            formData["ajaxForm"] = 1;
        }catch(e) { }
        
        $.ajax({
            type:"POST",
            url: $thisForm.attr("action"),
            data: formData,
            crossDomain: true,
            xhrFields: {
                withCredentials: true
            },
            success: function( data, status, jqXHR ){
                
                window["fSubmitInProgress"] = false;
                //$btn.button('reset');
            },
            error: function(){
                alert(window["submit_failed"]);
                window["fSubmitInProgress"] = false;
                //$btn.button('reset');
            }        
        });
    }
});    