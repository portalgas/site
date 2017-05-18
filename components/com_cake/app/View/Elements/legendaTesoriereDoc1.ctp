<div class="legenda legenda-ico-alert" style="float:none;">
<p>Il <b>nome</b> del file non pu&ograve; contenere <b>caratteri strani</b>
<ul>
	<li>- caratteri ammessi: lettere, numeri e . (punto) - (trattino) _ (underscore)</li>
	<li>- alcuni caratteri <b>non</b> ammessi: &euro; " | \ / £ $ % & ( ) = ? ' ^ ç ° § > < ; : , - @ #  [ ]
</ul>
</p>


<p><b>Estensione</b> che si possono caricare:&nbsp;
<?php
foreach ( Configure::read('App.web.pdf.upload.extension') as $estensione)
	echo '.'.$estensione.'&nbsp;';
foreach ( Configure::read('App.web.zip.upload.extension') as $estensione)
	echo '.'.$estensione.'&nbsp;';
foreach ( Configure::read('App.web.img.upload.extension') as $estensione) 
	echo '.'.$estensione.'&nbsp;';	
?>
</p>


<p>Se devi caricare <b>più documenti</b>
zippali e fai l'upload del file .zip
</p>