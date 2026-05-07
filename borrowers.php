<?php
require_once "config.php";

// Handle Return Logic
if (isset($_GET['return_id'])) {
    $borrower_id = intval($_GET['return_id']);
    $book_id = intval($_GET['book_id']);

    $mysqli->query("UPDATE books SET stocks = stocks + 1 WHERE id = $book_id");
    $mysqli->query("UPDATE borrowers SET status = 'Returned', return_date = NOW() WHERE id = $borrower_id");

    header("Location: borrowers.php?msg=returned");
    exit;
}

include_once "header.php";

// 1. Updated Query for Active Borrowers - Added JOIN to get ISBN
$active_query = "SELECT b.*, s.first_name, s.last_name, s.grade_level, s.strand, s.section, s.adviser, bk.isbn 
                 FROM borrowers b
                 JOIN students s ON b.student_id = s.student_id 
                 JOIN books bk ON b.book_id = bk.id 
                 WHERE b.status = 'Borrowed'
                 ORDER BY b.date_borrowed DESC";
$active_res = $mysqli->query($active_query);

// 2. Query for Books Issued (History)
$history_query = "SELECT b.*, s.first_name, s.last_name, bk.author, bk.image, bk.isbn 
                  FROM borrowers b
                  JOIN students s ON b.student_id = s.student_id 
                  JOIN books bk ON b.book_id = bk.id
                  WHERE b.status = 'Returned'
                  ORDER BY b.return_date DESC";
$history_res = $mysqli->query($history_query);
?>

