<?php
header("Content-Type", "application/pdf");
header("Content-disposition:attachment;filename=".$fileData['fileName'].".pdf");
header("Expires: 0");
header("Cache-Control: must-revalidate, post-check=0,pre-check=0");
header("Pragma: public");
echo $content_for_layout;
?>