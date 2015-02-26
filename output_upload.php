<?php

$target_dir = "SMT_OUTPUT/";

if(empty($_FILES['file']["name"])){
    $error = "Select a file first.";
    $resp['resp'] = $error;
    echo json_encode($resp);
    die();
}
$target_file = $target_dir . $_FILES['file']["name"];
$fileType = pathinfo($target_file,PATHINFO_EXTENSION);
$resp = array();
//$er = "";

// Check if file already exists
if (file_exists($target_file)) {
    unlink($target_file);
}

// Check file size
if ($_FILES["file"]["size"] > 500000) {
    $error = ("Sorry, your file is too large.");
    $resp['resp'] = $error;
    echo json_encode($resp);
    die();
}

// Allow certain file formats
if($fileType != "xlsx" && $fileType != "xls") {
    $error = ("Sorry, only XLSX or XLS files are allowed.");
    
    $resp['resp'] = $error;
    echo json_encode($resp);
    die();
}

if (!move_uploaded_file($_FILES["file"]["tmp_name"], $target_file)) {
    $error = ("Sorry, there was an error uploading your file.");
    $resp['resp'] = $error;
    echo json_encode($resp);
    die();
} else {
    $er = "The file ". basename( $_FILES["file"]["name"]). " has been uploaded.";
}

$resp['resp'] = $er;
    echo json_encode($resp);
    die();

?>