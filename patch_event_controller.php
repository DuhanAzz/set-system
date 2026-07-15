<?php
$content = file_get_contents('app/Swim/Controllers/EventProfileController.php');
$content = str_replace("use App\Core\Database;", "use App\Core\Database;\nuse App\Core\UploadService;", $content);

$oldLogic = <<<OLD
            \$targetDir = __DIR__ . "/../../../../public/uploads/logos/";
            \$posterDir = __DIR__ . "/../../../../public/uploads/posters/";
            \$docDir    = __DIR__ . "/../../../../public/uploads/documents/";

            try {
                \$pdo->beginTransaction();
                
                if (!is_dir(\$targetDir)) mkdir(\$targetDir, 0755, true);
                if (!is_dir(\$posterDir)) mkdir(\$posterDir, 0755, true);
                if (!is_dir(\$docDir)) mkdir(\$docDir, 0755, true);
OLD;

$newLogic = <<<NEW
            try {
                \$pdo->beginTransaction();
NEW;

$content = str_replace($oldLogic, $newLogic, $content);

$oldCallLogic = <<<OLD
                \$this->handleImageUpload(\$pdo, 'logo_left', \$targetDir, \$eventId, 'uploads/logos/');
                \$this->handleImageUpload(\$pdo, 'logo_right', \$targetDir, \$eventId, 'uploads/logos/');
                \$this->handleImageUpload(\$pdo, 'poster_file', \$posterDir, \$eventId, 'uploads/posters/', 'poster_image');
                \$this->handleDocumentUpload(\$pdo, 'juknis_file', \$docDir, \$eventId, 'JUKNIS');
                \$this->handleDocumentUpload(\$pdo, 'form_file', \$docDir, \$eventId, 'FORMULIR');
                \$this->handleSponsorsUpload(\$pdo, \$targetDir, \$eventId);
OLD;

$newCallLogic = <<<NEW
                \$this->handleImageUpload(\$pdo, 'logo_left', \$eventId, 'logos');
                \$this->handleImageUpload(\$pdo, 'logo_right', \$eventId, 'logos');
                \$this->handleImageUpload(\$pdo, 'poster_file', \$eventId, 'logos', 'poster_image');
                \$this->handleDocumentUpload(\$pdo, 'juknis_file', \$eventId, 'JUKNIS');
                \$this->handleDocumentUpload(\$pdo, 'form_file', \$eventId, 'FORMULIR');
                \$this->handleSponsorsUpload(\$pdo, \$eventId);
NEW;

$content = str_replace($oldCallLogic, $newCallLogic, $content);

$helpersRegex = '/private function handleImageUpload.*\}\n\n\s*\}\n$/ms';

$newHelpers = <<<NEW
    private function handleImageUpload(\$pdo, \$fileKey, \$eventId, \$folder, \$dbCol = null) {
        if (!\$dbCol) \$dbCol = \$fileKey;
        
        if (isset(\$_FILES[\$fileKey]) && \$_FILES[\$fileKey]['error'] !== UPLOAD_ERR_NO_FILE) {
            try {
                \$newName = UploadService::uploadImage(\$_FILES[\$fileKey], \$folder, 800);
                if (\$newName) {
                    // GC Old file
                    \$stmtOld = \$pdo->prepare("SELECT `\$dbCol` FROM swim_events WHERE id = ?");
                    \$stmtOld->execute([\$eventId]);
                    \$oldPath = \$stmtOld->fetchColumn();
                    if (\$oldPath) UploadService::deleteFile(\$folder, basename(\$oldPath));
                    
                    \$dbSavePath = "uploads/\$folder/" . \$newName;
                    \$pdo->prepare("UPDATE swim_events SET `\$dbCol` = ? WHERE id = ?")->execute([\$dbSavePath, \$eventId]);
                }
            } catch (\Exception \$e) {
                \$_SESSION['swal_type'] = "error";
                \$_SESSION['swal_msg']  = \$e->getMessage();
            }
        }
    }

    private function handleDocumentUpload(\$pdo, \$fileKey, \$eventId, \$kategori) {
        if (isset(\$_FILES[\$fileKey]) && \$_FILES[\$fileKey]['error'] !== UPLOAD_ERR_NO_FILE) {
            try {
                \$fileName = \$_FILES[\$fileKey]['name'];
                \$newName = UploadService::uploadDocument(\$_FILES[\$fileKey], 'documents', 5);
                if (\$newName) {
                    \$dbSavePath = "uploads/documents/" . \$newName;
                    
                    \$stmtCek = \$pdo->prepare("SELECT id, file_path FROM swim_documents WHERE event_id = ? AND kategori = ?");
                    \$stmtCek->execute([\$eventId, \$kategori]);
                    \$exists = \$stmtCek->fetch();
                    
                    if (\$exists) {
                        UploadService::deleteFile('documents', basename(\$exists['file_path']));
                        \$pdo->prepare("UPDATE swim_documents SET judul_file = ?, file_path = ?, created_at = NOW() WHERE id = ?")
                            ->execute([\$fileName, \$dbSavePath, \$exists['id']]);
                    } else {
                        \$pdo->prepare("INSERT INTO swim_documents (event_id, judul_file, file_path, kategori) VALUES (?, ?, ?, ?)")
                            ->execute([\$eventId, \$fileName, \$dbSavePath, \$kategori]);
                    }
                }
            } catch (\Exception \$e) {
                \$_SESSION['swal_type'] = "error";
                \$_SESSION['swal_msg']  = \$e->getMessage();
            }
        }
    }

    private function handleSponsorsUpload(\$pdo, \$eventId) {
        if (isset(\$_FILES['sponsor_files']) && is_array(\$_FILES['sponsor_files']['name'])) {
            \$count = count(\$_FILES['sponsor_files']['name']);
            for (\$i = 0; \$i < \$count; \$i++) {
                if (\$_FILES['sponsor_files']['error'][\$i] !== UPLOAD_ERR_NO_FILE) {
                    try {
                        \$fileArr = [
                            'name' => \$_FILES['sponsor_files']['name'][\$i],
                            'type' => \$_FILES['sponsor_files']['type'][\$i],
                            'tmp_name' => \$_FILES['sponsor_files']['tmp_name'][\$i],
                            'error' => \$_FILES['sponsor_files']['error'][\$i],
                            'size' => \$_FILES['sponsor_files']['size'][\$i],
                        ];
                        \$newName = UploadService::uploadImage(\$fileArr, 'logos', 800);
                        if (\$newName) {
                            \$dbSavePath = "uploads/logos/" . \$newName;
                            \$pdo->prepare("INSERT INTO event_sponsors (event_id, sponsor_name, image_path) VALUES (?, ?, ?)")
                                ->execute([\$eventId, "Sponsor", \$dbSavePath]);
                        }
                    } catch (\Exception \$e) {
                        // Ignore individual sponsor failure to let others upload
                    }
                }
            }
        }
    }
}
NEW;

$content = preg_replace($helpersRegex, $newHelpers, $content);
file_put_contents('app/Swim/Controllers/EventProfileController.php', $content);
