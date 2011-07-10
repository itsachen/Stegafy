<?php
include 'steg.php';

$width= 0;
$height= 0;
$password= '';
$message= '';
$img= null;
$decodedmessage= '';
$together= '';

//Directory for file storing
$upload_dir = 'upload/';

//@# Change when update site
$preview_url = 'http://localhost/steg/upload/';
$filename= '';
$filetype= '';

$result = 'ERROR';
$result_msg = ''; 
$allowed_image = array ('image/gif', 'image/jpeg', 'image/jpg', 'image/pjpeg','image/png');

//Upload limit
define('PICTURE_SIZE_ALLOWED', 5242880); // bytes

//File was sent from form
if (isset($_FILES['picture'])) {
    
    //Checking error
    if ($_FILES['picture']['error'] == UPLOAD_ERR_OK) {
        //Checks acceptable image formats
        if (in_array($_FILES['picture']['type'], $allowed_image)) {
            //Checks image size against limit
            if(filesize($_FILES['picture']['tmp_name']) <= PICTURE_SIZE_ALLOWED) {
                //All clear for processing!
                
                /* Encoding mode */
                if ($_POST['mode'] == 'encode') {
                    
                    echo $_FILES['picture']['type'];
                    
                    $sizeinfo= getimagesize($_FILES['picture']['tmp_name']);
                    $width= $sizeinfo[0];
                    $height= $sizeinfo[1];
                    
                    //Begin encoding
                    if ($width >= 35) {
                        
                        $filetype= $_FILES['picture']['type'];
                        
                        $password= $_POST['password'];
                        $message= $_POST['message'];
                        
                        //Renaming the file
                        if (in_array($_FILES['picture']['type'], array ('image/gif', 'image/jpg', 'image/png'))) {
                            $filename = substr('encoded_'.$_FILES['picture']['name'], 0, -3).'.png';
                        }
                        elseif (in_array($_FILES['picture']['type'], array ('image/jpeg', 'image/pjpeg'))) {
                            $filename = substr('encoded_'.$_FILES['picture']['name'], 0, -4).'.png';
                        }
                        
                        //Image is jpg
                        if (($_FILES['picture']['type'] == 'image/jpeg') || ($_FILES['picture']['type'] == 'image/jpg')) {
                            $img = imagecreatefromjpeg($_FILES['picture']['tmp_name']);
                        }
                        //Image from png
                        elseif ($_FILES['picture']['type'] == 'image/png') {
                            $img = imagecreatefrompng($_FILES['picture']['tmp_name']);
                        }
                        //Image from gif
                        elseif ($_FILES['picture']['type'] == 'image/gif') {
                            $img = imagecreatefromgif($_FILES['picture']['tmp_name']);
                        }
                        
                        encodePW($img, $password, $salt1, $salt2);
                        encodeMessage($img, $message, $width, $height);
                        imagepng($img, $upload_dir.$filename, 0);
                        
                        $together= $preview_url.$filename;
                        
                        $result = 'OK';
                    }
                    elseif ($width < 35) {
                        //Throw width error
                        $result= 'WIDTH_ERROR';
                    }
                }
                
                /* Decoding mode */
                elseif ($_POST['mode'] == 'decode') {
                    $sizeinfo= getimagesize($_FILES['picture']['tmp_name']);
                    $width= $sizeinfo[0];
                    $height= $sizeinfo[1];
                    $img = imagecreatefrompng($_FILES['picture']['tmp_name']);
                    
                    $password= $_POST['password'];
                    $token= getToken($password, $salt1, $salt2);
                    
                    $decodedtoken= decodePW($img);
                    
                    if ($token == $decodedtoken) {
                        $decodedmessage= decodeMessage($img, $height, $width);
                        $decodedmessage= nl2br($decodedmessage);
                        $decodedmessage= str_replace("\r\n",'',$decodedmessage);
                        $result= 'RIGHT_PASSWORD';
                    }
                    else {
                        //Wrong password
                        echo "Wrong password";
                        $result= 'WRONG_PASSWORD';
                    }
                }
            }
            else {
                $filesize = filesize($_FILES['picture']['tmp_name']); // or $_FILES['picture']['size']
                $filetype = $_FILES['picture']['type'];
                $result_msg = PICTURE_SIZE;
            }
        }
        else {
            $result_msg = SELECT_IMAGE;
        }
    }
    elseif ($_FILES['picture']['error'] == UPLOAD_ERR_INI_SIZE) {
        $result_msg = 'The uploaded file exceeds the upload_max_filesize directive in php.ini';
    }
    else {
        $result_msg = 'Unknown error';
    }
}
    
/* JAVASCRIPT SECTION BEGIN */
    
/* Encoding mode javascript */
if ($_POST['mode'] == 'encode') {
    
    //Width error
    if ($result == "WIDTH_ERROR") {
echo <<< _END
<script language="JavaScript" type="text/javascript">
var parDoc = window.parent.document;
parDoc.getElementById('message').style.borderColor = '#f86556';
parDoc.getElementById('message').value = 'Images that are to be decoded must be at least 35 pixels by 35 pixels!';
</script>
_END;
    }

    //Success! @#
    if($filename != '') {
echo <<< _END
<script language="JavaScript" type="text/javascript">
var parDoc = window.parent.document;
parDoc.getElementById('message').style.borderColor = '#ebebeb';
parDoc.getElementById('password').style.borderColor = '#ebebeb';
parDoc.getElementById('download_link').innerHTML = '<a href="$together">Download Encoded Image!</a>';
parDoc.getElementById('linksub').innerHTML= '(Right click and save file as)';
</script>
_END;
    }
    exit(); // do not go futher
}

/* Decoding mode javascript */
elseif ($_POST['mode'] == 'decode') {
    //Wrong password
    if ($result == 'WRONG_PASSWORD') {
echo <<< _END
<script language="JavaScript" type="text/javascript">
var parDoc = window.parent.document;
parDoc.getElementById('message').style.borderColor = '#ebebeb';
parDoc.getElementById('password').style.borderColor = '#f86556';
parDoc.getElementById('download_link').innerHTML = '';
parDoc.getElementById('linksub').innerHTML= '';
</script>
_END;
        exit();
    }
    //Correct password
    elseif ($result == 'RIGHT_PASSWORD') {
echo <<< _END
<script language="JavaScript" type="text/javascript">
var text = "$decodedmessage";
text = text.replace(/<br \/>/ig,"\\n");
var parDoc = window.parent.document;
parDoc.getElementById('message').style.borderColor = '#ebebeb';
parDoc.getElementById('password').style.borderColor = '#a8e368';
parDoc.getElementById('message').value = text;
parDoc.getElementById('download_link').innerHTML = '';
parDoc.getElementById('linksub').innerHTML= '';
</script>
_END;
exit(); // do not go futher
    }

}

?>