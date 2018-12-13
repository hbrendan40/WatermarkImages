<html>  
   <body>
      
      <form action="" method="POST" enctype="multipart/form-data">
         <input type="file" name="image" />
         <input type="submit"/>
      </form> 
    
      
   </body>
</html>



<?php
//make connection to database with login info
//user = root   password: password

require_once 'login.php';
$conn = new mysqli($hn, $un, $pw, $db);
if ($conn->connect_error) die($conn->connect_error);   


if(isset($_FILES['image'])){    //when file is uploaded - get file information and store in variables
    $errors= array();
    $file_name = $_FILES['image']['name'];
    $file_size =$_FILES['image']['size'];
    $file_tmp =$_FILES['image']['tmp_name'];
    $file_type=$_FILES['image']['type'];
    $file_ext=strtolower(end(explode('.',$_FILES['image']['name'])));
    $expensions= array("jpeg","jpg","png");

    
    $imageRe = getimagesize($_FILES['image']['tmp_name']);


    
    if(in_array($file_ext,$expensions)=== false){   //making sure image is JPEG or PNG 
       $errors[]="extension not allowed, please choose a JPEG or PNG file.";
    }
    
  
    
    if(empty($errors)==true){         //if file upload is successs

    $imageResolution = sprintf("%s X %s" ,$imageRe[0], $imageRe[1]);
    $imageSize = ($file_size/1000000);  //converting bytes to MB
    $imageName = $file_name;
    $imageName= mysql_real_escape_string($imageName);

$sql= "INSERT INTO imageINFO(imageResolution, imageSize, id, imageName) VALUES ('$imageResolution',$imageSize, UUID(), '$imageName')";

    
    if ($conn->query($sql) === TRUE) {
    echo "New record added successfully";
    } 
    else {
    echo "Error: " . $sql . "<br>" . $conn->error;
    }   
   
 
       


    }
    else{
       print_r($errors);
    }
 }//--------------------------------------------------------------------
//delete post when delete is pressed    
//match image id to database and delete if valid

if (isset($_POST['delete']) && isset($_POST['id']))   
{
$id = get_post($conn, 'id');
$query = "DELETE FROM imageINFO WHERE id='$id'";
$result = $conn->query($query);

if (!$result) echo "DELETE failed: $query<br>" . 
$conn->error . "<br><br>";

}

//-----------------------------------------------------------------------------



 //get data from imageInformation database and post it from newest to oldest post

$query = "SELECT * FROM imageINFO";        
$result = $conn->query($query);
if (!$result) die ("Database access failed: " . $conn->error);

$rows = $result->num_rows;
for ($j = 0;  $j < $rows ; ++$j)
{
$result->data_seek($j);
$row = $result->fetch_array(MYSQLI_NUM);
echo <<<_END
<pre>
Image Resolution: $row[0]
Image Size: $row[1] MB
Image Name: $row[2]
ID: $row[3]
</pre>
<form action="projecttest.php" method="post">
<input type="hidden" name="delete" value="yes">
<input type="hidden" name="id" value="$row[3]">
<input type="submit" value="DELETE RECORD"></form>

_END;
}


$result->close();

$conn->close();

function get_post($conn, $var)
{
return $conn->real_escape_string($_POST[$var]);
}





?>


