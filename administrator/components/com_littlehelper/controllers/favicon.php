<?php
/**
 * Create, copy, manage favicons and apple precomposed icons for your site.
 * 
 * @version SVN: $Id$
 * @package    LittleHelper
 * @author     Riccardo Zorn {@link http://www.fasterjoomla.com/littlehelper}
 * @author     Created on 22-Dec-2011
 * @license    GNU/GPL
 */

defined('_JEXEC') or die;

jimport('joomla.application.component.controllerform');


class LittleHelperControllerFavicon extends JControllerForm
{

	protected $default_view = 'favicon';
	
   function __construct() {
        parent::__construct();

    }
	
	/**
	 * This is the main view
	 * @see JController::display()
	 */
	public function display($cachable = false, $urlparams = false)
	{	
		parent::display();
	}
	
	/**
	 * create default folders (icons temporary folder...
	 * @param unknown_type $cachable
	 * @param unknown_type $urlparams
	 */
	public function createdefault($cachable = false, $urlparams = false) {
		if ($message = $this->getModel()->createDefault()) {
			$this->setRedirect(JRoute::_('index.php?option=com_littlehelper&view=favicon', false),
						$message);
		} else {
			$this->setRedirect(JRoute::_('index.php?option=com_littlehelper&view=favicon', false));
		}
		$this->redirect();		
	}
	
	/**
	 * Generate the favicons
	 * @param unknown_type $cachable
	 * @param unknown_type $urlparams
	 */
	public function generate($cachable = false, $urlparams = false) {
		if ($message = $this->getModel()->generate()) {
			$message .= $this->getModel()->saveConfiguration();
			$this->getModel()->setPluginState(true);
			
			$this->setRedirect(JRoute::_('index.php?option=com_littlehelper&view=favicon', false),
					$message);
		} else {
			$this->setRedirect(JRoute::_('index.php?option=com_littlehelper&view=favicon', false));
		}
		$this->redirect();
	}
	
	/**
	 * This is invoked after an upload. It defaults to showing the default favicon view.
	 * @param unknown_type $cachable
	 * @param unknown_type $urlparams
	 */
	public function clearResized($cachable = false, $urlparams = false) {
		if ($message = $this->getModel()->clearResized()) {
			$this->setRedirect(JRoute::_('index.php?option=com_littlehelper&view=favicon', false),
					$message);
		} else {
			$this->setRedirect(JRoute::_('index.php?option=com_littlehelper&view=favicon', false));
		}
		$this->redirect();
	}
	
	/**
	 * Disables the Little Helper plugin.
	 * @param unknown_type $cachable
	 * @param unknown_type $urlparams
	 */
	public function disablePlugin($cachable = false, $urlparams = false) {
		$this->getModel()->setPluginState(false);
		$this->setRedirect(JRoute::_('index.php?option=com_littlehelper&view=favicon', false));
	}
}
