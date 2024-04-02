<?php 
$requested_poster_name = $_GET['poster_name'];
$file_path = "video_covers/".$requested_poster_name;
header('Content-Length: ' . filesize($file_path));
header('Content-Type: ' . "image/png");
readfile($file_path);




?>