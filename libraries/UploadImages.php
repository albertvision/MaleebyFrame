<?php

namespace Maleeby\Libraries;

/**
 * Image uploading class
 *
 * @author Yasen Georgiev <avbincco@gmail.com>
 * @link http://framework.maleeby.ygeorgiev.com/
 * @copyright Copyright &copy; 2013 Yasen Georgiev
 * @license http://framework.maleeby.ygeorgiev.com/#license
 * @package Libraries
 */
class UploadImages {
    
    /**
     * Get allowed image formats
     * @static
     * @return array Allowed image formats
     */
    public static function allowedImageFormats() {
        return array('jpg','jpeg','png','gif','bmp');
    }
    
    /**
     * Check is file format allowed
     * @static
     * @param string $format File format
     * @param string $type File type
     * @return boolean
     * @throws \Exception Undefined file format
     */
    public static function isAllowedFormat($format, $type = 'image') {
        if($type == 'image') {
            $allowedFormats = self::allowedImageFormats();
        } else {
            throw new \Exception('Undefined file format: '.$type);
        }
        if(in_array($format, $allowedFormats) || in_array(strtolower($format), $allowedFormats)) {
            return true;
        }
        return false;
    }
    
    /**
     * Resizes images
     * @static
     * @param string $tmpName Tmp file
     * @param string  $fileType File type
     * @param string $uploadPath Upload path
     * @param int $newWidth New width
     * @param int $newHeight New height
     * @return boolean
     */
    public static function resizeImage($tmpName, $fileType, $uploadPath, $newWidth = 200, $newHeight = 200, $dbSave = TRUE) {
        $fileType = strtolower($fileType);
        
        if (self::isAllowedFormat($fileType, 'image')) {
            list($imageWidth, $imageHeight) = getimagesize($tmpName);
            $width = $newWidth;
            $height = $newHeight;
            if ($imageWidth < $width && $imageHeight < $height) {
                move_uploaded_file($tmpName, APP_PATH. $uploadPath);
            } elseif ($imageWidth >= $width) {
                $newWidth = $width;
                $newHeight = (int) ($imageHeight * $newWidth) / $imageWidth;
            } elseif ($imageHeight >= $height) {
                $newHeight = $height;
                $newWidth = (int) ($imageWidth * $newHeight) / $imageHeight;
            }

            $imagecreatefrom = 'imagecreatefrom';
            $image = 'image';
            if ($fileType == "jpeg" || $fileType == "jpg") {
                $format = 'jpeg';
            } else {
                $format = $fileType;
            }

            $imagecreatefrom .= $format;
            $image .= $format;
            $imageO = imagecreatetruecolor($newWidth, $newHeight);
            $imageT = $imagecreatefrom($tmpName);
            imagecopyresampled($imageO, $imageT, 0, 0, 0, 0, $newWidth, $newHeight, $imageWidth, $imageHeight);
            $image($imageO, $uploadPath, 100);
            
            if($dbSave != FALSE) {
                DB::query('INSERT INTO `uploads` (`path`,`added`) VALUES(?,  UNIX_TIMESTAMP())', array($uploadPath));
                return DB::lastID();
            }
            return true;
        }
        return false;
    }

    /**
     * Gets to the desired size
     * @static
     * @param string $image Image path
     * @param int $width Width to resize
     * @param int $height Height to resize
     * @return array
     */
    public static function getNewImageSize($image, $width, $height) {
        list($imageWidth, $imageHeight) = getimagesize($image);

        if ($imageWidth < $width && $imageHeight < $height) {
            $newWidth = $width;
            $newHeight = $height;
        } elseif ($imageWidth >= $width) {
            $newWidth = $width;
            $newHeight = (int) ($imageHeight * $newWidth) / $imageWidth;
        } elseif ($imageHeight >= $height) {
            $newHeight = $height;
            $newWidth = (int) ($imageWidth * $newHeight) / $imageHeight;
        }

        return array($newWidth, $newHeight);
    }

    /**
     * Upload a file
     * @static
     * @param string $tmpName File Tmp
     * @param string $path Path to upload
     * @return boolean
     */
    public static function uploadFile($tmpName, $path) {
        if (move_uploaded_file($tmpName, $path)) {
            return true;
        } else {
            return false;
        }
    }
}

?>
