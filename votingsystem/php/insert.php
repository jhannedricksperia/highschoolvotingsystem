<?php
//connection-database
include_once('connect.php');

//insert data
$username = $_POST['user'];
$password = $_POST['pass'];

$sql = "INSERT INTO account(username,password) values ('$username',md5('$password'))";
$result = mysqli_query($conn,$sql);

//error handling
if(!$result){
    die('Error: '.mysqli_error($conn));
}else{
    echo 'successfully added!';
}

mysqli_close($conn);
?>