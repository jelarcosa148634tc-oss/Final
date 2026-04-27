<?php
require_once "config.php";
$error_msg = "";
$success_msg = "";

// 1. Handle Form Submission (Registration)
if ($_SERVER["REQUEST_METHOD"] == "POST" && isset($_POST['register_student'])) {
    $fname   = $mysqli->real_escape_string($_POST['first_name']);
    $lname   = $mysqli->real_escape_string($_POST['last_name']);
    $course  = $mysqli->real_escape_string($_POST['course']);
    $contact = $mysqli->real_escape_string($_POST['contact']);
    $email   = $mysqli->real_escape_string($_POST['email']);







    // Backend Duplicate Check
    $check_duplicate = $mysqli->query("SELECT * FROM students WHERE email = '$email' OR contact_number = '$contact'");
    
    if ($check_duplicate->num_rows > 0) {
        $row = $check_duplicate->fetch_assoc();
        if ($row['email'] == $email) {
            $error_msg = "⚠️ Error: The Gmail address <b>$email</b> is already registered!";
        } else {
            $error_msg = "⚠️ Error: The Contact Number <b>$contact</b> is already linked to another student!";
        }
    } else {
        $sql = "INSERT INTO students (first_name, last_name, course, contact_number, email) 
                VALUES ('$fname', '$lname', '$course', '$contact', '$email')";

        if ($mysqli->query($sql)) {
            header("Location: add_student.php?success=1");
            exit;
        } else {
            $error_msg = "Database Error: " . $mysqli->error;
        }
    }
}






include_once "header.php";

// 2. Handle Search Logic (Like index.php)
$search = isset($_GET['student_q']) ? $mysqli->real_escape_string($_GET['student_q']) : '';
$query = "SELECT * FROM students";
if (!empty($search)) {
    // Search by ID, First Name, or Last Name
    $query .= " WHERE student_id LIKE '%$search%' OR first_name LIKE '%$search%' OR last_name LIKE '%$search%'";
}
$query .= " ORDER BY student_id ASC";
$student_list = $mysqli->query($query);
?>






<div class="container">
    




    <?php if(!empty($error_msg)): ?>
        <div style="background: #f8d7da; color: #721c24; padding: 15px; border-radius: 5px; margin-bottom: 20px; border: 1px solid #f5c6cb;">
            <?php echo $error_msg; ?>
        </div>
    <?php endif; ?>

    
    
    
    
    
    <div class="card" style="max-width: 800px; margin: auto; margin-bottom: 30px;">
        <h2 style="color: var(--primary-blue); border-bottom: 2px solid var(--accent-gold); padding-bottom: 10px;">Student Registration</h2>
        
        <form method="POST">
            <div style="display:grid; grid-template-columns: 1fr 1fr; gap: 20px;">
                
                
            
            
                <div>
                    <label>First Name</label>
                    <input type="text" name="first_name" required style="width:100%; padding:12px; border:1px solid #ccc; border-radius:5px;">
                </div>
                
                
                
                
                
                <div>
                    <label>Last Name</label>
                    <input type="text" name="last_name" required style="width:100%; padding:12px; border:1px solid #ccc; border-radius:5px;">
                </div>
                
                
                
                
                <div style="grid-column: span 2;">
                    <label>Course / Year Level</label>
                    <input type="text" name="course" required style="width:100%; padding:12px; border:1px solid #ccc; border-radius:5px;">
                </div>
                
                
                
                
                <div>
                    <label>Contact Number (Numbers only)</label>
                    <input type="text" name="contact" required 
                           oninput="this.value = this.value.replace(/[^0-9]/g, '');"
                           placeholder="09123456789"
                           style="width:100%; padding:12px; border:1px solid #ccc; border-radius:5px;">
                </div>
                
                
                
                
                
                <div>
                    <label>Gmail Address</label>
                    <input type="email" name="email" required placeholder="example@gmail.com" style="width:100%; padding:12px; border:1px solid #ccc; border-radius:5px;">
                </div>
            
            
            
            
            </div>
            <button type="submit" name="register_student" class="btn-home" style="width:100%; margin-top:25px; border:none; padding:15px; cursor:pointer; font-weight:bold;">Register Student</button>
        </form>
    </div>

    
    
    
    
    
    <div class="card" style="max-width: 1000px; margin: auto; margin-bottom: 20px; background: #eef2f7;">
        <form method="GET" action="add_student.php" style="display: flex; gap: 10px;">
            <input type="text" name="student_q" value="<?php echo htmlspecialchars($search); ?>" 
                   placeholder="Search Student by ID, Name..." 
                   style="flex: 1; padding: 12px; border: 1px solid #ccc; border-radius: 5px;">
            <button type="submit" class="btn-home" style="padding: 0 25px; border: none; cursor: pointer; font-weight: bold;">Search Registry</button>
            <?php if(!empty($search)): ?>
                <button><a href="add_student.php" style="padding: 12px; color: #666; text-decoration: none;">Clear</a></button>
            <?php endif; ?>
        </form>
    </div>





    
    <div class="card" style="max-width: 1000px; margin: auto;">
        <h3 style="color: var(--primary-blue); margin-top: 0;">Registered Students Registry</h3>
        <table style="width:100%; border-collapse: collapse;">
            <thead>
                <tr style="background: #f8f9fa; border-bottom: 2px solid #dee2e6;">
                    <th style="padding:12px; text-align:left;">ID</th>
                    <th style="padding:12px; text-align:left;">Full Name</th>
                    <th style="padding:12px; text-align:left;">Course</th>
                    <th style="padding:12px; text-align:left;">Email</th>
                    <th style="padding:12px; text-align:left;">Contact Nmber</th>
                </tr>
            </thead>
            <tbody>
                <?php if ($student_list->num_rows > 0): ?>
                    <?php while($s = $student_list->fetch_assoc()): ?>
                        <tr style="border-bottom: 1px solid #eee;">
                            <td style="padding:12px; font-weight:bold; color: var(--primary-blue);">#<?php echo $s['student_id']; ?></td>
                            <td style="padding:12px;"><?php echo htmlspecialchars($s['first_name'] . ' ' . $s['last_name']); ?></td>
                            <td style="padding:12px;"><?php echo htmlspecialchars($s['course']); ?></td>
                            <td style="padding:12px; color: #666; font-size: 0.9rem;"><?php echo htmlspecialchars($s['email']); ?></td>
                            <td style="padding:12px;"><?php echo htmlspecialchars($s['contact_number']); ?></td>
                        </tr>
                    <?php endwhile; ?>
                <?php else: ?>
                    <tr><td colspan="5" style="padding:30px; text-align:center; color:#999;">No matching students found.</td></tr>
                <?php endif; ?>
            </tbody>
        </table>
    </div>
</div>



<?php include_once "footer.php"; ?>