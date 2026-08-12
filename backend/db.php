<?php
$host="/*host*/";
$username="/*username*/";
$password="/*password*/";
$database="/*database name*/";
$conn=mysql_connect($host,$username,$password);
if(!$conn)
    {
        die("Database Connection Failed: ".mysql_error());
    }
    $db=mysql_select_db($database, $conn);
    if(!$db)
        {
            die('Database Not found');
        }
?>
