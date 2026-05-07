<?php
$current_page = basename($_SERVER['PHP_SELF']);

if (session_status() === PHP_SESSION_NONE) session_start();

// Security: If not logged in OR is an admin trying to access student area
if (!isset($_SESSION["loggedin"]) || $_SESSION["role"] !== "student") {
    header("location: login.php");
    exit;
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>TNTS Student Library</title>
    <style>
        /* REUSING YOUR EXACT ADMIN STYLES */
        :root {
            --primary-blue: #003366;
            --accent-gold: #FFD700;
            --light-gray: #f4f4f9;
        }

        body {
            font-family: 'Segoe UI', sans-serif;
            margin: 0;
            background-color: var(--light-gray);
        }

        header {
            background-color: var(--primary-blue);
            color: white;
            padding: 1rem 2rem;
            display: flex;
            justify-content: space-between;
            align-items: center;
        }

        .nav-links a {
            color: white;
            text-decoration: none;
            margin-left: 10px;
            font-weight: bold;
            padding: 10px 18px;
            border-radius: 5px;
            transition: 0.2s;
            display: inline-block;
        }

        .nav-links a:hover:not(.btn-home) {
            background-color: rgba(255, 255, 255, 0.1);
        }

        .btn-home {
            background-color: var(--accent-gold) !important;
            color: var(--primary-blue) !important;
            box-shadow: 0 2px 5px rgba(0,0,0,0.2);
        }

        .container {
            max-width: 1100px;
            margin: 30px auto;
            padding: 20px;
            background: white;
            border-radius: 10px;
            box-shadow: 0 4px 10px rgba(0, 0, 0, 0.1);
        }
        
        h2 {
            color: var(--primary-blue);
            border-bottom: 2px solid var(--primary-blue);
            padding-bottom: 10px;
        }
    </style>
</head>
<body>

<header>
    <div>
        <h1 style="margin:0;">TNTS Library <span style="font-size: 0.8rem; vertical-align: middle; background: var(--accent-gold); color: var(--primary-blue); padding: 2px 8px; border-radius: 4px; margin-left: 10px;">STUDENT</span></h1>
    </div>
    <nav class="nav-links">
        <a href="student_index.php" class="<?php echo ($current_page == 'student_index.php') ? 'btn-home' : ''; ?>">Dashboard</a>
        
        <a href="student_book.php" class="<?php echo ($current_page == 'student_book.php') ? 'btn-home' : ''; ?>">Books</a>
        
        <a href="student_borrowed.php" class="<?php echo ($current_page == 'student_borrowed.php') ? 'btn-home' : ''; ?>">Borrowed</a>
        
        <a href="logout.php" style="color: #ff6b6b !important;">Logout</a>
    </nav>
</header>