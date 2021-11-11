<!DOCTYPE html PUBLIC "-//W3C//DTD HTML 4.01//EN">
<html>
    <head>
        <title><?php echo $title_for_layout; ?></title>
    
		<style style="text/css">
		body {
			background: none repeat scroll 0 0 #F6F6F8;
			font-family: Verdana;
			font-size: 12px;
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
		    font-size: 12px;
		    font-weight: normal;
		    text-align: left;
			padding: 3px;
		}
		td {
			font-size: 12px;
		}
		.tblBig {	
			background: none repeat scroll 0 0 #F6F6F8;
			text-align:center;
			width:100%;
			height:100%;
			margin:0;
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
		.msgFooter {	
			color="#ececec";
			text-align:center;
			font-size: 9px;
			margin: 5px 0 40px 0;
		}
		</style>
		
    </head>
<body bgcolor="#F6F6F8;">	
<table border="0" cellpadding="0" cellspacing="0" class="tblBig" bgcolor="#F6F6F8;" align="center">
	<tr>
		<td align="center" width="100%" height="100%" style="text-align:center;vertical-align:middle;">
	
		<table width="750px" border="0" cellpadding="0" cellspacing="0" align="center">
			<tr>
				<td>		
			
						<!-- H E A D E R -->
						<table width="100%" border="0" cellpadding="5" cellspacing="0">
							<tr valign="middle" class="bgContenuto" bgcolor="#FFFFFF;">
								<td align="right"><?php echo $header;?></td>
							</tr>
							<tr valign="middle" class="tblHeader"  bgcolor="#CCCCCC;">
				      			<td align="left" nowrap="nowrap"></td>
							</tr>
						</table>
						
						<!-- C O N T E N T -->
						<table border="0" cellpadding="1" cellspacing="5" class="bgContenuto"  bgcolor="#FFFFFF;">
							<tr valign="middle">
								<td height="20">&nbsp;</td>
							</tr>
							<tr valign="middle">
								<td align="left">
									<?php echo $body_header;?><br />
									<?php echo $content; ?>
								</td>
							</tr>
							<tr valign="middle">
								<td height="20">&nbsp;</td>
							</tr>
						</table>

						<!-- F O O T E R -->
						<table border="0" cellpadding="1" cellspacing="5" class="tblFooter" bgcolor="#F0F0F0;">
							<tr valign="middle">
								<td align="left">
									<?php echo $body_footer;?>
								</td>
							</tr>
						</table>

			</td>
		  </tr>
		  <tr>
			<td class="msgFooter">
				<?php echo Configure::read('SOC.name')."/".Configure::read('SOC.descrizione');?> 
			</td>
		   </tr>
		</table>


				</td>
			</tr>
		</table>


		</body>
		</html>    
    
    <body style="margin:0; padding:0;">
		<div>
			<div class="header">
				<br />
				
			</div>
			<div class="content" style="padding:20px;">
				
			</div>
			<div class="footer"></div>
		</div>
	</body>
</html>