<div class="main-container" style="padding: 20px; max-width: 1300px; margin: 0 auto;">
    
    <div class="table-card" style="background: white; padding: 25px; border-radius: 12px; box-shadow: 0 4px 20px rgba(0,0,0,0.08); margin-bottom: 40px; border: 1px solid #eee;">
        <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 25px;">
            <h2 style="margin:0; color: var(--primary-blue); font-size: 1.5rem;">Active Borrowers</h2>
            <a href="borrow_book.php" class="btn-home" style="padding:10px 20px; text-decoration:none; border-radius:6px; font-weight:bold; font-size: 0.9rem;">+ Add Borrow Book</a>
        </div>

        <table style="width:100%; border-collapse: collapse; font-size: 0.9rem;">
            <thead>
                <tr style="background: #fcfcfc; border-bottom: 2px solid #eee;">
                    <th style="padding:12px; text-align:left; color: black;">Student LRN/ID</th>
                    <th style="padding:12px; text-align:left; color: black;">Full Name & Grade Level</th>
                    <th style="padding:12px; text-align:left; color: black;">Book Title</th>
                    <th style="padding:12px; text-align:left; color: black;">Contact/Section</th>
                    <th style="padding:12px; text-align:left; color: black;">Schedule</th>
                    <th style="padding:12px; text-align:center; color: black;">Action</th>
                </tr>
            </thead>
            <tbody>
                <?php if ($active_res && $active_res->num_rows > 0): ?>
                    <?php while ($row = $active_res->fetch_assoc()): ?>
                        <?php 
                            $today = strtotime(date("Y-m-d"));
                            $due_date = strtotime($row['due_date']);
                            $diff = $due_date - $today;
                            $days_left = round($diff / (60 * 60 * 24));
                            $is_overdue = ($today > $due_date);
                        ?>
                        <tr style="border-bottom: 1px solid #fafafa;">
                            <td style="padding:15px; font-family: monospace; font-weight: bold; color: #333;">
                                #<?php echo htmlspecialchars($row['student_id'] ?? 'N/A'); ?>
                            </td>
                            <td style="padding:15px;">
                                <div style="font-weight: bold;"><?php echo htmlspecialchars($row['first_name'] . ' ' . $row['last_name']); ?></div>
                                <div style="font-size: 0.75rem; color: #d63384; font-weight:bold;">Grade <?php echo htmlspecialchars($row['grade_level']); ?> - <?php echo htmlspecialchars($row['strand']); ?></div>
                            </td>
                            <td style="padding:15px;">
                                <div style="color: var(--primary-blue); font-weight: bold;"><?php echo htmlspecialchars($row['book_borrowed']); ?></div>
                                <div style="font-size: 0.8rem; color: #211e1e;">ISBN: <?php echo htmlspecialchars($row['isbn'] ?? 'N/A'); ?></div>
                            </td>
                            <td style="padding:15px;">
                                <div style="font-size: 1rem; font-weight:bold;">Section: <?php echo htmlspecialchars($row['section']); ?></div>
                                <div style="font-size: 0.9rem; color: #211e1e;">Adviser: <?php echo htmlspecialchars($row['adviser']); ?></div>
                            </td>
                            <td style="padding:15px;">
                                <div style="margin-bottom: 5px;">
                                    <span style="font-size: 0.7rem; color: #3498db; background: #ebf5fb; padding: 3px 8px; border-radius: 12px; font-weight: bold;">
                                        BORROWED: <?php echo date('M d, Y', strtotime($row['date_borrowed'])); ?>
                                    </span>
                                </div>
                                <div style="margin-bottom: 5px;">
                                    <span style="font-size: 0.7rem; color: #e67e22; background: #fff5eb; padding: 3px 8px; border-radius: 12px; font-weight: bold;">
                                        DUE: <?php echo date('M d, Y', strtotime($row['due_date'])); ?>
                                    </span>
                                </div>
                                <div>
                                    <?php if ($is_overdue): ?>
                                        <span style="font-size: 0.65rem; color: #fff; background: #dc3545; padding: 2px 8px; border-radius: 4px; font-weight: bold;">
                                            OVERDUE (<?php echo abs($days_left); ?> days late)
                                        </span>
                                    <?php else: ?>
                                        <span style="font-size: 0.65rem; color: #fff; background: #28a745; padding: 2px 8px; border-radius: 4px; font-weight: bold;">
                                            PENDING (<?php echo $days_left; ?> days left)
                                        </span>
                                    <?php endif; ?>
                                </div>
                            </td>
                            <td style="padding:15px; text-align:center;">
                                <a href="borrowers.php?return_id=<?php echo $row['id']; ?>&book_id=<?php echo $row['book_id']; ?>" 
                                   onclick="return confirm('Confirm book return?');"
                                   style="text-decoration:none; background: #a72828; color: white; padding: 8px 15px; border-radius: 6px; font-size: 0.75rem; font-weight: bold;">
                                   Return Book
                                </a>
                            </td>
                        </tr>
                    <?php endwhile; ?>
                <?php else: ?>
                    <tr><td colspan="6" style="padding:40px; text-align:center; color:#ccc;">No active borrowers found.</td></tr>
                <?php endif; ?>
            </tbody>
        </table>
    </div>

    <div class="table-card" style="background: #fdfdfd; padding: 25px; border-radius: 12px; border: 1px solid #eee;">
        <h3 style="margin:0 0 20px 0; color: var(--primary-blue); font-size: 1.3rem;">
            <i class="fa fa-history"></i> Books Issued List
        </h3>

        <table style="width:100%; border-collapse: collapse; font-size: 0.85rem;">
            <thead>
                <tr style="background: #f8f9fa; border-bottom: 2px solid #eee;">
                    <th style="padding:12px; text-align:left; color: black;">Student LRN/ID</th>
                    <th style="padding:12px; text-align:left; color: black;">Student Name</th>
                    <th style="padding:12px; text-align:left; color: black;">Book Details</th>
                    <th style="padding:12px; text-align:left; color: black;">Issue Date</th>
                    <th style="padding:12px; text-align:left; color: black;">Returned Date</th>
                    <th style="padding:12px; text-align:center; color: black;">Status</th>
                </tr>
            </thead>
            <tbody>
                <?php if ($history_res && $history_res->num_rows > 0): ?>
                    <?php while ($h_row = $history_res->fetch_assoc()): ?>
                        <tr style="border-bottom: 1px solid #eee; background: white;">
                            <td style="padding:12px;">
                                <div style="font-weight:bold;">#<?php echo htmlspecialchars($h_row['student_id']); ?></div>
                            </td>
                            <td style="padding:12px;">
                                <div style="font-size:1rem; color:#211e1e; font-weight: bold"><?php echo htmlspecialchars($h_row['first_name'] . ' ' . $h_row['last_name']); ?></div>
                            </td>
                            <td style="padding:12px;">
                                <div style="display: flex; align-items: center; gap: 10px;">
                                    <?php 
                                        $img_path = "uploads/" . $h_row['image'];
                                        $display_img = (!empty($h_row['image']) && file_exists($img_path)) ? $img_path : 'assets/no-cover.png';
                                    ?>
                                    <img src="<?php echo $display_img; ?>" style="width:40px; height:55px; object-fit:cover; border-radius:4px; border:1px solid #ddd;">
                                    <div>
                                        <div style="font-weight: 600; color: #211e1e; font-weight: bold"><?php echo htmlspecialchars($h_row['book_borrowed']); ?></div>
                                        <div style="font-size: 0.9rem; color: #211e1e;">by <?php echo htmlspecialchars($h_row['author']); ?></div>
                                        <div style="font-size: 0.75rem; color: #666;">ISBN: <?php echo htmlspecialchars($h_row['isbn'] ?? 'N/A'); ?></div>
                                    </div>
                                </div>
                            </td>
                            <td style="padding:12px; color: #211e1e;">
                                <?php echo date('M d, Y', strtotime($h_row['date_borrowed'])); ?>
                            </td>
                            <td style="padding:12px; font-weight: bold; color: #28a745;">
                                <?php echo date('M d, Y | h:i A', strtotime($h_row['return_date'])); ?>
                            </td>
                            <td style="padding:12px; text-align:center;">
                                <span style="background: #e8f5e9; color: #2e7d32; padding: 4px 10px; border-radius: 4px; font-size: 0.7rem; font-weight: bold;">RETURNED</span>
                            </td>
                        </tr>
                    <?php endwhile; ?>
                <?php else: ?>
                    <tr><td colspan="6" style="padding:30px; text-align:center; color:#bbb;">No history available.</td></tr>
                <?php endif; ?>
            </tbody>
        </table>
    </div>
</div>

<?php include_once "footer.php"; ?>