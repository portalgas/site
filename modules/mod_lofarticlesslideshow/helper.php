<?php 
/**
 * $ModDesc
 * 
 * @version   $Id: $file.php $Revision
 * @package   modules
 * @subpackage  $Subpackage.
 * @copyright Copyright (C) November 2010 LandOfCoder.com <@emai:landofcoder@gmail.com>.All rights reserved.
 * @license   GNU General Public License version 2
 */
 
// no direct access
defined('_JEXEC') or die;
require_once JPATH_SITE.'/components/com_content/helpers/route.php';
JModel::addIncludePath(JPATH_SITE.'/components/com_content/models');
require_once JPATH_SITE.DS.'components'.DS.'com_content'.DS.'helpers'.DS.'route.php';
if( !defined('PhpThumbFactoryLoaded') ) {
	require_once dirname(__FILE__).DS.'libs'.DS.'phpthumb/ThumbLib.inc.php';
	define('PhpThumbFactoryLoaded',1);
}
/**
 * modLofArticlesSlideShowHelper Class 
 */
abstract class modLofArticlesSlideShowHelper {
	
  /**
   * get list of articles follow conditions user selected
   */ 
  public static function getList(&$params)
  {
    $formatter           = $params->get( 'style_displaying', 'title' );
    $titleMaxChars       = $params->get( 'title_max_chars', '100' );
    $descriptionMaxChars = $params->get( 'description_max_chars', 100 );
    $thumbWidth    = (int)$params->get( 'thumbnail_width', 60 );
    $thumbHeight   = (int)$params->get( 'thumbnail_height', 60 );
    $imageHeight   = (int)$params->get( 'main_height', 300 ) ;
    $imageWidth    = (int)$params->get( 'main_width', 650 ) ;
    $isThumb       = $params->get( 'auto_renderthumb',1);
    $ordering      = $params->get('ordering', 'created-asc');
    $replacer      = $params->get('replacer','...'); 
    $limitDescriptionBy = $params->get('limit_description_by','char');
	$isAutoStripsTag = $params->get('auto_strip_tags','1');
    // Get the dbo
    $db = JFactory::getDbo();
	
	if( !is_dir(JPATH_SITE.DS.'cache'.DS.'lofthumbs') ){
		JFolder::create( JPATH_SITE.DS.'cache'.DS.'lofthumbs' );
	}
	
	$overrideLinks = array();
	if( $tmp = $params->get('override_links', '' ) ){
			$tmp = is_array($tmp)?$tmp:array($tmp);
			foreach( $tmp as $titem ){
				$link  = explode("@", $titem );	
				if( count($link) > 1 ){
					$overrideLinks[(int)trim(strtolower($link[0]))]=$link[1];
				}
			}
		}
    // Get an instance of the generic articles model
    $model = JModel::getInstance('Articles', 'ContentModel', array('ignore_request' => true));

    // Set application parameters in model
    $app = JFactory::getApplication();
    $appParams = $app->getParams();
    $model->setState('params', $appParams);
	$model->setState('list.select', 'a.fulltext, a.id, a.title, a.alias, a.title_alias, a.introtext, a.state, a.catid, a.created, a.created_by, a.created_by_alias,' .
								' a.modified, a.modified_by,a.publish_up, a.publish_down, a.attribs, a.metadata, a.metakey, a.metadesc, a.access,a.images,' .
								' a.hits, a.featured,' .
								' LENGTH(a.fulltext) AS readmore');
    // Set the filters based on the module params
    $model->setState('list.start', 0);
    $model->setState('list.limit', (int) $params->get('limit_items', 5));
    $model->setState('filter.published', 1);

    // Access filter
    $access = !JComponentHelper::getParams('com_content')->get('show_noauth');
    $authorised = JAccess::getAuthorisedViewLevels(JFactory::getUser()->get('id'));
    $model->setState('filter.access', $access);
   
   
   $source = trim($params->get( 'source', 'category' ) );
    if( $source == 'category' ){
      // Category filter
      $model->setState('filter.category_id', $params->get('category', array()));
    }else{
      $ids = preg_split('/,/',$params->get( 'article_ids','')); 
      $tmp = array();
      foreach( $ids as $id ){
        $tmp[] = (int) trim($id);
      }
      $model->setState('filter.article_id', $tmp);  
    }

    // User filter
    $userId = JFactory::getUser()->get('id');
    switch ($params->get('user_id') ) {
      case 'by_me':
        $model->setState('filter.author_id', (int) $userId);
        break;
      case 'not_me':
        $model->setState('filter.author_id', $userId);
        $model->setState('filter.author_id.include', false);
        break;

      case 0:
        break;

      default:
        $model->setState('filter.author_id', (int) $params->get('user_id'));
        break;
    }

    // Filter by language
    $model->setState('filter.language',$app->getLanguageFilter());
    //  Featured switch
    switch ( $params->get('show_featured') )  {
      case 1:
        $model->setState('filter.featured', 'only');
        break;
      case 0:
        $model->setState('filter.featured', 'hide');
        break;
      default:
        break;
    }

    // Set ordering
    $ordering = explode( '-', $ordering );

    if( trim($ordering[0]) == 'rand' ){
        $model->setState('list.ordering', ' RAND() '); 
    }
    else {
      $model->setState('list.ordering', 'a.'.$ordering[0]);
      $model->setState('list.direction', $ordering[1]);
    } 

    $items = $model->getItems();  
    foreach ($items as &$item) {
      $item->slug = $item->id.':'.$item->alias;
      $item->catslug = $item->catid.':'.$item->category_alias;

      if ($access || in_array($item->access, $authorised))
      {
        // We know that user has the privilege to view the article
        $item->link = JRoute::_(ContentHelperRoute::getArticleRoute($item->slug, $item->catslug));
      }
      else {
        $item->link = JRoute::_('index.php?option=com_user&view=login');
      }

      $item->date = JHtml::_('date', $item->created, JText::_('DATE_FORMAT_LC2')); 
    	
    	if( array_key_exists($item->id,$overrideLinks) ){
				$item->link=$overrideLinks[$item->id];
		}
		
      $item = self::parseImages( $item );

      if( $item->mainImage &&  $image=self::renderThumb($item->mainImage, $imageWidth, $imageHeight, $item->title, $isThumb ) ){
        $item->mainImage = $image;
      }
      if( $item->thumbnail &&  $image = self::renderThumb($item->thumbnail, $thumbWidth, $thumbHeight, $item->title ,  $isThumb ) ){
        $item->thumbnail = $image;
      }
      $item->fulltext = $item->introtext;
      $item->introtext   = JHtml::_('content.prepare', $item->introtext);
      $item->subtitle    = self::substring( $item->title, $titleMaxChars, $replacer, $isAutoStripsTag  );
     
      if( $limitDescriptionBy=='word' ){
        $string        = preg_replace( "/\s+/", " ", strip_tags($item->introtext) );
        $tmp         = explode(" ", $string);
        $item->description = $descriptionMaxChars>count($tmp)?$string:implode(" ",array_slice($tmp, 0, $descriptionMaxChars)).$replacer;
      } else {
        $item->description = self::substring( $item->introtext, $descriptionMaxChars, $replacer , $isAutoStripsTag );
      }
      
        
    }
	
    return $items;
  }
		
