<?php
// force download
header("Content-Type: application/force-download");
header("Content-Type: application/octet-stream");
header("Content-Type: application/download");

// disposition / encoding on response body
header("Content-Transfer-Encoding: binary");

// header("Content-type:application/vnd.ms-excel");
header('Content-type: text/csv; charset=UTF-8');
header("Content-disposition:attachment;filename=".$fileData['fileName'].".csv");
header("Expires: 0");
header("Cache-Control: must-revalidate, post-check=0,pre-check=0");
header("Pragma: public");

// If you're serving to IE 9, then the following may be needed
header('Cache-Control: max-age=1');

// http://bakery.cakephp.org/articles/jeroendenhaan/2010/04/23/exporting-data-to-csv-the-cakephp-way
// http://bakery.cakephp.org/articles/ifunk/2007/09/10/csv-helper-php5

echo $content_for_layout;
?> 