<?php
require_once '../config/config.php';

if (!isAdmin()) {
    redirect('../login.php');
}

$pageTitle = 'Edit Product';
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

$productId = intval($_GET['id'] ?? 0);
if ($productId <= 0) {
    redirect('products.php');
}

$categories = $db->query("SELECT * FROM categories WHERE status = 'active' ORDER BY name")->fetchAll();

$stmt = $db->prepare("SELECT * FROM products WHERE id = ?");
$stmt->execute([$productId]);
$product = $stmt->fetch();

if (!$product) {
    redirect('products.php');
}

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

            $slug = generateSlug($title);
            $checkSlug = $db->prepare("SELECT id FROM products WHERE slug = ? AND id != ? LIMIT 1");
            $checkSlug->execute([$slug, $productId]);
            if ($checkSlug->fetch()) {
                $slug .= '-' . $productId;
            }

            $fileName = $product['file_path'];
            $fileSize = $product['file_size'];

            if (isset($_FILES['product_file']) && !empty($_FILES['product_file']['name'])) {
                if ($_FILES['product_file']['error'] !== 0) {
                    throw new Exception('Product file upload failed: ' . getUploadErrorMessage($_FILES['product_file']['error']));
                }

                $newFileName = time() . '_' . preg_replace('/[^A-Za-z0-9._-]/', '_', basename($_FILES['product_file']['name']));
                $newFileSize = round($_FILES['product_file']['size'] / 1024 / 1024, 2) . ' MB';

                if (!move_uploaded_file($_FILES['product_file']['tmp_name'], PRODUCTS_PATH . $newFileName)) {
                    throw new Exception('Failed to save product file. Please check folder permissions.');
                }

                if (!empty($product['file_path']) && file_exists(PRODUCTS_PATH . $product['file_path'])) {
                    @unlink(PRODUCTS_PATH . $product['file_path']);
                }

                $fileName = $newFileName;
                $fileSize = $newFileSize;
            }

            $update = $db->prepare("
                UPDATE products
                SET title = ?, slug = ?, description = ?, price = ?, category_id = ?, file_path = ?, file_size = ?, demo_url = ?, status = ?
                WHERE id = ?
            ");
            $update->execute([$title, $slug, $description, $price, $categoryId, $fileName, $fileSize, $demoUrl, $status, $productId]);

            if (!empty($_POST['delete_screenshots']) && is_array($_POST['delete_screenshots'])) {
                $deleteStmt = $db->prepare("SELECT image_path FROM product_screenshots WHERE id = ? AND product_id = ?");
                $removeStmt = $db->prepare("DELETE FROM product_screenshots WHERE id = ? AND product_id = ?");

                foreach ($_POST['delete_screenshots'] as $shotId) {
                    $shotId = intval($shotId);
                    if ($shotId <= 0) continue;

                    $deleteStmt->execute([$shotId, $productId]);
                    $shot = $deleteStmt->fetch();

                    if ($shot) {
                        if (!empty($shot['image_path']) && file_exists(SCREENSHOTS_PATH . $shot['image_path'])) {
                            @unlink(SCREENSHOTS_PATH . $shot['image_path']);
                        }
                        $removeStmt->execute([$shotId, $productId]);
                    }
                }
            }

            if (isset($_FILES['screenshots']) && !empty($_FILES['screenshots']['name'][0])) {
                $insertShot = $db->prepare("INSERT INTO product_screenshots (product_id, image_path, display_order) VALUES (?, ?, ?)");

                $maxOrderStmt = $db->prepare("SELECT COALESCE(MAX(display_order), -1) as max_order FROM product_screenshots WHERE product_id = ?");
                $maxOrderStmt->execute([$productId]);
                $maxOrder = intval($maxOrderStmt->fetch()['max_order']);

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

                    $maxOrder++;
                    $insertShot->execute([$productId, $imageName, $maxOrder]);
                }
            }

            $db->commit();
            $success = 'Product updated successfully!';

            $stmt = $db->prepare("SELECT * FROM products WHERE id = ?");
            $stmt->execute([$productId]);
            $product = $stmt->fetch();
        } catch (Exception $e) {
            $db->rollBack();
            $error = 'Failed to update product: ' . $e->getMessage();
        }
    }
}

$screenshotsStmt = $db->prepare("SELECT * FROM product_screenshots WHERE product_id = ? ORDER BY display_order ASC, id ASC");
$screenshotsStmt->execute([$productId]);
$screenshots = $screenshotsStmt->fetchAll();

include 'includes/header.php';
?>

