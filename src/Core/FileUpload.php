<?php
/**
 * Universal File Upload Handler
 * 
 * Provides secure file upload functionality with configurable
 * size limits, file type restrictions, and storage options.
 */

namespace App\Core;

class FileUpload
{
    /**
     * Default configuration
     */
    private array $config = [
        'max_size' => 5242880, // 5 MB default
        'allowed_types' => ['pdf', 'jpg', 'jpeg', 'png', 'gif'],
        'allowed_mimes' => [
            'application/pdf',
            'image/jpeg',
            'image/png',
            'image/gif'
        ],
        'upload_dir' => null,
        'create_subdirs' => true, // Create year/month subdirectories
        'filename_strategy' => 'hash', // 'original', 'hash', 'timestamp'
        'overwrite' => false
    ];

    private array $errors = [];
    private ?string $uploadedFile = null;

    /**
     * Constructor
     * 
     * @param array $config Configuration options to override defaults
     */
    public function __construct(array $config = [])
    {
        $this->config = array_merge($this->config, $config);
        
        // Set default upload directory if not specified
        if ($this->config['upload_dir'] === null) {
            $this->config['upload_dir'] = dirname(dirname(__DIR__)) . '/uploads';
        }
        
        // Ensure upload directory exists
        $this->ensureUploadDirectory();
    }

    /**
     * Upload a file
     * 
     * @param array $file The file from $_FILES array
     * @param string|null $customName Optional custom filename (without extension)
     * @return string|false The path to uploaded file on success, false on failure
     */
    public function upload(array $file, ?string $customName = null)
    {
        $this->errors = [];
        $this->uploadedFile = null;

        // Validate file array structure
        if (!$this->validateFileArray($file)) {
            return false;
        }

        // Check for upload errors
        if ($file['error'] !== UPLOAD_ERR_OK) {
            $this->addError($this->getUploadErrorMessage($file['error']));
            return false;
        }

        // Validate file size
        if (!$this->validateFileSize($file['size'])) {
            return false;
        }

        // Validate file type
        if (!$this->validateFileType($file)) {
            return false;
        }

        // Validate actual file content (security check)
        if (!$this->validateFileContent($file['tmp_name'], $file['type'])) {
            return false;
        }

        // Generate filename
        $filename = $this->generateFilename($file['name'], $customName);

        // Determine target directory
        $targetDir = $this->getTargetDirectory();

        // Full target path
        $targetPath = $targetDir . '/' . $filename;

        // Check if file exists and overwrite is disabled
        if (!$this->config['overwrite'] && file_exists($targetPath)) {
            $this->addError('Eine Datei mit diesem Namen existiert bereits');
            return false;
        }

        // Move uploaded file
        if (!move_uploaded_file($file['tmp_name'], $targetPath)) {
            $this->addError('Fehler beim Speichern der Datei');
            return false;
        }

        // Set permissions
        chmod($targetPath, 0644);

        $this->uploadedFile = $targetPath;
        return $targetPath;
    }

    /**
     * Upload multiple files
     * 
     * @param array $files Array from $_FILES with multiple files
     * @return array Array of uploaded file paths
     */
    public function uploadMultiple(array $files): array
    {
        $uploaded = [];
        
        // Handle both single and multiple file formats
        if (isset($files['name']) && is_array($files['name'])) {
            // Multiple files in single input
            $fileCount = count($files['name']);
            for ($i = 0; $i < $fileCount; $i++) {
                $file = [
                    'name' => $files['name'][$i],
                    'type' => $files['type'][$i],
                    'tmp_name' => $files['tmp_name'][$i],
                    'error' => $files['error'][$i],
                    'size' => $files['size'][$i]
                ];
                
                $result = $this->upload($file);
                if ($result !== false) {
                    $uploaded[] = $result;
                }
            }
        }
        
        return $uploaded;
    }

