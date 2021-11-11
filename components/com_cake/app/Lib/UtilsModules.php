<?php 
/* 
 * get content_id, id dell'articolo legato al produttore
 * $menuActive->route   friendly-url (/notizie/1-name)
 * $menuActive->link    index.php?option=com_content&view=article&id=1
 * */
function getJContentId() {	
	$content_id=0;
	$menuActive = JFactory::getApplication()->getMenu()->getActive();

	/*
	echo "<pre>";
	print_r($menuActive->route);
	echo "</pre>";
	echo "<pre>";
	print_r($menuActive->link);
	echo "</pre>";
	*/
	if($menuActive==null)
		return $content_id;
	
	if(!isset($menuActive->query['view']))
		$content_id = 0;
	else
	if( $menuActive->query['view']=='featured') {  // in primo piano
		$content_id=1;
	}
	else
	if($menuActive->query['view']=='article') {
		$content_id=$menuActive->query['id'];
	}
	else 
	if($menuActive->query['view']=='category' || $menuActive->query['view']=='categories') {
		
		/*
		 * case di content navigato dalla categoria view=category
		 * quindi l'id lo prendo dall'url
		 * /produttori/{ID-CATEGORY}-pesce/{ID-CONTENT}-fishbox
		 */
		if(isset($_SERVER['REQUEST_URI'])) {
			$url = explode('/', $_SERVER['REQUEST_URI']);
			
			/*
			 * ctrl di essere in un articolo di un produttore 
			 * 		/home-gas-cavagnetta/produttori/31-pesce/12-bio-e-mare
			 * non in una categoria
			 * 		/home-gas-cavagnetta/produttori/31-pesce
			 * => ctrl che il penultimo inizii con un number
			 */
			$urlPenultimo = $url[count($url)-2];
			$urlUltimo = $url[count($url)-1];
			
			//echo '<br />urlPenultimo '.$urlPenultimo;
			//echo '<br />urlUltimo '.$urlUltimo;
			$content_idPenultimo = "";
			$content_idUltimo = "";
			
			if(strpos($urlPenultimo,'-')!=false)
				list($content_idPenultimo, $seoPenultimo) = explode('-', $urlPenultimo);
				
			if(strpos($urlUltimo,'-')!=false)
				list($content_idUltimo, $seoUltimo) = explode('-', $urlUltimo);
				
			if(is_numeric($content_idPenultimo)) // sono nell'articolo del produttore (/home-gas-cavagnetta/produttori/31-pesce/12-bio-e-mare)
				$content_id = $content_idUltimo;
			else // sono in una categoria (/home-gas-cavagnetta/produttori/31-pesce)
				$content_id = 0;
			
			//echo '<br />content_id '.$content_id;
		}
	}

	return $content_id;
}
?>