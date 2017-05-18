<?php
/**
 * plugin_component.php,v 1.9 2010/01/02 12:34:11
 * @copyright (C) Reumer.net
 * @license http://www.gnu.org/copyleft/gpl.html GNU/GPL
 */

// no direct access
defined( '_JEXEC' ) or die( 'Restricted access' );

jimport( 'joomla.plugin.plugin' );
jimport( 'joomla.html.parameter' );

class plgSystemPlugin_component extends JPlugin
{
	var $config;
	var $subject;
	var $params;
	var $regex;
	var $document;
	var $doctype;
	var $ignore_scripts;
	var $ignore_styles;
	var $method;
	var $cbreplace;
	
	/**
	 * Constructor
	 *
	 * @access      protected
	 * @param       object  $subject The object to observe
	 * @param       array   $config  An array that holds the plugin configuration
	 * @since       1.0
	 */
	public function __construct( &$subject, $config )
//	public function plgSystemplugin_component( &$subject, $config )
	{
			parent::__construct( $subject, $config );

			// Do some extra initialisation in this constructor if required
			$this->subject = $subject;
			$this->config = $config;
			$plugin =& JPluginHelper::getPlugin('system', 'plugin_component');
			$this->params = new JParameter( $plugin->params );
			// define the regular expression for the bot
			$this->regex = "#(<p\b[^>]*>\s*)?{component\surl='(.*?)'}(\s*</p>)?#s";
			// Get document and doctype
			$this->document =& JFactory::getDocument();
			$this->doctype = $this->document->getType();
			// Get ignores for stylesheets and scripts
			$this->ignore_scripts = $this->params->get( 'ignore_script', '' );
			$this->ignore_scripts = preg_split("/[\n\r]+/", $this->ignore_scripts);
			$this->ignore_styles = $this->params->get( 'ignore_style', '' );
			$this->ignore_styles = preg_split("/[\n\r]+/", $this->ignore_styles);
			// Get method
			$this->method = $this->params->get( 'method', '' );
			$this->cbreplace = $this->params->get( 'cbreplace', '0' );
			// What is the url of website without / at the end
			$this->url = preg_replace('/\/$/', '', JURI::base());
			$this->base = JURI::base(true);
	}
	
	/**
	 * Do something onAfterInitialise 
	 */
	public function onAfterInitialise()
	{
	}
	
	/**
	 * onPrepareContent is rename in Joomla 1.6 to onContentPrepare
	 */
	public function onContentPrepare($context, &$article, &$params, $limitstart)
	{
		$app = JFactory::getApplication();
		if($app->isAdmin()) {
			return;
		}
		
		$text = &$article->text;
		$introtext = &$article->introtext;
		
		// check whether plugin has been unpublished
		if ( !$this->params->get( 'enabled', 1 ) ) {
			$text = preg_replace( $this->regex, '', $text );
			return true;
		}
	
		// perform the replacement	
		$this->_replace( $text );	
		$this->_replace( $introtext );	
	}
	
	/**
	 * onPrepareContent is for Joomla 1.5
	 */
	public function onPrepareContent(&$article)
	{
		$app = JFactory::getApplication();

		if($app->isAdmin()) {
			return;
		}
		
		$text = &$article->text;
		$introtext = &$article->introtext;
		
		// check whether plugin has been unpublished
		if ( !$this->params->get( 'enabled', 1 ) ) {
			$text = preg_replace( $this->regex, '', $text );
			return true;
		}
	
		// perform the replacement	
		$this->_replace( $text );	
		$this->_replace( $introtext );	
	}
	
	/**
	 * Do something onAfterRoute 
	 */
	public function onAfterRoute()
	{
	}
	
	/**
	 * Do something onAfterDispatch 
	 */
	public function onAfterDispatch()
	{
		$app = JFactory::getApplication();

		if($app->isAdmin()) {
			return;
		}
		
		// FEED
		if ($this->doctype=='feed'&&isset($this->document->items)) {
			foreach($this->document->items as $item) {
				$text = &$item->description;
				if ( !$this->params->get( 'enabled', 1 ) )
					$text = preg_replace( $this->regex, '', $text );
				else
					$this->_replace($text);
			}
			
			return true;
		}
		
		// In other components or leftovers
		if (isset($this->document->buffer)) {
			$text = $this->document->getBuffer("component");
			
			// check whether plugin has been unpublished
			if ( !$this->params->get( 'enabled', 1 ) )
				$text = preg_replace( $this->regex, '', $text );
			else
				$this->_replace($text);			
				
			$this->document->setBuffer($text, "component"); 
		}
	}
	
