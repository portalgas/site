<div class="legenda legenda-ico-alert" style="float:none;">
Se <b>è</b> presente l'articolo di Joomla:
<ul>
	<li>L'immagine uplodata sarà rinominata con l'ID dell'articolo di Joomla</li>
</ul>
Se <b>non</b> è presente l'articolo di Joomla:
<ul>
	<li>L'immagine uplodata sarà rinominata con <?php echo Configure::read('App.prefix.upload.content');?> e l'ID del produttore</li>
</ul>
e archiviata in <?php echo Configure::read('App.root').Configure::read('App.img.upload.content').DS;?>:<br />
<br />
Utilizzata in:
<ul>
	<li>1) invio mail per apertura/chiusura ordini</li>
	<li>2) front-end, colonna di destra a fianco dell'articolo</li>
	<li>3) front-end, colonna di destra a fianco dell'articolo aperto dal <b>popup</b> della pagina delle consegne <img class="img-responsive-disabled" src="/images/cake/apps/32x32/kontact.png" alt="Leggi la scheda del produttore" /></li>
</ul>
<br />
<br />
Al punto 3) si visualizzano, se esistono, anche le immagini IDa.jpg, IDb.jpg, IDc.jpg, IDd.jpg 
<br /><br />
Dimensione massima in lunghezza: <b><?php echo Configure::read('App.web.img.upload.width.content');?></b> pixel
</div>