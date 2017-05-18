<style type="text/css">
ol li {
	margin:0 0 5px;
}
ul {

}
ul li {
	margin: 5px 0 0 25px;list-style: disc outside none;
}
</style>

<ol>
	<li>
				clicca sul tabs della <b>consegna</b> per visualizzare l'elenco dei produttori associati<br />
				<img alt="tabs consegne" src="<?php echo Configure::read('App.img.cake'); ;?>/info_tabs_consegne.png" />
	</li>
	<li>
				ogni riga della tabella relativa ad un produttore puo&grave; presentare 4 diverse tipologie <b>di STATO</b>: 
				<ul>
					<li><span style="color:red;">Chiuso</span>: non si possono fare piu&grave; acquisti.</li>
					<li><span style="color:green;">Aperto</span>: si possono effetturare acquisti (controllare la colonna "Data di chiusura" per sapere quando l'ordine si chiudera&grave;).</li>
					<li><span style="color:yellow;background-color:#999;">In chiusura</span>: mancano pochi giorni alla chiusura dell'ordine (controllare la colonna "Data di chiusura" per sapere quando l'ordine si chiudera&grave;).</li>
					<li>Aprira&grave; il ....: ordine non ancora aperto.</li>
				</ul>
				<!-- img alt="stato ordine" src="<?php echo Configure::read('App.img.cake'); ;?>/info_stato.png" style="border:1px solid #999;float:right;margin-bottom:10px;margin-right: 15px;" /> 
				<div class="clr"></div -->
			</li>
			<li>
				<img alt="leggi articolo" src="<?php echo Configure::read('App.img.cake'); ;?>/apps/32x32/kontact.png" /> leggi l'<b>articolo</b> relativo al produttore
			</li>
			<li> 
				<img alt="ci sono prodotti ordinati" src="<?php echo Configure::read('App.img.cake'); ;?>/cesta-piena.png" /> hai <b>ordinato</b> articoli relativi al produttore scelto
			</li>
			<li> 
				<img alt="nessun prodotto ordinato" src="<?php echo Configure::read('App.img.cake'); ;?>/cesta-vuota.png" /> <b>non</b> hai <b>ordinato</b> articoli relativi al produttore scelto
			</li>
			<li>
				<img alt="ancora nel carrello" src="<?php echo Configure::read('App.img.cake'); ;?>/cart_add.png" /> gli articoli del produttore scelto sono ancora nel <b>carrello</b>, quindi puoi ancora modificare le quantita&grave; o aggiungere nuovi articoli
			</li>
			<li>
				<img alt="non + nel carrello" src="<?php echo Configure::read('App.img.cake'); ;?>/cart_no.png" /> gli articoli del produttore scelto <b>non</b> sono piu&grave; ancora nel <b>carrello</b>, quindi non puoi piu&grave; cambiare l'ordine
			</li>
			<li> 
				<img alt="Invia mail" src="<?php echo Configure::read('App.img.cake'); ;?>/icons/16x16/email.png" /> invia una mail al <b>referente</b>
			</li>
</ol>