<div class="container-fluid py-4">
    <div class="d-flex justify-content-between align-items-center mb-4">
        <h1 class="fw-bold">
            <i class="fas fa-edit text-primary"></i> Edit Product
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
            <div class="col-lg-8">
                <div class="admin-form-section">
                    <h5><i class="fas fa-info-circle me-2"></i> Product Details</h5>

                    <div class="form-outline mb-4">
                        <input type="text" id="title" name="title" class="form-control" value="<?php echo htmlspecialchars($product['title']); ?>" required>
                        <label class="form-label" for="title">Product Title *</label>
                    </div>

                    <div class="form-outline mb-4">
                        <textarea id="description" name="description" class="form-control" rows="6" required><?php echo htmlspecialchars($product['description']); ?></textarea>
                        <label class="form-label" for="description">Description *</label>
                    </div>

                    <div class="row g-3 mb-4">
                        <div class="col-md-6">
                            <div class="form-outline">
                                <input type="number" id="price" name="price" class="form-control" step="0.01" min="0" value="<?php echo htmlspecialchars($product['price']); ?>" required>
                                <label class="form-label" for="price">Price (<?php echo getSetting('currency_symbol'); ?>) *</label>
                            </div>
                        </div>
                        <div class="col-md-6">
                            <label class="form-label" for="category_id">Category</label>
                            <select id="category_id" name="category_id" class="form-select">
                                <option value="">Select Category</option>
                                <?php foreach ($categories as $cat): ?>
                                <option value="<?php echo $cat['id']; ?>" <?php echo intval($product['category_id']) === intval($cat['id']) ? 'selected' : ''; ?>>
                                    <?php echo htmlspecialchars($cat['name']); ?>
                                </option>
                                <?php endforeach; ?>
                            </select>
                        </div>
                    </div>

                    <div class="form-outline mb-4">
                        <input type="url" id="demo_url" name="demo_url" class="form-control" value="<?php echo htmlspecialchars($product['demo_url'] ?? ''); ?>">
                        <label class="form-label" for="demo_url">Demo URL (Optional)</label>
                    </div>
                </div>

                <div class="admin-form-section">
                    <h5><i class="fas fa-images me-2"></i> Screenshots</h5>

                    <?php if (!empty($screenshots)): ?>
                    <div class="row g-3 mb-4">
                        <?php foreach ($screenshots as $shot): ?>
                        <div class="col-md-4">
                            <div class="card h-100">
                                <img src="<?php echo BASE_URL . 'uploads/screenshots/' . htmlspecialchars($shot['image_path']); ?>" class="card-img-top" style="height: 160px; object-fit: cover;" alt="Screenshot">
                                <div class="card-body py-2">
                                    <div class="form-check">
                                        <input class="form-check-input" type="checkbox" name="delete_screenshots[]" value="<?php echo $shot['id']; ?>" id="delete_shot_<?php echo $shot['id']; ?>">
                                        <label class="form-check-label small text-danger" for="delete_shot_<?php echo $shot['id']; ?>">
                                            Delete this screenshot
                                        </label>
                                    </div>
                                </div>
                            </div>
                        </div>
                        <?php endforeach; ?>
                    </div>
                    <?php else: ?>
                    <p class="text-muted">No screenshots uploaded yet.</p>
                    <?php endif; ?>

                    <div class="mb-4">
                        <label class="form-label">Add More Screenshots</label>
                        <input type="file" name="screenshots[]" class="form-control" multiple accept="image/*" onchange="handleMultipleFiles(this, 'screenshotPreviews')">
                        <small class="text-muted">Upload JPG, PNG, GIF, or WEBP images</small>
                        <div id="screenshotPreviews" class="row mt-3"></div>
                    </div>
                </div>
            </div>

            <div class="col-lg-4">
                <div class="admin-form-section">
                    <h5><i class="fas fa-file-upload me-2"></i> Product File</h5>

                    <div class="mb-3">
                        <label class="form-label">Replace Product File (Optional)</label>
                        <input type="file" name="product_file" class="form-control" accept=".zip,.pdf,.rar,.7z">
                    </div>

                    <div class="small text-muted mb-3">
                        Current file:
                        <strong><?php echo !empty($product['file_path']) ? htmlspecialchars($product['file_path']) : 'Not uploaded'; ?></strong>
                    </div>
                </div>

                <div class="admin-form-section mt-3">
                    <h5><i class="fas fa-cog me-2"></i> Settings</h5>

                    <div class="mb-4">
                        <label class="form-label">Status</label>
                        <select name="status" class="form-select">
                            <option value="active" <?php echo $product['status'] === 'active' ? 'selected' : ''; ?>>Active</option>
                            <option value="inactive" <?php echo $product['status'] === 'inactive' ? 'selected' : ''; ?>>Inactive</option>
                        </select>
                    </div>

                    <div class="d-grid gap-2">
                        <button type="submit" class="btn btn-primary btn-lg">
                            <i class="fas fa-save me-2"></i> Update Product
                        </button>
                        <a href="products.php" class="btn btn-outline-secondary">
                            <i class="fas fa-times me-2"></i> Cancel
                        </a>
                    </div>
                </div>
            </div>
        </div>
    </form>
</div>

<?php include 'includes/footer.php'; ?>
