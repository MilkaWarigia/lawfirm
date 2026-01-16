<?php
/**
 * ADMIN - DOCUMENT MANAGEMENT
 * Upload, download, and manage case documents with categories and version control
 */

require_once '../config/database.php';
require_once '../config/session.php';
requireRole('admin');

$message = "";
$error = "";

// Create uploads directory if it doesn't exist
$upload_dir = '../uploads/documents/';
if (!file_exists($upload_dir)) {
    mkdir($upload_dir, 0777, true);
}

// Handle document upload
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['upload'])) {
    $case_no = $_POST['case_no'] ?? null;
    $document_name = trim($_POST['document_name'] ?? '');
    $category = trim($_POST['category'] ?? '');
    $description = trim($_POST['description'] ?? '');
    
    if (empty($case_no) || empty($document_name) || empty($category)) {
        $error = "Please fill in all required fields";
    } elseif (!isset($_FILES['document_file']) || $_FILES['document_file']['error'] !== UPLOAD_ERR_OK) {
        $error = "Please select a file to upload";
    } else {
        $file = $_FILES['document_file'];
        $file_name = $file['name'];
        $file_size = $file['size'];
        $file_tmp = $file['tmp_name'];
        $file_type = $file['type'];
        
        // Get file extension
        $file_ext = strtolower(pathinfo($file_name, PATHINFO_EXTENSION));
        
        // Allowed file types
        $allowed_extensions = ['pdf', 'doc', 'docx', 'txt', 'jpg', 'jpeg', 'png', 'xls', 'xlsx'];
        
        if (!in_array($file_ext, $allowed_extensions)) {
            $error = "File type not allowed. Allowed types: " . implode(', ', $allowed_extensions);
        } elseif ($file_size > 10485760) { // 10MB limit
            $error = "File size exceeds 10MB limit";
        } else {
            // Generate unique filename
            $unique_name = uniqid() . '_' . time() . '.' . $file_ext;
            $file_path = $upload_dir . $unique_name;
            
            if (move_uploaded_file($file_tmp, $file_path)) {
                try {
                    // Get current version number
                    $stmt = $conn->prepare("SELECT MAX(Version) as max_version FROM DOCUMENT WHERE CaseNo = ? AND DocumentName = ?");
                    $stmt->execute([$case_no, $document_name]);
                    $result = $stmt->fetch(PDO::FETCH_ASSOC);
                    $new_version = ($result['max_version'] ?? 0) + 1;
                    
                    // Mark old versions as not current
                    $stmt = $conn->prepare("UPDATE DOCUMENT SET IsCurrentVersion = FALSE WHERE CaseNo = ? AND DocumentName = ?");
                    $stmt->execute([$case_no, $document_name]);
                    
                    // Insert new document version
                    $stmt = $conn->prepare("INSERT INTO DOCUMENT (CaseNo, DocumentName, DocumentCategory, FilePath, FileSize, FileType, UploadedBy, UploadedByRole, Version, IsCurrentVersion, Description) VALUES (?, ?, ?, ?, ?, ?, ?, 'admin', ?, TRUE, ?)");
                    $stmt->execute([$case_no, $document_name, $category, $file_path, $file_size, $file_type, $_SESSION['user_id'], $new_version, $description]);
                    
                    $message = "Document uploaded successfully (Version {$new_version})";
                } catch(PDOException $e) {
                    unlink($file_path); // Delete file if database insert fails
                    $error = "Error uploading document: " . $e->getMessage();
                }
            } else {
                $error = "Error moving uploaded file";
            }
        }
    }
}

// Handle document delete
if (isset($_GET['delete']) && is_numeric($_GET['delete'])) {
    try {
        $stmt = $conn->prepare("SELECT FilePath FROM DOCUMENT WHERE DocumentId = ?");
        $stmt->execute([$_GET['delete']]);
        $doc = $stmt->fetch(PDO::FETCH_ASSOC);
        
        if ($doc) {
            // Delete file
            if (file_exists($doc['FilePath'])) {
                unlink($doc['FilePath']);
            }
            
            // Delete database record
            $stmt = $conn->prepare("DELETE FROM DOCUMENT WHERE DocumentId = ?");
            $stmt->execute([$_GET['delete']]);
            $message = "Document deleted successfully";
        }
    } catch(PDOException $e) {
        $error = "Error deleting document: " . $e->getMessage();
    }
}

// Get all cases for dropdown
try {
    $stmt = $conn->query("SELECT CaseNo, CaseName FROM `CASE` ORDER BY CaseName");
    $cases = $stmt->fetchAll(PDO::FETCH_ASSOC);
} catch(PDOException $e) {
    $error = "Error loading cases: " . $e->getMessage();
}

