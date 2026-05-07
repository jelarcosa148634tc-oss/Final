<?php
require_once "config.php";

// 1. HANDLE LOGIC FIRST (Before any HTML is sent)
// Handle Update Logic
if ($_SERVER["REQUEST_METHOD"] == "POST" && isset($_POST['update_student'])) {
    $sid = $_POST['student_id'];
    $grade = $_POST['grade_level'];
    $strand = isset($_POST['strand']) ? $_POST['strand'] : 'N/A';
    $section = $_POST['section'];
    $adviser = $_POST['adviser'];
    $contact = $_POST['contact'];
    $email = $_POST['email'];

    $sql = "UPDATE students SET grade_level='$grade', strand='$strand', section='$section', 
            adviser='$adviser', contact_number='$contact', email='$email' WHERE student_id='$sid'";
    
    if ($mysqli->query($sql)) {
        header("Location: add_student.php?msg=updated");
        exit;
    }
}

// Handle Remove Logic (Transfer)
if (isset($_POST['remove_student'])) {
    $sid = $_POST['student_id'];
    $mysqli->query("DELETE FROM students WHERE student_id = '$sid'");
    header("Location: add_student.php?msg=removed");
    exit;
}

// 2. NOW INCLUDE THE HEADER (After potential redirects)
include_once "header.php";

// Fetch Student Data for display in the form
if (isset($_GET['id'])) {
    $id = $mysqli->real_escape_string($_GET['id']);
    $res = $mysqli->query("SELECT * FROM students WHERE student_id = '$id'");
    $s = $res->fetch_assoc();
}
?>

<div class="container" style="padding:40px; display:flex; justify-content:center;">
    <div class="card" style="width:100%; max-width: 600px; border-top: 5px solid #003366; background:white; padding:30px; border-radius:10px; box-shadow: 0 4px 15px rgba(0,0,0,0.1);">
        <h2 style="margin-top:0; color:#333;">Edit Student Profile</h2>
        <p style="color:#666; font-size:0.9rem;">Update details for <b><?php echo $s['first_name'] . ' ' . $s['last_name']; ?></b> (ID: #<?php echo $s['student_id']; ?>)</p>
        
        <form method="POST" id="editForm">
            <input type="hidden" name="student_id" value="<?php echo $s['student_id']; ?>">
            
            <div style="margin-bottom:15px;">
                <label style="display:block; font-weight:bold; margin-bottom:5px;">Grade Level</label>
                <select name="grade_level" required style="width:100%; padding:10px; border:1px solid #ccc; border-radius:5px;">
                    <?php for($i=7; $i<=12; $i++): ?>
                        <option value="<?php echo $i; ?>" <?php echo ($s['grade_level'] == $i) ? 'selected' : ''; ?>>Grade <?php echo $i; ?></option>
                    <?php endfor; ?>
                </select>
            </div>

            <div style="margin-bottom:15px;">
                <label style="display:block; font-weight:bold; margin-bottom:5px;">Section</label>
                <input type="text" name="section" value="<?php echo htmlspecialchars($s['section']); ?>" required style="width:100%; padding:10px; border:1px solid #ccc; border-radius:5px;">
            </div>

            <div style="margin-bottom:15px;">
                <label style="display:block; font-weight:bold; margin-bottom:5px;">Adviser</label>
                <input type="text" name="adviser" value="<?php echo htmlspecialchars($s['adviser']); ?>" required style="width:100%; padding:10px; border:1px solid #ccc; border-radius:5px;">
            </div>

            <div style="margin-bottom:15px;">
                <label style="display:block; font-weight:bold; margin-bottom:5px;">Contact Number</label>
                <input type="text" name="contact" value="<?php echo htmlspecialchars($s['contact_number']); ?>" style="width:100%; padding:10px; border:1px solid #ccc; border-radius:5px;">
            </div>

            <div style="margin-bottom:25px;">
                <label style="display:block; font-weight:bold; margin-bottom:5px;">Gmail Address</label>
                <input type="email" name="email" value="<?php echo htmlspecialchars($s['email']); ?>" style="width:100%; padding:10px; border:1px solid #ccc; border-radius:5px;">
            </div>

            <div style="display:flex; flex-direction:column; gap:10px;">
                <button type="submit" name="update_student" class="btn-home" style="padding:15px; border:none; border-radius:5px; font-weight:bold; cursor:pointer;">Update Record</button>
                
                <div style="display:flex; gap:10px;">
                    <a href="add_student.php" style="flex:1; text-align:center; padding:12px; background:#eee; color:#333; text-decoration:none; border-radius:5px;">Cancel</a>
                    
                    <button type="submit" name="remove_student" 
                            onclick="return confirm('Are you sure? This will remove the student because they transferred or left.')"
                            style="flex:1; padding:12px; background:#fff; color:#dc3545; border:1px solid #dc3545; border-radius:5px; font-weight:bold; cursor:pointer;">
                        Remove Student
                    </button>
                </div>
            </div>
        </form>
    </div>
</div>

<?php include_once "footer.php"; ?>