<?php
/**
 *
 * PHP 5
 *
 * CakePHP(tm) : Rapid Development Framework (http://cakephp.org)
 * Copyright 2005-2012, Cake Software Foundation, Inc. (http://cakefoundation.org)
 *
 * Licensed under The MIT License
 * Redistributions of files must retain the above copyright notice.
 *
 * @copyright     Copyright 2005-2012, Cake Software Foundation, Inc. (http://cakefoundation.org)
 * @link          http://cakephp.org CakePHP(tm) Project
 * @package       Cake.View.Emails.html
 * @since         CakePHP(tm) v 0.10.0.1076
 * @license       MIT License (http://www.opensource.org/licenses/mit-license.php)
 */
/*
  $content = explode("\n", $content);
  foreach ($content as $line):
	 echo '<p> ' . $line . "</p>\n";
  endforeach;
  
  
  utilizzo questo template
*/
?>
<!DOCTYPE html PUBLIC "-//W3C//DTD HTML 4.01//EN">
<html>
    <head>
        <title><?php echo $title_for_layout; ?></title>
    
		<style style="text/css">
		body {
			background: none repeat scroll 0 0 #F6F6F8;
			font-family: Verdana;
			font-size: 14px;
			margin:0;
		}
		a {
			color:#000000;
		}	
		.bgContenuto {
			width:100%;
			background-color: #FFFFFF;
		}
		th {
			background-color: #FFFFFF;
		    color: #000000;
		    font-size: 14px;
		    font-weight: normal;
		    text-align: left;
			padding: 3px;
		}
		td {
			font-size: 14px;
		}
		.bgContenuto td {
			font-size: 16px;
		}		
		.tblHeader {
			width:100%;
			height:30px;
			background-color: #CCCCCC;
			border-bottom: 1px solid #999999;
		}
		.tblFooter {
			width:100%;
			background-color: #F0F0F0;
			border-top: 1px solid #999999;
		}
		.tblFooter ul.social > li {
			list-style: outside none none;
			margin: 0 0 10px;
		}                
		.mobileHide { display: inline; } 
		 
		 /* Smartphone Portrait and Landscape */ 
		 @media only screen 
			and (min-device-width : 320px) 
			and (max-device-width : 480px){ 
			 .mobileHide { display: none;}
		 }		
		</style>
		
    </head>

<body>	  
<center>
   
	<table border="0" cellpadding="0" cellspacing="0" align="center" height="100%" width="100%" style="margin: 0;padding: 0;background-color:#F6F6F8;height: 100% !important;max-width: 1240px !important;">
    	<tbody>
			<tr>
		    	<td>
					
						<!-- H E A D E R -->
						<table width="100%" border="0" cellpadding="5" cellspacing="0">
							<tr valign="middle" class="bgContenuto" bgcolor="#FFFFFF">
								<td align="right"><?php echo $header;?></td>
							</tr>
							<tr valign="middle" class="tblHeader" bgcolor="#CCCCCC">
				      			<td height="35" align="left" nowrap="nowrap" bgcolor="#CCCCCC">
				      				<?php echo $body_header;?>
				      			</td>
							</tr>
						</table>
						
						<!-- C O N T E N T -->
						<table width="100%" border="0" cellpadding="1" cellspacing="5" class="bgContenuto"  bgcolor="#FFFFFF">
							<tr valign="middle">
								<td height="20">&nbsp;</td>
							</tr>
							<tr valign="middle">
								<td align="left">
									<?php echo $content; ?>
								</td>
							</tr>
							<tr valign="middle">
								<td height="20">
									<?php 
									if(isset($content_info) && !empty($content_info)) {
										echo '<div style="border-radius:5px;border:2px dotted rgb(204, 204, 204); padding: 10px 10px 10px 50px; margin: 0px; background: rgb(255, 255, 255) url(https://www.portalgas.it/images/cake/actions/32x32/info.png) no-repeat scroll 5px center;">'.$content_info.'</div>';										
									}
									else
										echo '&nbsp;';
									?>
								</td>
							</tr>
						</table>

						<!-- F O O T E R -->
						<table width="100%" border="0" cellpadding="1" cellspacing="5" class="tblFooter" bgcolor="#F0F0F0">
							<tr valign="middle">
								<td align="left" width="59%">
									<?php echo $body_footer;?>
								</td>
								<td align="left" width="20%">
										<!-- span>
											<a target="_blank" href="https://itunes.apple.com/us/app/portalgas/id1133263691">
												<img border="0" title="vai allo store di Itunes" src="https://www.portalgas.it/images/appstore.png"></a>		
										</span>								
										<span>
											<a href="https://play.google.com/store/apps/details?id=com.ionicframework.portalgas" target="_blank">
												<img border="0" src="https://www.portalgas.it/images/googleplay.png" title="vai allo store di Google"></a>
										</span -->	
								</td>								
								<td align="left" width="20%">
									<ul class="social">
											<li style="list-style: outside none none; margin-bottom:5px">
													<a href="https://facebook.com/portalgas.it" target="_blank"><img border="0" title="PortAlGas su facebook" alt="PortAlGas su facebook" src="https://www.portalgas.it/images/cake/ico-social-fb.png"> Facebook</a>
											</li>
											<li style="list-style: outside none none; margin-bottom:5px">
													<a href="http://manuali.portalgas.it" target="_blank"><img border="0" title="I manuali di PortAlGas" alt="I manuali di PortAlGas" src="https://www.portalgas.it/images/cake/ico-manual.png"> Manuali</a>
											</li>
											<li style="list-style: outside none none; margin-bottom:5px">
													<a href="https://www.youtube.com/channel/UCo1XZkyDWhTW5Aaoo672HBA" target="_blank"><img border="0" title="PortAlGas su YouTube" alt="PortAlGas su YouTube" src="https://www.portalgas.it/images/cake/ico-social-youtube.png"> YouTube</a>
											</li>
											<!-- li style="list-style: outside none none; margin-bottom:5px">
													<a href="https://www.portalgas.it/mobile" target="_blank"><img border="0" title="PortAlGas per tablet e mobile" alt="PortAlGas per tablet e mobile" src="https://www.portalgas.it/images/cake/ico-mobile.png"> Mobile</a>
											</li -->
									</ul>
								</td>

								
							</tr>
							<tr valign="middle">
								<td colspan="3" style="background-color:#F0F0F0;text-align:center;font-size: 12px;margin: 5px 0 40px 0;">
									<br /><?php echo Configure::read('SOC.name')."/".Configure::read('SOC.descrizione');?> 
								</td>
							</tr>
						</table>


				</td>
			</tr>
			
			<!-- IMG sfondo -->
			<tr class="mobileHide">
			  <td style="background:url(https://www.portalgas.it/images/mails/<?php echo date('N');?>.jpg) no-repeat scroll center center #000; height: 250px;">
			  </td>
			</tr>
			
		</tbody>	
	</table>
</center>

</body>
</html>