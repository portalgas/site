<?php
$this->Html->addCrumb(__('Home'), ['controller' => 'Pages', 'action' => 'home']);
$this->Html->addCrumb(__('Help'));
echo $this->Html->getCrumbList(array('class'=>'crumbs'));
?>
<style type="text/css">
.cakeContainer h1 {
    background: none repeat scroll 0 0 #F0F0F0;
    border-radius: 5px 5px 5px 5px;
    color: #003D4C;
    font-size: 20px;
    padding: 3px;
}
.cakeContainer h2 {
    color: #003D4C;
    font-size: 18px;
    height: auto;
    margin-left: 5px;
}
.cakeContainer h3 {
	background: none repeat scroll 0 0 #e0e0e0;
	font-size:16px;
	margin-left:5px;
	color:#003D4C;
	padding: 3px;
}
ul.menu {
	font-size:16px;
}
ul.menu li, 
ul.menu.li ul li {
	margin-bottom: 5px;
}
ul.menu a {
	text-decoration: none;
	font-weight: normal;
}
ul.help, 
ul.help li ul  {
	list-style-type: none;
	padding: 0px;
	margin: 5px;
}
ul.help  {
	font-size:16px;
}
</style>

<div class="mails">
	<h2 class="ico-config">
		<?php echo __('Help');?>
	</h2>
</div>

<ul class="menu">
<!--
	<li><a href="#consegna_flusso">Consegna: flusso</a></li>
	<li><a href="#ordini_flusso">Ordini: flusso</a></li>
	<li><a href="#request_payment_flusso">Richieste di pagamento: flusso (cancellazione Carrello / dati archiviati in Statistiche)</a></li>
	<li><a href="#stato_elaborazione">Stato elaborazione: flusso</a></li>
	<li><a href="#supplier_flusso">Produttori: flusso</a></li>
	<li><a href="#articles_flusso">Articoli: flusso</a></li>
	<li><a href="#carrello_flusso">Carrello: flusso</a></li>
	<li><a href="#carrello_backoffice_flusso">Carrello backoffice: flusso</a></li>
	<li><a href="#dispensa">Dispensa</a></li>
	<li><a href="#stampe">Stampe</a></li>
	<li><a href="#dettaglio_informazioni">Dettaglio informazioni</a></li>
