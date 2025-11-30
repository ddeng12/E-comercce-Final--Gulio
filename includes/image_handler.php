<?php
/**
 * Image Upload and Processing Handler
 */

class ImageHandler {
    private $uploadDir;
    private $allowedTypes = ['image/jpeg', 'image/jpg', 'image/png', 'image/gif', 'image/webp'];
    private $maxFileSize = 5 * 1024 * 1024; // 5MB
    
    public function __construct($uploadDir = 'assets/images/products/') {
        $this->uploadDir = rtrim($uploadDir, '/') . '/';
        
        // Create directory if it doesn't exist
        $fullPath = __DIR__ . '/../' . $this->uploadDir;
        if (!is_dir($fullPath)) {
            mkdir($fullPath, 0755, true);
        }
    }
    
    public function uploadImage($file, $prefix = '') {
        if (!isset($file) || $file['error'] !== UPLOAD_ERR_OK) {
            return ['success' => false, 'message' => 'No file uploaded or upload error'];
        }
        
        // Validate file type
        if (!in_array($file['type'], $this->allowedTypes)) {
            return ['success' => false, 'message' => 'Invalid file type. Only JPEG, PNG, GIF, and WebP are allowed'];
        }
        
        // Validate file size
        if ($file['size'] > $this->maxFileSize) {
            return ['success' => false, 'message' => 'File too large. Maximum size is 5MB'];
        }
        
        // Generate unique filename
        $extension = pathinfo($file['name'], PATHINFO_EXTENSION);
        $filename = ($prefix ? $prefix . '_' : '') . uniqid(time()) . '.' . strtolower($extension);
        
        $uploadPath = __DIR__ . '/../' . $this->uploadDir . $filename;
        
        // Move uploaded file
        if (move_uploaded_file($file['tmp_name'], $uploadPath)) {
            // Optionally resize/optimize image here
            $this->optimizeImage($uploadPath, $file['type']);
            
            return [
                'success' => true, 
                'filename' => $filename,
                'path' => $this->uploadDir . $filename,
                'url' => $this->uploadDir . $filename
            ];
        } else {
            return ['success' => false, 'message' => 'Failed to move uploaded file'];
        }
    }
    
    private function optimizeImage($filePath, $mimeType) {
        // Basic image optimization (optional)
        try {
            $maxWidth = 800;
            $maxHeight = 800;
            $quality = 85;
            
            list($width, $height) = getimagesize($filePath);
            
            // Only resize if image is larger than max dimensions
            if ($width > $maxWidth || $height > $maxHeight) {
                $ratio = min($maxWidth / $width, $maxHeight / $height);
                $newWidth = intval($width * $ratio);
                $newHeight = intval($height * $ratio);
                
                $newImage = imagecreatetruecolor($newWidth, $newHeight);
                
                switch ($mimeType) {
                    case 'image/jpeg':
                    case 'image/jpg':
                        $source = imagecreatefromjpeg($filePath);
                        imagecopyresampled($newImage, $source, 0, 0, 0, 0, $newWidth, $newHeight, $width, $height);
                        imagejpeg($newImage, $filePath, $quality);
                        break;
                    case 'image/png':
                        $source = imagecreatefrompng($filePath);
                        imagealphablending($newImage, false);
                        imagesavealpha($newImage, true);
                        imagecopyresampled($newImage, $source, 0, 0, 0, 0, $newWidth, $newHeight, $width, $height);
                        imagepng($newImage, $filePath);
                        break;
                    case 'image/gif':
                        $source = imagecreatefromgif($filePath);
                        imagecopyresampled($newImage, $source, 0, 0, 0, 0, $newWidth, $newHeight, $width, $height);
                        imagegif($newImage, $filePath);
                        break;
                }
                
                imagedestroy($source);
                imagedestroy($newImage);
            }
        } catch (Exception $e) {
            // If optimization fails, keep original image
            error_log('Image optimization failed: ' . $e->getMessage());
        }
    }
    
    public function deleteImage($imagePath) {
        $fullPath = __DIR__ . '/../' . $imagePath;
        if (file_exists($fullPath)) {
            return unlink($fullPath);
        }
        return false;
    }
    
    public static function getImageUrl($imagePath) {
        if (empty($imagePath)) {
            return 'assets/images/placeholder-product.jpg';
        }
        
        // If it's already a full URL, return as is
        if (strpos($imagePath, 'http') === 0) {
            return $imagePath;
        }
        
        // Return relative path
        return $imagePath;
    }
}
?>