    /**
     * Delete an uploaded file
     * 
     * @param string $filepath Path to the file
     * @return bool True on success, false on failure
     */
    public function delete(string $filepath): bool
    {
        if (!file_exists($filepath)) {
            $this->addError('Datei nicht gefunden');
            return false;
        }

        // Security: only delete files within upload directory
        $realPath = realpath($filepath);
        $uploadDir = realpath($this->config['upload_dir']);
        
        if ($realPath === false || $uploadDir === false) {
            $this->addError('Ungültiger Dateipfad');
            return false;
        }
        
        // Use str_starts_with for PHP 8+ or manual check for compatibility
        if (function_exists('str_starts_with')) {
            if (!str_starts_with($realPath, $uploadDir . DIRECTORY_SEPARATOR)) {
                $this->addError('Ungültiger Dateipfad');
                return false;
            }
        } else {
            // Fallback for PHP < 8.0
            if (substr($realPath, 0, strlen($uploadDir . DIRECTORY_SEPARATOR)) !== $uploadDir . DIRECTORY_SEPARATOR) {
                $this->addError('Ungültiger Dateipfad');
                return false;
            }
        }

        if (!unlink($filepath)) {
            $this->addError('Fehler beim Löschen der Datei');
            return false;
        }

        return true;
    }

    /**
     * Get file information
     * 
     * @param string $filepath Path to the file
     * @return array|false File information or false
     */
    public function getFileInfo(string $filepath)
    {
        if (!file_exists($filepath)) {
            return false;
        }

        return [
            'name' => basename($filepath),
            'size' => filesize($filepath),
            'type' => mime_content_type($filepath),
            'extension' => strtolower(pathinfo($filepath, PATHINFO_EXTENSION)),
            'path' => $filepath,
            'url' => $this->getFileUrl($filepath),
            'created' => filectime($filepath),
            'modified' => filemtime($filepath)
        ];
    }

    /**
     * Get relative URL for a file
     * 
     * @param string $filepath Absolute file path
     * @return string Relative URL
     */
    public function getFileUrl(string $filepath): string
    {
        $uploadDir = realpath($this->config['upload_dir']);
        $filePath = realpath($filepath);
        
        if ($filePath === false || $uploadDir === false) {
            return '';
        }
        
        // Security check: ensure file is within upload directory
        if (function_exists('str_starts_with')) {
            if (!str_starts_with($filePath, $uploadDir . DIRECTORY_SEPARATOR)) {
                return '';
            }
        } else {
            // Fallback for PHP < 8.0
            if (substr($filePath, 0, strlen($uploadDir . DIRECTORY_SEPARATOR)) !== $uploadDir . DIRECTORY_SEPARATOR) {
                return '';
            }
        }
        
        $relativePath = substr($filePath, strlen($uploadDir) + 1);
        return '/uploads/' . str_replace('\\', '/', $relativePath);
    }

    /**
     * Get errors
     * 
     * @return array Array of error messages
     */
    public function getErrors(): array
    {
        return $this->errors;
    }

    /**
     * Get last uploaded file path
     * 
     * @return string|null
     */
    public function getUploadedFile(): ?string
    {
        return $this->uploadedFile;
    }

    /**
     * Validate file array structure
     */
    private function validateFileArray(array $file): bool
    {
        $required = ['name', 'type', 'tmp_name', 'error', 'size'];
        
        foreach ($required as $key) {
            if (!isset($file[$key])) {
                $this->addError('Ungültiges Datei-Array');
                return false;
            }
        }
        
        return true;
    }

    /**
     * Validate file size
     */
    private function validateFileSize(int $size): bool
    {
        if ($size > $this->config['max_size']) {
            $maxMB = round($this->config['max_size'] / 1048576, 2);
            $this->addError("Datei zu groß (max. {$maxMB} MB)");
            return false;
        }
        
        if ($size === 0) {
            $this->addError('Datei ist leer');
            return false;
        }
        
        return true;
    }

    /**
     * Validate file type by extension and MIME type
     */
    private function validateFileType(array $file): bool
    {
        $extension = strtolower(pathinfo($file['name'], PATHINFO_EXTENSION));
        
        // Check extension
        if (!in_array($extension, $this->config['allowed_types'])) {
            $allowed = implode(', ', $this->config['allowed_types']);
            $this->addError("Dateityp nicht erlaubt (erlaubt: {$allowed})");
            return false;
        }
        
        // Check MIME type
        if (!in_array($file['type'], $this->config['allowed_mimes'])) {
            $this->addError('MIME-Type nicht erlaubt');
            return false;
        }
        
        return true;
    }

