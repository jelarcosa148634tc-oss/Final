<?php
require_once "config.php";

if (isset($_GET['fetch_lrn'])) {
    $lrn = $mysqli->real_escape_string($_GET['fetch_lrn']);
    
    // Check if account exists in Credentials
    $res = $mysqli->query("SELECT FirstName, LastName, Gmail FROM tb_StudentCredentials WHERE StudentID = '$lrn' LIMIT 1");
    
    if ($row = $res->fetch_assoc()) {
        // Check if they are already in the actual Registry (Verified)
        $check_verified = $mysqli->query("SELECT student_id FROM students WHERE student_id = '$lrn'");
        $is_verified = ($check_verified->num_rows > 0);

        echo json_encode([
            'success' => true, 
            'fname' => $row['FirstName'], 
            'lname' => $row['LastName'],
            'email' => $row['Gmail'],
            'is_verified' => $is_verified
        ]);
    } else {
        echo json_encode(['success' => false]);
    }
    exit;
}

if (isset($_GET['fetch_lrn'])) {
    $lrn = $mysqli->real_escape_string($_GET['fetch_lrn']);
    // Adjust 'tb_StudentCredentials' and column names to match your actual signup table
    $res = $mysqli->query("SELECT FirstName, LastName FROM tb_StudentCredentials WHERE StudentID = '$lrn' LIMIT 1");
    if ($row = $res->fetch_assoc()) {
        echo json_encode(['success' => true, 'fname' => $row['FirstName'], 'lname' => $row['LastName']]);
    } else {
        echo json_encode(['success' => false]);
    }
    exit;
}

$error_msg = "";
$success_msg = "";

if (isset($_GET['delete_id'])) {
    $del_id = $mysqli->real_escape_string($_GET['delete_id']);
    $mysqli->query("DELETE FROM students WHERE student_id = '$del_id'");
    header("Location: add_student.php?msg=deleted");
    exit;
}

if ($_SERVER["REQUEST_METHOD"] == "POST" && isset($_POST['register_student'])) {
    $sid     = $mysqli->real_escape_string($_POST['student_id']);
    $fname   = $mysqli->real_escape_string($_POST['first_name']);
    $lname   = $mysqli->real_escape_string($_POST['last_name']);
    $grade   = $mysqli->real_escape_string($_POST['grade_level']);
    $strand  = isset($_POST['strand']) ? $mysqli->real_escape_string($_POST['strand']) : 'N/A';
    $section = $mysqli->real_escape_string($_POST['section']);
    $adviser = $mysqli->real_escape_string($_POST['adviser']);
    $contact = $mysqli->real_escape_string($_POST['contact']);
    $email   = $mysqli->real_escape_string($_POST['email']);

    $check_duplicate = $mysqli->query("SELECT * FROM students WHERE student_id = '$sid' OR email = '$email' OR contact_number = '$contact'");
    
    if ($check_duplicate->num_rows > 0) {
        $row = $check_duplicate->fetch_assoc();
        if ($row['student_id'] == $sid) {
            $error_msg = "⚠️ Error: The LRN/ID Number <b>$sid</b> is already registered!";
        } elseif ($row['email'] == $email) {
            $error_msg = "⚠️ Error: The Gmail address <b>$email</b> is already registered!";
        } else {
            $error_msg = "⚠️ Error: The Contact Number <b>$contact</b> is already linked to another student!";
        }
    } else {
        $sql = "INSERT INTO students (student_id, first_name, last_name, grade_level, strand, section, adviser, contact_number, email) 
                VALUES ('$sid', '$fname', '$lname', '$grade', '$strand', '$section', '$adviser', '$contact', '$email')";

        if ($mysqli->query($sql)) {
            header("Location: add_student.php?success=1");
            exit;
        } else {
            $error_msg = "Database Error: " . $mysqli->error;
        }
    }
}

include_once "header.php";

$search = isset($_GET['student_q']) ? $mysqli->real_escape_string($_GET['student_q']) : '';
$query = "SELECT * FROM students";
if (!empty($search)) {
    $query .= " WHERE student_id LIKE '%$search%' OR first_name LIKE '%$search%' OR last_name LIKE '%$search%' OR section LIKE '%$search%'";
}
$query .= " ORDER BY grade_level ASC, section ASC";
$student_list = $mysqli->query($query);
?>

