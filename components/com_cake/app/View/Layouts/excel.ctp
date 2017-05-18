<?php
header("Content-type: application/vnd.ms-excel");
header("Content-disposition:attachment;filename=".$fileData['fileName'].".xls");
header("Expires: 0");
header("Cache-Control: must-revalidate, post-check=0,pre-check=0");
header("Pragma: public");
echo $content_for_layout;
?>