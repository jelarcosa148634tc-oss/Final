<?php
require_once "config.php";
if(isset($_GET['lrn'])) {
    $lrn = $mysqli->real_escape_string($_GET['lrn']);
    // Search in the credentials table where they signed up
    $result = $mysqli->query("SELECT FirstName, Role FROM tb_StudentCredentials WHERE StudentID = '$lrn'");
    
    if($row = $result->fetch_assoc()) {
        // Since your tb_StudentCredentials only has FirstName, we return it. 
        // If you added LastName to that table, include it here.
        echo json_encode($row);
    } else {
        echo json_encode(null);
    }
}
?>