<?php
if(!extension_loaded('gd')) return;
    
kirby()->hook('panel.file.upload', 'watermarkImage');

function watermarkImage($file)
{
    try
    {
        // Check if we should ignore or not this upload
        $page = $file->page();
        
        $lang = site()->defaultLanguage() ? site()->defaultLanguage()->code : null;
    
        foreach(c::get('watermark.ignore', array()) as $pattern)
        {
            if(fnmatch($pattern, $page->uri($lang)) === true)
            {
                return;
            }
        }

        // Check if image is configured in options
        $markImage = c::get('watermark.image');
        if(!isset($markImage))
            return;

         // Check if image exists
        $markFile = site()->image($markImage);
        if(!isset($markFile))
            return;
            
        // Obtain the watermark image's resource and check if valid
        $watermark = getResource($markFile);
        if(!isset($watermark))
            return;

        // Obtain the target image's resource and check if valid
        $image = getResource($file);
        if(!isset($image))
            return; 
        
        // Get the watermark size
        $watermarkWidth = imagesx($watermark);
        $watermarkHeight = imagesy($watermark);

        // Only true when we shouldn't crop and watermark is bigger than image itself
        if( $watermarkWidth > imagesx($image) || $watermarkHeight > imagesy($image) ) 
            return;

        // Check if the image is wide enought to support the number of wanted columns, else reduce the number of it
        $columns = c::get('watermark.columns', 1);
        $tileWidth = imagesx($image) / $columns;
        while( $watermarkWidth > $tileWidth )
        {
            $columns--;
            $tileWidth = imagesx($image) / $columns;
        }

        // Check if the image is tall enought to support the number of wanted rows, else reduce the number of it
        $rows = c::get('watermark.rows', 1);
        $tileHeight = imagesy($image) / $rows ;
        while( $watermarkHeight > $tileHeight )
        {
            $rows--;
            $tileHeight = imagesy($image) / $rows;
        }

        // Get the watermark opacity
        $markOpacity = c::get('watermark.opacity', 30);

        // Iterate thru all tiles and insert the watermarks
        for($c = 0 ; $c < $columns; $c++)
        {
            for($r = 0; $r < $rows; $r++)
            {
                // Set the top-left corner of the watermark in relation to the image
                $destx = ($c * $tileWidth) + ($tileWidth / 2) - ( $watermarkWidth / 2 );
                $desty = ($r * $tileHeight) + ($tileHeight / 2) - ( $watermarkHeight / 2 );
 
                imagecopymerge_alpha ( $image , $watermark , $destx , $desty , 0 , 0 , $watermarkWidth , $watermarkHeight , $markOpacity);
            }
        }
        saveResource($file, $image);
        imagedestroy($image);
        imagedestroy($watermark);

    }
    catch (Exception $e)
    {
        return response::error($e->getMessage());
    }
}


/**
 * Give a path to an image it returns its associated resource so we can handle it
 */
function getResource($file)
{
    // Get the path to image and its type
    $path = $file->dir() . '/' . $file->filename();
    $ext = strtolower( pathinfo( $path, PATHINFO_EXTENSION ));
        
    // Return the appropriate resource
    if($ext == "jpg")
        return imagecreatefromjpeg($path);
    else if($ext == "png")
        return imagecreatefrompng($path);
}


/**
 * Saves the given resource to the given file's path
 */
function saveResource($file, $resource)
{
    // Get the path to image and its type
    $path = $file->dir() . '/' . $file->filename();
    $ext = strtolower( pathinfo( $path, PATHINFO_EXTENSION ));
        
    // Return the appropriate resource
    if($ext == "jpg")
        return imagejpeg($resource, $path);
    else if($ext == "png")
        return imagepng ($resource, $path);
}


/**
* PNG ALPHA CHANNEL SUPPORT for imagecopymerge();
* by Sina Salek
*
* Bugfix by Ralph Voigt (bug which causes it
* to work only for $src_x = $src_y = 0.
* Also, inverting opacity is not necessary.)
* 08-JAN-2011
*
**/
function imagecopymerge_alpha($dst_im, $src_im, $dst_x, $dst_y, $src_x, $src_y, $src_w, $src_h, $pct)
{
    // creating a cut resource
    $cut = imagecreatetruecolor($src_w, $src_h);

    // copying relevant section from background to the cut resource
    imagecopy($cut, $dst_im, 0, 0, $dst_x, $dst_y, $src_w, $src_h);
   
    // copying relevant section from watermark to the cut resource
    imagecopy($cut, $src_im, 0, 0, $src_x, $src_y, $src_w, $src_h);
   
    // insert cut resource to destination image
    imagecopymerge($dst_im, $cut, $dst_x, $dst_y, 0, 0, $src_w, $src_h, $pct);
} 