-->	<?php 
	if($isRoot) {
	?>
<!--	
	<li><a href="#model">Model</a></li>
	<li><a href="#order_actions_component">OrderActions component</a></li>
-->	
	<li><a href="#account">account</a></li>
	<li><a href="#prod_gas">produttori (ProdGas)</a></li>
	<li><a href="#cron">cron</a></li>
	<li><a href="#tcpdf">tcpdf</a></li>
	<li><a href="#upgrade_joomla">Joomla Upgrade</a></li>
	<li><a href="#cakephp">Cakephp</a></li>
	<li><a href="#upgrade_cakephp">Cakephp Upgrade</a></li>
	<li><a href="#integration_cake_joomla">Integration Cake/Joomla</a>
		<ul>
			<li><a href="#integration_cake_joomla_seo_categories">SEO categorie</a></li>		
			<li><a href="#integration_cake_joomla_seo_news">SEO news</a></li>		
			<li><a href="#integration_cake_joomla_override">Override</a></li>		
			<li><a href="#integration_cake_joomla_templates_joomla">Templates Joomla</a></li>
			<li><a href="#integration_cake_joomla_componenti_joomla">Componenti Joomla</a></li>
			<li><a href="#integration_cake_joomla_moduli_joomla">Moduli Joomla</a></li>
			<li><a href="#integration_cake_joomla_voce_menu_joomla">Gestione "Tipo di voce di menu"</a></li>
			<li><a href="#integration_cake_joomla_code_joomla">Codice di Joomla</a></li>
			<li><a href="#integration_cake_joomla_request">Request</a></li>
			<li><a href="#integration_cake_joomla_gestione_org_id">Gestione organization_id</a></li>
		</ul>
	</li>
	<li><a href="#php_setting">PHP setting</a>
		<ul>
			<li><a href="#php_setting_fastcgi">FastCgi</a></li>
			<li><a href="#php_setting_apc">APC (Alternative PHP Cache)</a></li>
		</ul>	
	</li>
	<li><a href="#joomla_setting">Joomla setting</a>
		<ul>
			<li><a href="#joomla_setting_configurazione_globale_sistema">Configurazione globale -> sistema</a></li>
			<li><a href="#joomla_setting_sito_informazioni_di_sistema">Sito -> informazioni di sistema</a></li>
			<li><a href="#joomla_setting_sito_informazioni_di_sistema_impostazioni_PHP">Sito -> informazioni di sistema -> impostazioni PHP</a></li>
			<li><a href="#joomla_setting_sito_informazioni_di_sistema_permessi_cartelle">Sito -> informazioni di sistema -> permessi cartelle</a></li>
			<li><a href="#joomla_setting_gestione_estensioni_avvisi">Gestione estensioni -> avvisi</a></li>
			<li><a href="#joomla_setting_configurazione_globale_server">Configurazione globale -> server</a></li>
		</ul>
	</li>
	<li><a href="#permessi_file_cartelle">Permessi file cartelle</a>
		<ul>
			<li><a href="#permessi_file_cartelle_chmod">Chmod</a></li>
			<li><a href="#permessi_file_cartelle_chown">Chown</a></li>		
		</ul>
	</li>
	<li><a href="#linux">Linux</a></li>
	<li><a href="#apache">Apache</a></li>
	<li><a href="#php">PHP</a></li>
	<li><a href="#smtp">Smtp e posta</a></li>
	<li><a href="#postfix">Postfix</a></li>
	<li><a href="#database">Database</a></li>
	<li><a href="#phpmyadmin">PhpMyadmin</a></li>
	<li><a href="#compressione_css_js">Compressione .css .js</a></li>
	<li><a href="#portalgas_com">portalgas.com (app)</a></li>
	<li><a href="#migrazione">Migrazione / Allineamento</a></li>
	<li><a href="#google">Google</a></li>
	<li><a href="#gcalendar">Gcalendar</a></li>
	<li><a href="#apple">Apple</a></li>
	<li><a href="#facebook">Facebook</a></li>
	<li><a href="#gdxp">Interoperabilità gdxp</a></li>
	<!--
	<li><a href="#test_performace">Test performace</a></li>
	-->
	<li><a href="#errori">Errori</a>
		<ul>
			<li><a href="#errori_decoding">ERR_CONTENT_DECODING_FAILED</a></li>
			<li><a href="#errori_proxy">Proxy stronca html</a></li>
			<li><a href="#errori_export_excel">Export excel</a></li>
			<li><a href="#errori_cake_log">Non scrive i log in /tmp/logs</a></li>
			<li><a href="#errori_front_end_login">Front end: login</a></li>
			<li><a href="#errori_backoffice_login">BackOffice: login</a></li>
			<li><a href="#errori_float">Somma calcolo totale importo di un ordine</a></li>
			<li><a href="#errori_sql">Sql errata</a></li>
			<li><a href="#errori_model_not_found">Model not found</a></li>
			<li><a href="#errori_form_not_mapped">Il form non è mappato con il Model</a></li>
			<li><a href="#errori_cache_not_configure">Cache configuraton</a></li>
			<li><a href="#errori_gest_estensioni1">Gestione estensioni 1</a></li>
			<li><a href="#errori_gest_estensioni2">Gestione estensioni 2</a></li>
			<li><a href="#errori_phpmyadmin_import">PhpMyAdmin import</a></li>
			<li><a href="#errori_mail_invio">Invio mail</a></li>
			<li><a href="#errori_test_portalgas">test.portalgas mappa file di portalgas</a></li>
			<li><a href="#tcp">problemi con pdf</a></li>
		</ul>	
	</li>
	<li><a href="#link">Link</a></li>
	<li><a href="#query">Query</a></li>
	<li><a href="#mail">Mail</a></li>
	<?php 
	}
	?>
</ul>


