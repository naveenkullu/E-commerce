<?php
require_once '../config/config.php';

if (!isAdmin()) {
    redirect('../login.php');
}

$pageTitle = 'Add Product';
$success = '';
$error = '';

function getUploadErrorMessage($errorCode) {
    $messages = [
        UPLOAD_ERR_INI_SIZE => 'The uploaded file exceeds the server upload_max_filesize limit.',
        UPLOAD_ERR_FORM_SIZE => 'The uploaded file exceeds the form size limit.',
        UPLOAD_ERR_PARTIAL => 'The file was only partially uploaded.',
        UPLOAD_ERR_NO_FILE => 'No file was uploaded.',
        UPLOAD_ERR_NO_TMP_DIR => 'Missing temporary upload folder.',
        UPLOAD_ERR_CANT_WRITE => 'Failed to write file to disk.',
        UPLOAD_ERR_EXTENSION => 'A PHP extension stopped the file upload.'
    ];

    return $messages[$errorCode] ?? 'Unknown upload error.';
}

// Fetch categories
$categories = $db->query("SELECT * FROM categories WHERE status = 'active' ORDER BY name")->fetchAll();

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $title = sanitize($_POST['title'] ?? '');
    $description = sanitize($_POST['description'] ?? '');
    $price = floatval($_POST['price'] ?? 0);
    $categoryId = intval($_POST['category_id'] ?? 0);
    $demoUrl = sanitize($_POST['demo_url'] ?? '');
    $status = $_POST['status'] ?? 'active';
    
    if (empty($title) || empty($description) || $price <= 0) {
        $error = 'Please fill in all required fields';
    } else {
        try {
            $db->beginTransaction();
            
            // Generate slug
            $slug = generateSlug($title);
            
            // Handle product file upload
            $fileName = null;
            $fileSize = null;
            if (isset($_FILES['product_file']) && !empty($_FILES['product_file']['name'])) {
                if ($_FILES['product_file']['error'] !== 0) {
                    throw new Exception('Product file upload failed: ' . getUploadErrorMessage($_FILES['product_file']['error']));
                }

                $fileName = time() . '_' . preg_replace('/[^A-Za-z0-9._-]/', '_', basename($_FILES['product_file']['name']));
                $fileSize = round($_FILES['product_file']['size'] / 1024 / 1024, 2) . ' MB';

                if (!move_uploaded_file($_FILES['product_file']['tmp_name'], PRODUCTS_PATH . $fileName)) {
                    throw new Exception('Failed to save product file. Please check folder permissions.');
                }
            }
            
            // Insert product
            $stmt = $db->prepare("
                INSERT INTO products (title, slug, description, price, category_id, file_path, file_size, demo_url, status)
                VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?)
                RETURNING id
            ");
            $stmt->execute([$title, $slug, $description, $price, $categoryId, $fileName, $fileSize, $demoUrl, $status]);
            $productId = $stmt->fetchColumn();
            
            // Handle screenshots
            if (isset($_FILES['screenshots']) && !empty($_FILES['screenshots']['name'][0])) {
                $stmt = $db->prepare("INSERT INTO product_screenshots (product_id, image_path, display_order) VALUES (?, ?, ?)");
                
                foreach ($_FILES['screenshots']['tmp_name'] as $index => $tmpName) {
                    if (empty($_FILES['screenshots']['name'][$index])) {
                        continue;
                    }

                    if ($_FILES['screenshots']['error'][$index] !== 0) {
                        throw new Exception('Screenshot upload failed for file #' . ($index + 1) . ': ' . getUploadErrorMessage($_FILES['screenshots']['error'][$index]));
                    }

                    $imageInfo = @getimagesize($tmpName);
                    if ($imageInfo === false) {
                        throw new Exception('Invalid image selected for screenshot #' . ($index + 1) . '. Please upload JPG, PNG, GIF, or WEBP image files.');
                    }

                    $imageName = time() . '_' . $index . '_' . preg_replace('/[^A-Za-z0-9._-]/', '_', basename($_FILES['screenshots']['name'][$index]));
                    if (!move_uploaded_file($tmpName, SCREENSHOTS_PATH . $imageName)) {
                        throw new Exception('Failed to save screenshot #' . ($index + 1) . '. Please check folder permissions.');
                    }

                    $stmt->execute([$productId, $imageName, $index]);
                }
            }
            
            $db->commit();
            $success = 'Product added successfully!';
            header("refresh:2;url=products.php");
            
        } catch (Exception $e) {
            $db->rollBack();
            $error = 'Failed to add product: ' . $e->getMessage();
        }
    }
}

include 'includes/header.php';
?>

