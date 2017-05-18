<?php
/**
 * @version SVN: $Id$
 * @package    LittleHelper
 * @author     Riccardo Zorn {@link http://www.fasterjoomla.com/littlehelper}
 * @author     Created on 22-Dec-2011
 * @license    GNU/GPL
 */
/*
 * Icons from http://commons.wikimedia.org/wiki/Tango_icons
 * with some photoshopping
 */
defined('_JEXEC') or die;
$document = JFactory::getDocument();

$document->addScriptDeclaration('
		var currColor = 1;
		var backArray = new Array("white","red","blue","black","gray","transparent");
		var colorArray = new Array("black","white","white","white","white","black");
		function changeBackground() {
			if (++currColor>=colorArray.length) {currColor = 0}
			document.getElementById("chosenImages").style.backgroundColor = backArray[currColor];
			document.getElementById("chosenImages").style.color = colorArray[currColor];
		}
		');

?>
<div class='sa_favicon_main'>
<div id="step1">
	<div class="folder">
	 <?php 
	 if (!empty($this->params->favicons_sourcepath))
	 	$fiPath = $this->params->favicons_sourcepath;
	 else
	 	$fiPath = null;
	 if (empty($fiPath)) {
		echo "<span class='warn'>";
	 	echo JText::_("COM_LITTLEHELPER_FAVICONS_NO_SOURCE_PATH");
	 	echo "</span>";
	 	echo "<br><a href='index.php?option=com_littlehelper&task=favicon.createDefault' class='fancybutton foldercreate'>".JText::_("COM_LITTLEHELPER_FAVICONS_CREATE")."</a>";
	 } else {
			
			echo "<div class='chosenImages' ><div class='topList'>";
			
			echo "
				<div class='chktransp box'>
				<h3>".JText::_("COM_LITTLEHELPER_FAVICON_BUTTON_BOX_TITLE")."</h3> <br>";
			echo $this->getUploadForm($fiPath);
			
		if (!empty($this->images)) {
			
			echo "
				<a href='javascript:changeBackground();' class='fancybutton testtransp'>".JText::_("COM_LITTLEHELPER_FAVICON_BUTTON_TEST_TRANSPARENCY")."</a>
				<br>";

		echo '<a href="index.php?option=com_littlehelper&task=favicon.generate" class="fancybutton save">'. JText::_("COM_LITTLEHELPER_FAVICON_BUTTON_SAVE")."</a>";
		/**
		 * Here we propose the markup and we have one button to save it.
		 * The button enables the plugin if necessary, and saves its parameters.
		 * a second button takes to the plugin administration to disable it.
		 * The params are saved to the plugin as getting them from the component would be slower.
		 * 7
		 * The following buttons are now in the toolbar:
		 */
			// echo "<a href='index.php?option=com_littlehelper&task=favicon.enablePlugin' class='fancybutton enableplugin'>".JText::_("COM_LITTLEHELPER_FAVICONS_PLUGIN_ENABLE")."</a>";
			// echo "<a href='index.php?option=com_littlehelper&task=favicon.disablePlugin' class='fancybutton disableplugin'>".JText::_("COM_LITTLEHELPER_FAVICONS_PLUGIN_DISABLE")."</a>";
			// echo "<a href='index.php?option=com_plugins&view=plugins&filter_search=Little+Helper' class='fancybutton manageplugin'>".JText::_("COM_LITTLEHELPER_FAVICONS_PLUGIN_MANAGE")."</a>";
		
			echo "</div>";
			if (!empty($this->images)) {
					
				echo "<ul id='chosenImages'>";
				$basepath = ltrim(dirname(JUri::base(true)),"/") ;
				
				foreach ($this->images as $image) {	
					$resizedText = $image->resized?JText::_("COM_LITTLEHELPER_FAVICON_RESIZED"):"<i>".JText::_("COM_LITTLEHELPER_FAVICON_ORIGINAL")."</i>";	
					echo sprintf("
						<li>%s<br><img src='%s%s%s' class='thumb %s' /><br>
							<span class='size'>%sx%s</span><br>
							<span class='notes'>%s</span>
							</li>",$image->description,
								$basepath, $image->path , $image->name."?". rand(120120,990390),
								$image->size,
								$image->width, $image->height,
								$resizedText);
				}	
			
					
				echo "</ul>";
			}
			echo "</div>";
			
			
			echo "<span class='warn'>".JText::_("COM_LITTLEHELPER_FAVICON_QUALITY_NOTICE")."</span></div>";
		} else {
			echo "</div></div>";
		}
 	?>
 	</div>
 </div>
	<?php 
 } // here ends the if (empty($fiPath)) {...} else { 
 ?>
 
</div>
<form action="<?php echo JRoute::_('index.php?option=com_littlehelper'); ?>" method="post" name="adminForm" id="adminForm">
<input type="hidden" name="task" value="" />
</form>