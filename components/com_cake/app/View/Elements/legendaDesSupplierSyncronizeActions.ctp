<div class="legenda">
	<table cellspacing="0" cellpadding="0" border="0">
		<tr>
			<td><h3><?php echo __('DesSyncronizeUpdate');?></h3></td>
			<td><h3><?php echo __('DesSyncronizeInsert');?></h3></td>
			<td><h3><?php echo __('DesSyncronizeFlagPresenteArticlesorders');?></h3></td>
		</tr>
		<tr>
			<td class="actionDesSyncronizen" style="background-color: rgb(255, 255, 255); border-radius: 0px;">
				<div style="padding-left:45px;width: 80%;" class="action actionCopy"><?php echo __('DesSyncronizeUpdate-descri');?></div>
			</td>
			<td class="actionDesSyncronizen" style="background-color: rgb(255, 255, 255); border-radius: 0px;">
				<div style="padding-left:45px;width: 80%;" class="action actionAdd"><?php echo __('DesSyncronizeInsert-descri');?></div>
			</td>
			<td class="actionDesSyncronizen" style="background-color: rgb(255, 255, 255); border-radius: 0px;">
				<div style="padding-left:45px;width: 80%;" class="action actionClose"><?php echo __('DesSyncronizeFlagPresenteArticlesorders-descri');?></div>
			</td>
		</tr>
	</table>
	
	<script type="text/javascript">
	jQuery( ".actionDesSyncronizen" ).mouseenter(function () {
		jQuery(this).css("background-color","yellow").css("border-radius","15px 15px 15px 15px");
	});
	jQuery( ".actionDesSyncronizen" ).mouseleave(function () {
		jQuery(this).css("background-color","#ffffff");
	});
	</script>	
</div>