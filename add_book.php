<?php
require_once "config.php";

$error_msg = "";
$success_msg = "";

if ($_SERVER["REQUEST_METHOD"] == "POST" && isset($_POST['add_book'])) {
    $isbn   = $mysqli->real_escape_string($_POST['isbn']);
    $title  = $mysqli->real_escape_string($_POST['title']);
    $author = $mysqli->real_escape_string($_POST['author']);
    $genre  = $mysqli->real_escape_string($_POST['genre']);
    $stocks = intval($_POST['stocks']);
    $format = $mysqli->real_escape_string($_POST['format']);

    // --- PHOTO UPLOAD ---
    $image_name = null;
    if (isset($_FILES['book_image']) && $_FILES['book_image']['error'] == 0) {
        $target_dir = "uploads/";
        if (!is_dir($target_dir)) mkdir($target_dir, 0777, true);
        
        $extension = pathinfo($_FILES["book_image"]["name"], PATHINFO_EXTENSION);
        $image_name = time() . "_" . $isbn . "." . $extension; // Unique name
        $target_file = $target_dir . $image_name;

        if (!move_uploaded_file($_FILES["book_image"]["tmp_name"], $target_file)) {
            $error_msg = "Error uploading photo.";
        }
    }

    // Check if ISBN already exists
    $check_isbn = $mysqli->query("SELECT id FROM books WHERE isbn = '$isbn'");
    
    if ($check_isbn->num_rows > 0) {
        $error_msg = "⚠️ Error: A book with ISBN <b>$isbn</b> already exists!";
    } elseif (empty($error_msg)) {
        // SQL Command (Removed location, publisher, and pub_year)
        $sql = "INSERT INTO books (isbn, title, author, genre, stocks, format, image, status) 
                VALUES ('$isbn', '$title', '$author', '$genre', $stocks, '$format', '$image_name', 'Available')";

        if ($mysqli->query($sql)) {
            header("Location: books.php?success=1");
            exit;
        } else {
            $error_msg = "Database Error: " . $mysqli->error;
        }
    }
}

include_once "header.php";
?>

