<div class="legenda legenda-ico-alert" style="float:none;">
Dimensione massima in lunghezza: <b><?php echo Configure::read('App.web.img.upload.width.content');?></b> pixel.
<br />
Si possono caricare file con la seguente <b>estensione</b>: 
<?php
foreach ( Configure::read('App.web.img.upload.extension') as $estensione) 
	echo '.'.$estensione.'&nbsp;';	
?>
</div>