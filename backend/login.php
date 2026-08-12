<?php
session_start();
include("db.php");
include("functions_hash.php");
$email=clean($_POST['email']);
$password=$_POST['password'];
$query=mysql_query("Select * from users where email = '$email'");
if(mysql_num_rows($query)==1)
    {
        $user = mysql_fetch_assoc($query);
        if(verifyPass($password, $user['password']))
            {
                $_SESSION['user_id']=$user['id'];
                $_SESSION['name'] = $user['name'];
                $_SESSION['email'] = $user['email'];
                $_SESSION['role'] = $user['role'];
                if($user['role']==='admin')
                    {
                        header("Location: admindash.php");
                    }
                    else
                        {
                            header("Location: userdashboard.php");
                        }
                        exit();
            }
    }
    $_SESSION['error']="Invalid Email or Password.";
    header("Location: userlogin.php");
    exit();
?>