<?php
header("Content-type:application/vnd.ms-excel");
header("Content-disposition:attachment;filename=".$fileData['fileName'].".csv");
header("Expires: 0");
header("Cache-Control: must-revalidate, post-check=0,pre-check=0");
header("Pragma: public");

// http://bakery.cakephp.org/articles/jeroendenhaan/2010/04/23/exporting-data-to-csv-the-cakephp-way
// http://bakery.cakephp.org/articles/ifunk/2007/09/10/csv-helper-php5

echo $content_for_layout;
?> 