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
?>
<!------------------------------------- THE CONTENT ------------------------------------------------->
<div id="lofass<?php echo $module->id; ?>" class="hidden-sm hidden-xs lof-ass<?php echo $params->get('moduleclass_sfx','');?> ">
<div class="lofass-container <?php echo $css3; ?> <?php echo $themeClass ;?> <?php echo $class;?>">
    
    <?php if( $params->get("preload",1) ): ?>
    <!-- div class="preload"><div></div></div -->
    <?php endif; ?>
    <?php if(  $params->get( 'enable_playstop' , 1) ): ?>
    <div class="lof-startstop"><div></div></div>
    <?php endif; ?>
     
     <!-- MAIN CONTENT --> 
      <?php /* <div class="lof-main-wapper" style="height:<?php echo (int)$params->get('main_height',300);?>px;width:100%;"> */ ?>
      <div class="col col-md-8">
	  
          <?php 
		  foreach( $list as $no => $row ): 
			  /*
			  echo "<pre>";
			  print_r($row);
			  echo "</pre>";
			  */
			  
			  if($no==0) {
		  ?>
            <div class="lof-main-item<?php echo(isset($customSliderClass[$no])? " ".$customSliderClass[$no]:"" );?>">
              <?php if( $isIntrotext ) : ?>
                  <div class="lof-inner">
                  <?php echo $row->introtext; ?>
                  </div>
                <?php else: ?>
                
                <?php if( !$enableImageLink ) : echo $row->mainImage; else :?>
                <a target="_<?php echo $openTarget ;?>" title="<?php echo $row->title;?>" href="<?php echo $row->link;?>">
               		<?php  echo $row->mainImage; ?>
                </a>
                <?php endif; ?>
				
                 
                 <?php if( $enableBlockdescription ):  ?>    
                 <div class="lof-description">
                    <h4><a target="_<?php echo $openTarget ;?>" title="<?php echo $row->title;?>" href="<?php echo $row->link;?>"><?php echo $row->title;?></a></h4>
                    <?php if( $row->description != '...') : ?>
                    <p><?php echo $row->description;?></p>
                    <?php endif; ?>
                 </div>
                 <?php endif; ?>
                 <?php endif; ?>
            </div> 
		  <?php 
		  } // end if($no==0)
		  
		  endforeach; 
		?>
        
      </div>
      <!-- END MAIN CONTENT --> 
        <!-- NAVIGATOR -->
      <?php if( $params->get('display_button',1) ) : ?>
                <!-- div class="lof-buttons-control">
                  <a href="" onclick="return false;" class="lof-previous"><?php echo JText::_('Previous');?></a>
                  <a href="" class="lof-next"  onclick="return false;"><?php echo JText::_('Next');?></a>
                </div -->
            <?php endif; ?>
        <?php if( $class ): ?>
              <div class="lof-navigator-outer-disabled col col-md-4">
                    <ul class="lof-navigator" style="background: none repeat scroll 0 0 #C6D5E0 !important;padding: 10px 25px 0 10px !important;">
                    <?php foreach( $list as $row ): ?>
                        <li style="border-bottom:1px solid #fff;">
							<?php if( $navEnableThumbnail ): ?>
							 <?php echo $row->thumbnail; ?> 
							 <?php endif; ?>
							 <?php if( $navEnableTitle ) : ?>
							<p style="color:#777;"><?php echo $row->subtitle;?></p>
							<?php endif; ?>
							<?php if( $navEnableDate ) : ?>
							<span><?php echo $row->date; ?></span>
							<?php endif; ?>
							<?php if( $navEnableCate ) :?>
							 <p><i><?php echo JText::_("Published in");?></i>
								 <a href="<?php echo JRoute::_(ContentHelperRoute::getCategoryRoute($row->catid));?>"><i><?php echo $row->category_title;?></i></a></p>
							<?php endif; ?>
                        </li>
                     <?php endforeach; ?>     
                    </ul>
              </div>
       <?php endif; ?>       
  </div>
 </div> 
 
 <style>
 .lof-ass .lof-main-wapper  {
	 overflow: visible !important; 
 }
 .lof-ass .lof-main-wapper .lof-main-item {
	 overflow: visible !important; 
 }
 </style>