<div class="container" style="padding: 20px;">

    <?php if(isset($_GET['success'])): ?>
        <div style="background: #d4edda; color: #155724; padding: 15px; border-radius: 5px; margin-bottom: 20px; border: 1px solid #c3e6cb;">
            ✅ Student successfully registered to the registry!
        </div>
    <?php endif; ?>

    <?php if(!empty($error_msg)): ?>
        <div style="background: #f8d7da; color: #721c24; padding: 15px; border-radius: 5px; margin-bottom: 20px; border: 1px solid #f5c6cb;">
            <?php echo $error_msg; ?>
        </div>
    <?php endif; ?>

    <div class="card" style="max-width: 900px; margin: auto; margin-bottom: 30px; border-top: 5px solid var(--primary-blue);">
        <h2 style="color: var(--primary-blue); margin-bottom: 20px;">Student Registration</h2>
        
        <form method="POST" id="regForm">
            <div style="display:grid; grid-template-columns: 1fr 1fr 1fr; gap: 25px;">
                
                <div style="grid-column: span 1;">
                    <label style="font-weight:bold; display:block; margin-bottom:8px;">LRN / Student ID#</label>
                    <input type="text" name="student_id" id="student_id" required placeholder="Type LRN to autofill..." onkeyup="lookupLRN(this.value)" style="width:100%; padding:10px; border:1px solid #ccc; border-radius:5px;">
                    <small id="lrn_status" style="display:block; margin-top:3px; font-size:0.75rem;"></small>
                </div>

                <div>
                    <label style="font-weight:bold; display:block; margin-bottom:8px;">First Name</label>
                    <input type="text" name="first_name" id="first_name" required readonly style="width:100%; padding:10px; border:1px solid #ccc; border-radius:5px; background:#f9f9f9; color:#555;" placeholder="Auto-filled">
                </div>

                <div>
                    <label style="font-weight:bold; display:block; margin-bottom:8px;">Last Name</label>
                    <input type="text" name="last_name" id="last_name" required readonly style="width:100%; padding:10px; border:1px solid #ccc; border-radius:5px; background:#f9f9f9; color:#555;" placeholder="Auto-filled">
                </div>

                <div>
                    <label style="font-weight:bold; display:block; margin-bottom:8px;">Grade Level</label>
                    <select name="grade_level" id="grade_level" onchange="toggleStrand()" required style="width:100%; padding:10px; border:1px solid #ccc; border-radius:5px;">
                        <option value="">Select Grade</option>
                        <option value="7">Grade 7</option>
                        <option value="8">Grade 8</option>
                        <option value="9">Grade 9</option>
                        <option value="10">Grade 10</option>
                        <option value="11">Grade 11</option>
                        <option value="12">Grade 12</option>
                    </select>
                </div>

                <div id="strand_container" style="display:none;">
                    <label style="font-weight:bold; display:block; margin-bottom:8px;">Strand</label>
                    <select name="strand" style="width:100%; padding:10px; border:1px solid #ccc; border-radius:5px;">
                        <option value="N/A">Select Strand</option>
                        <optgroup label="Academic Track">
                            <option value="STEM">STEM</option>
                            <option value="HUMSS">HUMSS</option>
                            <option value="ABM">ABM</option>
                            <option value="GAS">GAS</option>
                        </optgroup>
                        <optgroup label="TVL Track">
                            <option value="ICT">ICT</option>
                            <option value="Home Economics">Home Economics</option>
                        </optgroup>
                    </select>
                </div>

                <div>
                    <label style="font-weight:bold; display:block; margin-bottom:8px;">Section</label>
                    <input type="text" name="section" required style="width:100%; padding:10px; border:1px solid #ccc; border-radius:5px;">
                </div>

                <div>
                    <label style="font-weight:bold; display:block; margin-bottom:8px;">Adviser</label>
                    <input type="text" name="adviser" required style="width:100%; padding:10px; border:1px solid #ccc; border-radius:5px;">
                </div>

                <div>
                    <label style="font-weight:bold; display:block; margin-bottom:8px;">Contact Number</label>
                    <input type="text" name="contact" required oninput="this.value = this.value.replace(/[^0-9]/g, '');" placeholder="09123456789" style="width:100%; padding:10px; border:1px solid #ccc; border-radius:5px;">
                </div>

                <div>
                    <label style="font-weight:bold; display:block; margin-bottom:8px;">Gmail Address</label>
                    <input type="email" name="email" id="email" required readonly style="width:100%; padding:10px; border:1px solid #ccc; border-radius:5px; background:#f9f9f9; color:#555;" placeholder="Auto-filled">
                </div>
            
            </div>
            <button type="submit" name="register_student" id="regBtn" class="btn-home" style="width:100%; margin-top:30px; border:none; padding:15px; cursor:pointer; font-weight:bold">Verify & Register Student</button>
        </form>
    </div>

    <div class="card" style="max-width: 1000px; margin: auto; margin-bottom: 20px; background: #eef2f7;">
        <form method="GET" action="add_student.php" style="display: flex; gap: 10px;">
            <input type="text" name="student_q" value="<?php echo htmlspecialchars($search); ?>" 
                   placeholder="Search Student by ID, Name, or Section..." 
                   style="flex: 1; padding: 12px; border: 1px solid #ccc; border-radius: 5px;">
            <button type="submit" class="btn-home" style="padding: 0 25px; border: none; cursor: pointer; font-weight: bold;">Search Registry</button>
            <?php if(!empty($search)): ?>
                <a href="add_student.php" style="padding: 12px; color: #666; text-decoration: none; background: #ddd; border-radius: 5px;">Clear</a>
            <?php endif; ?>
        </form>
    </div>

    <div class="card" style="max-width: 1100px; margin: auto;">
        <h3 style="color: var(--primary-blue); margin-top: 0;">Registered Students List</h3>
        <div style="overflow-x:auto;">
            <table style="width:100%; border-collapse: collapse;">
                <thead>
                    <tr style="background: #f8f9fa; border-bottom: 2px solid #dee2e6;">
                        <th style="padding:12px; text-align:left;">ID/LRN</th>
                        <th style="padding:12px; text-align:left;">Name</th>
                        <th style="padding:12px; text-align:left;">Level & Strand</th>
                        <th style="padding:12px; text-align:left;">Section/Adviser</th>
                        <th style="padding:12px; text-align:left;">Contact & Email</th>
                        <th style="padding:12px; text-align:center;">Action</th>
                    </tr>
                </thead>
                <tbody>
    <?php if ($student_list->num_rows > 0): ?>
        <?php while($s = $student_list->fetch_assoc()): ?>
            <tr style="border-bottom: 1px solid #eee;">
                <td style="padding:12px; font-weight:bold; color: var(--primary-blue);">#<?php echo $s['student_id']; ?></td>
                <td style="padding:12px;"><?php echo htmlspecialchars($s['first_name'] . ' ' . $s['last_name']); ?></td>
                <td style="padding:12px;">Grade <?php echo htmlspecialchars($s['grade_level']); ?> <br><small><?php echo htmlspecialchars($s['strand']); ?></small></td>
                <td style="padding:12px;"><?php echo htmlspecialchars($s['section']); ?> <br><small>Adviser: <?php echo htmlspecialchars($s['adviser']); ?></small></td>
                <td style="padding:12px; font-size: 0.85rem;"><?php echo htmlspecialchars($s['contact_number']); ?><br><?php echo htmlspecialchars($s['email']); ?></td>
                
                <td style="padding:12px; text-align:center;">
                    <a href="edit_student.php?id=<?php echo $s['student_id']; ?>" 
                       style="display:inline-block; padding: 8px 15px; background: #f0f0f0; border-radius: 5px; color: #007bff; text-decoration: none; font-size: 0.9rem; font-weight: bold;">
                       Edit
                    </a>
                </td>
            </tr>
        <?php endwhile; ?>
    <?php else: ?>
        <tr><td colspan="6" style="padding:30px; text-align:center; color:#999;">No matching students found.</td></tr>
    <?php endif; ?>