// Get all documents
try {
    $stmt = $conn->query("SELECT d.*, c.CaseName 
                          FROM DOCUMENT d 
                          JOIN `CASE` c ON d.CaseNo = c.CaseNo 
                          ORDER BY d.UploadedAt DESC");
    $documents = $stmt->fetchAll(PDO::FETCH_ASSOC);
} catch(PDOException $e) {
    $error = "Error loading documents: " . $e->getMessage();
}

// Document categories
$categories = ['Contract', 'Evidence', 'Court Filing', 'Correspondence', 'Report', 'Other'];

include 'header.php';
?>

<h2>Document Management</h2>

<?php if ($message): ?>
    <div class="alert alert-success"><?php echo htmlspecialchars($message); ?></div>
<?php endif; ?>

<?php if ($error): ?>
    <div class="alert alert-error"><?php echo htmlspecialchars($error); ?></div>
<?php endif; ?>

<div class="form-container">
    <h3>Upload Document</h3>
    <form method="POST" action="" enctype="multipart/form-data">
        <input type="hidden" name="upload" value="1">
        
        <div class="form-group">
            <label for="case_no">Case *</label>
            <select id="case_no" name="case_no" required>
                <option value="">-- Select Case --</option>
                <?php foreach ($cases as $case): ?>
                    <option value="<?php echo htmlspecialchars($case['CaseNo']); ?>">
                        <?php echo htmlspecialchars($case['CaseName']); ?>
                    </option>
                <?php endforeach; ?>
            </select>
        </div>
        
        <div class="form-group">
            <label for="document_name">Document Name *</label>
            <input type="text" id="document_name" name="document_name" required 
                   placeholder="e.g., Contract Agreement, Evidence Photo">
        </div>
        
        <div class="form-group">
            <label for="category">Category *</label>
            <select id="category" name="category" required>
                <option value="">-- Select Category --</option>
                <?php foreach ($categories as $cat): ?>
                    <option value="<?php echo htmlspecialchars($cat); ?>">
                        <?php echo htmlspecialchars($cat); ?>
                    </option>
                <?php endforeach; ?>
            </select>
        </div>
        
        <div class="form-group">
            <label for="document_file">File * (Max 10MB)</label>
            <input type="file" id="document_file" name="document_file" required 
                   accept=".pdf,.doc,.docx,.txt,.jpg,.jpeg,.png,.xls,.xlsx">
        </div>
        
        <div class="form-group">
            <label for="description">Description</label>
            <textarea id="description" name="description" rows="3"></textarea>
        </div>
        
        <div class="form-actions">
            <button type="submit" class="btn btn-primary">Upload Document</button>
        </div>
    </form>
</div>

<div class="card mt-20">
    <h3>All Documents</h3>
    <div class="table-container">
        <table>
            <thead>
                <tr>
                    <th>Document Name</th>
                    <th>Case</th>
                    <th>Category</th>
                    <th>Version</th>
                    <th>File Size</th>
                    <th>Uploaded By</th>
                    <th>Uploaded At</th>
                    <th>Actions</th>
                </tr>
            </thead>
            <tbody>
                <?php if (isset($documents) && count($documents) > 0): ?>
                    <?php foreach ($documents as $doc): ?>
                        <tr>
                            <td><?php echo htmlspecialchars($doc['DocumentName']); ?>
                                <?php if ($doc['IsCurrentVersion']): ?>
                                    <span style="color: var(--success-color); font-size: 12px;">(Current)</span>
                                <?php endif; ?>
                            </td>
                            <td><?php echo htmlspecialchars($doc['CaseName']); ?></td>
                            <td><?php echo htmlspecialchars($doc['DocumentCategory']); ?></td>
                            <td>v<?php echo htmlspecialchars($doc['Version']); ?></td>
                            <td><?php echo number_format($doc['FileSize'] / 1024, 2); ?> KB</td>
                            <td><?php echo htmlspecialchars($doc['UploadedByRole']); ?></td>
                            <td><?php echo date('Y-m-d H:i', strtotime($doc['UploadedAt'])); ?></td>
                            <td>
                                <a href="document_download.php?id=<?php echo $doc['DocumentId']; ?>" 
                                   class="btn btn-sm btn-success">
                                    <i class="fas fa-download"></i> Download
                                </a>
                                <a href="document_versions.php?case=<?php echo $doc['CaseNo']; ?>&name=<?php echo urlencode($doc['DocumentName']); ?>" 
                                   class="btn btn-sm btn-info">
                                    <i class="fas fa-history"></i> Versions
                                </a>
                                <a href="?delete=<?php echo $doc['DocumentId']; ?>" 
                                   class="btn btn-sm btn-danger"
                                   onclick="return confirm('Are you sure you want to delete this document?');">
                                    <i class="fas fa-trash"></i> Delete
                                </a>
                            </td>
                        </tr>
                    <?php endforeach; ?>
                <?php else: ?>
                    <tr>
                        <td colspan="8" class="empty-state">No documents found</td>
                    </tr>
                <?php endif; ?>
            </tbody>
        </table>
    </div>
</div>

<?php include 'footer.php'; ?>
