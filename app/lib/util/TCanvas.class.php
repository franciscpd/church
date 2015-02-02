<?php
class TCanvas
{
    private $image;
    private $width;
    private $height;
    private $imageResized;

    public function __construct($source_file)
    {
        $this->image = $this->openImage($source_file);
            
        if (! $this->image)
        {
            unlink($source_file);
            throw new Exception(_t('Invalid image')); 
        }
            
        $this->width = imagesx($this->image);
        $this->height= imagesy($this->image);       
    }
    
    private function openImage($file)
    {
        $extension = pathinfo($file, PATHINFO_EXTENSION);
        switch($extension)
        {
            case 'jpg':
            case 'jpeg':
                $img = @imagecreatefromjpeg($file);
                break;
            case 'gif':
                $img = @imagecreatefromgif($file);
                break;
            case 'png':
                $img = @imagecreatefrompng($file);
                break;
            default:
                $img = false;
                break;
        }
        
        return $img;
    }
    
    public function resize($width, $height, $option="auto")
    {
        $optionArray = $this->getDimensions($width, $height, strtolower($option));
        
        $optimalWidth = $optionArray['optimalWidth'];
        $optimalHeight= $optionArray['optimalHeight'];
        
        $this->imageResized = imagecreatetruecolor($optimalWidth, $optimalHeight);
        imagecopyresampled($this->imageResized, $this->image, 0, 0, 0, 0, $optimalWidth, $optimalHeight, $this->width, $this->height);
  
        switch($option)
        {
            case 'crop':
                $this->crop($optimalWidth, $optimalHeight, $width, $height);
        }      
    }
    
    private function getDimensions($width, $height, $option)
    {
        switch($option)
        {
            case 'exact':
                $optimalWidth = $width;
                $optimalHeight= $height;
                break;
            case 'portrait':
                $optimalWidth = $this->getSizeByFixedHeight($height);
                $optimalHeight= $height;
                break;
            case 'landscape':
                $optimalWidth = $width;
                $optimalHeight= $this->getSizeByFixedWidth($width);
                break;
            case 'auto':
                $optionArray  = $this->getSizeByAuto($width, $height);
                $optimalHeight= $optionArray['optimalHeight'];
                $optimalWidth = $optionArray['optimalWidth'];
                break;
            case 'crop':
                $optionArray = $this->getOptimalCrop($width, $height);
                $optimalHeight= $optionArray['optimalHeight'];
                $optimalWidth = $optionArray['optimalWidth'];
                break;           
        }
        
        return array('optimalWidth' => $optimalWidth, 'optimalHeight' => $optimalHeight);
    }
    
    private function getSizeByFixedHeight($height)
    {
        $ratio = $this->width / $this->height;
        return $height * $ratio;
    }
    
    private function getSizeByFixedWidth($width)
    {
        $ratio = $this->height / $this->width;
        return $width * $ratio;
    }
    
    private function getSizeByAuto($width, $height)
    {
        if ($this->height < $this->width)
        {
            $optimalWidth = $width;
            $optimalHeight= $this->getSizeByFixedWidth($width);
        }
        elseif ($this->height > $this->width)
        {
            $optimalWidth = $this->getSizeByFixedHeight($height);
            $optimalHeight= $height;
        }
        else
        {
            if ($height < $width) 
            {
                $optimalWidth = $width;
                $optimalHeight= $this->getSizeByFixedWidth($width);
            } 
            else if ($height > $width) 
            {
                $optimalWidth = $this->getSizeByFixedHeight($height);
                $optimalHeight= $height;
            } 
            else 
            {
                $optimalWidth = $width;
                $optimalHeight= $height;
            }              
        }
        
        return array('optimalWidth' => $optimalWidth, 'optimalHeight' => $optimalHeight);
    }
    
    private function getOptimalCrop($width, $height)
    {
        $heightRatio = $this->height / $height;
        $widthRatio  = $this->width / $width;
        
        if ($heightRatio < $widthRatio)
        {
            $optimalRatio = $heightRatio;
        }
        else
        {
            $optimalRatio = $widthRatio;
        }
        
        $optimalHeight= $this->height / $optimalRatio;
        $optimalWidth = $this->width  / $optimalRatio;
        
        return array('optimalWidth' => $optimalWidth, 'optimalHeight' => $optimalHeight);
    }
    
    private function crop($optimalWidth, $optimalHeight, $width, $height)
    {
        $cropStartX = ($optimalWidth / 2) - ($width / 2);
        $cropStartY = ($optimalHeight/ 2) - ($height/ 2);
        
        $crop = $this->imageResized;
        
        $this->imageResized = imagecreatetruecolor($width, $height);
        imagecopyresampled($this->imageResized, $crop, 0, 0, $cropStartX, $cropStartY, $width, $height, $width, $height);
    }
    
    public function save($destination_file, $quality="100")
    {
        $extension = pathinfo($destination_file, PATHINFO_EXTENSION);
        
        switch($extension)
        {
            case 'jpg':
            case 'jpeg':
                if (imagetypes() & IMG_JPG)
                {
                    imagejpeg($this->imageResized, $destination_file, $quality);
                }
                break;
            case 'gif':
                if (imagetypes() & IMG_GIF)
                {
                    imagegif($this->imageResized, $destination_file);
                }
                break;
            case 'png':
                $scaleQuality = round(($quality/100) * 9);
                $invertScaleQuality = 9 - $scaleQuality;
            
                if (imagetypes() & IMG_PNG)
                {
                    imagepng($this->imageResized, $destination_file, $invertScaleQuality);
                }
                break;
            default:
                break;
        }       
        
        imagedestroy($this->imageResized);
    }
}