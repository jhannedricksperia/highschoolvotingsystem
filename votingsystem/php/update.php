<?php
include_once('connect.php');

$id = $_POST['aid'];
$npw = $_POST['newpass'];

$sql = "UPDATE account set password = '$npw' where ID = $id";
$result = mysqli_query($conn, $sql);

if(!$result){
    die('Error: '.mysqli_error($conn));
}

?>