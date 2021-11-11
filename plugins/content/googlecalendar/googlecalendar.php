<?php
/**
 *GoogleCalendar Plugin
 *
 * Author			:Jan Maat
 * Date				: Decemeber 2011
 * email			: jan.maat@hetnet.nl
 * copyright		: Jan Maat 2010
 * @license			: GNU/GPL
 * Description		: Displays the location of a house in an article on the position {ligging}
 */

// No direct access
defined( '_JEXEC' ) or die( 'Restricted access' );

// Import the JPlugin class
jimport('joomla.event.plugin');
jimport( 'joomla.application.menu' );

/**
 *  HasData event listener
 *
 */
class plgContentGoogleCalendar extends JPlugin
{
	/**
	 * The regular expression used to detect if Locatie has been embedded into the article
	 *
	 * @var		string Regular Expression
	 * @access	protected
	 * @since	0.1
	 *
	 */
	protected $_regex1	= '/{googlecalendar}/i';



	//Constructor
	function plgContentGoogleCalendar( &$subject, $params  )
	{
		parent::__construct( $subject, $params  );
		$this->loadLanguage();
	}

	/**
	 *  Handle onPrepareLocatie
	 *
	 */

	public function onContentPrepare($context, $article, $params, $page = 0 ) {

		// just startup
		$replacement = '';
		$app = JFactory::getApplication();

		// Only render in the HTML format
		$document	= JFactory::getDocument();
		$type		= $document->getType();
		$html		= ( $type == 'html' );



		// find all instances of plugin and put in $matches
		preg_match_all( $this->_regex1, $article->text, $declarations );

		// Return if there are no matches
		if ( ! count($declarations[0]) ) {
			return true;
		}

		JPlugin::loadLanguage('plg_content_googlecalendar', JPATH_ADMINISTRATOR);


		// make replacement

		$replacement = $this->params->get('code');
		if ((!$replacement))	{
			$article->text = preg_replace( $this->_regex1, '', $article->text );
			return true;
		}

		//Replace
		$article->text = preg_replace( $this->_regex1,$replacement, $article->text );

	}
}
?>
