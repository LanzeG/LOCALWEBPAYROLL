<?php
/* Database connection settings */


    $host = "localhost";
    $user= "root";
    $pass= "";
    $dbname1="masterdb";
    $conn1 = mysqli_connect($host,$user,$pass,$dbname1);


    if ( !$conn1 ) {
    die("Connection failed : " . mysql_error());
    }

?>