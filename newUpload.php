<?php include "../inc/dbinfo.inc"; ?>

<?php


//Brendan Hui
//upload image and put watermark 

$connection = mysqli_connect(DB_SERVER, DB_USERNAME, DB_PASSWORD);
if (mysqli_connect_errno()) echo "Failed to connect to MySQL: " . mysqli_connect_error();

$database = mysqli_select_db($connection, DB_DATABASE);

$file2 = "https://icon2.kisspng.com/20180418/kkw/kisspng-digital-watermarking-watercolor-watermark-5ad7f5dc840cc9.0658787515241026205409.jpg";

if(isset($_FILES['image_file']))
{
$image_name = $_FILES['image_file']['name']; //file name
$image_size = $_FILES['image_file']['size']; //file size
$image_temp = $_FILES['image_file']['tmp_name']; //file temp
$image_type = $_FILES['image_file']['type']; //file type

$image= addslashes(file_get_contents($_FILES['image_file']['tmp_name']));


switch(strtolower($image_type)){ //determine uploaded image type 
//Create new image from file
case 'image/png': 
$image_resource =  imagecreatefrompng($image_temp);
break;
case 'image/gif':
$image_resource =  imagecreatefromgif($image_temp);
break;          
case 'image/jpeg': case 'image/pjpeg':
$image_resource = imagecreatefromjpeg($image_temp);
break;
default:
$image_resource = false;
}

if($image_resource){
//Copy and resize part of an image with resampling
list($img_width, $img_height) = getimagesize($image_temp);

//Construct a proportional size of new image

$new_canvas= imagecreatetruecolor($img_width , $img_height);

if(imagecopyresampled($new_canvas, $image_resource , 0, 0, 0, 0, $img_width, $img_height, $img_width, $img_height))
{



watermarkResize($file2, $new_canvas, $img_width, $img_height, $image);

//free up memory
imagedestroy($new_canvas); 
imagedestroy($image_resource);
die();
}
}
}



function watermarkResize($filename,$filename2,$imgWidth,$imgHeight, $orginalImage){

// Get new sizes (change watermark size to fit into image) 
list($width, $height) = getimagesize($filename);
$newwidth = $imgWidth;
$newheight = $imgHeight;

// Load
$thumb = imagecreatetruecolor($newwidth, $newheight);
$source = imagecreatefromjpeg($filename);

// Resize
imagecopyresized($thumb, $source, 0, 0, 0, 0, $newwidth, $newheight, $width, $height);
$thumb2 = $thumb;

mergeImage($thumb2, $filename2, $orginalImage); 
}


function mergeImage($foreground2, $background2, $Original) {
/* Connect to MySQL and select the database. */

$foregroundImage = ($foreground2); 
$backgroundImage = ($background2);

$imgw = imagesx($foregroundImage);   //image width in pixels
$imgh = imagesy($foregroundImage);   //image heigh in pixels 

$imgw2 = imagesx($backgroundImage);   //image width in pixels
$imgh2 = imagesy($backgroundImage); 

for ($i=0; $i<$imgw; $i++)
{
for ($j=0; $j<$imgh; $j++)
{


$rgb = ImageColorAt($foregroundImage, $i, $j);   
$rgb2 = ImageColorAt($backgroundImage, $i , $j); 


//foregrounf rgb values
$rr = ($rgb >> 16) & 0xFF;
$gg = ($rgb >> 8) & 0xFF;
$bb = $rgb & 0xFF;

//background rgb values
$rr2 = ($rgb2 >> 16) & 0xFF;   
$gg2 = ($rgb2 >> 8) & 0xFF; 
$bb2 = $rgb2 & 0xFF;

if ($rr >= 180 && $gg >=180 && $bb >=180) {
$a=1;
}
else
$a=0;


$A = imagecolorallocate($foregroundImage, $rr, $gg, $bb);  //set color 
$B = imagecolorallocate($backgroundImage, $rr2, $gg2, $bb2); 

$C = ($a * $B + (1-$a)* $A);

imagesetpixel ($foregroundImage, $i, $j, $C);
    
}

}     
imagecopymerge ($backgroundImage, $foregroundImage, 0, 0, 0, 0, $imgw, $imgh, 40); 

$mergeImage = $backgroundImage;
ob_start();
imagejpeg($mergeImage,null,100);
$imageblob = ob_get_contents();
ob_clean();

$rawr2= mysql_real_escape_string($imageblob);

$category = $_POST["category"];
$cost= $_POST["cost"];
$name= $_POST["name"];



$sql= "INSERT INTO imageINFO(imageName,id, image, imageWatermark, category) VALUES ('$name', UUID(), '$Original', '$rawr2', '$category')";


if ($connection->query($sql) === TRUE) {
echo "New record added successfully";
} 
else {
echo "Error: " . $sql . "<br>" . $connection->error;
}   



}


?>
<!DOCTYPE HTML>
<html>
<head>
<style type="text/css">
#upload-form {
padding: 20px;
background: #F7F7F7;
border: 1px solid #CCC;
margin-left: auto;
margin-right: auto;
width: 400px;
}
#upload-form input[type=file] {
border: 1px solid #ddd;
padding: 4px;
}
#upload-form input[type=submit] {
height: 30px;
}
</style>
</head>
<body>

<form action="" id="upload-form" method="post" enctype="multipart/form-data">
<input type="file" name="image_file" />

<br> Image Name:
<input type="text" name="name" value=" "><br>
<br> Category Type:
<input type="text" name="category" value=" "><br>
<br> Cost of Image:
<input type="text" name="cost" value=" "><br>
<input type="submit" value="Submit" />
</form>

</body>
</html>