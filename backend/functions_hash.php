<?php
function clean($data)
{
    $data=trim($data);
    $data=stripslashes($data);
    $data=htmlspecialchars($data);
    return $data;
}
function hashPass($password)
{
    $char = 'abcdefghijklmnopqrstuvwxyzABCDEFGHIJKLMNO0123498765PQRSTUVWXYZ./';
    $salt='';
    for($i=0;$i<22;$i++)
        {
            $salt.=$char[mt_rand(0,strlen($char)-1)];
        }
        $salt='$2a$10$'.$salt.'$';
        return crypt($password,$salt);
}
function verifyPass($password, $storedHash)
{
    return crypt($password,$storedHash)===$storedHash;
}
?>