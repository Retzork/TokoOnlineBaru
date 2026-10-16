<?php 

namespace App\Helpers;

use Exception;

class ImageHelper
{
    /**
     * Uploads and resizes an image to a specified directory.
     *
     * @param  \Illuminate\Http\UploadedFile  $file
     * @param  string  $directory
     * @param  string  $fileName
     * @param  int|null  $width
     * @param  int|null  $height
     * @return string
     * @throws \Exception
     */
    public static function uploadAndResize($file, $directory, $fileName, $width = null, $height = null)
    {
        // Path to store the image
        $destinationPath = public_path($directory);

        // Ensure directory exists
        if (!file_exists($destinationPath)) {
            mkdir($destinationPath, 0777, true);
        }

        // Get the file extension and prepare the image
        $extension = strtolower($file->getClientOriginalExtension());
        $image = null;

        // Determine image type and create image resource
        switch ($extension) {
            case 'jpeg':
            case 'jpg':
                $image = imagecreatefromjpeg($file->getRealPath());
                break;
            case 'png':
                $image = imagecreatefrompng($file->getRealPath());
                break;
            case 'gif':
                $image = imagecreatefromgif($file->getRealPath());
                break;
            default:
                throw new Exception('Unsupported image type');
        }

        // Resize image if width is provided
        if ($width) {
            $oldWidth = imagesx($image);
            $oldHeight = imagesy($image);
            $aspectRatio = $oldWidth / $oldHeight;

            // Calculate height if not provided
            if (!$height) {
                $height = $width / $aspectRatio;
            }

            // Create a new image resource
            $newImage = imagecreatetruecolor($width, $height);

            // Handle transparency for PNG and GIF
            if ($extension === 'png' || $extension === 'gif') {
                imagealphablending($newImage, false);
                imagesavealpha($newImage, true);
                $transparent = imagecolorallocatealpha($newImage, 255, 255, 255, 127);
                imagefilledrectangle($newImage, 0, 0, $width, $height, $transparent);
            }

            // Resample the image
            imagecopyresampled($newImage, $image, 0, 0, 0, 0, $width, $height, $oldWidth, $oldHeight);
            imagedestroy($image); // Destroy original image
            $image = $newImage; // Assign resized image
        }

        // Save the image to the specified path
        switch ($extension) {
            case 'jpeg':
            case 'jpg':
                imagejpeg($image, $destinationPath . '/' . $fileName, 90);
                break;
            case 'png':
                imagepng($image, $destinationPath . '/' . $fileName);
                break;
            case 'gif':
                imagegif($image, $destinationPath . '/' . $fileName);
                break;
        }

        // Destroy the image resource after saving
        imagedestroy($image);

        // Return the file name
        return $directory . $fileName;
    }
}