<div class="container-fluid py-4">
    <div class="d-flex justify-content-between align-items-center mb-4">
        <h1 class="fw-bold">
            <i class="fas fa-plus-circle text-primary"></i> Add New Product
        </h1>
        <a href="products.php" class="btn btn-outline-secondary">
            <i class="fas fa-arrow-left me-2"></i> Back to Products
        </a>
    </div>
    
    <?php if ($error): ?>
    <div class="alert alert-danger alert-dismissible fade show">
        <i class="fas fa-exclamation-circle me-2"></i><?php echo $error; ?>
        <button type="button" class="btn-close" data-mdb-dismiss="alert"></button>
    </div>
    <?php endif; ?>
    
    <?php if ($success): ?>
    <div class="alert alert-success alert-dismissible fade show">
        <i class="fas fa-check-circle me-2"></i><?php echo $success; ?>
        <button type="button" class="btn-close" data-mdb-dismiss="alert"></button>
    </div>
    <?php endif; ?>
    
    <form method="POST" action="" enctype="multipart/form-data">
        <div class="row g-4">
            <!-- Product Details -->
            <div class="col-lg-8">
                <div class="admin-form-section">
                    <h5><i class="fas fa-info-circle me-2"></i> Product Details</h5>
                    
                    <div class="form-outline mb-4">
                        <input type="text" id="title" name="title" class="form-control" required>
                        <label class="form-label" for="title">Product Title *</label>
                    </div>
                    
                    <div class="form-outline mb-4">
                        <textarea id="description" name="description" class="form-control" rows="6" required></textarea>
                        <label class="form-label" for="description">Description *</label>
                    </div>
                    
                    <div class="row g-3 mb-4">
                        <div class="col-md-6">
                            <div class="form-outline">
                                <input type="number" id="price" name="price" class="form-control" step="0.01" min="0" required>
                                <label class="form-label" for="price">Price (<?php echo getSetting('currency_symbol'); ?>) *</label>
                            </div>
                        </div>
                        <div class="col-md-6">
                            <select id="category_id" name="category_id" class="form-select">
                                <option value="">Select Category</option>
                                <?php foreach ($categories as $cat): ?>
                                <option value="<?php echo $cat['id']; ?>"><?php echo htmlspecialchars($cat['name']); ?></option>
                                <?php endforeach; ?>
                            </select>
                            <label class="form-label" for="category_id">Category</label>
                        </div>
                    </div>
                    
                    <div class="form-outline mb-4">
                        <input type="url" id="demo_url" name="demo_url" class="form-control">
                        <label class="form-label" for="demo_url">Demo URL (Optional)</label>
                    </div>
                </div>
                
                <!-- File Uploads -->
                <div class="admin-form-section">
                    <h5><i class="fas fa-upload me-2"></i> Files & Images</h5>
                    
                    <div class="mb-4">
                        <label class="form-label">Product File (ZIP, PDF, etc.)</label>
                        <input type="file" name="product_file" class="form-control" accept=".zip,.pdf,.rar,.7z">
                        <small class="text-muted">Upload the digital product file that customers will download</small>
                    </div>
                    
                    <div class="mb-4">
                        <label class="form-label">Product Screenshots</label>
                        <input type="file" name="screenshots[]" class="form-control" multiple accept="image/*" 
                               onchange="handleMultipleFiles(this, 'screenshotPreviews')">
                        <small class="text-muted">Upload multiple images to showcase your product</small>
                        <div id="screenshotPreviews" class="row mt-3"></div>
                    </div>
                </div>
            </div>
            
            <!-- Sidebar -->
            <div class="col-lg-4">
                <div class="admin-form-section">
                    <h5><i class="fas fa-cog me-2"></i> Settings</h5>
                    
                    <div class="mb-4">
                        <label class="form-label">Status</label>
                        <select name="status" class="form-select">
                            <option value="active">Active</option>
                            <option value="inactive">Inactive</option>
                        </select>
                    </div>
                    
                    <hr>
                    
                    <div class="d-grid gap-2">
                        <button type="submit" class="btn btn-primary btn-lg">
                            <i class="fas fa-save me-2"></i> Save Product
                        </button>
                        <a href="products.php" class="btn btn-outline-secondary">
                            <i class="fas fa-times me-2"></i> Cancel
                        </a>
                    </div>
                </div>
                
                <div class="admin-form-section mt-3">
                    <h6><i class="fas fa-info-circle me-2"></i> Tips</h6>
                    <ul class="small text-muted mb-0">
                        <li>Use clear, descriptive titles</li>
                        <li>Add detailed descriptions</li>
                        <li>Upload high-quality screenshots</li>
                        <li>Set competitive pricing</li>
                        <li>Include demo links if available</li>
                    </ul>
                </div>
            </div>
        </div>
    </form>
</div>

<?php include 'includes/footer.php'; ?>
