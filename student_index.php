<?php
// student_index.php
session_start();

// include the student header which also handles the security check
include_once "student_header.php";
require_once "config.php";

// Get student name for a personal greeting
$student_name = $_SESSION["username"] ?? "Student";
?>

<div class="container">
    <div style="text-align: center; padding: 20px 0;">
        <h2 style="border: none;">Welcome to the Library, <?php echo htmlspecialchars($student_name); ?>!</h2>
        <p style="color: #666;">Explore our collection or check your current borrowed books below.</p>
    </div>

    <div class="stats-grid">
        <div class="stat-card bg-blue">
            <h3 style="margin: 0; font-size: 0.9rem; opacity: 0.9;">Available Books</h3>
            <p style="font-size: 2rem; margin: 10px 0; font-weight: bold;">
                <?php 
                   $res = $mysqli->query("SELECT COUNT(*) as total FROM books WHERE stocks > 0");
                   echo $res->fetch_assoc()['total'];
                ?>
            </p>
            <a href="student_book.php" style="color: white; font-size: 0.8rem;">Browse Gallery →</a>
        </div>

        <div class="stat-card bg-gold">
            <h3 style="margin: 0; font-size: 0.9rem; opacity: 0.9;">My Borrowed</h3>
            <p style="font-size: 2rem; margin: 10px 0; font-weight: bold;">
                <?php 
                   $lrn = $_SESSION['lrn'];
                   $res = $mysqli->query("SELECT COUNT(*) as total FROM borrowers WHERE student_id = '$lrn' AND status = 'Borrowed'");
                   echo $res->fetch_assoc()['total'];
                ?>
            </p>
            <a href="student_borrowed.php" style="color: var(--primary-blue); font-size: 0.8rem;">View History →</a>
        </div>

        <div class="stat-card bg-green">
            <h3 style="margin: 0; font-size: 0.9rem; opacity: 0.9;">Library Status</h3>
            <p style="font-size: 1.2rem; margin: 10px 0; font-weight: bold; padding-top: 10px;">OPEN</p>
            <span style="font-size: 0.8rem;">8:00 AM - 5:00 PM</span>
        </div>
    </div>

    <div class="card">
        <h3>Library Announcements</h3>
        <p style="font-size: 0.95rem; color: #444; line-height: 1.6;">
            Welcome to the new **TNTS Student Library Portal**. You can now view available books online. 
            To borrow a book, please visit the librarian and present your Student ID / LRN.
        </p>
    </div>
</div>

<?php include_once "footer.php"; ?>