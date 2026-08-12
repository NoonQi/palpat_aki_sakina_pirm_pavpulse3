<?php
session_start();
include("db.php");
include("functions_hash.php");
if(!isset($_SESSION['user_id']))
    {
        header("Location:userlogin.php");
        exit();
    }
    $user_id=$_SESSION['user_id'];
    $title=clean($_POST['title']);
    $severity = clean($_POST['severity']);
    $road_type = clean($_POST['road_type']);
    $description = clean($_POST['description']);
    $latitude = $_POST['latitude'];
    $longitude = $_POST['longitude'];
    if(empty($title)||empty($severity)||empty($road_type)||empty($description)||empty($latitude)||empty($longitude))
        {
            die("All report Fields are required.");
        }
        if(!isset($_FILES['photo'])||$_FILES['photo']['error']!=0)
            {
                die("Photo is required.");
            }
            $photo=$_FILES['photo'];
            $allowedTypes=array('image/jpeg','image/png','image/jpg','image/gif');
            if(!in_array($photo['type'],$allowedTypes))
                {
                    die("Only JPG, JPEG, PNG and GIF images are allowed.");
                }
                $extension = pathinfo($photo['name'],PATHINFO_EXTENSION);
                $fileName=time()."_".mt_rand(1000,9999).".".$extension;
                $uploadPath="uploads/".$fileName;
                if(!move_uploaded_file($photo['tmp_name'],$uploadPath))
                    {
                        die("Failed to upload photo.");
                    }
            $query="Insert into reports(user_id,title,severity,road_type,description,photo,latitude,longitude) values('$user_id','$title','$severity','$road_type','$description','$uploadPath','$latitude','$longitude')";
            $result= mysql_query($query);
                    if($result)
                        {
                            header("Location:userdashboard.php?success=1");
                            exit();
                        }
                        else{
                            die("Report submission failed: " . mysql_error());
                        }
?>
