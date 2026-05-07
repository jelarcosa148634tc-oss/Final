<?php
require_once "config.php";

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $student_id = $mysqli->real_escape_string($_POST['student_id']);
    $due_date = $mysqli->real_escape_string($_POST['due_date']);
    
    // FIX: Use ?? [] to prevent the "Undefined array key" error
    $book_ids = $_POST['book_ids'] ?? []; 

    if (!empty($book_ids)) {
        foreach ($book_ids as $b_id) {
            $b_id = intval($b_id);
            
            // 1. Fetch book title
            $book_info = $mysqli->query("SELECT title FROM books WHERE id = $b_id")->fetch_assoc();
            
            if ($book_info) {
                $book_title = $mysqli->real_escape_string($book_info['title']);

                // 2. Insert transaction
                $sql = "INSERT INTO borrowers (student_id, book_id, book_borrowed, date_borrowed, due_date, status) 
                        VALUES ('$student_id', $b_id, '$book_title', NOW(), '$due_date', 'Borrowed')";
                
                if ($mysqli->query($sql)) {
                    // 3. Update stocks
                    $mysqli->query("UPDATE books SET stocks = stocks - 1 WHERE id = $b_id");
                }
            }
        }
        header("Location: borrowers.php?msg=success");
        exit;
    } else {
        // Handle the case where the user clicked "Confirm" without picking a book
        echo "<script>alert('Error: Please select at least one book before confirming.'); window.history.back();</script>";
        exit;
    }
}
?>