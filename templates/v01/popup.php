<?php
// No direct access.
defined('_JEXEC') or die;

/*
 * estrago l'id dell'articolo del produttore
 * l'immagine e' in /images/organizations/contents/ID.jpg
 */

$content_id = 0;

// /component/content/article/40-produttori/detersivi-c/12-le-erbe-di-brillor
$uri = split('/', $_SERVER['REQUEST_URI']);
if(isset($uri[count($uri)-1])) {
	$uriUltimo = $uri[count($uri)-1];
	
	if(strpos($uriUltimo,'-') > 0) {
		$path = split('-', $uriUltimo);
		$content_id = $path[0];
	}
}
?>

<div class="row">
			<div class="col-xs-8">
				<jdoc:include type="component" />
			</div>
			<div class="col-xs-4 hidden-xs hidden-sm">
					
				<?php 
				if(file_exists(JPATH_BASE.'/images/organizations/contents/'.$content_id.'.jpg')) 
					echo '<img class="img-responsive" src="/images/organizations/contents/'.$content_id.'.jpg" />';
				else
				if(file_exists(JPATH_BASE.'/images/organizations/contents/'.$content_id.'.jpeg')) 	
					echo '<img class="img-responsive" src="/images/organizations/contents/'.$content_id.'.jpeg" />';
				else
				if(file_exists(JPATH_BASE.'/images/organizations/contents/'.$content_id.'.png')) 
					echo '<img class="img-responsive" src="/images/organizations/contents/'.$content_id.'.png" />';
				else
				if(file_exists(JPATH_BASE.'/images/organizations/contents/'.$content_id.'.gif')) 
					echo '<img class="img-responsive" src="/images/organizations/contents/'.$content_id.'.gif" />';
				else
				if(file_exists(JPATH_BASE.'/images/organizations/contents/tmp-'.$content_id.'.jpg')) 
					echo '<img class="img-responsive" src="/images/organizations/contents/tmp-'.$content_id.'.jpg" />';
				else
				if(file_exists(JPATH_BASE.'/images/organizations/contents/tmp-'.$content_id.'.jpeg')) 	
					echo '<img class="img-responsive" src="/images/organizations/contents/tmp-'.$content_id.'.jpeg" />';
				else
				if(file_exists(JPATH_BASE.'/images/organizations/contents/tmp-'.$content_id.'.png')) 
					echo '<img class="img-responsive" src="/images/organizations/contents/tmp-'.$content_id.'.png" />';
				else
				if(file_exists(JPATH_BASE.'/images/organizations/contents/tmp-'.$content_id.'.gif')) 
					echo '<img class="img-responsive" src="/images/organizations/contents/tmp-'.$content_id.'.gif" />';					
				
				if(file_exists(JPATH_BASE.'/images/organizations/contents/'.$content_id.'a.jpg')) {
					echo '<div class="image">';
					echo '<img class="img-responsive" src="/images/organizations/contents/'.$content_id.'a.jpg" />';
					echo '</div>';
				}

				if(file_exists(JPATH_BASE.'/images/organizations/contents/'.$content_id.'b.jpg')) {
					echo '<div class="image">';
					echo '<img class="img-responsive" src="/images/organizations/contents/'.$content_id.'b.jpg" />';
					echo '</div>';
				}

				if(file_exists(JPATH_BASE.'/images/organizations/contents/'.$content_id.'c.jpg')) {
					echo '<div class="image">';
					echo '<img class="img-responsive" src="/images/organizations/contents/'.$content_id.'c.jpg" />';
					echo '</div>';
				}
				?>

			</div>
</div> 										