<ul class="help">
	<?php
	/*
	<li><a name="consegna_flusso"></a>
		<?php include('box_consegna_flusso.ctp');?>
	</li>
	<li><a name="ordini_flusso"></a>
		<?php include('box_ordini_flusso.ctp');?>
	</li>
	<li><a name="request_payment_flusso"></a>
		<?php include('box_request_payment_flusso.ctp');?>
	</li>	
	<li><a name="stato_elaborazione"></a>
		<?php include('box_stato_elaborazione.ctp');?>
	</li>	
	<li><a name="supplier_flusso"></a>
		<?php include('box_supplier_flusso.ctp');?>
	</li>	
	<li><a name="articles_flusso"></a>
		<?php include('box_articles_flusso.ctp');?>
	</li>
	<li><a name="carrello_flusso"></a>
		<?php include('box_carrello_flusso.ctp');?>
	</li>
	<li><a name="carrello_backoffice_flusso"></a>
		<?php include('box_carrello_backoffice_flusso.ctp');?>
	</li>	
	<li><a name="dispensa"></a>
		<?php include('box_dispensa.ctp');?>		
	</li>
	<li><a name="stampe"></a>
		<?php include('box_stampe.ctp');?>
	</li>
	<li><a name="dettaglio_informazioni"></a>
		<?php include('box_dettaglio_informazioni.ctp');?>
	</li>	
	<?php 
	*/
	if($isRoot) {
		/*
		<li><a name="model"></a>
			<?php include('box_model.ctp');?>
		</li>	
		<li><a name="order_actions_component"></a>
			<?php include('box_order_actions_component.ctp');?>
		</li>
		*/
	?>
		<li><a name="account"></a>
			<?php include('box_account.ctp');?>
		</li>	
		<li><a name="prod_gas"></a>
			<?php include('box_prod_gas.ctp');?>
		</li>	
		<li><a name="cron"></a>
			<?php include('box_cron.ctp');?>
		</li>	
		<li><a name="tcpdf"></a>
			<?php include('box_tcpdf.ctp');?>
		</li>	
		<li><a name="tcpdf"></a>
			<?php include('box_tcpdf.ctp');?>
		</li>	
		<li><a name="upgrade_joomla"></a>
			<?php include('box_upgrade_joomla.ctp');?>
		</li>	
		<li><a name="cakephp"></a>
			<?php include('box_cakephp.ctp');?>
		</li>	
		<li><a name="upgrade_cakephp"></a>
			<?php include('box_upgrade_cakephp.ctp');?>
		</li>	
		<li><a name="integration_cake_joomla"></a><h1>Integration Cake/Joomla</h1>	
			<ul>
				<li><a name="integration_cake_joomla_seo_categories"></a><h2>SEO categories</h2>
					<?php include('box_integration_cake_joomla_seo_categories.ctp');?>
				</li>
				<li><a name="integration_cake_joomla_seo_news"></a><h2>SEO news</h2>
					<?php include('box_integration_cake_joomla_seo_news.ctp');?>
				</li>
				<li><a name="integration_cake_joomla_override"></a><h2>Override</h2>
					<?php include('box_integration_cake_joomla_override.ctp');?>
				</li>
				<li><a name="integration_cake_joomla_templates_joomla"></a><h2>Templates Joomla</h2>
					<?php include('box_integration_cake_joomla_templates_joomla.ctp');?>
				</li>
				<li><a name="integration_cake_joomla_componenti_joomla"></a><h2>Componeti di Joomla</h2>
					<?php include('box_integration_cake_joomla_componenti_joomla.ctp');?>
				</li>
				<li><a name="integration_cake_joomla_moduli_joomla"></a><h2>Moduli di Joomla</h2>
					<?php include('box_integration_cake_joomla_moduli_joomla.ctp');?>
				</li>
				<li><a name="integration_cake_joomla_voce_menu_joomla"></a><h2>Gestione "Tipo di voce di menu"</h2>
					<?php include('box_integration_cake_joomla_voce_menu_joomla.ctp');?>
				</li> 	
				<li><a name="integration_cake_joomla_code_joomla"></a><h2>Codice di Joomla</h2>
		    		<?php include('box_integration_cake_joomla_code_joomla.ctp');?>	
				</li>
				<li><a name="integration_cake_joomla_request"></a><h2>Request</h2>
					<?php include('box_integration_cake_joomla_request.ctp');?>	
				</li>
				<li><a name="integration_cake_joomla_gestione_org_id"></a><h2>Gestione organization_id</h2>
					<?php include('box_integration_cake_joomla_gestione_org_id.ctp');?>
				</li>
			</ul>		
		</li>
		<li><a name="php_setting"></a>
			<?php include('box_php_setting.ctp');?>
		</li>
		<li><a name="joomla_setting"></a>
			<?php include('box_joomla_setting.ctp');?>
			<ul>
				<li><a name="joomla_setting_configurazione_globale_sistema"></a>
					<?php include('box_joomla_setting_configurazione_globale_sistema.ctp');?>
				</li>
				<li><a name="joomla_setting_sito_informazioni_di_sistema_impostazioni_PHP"></a>
					<?php include('box_joomla_setting_sito_informazioni_di_sistema_impostazioni_PHP.ctp');?>
				</li>
				<li><a name="joomla_setting_sito_informazioni_di_sistema_permessi_cartelle"></a>
					<?php include('box_joomla_setting_sito_informazioni_di_sistema_permessi_cartelle.ctp');?>
				</li>
				<li><a name="joomla_setting_gestione_estensioni_avvisi"></a>
					<?php include('box_joomla_setting_gestione_estensioni_avvisi.ctp');?>
				</li>
				<li><a name="joomla_setting_configurazione_globale_server"></a>
					<?php include('box_joomla_setting_configurazione_globale_server.ctp');?>
				</li>
			</ul>
		</li>
		<li><a name="permessi_file_cartelle"></a>
			<h1>Permessi file/cartelle</h1>		
			<ul>
				<li><a name="permessi_file_cartelle_chmod"></a>
					<?php include('box_permessi_file_cartelle_chmod.ctp');?>
				</li>
				<li><a name="permessi_file_cartelle_chown"></a>
					<?php include('box_permessi_file_cartelle_chown.ctp');?>					
				</li>
			</ul>
		</li>
		<li><a name="linux"></a>
			<?php include('box_linux.ctp');?>
		</li>
		<li><a name="apache"></a>
			<?php include('box_apache.ctp');?>
		</li>
		<li><a name="php"></a>
			<?php include('box_php.ctp');?>
		</li>
		<li><a name="smtp"></a>
			<?php include('box_smtp.ctp');?>
		</li>
		<li><a name="postfix"></a>
			<?php include('box_postfix.ctp');?>
		</li>
		<li><a name="database"></a>
			<?php include('box_database.ctp');?>
		</li>
		<li><a name="phpmyadmin"></a>
			<?php include('box_phpmyadmin.ctp');?>
		</li>
		<li><a name="compressione_css_js"></a>
			<?php include('box_compressione_css_js.ctp');?>
		</li>
		<li><a name="portalgas_com"></a>
			<?php include('box_portalgas_com.ctp');?>
		</li>
		<li><a name="migrazione"></a>
			<?php include('box_migrazione.ctp');?>
		</li>
		<li><a name="google"></a>
			<?php include('box_google.ctp');?>
		</li>
		<li><a name="gcalendar"></a>
			<?php include('box_gcalendar.ctp');?>
		</li>
		<li><a name="apple"></a>
			<?php include('box_apple.ctp');?>
		</li>
		<li><a name="facebook"></a>
			<?php include('box_facebook.ctp');?>
		</li>
		<li><a name="gdxp"></a>
			<?php include('box_gdxp.ctp');?>
		</li>
		<?php
		/*
		<li><a name="test_performace"></a>
			<?php include('box_test_performace.ctp');?>
		</li>
		*/
		?>
		<li><a name="errori"></a>
			<?php include('box_errori.ctp');?>
		</li>
		<li><a name="link"></a>
			<?php include('box_link.ctp');?>
		</li>
		<li><a name="query"></a>
			<?php include('box_query.ctp');?>
		</li>
		<li><a name="mail"></a>
			<?php include('box_mail.ctp');?>
		</li>
	<?php 
	} // end if($isRoot)
	?>			
</ul>
