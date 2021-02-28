<?php
use Illuminate\Support\Facades\Storage;

if (! function_exists('imageUpload')) {
    function imageUpload($inputImage)
    {
        $today = date('Y-m-d');
        $today = strtotime($today);
        $path = 'product/i/'.$today;
        $storagePath = 'public/'.$path;
        $storedImage = $inputImage->store($storagePath);
        $imagePath = '/'.str_replace("public", 'storage', $storedImage);
        return $imagePath;
    }
}

if (! function_exists('imageDelete')) {
    function imageDelete($imageUrl, $permanentDelete = false)
    {
        $imageUrl = str_replace('storage', 'public', $imageUrl);
        $trashPath = str_replace('u', 't', $imageUrl);
        if(Storage::exists($imageUrl)) {
            if ($permanentDelete) {
                Storage::delete( $imageUrl);
            } else {
                Storage::move($imageUrl, $trashPath); 
            }   
            return true;
        }
        return false;
    }
}
if (! function_exists('imageUploadFromBase64')) {
    function imageUploadFromBase64($url)
    {
        $inputImage = Image::make($url);
        $storeImageName = time().'.png';
        $today = date('Y-m-d');
        $today = strtotime($today);
        $path = 'product/i/'.$today;
        $storagePath = 'public/'.$path;
        $storedImage = $storagePath.'/'.$storeImageName;

        if(Storage::exists($storedImage)) {
            $d = new DateTime();
            $miliSecond = $d->format('s-u-');
            $storedImage = $storagePath.'/'.$miliSecond.$storeImageName;
        }
        Storage::put($storedImage, (string) $inputImage->encode());
        $imagePath = '/'.str_replace("public", 'storage', $storedImage);
        return $imagePath;
    }
}

    