<div class="container">
    <div class="card" style="max-width: 900px; margin: auto;">
        <h2 style="color: var(--primary-blue); border-bottom: 2px solid var(--accent-gold); padding-bottom: 10px;">Catalog New Book</h2>

        <?php if(!empty($error_msg)): ?>
            <div style="background: #f8d7da; color: #721c24; padding: 15px; border-radius: 5px; margin-bottom: 20px; border: 1px solid #f5c6cb;">
                <?php echo $error_msg; ?>
            </div>
        <?php endif; ?>

        <form method="POST" enctype="multipart/form-data">
            <div style="display: flex; gap: 30px;">
                
                <div style="flex: 1; text-align: center; border-right: 1px solid #eee; padding-right: 30px;">
                    <label style="font-weight: bold; display: block; margin-bottom: 10px;">Book Cover Photo</label>
                    <div id="photo-preview-container" style="width: 200px; height: 280px; background: #f0f0f0; border: 2px dashed #ccc; margin: 0 auto 15px; display: flex; align-items: center; justify-content: center; overflow: hidden; border-radius: 8px;">
                        <img id="preview-img" src="#" alt="Preview" style="display: none; width: 100%; height: 100%; object-fit: cover;">
                        <span id="preview-text">No Image Selected</span>
                    </div>
                    <input type="file" name="book_image" id="book_image" accept="image/*" style="font-size: 0.8rem;" onchange="previewFile()">
                    <p style="font-size: 0.75rem; color: #888; margin-top: 10px;">(Supported: JPG, PNG. Max 2MB)</p>
                </div>

                <div style="flex: 2;">
                    <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 15px;">
                        <div style="grid-column: span 2;">
                            <label>Book Title</label>
                            <input type="text" name="title" required style="width:100%; padding:10px; border:1px solid #ddd; border-radius:4px;">
                        </div>
                        <div>
                            <label>ISBN</label>
                            <input type="text" name="isbn" required placeholder="000-000-000" style="width:100%; padding:10px; border:1px solid #ddd; border-radius:4px;">
                        </div>
                        <div>
                            <label>Author</label>
                            <input type="text" name="author" required style="width:100%; padding:10px; border:1px solid #ddd; border-radius:4px;">
                        </div>
                        <div style="grid-column: span 2;">
                            <label>Genre</label>
                            <select name="genre" style="width:100%; padding:10px; border:1px solid #ddd; border-radius:4px;">
                                
                                <option></option>

                                <option>ABM (Accountancy, Business, and Management)</option>

                                <option>Araling Panlipunan (Social Studies)</option>

                                <option>Arts & Design</option>

                                <option>Business & Economics</option>

                                <option>Civics & Government</option>

                                <option>Computers & Technology</option>

                                <option>Contemporary Philippine Arts</option>

                                <option>Creative Nonfiction(21st-century literary)</option>

                                <option>English for Academic and Professional Purposes (EAPP)</option>

                                <option>Fiction</option>

                                <option>Filipino</option>

                                <option>GAS (General Academic Strand)</option>

                                <option>General</option>

                                <option>HUMSS (Humanities and Social Sciences)</option>

                                <option>Health & Life Skills</option>

                                <option>History</option>

                                <option>Komunikasyon at Pananaliksik</option>

                                <option>MAPEH</option>

                                <option>Mathematics</option>

                                <option>Media and Information Literacy(MIL)</option>

                                <option>Practical Research</option>

                                <option>STEM (Science, Technology, Engineering, and Mathematics)</option>

                                <option>Science</option>

                                <option>TVL (Technical-Vocational-Livelihood)</option>

                                <option>Technology</option>
                                </select>
                        </div>
                        <div>
                            <label>Stocks</label>
                            <input type="number" name="stocks" value="1" min="1" style="width:100%; padding:10px; border:1px solid #ddd; border-radius:4px;">
                        </div>
                        <div>
                            <label>Format</label>
                            <select name="format" style="width:100%; padding:10px; border:1px solid #ddd; border-radius:4px;">
                                <optgroup label="Physical Prints">

                                    <option value="Hardcover">Hardcover</option>

                                    <option value="Paperback">Paperback</option>

                                    <option value="Spiral Bound">Spiral Bound (Manuals/Workbooks)</option>

                                </optgroup>

                                <optgroup label="Digital Formats">

                                    <option value="eBook (PDF)">eBook (PDF)</option>

                                    <option value="eBook (EPUB)">eBook (EPUB)</option>

                                    <option value="Digital Module">Learning Module (DepEd/OER)</option>

                                </optgroup>

                                <optgroup label="Other Media">

                                    <option value="Audiobook">Audiobook</option>

                                    <option value="Periodical">Journal / Magazine</option>

                                </optgroup>
                            </select>
                        </div>
                    </div>
                </div>
            </div>

            <div style="margin-top: 30px; border-top: 1px solid #eee; padding-top: 20px; display: flex; justify-content: flex-end; gap: 10px;">
                <a href="books.php" style="padding: 12px 25px; text-decoration: none; color: #666; background: #eee; border-radius: 4px;">Cancel</a>
                <button type="submit" name="add_book" class="btn-home" style="padding: 12px 40px; border: none; border-radius: 4px; font-weight: bold; cursor: pointer;">Save Book to Inventory</button>
            </div>
        </form>
    </div>
</div>

<script>
// JavaScript for Live Photo Preview
function previewFile() {
    const preview = document.getElementById('preview-img');
    const text = document.getElementById('preview-text');
    const file = document.getElementById('book_image').files[0];
    const reader = new FileReader();

    reader.onloadend = function () {
        preview.src = reader.result;
        preview.style.display = "block";
        text.style.display = "none";
    }

    if (file) {
        reader.readAsDataURL(file);
    } else {
        preview.src = "";
        preview.style.display = "none";
        text.style.display = "block";
    }
}
</script>

<?php include_once "footer.php"; ?>