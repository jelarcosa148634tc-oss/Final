<?php
require_once "config.php";

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $student_id = $mysqli->real_escape_string($_POST['student_id']);
    $due_date = $mysqli->real_escape_string($_POST['due_date']); // GET THE DATE FROM YOUR FORM
    $book_ids = $_POST['book_ids']; // This is the array of selected books

    if (!empty($book_ids)) {
        foreach ($book_ids as $book_id) {
            $book_id = intval($book_id);

            // 1. Get the book title
            $book_res = $mysqli->query("SELECT title FROM books WHERE id = $book_id");
            $book_data = $book_res->fetch_assoc();
            $book_title = $mysqli->real_escape_string($book_data['title']);

            // 2. Bawas stock by 1
            $mysqli->query("UPDATE books SET stocks = stocks - 1 WHERE id = $book_id");

            // 3. MAG Insert into borrowers table WITH the due_date
            $query = "INSERT INTO borrowers (student_id, book_borrowed, book_id, date_borrowed, due_date, status) 
                      VALUES ('$student_id', '$book_title', $book_id, NOW(), '$due_date', 'Borrowed')";
            
            $mysqli->query($query);
        }
        header("Location: borrowers.php?msg=success");
    } else {
        header("Location: borrow_book.php?error=no_books");
    }
    exit;
}
?>