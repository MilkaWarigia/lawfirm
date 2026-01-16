<?php
/**
 * DOCUMENT VERSIONS
 * View all versions of a document
 */

require_once '../config/database.php';
require_once '../config/session.php';
requireRole('admin');

$case_no = $_GET['case'] ?? null;
$doc_name = $_GET['name'] ?? '';

if (!$case_no || !$doc_name) {
    header("Location: documents.php");
    exit();
}

try {
    $stmt = $conn->prepare("SELECT d.*, c.CaseName 
                            FROM DOCUMENT d 
                            JOIN `CASE` c ON d.CaseNo = c.CaseNo 
                            WHERE d.CaseNo = ? AND d.DocumentName = ? 
                            ORDER BY d.Version DESC");
    $stmt->execute([$case_no, $doc_name]);
    $versions = $stmt->fetchAll(PDO::FETCH_ASSOC);
    
    if (count($versions) === 0) {
        header("Location: documents.php");
        exit();
    }
} catch(PDOException $e) {
    $error = "Error loading versions: " . $e->getMessage();
}

include 'header.php';
?>

<h2>Document Versions: <?php echo htmlspecialchars($doc_name); ?></h2>
<p><strong>Case:</strong> <?php echo htmlspecialchars($versions[0]['CaseName'] ?? ''); ?></p>

<?php if (isset($error)): ?>
    <div class="alert alert-error"><?php echo htmlspecialchars($error); ?></div>
<?php endif; ?>

<div class="card mt-20">
    <h3>Version History</h3>
    <div class="table-container">
        <table>
            <thead>
                <tr>
                    <th>Version</th>
                    <th>Status</th>
                    <th>File Size</th>
                    <th>Uploaded By</th>
                    <th>Uploaded At</th>
                    <th>Description</th>
                    <th>Actions</th>
                </tr>
            </thead>
            <tbody>
                <?php foreach ($versions as $version): ?>
                    <tr>
                        <td><strong>v<?php echo htmlspecialchars($version['Version']); ?></strong></td>
                        <td>
                            <?php if ($version['IsCurrentVersion']): ?>
                                <span style="color: var(--success-color); font-weight: bold;">Current</span>
                            <?php else: ?>
                                <span style="color: var(--gray);">Old</span>
                            <?php endif; ?>
                        </td>
                        <td><?php echo number_format($version['FileSize'] / 1024, 2); ?> KB</td>
                        <td><?php echo htmlspecialchars($version['UploadedByRole']); ?></td>
                        <td><?php echo date('Y-m-d H:i', strtotime($version['UploadedAt'])); ?></td>
                        <td><?php echo htmlspecialchars($version['Description'] ?? '-'); ?></td>
                        <td>
                            <a href="document_download.php?id=<?php echo $version['DocumentId']; ?>" 
                               class="btn btn-sm btn-success">
                                <i class="fas fa-download"></i> Download
                            </a>
                        </td>
                    </tr>
                <?php endforeach; ?>
            </tbody>
        </table>
    </div>
</div>

<div style="margin-top: 20px;">
    <a href="documents.php" class="btn btn-secondary">
        <i class="fas fa-arrow-left"></i> Back to Documents
    </a>
</div>

<?php include 'footer.php'; ?>
