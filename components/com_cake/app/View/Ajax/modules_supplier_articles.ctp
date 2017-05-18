<?php
if (isset($results['Article'])) {

	echo '<div class="gas_modules">';
	echo '<h3>'.__('ArticlesSupplier').'</h3>';
	 	
 	echo '<ul class="list-unstyled">';
	foreach($results['Article'] as $result) {
		echo "\r\n";
		echo '<li style="font-size: 14px;">';
		if(!empty($result['Article']['img1']) && file_exists(Configure::read('App.root').Configure::read('App.img.upload.article').DS.$result['Article']['organization_id'].DS.$result['Article']['img1'])) {
			echo '<img width="50" class="userAvatar" src="'.Configure::read('App.server').Configure::read('App.web.img.upload.article').'/'.$result['Article']['organization_id'].'/'.$result['Article']['img1'].'" />';
		}				
		echo $result['Article']['name'].',&nbsp;'.$this->App->getArticleConf($result['Article']['qta'], $result['Article']['um']);
		echo '</li>';			
	}
	echo '</ul>';	
	
	echo '</div>';
}
/*
else { ?>	
	<div class="alert alert-info" role="alert">
		Non ci sono ancora articoli associati
	</div>
<?php
} */
?>