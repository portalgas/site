<?php
/**
 *  utilizzato questo
 *
 * PHP 5
 *
 * CakePHP(tm) : Rapid Development Framework (http://cakephp.org)
 * Copyright 2005-2012, Cake Software Foundation, Inc. (http://cakefoundation.org)
 *
 * Licensed under The MIT License
 * Redistributions of files must retain the above copyright notice.
 *
 * @copyright     Copyright 2005-2012, Cake Software Foundation, Inc. (http://cakefoundation.org)
 * @link          http://cakephp.org CakePHP(tm) Project
 * @package       Cake.View.Emails.html
 * @since         CakePHP(tm) v 0.10.0.1076
 * @license       MIT License (http://www.opensource.org/licenses/mit-license.php)
 */
?>
<?php
//$content = explode("\n", $content);

/*foreach ($content as $line):
	echo '<p> ' . $line . "</p>\n";
endforeach;
*/
?>
<!DOCTYPE html PUBLIC "-//W3C//DTD HTML 4.01//EN">
<html>
    <head>
        <title><?php echo $title_for_layout; ?></title>
    </head>
    <body style="margin:0; padding:0;">
		<div>
			<div class="header"><?php echo $body_header;?>
			</div>
			<div class="content" style="padding:20px;">
				<?php echo $content; ?>
			</div>
			<div class="footer"><?php echo $body_footer;?></div>
		</div>
	</body>
</html>