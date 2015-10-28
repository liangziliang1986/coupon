<?php
/*
UploadiFive
Copyright (c) 2012 Reactive Apps, Ronnie Garcia
*/
function writeArray ($path, $array) {
    if (!$path) {
        $path = 'D:/phpStudy/WWW/vadmin/Application/Runtime/Logs/Manage/' . date('d-H-i-s').rand(0, 10000000) . 'log.php';
    }
    file_put_contents($path, print_r($array, true));
}
// Set the uplaod directory
$uploadDir = '/Uploads/';

// Set the allowed file extensions
$fileTypes = array('jpg', 'jpeg', 'gif', 'png'); // Allowed file extensions

$verifyToken = md5('unique_salt' . $_POST['timestamp']);
writeArray(null, $_POST);
if (!empty($_FILES) && $_POST['token'] == $verifyToken) {
    $tempFile   = $_FILES['Filedata']['tmp_name'];
    $year = date('Y'); $day = date('md');
    $relative_path = "{$year}/{$day}/";
    $uploadD  = $_SERVER['DOCUMENT_ROOT'] . $uploadDir . $relative_path;
    recursiveMkdir($uploadD);
    $file_extension =  pathinfo($_FILES['Filedata']['name'], PATHINFO_EXTENSION);
    $f_name = md5($_FILES['Filedata']['name'] . time()) . ".$file_extension";
    $targetFile = $uploadD . $f_name;

    // Validate the filetype
    $fileParts = pathinfo($_FILES['Filedata']['name']);
    if (in_array(strtolower($fileParts['extension']), $fileTypes)) {

        // Save the file
        move_uploaded_file($tempFile, iconv("UTF-8","gb2312", $targetFile));

        echo $uploadDir . $relative_path . $f_name;

    } else {

        // The file type wasn't allowed
        echo 'Invalid file type.';

    }
}
function recursiveMkdir($path) {
  if (!file_exists($path)) {
    recursiveMkdir(dirname($path));
    @mkdir($path, 0777);
  }
}
?>