</tbody>
            </table>
        </div>
    </div>
</div>

<script>
// Toggle Strand visibility
function toggleStrand() {
    var grade = document.getElementById("grade_level").value;
    var strandContainer = document.getElementById("strand_container");
    if (grade == "11" || grade == "12") {
        strandContainer.style.display = "block";
    } else {
        strandContainer.style.display = "none";
    }
}

// Autofill logic via AJAX
function lookupLRN(val) {
    const status = document.getElementById('lrn_status');
    const fname = document.getElementById('first_name');
    const lname = document.getElementById('last_name');
    const email = document.getElementById('email');
    const btn = document.getElementById('regBtn');

    // 1. Immediately clear the status and names when user starts typing
    status.innerHTML = ""; 
    
    if (val.length < 1) {
        fname.value = "";
        lname.value = "";
        email.value = "";
        btn.disabled = true;
        btn.style.opacity = "0.6";
        return;
    }

    // 2. Fetch and update based on result
    fetch('add_student.php?fetch_lrn=' + val)
    .then(response => response.json())
    .then(data => {
        if (data.success) {
            if(data.is_verified) {
                status.innerHTML = "<span style='color:green;'>✔️ Account Found! Account Verified.</span>";
                btn.disabled = true; // Don't allow re-registering
                btn.style.opacity = "0.5";
            } else {
                status.innerHTML = "<span style='color:orange;'>⚠️ Account Found! Unverified Account.</span>";
                btn.disabled = false;
                btn.style.opacity = "1";
            }
            fname.value = data.fname;
            lname.value = data.lname;
            email.value = data.email;
        } else {
            status.innerHTML = "<span style='color:red;'>❌ LRN not found in records.</span>";
            fname.value = ""; lname.value = ""; email.value = "";
            btn.disabled = true;
        }
    });

    

}
</script>

<script>
// This will hide the Green Success or Red Error alert after 3 seconds
setTimeout(function() {
    let alerts = document.querySelectorAll('[style*="background: #d4edda"], [style*="background: #f8d7da"]');
    alerts.forEach(function(alert) {
        alert.style.transition = "opacity 0.5s";
        alert.style.opacity = "0";
        setTimeout(() => alert.remove(), 500);
    });
}, 3000);
</script>

<?php include_once "footer.php"; ?>