	/**
	 * parser a custom tag in the content of article to get the image setting.
	 * 
	 * @param string $text
	 * @return array if maching.
	 */
	public static function parserCustomTag( $text ){ 
		if( preg_match("#{lofimg(.*)}#s", $text, $matches, PREG_OFFSET_CAPTURE) ){ 
			return $matches;
		}	
		return null;
	}
	
	/**
	 *  check the folder is existed, if not make a directory and set permission is 755
	 *
	 * @param array $path
	 * @access public,
	 * @return boolean.
	 */
	public static function renderThumb( $path, $width=100, $height=100, $title='', $isThumb=true ){
		
		if( $isThumb ){
			$path = str_replace( JURI::base(), '', $path );
			$imagSource = JPATH_SITE.DS. str_replace( '/', DS,  $path );
			if( file_exists($imagSource)  ) {
				$tmp = explode("/", $path);
				$imageName = $width."x".$height."-".$tmp[count($tmp)-1];
				$thumbPath = JPATH_SITE.DS.'cache'.DS.'lofthumbs'.DS.$imageName;
				if( !file_exists($thumbPath) ) {
					$thumb = PhpThumbFactory::create( $imagSource  );  	
					$thumb->adaptiveResize( $width, $height);
					 
					$thumb->save( $thumbPath  ); 
				}
				$path = JURI::base().'cache/lofthumbs/'.$imageName;
			} 
		}
		
		return '<img src="'.$path.'" title="'.$title.'" width="'.$width.'" alt="'.$title.'" >';;
	}
 
	
	/**
	 * get parameters from configuration string.
	 *
	 * @param string $string;
	 * @return array.
	 */
	public static function parseParams( $string ) {
		$string = html_entity_decode($string, ENT_QUOTES);
		$regex = "/\s*([^=\s]+)\s*=\s*('([^']*)'|\"([^\"]*)\"|([^\s]*))/";
		 $params = null;
		 if(preg_match_all($regex, $string, $matches) ){
				for ($i=0;$i<count($matches[1]);$i++){ 
				  $key 	 = $matches[1][$i];
				  $value = $matches[3][$i]?$matches[3][$i]:($matches[4][$i]?$matches[4][$i]:$matches[5][$i]);
				  $params[$key] = $value;
				}
		  }
		  return $params;
	}
	
