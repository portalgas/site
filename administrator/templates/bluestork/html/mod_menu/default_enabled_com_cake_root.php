<?php
		new JMenuNode(JText::_('Organizzazioni'), '#', ''), true
	);	
		new JMenuNode(JText::_('Help'), 'index.php?option=com_cake&controller=Helps&action=index', 'class:help')
	);
			new JMenuNode(JText::_('Il mio profilo'), 'index.php?option=com_admin&task=profile.edit&id='.$user->get('id'), 'class:user')
	);
	$menu->getParent();