<?php
// No direct access.
defined('_JEXEC') or die;

/* @var $menu JAdminCSSMenu */
$shownew = (boolean)$params->get('shownew', 1);



$i=-1;

/*
 * G R O U P _ R O O T
 */
if(in_array(group_id_root,$user->getAuthorisedGroups()) || in_array(group_id_root_supplier,$user->getAuthorisedGroups())) { 
	$i++;
	$_menus[$i]['level'] = 0;
	$_menus[$i]['label'] = "G.A.S.";
	$_menus[$i]['url'] = "#";
	$i++;
	$_menus[$i]['level'] = 1;
	$_menus[$i]['label'] = "Produttori di PortAlGas";
	$_menus[$i]['url'] = "index.php?option=com_cake&controller=Suppliers&action=index";
	$i++;
	$_menus[$i]['level'] = 1;
	$_menus[$i]['label'] = "Categorie produttori";
	$_menus[$i]['url'] = "index.php?option=com_cake&controller=CategoriesSuppliers&action=index";
	if(in_array(group_id_root, $user->getAuthorisedGroups())) {
		$i++;
		$_menus[$i]['level'] = 1;
		$_menus[$i]['label'] = "Ruoli";
		$_menus[$i]['url'] = "index.php?option=com_cake&controller=UserGroupMaps&action=intro";
		
	}
	/*
	if ($user->authorise('core.manage', 'com_content')) { //if ($user->authorise('core.admin', 'com_content'))
		$createContent =  $shownew && $user->authorise('core.create', 'com_content');

		$i++;
		$_menus[$i]['level'] = 1;
		$_menus[$i]['label'] = JText::_('MOD_MENU_COM_CONTENT_ARTICLE_MANAGER'); // fractis new JMenuNode(JText::_('MOD_MENU_COM_CONTENT_ARTICLE_MANAGER'), 'index.php?option=com_content', 'class:article'), $createContent);
		$_menus[$i]['url'] = "index.php?option=com_content";
	
		if ($createContent)	{
			$i++;
			$_menus[$i]['level'] = 1;
			$_menus[$i]['label'] = JText::_('MOD_MENU_COM_CONTENT_NEW_ARTICLE'); // // fractis new JMenuNode(JText::_('MOD_MENU_COM_CONTENT_NEW_ARTICLE'), 'index.php?option=com_content&task=article.add', 'class:newarticle')
			$_menus[$i]['url'] = "index.php?option=com_content&task=article.add";
		}

		$i++;
		$_menus[$i]['level'] = 1;
		$_menus[$i]['label'] = JText::_('MOD_MENU_COM_CONTENT_CATEGORY_MANAGER'); // new JMenuNode(JText::_('MOD_MENU_COM_CONTENT_CATEGORY_MANAGER'), 'index.php?option=com_categories&extension=com_content', 'class:category'), $createContent
		$_menus[$i]['url'] = "index.php?option=com_categories&extension=com_content";
		$i++;
		$_menus[$i]['level'] = 1;
		$_menus[$i]['label'] = JText::_('MOD_MENU_COM_CONTENT_NEW_CATEGORY'); // new JMenuNode(JText::_('MOD_MENU_COM_CONTENT_NEW_CATEGORY'), 'index.php?option=com_categories&task=category.add&extension=com_content', 'class:newarticle');
		$_menus[$i]['url'] = "index.php?option=com_categories&task=category.add&extension=com_content";
	}
	*/
} // end if(in_array(group_id_root,$user->getAuthorisedGroups()) || in_array(group_id_root_supplier,$user->getAuthorisedGroups())) 

