<?php
session_start();
include("db.php");
include("functions_hash.php");
$name=clean($_POST['name']);
$email=clean($_POST['email']);
$password=$_POST['password'];
$confirmPassword = $_POST['confirm_password'];
if($password!=$confirmPassword)
    {
        $_SESSION['error']="Passwords do not match.";
        header("Location: userregister.php");
        exit();
    }
    $check=mysql_query("Select * from users where email='$email'");
    if(mysql_num_rows($check)>0)
        {
            $_SESSION['error']="Email is already registered.";
            header("Location:userregister.php");
                    exit();
        }
        $hashedPass=hashPass($password);
        $query = "Insert into users(name,email,password,role) values('$name','$email','$hashedPass','citizen');";
        $result = mysql_query($query);
        if($result)
            {
                $_SESSION['success']="Registration successful! Please Login.";
                header("Location: userlogin.php");
                exit();
            }
            else
                {
                    $_SESSION['error']="Registration Failed. Please try again.";
                    header("Location:userregister.php");
                    exit();
                }
?>