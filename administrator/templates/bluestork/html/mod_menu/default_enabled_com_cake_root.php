<?php
// No direct access.
defined('_JEXEC') or die;

/* @var $menu JAdminCSSMenu */
$shownew = (boolean)$params->get('shownew', 1);



$i=-1;

/*
 * G R O U P _ R O O T
 */
if(in_array(group_id_root,$user->getAuthorisedGroups())) {
	$i++;
	$_menus_root[$i]['level'] = 0;
	$_menus_root[$i]['label'] = "Root";
	$_menus_root[$i]['url'] = "#";
	$i++;
	$_menus_root[$i]['level'] = 1;
	$_menus_root[$i]['label'] = "Joomla";
	$_menus_root[$i]['url'] = "#";
	$i++;
	$_menus_root[$i]['level'] = 2;
	$_menus_root[$i]['label'] = "Utenti";
	$_menus_root[$i]['url'] = "index.php?option=com_users&view=users";
	$i++;
	$_menus_root[$i]['level'] = 2;
	$_menus_root[$i]['label'] = "Menu";
	$_menus_root[$i]['url'] = "index.php?option=com_menus&view=menus";
	$i++;
	$_menus_root[$i]['level'] = 2;
	$_menus_root[$i]['label'] = "Articoli";
	$_menus_root[$i]['url'] = "index.php?option=com_content";
	$i++;
	$_menus_root[$i]['level'] = 1;
	$_menus_root[$i]['label'] = "Sistema";
	$_menus_root[$i]['url'] = "#";
	$i++;
	$_menus_root[$i]['level'] = 2;
	$_menus_root[$i]['label'] = "Scegli l'organizzazione";
	$_menus_root[$i]['url'] = "index.php?option=com_cake&controller=Organizations&action=choice";
	$i++;
	$_menus_root[$i]['level'] = 2;
	$_menus_root[$i]['label'] = "Organizzazioni";
	$_menus_root[$i]['url'] = "#";
	$i++;
	$_menus_root[$i]['level'] = 3;
	$_menus_root[$i]['label'] = "Elenco organizzazioni - G.A.S.";
	$_menus_root[$i]['url'] = "index.php?option=com_cake&controller=Organizations&action=index&type=GAS";
	$i++;
	$_menus_root[$i]['level'] = 3;
	$_menus_root[$i]['label'] = "Aggiungi organizzazione - G.A.S.";
	$_menus_root[$i]['url'] = "index.php?option=com_cake&controller=Organizations&action=add";
	$i++;
	$_menus_root[$i]['level'] = 3;
	$_menus_root[$i]['label'] = "Passaggi per nuovo G.A.S.";
	$_menus_root[$i]['url'] = "index.php?option=com_cake&controller=Organizations&action=step_add&type=GAS";
	$i++;
	$_menus_root[$i]['level'] = 3;
	$_menus_root[$i]['label'] = "Controllo G.A.S.";
	$_menus_root[$i]['url'] = "index.php?option=com_cake&controller=Organizations&action=index_ctrl&type=GAS";
	$i++;
	$_menus_root[$i]['separator'] = true;

	/*$i++;
	$_menus_root[$i]['level'] = 3;
	$_menus_root[$i]['label'] = "Elenco organizzazioni - Produttori";
	$_menus_root[$i]['url'] = "index.php?option=com_cake&controller=Organizations&action=index&type=PRODGAS";
	*/
	$i++;
    $_menus_root[$i]['level'] = 3;
    $_menus_root[$i]['label'] = "Elenco organizzazioni - Produttori";
    $_menus_root[$i]['url'] = "index.php?option=com_cake&controller=ProdGasSuppliersImports&action=index";
    $i++;
    $_menus_root[$i]['level'] = 3;
    $_menus_root[$i]['label'] = "Elenco organizzazioni - SocialMarket";
    $_menus_root[$i]['url'] = "index.php?option=com_cake&controller=SocialmarketOrganizations&action=index";
    $i++;
	$_menus_root[$i]['level'] = 3;
	$_menus_root[$i]['label'] = "Elenco organizzazioni - Gestori Patti";
	$_menus_root[$i]['url'] = "index.php?option=com_cake&controller=PactSupplierImports&action=index";
	$i++;
	$_menus_root[$i]['separator'] = true;
	$i++;
	$_menus_root[$i]['level'] = 3;
	$_menus_root[$i]['label'] = "D.E.S.";
	$_menus_root[$i]['url'] = "index.php?option=com_cake&controller=DesRoot&action=index";
	$i++;
	$_menus_root[$i]['separator'] = true;
	$i++;
	$_menus_root[$i]['level'] = 3;
	$_menus_root[$i]['label'] = "Azioni con ACL";
	$_menus_root[$i]['url'] = "index.php?option=com_cake&controller=OrdersActions&action=index";
	$i++;
	$_menus_root[$i]['level'] = 3;
	$_menus_root[$i]['label'] = "Az. dei Templates/Gruppi";
	$_menus_root[$i]['url'] = "index.php?option=com_cake&controller=TemplatesOrdersStates&action=index";
	$i++;
	$_menus_root[$i]['level'] = 3;
	$_menus_root[$i]['label'] = "Join Az. ACL e Az. Templates/Gruppi";
	$_menus_root[$i]['url'] = "index.php?option=com_cake&controller=TemplatesOrdersStatesOrdersActions&action=index";
	$i++;
	$_menus_root[$i]['level'] = 2;
	$_menus_root[$i]['label'] = "Pagamenti";
	$_menus_root[$i]['url'] = "#";
	$i++;
	$_menus_root[$i]['level'] = 3;
	$_menus_root[$i]['label'] = "Mail manager / tesorieri";
	$_menus_root[$i]['url'] = "index.php?option=com_cake&controller=OrganizationsPays&action=mail";
	$i++;
	$_menus_root[$i]['level'] = 3;
	$_menus_root[$i]['label'] = "Prospetto";
	$_menus_root[$i]['url'] = "index.php?option=com_cake&controller=OrganizationsPays&action=index";
	$i++;
	$_menus_root[$i]['level'] = 3;
	$_menus_root[$i]['label'] = "Gestisci pagamenti ".date('Y')." <label class='label label-success'>new</label>";
	$_menus_root[$i]['url'] = "index.php?option=com_cake&controller=Connects&action=index&c_to=admin/organizations-pays&a_to=index";
	$_menus_root[$i]['target'] = "_blank";	
	$i++;
    $_menus_root[$i]['level'] = 3;
    $_menus_root[$i]['label'] = "1 Genera i pagamenti ".date('Y')." <label class='label label-success'>new</label>";
    $_menus_root[$i]['url'] = "index.php?option=com_cake&controller=Connects&action=index&c_to=admin/organizations-pays&a_to=generate";
	$_menus_root[$i]['target'] = "_blank";
	$i++;
    $_menus_root[$i]['level'] = 3;
    $_menus_root[$i]['label'] = "2 Genera tutte le fatture ".date('Y');
    $_menus_root[$i]['url'] = "index.php?option=com_cake&controller=OrganizationsPays&action=invoice_create_pdfs";

	$i++;
	$_menus_root[$i]['level'] = 3;
	$_menus_root[$i]['label'] = "Genera fattura";
	$_menus_root[$i]['url'] = "index.php?option=com_cake&controller=OrganizationsPays&action=invoice_create_form";
	$i++;
	$_menus_root[$i]['level'] = 3;
	$_menus_root[$i]['label'] = "Stampa documenti";
	$_menus_root[$i]['url'] = "index.php?option=com_cake&controller=Pages&action=export_docs_root";	
	$i++;
	$_menus_root[$i]['level'] = 2;
	$_menus_root[$i]['label'] = "Cron";
	$_menus_root[$i]['url'] = "index.php?option=com_cake&controller=Crons&action=index";
	$i++;
	$_menus_root[$i]['level'] = 2;
	$_menus_root[$i]['label'] = "Migration Eg3";
	$_menus_root[$i]['url'] = "#";
	$i++;
	$_menus_root[$i]['level'] = 3;
	$_menus_root[$i]['label'] = "Categorie degli articoli";
	$_menus_root[$i]['url'] = "index.php?option=com_cake&controller=DatabaseDate&action=migration_eg3_categories_articles";
	$i++;
	$_menus_root[$i]['level'] = 3;
	$_menus_root[$i]['label'] = "Produttori";
	$_menus_root[$i]['url'] = "index.php?option=com_cake&controller=DatabaseDate&action=migration_eg3_suppliers";
	$i++;
	$_menus_root[$i]['level'] = 3;
	$_menus_root[$i]['label'] = "Utenti";
	$_menus_root[$i]['url'] = "'index.php?option=com_cake&controller=DatabaseDate&action=migration_eg3_users";
	$i++;
	$_menus_root[$i]['level'] = 3;
	$_menus_root[$i]['label'] = "Utenti password";
	$_menus_root[$i]['url'] = "index.php?option=com_cake&controller=DatabaseDate&action=migration_eg3_users_pwd";
	$i++;
	$_menus_root[$i]['level'] = 3;
	$_menus_root[$i]['label'] = "Produttori dell'organizzazione";
	$_menus_root[$i]['url'] = "index.php?option=com_cake&controller=DatabaseDate&action=migration_eg3_suppliers_organizations";
	$i++;
	$_menus_root[$i]['level'] = 3;
	$_menus_root[$i]['label'] = "Referenti";
	$_menus_root[$i]['url'] = "index.php?option=com_cake&controller=DatabaseDate&action=migration_eg3_suppliers_organizations_referents";
	$i++;
	$_menus_root[$i]['level'] = 3;
	$_menus_root[$i]['label'] = "Articoli";
	$_menus_root[$i]['url'] = "index.php?option=com_cake&controller=DatabaseDate&action=migration_eg3_articles";
	$i++;
	$_menus_root[$i]['level'] = 3;
	$_menus_root[$i]['label'] = "Drop tmp_migration_codice";
	$_menus_root[$i]['url'] = "index.php?option=com_cake&controller=DatabaseDate&action=migration_eg3_drop_field";
	$i++;
	$_menus_root[$i]['level'] = 2;
	$_menus_root[$i]['label'] = "Configurazione";
	$_menus_root[$i]['url'] = "#";
	$i++;
	$_menus_root[$i]['level'] = 3;
	$_menus_root[$i]['label'] = "Php info";
	$_menus_root[$i]['url'] = "index.php?option=com_cake&controller=Configurations&action=php_info";
	$i++;
	$_menus_root[$i]['level'] = 3;
	$_menus_root[$i]['label'] = "APC";
	$_menus_root[$i]['url'] = "index.php?option=com_cake&controller=Configurations&action=apc_info";
	$i++;
	$_menus_root[$i]['level'] = 3;
	$_menus_root[$i]['label'] = "APC clean";
	$_menus_root[$i]['url'] = "index.php?option=com_cake&controller=Configurations&action=apc_clean";
	$i++;
	$_menus_root[$i]['level'] = 3;
	$_menus_root[$i]['label'] = "Logs";
	$_menus_root[$i]['url'] = "index.php?option=com_cake&controller=Logs&action=index";
	$i++;
	$_menus_root[$i]['level'] = 3;
	$_menus_root[$i]['label'] = "Db collation";
	$_menus_root[$i]['url'] = "index.php?option=com_cake&controller=Configurations&action=db_change_collation";
	$i++;
	$_menus_root[$i]['level'] = 3;
	$_menus_root[$i]['label'] = "Db prefix";
	$_menus_root[$i]['url'] = "index.php?option=com_cake&controller=Configurations&action=db_change_prefix";
	$i++;
	$_menus_root[$i]['level'] = 3;
	$_menus_root[$i]['label'] = "Analytics";
	$_menus_root[$i]['url'] = "https://www.google.com/analytics";
	$_menus_root[$i]['target'] = "_blank";
	$i++;
	$_menus_root[$i]['level'] = 3;
	$_menus_root[$i]['label'] = "Bing";
	$_menus_root[$i]['url'] = "http://www.bing.com/toolbox/webmaster/";
	$_menus_root[$i]['target'] = "_blank";
	$i++;
	$_menus_root[$i]['level'] = 3;
	$_menus_root[$i]['label'] = "Help";
	$_menus_root[$i]['url'] = "index.php?option=com_cake&controller=Helps&action=index";
	$i++;
	$_menus_root[$i]['level'] = 3;
	$_menus_root[$i]['label'] = "Il mio profilo";
	$_menus_root[$i]['url'] = "index.php?option=com_admin&task=profile.edit&id=".$user->get('id');
	$i++;
	$_menus_root[$i]['level'] = 2;
	$_menus_root[$i]['label'] = "Mail";
	$_menus_root[$i]['url'] = "#";
	$i++;
	$_menus_root[$i]['level'] = 3;
	$_menus_root[$i]['label'] = "Elenco mail";
	$_menus_root[$i]['url'] = "index.php?option=com_cake&controller=Mails&action=root_index";
	$i++;
	$_menus_root[$i]['level'] = 3;
	$_menus_root[$i]['label'] = "Invia mail";
	$_menus_root[$i]['url'] = "index.php?option=com_cake&controller=Mails&action=root_send";
	$i++;
	$_menus_root[$i]['level'] = 3;
	$_menus_root[$i]['label'] = "Logs cron mail <label class='label label-success'>new</label>";
	$_menus_root[$i]['url'] = "index.php?option=com_cake&controller=Connects&action=index&c_to=admin/mail-sends&a_to=index";
	$_menus_root[$i]['target'] = "_blank";	

	$i++;
	$_menus_root[$i]['level'] = 1;
	$_menus_root[$i]['label'] = "Amminstrazione dati";
	$_menus_root[$i]['url'] = "#";
	$i++;
	$_menus_root[$i]['level'] = 2;
	$_menus_root[$i]['label'] = "Test Ajax Services <label class='label label-success'>new</label>";
	$_menus_root[$i]['url'] = "index.php?option=com_cake&controller=Connects&action=index&c_to=admin/tests&a_to=ajax";
	$_menus_root[$i]['target'] = "_blank";	
	$i++;
	$_menus_root[$i]['level'] = 2;
	$_menus_root[$i]['label'] = "Test Code";
	$_menus_root[$i]['url'] = "index.php?option=com_cake&controller=Tests&action=index";
	$i++;
	$_menus_root[$i]['level'] = 2;
	$_menus_root[$i]['label'] = "Test Order LifeCycles";
	$_menus_root[$i]['url'] = "index.php?option=com_cake&controller=TestLifeCycles&action=index";	
	$i++;
	$_menus_root[$i]['level'] = 2;
	$_menus_root[$i]['label'] = "Consegne => Ordini";
	$_menus_root[$i]['url'] = "index.php?option=com_cake&controller=ValidateDates&action=index_deliveries";
	$i++;
	$_menus_root[$i]['level'] = 2;
	$_menus_root[$i]['label'] = "Rich pagamento => Ordini";
	$_menus_root[$i]['url'] = "index.php?option=com_cake&controller=ValidateDates&action=index_request_payments";
	$i++;
	$_menus_root[$i]['level'] = 2;
	$_menus_root[$i]['label'] = "GCalendar per consegne";
	$_menus_root[$i]['url'] = "index.php?option=com_cake&controller=ValidateDates&action=gcalendar_deliveries";
	$i++;
	$_menus_root[$i]['level'] = 2;
	$_menus_root[$i]['label'] = "Query";
	$_menus_root[$i]['url'] = "index.php?option=com_cake&controller=Sqls&action=index";
	$i++;
	$_menus_root[$i]['level'] = 1;
	$_menus_root[$i]['label'] = "Produttori";
	$_menus_root[$i]['url'] = "#";
	$i++;
	$_menus_root[$i]['level'] = 2;
	$_menus_root[$i]['label'] = "Produttori di PortAlGas";
	$_menus_root[$i]['url'] = "index.php?option=com_cake&controller=Suppliers&action=index";
	if(!empty($organization_id)) {
		$i++;
		$_menus_root[$i]['level'] = 2;
		$_menus_root[$i]['label'] = "Produttori dell'organizzazione ".$organization_name;
		$_menus_root[$i]['url'] = "index.php?option=com_cake&controller=SuppliersOrganizations&action=index";
		$i++;
		$_menus_root[$i]['level'] = 2;
		$_menus_root[$i]['label'] = "Referenti dell'organizzazione ".$organization_name;
		$_menus_root[$i]['url'] = "index.php?option=com_cake&controller=SuppliersOrganizationsReferents&action=index&group_id=18";		
	}
	$i++;
	$_menus_root[$i]['level'] = 2;
	$_menus_root[$i]['label'] = "Categorie produttori";
	$_menus_root[$i]['url'] = "index.php?option=com_cake&controller=CategoriesSuppliers&action=index";
	$i++;
	$_menus_root[$i]['level'] = 2;
	$_menus_root[$i]['label'] = "Importa listino del produttore <label class='label label-success'>new</label>";
	$_menus_root[$i]['url'] = "index.php?option=com_cake&controller=Connects&action=index&c_to=admin/articles&a_to=import-supplier";
	$_menus_root[$i]['target'] = "_blank";		
	$i++;
	$_menus_root[$i]['level'] = 1;
	$_menus_root[$i]['label'] = "Utility";
	$_menus_root[$i]['url'] = "#";
	$i++;
	$_menus_root[$i]['level'] = 2;
	$_menus_root[$i]['label'] = "Stampa documenti";
	$_menus_root[$i]['url'] = "index.php?option=com_cake&controller=Pages&action=export_docs_root";
	if(!empty($organization_id)) {
		$i++;
		$_menus_root[$i]['level'] = 1;
		$_menus_root[$i]['label'] = "GDXP per l'organizzazione ".$organization_name." <label class='label label-success'>new</label>";
		$_menus_root[$i]['url'] = "index.php?option=com_cake&controller=Connects&action=index&c_to=admin/gdxps&a_to=suppliers-index";
		$_menus_root[$i]['target'] = "_blank";	
	}
} 
/*
echo "<pre>";
print_r($_menus_root);
echo "</pre>";
*/