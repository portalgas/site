<?php
			new JMenuNode(JText::_('Utenti'), '#', ''), true
		);
		$menu->addChild(
			new JMenuNode(JText::_('Il mio profilo'), 'index.php?option=com_admin&task=profile.edit&id='.$user->get('id'), 'class:user')
		);
		$menu->addChild(
			new JMenuNode(JText::_('Produttore'), '#'), true
		);
		$menu->addChild(
			new JMenuNode(JText::_('Elenco G.A.S.'), 'index.php?option=com_cake&controller=ProdGasSuppliers&action=index', 'class:user')
		);	
		$menu->getParent();