    /**
     * Validate file content (security check)
     */
    private function validateFileContent(string $tmpName, string $mimeType): bool
    {
        // Check actual MIME type from file content
        $finfo = finfo_open(FILEINFO_MIME_TYPE);
        if ($finfo === false) {
            $this->addError('Fileinfo-Erweiterung nicht verfügbar');
            return false;
        }
        
        $detectedMime = finfo_file($finfo, $tmpName);
        finfo_close($finfo);
        
        if (!in_array($detectedMime, $this->config['allowed_mimes'])) {
            $this->addError('Dateiinhalt stimmt nicht mit Dateityp überein');
            return false;
        }
        
        return true;
    }

    /**
     * Generate filename based on strategy
     */
    private function generateFilename(string $originalName, ?string $customName = null): string
    {
        $extension = strtolower(pathinfo($originalName, PATHINFO_EXTENSION));
        
        if ($customName !== null) {
            return $this->sanitizeFilename($customName) . '.' . $extension;
        }
        
        switch ($this->config['filename_strategy']) {
            case 'original':
                return $this->sanitizeFilename(pathinfo($originalName, PATHINFO_FILENAME)) . '.' . $extension;
                
            case 'timestamp':
                return date('YmdHis') . '_' . bin2hex(random_bytes(4)) . '.' . $extension;
                
            case 'hash':
            default:
                return bin2hex(random_bytes(16)) . '.' . $extension;
        }
    }

    /**
     * Sanitize filename
     */
    private function sanitizeFilename(string $filename): string
    {
        // Remove any path separators
        $filename = basename($filename);
        
        // Replace spaces and special characters
        $filename = preg_replace('/[^a-zA-Z0-9_-]/', '_', $filename);
        
        // Remove multiple underscores
        $filename = preg_replace('/_+/', '_', $filename);
        
        return trim($filename, '_');
    }

    /**
     * Get target directory (with optional subdirectories)
     */
    private function getTargetDirectory(): string
    {
        $dir = $this->config['upload_dir'];
        
        if ($this->config['create_subdirs']) {
            $dir .= '/' . date('Y') . '/' . date('m');
            
            if (!is_dir($dir)) {
                mkdir($dir, 0755, true);
            }
        }
        
        return $dir;
    }

    /**
     * Ensure upload directory exists
     */
    private function ensureUploadDirectory(): void
    {
        if (!is_dir($this->config['upload_dir'])) {
            mkdir($this->config['upload_dir'], 0755, true);
        }
        
        // Create .htaccess to protect uploads directory
        $htaccess = $this->config['upload_dir'] . '/.htaccess';
        if (!file_exists($htaccess)) {
            $content = "# Protect upload directory\n";
            $content .= "Options -Indexes\n\n";
            $content .= "# Apache 2.4+\n";
            $content .= "<IfModule mod_authz_core.c>\n";
            $content .= "    <FilesMatch \"\.(pdf|jpg|jpeg|png|gif)$\">\n";
            $content .= "        Require all granted\n";
            $content .= "    </FilesMatch>\n";
            $content .= "</IfModule>\n\n";
            $content .= "# Apache 2.2 (fallback)\n";
            $content .= "<IfModule !mod_authz_core.c>\n";
            $content .= "    <FilesMatch \"\.(pdf|jpg|jpeg|png|gif)$\">\n";
            $content .= "        Order Allow,Deny\n";
            $content .= "        Allow from all\n";
            $content .= "    </FilesMatch>\n";
            $content .= "</IfModule>\n";
            file_put_contents($htaccess, $content);
        }
    }

    /**
     * Add error message
     */
    private function addError(string $message): void
    {
        $this->errors[] = $message;
    }

    /**
     * Get upload error message
     */
    private function getUploadErrorMessage(int $errorCode): string
    {
        switch ($errorCode) {
            case UPLOAD_ERR_INI_SIZE:
            case UPLOAD_ERR_FORM_SIZE:
                return 'Datei überschreitet maximale Größe';
            case UPLOAD_ERR_PARTIAL:
                return 'Datei wurde nur teilweise hochgeladen';
            case UPLOAD_ERR_NO_FILE:
                return 'Keine Datei hochgeladen';
            case UPLOAD_ERR_NO_TMP_DIR:
                return 'Temporäres Verzeichnis fehlt';
            case UPLOAD_ERR_CANT_WRITE:
                return 'Fehler beim Schreiben auf Festplatte';
            case UPLOAD_ERR_EXTENSION:
                return 'Upload durch PHP-Erweiterung blockiert';
            default:
                return 'Unbekannter Upload-Fehler';
        }
    }
}
