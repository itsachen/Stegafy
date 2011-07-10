/*Box*/
$(document).ready(function() {
	//When you click on a link with class of poplight and the href starts with a # 
$('a.poplight[href^=#]').click(function() {
    var popID = $(this).attr('rel'); //Get Popup Name
    var popURL = $(this).attr('href'); //Get Popup href to define size

    //Pull Query & Variables from href URL
    var query= popURL.split('?');
    var dim= query[1].split('&');
    var popWidth = dim[0].split('=')[1]; //Gets the first query string value

    //Fade in the Popup and add close button
    $('#' + popID).fadeIn().css({ 'width': Number( popWidth ) }).prepend('<a href="#" class="close"><img src="images/close.png" class="btn_close" title="Close Window" alt="Close" /></a>');

    //Define margin for center alignment (vertical   horizontal) - we add 80px to the height/width to accomodate for the padding  and border width defined in the css
    var popMargTop = ($('#' + popID).height() + 80) / 2;
    var popMargLeft = ($('#' + popID).width() + 80) / 2;

    //Apply Margin to Popup
    $('#' + popID).css({
        'margin-top' : -popMargTop,
        'margin-left' : -popMargLeft
    });

    //Fade in Background
    $('body').append('<div id="fade"></div>'); //Add the fade layer to bottom of the body tag.
    $('#fade').css({'filter' : 'alpha(opacity=80)'}).fadeIn(); //Fade in the fade layer - .css({'filter' : 'alpha(opacity=80)'}) is used to fix the IE Bug on fading transparencies 

    return false;
});

//Close Popups and Fade Layer
$('a.close, #fade').live('click', function() { //When clicking on the close or fade layer...
    $('#fade , .popup_block').fadeOut(function() {
        $('#fade, a.close').remove();  //fade them both out
    });
    return false;
});
});

/*Collapsable div*/
animatedcollapse.addDiv('explination', 'fade=1,height=330px')
animatedcollapse.ontoggle=function($, divobj, state){ //fires each time a DIV is expanded/contracted
	//$: Access to jQuery
	//divobj: DOM reference to DIV being expanded/ collapsed. Use "divobj.id" to get its ID
	//state: "block" or "none", depending on state
}

animatedcollapse.init()



function ajaxFileUpload(upload_field) {
	
    if (document.getElementById("encode").checked == true){
        if (document.getElementById('message').value == ''){
            //JS validation for message
			document.getElementById('message').style.borderColor = '#f86556';
			upload_field.form.reset();
			return false;
        }
    }
    
    // Checking file type
    var re_text = /\.jpg|\.gif|\.png|\.jpeg/i;
    var filename = upload_field.value;
    if (filename.search(re_text) == -1) {
        alert("File should be either jpg, gif, or png");
        upload_field.form.reset();
        return false;
    }
    
    //Loading only when encoding
    if (document.forms.pictureForm.elements.mode.value == "encode"){
        document.getElementById('picture_preview').innerHTML = '<div><img src="images/loading.gif" border="0" /></div>';
    }
    
    upload_field.form.action = 'upload-picture.php';
    upload_field.form.target = 'upload_iframe';
    upload_field.form.submit();
    upload_field.form.action = '';
    upload_field.form.target = '';
    return true;
}