<?php
include_once('connect.php');

//retrieve
$sql = 'SELECT * FROM account';
$result = mysqli_query($conn, $sql);

//error handling
if(!$result){
    die('Error: '.mysqli_error($conn));
}

//fetching and retrieving data from phpmyadmin
if(mysqli_num_rows($result)>0){
    echo "<table border =1>";
    while($row = mysqli_fetch_assoc($result)){
        echo "<tr>";
        echo "<td>ID:</td><td>".$row['ID']."</td>";
        echo "<td>Username:<t/td><td>".$row['username']."</td>";
        echo "<td>Password:<t/td><td>".$row['password']."</td>";
        echo "</tr>";
    }
    echo "</table>";
}else{
    echo '0 result';
}

/*
'accountid' => 1
'username' => 'shun'
'password' => 'shun'
*/

?>