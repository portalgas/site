<?php// No direct access.defined('_JEXEC') or die;/* @var $menu JAdminCSSMenu */$shownew = (boolean)$params->get('shownew', 1);$_menus = [];$i=-1;/* * G R O U P _ R O O T */if(in_array(group_id_root,$user->getAuthorisedGroups()) || in_array(group_id_root_supplier,$user->getAuthorisedGroups())) { 	$i++;	$_menus[$i]['level'] = 0;	$_menus[$i]['label'] = "G.A.S.";	$_menus[$i]['url'] = "#";	$i++;	$_menus[$i]['level'] = 1;	$_menus[$i]['label'] = "Produttori di PortAlGas";	$_menus[$i]['url'] = "index.php?option=com_cake&controller=Suppliers&action=index";	$i++;	$_menus[$i]['level'] = 1;	$_menus[$i]['label'] = "Categorie produttori";	$_menus[$i]['url'] = "index.php?option=com_cake&controller=CategoriesSuppliers&action=index";	if(in_array(group_id_root, $user->getAuthorisedGroups())) {		$i++;		$_menus[$i]['level'] = 1;		$_menus[$i]['label'] = "Ruoli";		$_menus[$i]['url'] = "index.php?option=com_cake&controller=UserGroupMaps&action=intro";			}	if ($user->authorise('core.manage', 'com_content')) { //if ($user->authorise('core.admin', 'com_content'))		$createContent =  $shownew && $user->authorise('core.create', 'com_content');		$i++;		$_menus[$i]['level'] = 1;		$_menus[$i]['label'] = JText::_('MOD_MENU_COM_CONTENT_ARTICLE_MANAGER'); // fractis new JMenuNode(JText::_('MOD_MENU_COM_CONTENT_ARTICLE_MANAGER'), 'index.php?option=com_content', 'class:article'), $createContent);		$_menus[$i]['url'] = "index.php?option=com_cake&controller=com_content";			if ($createContent)	{			$i++;			$_menus[$i]['level'] = 1;			$_menus[$i]['label'] = JText::_('MOD_MENU_COM_CONTENT_NEW_ARTICLE'); // // fractis new JMenuNode(JText::_('MOD_MENU_COM_CONTENT_NEW_ARTICLE'), 'index.php?option=com_content&task=article.add', 'class:newarticle')			$_menus[$i]['url'] = "index.php?option=com_cake&controller=com_content&task=article.add";		}		$i++;		$_menus[$i]['level'] = 1;		$_menus[$i]['label'] = JText::_('MOD_MENU_COM_CONTENT_CATEGORY_MANAGER'); // new JMenuNode(JText::_('MOD_MENU_COM_CONTENT_CATEGORY_MANAGER'), 'index.php?option=com_categories&extension=com_content', 'class:category'), $createContent		$_menus[$i]['url'] = "index.php?option=com_cake&controller=com_categories&extension=com_content";		$i++;		$_menus[$i]['level'] = 1;		$_menus[$i]['label'] = JText::_('MOD_MENU_COM_CONTENT_NEW_CATEGORY'); // new JMenuNode(JText::_('MOD_MENU_COM_CONTENT_NEW_CATEGORY'), 'index.php?option=com_categories&task=category.add&extension=com_content', 'class:newarticle');		$_menus[$i]['url'] = "index.php?option=com_cake&controller=com_categories&task=category.add&extension=com_content";	}} // end if(in_array(group_id_root,$user->getAuthorisedGroups()) || in_array(group_id_root_supplier,$user->getAuthorisedGroups())) if(!empty($gasId)) {	/*	 * M A N A G E R + S T A T I S T I C	 */		if(in_array(group_id_manager, $user->getAuthorisedGroups()) || 	   in_array(group_id_cassiere, $user->getAuthorisedGroups()) || in_array(group_id_referent_cassiere, $user->getAuthorisedGroups()) ||	   in_array(group_id_referent_tesoriere, $user->getAuthorisedGroups())) {			$i++;			$_menus[$i]['level'] = 0;			$_menus[$i]['label'] = $gasName;			$_menus[$i]['url'] = "#";					if(in_array(group_id_manager, $user->getAuthorisedGroups())) {				$i++;				$_menus[$i]['level'] = 1;				$_menus[$i]['label'] = "Il proprio G.A.S.";				$_menus[$i]['url'] = "index.php?option=com_cake&controller=OrganizationsPayments&action=edit";				$i++;				$_menus[$i]['level'] = 1;				$_menus[$i]['label'] = "Utenti";				$_menus[$i]['url'] = "#";				$i++;				$_menus[$i]['level'] = 2;				$_menus[$i]['label'] = "Il mio profilo";				$_menus[$i]['url'] = "index.php?option=com_admin&task=profile.edit&id=".$user->get('id');				$i++;				$_menus[$i]['level'] = 2;				$_menus[$i]['label'] = "Gestione completa";				$_menus[$i]['url'] = "index.php?option=com_users&view=users";				$createUser = $shownew && $user->authorise('core.create', 'com_users');								if ($createUser) {					$i++;					$_menus[$i]['level'] = 2;					$_menus[$i]['label'] = JText::_('MOD_MENU_COM_USERS_ADD_USER');					$_menus[$i]['url'] = "index.php?option=com_users&task=user.add";				}				$i++;				$_menus[$i]['level'] = 2;				$_menus[$i]['label'] = "Visualizzazione rapida";				$_menus[$i]['url'] = "index.php?option=com_cake&controller=Users&action=index";				$i++;				$_menus[$i]['level'] = 2;				$_menus[$i]['label'] = "Utenti disattivati";				$_menus[$i]['url'] = "index.php?option=com_cake&controller=Users&action=index_block";				$i++;				$_menus[$i]['level'] = 2;				$_menus[$i]['label'] = "Entrata/uscita utenti";				$_menus[$i]['url'] = "index.php?option=com_cake&controller=Users&action=index_date";				$i++;				$_menus[$i]['level'] = 2;				$_menus[$i]['label'] = "Importa utenti";				$_menus[$i]['url'] = "index.php?option=com_cake&controller=CsvImports&action=users";				$i++;				$_menus[$i]['level'] = 2;				$_menus[$i]['label'] = "Stampa utenti/referenti";				$_menus[$i]['url'] = "index.php?option=com_cake&controller=Pages&action=export_docs_users";				$i++;				$_menus[$i]['level'] = 1;				$_menus[$i]['label'] = "Ruoli";				$_menus[$i]['url'] = "index.php?option=com_cake&controller=UserGroupMaps&action=intro";									if($hasFieldArticleCategoryId=='Y') {					$i++;					$_menus[$i]['level'] = 1;					$_menus[$i]['label'] = "Categorie articoli";					$_menus[$i]['url'] = "index.php?option=com_cake&controller=CategoriesArticles&action=index";				}				$i++;				$_menus[$i]['level'] = 1;				$_menus[$i]['label'] = "Calendario attività";				$_menus[$i]['url'] = "#";				$i++;				$_menus[$i]['level'] = 2;				$_menus[$i]['label'] = "Calendario attività";				$_menus[$i]['url'] = "index.php?option=com_cake&controller=Events&action=index";				$i++;				$_menus[$i]['level'] = 2;				$_menus[$i]['label'] = "Calendario attività storiche";				$_menus[$i]['url'] = "index.php?option=com_cake&controller=Events&action=index_history";				$i++;				$_menus[$i]['level'] = 2;				$_menus[$i]['label'] = "Tipologie attività";				$_menus[$i]['url'] = "index.php?option=com_cake&controller=EventTypes&action=index";			} // end if(in_array(group_id_manager, $user->getAuthorisedGroups()))				$i++;			$_menus[$i]['level'] = 1;			$_menus[$i]['label'] = "Statistiche";			$_menus[$i]['url'] = "#";			$i++;			$_menus[$i]['level'] = 2;			$_menus[$i]['label'] = "Statistiche";			$_menus[$i]['url'] = "index.php?option=com_cake&controller=Statistics&action=index";			$i++;			$_menus[$i]['level'] = 2;			$_menus[$i]['label'] = "Esporta";			$_menus[$i]['url'] = "index.php?option=com_cake&controller=Statistics&action=export";		} // end group_id_manager  				/*		 * D. E. S.		*/		if($hasDes=='Y' && ( 		   in_array(group_id_manager_des, $user->getAuthorisedGroups()) || 		   in_array(group_id_referent_des, $user->getAuthorisedGroups()) || 		   in_array(group_id_super_referent_des, $user->getAuthorisedGroups()) ||		   in_array(group_id_titolare_des_supplier, $user->getAuthorisedGroups()) )) {		   				$i++;				$_menus[$i]['level'] = 0;				$_menus[$i]['label'] = "D.E.S.";				$_menus[$i]['url'] = "#";				$i++;				$_menus[$i]['level'] = 1;				$_menus[$i]['label'] = "I D.E.S. di cui faccio parte";				$_menus[$i]['url'] = "index.php?option=com_cake&controller=Des&action=index";		   				if(!empty($user->des_id)) {					if(in_array(group_id_manager_des, $user->getAuthorisedGroups())) {						$i++;						$_menus[$i]['level'] = 1;						$_menus[$i]['label'] = "Ruoli";						$_menus[$i]['url'] = "#";						$i++;						$_menus[$i]['level'] = 2;						$_menus[$i]['label'] = "Gestione dei Ruoli";						$_menus[$i]['url'] = "index.php?option=com_cake&controller=DesUserGroupMaps&action=intro";						$i++;						$_menus[$i]['level'] = 2;						$_menus[$i]['label'] = "Controllo ruoli";						$_menus[$i]['url'] = "index.php?option=com_cake&controller=DesUserGroupMaps&action=ctrl_roles_assigned";						$i++;						$_menus[$i]['level'] = 1;						$_menus[$i]['label'] = "Produttori";						$_menus[$i]['url'] = "index.php?option=com_cake&controller=DesSuppliers&action=index";						$i++;						$_menus[$i]['separator'] = true;						$i++;						$_menus[$i]['level'] = 1;						$_menus[$i]['label'] = "Ordini condivisi";						$_menus[$i]['url'] = "index.php?option=com_cake&controller=DesOrders&action=index";						$i++;						$_menus[$i]['level'] = 1;						$_menus[$i]['label'] = "Ordini condivisi storici";						$_menus[$i]['url'] = "index.php?option=com_cake&controller=DesOrders&action=index_history";						$i++;						$_menus[$i]['level'] = 1;						$_menus[$i]['label'] = "Listino articoli da sincronizzare tra G.A.S.";						$_menus[$i]['url'] = "index.php?option=com_cake&controller=DesArticlesSyncronizes&action=intro";						$i++;						$_menus[$i]['level'] = 1;						$_menus[$i]['label'] = "Invia mail";						$_menus[$i]['url'] = "index.php?option=com_cake&controller=Mails&action=des_send";					} // end if(in_array(group_id_manager_des, $user->getAuthorisedGroups())) 				} // end if(!empty($user->des_id))						} // end DES					/*		 * M A N A G E R 	/ 	M A N A G E R _ D E L I V E R Y		*/		if(in_array(group_id_manager_delivery, $user->getAuthorisedGroups()) || 		   in_array(group_id_referent, $user->getAuthorisedGroups()) || 		   in_array(group_id_super_referent, $user->getAuthorisedGroups()) || 		   in_array(group_id_referent_tesoriere, $user->getAuthorisedGroups()) || 		   in_array(group_id_events, $user->getAuthorisedGroups())) {				$i++;				$_menus[$i]['level'] = 0;				$_menus[$i]['label'] = "G.A.S.";				$_menus[$i]['url'] = "#";				if(in_array(group_id_manager_delivery, $user->getAuthorisedGroups())) {					$i++;					$_menus[$i]['level'] = 1;					$_menus[$i]['label'] = "Consegne";					$_menus[$i]['url'] = "index.php?option=com_cake&controller=Deliveries&action=index";					$i++;					$_menus[$i]['level'] = 1;					$_menus[$i]['label'] = "Consegne storiche";					$_menus[$i]['url'] = "index.php?option=com_cake&controller=Deliveries&action=index_history";					$i++;					$_menus[$i]['level'] = 1;					$_menus[$i]['label'] = "Ricorsione delle consegne";					$_menus[$i]['url'] = "index.php?option=com_cake&controller=LoopsDeliveries&action=index";				}				else {						$i++;					$_menus[$i]['level'] = 1;					$_menus[$i]['label'] = "Consegne";					$_menus[$i]['url'] = "index.php?option=com_cake&controller=Deliveries&action=view";				}				if(in_array(group_id_events, $user->getAuthorisedGroups())) {					$i++;					$_menus[$i]['level'] = 1;					$_menus[$i]['label'] = "Calendario attività";					$_menus[$i]['url'] = "#";					$i++;					$_menus[$i]['level'] = 1;					$_menus[$i]['label'] = "Calendario attività";					$_menus[$i]['url'] = "index.php?option=com_cake&controller=Events&action=index";					$i++;					$_menus[$i]['level'] = 1;					$_menus[$i]['label'] = "Calendario attività storiche";					$_menus[$i]['url'] = "index.php?option=com_cake&controller=Events&action=index_history";					$i++;					$_menus[$i]['level'] = 1;					$_menus[$i]['label'] = "Tipologie attività";					$_menus[$i]['url'] = "index.php?option=com_cake&controller=EventTypes&action=index";				}		} // end Deliveries		   		/*		 * R E F E R E N T I		 */		if(in_array(group_id_referent, $user->getAuthorisedGroups()) || 		   in_array(group_id_super_referent, $user->getAuthorisedGroups()) || 		   in_array(group_id_referent_tesoriere, $user->getAuthorisedGroups())) {			   			   				/*				 * R E F E R E N T I				 */			   				$i++;				$_menus[$i]['level'] = 0;				$_menus[$i]['label'] = "Referenti";				$_menus[$i]['url'] = "#";		   				$i++;				$_menus[$i]['level'] = 1;				$_menus[$i]['label'] = "Produttori";				$_menus[$i]['url'] = "#";		   				$i++;				$_menus[$i]['level'] = 2;				$_menus[$i]['label'] = "Produttori di PortAlGas";				$_menus[$i]['url'] = "index.php?option=com_cake&controller=Suppliers&action=index_relations";		   				$i++;				$_menus[$i]['level'] = 2;				$_menus[$i]['label'] = "Produttori del G.A.S.";				$_menus[$i]['url'] = "index.php?option=com_cake&controller=SuppliersOrganizations&action=index";				$i++;				$_menus[$i]['level'] = 2;				$_menus[$i]['label'] = "Voto ai produttori G.A.S.";				$_menus[$i]['url'] = "index.php?option=com_cake&controller=SuppliersVotes&action=index";				if(in_array(group_id_super_referent, $user->getAuthorisedGroups())) {						$i++;					$_menus[$i]['level'] = 2;					$_menus[$i]['label'] = "Monitoraggio ordini dei produttori";					$_menus[$i]['url'] = "index.php?option=com_cake&controller=MonitoringSuppliersOrganizations&action=home";				}				$i++;				$_menus[$i]['level'] = 1;				$_menus[$i]['label'] = "Referenti";				$_menus[$i]['url'] = "index.php?option=com_cake&controller=SuppliersOrganizationsReferents&action=index&group_id=".group_id_referent;									/*				 *  A R T I C L E S				 */					$i++;				$_menus[$i]['level'] = 1;				$_menus[$i]['label'] = "Articoli";				$_menus[$i]['url'] = "#";				$i++;				$_menus[$i]['level'] = 2;				$_menus[$i]['label'] = "Articoli";				$_menus[$i]['url'] = "index.php?option=com_cake&controller=Articles&action=context_articles_index";				if($hasArticlesOrder=='Y' && $user->user['User']['hasArticlesOrder']=='Y') {					$i++;					$_menus[$i]['level'] = 2;					$_menus[$i]['label'] = "Modifica Rapida Articoli";					$_menus[$i]['url'] = "index.php?option=com_cake&controller=Articles&action=context_articles_index_quick";				}				$i++;				$_menus[$i]['level'] = 2;				$_menus[$i]['label'] = "Stampa articoli";				$_menus[$i]['url'] = "index.php?option=com_cake&controller=Pages&action=export_docs_articles";				if($hasFieldArticleCategoryId=='Y') {					$i++;					$_menus[$i]['level'] = 2;					$_menus[$i]['label'] = "Gestisci categorie";					$_menus[$i]['url'] = "index.php?option=com_cake&controller=Articles&action=gest_categories";				} 				$i++;				$_menus[$i]['level'] = 2;				$_menus[$i]['label'] = "Modifica prezzi";				$_menus[$i]['url'] = "index.php?option=com_cake&controller=Articles&action=index_edit_prices_default";				$i++;				$_menus[$i]['level'] = 2;				$_menus[$i]['label'] = "Modifica prezzi in %";				$_menus[$i]['url'] = "index.php?option=com_cake&controller=Articles&action=index_edit_prices_percentuale"; 				if($hasArticlesOrder=='Y' && $user->user['User']['hasArticlesOrder']=='Y') {					$i++;					$_menus[$i]['level'] = 2;					$_menus[$i]['label'] = "Modifica prezzo degli articolo associati agli ordini";					$_menus[$i]['url'] = "index.php?option=com_cake&controller=ArticlesOrders&action=order_choice";				}				else {					$i++;					$_menus[$i]['level'] = 2;					$_menus[$i]['label'] = "Gestione articoli da associare ad un ordine";					$_menus[$i]['url'] = "index.php?option=com_cake&controller=Articles&action=index_flag_presente_articlesorders";								}				if($hasBookmarsArticles=='Y') {					$i++;					$_menus[$i]['level'] = 2;					$_menus[$i]['label'] = "Articoli preferiti";					$_menus[$i]['url'] = "index.php?option=com_cake&controller=BookmarksArticles&action=index";									}				$i++;				$_menus[$i]['separator'] = true;				$i++;				$_menus[$i]['level'] = 2;				$_menus[$i]['label'] = "Importa articoli";				$_menus[$i]['url'] = "index.php?option=com_cake&controller=CsvImports&action=articles";					$i++;				$_menus[$i]['level'] = 2;				$_menus[$i]['label'] = "Esporta articoli per reimportarli";				$_menus[$i]['url'] = "index.php?option=com_cake&controller=CsvImports&action=articles_form_export";					$i++;				$_menus[$i]['level'] = 2;				$_menus[$i]['label'] = "Importa articoli da esportazione precedente";				$_menus[$i]['url'] = "index.php?option=com_cake&controller=CsvImports&action=articles_form_import";																/*				 *  O R D E R S				 */									$i++;				$_menus[$i]['level'] = 1;				$_menus[$i]['label'] = "Ordini";				$_menus[$i]['url'] = "#";								$i++;				$_menus[$i]['level'] = 2;				$_menus[$i]['label'] = "Ordini";				$_menus[$i]['url'] = "index.php?option=com_cake&controller=Orders&action=index";								$i++;				$_menus[$i]['level'] = 2;				$_menus[$i]['label'] = "Aggiungi un nuovo ordine";				$_menus[$i]['url'] = "index.php?option=com_cake&controller=Orders&action=add";							$i++;				$_menus[$i]['level'] = 2;				$_menus[$i]['label'] = "Aggiungi un nuovo ordine (modalità semplificata)";				$_menus[$i]['url'] = "index.php?option=com_cake&controller=Orders&action=easy_add";							$i++;				$_menus[$i]['level'] = 2;				$_menus[$i]['label'] = "Ordini storici";				$_menus[$i]['url'] = "index.php?option=com_cake&controller=Orders&action=index_history";							$i++;				$_menus[$i]['level'] = 2;				$_menus[$i]['label'] = "Ricorsione";				$_menus[$i]['url'] = "index.php?option=com_cake&controller=LoopsOrders&action=index";							$i++;				$_menus[$i]['level'] = 2;				$_menus[$i]['label'] = "Monitoraggio Ordini";				$_menus[$i]['url'] = "index.php?option=com_cake&controller=MonitoringOrders&action=home";							$i++;				$_menus[$i]['level'] = 2;				$_menus[$i]['label'] = "Controlli dati aggregati sugli ordini";				$_menus[$i]['url'] = "index.php?option=com_cake&controller=SummaryOrders&action=orders_validate";							$i++;				$_menus[$i]['level'] = 1;				$_menus[$i]['label'] = "Invia mail";				$_menus[$i]['url'] = "index.php?option=com_cake&controller=Mails&action=send";			} // end referents												switch($payToDelivery) {				case "ON":					/*					 * C A S S I E R E					*/					if(in_array(group_id_cassiere, $user->getAuthorisedGroups()) || in_array(group_id_referent_cassiere, $user->getAuthorisedGroups())) { 						$i++;						$_menus[$i]['level'] = 0;						$_menus[$i]['label'] = "Cassiere";						$_menus[$i]['url'] = "#";						if(in_array(group_id_cassiere, $user->getAuthorisedGroups())) {							$i++;							$_menus[$i]['level'] = 1;							$_menus[$i]['label'] = "Gestione cassa";							$_menus[$i]['url'] = "index.php?option=com_cake&controller=Cashs&action=index";							$i++;							$_menus[$i]['level'] = 1;							$_menus[$i]['label'] = "Gestione cassa rapida";							$_menus[$i]['url'] = "index.php?option=com_cake&controller=Cashs&action=index_quick";						}						$i++;						$_menus[$i]['level'] = 1;						$_menus[$i]['label'] = "Stampa/Gestisci l'intera consegna";						$_menus[$i]['url'] = "index.php?option=com_cake&controller=Docs&action=cassiere_delivery_docs_export";						$i++;						$_menus[$i]['level'] = 1;						$_menus[$i]['label'] = "Stampa i singoli ordini";						$_menus[$i]['url'] = "index.php?option=com_cake&controller=Docs&action=cassiere_docs_export";						if(in_array(group_id_cassiere, $user->getAuthorisedGroups())) {							$i++;							$_menus[$i]['level'] = 1;							$_menus[$i]['label'] = "Gestisci le consegne";							$_menus[$i]['url'] = "index.php?option=com_cake&controller=Cassiere&action=home";						}						$i++;						$_menus[$i]['level'] = 1;						$_menus[$i]['label'] = "Stampe cassiere";						$_menus[$i]['url'] = "index.php?option=com_cake&controller=Pages&action=export_docs_cassiere";						$i++;						$_menus[$i]['level'] = 1;						$_menus[$i]['label'] = "Utility da scaricare";						$_menus[$i]['url'] = "index.php?option=com_cake&controller=Pages&action=utility_docs_cassiere";											} // end if(in_array(group_id_cassiere, $user->getAuthorisedGroups()) || in_array(group_id_referent_cassiere, $user->getAuthorisedGroups())) 			   			/*		   			 * T E S O R I E R E		   			*/				   			if(in_array(group_id_tesoriere, $user->getAuthorisedGroups())) {			   									$i++;						$_menus[$i]['level'] = 0;						$_menus[$i]['label'] = "Tesoriere";						$_menus[$i]['url'] = "#";						$i++;						$_menus[$i]['level'] = 1;						$_menus[$i]['label'] = "Home";						$_menus[$i]['url'] = "index.php?option=com_cake&controller=Tesoriere&action=home";						$i++;						$_menus[$i]['level'] = 1;						$_menus[$i]['label'] = "Pagamento Produttori";						$_menus[$i]['url'] = "#";						$i++;						$_menus[$i]['level'] = 2;						$_menus[$i]['label'] = "Pagamenti per consegne";						$_menus[$i]['url'] = "index.php?option=com_cake&controller=Tesoriere&action=pay_suppliers";						$i++;						$_menus[$i]['level'] = 2;						$_menus[$i]['label'] = "Pagamenti per produttore";						$_menus[$i]['url'] = "index.php?option=com_cake&controller=Tesoriere&action=pay_suppliers_by_supplier";						$i++;						$_menus[$i]['level'] = 2;						$_menus[$i]['label'] = "Pagamenti storici per consegne";						$_menus[$i]['url'] = "index.php?option=com_cake&controller=Tesoriere&action=pay_suppliers_history";					}				break; // $payToDelivery=='ON'				case 'POST':		   			/*		   			 * T E S O R I E R E		   			*/		   					if(in_array(group_id_tesoriere, $user->getAuthorisedGroups()) ||   			   			in_array(group_id_referent_tesoriere, $user->getAuthorisedGroups())) {	   			   									$i++;						$_menus[$i]['level'] = 0;						$_menus[$i]['label'] = "Tesoriere";						$_menus[$i]['url'] = "#";						$i++;						$_menus[$i]['level'] = 1;						$_menus[$i]['label'] = "Home";						$_menus[$i]['url'] = "index.php?option=com_cake&controller=Tesoriere&action=home";						$i++;						$_menus[$i]['level'] = 1;						$_menus[$i]['label'] = "Prendi in carico ordini";						$_menus[$i]['url'] = "index.php?option=com_cake&controller=Tesoriere&action=orders_get_WAIT_PROCESSED_TESORIERE";						$i++;						$_menus[$i]['level'] = 1;						$_menus[$i]['label'] = "Gestione ordini in elaborazione";						$_menus[$i]['url'] = "index.php?option=com_cake&controller=Tesoriere&action=orders_get_PROCESSED_TESORIERE";						$i++;						$_menus[$i]['level'] = 1;						$_menus[$i]['label'] = "Gestione del pagamento degli ordini";						$_menus[$i]['url'] = "index.php?option=com_cake&controller=RequestPayments&action=index";						$i++;						$_menus[$i]['level'] = 1;						$_menus[$i]['label'] = "Pagamento Produttori";						$_menus[$i]['url'] = "#";						$i++;						$_menus[$i]['level'] = 2;						$_menus[$i]['label'] = "Pagamenti per consegne";						$_menus[$i]['url'] = "index.php?option=com_cake&controller=Tesoriere&action=pay_suppliers";						$i++;						$_menus[$i]['level'] = 2;						$_menus[$i]['label'] = "Pagamenti per produttore";						$_menus[$i]['url'] = "index.php?option=com_cake&controller=Tesoriere&action=pay_suppliers_by_supplier";						$i++;						$_menus[$i]['level'] = 2;						$_menus[$i]['label'] = "Pagamenti storici per consegne";						$_menus[$i]['url'] = "index.php?option=com_cake&controller=Tesoriere&action=pay_suppliers_history";   					}				   			/*		   			 * C A S S I E R E		   			*/		   			if(in_array(group_id_cassiere, $user->getAuthorisedGroups())) {		   									$i++;						$_menus[$i]['level'] = 0;						$_menus[$i]['label'] = "Cassiere";						$_menus[$i]['url'] = "#";						$i++;						$_menus[$i]['level'] = 1;						$_menus[$i]['label'] = "Gestione cassa";						$_menus[$i]['url'] = "index.php?option=com_cake&controller=Cashs&action=index";						$i++;						$_menus[$i]['level'] = 1;						$_menus[$i]['label'] = "Gestione cassa rapida";						$_menus[$i]['url'] = "index.php?option=com_cake&controller=Cashs&action=index_quick";						$i++;						$_menus[$i]['level'] = 1;						$_menus[$i]['label'] = "Stampe cassiere";						$_menus[$i]['url'] = "index.php?option=com_cake&controller=Pages&action=export_docs_cassiere";						$i++;						$_menus[$i]['level'] = 1;						$_menus[$i]['label'] = "Utility da scaricare";						$_menus[$i]['url'] = "index.php?option=com_cake&controller=Pages&action=utility_docs_cassiere";			   						   			} 				break; // $payToDelivery=='POST'				case 'ON-POST': 			   		/*			   		 * C A S S I E R E			   		*/			   		if(in_array(group_id_cassiere, $user->getAuthorisedGroups()) || in_array(group_id_referent_cassiere, $user->getAuthorisedGroups())) {						$i++;						$_menus[$i]['level'] = 0;						$_menus[$i]['label'] = "Cassiere";						$_menus[$i]['url'] = "#";						if(in_array(group_id_cassiere, $user->getAuthorisedGroups())) {							$i++;							$_menus[$i]['level'] = 1;							$_menus[$i]['label'] = "Gestione cassa";							$_menus[$i]['url'] = "index.php?option=com_cake&controller=Cashs&action=index";							$i++;							$_menus[$i]['level'] = 1;							$_menus[$i]['label'] = "Gestione cassa rapida";							$_menus[$i]['url'] = "index.php?option=com_cake&controller=Cashs&action=index_quick";						}						$i++;						$_menus[$i]['level'] = 1;						$_menus[$i]['label'] = "Stampa/Gestisci l'intera consegna";						$_menus[$i]['url'] = "index.php?option=com_cake&controller=Docs&action=cassiere_delivery_docs_export";						$i++;						$_menus[$i]['level'] = 1;						$_menus[$i]['label'] = "Stampa i singoli ordini";						$_menus[$i]['url'] = "index.php?option=com_cake&controller=Docs&action=cassiere_docs_export";						if(in_array(group_id_cassiere, $user->getAuthorisedGroups())) {							$i++;							$_menus[$i]['level'] = 1;							$_menus[$i]['label'] = "Passa gli ordini al tesoriere";							$_menus[$i]['url'] = "index.php?option=com_cake&controller=Cassiere&action=orders_to_wait_processed_tesoriere";													$i++;							$_menus[$i]['level'] = 1;							$_menus[$i]['label'] = "Gestisci le consegne";							$_menus[$i]['url'] = "index.php?option=com_cake&controller=Cassiere&action=home";						}						$i++;						$_menus[$i]['level'] = 1;						$_menus[$i]['label'] = "Stampe cassiere";						$_menus[$i]['url'] = "index.php?option=com_cake&controller=Pages&action=export_docs_cassiere";						$i++;						$_menus[$i]['level'] = 1;						$_menus[$i]['label'] = "Utility da scaricare";						$_menus[$i]['url'] = "index.php?option=com_cake&controller=Pages&action=utility_docs_cassiere"; 			   		}						   		/*			   		 * T E S O R I E R E			   		*/			   		if(in_array(group_id_tesoriere, $user->getAuthorisedGroups()) ||	   					in_array(group_id_referent_tesoriere, $user->getAuthorisedGroups())) {	   									$i++;						$_menus[$i]['level'] = 0;						$_menus[$i]['label'] = "Tesoriere";						$_menus[$i]['url'] = "#";						$i++;						$_menus[$i]['level'] = 1;						$_menus[$i]['label'] = "Home";						$_menus[$i]['url'] = "index.php?option=com_cake&controller=Tesoriere&action=home";						$i++;						$_menus[$i]['level'] = 1;						$_menus[$i]['label'] = "Prendi in carico ordini";						$_menus[$i]['url'] = "index.php?option=com_cake&controller=Tesoriere&action=orders_get_WAIT_PROCESSED_TESORIERE";						$i++;						$_menus[$i]['level'] = 1;						$_menus[$i]['label'] = "Gestione ordini in elaborazione";						$_menus[$i]['url'] = "index.php?option=com_cake&controller=Tesoriere&action=orders_get_PROCESSED_TESORIERE";						$i++;						$_menus[$i]['level'] = 1;						$_menus[$i]['label'] = "Gestione del pagamento degli ordini";						$_menus[$i]['url'] = "index.php?option=com_cake&controller=RequestPayments&action=index";						$i++;						$_menus[$i]['level'] = 1;						$_menus[$i]['label'] = "Pagamento Produttori";						$_menus[$i]['url'] = "#";						$i++;						$_menus[$i]['level'] = 2;						$_menus[$i]['label'] = "Pagamenti per consegne";						$_menus[$i]['url'] = "index.php?option=com_cake&controller=Tesoriere&action=pay_suppliers";						$i++;						$_menus[$i]['level'] = 2;						$_menus[$i]['label'] = "Pagamenti per produttore";						$_menus[$i]['url'] = "index.php?option=com_cake&controller=Tesoriere&action=pay_suppliers_by_supplier";						$i++;						$_menus[$i]['level'] = 2;						$_menus[$i]['label'] = "Pagamenti storici per consegne";						$_menus[$i]['url'] = "index.php?option=com_cake&controller=Tesoriere&action=pay_suppliers_history";	   				} // end tesoriere			break; // $payToDelivery=='ON-POST'   		} // end switch   		   		 		if($hasStoreroom=='Y') { 				$i++;				$_menus[$i]['level'] = 0;				$_menus[$i]['label'] = "Dispensa";				$_menus[$i]['url'] = "#";				$i++;				$_menus[$i]['level'] = 1;				$_menus[$i]['label'] = "Cosa c'è in dispensa";				$_menus[$i]['url'] = "index.php?option=com_cake&controller=Storerooms&action=index";				$i++;				$_menus[$i]['level'] = 1;				$_menus[$i]['label'] = "Aggiungi articoli in dispensa";				$_menus[$i]['url'] = "index.php?option=com_cake&controller=Storerooms&action=add";				if($hasStoreroomFrontEnd=='Y') {					$i++;					$_menus[$i]['level'] = 1;					$_menus[$i]['label'] = "Cosa è stato acquistato";					$_menus[$i]['url'] = "index.php?option=com_cake&controller=Storerooms&action=index_to_users";				}				$i++;				$_menus[$i]['level'] = 1;				$_menus[$i]['label'] = "Stampa dispensa";				$_menus[$i]['url'] = "index.php?option=com_cake&controller=Pages&action=export_docs_storeroom";		}					/* 		 *  promozioni  M A N A G E R		 */		if(in_array(group_id_manager, $user->getAuthorisedGroups())) {						$i++;				$_menus[$i]['level'] = 0;				$_menus[$i]['label'] = "Promozioni";				$_menus[$i]['url'] = "#";				$i++;				$_menus[$i]['level'] = 1;				$_menus[$i]['label'] = "Elenco nuove promozioni";				$_menus[$i]['url'] = "index.php?option=com_cake&controller=ProdGasPromotionsOrganizationsManagers&action=index_new";				$i++;				$_menus[$i]['level'] = 1;				$_menus[$i]['label'] = "Elenco promozioni già associate ad un ordine";				$_menus[$i]['url'] = "index.php?option=com_cake&controller=ProdGasPromotionsOrganizationsManagers&action=index";		}				$i++;		$_menus[$i]['level'] = 0;		$_menus[$i]['label'] = "Utility";		$_menus[$i]['url'] = "#";		$i++;		$_menus[$i]['level'] = 1;		$_menus[$i]['label'] = "Generali";		$_menus[$i]['url'] = "index.php?option=com_cake&controller=Utilities&action=index";		$i++;		$_menus[$i]['level'] = 1;		$_menus[$i]['label'] = "Il mio profilo";		$_menus[$i]['url'] = "index.php?option=com_admin&task=profile.edit&id=".$user->get('id');		$i++;		$_menus[$i]['level'] = 1;		$_menus[$i]['label'] = "Stampa documenti";		$_menus[$i]['url'] = "index.php?option=com_cake&controller=Pages&action=export_docs_user_intro";		$i++;		$_menus[$i]['level'] = 1;		$_menus[$i]['label'] = "Mail";		$_menus[$i]['url'] = "#";		$i++;		$_menus[$i]['level'] = 2;		$_menus[$i]['label'] = "Elenco mail";		$_menus[$i]['url'] = "index.php?option=com_cake&controller=Mails&action=index";		$i++;		$_menus[$i]['level'] = 2;		$_menus[$i]['label'] = "Invia mail";		$_menus[$i]['url'] = "index.php?option=com_cake&controller=Mails&action=send";		$i++;		$_menus[$i]['level'] = 2;		$_menus[$i]['label'] = "Manuali";		$_menus[$i]['url'] = "index.php?option=com_cake&controller=Manuals&action=index";				 } // if(!empty($gasId)) /* * H T M L */echo '<div class="container-disabled">';echo '<nav class="navbar navbar-default">';echo '<div class="container-fluid">';echo '<div class="navbar-header">';echo '<button type="button" class="navbar-toggle collapsed" data-toggle="collapse" data-target="#navbar" aria-controls="navbar" aria-expanded="false">';echo '<span class="sr-only">Toggle navigation</span>';echo '<span class="icon-bar"></span>';echo '<span class="icon-bar"></span>';echo '<span class="icon-bar"></span>';echo '</button>';echo '<a class="navbar-brand" href="index.php?option=com_cake&amp;controller=Pages&amp;action=home">PortAlGas</a>';echo '</div>';echo '<div class="collapse navbar-collapse" id="navbar">';echo '<ul class="nav navbar-nav">'; foreach($_menus as $numResults => $_menu) {	if(isset($_menu['separator'])) {		echo '<li role="separator" class="divider"></li>';	}	else {					if($_menu['level']==0) {			echo '<li class="dropdown">';			echo '	<a href="'.$_menu['url'].'" class="dropdown-toggle" data-toggle="dropdown" role="button" aria-haspopup="true" aria-expanded="false">'.$_menu['label'].' <span class="caret"></span></a>';			echo '	<ul class="dropdown-menu">';		}				if($_menu['level']==1) {						// $tmp .= 'Current level '.$_menu['level'].' '.$_menu['label'].' - successivo '.$_menus[$numResults-1]['level'].' '.$_menus[$numResults+1]['label'].'<br />';						if(isset($_menus[$numResults+1]['level']) && $_menus[$numResults+1]['level']==2) {				echo '<li class="dropdown-submenu">';				echo '	<a href="'.$_menu['url'].'" class="dropdown-toggle" data-toggle="dropdown" role="button" aria-haspopup="true" aria-expanded="false">'.$_menu['label'].'</a>';				echo '		<ul class="dropdown-menu">';			}				else				echo '<li><a href="'.$_menu['url'].'">'.$_menu['label'].'</a></li>';						if(isset($_menus[$numResults+1]['level']) && $_menus[$numResults+1]['level']==0) {				echo '	</ul>';				echo '</li>';			}					}				if($_menu['level']==2) {			echo '<li><a href="'.$_menu['url'].'">'.$_menu['label'].'</a></li>';						if(isset($_menus[$numResults+1]['level']) && $_menus[$numResults+1]['level']==1) {				echo '	</ul>';				echo '</li>';			}				else			if(isset($_menus[$numResults+1]['level']) && $_menus[$numResults+1]['level']==0) {				echo '	</ul>';				echo '</li>';				echo '	</ul>';				echo '</li>';			}					}	}}if(!empty($_menus)) {	echo '	</ul>';	echo '</li>';	echo '	</ul>';	echo '</li>';}	echo '</ul>';echo '<ul class="nav navbar-nav navbar-right">';$task = JRequest::getCmd('task');if ($task == 'edit' || $task == 'editA' || JRequest::getInt('hidemainmenu')) {	$logoutLink = '';} else {	$logoutLink = JRoute::_('index.php?option=com_login&task=logout&'. JSession::getFormToken() .'=1');}$hideLinks	= JRequest::getBool('hidemainmenu');echo '<li>' .($hideLinks ? '' : '<a href="'.$logoutLink.'"><i class="fa fa-power-off"></i> ').JText::_('JLOGOUT').($hideLinks ? '' : '</a>').'</li>';echo ' </ul>';echo '</div>';echo '</div>';echo '</nav>';echo '</div>';?>