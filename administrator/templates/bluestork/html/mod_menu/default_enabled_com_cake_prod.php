<?php// No direct access.defined('_JEXEC') or die;/* @var $menu JAdminCSSMenu */$shownew = (boolean)$params->get('shownew', 1);$menu->addChild(	new JMenuNode('Home', 'index.php?option=com_cake&controller=Pages&action=home'), true);$menu->getParent();			if(!empty($gasId)) {			$menu->addChild(
			new JMenuNode(JText::_('Utenti'), '#', ''), true
		);
		$menu->addChild(
			new JMenuNode(JText::_('Il mio profilo'), 'index.php?option=com_admin&task=profile.edit&id='.$user->get('id'), 'class:user')
		);
		$menu->addChild(
			new JMenuNode(JText::_('Gestione completa'), 'index.php?option=com_users&view=users', 'class:user')
		);
		$createUser = $shownew && $user->authorise('core.create', 'com_users');
		if ($createUser)
		{
			$menu->addChild(
				new JMenuNode(JText::_('MOD_MENU_COM_USERS_ADD_USER'), 'index.php?option=com_users&task=user.add', 'class:newarticle')
			);
		}
		$menu->addChild(
			new JMenuNode(JText::_('Visualizzazione rapida'), 'index.php?option=com_cake&controller=Users&action=index', 'class:user')
		);
		$menu->addChild(
			new JMenuNode(JText::_('Importa utenti'), 'index.php?option=com_cake&controller=CsvImports&action=users', 'class:weblinks')
		);				$menu->addChild(
				new JMenuNode(JText::_('Ruoli'), 'index.php?option=com_cake&controller=UserGroupMaps&action=intro', 'class:user-note')
		);				
		$menu->getParent();		
		$menu->addChild(
			new JMenuNode(JText::_('Produttore'), '#'), true
		);
		$menu->addChild(
			new JMenuNode(JText::_('Gruppi di utenti'), 'index.php?option=com_cake&controller=ProdGroups&action=index', 'class:user')
		);
		$menu->addChild(
			new JMenuNode(JText::_('Consegne'), 'index.php?option=com_cake&controller=ProdDeliveries&action=index', 'class:content')
		);		if($hasFieldArticleCategoryId=='Y') {
			$menu->addChild(
				new JMenuNode(JText::_('Categorie articoli'), 'index.php?option=com_cake&controller=CategoriesArticles&action=index', 'class:category')
			);
		}		
		$menu->getParent();				$menu->addChild(
			new JMenuNode(JText::_('Articoli'), '#', ''), true
		);
		$menu->addChild(
			new JMenuNode(JText::_('Articoli'), 'index.php?option=com_cake&controller=Articles&action=context_articles_index', 'class:weblinks')
		);
		
		if($hasArticlesOrder=='Y' && $user->user['User']['hasArticlesOrder']=='Y') {
			$menu->addChild(
				new JMenuNode(JText::_('Modifica Rapida Articoli'), 'index.php?option=com_cake&controller=Articles&action=context_articles_index_quick', 'class:weblinks')
			);
		}
		$menu->addChild(
			new JMenuNode(JText::_('Stampa articoli'), 'index.php?option=com_cake&controller=Pages&action=export_docs_articles', 'class:contact')
		);
		$menu->addChild(
			new JMenuNode(JText::_('Modifica prezzi'), 'index.php?option=com_cake&controller=Articles&action=index_edit_prices_default', 'class:levels')
		);
		$menu->addChild(
			new JMenuNode(JText::_('Modifica prezzi in %'), 'index.php?option=com_cake&controller=Articles&action=index_edit_prices_percentuale', 'class:levels')
		);
		
		if($hasArticlesOrder=='Y' && $user->user['User']['hasArticlesOrder']=='Y') {
			$menu->addChild(
				new JMenuNode(JText::_('Modifica prezzo degli articolo associati agli ordini'), 'index.php?option=com_cake&controller=ArticlesOrders&action=order_choice', 'class:levels')
			);
		}
		$menu->addChild(
			new JMenuNode(JText::_('Importa articoli'), 'index.php?option=com_cake&controller=CsvImports&action=articles', 'class:weblinks')
		);
		$menu->getParent();	} // end if(!empty($gasId)) 