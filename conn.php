<?php
    $conn = new mysqli('localhost','root','','db_sig');

    if(mysqli_connect_errno()){
        printf ("connection error : ".mysqli_connect_error());
        exit();
    }
?>