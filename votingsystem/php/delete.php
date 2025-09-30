<?php
include_once('connect.php');

$todel = $_POST['todel'];

$sql = "DELETE from account where ID = $todel";
$result = mysqli_query($conn, $sql);

if(!$result){
    die('Error: '.mysqli_error($conn));
}
?>