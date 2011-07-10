<?php
//Steganography code
//Written by Anthony Chen
include 'config.php';

/* Functions */
function decodeMessage($image, $height, $width)
{
    $breakflag= false;
    $decodedMessage= "";
    for($i= 1; $i < $height; $i++) {
        for($j= 0; $j < $width; $j++) {
            
            $decodedChar= decodePixel($j, $i, $image);
            if (ord($decodedChar) == 219) {
                $breakflag= true;
                break;
            }
            else {
                $decodedMessage .= $decodedChar;
            }
        }
        if ($breakflag) {
            break;
        }
    }
    return $decodedMessage;
}

function decodePW($image)
{
    $password= "";
    for($i= 0; $i < 32; $i++){
            $password .= decodePixel($i, 0, $image);
    }
    return $password;
}

function encodePW($image, $password, $s1, $s2)
{
    $token= getToken($password, $s1, $s2);
    
    for($i= 0; $i < 32; $i++){
        $char= substr($token, 0, 1);
        encodePixel($i, 0, ord($char), $image);

        $token= substr($token, 1);
    }
}

function encodeMessage($image, $message, $width, $height)
{
    $breakloop= false;
    $length= strlen($message);
    $lastx= 0;
    $lasty= 1;
    $flag= FALSE;
    
    for($i= 1; $i < $height; $i++) {
        for($j= 0; $j < $width; $j++) {
            if (strlen($message) > 0) {
                $char= substr($message, 0, 1);
                encodePixel($j, $i, ord($char), $image);

                $message= substr($message, 1);
                $lastx= $j;
                $lasty= $i;
            }
            else {
                $lastx++;
                $breakloop= true;
                break;
            }
        }
        if ($breakloop) {
            break;
        }
    }

    encodePixel($lastx, $lasty, 219, $image);
}

function decodePixel($x, $y, $image)
{
    $rgb= colorAtPixel($x, $y, $image);
    //echo '<br/>';
    //echo var_dump($rgb);
    $red= $rgb['red'];
    $green= $rgb['green'];
    $blue= $rgb['blue'];
    
    $r= findones($red);
    $g= findones($green);
    $b= findones($blue);
    
    $intchar= intval($r . $g . $b);
    
    return chr($intchar);
}

function findones($i)
{
    $ss= "$i";
    if (strlen($ss) == 1) {
        return charAt($ss, 0);
    }
    elseif (strlen($ss) == 2) {
        return charAt($ss, 1);
    }
    else {
        return charAt($ss, 2);
    }
}

function getToken($password, $s1, $s2)
{
	$token= md5($s1.$password.$s2);
	return $token;
}

function encodePixel($x, $y, $value, $image)
{
    $rgb= colorAtPixel($x, $y, $image);
	
    $red= $rgb['red'];
    $green= $rgb['green'];
    $blue= $rgb['blue'];
    
    $hundreds= (int)($value / 100);
    $tens= (int)(($value % 100)/10);
    $ones= (int)($value % 10);
	
    $newred= encodeChannel($red,$hundreds);
    $newgreen= encodeChannel($green,$tens);
    $newblue= encodeChannel($blue,$ones);
	
    $im = imagecreatetruecolor(3,3);
    $newrgb = imagecolorallocate($im, $newred, $newgreen, $newblue);

    imagesetpixel($image, $x, $y, $newrgb);
}

function encodeChannel($channelvalue, $encodedvalue)
{
	if ($channelvalue >= 250) {
		$encodedchannelstring= "24" . "$encodedvalue";
		return intval($encodedchannelstring);
	}
	else {
		$channelvaluestring= "$channelvalue";
		$encodedchannelstring= substr($channelvaluestring, 0, strlen($channelvaluestring) - 1) . "$encodedvalue";
                return intval($encodedchannelstring);
	}
}

function LoadJpeg($imgname)
{
    /* Attempt to open */
    $im = @imagecreatefromjpeg($imgname);

    /* Failed */
    if(!$im)
    {
        /* Create a black image */
        $im  = imagecreatetruecolor(150, 30);
        $bgc = imagecolorallocate($im, 255, 255, 255);
        $tc  = imagecolorallocate($im, 0, 0, 0);

        imagefilledrectangle($im, 0, 0, 150, 30, $bgc);

        /* Output an error message */
        imagestring($im, 1, 5, 5, 'Error loading ' . $imgname, $tc);
    }

    return $im;
}

function colorAtPixel($x, $y, $image)
{
    $colorindex = imagecolorat($image, $x, $y);
    $colorarray = imagecolorsforindex($image, $colorindex);
    
    return $colorarray;
}

function charAt($string, $index)
{
    if ($index < mb_strlen($string)) {
        return mb_substr($string, $index, 1);
    }
    else {
        return -1;
    }
}
?>