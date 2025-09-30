<?php
//connect
$servername = "localhost";
$username = "root";
$password = "";
$dbname = "bsit2dv2";

/*
//creating connection
$conn = mysqli_connect($servername, $username,$password,$dbname);

//checking the connection
if(!$conn){
    die('Error: '.mysqli_connect_error());
}else{
    echo 'Connected Sucessfully';
}*/
$conn = new mysqli($servername,$username,$password,$dbname);

//check connection
if($conn->connect_error){
    die('Error: '.$conn->connect_error);
}else{
    echo 'connected successfully';
}

?>