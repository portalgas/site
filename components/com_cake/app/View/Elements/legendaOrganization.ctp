<div class="legenda">

<p>gasAlias: <input size="25" type="text" value="gastorino" id="gasAlias" /></p>
<p>gasAliasSEO: <input size="25" type="text" value="gas-gastorino" id="gasAliaSEO" /></p>
<p>organization_id <input size="3" type="text" value="" id="organizationId" /></p>
<p><a href="#" id="custom">parametrizza</a></p>

<!--  			server 			 -->
<!--  			server			 -->
<!--  			server			 -->
<h1 class="header" id="header-server">Server (ssh)</h1>
<div class="contenuto" id="contenuto-server" style="display:none;">
<table cellpadding="0" cellspacing="0" >	
	<tr>
		<td width="25%">
			<b>Sotto dominio</b><br /><span class="gasAlias"></span>.portalgas.it
		</td>
		<td width="75%">
			Glesys: domains => Create new records => A record
		</td>
	</tr>
	<tr>
		<td>
			<b>Cron</b>
		</td>
		<td>
			nel file /var/portalgas/cron/config.conf settare la variabile TOT_ORGANIZATION
			<br />
			permessi ai files 755
		</td>
	</tr>
	<tr>
		<td>
			<b>Exe</b>
		</td>
		<td>
			/var/portalgas/org_new.sh <span class="organizationId"></span>
			 <ul>
			 	<li>Loghi per pdf e mail, directory e permessi</li>
			 	<li>Database (consegna da definire)</li>
			 	<li>Database (categorie articoli)</li>
			 </ul>
			
		</td>
	</tr>
	<tr>
		<td>Apache</td>
		<td>		
			<pre class="shell" rel="abilitazione virtualhost">
	cd /etc/apache2/sites-available
	cp arcoiris.portalgas.it <span class="gasAlias"></span>.portalgas.it
	vi <span class="gasAlias"></span>.portalgas.it
	ESC : SHIFT  
	%s/arcoiris/<span class="gasAlias"></span>/g
</pre>

<pre class="shell" rel="vi gasAlias.portalgas.it">
	&lt;VirtualHost *:80&gt;
	        ServerAdmin info@portalgas.it
	        ServerName <span class="gasAlias"></span>.portalgas.it
	        ServerAlias <span class="gasAlias"></span>.portalgas.it
	        Redirect / http://www.portalgas.it/home-<span class="gasAliasSEO"></span>/consegne-<span class="gasAliasSEO"></span>
	
	        # ${APACHE_LOG_DIR} /var/log/apache2/
	        ErrorLog ${APACHE_LOG_DIR}/error-<span class="gasAlias"></span>.portalgas.it.log
	
	        # Possible values include: debug, info, notice, warn, error, crit, alert, emerg.
	        LogLevel notice
	
	        CustomLog ${APACHE_LOG_DIR}/access-<span class="gasAlias"></span>.portalgas.it.log combined env=!dontlog
	&lt;/VirtualHost&gt;
</pre>

<pre class="shell" rel="abilitazione virtualhost">
	a2ensite <span class="gasAlias"></span>.portalgas.it
	/etc/init.d/apache2 reload
</pre>
		</td>
	</tr>
</table>	
</div> 




<!--  			codice 			 -->
<!--  			codice 			 -->
<!--  			codice 			 
<h1 class="header" id="header-codice">Codice</h1>
<div class="contenuto" id="contenuto-codice" style="display:none;">
<table cellpadding="0" cellspacing="0" >	
	<tr>
		<td>
			<b>Core</b>
		</td>
		<td>
			in <?php echo Configure::read('App.root');?>/components/com_cake/app/core.php aggiungere
			<pre class="shell">
