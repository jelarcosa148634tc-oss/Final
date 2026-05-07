<?php
require_once "config.php";

if(isset($_GET['id'])) {
    $id = $mysqli->real_escape_string($_GET['id']);
    $result = $mysqli->query("SELECT * FROM students WHERE student_id = '$id'");

    if($result->num_rows > 0) {
        $row = $result->fetch_assoc();
        $row['success'] = true;
        echo json_encode($row);
    } else {
        echo json_encode(['success' => false]);
    }
}
?>