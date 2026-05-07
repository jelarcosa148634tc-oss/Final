<?php
require_once "config.php";

if (isset($_GET['id'])) {
    $id = intval($_GET['id']);
    $res = $mysqli->query("SELECT * FROM books WHERE id = $id");
    $book = $res->fetch_assoc();
} else {
    header("Location: books.php");
    exit;
}

if ($_SERVER["REQUEST_METHOD"] == "POST" && isset($_POST['update_inventory'])) {
    $new_stocks = intval($_POST['stocks']);
    $id = intval($_POST['book_id']);
    
    // --- PHOTO UPDATE LOGIC ---
    $image_query = ""; 
    if (isset($_FILES['book_image']) && $_FILES['book_image']['error'] == 0) {
        $target_dir = "uploads/";
        $extension = pathinfo($_FILES["book_image"]["name"], PATHINFO_EXTENSION);
        $image_name = time() . "_" . $id . "." . $extension; 
        $target_file = $target_dir . $image_name;

        if (move_uploaded_file($_FILES["book_image"]["tmp_name"], $target_file)) {
            // Delete old photo if it exists to save space
            if (!empty($book['image']) && file_exists("uploads/" . $book['image'])) {
                unlink("uploads/" . $book['image']);
            }
            $image_query = ", image = '$image_name'";
        }
    }

    // Updated Query: Now updates stocks AND optionally the image
    $mysqli->query("UPDATE books SET stocks = $new_stocks $image_query WHERE id = $id");
    header("Location: books.php?msg=updated");
    exit;
}

include_once "header.php";
?>

<div class="container" style="display:flex; justify-content:center; align-items:center; min-height: 70vh; padding: 20px;">
    <div class="card" style="width:100%; max-width: 500px; border-top: 5px solid var(--accent-gold); background: white; padding: 25px; border-radius: 8px; box-shadow: 0 4px 15px rgba(0,0,0,0.1);">
        <h2 style="margin-top:0; color: #333;">Update Book Details</h2>
        
        <div style="background: #f8f9fa; padding: 15px; border-radius: 8px; margin-bottom: 20px;">
            <p style="margin:0; font-size:0.8rem; color:#666;">Current Book:</p>
            <h3 style="margin:5px 0; color: var(--primary-blue);"><?php echo htmlspecialchars($book['title']); ?></h3>
            <span style="font-family:monospace; background:#eee; padding:2px 5px; border-radius:4px; font-size: 0.85rem;">ISBN: <?php echo htmlspecialchars($book['isbn']); ?></span>
        </div>

        <form method="POST" enctype="multipart/form-data">
            <input type="hidden" name="book_id" value="<?php echo $book['id']; ?>">
            
            <div style="margin-bottom: 25px; text-align: center; border-bottom: 1px solid #eee; padding-bottom: 20px;">
                <label style="display:block; margin-bottom:10px; font-weight:bold; text-align: left;">Book Cover Image</label>
                
                <div id="preview-container" style="margin-bottom: 15px;">
                    <?php if (!empty($book['image']) && file_exists("uploads/" . $book['image'])): ?>
                        <img id="edit-preview" src="uploads/<?php echo $book['image']; ?>" style="width: 120px; height: 170px; object-fit: cover; border-radius: 5px; border: 1px solid #ddd; box-shadow: 0 2px 8px rgba(0,0,0,0.1);">
                    <?php else: ?>
                        <div id="placeholder" style="width: 120px; height: 170px; background: #eee; margin: 0 auto; display: flex; align-items: center; justify-content: center; border-radius: 5px; color: #999;">
                            <i class="fa fa-book" style="font-size: 2rem;"></i>
                        </div>
                    <?php endif; ?>
                </div>

                <input type="file" name="book_image" id="file-input" accept="image/*" style="font-size: 0.8rem;" onchange="previewImage(event)">
                <p style="font-size: 0.7rem; color: #888; margin-top: 5px;">Click to change current cover photo</p>
            </div>

            <div style="margin-bottom:20px;">
                <label style="display:block; margin-bottom:8px; font-weight:bold;">Total Copies Available</label>
                <div style="display:flex; align-items:center; gap:10px;">
                    <input type="number" name="stocks" value="<?php echo $book['stocks']; ?>" min="0" 
                           style="flex:1; padding:12px; font-size:1.1rem; border:1px solid #ccc; border-radius:5px; text-align:center;">
                    <span style="color:#666;">Units</span>
                </div>
            </div>

            <div style="display:flex; gap:10px; margin-top:30px;">
                <a href="books.php" style="flex:1; text-align:center; padding:12px; background:#eee; color:#333; text-decoration:none; border-radius:5px; font-weight: bold;">Cancel</a>
                <button type="submit" name="update_inventory" class="btn-home" 
                        style="flex:2; padding:12px; border:none; border-radius:5px; font-weight:bold; cursor:pointer;">Save Changes</button>
            </div>
        </form>
    </div>
</div>

<script>
// Live preview for when you pick a new image
function previewImage(event) {
    const reader = new FileReader();
    reader.onload = function() {
        let output = document.getElementById('edit-preview');
        // If there was no image before, we need to create the img tag
        if (!output) {
            document.getElementById('preview-container').innerHTML = '<img id="edit-preview" style="width: 120px; height: 170px; object-fit: cover; border-radius: 5px; border: 1px solid #ddd; box-shadow: 0 2px 8px rgba(0,0,0,0.1);">';
            output = document.getElementById('edit-preview');
        }
        output.src = reader.result;
    }
    reader.readAsDataURL(event.target.files[0]);
}
</script>

<?php include_once "footer.php"; ?>