	/**
	 * parser a image in the content of article.
	 *
	 * @param.
	 * @return
	 */
	public static function parseImages( $row ){
		
		$row->images = json_decode( $row->images );
		if( isset($row->images->image_fulltext) && isset($row->images->image_intro) ){
			$row->thumbnail = $row->images->image_intro;
			$row->mainImage = $row->images->image_fulltext;	
			if( empty($row->images->image_fulltext) ){
				$row->mainImage = $row->images->image_intro;
			} 
			if( empty($row->images->image_intro) ){
				$row->thumbnail = $row->images->image_fulltext;	
			}
		}
		if( empty($row->thumbnail) &&   empty($row->mainImage) ){
			$text =  $row->introtext;
			$regex = "/\<img.+src\s*=\s*\"([^\"]*)\"[^\>]*\>/";
			preg_match ($regex, $text, $matches); 
			$images = (count($matches)) ? $matches : array();
			if (count($images)){
				$row->mainImage = $images[1];
				$row->thumbnail = $images[1];
			} else {
				$row->thumbnail = '';
				$row->mainImage = '';	
			}
		}
		
		return $row;
	}
	
	/**
	 * load css - javascript file.
	 * 
	 * @param JParameter $params;
	 * @param JModule $module
	 * @return void.
	 */
	public static function loadMediaFiles( $params, $module ){
		$document = &JFactory::getDocument();
		$document->addScript( JURI::root(true). '/modules/'.$module->module.'/assets/jscript.js' );	
		$document->addStyleSheet( JURI::root(true). '/modules/'.$module->module.'/assets/jstyle.css' );	
	}
	
	/**
	 * get a subtring with the max length setting.
	 * 
	 * @param string $text;
	 * @param int $length limit characters showing;
	 * @param string $replacer;
	 * @return tring;
	 */
	public static function substring( $text, $length = 100, $replacer='...', $isAutoStripsTag = true ){
		$string =  $isAutoStripsTag?  strip_tags( $text ):$text;
		return JString::strlen( $string ) > $length ?  JHtml::_('string.truncate', $string, $length ): $string;
	}
}
?>