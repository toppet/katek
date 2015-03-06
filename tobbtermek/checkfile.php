<?php
$return = array();
$er = '';

if(file_exists('query_progress.txt')){
    $er = true;
}else{
    $er = false;
}
$return["exists"] = $er;    
echo json_encode($return);
?>