Configure::write('urlFrontEndToRewriteCakeRequest',array(
       ...
       ...
		array('controller'=>'Deliveries','action'=>'tabsEcomm','admin'=>false,'SEO'=>'fai-la-spesa-<span class="gasAliasSEO"></span>'),
		...
			</pre>
			
<ul>
	<li>fai-la-spesa-gas-...</li>
	<li>carrello-gas-...</li>
	<li>preview-carrello-gas-...</li>
	<li>consegne-gas-...</li>
	<li>dispensa-gas-...</li>
	<li>stampe-gas-...</li>
</ul>			
		</td>
	</tr>
</table>
</div>
-->

<!--  			database 			 -->
<!--  			database 			 -->
<!--  			database 		
<h1 class="header" id="header-database">Database</h1>
<div class="contenuto" id="contenuto-database" style="display:none;">
<table cellpadding="0" cellspacing="0" >
	<tr>
		<td>
			creare Delivery con 
			<ul>
				<li>sys = Y</li>
				<li>luogo = Da definire</li>
				<li>data = <?php echo Configure::read('DeliveryToDefinedDate');?></li>
				<li>orario_da = 00:00:00</li>
				<li>orario_a = 00:00:00</li>
				<li>nota = La data e il luogo della consegna sono ancora da definire</li>
				<li>nota_evidenza = ALERT</li>
			</ul>
<pre class="shell" rel="sql">			
	INSERT INTO k_deliveries  
	(`id`, `organization_id`, `luogo`, `data`, `orario_da`, `orario_a`, `nota`, `nota_evidenza`, 
	`isToStoreroom`, `isToStoreroomPay`, `stato_elaborazione`, 
	`isVisibleFrontEnd`, `isVisibleBackOffice`, `sys`, `created`, `modified`) 
	VALUES 
	(NULL, '<span class="organizationId"></span>', 'Da definire', '2025-01-01', '00:00:00', '00:00:00', 
	'La data e il luogo della consegna sono ancora da definire', 'ALERT', 'N', 'N', 'OPEN', 
	'Y', 'Y', 'Y', '2014-10-10 00:00:00', NULL);		
			</pre>
		</td>
	</tr>
	<tr>
		<td>
			creare Categorie di articoli 
<pre class="shell" rel="sql">			
	INSERT INTO 
	k_categories_articles (organization_id, parent_id, lft, rght, name, description) 
	(
	 SELECT <span class="organizationId"></span> , parent_id, lft, rght, name, description 
	 FROM k_categories_articles 
	 WHERE organization_id = 1
	)
			</pre>			
		</td>
	</tr>		
</table>
</div>
-->


<!--  			gruppi 			 -->
<!--  			gruppi 			 -->
<!--  			gruppi	 		 -->
<h1 class="header" id="header-gruppi">Gruppi</h1>
<div class="contenuto" id="contenuto-gruppi" style="display:none;">
<table cellpadding="0" cellspacing="0" >
	<tr>
		<td>
			creare i <b>gruppi</b>
			<ul>
				<li>Sotto <b>Registred</b> => GasPages<span class="gasAlias"></span> => segnare <b>group_id</b></li>
			</ul>			
			creo gruppi => <b>livello d'accesso</b> da associare alla voce di menù per gestire la visibilità 
			<ul>
				<li>Registred<span class="gasAlias"></span> e associo il gruppo corrispondente</li>
			</ul>			
		</td>
	</tr>
</table>
</div>








<!--  			template_joomla 			 -->
<!--  			template_joomla 			 -->
<!--  			template_joomla 			 -->
<h1 class="header" id="header-template_joomla">Joomla Template</h1>
<div class="contenuto" id="contenuto-template_joomla" style="display:none;">
<table cellpadding="0" cellspacing="0" >
	<tr>
		<td>
			<ul>
				<li>/templates/v01/templateDetails.xml
<pre class="shell">					
	&lt;field name="organizationId" type="list" default="0"
		label="Organizzazione Associata"
		description="Organizzazione Associata al templates"
		filter="integer"
	&gt;
		&lt;option value="0"&gt;portale&lt;/option&gt;
		&lt;option value="1"&gt;cavagnetta (id 1)&lt;/option&gt;
		&lt;option value="2"&gt;oca sansalvario (id 2)&lt;/option&gt;

	&lt;/field&gt;
	
	
	&lt;field name="organizationSEO" type="list" default="0"
		label="Codice SEO per Organizzazione"
		description="Codice SEO per Organizzazione Associata al templates"
		filter="string"
	&gt;
		&lt;option value="portale"&gt;portale&lt;/option&gt;
		&lt;option value="cavagnetta"&gt;cavagnetta&lt;/option&gt;
		&lt;option value="ocasansalvario"&gt;ocasansalvario&lt;/option&gt;
		
		&lt;/field&gt;
</pre>							
				</li>
				<li><b>Duplicare</b> un template di joomla</li>				
				<li>in Gestione template: Modifica stile
					<ul>
						<li>settare organizationId</li>
						<li>settare organizationSEO</li>
					</ul>
				</li>




<!--  			
				<li>gestione colore custom: modificare /templates/v01/index.php e /templates/v01/css/default-min.css
<pre class="shell">				
	jQuery('h2').each(function() {
		jQuery(this).addClass('h2-color');
	});
	jQuery('#header-menu').addClass('header-menu-color');
	jQuery('a').each(function() {
		jQuery(this).addClass('color');
	});		
</pre>	
				
				<table>
					<tr>
						<th>Class</th>
						<th>Blu</th>
						<th>Verde</th>
						<th>Rosso</th>
					</tr>
					<tr>
						<th>.header-menu-color</th>
						<td><div style="background-color:#0A659E;width:100%;height:20px;color:#fff">#0A659E</div></td>
						<td><div style="background-color:#4B6A38;width:100%;height:20px;color:#fff">#4B6A38</div></td>
						<td><div style="background-color:#DE321B;width:100%;height:20px;color:#fff">#DE321B</div></td>
					</tr>
					<tr>
						<th>a.color, a.color:link, a.color:visited</th>
						<td><div style="background-color:#0060A6;width:100%;height:20px;color:#fff">#0060A6</div></td>
						<td><div style="background-color:#3C851B;width:100%;height:20px;color:#fff">#3C851B</div></td>
						<td><div style="background-color:#E5412A;width:100%;height:20px;color:#fff">#E5412A</div></td>
					</tr>
					<tr>
						<th>.h2-color</th>
						<td><div style="background-color:#1E83C2;width:100%;height:20px;color:#fff">#1E83C2</div></td>
						<td><div style="background-color:#7BAA5F;width:100%;height:20px;color:#fff">#7BAA5F</div></td>
						<td><div style="background-color:#F34F3E;width:100%;height:20px;color:#fff">#F34F3E</div></td>
					</tr>
				</table>
				</li>
			</ul>
-->

						
		</td>
	</tr>
</table>
</div>




<!--  			config joomla 			 -->
<!--  			config joomla 			 -->
<!--  			config joomla 			 -->
<h1 class="header" id="header-joomla_config">Joomla configuration</h1>
<div class="contenuto" id="contenuto-joomla_config" style="display:none;">
<table cellpadding="0" cellspacing="0" >
	<tr>
		<th>Categoria</th>
		<td class="border-bottom">creare categoria per pagine del Gas
			<ul>
				<li>Title: Pages <span class="gasAlias"></span></li>
				<li>Alias: <span class="gasAliasSEO"></span></li>
				<li>aggiornare database Organizations.j_page_category_id = category_id</li>
			</ul>	
		</td>
	</tr>	
	<tr>
		<th>Articolo</th>
		<td class="border-bottom">creare articolo per home
			<ul>
				<li>Title: Gas <span class="gasAlias"></span></li>
				<li>Alias: home-<span class="gasAliasSEO"></span></li>
				<li>Categoria: Pages <span class="gasAlias"></span></li>
				<li>Accesso: public</li>
			</ul>
			
			&lt;h2&gt; per i titoli
			<br /><br />
			&lt;h3&gt; per i sotto-titoli
			con 
<pre class="shell" rel="testo generico">
	Appena nati entriamo subito nella community di PortAlGas per gestire i nostri ordini e le nostre consegne, si parte!
</pre>	
			
			
<pre class="shell" rel="footer per mail">
	&lt;p class="emailbox"&gt;&lt;a href="/contattaci?contactOrganizationId=<span class="organizationId"></span>" title="scrivi una mail al G.A.S."&gt;Contattaci scrivendo una mail&lt;/a&gt;&lt;/p&gt;
	&lt;p>{flike}&lt;/p&gt;
</pre>	
<p>segnare <b>article_id</b> per logo 
<ul>
	<li>filesystem /var/www/portalgas/images/organizations/contents/</li>
	<li>database Organizations.img1</li>
</ul>	
	</li></p>
		</td>
	</tr>	
	<tr>
		<th>Logo</th>
		<td class="border-bottom">dall'ID dell'articolo per la home
			<ul>
				<li>mettere il file in <?php echo Configure::read('App.img.upload.content');?>/N.jpg  con width <b>massima</b> 250px</li>
				<li>se non c'&egrave; il logo, prendere la <?php echo Configure::read('App.img.upload.content');?>/0.jpg</li>
				<li>aggiornare database Organizations.img1 = N.<b>jpg</b></li>
			</ul>
		</td>
	</tr>	
	<tr>
		<th>Menù Top</th>
		<td class="border-bottom">
			<ul>
				<li>Menu / Gestione menù => nuovo menù</li>
				<li>"Titolo": Top menu Gas <span class="gasAliasSEO"></span></li>
				<li>"Tipo menu": topmenu-<span class="gasAliasSEO"></span> - qui va inserito un nome che deve essere unico per ogni menu</li>
				<li>Seleziono tutte le voce del menù <b>Top menu Gas GassePiossasco</b> e "Seleziona il menu per Spostare/Copiare" (<span style="color:red;">Nota</span>: non il menù Cavagnetta perchè ha più voci)</li>
				<li>Per ogni voce di menù copiata cambiare
					<ul>
						<li>Alias (<span class="gasAliasSEO"></span>)</li>
						<li>Accesso: Registred...</li>
						<li>Stile template</li>
						<li>Opzioni visualizzazione pagina -> Titolo pagina Browser</li>
					</ul>				
				</li>
				<li>Per home del Gas associare il suo articolo</li>
			</ul>
		</td>
	</tr>
	<!-- tr>
		<td colspan="2">
			<img width="100%" src="<?php echo Configure::read('App.img.cake');?>/print_screen_joomla_menu.jpg" title="" border="0" />
		</td>
	</tr>
	<tr>
		<td colspan="2">
			<ul>
				<li>home-...
					<ul>
						<li>consegne-gas-...</li>
						<li>dispensa-gas-...</li>
						<li>fai-la-spesa-gas-...</li>
						<li>carrello-gas-...</li>
						<li>preview-carrello-gas-...</li>
						<li>stampe-gas-...</li>
						<li>contattaci-gas-...</li>
						<li>my-profilo</li>	
					</ul>	
				</li>
			</ul>		
		</td>
	</tr  -->
	<tr>
		<th>Modulo: top menù</th>
		<td class="border-bottom">Creare modulo Nuovo => menu
					<ul>
						<li>Titolo: Top menu <span class="gasAlias"></span></li>
						<li>Mostra titolo: Si</li>
						<li>Position: position-menu-left</li>
						<li>Livello iniziale: 2</li>	
						<li>Livello finale: 2</li>	
						<li>Mostra sotto-voci di menu: no</li>
						<li>Opzioni avanzate -> Suffisso classe menu: <b>" nav navbar-nav"</b></li>
						<li>Assegnazione modulo: solo su quelle selezionate</li>
					</ul>	

		</td>
	</tr>
	<tr>
		<th>Modulo: Gas - Contenuto immagine <i>position-cols-right</i></th>
		<td class="border-bottom">Assegnarlo alla voce di menù della home	
		</td>
	</tr>	
	<tr>
		<th>Modulo: Facebook LikeBox <i>position-cols-right</i></th>
		<td class="border-bottom">Assegnarlo alla voce di menù della home	
		</td>
	</tr>
	<tr>
		<th class="border-bottom">Modulo: FaceBook Html <i>position-cols-right</i></th>
		<td>Assegnarlo alla voce di menù della home	
		</td>
	</tr>	
		
</table>
</div>



<!--  			utenti 			 -->
<!--  			utenti 			 -->
<!--  			utenti	 		 -->
<h1 class="header" id="header-utenti">Utenti</h1>
<div class="contenuto" id="contenuto-utenti" style="display:none;">
<table cellpadding="0" cellspacing="0" >
	<!-- tr>
		<td colspan="2">
			<img width="100%" src="<?php echo Configure::read('App.img.cake');?>/print_screen_import_user.jpg" title="" border="0" />
		</td>
	</tr -->
	<tr>
		<th>Manager</th>
		<td>			
			creo utente di default
			<ul>
				<li>Nome: Assistente PortAlGas</li>
				<li>Nome utente Login: info@<span class="gasAlias"></span>.portalgas.it</li>
				<li>p0rtA1gax</li>
				<li>Mail info@<span class="gasAlias"></span>.portalgas.it</li>
				<li>Gruppo 	gasSystem / gasManager / gasCassiere / gasManagerConsegne / gasSuperReferente / gasTesoriere</li>
			</ul>  			
			<ul>
				<li>Ricevi email di sistema = Si</li>
			</ul>
		</td>
	</tr>
	<tr>
		<th>Manager</th>
		<td>			
			creo utente dispensa
			<ul>
				<li>Nome: Dispensa PortAlGas</li>
				<li>Nome utente Login: dispensa@<span class="gasAlias"></span>portalgas.it</li>
				<li>p0rtA1gax</li>
				<li>Mail dispensa@<span class="gasAlias"></span>portalgas.it</li>
			</ul>  			
			<ul>
				<li>Ricevi email di sistema = No</li>
			</ul>
		</td>
	</tr>
	<tr>
		<th rowspan="2">Users</th>
		<td>
			a tutti gli utenti associare i <b>gruppi</b>
			<ul>
				<li>Registred di default</li>
				<li>Sotto Registred => GasPages[nome organizazione]</li>
			</ul>
		</td>
	</tr>
	<tr>
		<td>
			Regola per creare la <b>Password di default</b> degli utenti
			<ul>
				<li>primi 5 crt del nome del gas (senza il prefisso Gas)</li> 
				<li>il simbolo - </li> 
				<li>ultimi due caratteri anno</li> 
				<li>es aaaaa-nn<br />
					    arcoi-15<br />
					    avigl-15</li>
			</ul>
		</td>
	</tr>	
	<tr>
		<td colspan="2">
			creare gli <b>utenti</b>
			<ul>
				<li>Dispensa con gruppo gasDispensa (e solo quel gruppo)</li>
				<li>Tesoriere con gruppo gasTesoriere (tesoriere@gaslacavagnetta.portalgas.it)</li>
			</ul>
		</td>
	</tr>	
	<tr>
		<td colspan="2">
			Se devo <b>disabilitare</b> utenti in Produzione e lasciarli solo in Test
<pre class="shell" rel="sql">			
	update j_users set block = 1 where organization_id = x 
	and name != 'Francesco Actis';			
</pre>
		</td>
	</tr>	
</table>
</div>

<!--  			sitemap.xml 			 -->
<!--  			sitemap.xml 			 -->
<!--  			sitemap.xml	 		 -->
<h1 class="header" id="header-sitemap">sitemap.xml</h1>

<pre class="shell">
  &lt;url&gt;
    &lt;loc&gt;http://www.portalgas.it/home-<span class="gasAliasSEO"></span>&lt;/loc&gt;
    &lt;lastmod&gt;<?php echo date('Y-m-d');?>&lt;/lastmod&gt;
    &lt;changefreq&gt;yearly&lt;/changefreq&gt;
  &lt;/url&gt;
  &lt;url&gt;
    &lt;loc&gt;http://www.portalgas.it/home-<span class="gasAliasSEO"></span>/consegne-<span class="gasAliasSEO"></span>&lt;/loc&gt;
    &lt;lastmod&gt;<?php echo date('Y-m-d');?>&lt;/lastmod&gt;
    &lt;changefreq&gt;yearly&lt;/changefreq&gt;
  &lt;/url&gt;
 </pre> 
  


<!--  			override joomla dinamic			 -->
<!--  			override joomla dinamic			 -->
<!--  			override joomla dinamic			 -->
<h1 class="header" id="header-joomla_override_dinamic">Joomla override gestito dinamicamente</h1>
<div class="contenuto" id="contenuto-joomla_override_dinamic" style="display:none;">
<table cellpadding="0" cellspacing="0" >
	<tr>
		<td>
			<b>Mod_gas_organization_choice *</b>
		</td>
		<td>
			<?php echo Configure::read('App.root');?>/modules/mod_organization_choice/tmpl/default.php
		</td>
		<td>Menù tendina G.A.S.</td>
	</tr>
	<tr>
		<td>
			<b>administrator/../com_media</b>
		</td>
		<td>
			<?php echo Configure::read('App.root');?>/administrator/com_media/media.php
			<br />
			<?php echo Configure::read('App.root');?>/administrator/com_media/views/images/tmpl/default.php
		</td>
		<td>Directory profilate per Organization.seo</td>
	</tr>
	<tr>
		<td>
			<b>administrator/../mod_status</b>
		</td>
		<td>
			<?php echo Configure::read('App.root');?>/administrator/modules/mod_status/tmpl/default.php
		</td>
		<td>Header in alto a destra</td>
	</tr>
	<tr>
		<td>
			<b>plugin/user/joomla</b>
		</td>
		<td>
			<?php echo Configure::read('App.root');?>/plugin/user/joomla/joomla.php
		</td>
		<td>Redirect dopo login</td>
	</tr>
	
	<tr>
		<td>
			<b>com_users</b>
		</td>
		<td>
			<ul>
				<li>aggiungere case in <?php echo Configure::read('App.root');?>/components/com_users/models/forms/registration.xml (non + utilizzato)</li>
				<li>aggiungere case in <?php echo Configure::read('App.root');?>/templates/v01/html/com_users/registration/default.php (lettura da database)</li>
			</ul>
		</td>
		<td>Menù tendina G.A.S.</td>
	</tr>
	
</table>
</div>	
	
<h2>pulire cache in joomla</h2>

<p>(*) vedere Help</p>
</div>

<style>
h1.header {
    background: none repeat scroll 0 0 #D1D3D4 !important;
    font-size: 18px;
    line-height: 24px;
    margin: 0 0 20px;
    padding: 10px;
    text-transform: capitalize;
}
.header {
	cursor:pointer;
}
.border-bottom {
	border-bottom: 2px solid #555 !important;
}
</style>
<script type="text/javascript">
jQuery(document).ready(function() {
	jQuery(".header").click(function() {	
		dataElement = jQuery(this).attr('id');
		dataElementArray = dataElement.split('-');
		var idElement = dataElementArray[1];

		if(jQuery("#contenuto-"+idElement).css('display')=='none')
			jQuery("#contenuto-"+idElement).show();
		else
			jQuery("#contenuto-"+idElement).hide();
	});	
	
	jQuery("#custom").click(function() {
		jQuery(".gasAlias").html(jQuery("#gasAlias").val());
		jQuery(".gasAliasSEO").html(jQuery("#gasAliaSEO").val());
		jQuery(".organizationId").html(jQuery("#organizationId").val());
		
		return false;
	});	
});
</script>