<?php// No direct access.defined('_JEXEC') or die;/* @var $menu JAdminCSSMenu */$shownew = (boolean)$params->get('shownew', 1);$menu->addChild(	new JMenuNode('Home', 'index.php?option=com_cake&controller=Pages&action=home'), true);$menu->getParent();			if(!empty($gasId)) {			$menu->addChild(
			new JMenuNode(JText::_('Utenti'), '#', ''), true
		);
		$menu->addChild(
			new JMenuNode(JText::_('Il mio profilo'), 'index.php?option=com_admin&task=profile.edit&id='.$user->get('id'), 'class:user')
		);		$menu->getParent();		
		$menu->addChild(
			new JMenuNode(JText::_('Produttore'), '#'), true
		);
		$menu->addChild(
			new JMenuNode(JText::_('Elenco G.A.S.'), 'index.php?option=com_cake&controller=ProdGasSuppliers&action=index', 'class:user')
		);	
		$menu->getParent();				$menu->addChild(			new JMenuNode(JText::_('Articoli'), '#', ''), true		);		$menu->addChild(			new JMenuNode(JText::_('Elenco articoli'), 'index.php?option=com_cake&controller=ProdGasArticles&action=index', 'class:weblinks')		);		$menu->getParent();				$menu->addChild(			new JMenuNode(JText::_('Promozioni'), '#', ''), true		);		$menu->addChild(			new JMenuNode(JText::_('Elenco promozioni'), 'index.php?option=com_cake&controller=ProdGasPromotions&action=index', 'class:weblinks')		);		$menu->getParent();	} // end if(!empty($gasId)) 