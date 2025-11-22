// FILE: /app/helpers/FileUpload.php
<?php

/**
 * FileUpload Helper
 * Handles file uploads securely
 * SplashProperty - Multi-tenant Real Estate Management SaaS
 */
class FileUpload
{
    private $allowedMimes = [
        'image/jpeg',
        'image/png',
        'image/gif',
        'image/webp',
        'application/pdf',
        'application/msword',
        'application/vnd.openxmlformats-officedocument.wordprocessingml.document',
        'application/vnd.ms-excel',
        'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet'
    ];

    private $maxSize = 10485760; // 10MB in bytes
    private $uploadPath;
    private $errors = [];

    /**
     * Constructor
     */
    public function __construct($uploadPath = null)
    {
        $this->uploadPath = $uploadPath ?: __DIR__ . '/../../storage/uploads/';
    }

    /**
     * Set allowed MIME types
     */
    public function setAllowedMimes($mimes)
    {
        $this->allowedMimes = $mimes;
        return $this;
    }

    /**
     * Set max file size
     */
    public function setMaxSize($bytes)
    {
        $this->maxSize = $bytes;
        return $this;
    }

    /**
     * Upload file
     */
    public function upload($file, $subfolder = '')
    {
        $this->errors = [];

        // Check if file was uploaded
        if (!isset($file['tmp_name']) || !is_uploaded_file($file['tmp_name'])) {
            $this->errors[] = "No file uploaded";
            return false;
        }

        // Check for upload errors
        if ($file['error'] !== UPLOAD_ERR_OK) {
            $this->errors[] = $this->getUploadErrorMessage($file['error']);
            return false;
        }

        // Validate file size
        if ($file['size'] > $this->maxSize) {
            $this->errors[] = "File size exceeds maximum allowed size of " . $this->formatBytes($this->maxSize);
            return false;
        }

        // Validate MIME type
        $finfo = finfo_open(FILEINFO_MIME_TYPE);
        $mimeType = finfo_file($finfo, $file['tmp_name']);
        finfo_close($finfo);

        if (!in_array($mimeType, $this->allowedMimes)) {
            $this->errors[] = "File type not allowed";
            return false;
        }

        // Generate unique filename
        $extension = $this->getExtension($file['name']);
        $filename = $this->generateUniqueFilename($extension);

        // Create subfolder if needed
        $targetPath = $this->uploadPath . $subfolder;
        if (!is_dir($targetPath)) {
            mkdir($targetPath, 0755, true);
        }

        // Full path
        $targetFile = rtrim($targetPath, '/') . '/' . $filename;

        // Move uploaded file
        if (move_uploaded_file($file['tmp_name'], $targetFile)) {
            return [
                'success' => true,
                'filename' => $filename,
                'original_name' => $file['name'],
                'path' => $targetFile,
                'relative_path' => $subfolder . '/' . $filename,
                'size' => $file['size'],
                'mime_type' => $mimeType
            ];
        }

        $this->errors[] = "Failed to move uploaded file";
        return false;
    }

    /**
     * Upload multiple files
     */
    public function uploadMultiple($files, $subfolder = '')
    {
        $results = [];

        if (!isset($files['name'])) {
            return $results;
        }

        $fileCount = count($files['name']);

        for ($i = 0; $i < $fileCount; $i++) {
            $file = [
                'name' => $files['name'][$i],
                'type' => $files['type'][$i],
                'tmp_name' => $files['tmp_name'][$i],
                'error' => $files['error'][$i],
                'size' => $files['size'][$i]
            ];

            $result = $this->upload($file, $subfolder);
            if ($result) {
                $results[] = $result;
            }
        }

        return $results;
    }

    /**
     * Delete file
     */
    public function delete($filepath)
    {
        if (file_exists($filepath)) {
            return unlink($filepath);
        }
        return false;
    }

    /**
     * Generate unique filename
     */
    private function generateUniqueFilename($extension)
    {
        return uniqid('file_', true) . '_' . bin2hex(random_bytes(8)) . '.' . $extension;
    }

    /**
     * Get file extension
     */
    private function getExtension($filename)
    {
        return strtolower(pathinfo($filename, PATHINFO_EXTENSION));
    }

    /**
     * Get upload error message
     */
    private function getUploadErrorMessage($code)
    {
        switch ($code) {
            case UPLOAD_ERR_INI_SIZE:
            case UPLOAD_ERR_FORM_SIZE:
                return "File size exceeds maximum allowed";
            case UPLOAD_ERR_PARTIAL:
                return "File was only partially uploaded";
            case UPLOAD_ERR_NO_FILE:
                return "No file was uploaded";
            case UPLOAD_ERR_NO_TMP_DIR:
                return "Missing temporary folder";
            case UPLOAD_ERR_CANT_WRITE:
                return "Failed to write file to disk";
            case UPLOAD_ERR_EXTENSION:
                return "Upload blocked by extension";
            default:
                return "Unknown upload error";
        }
    }

    /**
     * Format bytes to human readable
     */
    private function formatBytes($bytes)
    {
        if ($bytes >= 1073741824) {
            return number_format($bytes / 1073741824, 2) . ' GB';
        } elseif ($bytes >= 1048576) {
            return number_format($bytes / 1048576, 2) . ' MB';
        } elseif ($bytes >= 1024) {
            return number_format($bytes / 1024, 2) . ' KB';
        }
        return $bytes . ' bytes';
    }

    /**
     * Get errors
     */
    public function getErrors()
    {
        return $this->errors;
    }

    /**
     * Check if is image
     */
    public function isImage($mimeType)
    {
        return strpos($mimeType, 'image/') === 0;
    }
}