if(!empty($organization_id)) {
	/*
	 * M A N A G E R + S T A T I S T I C
	 */	
	if(in_array(group_id_manager, $user->getAuthorisedGroups()) || 
	   in_array(group_id_cassiere, $user->getAuthorisedGroups()) || in_array(group_id_referent_cassiere, $user->getAuthorisedGroups()) ||
	   in_array(group_id_referent_tesoriere, $user->getAuthorisedGroups())) {
			$i++;
			$_menus[$i]['level'] = 0;
			$_menus[$i]['label'] = $organization_name;
			$_menus[$i]['url'] = "#";
		
			if(in_array(group_id_manager, $user->getAuthorisedGroups())) {

				$i++;
				$_menus[$i]['level'] = 1;
				$_menus[$i]['label'] = "Il proprio G.A.S.";
				$_menus[$i]['url'] = "index.php?option=com_cake&controller=OrganizationsPayments&action=edit";
				$i++;
				$_menus[$i]['level'] = 1;
				$_menus[$i]['label'] = "Gestione prepagato";
				$_menus[$i]['url'] = "#";
				$i++;
				$_menus[$i]['level'] = 2;
				$_menus[$i]['label'] = "Configura";
				$_menus[$i]['url'] = "index.php?option=com_cake&controller=OrganizationsCashs&action=index";
				$i++;
				$_menus[$i]['level'] = 2;
				$_menus[$i]['label'] = "Prospetto utenti";
				$_menus[$i]['url'] = "index.php?option=com_cake&controller=OrganizationsCashs&action=ctrl";
				if($hasCashFilterSupplier=='Y') {
					$i++;
					$_menus[$i]['level'] = 2;
					$_menus[$i]['label'] = "Prepagato per produttori <label class='label label-success'>new</label>";
					$_menus[$i]['url'] = "index.php?option=com_cake&controller=Connects&action=index&c_to=admin/cashes&a_to=supplier-organization-filter";
				}				
				$i++;
				$_menus[$i]['level'] = 1;
				$_menus[$i]['label'] = "Utenti";
				$_menus[$i]['url'] = "#";
				$i++;
				$_menus[$i]['level'] = 2;
				$_menus[$i]['label'] = "Il mio profilo";
				$_menus[$i]['url'] = "index.php?option=com_admin&task=profile.edit&id=".$user->get('id');
				$i++;
				$_menus[$i]['level'] = 2;
				$_menus[$i]['label'] = "Gestione completa";
				$_menus[$i]['url'] = "index.php?option=com_users&view=users";
				$createUser = $shownew && $user->authorise('core.create', 'com_users');				
				if ($createUser) {
					$i++;
					$_menus[$i]['level'] = 2;
					$_menus[$i]['label'] = JText::_('MOD_MENU_COM_USERS_ADD_USER');
					$_menus[$i]['url'] = "index.php?option=com_users&task=user.add";
				}
				$i++;
				$_menus[$i]['level'] = 2;
				$_menus[$i]['label'] = "Visualizzazione rapida";
				$_menus[$i]['url'] = "index.php?option=com_cake&controller=Users&action=index";
				$i++;
				$_menus[$i]['level'] = 2;
				$_menus[$i]['label'] = "Utenti disattivati";
				$_menus[$i]['url'] = "index.php?option=com_cake&controller=Users&action=index_block";
				$i++;
				$_menus[$i]['level'] = 2;
				$_menus[$i]['label'] = "Entrata/uscita utenti";
				$_menus[$i]['url'] = "index.php?option=com_cake&controller=Users&action=index_date";
				if($hasUserRegistrationExpire == 'Y') {
					$i++;
					$_menus[$i]['level'] = 2;
					$_menus[$i]['label'] = "Gestione rinnovo";
					$_menus[$i]['url'] = "index.php?option=com_cake&controller=Users&action=index_flag_privacy";
				}
				if($hasUserFlagPrivacy == 'Y') {
					$i++;
					$_menus[$i]['level'] = 2;
					$_menus[$i]['label'] = "Gestione accettazione privacy";
					$_menus[$i]['url'] = "index.php?option=com_cake&controller=Users&action=index_flag_privacy";
				}
				if($hasUserFlagPrivacy == 'Y' && $hasUserRegistrationExpire == 'Y') {
					$i++;
					$_menus[$i]['level'] = 2;
					$_menus[$i]['label'] = "Gestione accettazione privacy e rinnovo";
					$_menus[$i]['url'] = "index.php?option=com_cake&controller=Users&action=index_flag_privacy";
				}
				$i++;
				$_menus[$i]['level'] = 2;
				$_menus[$i]['label'] = "Importa utenti";
				$_menus[$i]['url'] = "index.php?option=com_cake&controller=CsvImports&action=users";
				$i++;
				$_menus[$i]['level'] = 2;
				$_menus[$i]['label'] = "Stampa utenti/referenti";
				$_menus[$i]['url'] = "index.php?option=com_cake&controller=Pages&action=export_docs_users";
				$i++;
				$_menus[$i]['level'] = 1;
				$_menus[$i]['label'] = "Ruoli";
				$_menus[$i]['url'] = "index.php?option=com_cake&controller=UserGroupMaps&action=intro";					
				if($hasFieldArticleCategoryId=='Y') {
					$i++;
					$_menus[$i]['level'] = 1;
					$_menus[$i]['label'] = "Categorie articoli";
					$_menus[$i]['url'] = "index.php?option=com_cake&controller=CategoriesArticles&action=index";
				}
				$i++;
				$_menus[$i]['level'] = 1;
				$_menus[$i]['label'] = "Calendario attività";
				$_menus[$i]['url'] = "#";
				$i++;
				$_menus[$i]['level'] = 2;
				$_menus[$i]['label'] = "Calendario attività";
				$_menus[$i]['url'] = "index.php?option=com_cake&controller=Events&action=index";
				$i++;
				$_menus[$i]['level'] = 2;
				$_menus[$i]['label'] = "Calendario attività storiche";
				$_menus[$i]['url'] = "index.php?option=com_cake&controller=Events&action=index_history";
				$i++;
				$_menus[$i]['level'] = 2;
				$_menus[$i]['label'] = "Tipologie attività";
				$_menus[$i]['url'] = "index.php?option=com_cake&controller=EventTypes&action=index";
			} // end if(in_array(group_id_manager, $user->getAuthorisedGroups()))
	
			$i++;
			$_menus[$i]['level'] = 1;
			$_menus[$i]['label'] = "Statistiche";
			$_menus[$i]['url'] = "#";
			$i++;
			$_menus[$i]['level'] = 2;
			$_menus[$i]['label'] = "Statistiche";
			$_menus[$i]['url'] = "index.php?option=com_cake&controller=Statistics&action=index";
			$i++;
			$_menus[$i]['level'] = 2;
			$_menus[$i]['label'] = "Esporta";
			$_menus[$i]['url'] = "index.php?option=com_cake&controller=Statistics&action=export";
		} // end group_id_manager  

		/*
		 * R E F E R E N T I
		 */
		if(in_array(group_id_referent, $user->getAuthorisedGroups()) || 
		   in_array(group_id_super_referent, $user->getAuthorisedGroups()) || 
		   in_array(group_id_referent_tesoriere, $user->getAuthorisedGroups())) {
			   			   
				/*
				 * R E F E R E N T I
				 */			   
				$i++;
				$_menus[$i]['level'] = 0;
				$_menus[$i]['label'] = "Referenti";
				$_menus[$i]['url'] = "#";		   
				$i++;
				$_menus[$i]['level'] = 1;
				$_menus[$i]['label'] = "Produttori";
				$_menus[$i]['url'] = "#";		   
				$i++;
				$_menus[$i]['level'] = 2;
				$_menus[$i]['label'] = "Produttori di PortAlGas";
				$_menus[$i]['url'] = "index.php?option=com_cake&controller=Suppliers&action=index_relations";		   
				$i++;
				$_menus[$i]['level'] = 2;
				$_menus[$i]['label'] = "Produttori del G.A.S.";
				$_menus[$i]['url'] = "index.php?option=com_cake&controller=SuppliersOrganizations&action=index";
				$i++;
				$_menus[$i]['level'] = 2;
				$_menus[$i]['label'] = "Voto ai produttori G.A.S.";
				$_menus[$i]['url'] = "index.php?option=com_cake&controller=SuppliersVotes&action=index";
				if(in_array(group_id_super_referent, $user->getAuthorisedGroups())) {	
					$i++;
					$_menus[$i]['level'] = 2;
					$_menus[$i]['label'] = "Monitoraggio ordini dei produttori";
					$_menus[$i]['url'] = "index.php?option=com_cake&controller=MonitoringSuppliersOrganizations&action=home";
				}

				$i++;
				$_menus[$i]['level'] = 1;
				$_menus[$i]['label'] = "Referenti";
				$_menus[$i]['url'] = "index.php?option=com_cake&controller=SuppliersOrganizationsReferents&action=index&group_id=".group_id_referent;
					
				/*
				 *  A R T I C L E S
				 */	
				$i++;
				$_menus[$i]['level'] = 1;
				$_menus[$i]['label'] = "Articoli";
				$_menus[$i]['url'] = "#";
				$i++;
				$_menus[$i]['level'] = 2;
				$_menus[$i]['label'] = "Elenco Articoli";
				$_menus[$i]['url'] = "index.php?option=com_cake&controller=Articles&action=context_articles_index";
				if($hasArticlesOrder=='Y' && $user->user['User']['hasArticlesOrder']=='Y') {
					$i++;
					$_menus[$i]['level'] = 2;
					$_menus[$i]['label'] = "Modifica Rapida Articoli";
					$_menus[$i]['url'] = "index.php?option=com_cake&controller=Articles&action=context_articles_index_quick";
				}
				$i++;
				$_menus[$i]['level'] = 2;
				$_menus[$i]['label'] = "Stampa articoli";
				$_menus[$i]['url'] = "index.php?option=com_cake&controller=Pages&action=export_docs_articles";
				if($hasFieldArticleCategoryId=='Y') {
					$i++;
					$_menus[$i]['level'] = 2;
					$_menus[$i]['label'] = "Gestisci categorie";
					$_menus[$i]['url'] = "index.php?option=com_cake&controller=Articles&action=gest_categories";
				} 
				$i++;
				$_menus[$i]['level'] = 2;
				$_menus[$i]['label'] = "Modifica prezzi";
				$_menus[$i]['url'] = "index.php?option=com_cake&controller=Articles&action=index_edit_prices_default";
				$i++;
				$_menus[$i]['level'] = 2;
				$_menus[$i]['label'] = "Modifica prezzi in %";
				$_menus[$i]['url'] = "index.php?option=com_cake&controller=Articles&action=index_edit_prices_percentuale"; 
				if($hasArticlesOrder=='Y' && $user->user['User']['hasArticlesOrder']=='Y') {
					$i++;
					$_menus[$i]['level'] = 2;
					$_menus[$i]['label'] = "Modifica prezzo degli articolo associati agli ordini";
					$_menus[$i]['url'] = "index.php?option=com_cake&controller=ArticlesOrders&action=order_choice";
				}
				else {
					$i++;
					$_menus[$i]['level'] = 2;
					$_menus[$i]['label'] = "Gestione articoli da associare ad un ordine";
					$_menus[$i]['url'] = "index.php?option=com_cake&controller=Articles&action=index_flag_presente_articlesorders";				
				}
				if($hasBookmarsArticles=='Y') {
					$i++;
					$_menus[$i]['level'] = 2;
					$_menus[$i]['label'] = "Articoli preferiti";
					$_menus[$i]['url'] = "index.php?option=com_cake&controller=BookmarksArticles&action=index";					
				}
				$i++;
				$_menus[$i]['separator'] = true;

				if(in_array(group_id_super_referent, $user->getAuthorisedGroups()) && 
			      $hasArticlesGdxp=='Y') {
					$i++;
					$_menus[$i]['level'] = 2;
					$_menus[$i]['label'] = "Esporta articoli in GDXP <label class='label label-success'>new</label>";
					$_menus[$i]['url'] = "index.php?option=com_cake&controller=Connects&action=index&c_to=admin/gdxps&a_to=articles-index";						
					$i++;
					$_menus[$i]['level'] = 2;
					$_menus[$i]['label'] = "Importa articoli in GDXP <label class='label label-success'>new</label>";
					$_menus[$i]['url'] = "index.php?option=com_cake&controller=Connects&action=index&c_to=admin/import-files&a_to=json";	


				}
				$i++;
				$_menus[$i]['level'] = 2;
				$_menus[$i]['label'] = "Importa articoli";
				$_menus[$i]['url'] = "index.php?option=com_cake&controller=CsvImports&action=articles";	
				$i++;
				$_menus[$i]['level'] = 2;
				$_menus[$i]['label'] = "Esporta articoli per reimportarli";
				$_menus[$i]['url'] = "index.php?option=com_cake&controller=CsvImports&action=articles_form_export";	
				$i++;
				$_menus[$i]['level'] = 2;
				$_menus[$i]['label'] = "Importa articoli da esportazione precedente";
				$_menus[$i]['url'] = "index.php?option=com_cake&controller=CsvImports&action=articles_form_import";	
											
				/*
				 *  O R D E R S
				 */					
				$i++;
				$_menus[$i]['level'] = 1;
				$_menus[$i]['label'] = "Ordini";
				$_menus[$i]['url'] = "#";				
				$i++;
				$_menus[$i]['level'] = 2;
				$_menus[$i]['label'] = "Ordini";
				$_menus[$i]['url'] = "index.php?option=com_cake&controller=Orders&action=index";				
				$i++;
				$_menus[$i]['level'] = 2;
				$_menus[$i]['label'] = "Aggiungi un nuovo ordine";
				$_menus[$i]['url'] = "index.php?option=com_cake&controller=Orders&action=add";			
				$i++;
				$_menus[$i]['level'] = 2;
				$_menus[$i]['label'] = "Aggiungi un nuovo ordine (modalità semplificata)";
				$_menus[$i]['url'] = "index.php?option=com_cake&controller=Orders&action=easy_add";			
				$i++;
				$_menus[$i]['level'] = 2;
				$_menus[$i]['label'] = "Ordini storici";
				$_menus[$i]['url'] = "index.php?option=com_cake&controller=Orders&action=index_history";			
				$i++;
				$_menus[$i]['level'] = 2;
				$_menus[$i]['label'] = "Ricorsione  <label class='label label-success'>new</label>";
				// $_menus[$i]['url'] = "index.php?option=com_cake&controller=LoopsOrders&action=index";
				$_menus[$i]['url'] = "index.php?option=com_cake&controller=Connects&action=index&c_to=admin/loops_orders&a_to=/index";
				$i++;
				$_menus[$i]['level'] = 2;
				$_menus[$i]['label'] = "Monitoraggio Ordini";
				$_menus[$i]['url'] = "index.php?option=com_cake&controller=MonitoringOrders&action=home";	
				if(in_array(group_id_root,$user->getAuthorisedGroups())) {
					$i++;
					$_menus[$i]['level'] = 2;
					$_menus[$i]['label'] = "Ripristina ordini cancellati";
					$_menus[$i]['url'] = "index.php?option=com_cake&controller=BackupOrdersOrders&action=index";
				}
				/*
				$i++;
				$_menus[$i]['level'] = 2;
				$_menus[$i]['label'] = "Controlli dati aggregati sugli ordini";
				$_menus[$i]['url'] = "index.php?option=com_cake&controller=SummaryOrders&action=orders_validate";			
				*/
				$i++;
				$_menus[$i]['level'] = 1;
				$_menus[$i]['label'] = "Invia mail";
				$_menus[$i]['url'] = "index.php?option=com_cake&controller=Mails&action=send";	
		} // end referents						

		$i++;
		$_menus[$i]['level'] = 0;
		$_menus[$i]['label'] = "Utility";
		$_menus[$i]['url'] = "#";
        /*
        $i++;
        $_menus[$i]['level'] = 1;
        $_menus[$i]['label'] = "Generali";
        $_menus[$i]['url'] = "index.php?option=com_cake&controller=Utilities&action=index";
        $i++;
        $_menus[$i]['level'] = 1;
        $_menus[$i]['label'] = "Il mio profilo";
        $_menus[$i]['url'] = "index.php?option=com_admin&task=profile.edit&id=".$user->get('id');
        */
		$i++;
		$_menus[$i]['level'] = 1;
		$_menus[$i]['label'] = "Stampa documenti";
		$_menus[$i]['url'] = "index.php?option=com_cake&controller=Pages&action=export_docs_user_intro";
		if($hasDocuments=='Y' && in_array(group_id_manager, $user->getAuthorisedGroups())) {
			$i++;
			$_menus[$i]['level'] = 1;
			$_menus[$i]['label'] = "Gestione documenti front-end <label class='label label-success'>new</label>";
			$_menus[$i]['url'] = "index.php?option=com_cake&controller=Connects&action=index&c_to=admin/documents&a_to=organization-index";			
		}
		if(in_array(group_id_manager, $user->getAuthorisedGroups()) || 
		   in_array(group_id_cassiere, $user->getAuthorisedGroups()) || 
		   in_array(group_id_tesoriere, $user->getAuthorisedGroups())) {
			$i++;
			$_menus[$i]['level'] = 1;
			$_menus[$i]['label'] = "Crea documenti";
			$_menus[$i]['url'] = "index.php?option=com_cake&controller=DocsCreates&action=index";		
		}
		$i++;
		$_menus[$i]['level'] = 1;
		$_menus[$i]['label'] = "Mail";
		$_menus[$i]['url'] = "#";
		$i++;
		$_menus[$i]['level'] = 2;
		$_menus[$i]['label'] = "Elenco mail";
		$_menus[$i]['url'] = "index.php?option=com_cake&controller=Mails&action=index";
		$i++;
		$_menus[$i]['level'] = 2;
		$_menus[$i]['label'] = "Invia mail";
		$_menus[$i]['url'] = "index.php?option=com_cake&controller=Mails&action=send";
		$i++;
		$_menus[$i]['level'] = 1;
		$_menus[$i]['label'] = "Manuali";
		$_menus[$i]['url'] = "index.php?option=com_cake&controller=Manuals&action=index";	
			 
} // if(!empty($organization_id)) 
?>