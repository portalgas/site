<?php
class MenuHelper extends AppHelper {
        
    var $helpers = array('Html');

    public function draw($links, $options = array()) {

		$html = "";
		
		if(empty($links))
			return $html;
		
		
		$html .= '<nav class="nav-min navbar navbar-fixed-left navbar-minimal animate" role="navigation">';
		$html .= '	<div class="navbar-toggler animate">';
		$html .= '		<span class="menu-icon"></span>';
		$html .= '	</div>';
		$html .= '	<ul class="navbar-menu animate">';
		
		foreach($links as $link) {
			$html .= '<li>';
			$html .= $link;
			$html .= '</li>';
			
		}
		
		$html .= '	</ul>';
		$html .= '</nav>'; 

		return $html;		
	}	  
}
?>