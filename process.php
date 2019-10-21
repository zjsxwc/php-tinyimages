<?php

//sudo apt install libmagickwand-dev imagemagick


function qualityCompress($inputImageFullPath, $outputImageFullPath, $quality = 75)
{
    if (!file_exists($inputImageFullPath)) {
        return false;
    }
    @mkdir(dirname($outputImageFullPath), 0777, true);
    @unlink($outputImageFullPath);
    $output = [];
    $returnVar = -1;
    exec("convert -quality " . $quality . "% " . $inputImageFullPath . " " . $outputImageFullPath, $output, $returnVar);
    if ($returnVar != 0) {
        return false;
    }
    return $quality;
}

function compress($inputImageFullPath, $outputImageFullPath, $quality = 75)
{
    echo "start process $inputImageFullPath quality $quality\n";
    $quality = qualityCompress($inputImageFullPath, $outputImageFullPath, $quality);
    if ($quality) {
        $targetSize = 0.26 * 1024 * 1024;
        $currentSize = filesize($outputImageFullPath);
        echo "current size $currentSize \n";
        echo "target size $targetSize \n";

        if ($currentSize > $targetSize) {
            $newQuality = 1.0 * $quality * ($targetSize * 1.0 / $currentSize);
            $newQuality = intval(ceil($newQuality)) - 1;
            echo "new quality $newQuality\n";
            compress($inputImageFullPath, $outputImageFullPath, $newQuality);
        }
    }
}


function tree($directory, $srcPath)
{
    $mydir=dir($directory);
    while($file=$mydir->read()){
        if((is_dir("$directory/$file")) AND ($file!=".") AND ($file!=".."))
        {
            tree("$directory/$file", $srcPath);
        } else if (($file!=".") AND ($file!="..")) {
            echo "$directory/$file\n";
            $filePath = "$directory/$file";
            $relativePath = substr($filePath, strlen($srcPath));
            echo $relativePath . "\n";
            $extension = pathinfo($filePath, PATHINFO_EXTENSION);
            $extension = strtolower($extension);
            if (in_array($extension, ["jpg", "jpeg", "png"])) {
                compress($filePath, $srcPath . "/../dist/" . $relativePath);
            }
        }
    }
    $mydir->close();
}
function processAllImageFile($srcPath) {
    tree($srcPath, $srcPath);
}


$srcPath = __DIR__ . "/src";
processAllImageFile($srcPath);