	/**
	 * Do something onAfterRender 
	 */
	public function onAfterRender()
	{
	}
	
	function _replace(&$text) {
		$matches = array();
		$cnt = preg_match_all($this->regex,$text,$matches,PREG_OFFSET_CAPTURE | PREG_PATTERN_ORDER);
		for($counter = 0; $counter < $cnt; $counter ++) {
			$content = $this->_process($matches[2][$counter][0]);
			$text = preg_replace($this->regex, $content, $text, 1);
		}
	}
	
	function _process( $url ) {
		// Clean url
		$reg[] = "/<span[^>]*?>/si";
		$repl[] = '';
		$reg[] = "/<\/span>/si";
		$repl[] = '';
		$url = preg_replace( $reg, $repl, $url );
	
		if (strpos($url, 'index.php')!== false) {
			$sef=false;
			$url = $url.((strpos($url, '?')===false)?'?':'&').'tmpl=component&print=1';
			// Add origin too to the component so it can redirect to the origin if something goes wrong
			$url .= '&origin='.base64_encode( JUri::getInstance()->toString() );
		} else {
			$sef=true;
			$url = $url.((substr($url, -1)!='/')?'/':'').'tmpl,component/print,1';
			// Add origin too to the component so it can redirect to the origin if something goes wrong		
			$url .= '/origin='.base64_encode( JUri::getInstance()->toString() );
		}
	
		$url = JUri::base().$url;
		// We need to replace the &amp; to & because the &amp; is not recognized
		$url = preg_replace('/&amp;/', '&', $url);
		
		$ok = false;
		$postcurl  = array();
		$post  = '';
		$cookie = '';
		$reg = '/^[a-f0-9]+$/si';
		//get all session parameters
		foreach ($_COOKIE as $key => $value) {
			if (preg_match($reg,$key)>0) {
				$cookie.="$key=$value; "; // separation in cookies is ; with space!
				$postcurl[$key]=$value;
				if ($sef)
					$post.=((strlen($post)>0)?'/':'')."$key,$value";
				else
					$post.=((strlen($post)>0)?'&':'')."$key=$value";
			}
		}
	
		// Close session so the other component can use it
		$session =& JFactory::getSession();
		$session->close();
		
		if (ini_get('allow_url_fopen')&&$this->method!='curl')
			if ($response = @file_get_contents($url.((strlen($post)>0)?$post:'')))
				$ok = true;
	
		if (!$ok) {
			if (function_exists('curl_init')) {
				$ch = curl_init( $url );
				// Set curl options, see: http://www.php.net/manual/en/function.curl-setopt.php
				curl_setopt ($ch, CURLOPT_RETURNTRANSFER, true); // to return the transfer as a string
				curl_setopt ($ch, CURLOPT_USERAGENT, 'spider'); // The contents of the "User-Agent: " header
				curl_setopt ($ch, CURLOPT_AUTOREFERER, true); // set referer on redirect
				
				// Send authentication
				$username = "";
				$password = "";
				// mod_php
		        if (isset($_SERVER['PHP_AUTH_USER'])) {
					$username = $_SERVER['PHP_AUTH_USER'];
					$password = $_SERVER['PHP_AUTH_PW'];
		        }
		       // most other servers
				elseif (isset($_SERVER['HTTP_AUTHENTICATION'])) {
					if (strpos(strtolower($_SERVER['HTTP_AUTHENTICATION']),'basic')===0) {
						list($username,$password) = explode(':',base64_decode(substr($_SERVER['HTTP_AUTHORIZATION'], 6)));
					}  
				}
				if ($username!=""&&$password!="") {
					curl_setopt($ch, CURLOPT_HTTPAUTH, CURLAUTH_ANY);
					curl_setopt ($ch, CURLOPT_USERPWD, $username.":".$password); // set referer on redirect
				}
				
				$timeout = 5; // set to zero for no timeout
				curl_setopt ($ch, CURLOPT_CONNECTTIMEOUT, $timeout);
				curl_setopt ($ch, CURLOPT_TIMEOUT, $timeout);
				curl_setopt ($ch, CURLOPT_MAXREDIRS, 10); // stop after 10 redirects
	
				if ( strlen($cookie)>0 ){
					curl_setopt($ch, CURLOPT_COOKIESESSION, false);  // False to keep all cookies of previous session
					curl_setopt($ch, CURLOPT_COOKIE, $cookie);
				}
				if ( strlen($post)>0 ){
					curl_setopt($ch, CURLOPT_POST, true); 
					curl_setopt($ch, CURLOPT_POSTFIELDS, $postcurl);
				}
				
				$response = curl_exec($ch);    
				if (curl_errno($ch)) {
					$response = "<!-- url not received: #".curl_errno($ch)." \"".curl_error($ch)."\" -->";
				} else {
					$ok = true;
				}
				curl_close($ch);
				
			} else 
				$response = "<!-- curl not available as PHP library -->";
		}
		
		// Start the session again?
	
		if ($ok) {
			// Clean the returned page of all html tags
	
			// get head and remove it
			// to do move meta tags, scripts and links to header?
			$reg = "/(<HEAD[^>]*>)(.*?)(<\/HEAD>)(.*)/si";
			$count = preg_match_all($reg,$response,$html);	
			if ($count>0) {
				$head=$html[2][0];
				$response= $html[4][0];
			} else {
				$head='';
			}
	
			if ($this->doctype!="feed"&&$this->doctype!="pdf") {
				// Find stylesheets and javascripts and add them to this document
				// javascript scripts
				$reg = '/<script.*src=[\'\"](.*?)[\'\"][^>]*[^<]*(<\/script>)?/i';

				$count = preg_match_all($reg,$head,$scripts);	
				if ($count>0)
					foreach ($scripts[1] as $script) {
						$this->_addScript($script);
					}
				
				// javascript embedded
				$reg = '/<script[^>]*>(.*?)<\/script>/si';
				$scripts= array();
				$count = preg_match_all($reg,$head,$scripts);	
				if ($count>0)
					foreach ($scripts[1] as $script) {
						if (trim($script)!='')
							$this->document->addScriptDeclaration($script);
					}
			}
	
			if ($this->doctype!="feed") {
				// stylesheet links
				$reg = '/<link.*href=[\'\"](.*?)[\'\"][^>]*[^<]*(<\/link>)?/i';
				$count = preg_match_all($reg,$head,$styles);	
				if ($count>0)
					for ($x=0;$x<$count;$x++) {
						if ((preg_match('/type=[\'"]text\/css[\'"]/i', $styles[0][$x])>0)||(preg_match('/rel=[\'"]stylesheet[\'"]/i', $styles[0][$x])>0))
							$this->_addStyleSheet($styles[1][$x]);
					}
				
				// Embedded styles
				$reg = '/<style[^>]*>(.*?)<\/style>/si';
				$styles = array();
				$count = preg_match_all($reg,$head,$styles);	
				if ($count>0)
					foreach ($styles[1] as $style) {
						if (trim($style)!='')
							$this->document->addStyleDeclaration($style);
					}
			}
			
			if ($this->doctype!="feed") {
				// Add meta tags and description to calling page
				$reg = '/<meta.*name=[\'\"](keywords|description)[\'\"].*content=[\'\"](.*?)[\'\"][^\/>]*/i';
				$count = preg_match_all($reg,$head,$meta);	
				if ($count>0)
					for ($x=0;$x<$count;$x++) {
						$old = $this->document->getMetaData ($meta[1][$x]);
						// Check if not already added?
						if ($meta[2][$x]!="")
							if (strpos($old, $meta[2][$x])===false) {
								if (strlen($old)>0&&strlen($meta[2][$x])>0)
									if ($meta[1][$x]=='keywords')
										$meta[2][$x] = ", ".$meta[2][$x];
									else
										$meta[2][$x] = " ".$meta[2][$x];
									
								$this->document->setMetaData ($meta[1][$x], $old.$meta[2][$x]);
							}
					}
			}
			
			// get body and remove it
			// to do attributes of body onload and other move to the real page?
			$reg = '/(<BODY[^>]*>)(.*)(<\/BODY>)/si';
			$count = preg_match_all($reg,$response,$html);	
			if ($count>0)
				$response=$html[2][0];
	
			$reg = array();
			$repl = array();
			
			// clean javascript for feeds and pdf
			if ($this->doctype=="feed"||$this->doctype=="pdf") {
				// Find stylesheets and javascripts and remove them
				// javascript scripts
				$reg[] = '/<script.*src=[\'\"](.*?)[\'\"][^>]*[^<]*(<\/script>)?/i';
				$repl[] = '';
				// javascript embedded
				$reg[] = '/<script[^>]*>(.*?)<\/script>/si';
				$repl[] = '';
			}
	
			if ($this->doctype=="feed") {
				// stylesheet links
				$reg[] = '/<link.*href=[\'\"](.*?)[\'\"][^>]*[^<]*(<\/link>)?/i';
				$repl[] = '';
				// Embedded styles
				$reg[] = '/<style[^>]*>(.*?)<\/style>/si';
				$repl[] = '';
			}
	
			// Replace links
			$reg[] = "/&amp;print=1/";
			$repl[] = '';
			$reg[] = "/&print=1/";
			$repl[] = '';
			$reg[] = "/&amp;tmpl=component/";
			$repl[] = '';
			$reg[] = "/&tmpl=component/";
			$repl[] = '';
			$reg[] = "/\?tmpl=component/";
			$repl[] = '';
			$reg[] = "/index2.php/";
			$repl[] = 'index.php';
			$response = preg_replace( $reg, $repl, $response );
			
			// Replace tokes
			$reg = '/<input type=["\']hidden["\'] name=["\'][a-f0-9]+["\'] value=["\']1["\'] \/>/si';
			$tokens = array();
			$count = preg_match_all($reg,$response,$tokens);
			if ($count>0)
				foreach ($tokens[0] as $token) {
					$response = str_replace ($token, JHTML::_( 'form.token' ), $response);
				}
	
			// Replace CB tokens
			$cbfile0 = JPATH_ADMINISTRATOR."/components/com_comprofiler/plugin.class.php";
			$cbfile1 = JPATH_ADMINISTRATOR.DS.'components'.DS.'com_comprofiler'.DS.'comprofiler.class.php';
			$cbfile2 = JPATH_ADMINISTRATOR.DS.'components'.DS.'com_comprofiler'.DS.'plugin.foundation.php';
			if ($this->cbreplace&&file_exists($cbfile0)&&file_exists($cbfile1)&&file_exists($cbfile2)) {
				require_once($cbfile0);
				require_once($cbfile1);
				require_once($cbfile2);
				$reg = '/<input type=["\']hidden["\'] name=["\']'.cbSpoofField().'["\'] value=["\'].*?["\'] \/>/si';
				$tokens = array();
				$count = preg_match_all($reg,$response,$tokens);
				if ($count>0)
					foreach ($tokens[0] as $token) {
						$response = str_replace ($token, cbGetSpoofInputTag('registerForm', null), $response);
					}
				$reg = '/<input type=["\']hidden["\'] name=["\']'.cbGetRegAntiSpamFieldName().'["\'] value=["\'].*?["\'] \/>/si';
				$tokens = array();
				$count = preg_match_all($reg,$response,$tokens);
				if ($count>0)
					foreach ($tokens[0] as $token) {
						$response = str_replace ($token, cbGetRegAntiSpamInputTag(), $response);
					}
			}
		}
		
		$content = "\n<!-- Plugin Include component version 1.9 by Mike Reumer";
		$content .= "\n     for: ".$url." -->";
		$content .= "\n".$response;
		$content .= "\n<!-- End Plugin Include component -->";
		
		return $content;	
	}

	function _addScript($script) {
		$found = false;

		foreach ($this->ignore_scripts as $url) {
			if ($url == $script||($this->url.$url==$script)||($this->url."/".$url==$script)||($this->base."/".$url==$script))
				$found = true;
		}
		if (!$found)
			$this->document->addScript($script);
	}
	
	function _addStyleSheet($style) {
		$found = false;
		
		foreach ($this->ignore_styles as $url) {
			if ($url == $style||($this->url.$url==$style)||($this->url."/".$url==$style)||($this->base."/".$url==$style))
				$found = true;
		}
		if (!$found)
			$this->document->addStyleSheet($style);
	}
}

?>