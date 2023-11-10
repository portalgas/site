<?php
// No direct access.
defined('_JEXEC') or die;

/* @var $menu JAdminCSSMenu */
$shownew = (boolean)$params->get('shownew', 1);


$i=-1;

if(!empty($organization_id)) {
	$i++;
	$_menus[$i]['level'] = 0;
	$_menus[$i]['label'] = "Produttore ".$organization_name;
	$_menus[$i]['url'] = "#";
	$i++;
	$_menus[$i]['level'] = 1;
	$_menus[$i]['label'] = "Elenco G.A.S.";
	$_menus[$i]['url'] = 'index.php?option=com_cake&controller=ProdGasSuppliers&action=index';
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
	$_menus[$i]['label'] = "Visualizzazione rapida";
	$_menus[$i]['url'] = "index.php?option=com_cake&controller=Users&action=index";	
	if($hasFieldArticleCategoryId=='Y') {
		$i++;
		$_menus[$i]['level'] = 1;
		$_menus[$i]['label'] = "Categorie articoli";
		$_menus[$i]['url'] = "index.php?option=com_cake&controller=CategoriesArticles&action=index";
	}

	/*
	 *  A R T I C L E S
	 */	
	$i++;
	$_menus[$i]['level'] = 0;
	$_menus[$i]['label'] = "Articoli";
	$_menus[$i]['url'] = "#";
	$i++;
	$_menus[$i]['level'] = 1;
	$_menus[$i]['label'] = "Gestione completa <label class='label label-success'>new</label>";
	$_menus[$i]['url'] = "index.php?option=com_cake&controller=Connects&action=index&c_to=admin/articles&a_to=index-quick";
	$_menus[$i]['target'] = "_blank";	
	/*
	$i++;
	$_menus[$i]['level'] = 1;
	$_menus[$i]['label'] = "Elenco articoli <label class='label label-warning'>old</label>";
	// $_menus[$i]['url'] = 'index.php?option=com_cake&controller=ProdGasArticles&action=index';
	$_menus[$i]['url'] = 'index.php?option=com_cake&controller=Articles&action=context_articles_index';
	*/
	if($hasArticlesOrder=='Y' && $user->user['User']['hasArticlesOrder']=='Y') {
		$i++;
		$_menus[$i]['level'] = 1;
		$_menus[$i]['label'] = "Modifica Rapida Articoli";
		$_menus[$i]['url'] = "index.php?option=com_cake&controller=Articles&action=context_articles_index_quick";
	}
	$i++;
	$_menus[$i]['level'] = 1;
	$_menus[$i]['label'] = "Stampa articoli";
	$_menus[$i]['url'] = "index.php?option=com_cake&controller=Pages&action=export_docs_articles";
	if($hasFieldArticleCategoryId=='Y') {
		$i++;
		$_menus[$i]['level'] = 1;
		$_menus[$i]['label'] = "Gestisci categorie";
		$_menus[$i]['url'] = "index.php?option=com_cake&controller=Articles&action=gest_categories";
	} 
	$i++;
	$_menus[$i]['level'] = 1;
	$_menus[$i]['label'] = "Modifica prezzi";
	$_menus[$i]['url'] = "index.php?option=com_cake&controller=Articles&action=index_edit_prices_default";
	$i++;
	$_menus[$i]['level'] = 1;
	$_menus[$i]['label'] = "Modifica prezzi in %";
	$_menus[$i]['url'] = "index.php?option=com_cake&controller=Articles&action=index_edit_prices_percentuale"; 
	$i++;
	$_menus[$i]['separator'] = true;
	/*
	$i++;
	$_menus[$i]['level'] = 1;
	$_menus[$i]['label'] = "Importa articoli";
	$_menus[$i]['url'] = "index.php?option=com_cake&controller=CsvImports&action=articles";	
	$i++;
	$_menus[$i]['level'] = 1;
	$_menus[$i]['label'] = "Esporta articoli per reimportarli";
	$_menus[$i]['url'] = "index.php?option=com_cake&controller=CsvImports&action=articles_form_export";	
	$i++;
	$_menus[$i]['level'] = 1;
	$_menus[$i]['label'] = "Importa articoli da esportazione precedente";
	$_menus[$i]['url'] = "index.php?option=com_cake&controller=CsvImports&action=articles_form_import";	
	*/
	$i++;
	$_menus[$i]['level'] = 1;
	$_menus[$i]['label'] = "Istruzioni per esporta ed importa <label class='label label-success'>new</label>";
	$_menus[$i]['url'] = "index.php?option=com_cake&controller=Connects&action=index&c_to=admin/helps&a_to=articles-export-import";
	$_menus[$i]['target'] = "_blank";	
	$i++;
	$_menus[$i]['level'] = 1;
	$_menus[$i]['label'] = "Esporta articoli in EXCEL <label class='label label-success'>new</label>";
	$_menus[$i]['url'] = "index.php?option=com_cake&controller=Connects&action=index&c_to=admin/articles&a_to=export";
	$_menus[$i]['target'] = "_blank";
	$i++;
	$_menus[$i]['level'] = 1;
	$_menus[$i]['label'] = "Importa articoli da EXCEL <label class='label label-warning'>beta</label>";
	$_menus[$i]['url'] = "index.php?option=com_cake&controller=Connects&action=index&c_to=admin/articles&a_to=import";
	$_menus[$i]['target'] = "_blank";

	/*
	 * promotions
	 */
	$i++;
	$_menus[$i]['level'] = 0;
	$_menus[$i]['label'] = "Promozioni";
	$_menus[$i]['url'] = "#";
	if($hasPromotionGas=='Y') {
		$i++;
		$_menus[$i]['level'] = 1;
		$_menus[$i]['label'] = "Elenco promozioni ai G.A.S.";
		$_menus[$i]['url'] = 'index.php?option=com_cake&controller=ProdGasPromotions&action=index_gas';
	}
	if($hasPromotionGasUsers=='Y') {
		$i++;
		$_menus[$i]['level'] = 1;
		$_menus[$i]['label'] = "Elenco promozioni ai singoli utenti";
		$_menus[$i]['url'] = 'index.php?option=com_cake&controller=ProdGasPromotions&action=index_gas_users';
	}
	if($hasPromotionGas=='N' && $hasPromotionGasUsers=='N') {
		$i++;
		$_menus[$i]['level'] = 1;
		$_menus[$i]['label'] = "Non abilitato";
		$_menus[$i]['url'] = '#';
	}	

	$i++;
	$_menus[$i]['level'] = 0;
	$_menus[$i]['label'] = "Utility";
	$_menus[$i]['url'] = "#";
/*
		$i++;
		$_menus[$i]['level'] = 1;
		$_menus[$i]['label'] = "Stampa documenti";
		$_menus[$i]['url'] = "index.php?option=com_cake&controller=Pages&action=export_docs_user_intro";
*/
		$i++;
		$_menus[$i]['level'] = 1;
		$_menus[$i]['label'] = "Mail";
		$_menus[$i]['url'] = "#";
		$i++;
		$_menus[$i]['level'] = 2;
		$_menus[$i]['label'] = "Elenco mail";
		$_menus[$i]['url'] = "index.php?option=com_cake&controller=Mails&action=prod_gas_supplier_index";
		$i++;
		$_menus[$i]['level'] = 2;
		$_menus[$i]['label'] = "Invia mail";
		$_menus[$i]['url'] = "index.php?option=com_cake&controller=Mails&action=prod_gas_supplier_send";
		$i++;
		$_menus[$i]['level'] = 1;
		$_menus[$i]['label'] = "Manuali";
		$_menus[$i]['url'] = "index.php?option=com_cake&controller=Manuals&action=index";	
			
} // end if(!empty($organization_id))
?> 