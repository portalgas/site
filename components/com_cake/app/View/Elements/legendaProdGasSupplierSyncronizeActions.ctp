<div class="legenda">
	<table cellspacing="0" cellpadding="0" border="0">
		<tr>
			<td><h3><?php echo __('ProdGasSyncronizeUpdate');?></h3></td>
			<td><h3><?php echo __('ProdGasSyncronizeInsert');?></h3></td>
			<td><h3><?php echo __('ProdGasSyncronizeFlagPresenteArticlesorders');?></h3></td>
			<td><h3><?php echo __('ProdGasSyncronizeDelete');?></h3></td>
		</tr>
		<tr>
			<td class="actionProdGasSyncronizen" style="background-color: rgb(255, 255, 255); border-radius: 0px;">
				<div style="padding-left:45px;width: 80%;" class="action actionCopy"><?php echo __('ProdGasSyncronizeUpdate-descri');?></div>
			</td>
			<td class="actionProdGasSyncronizen" style="background-color: rgb(255, 255, 255); border-radius: 0px;">
				<div style="padding-left:45px;width: 80%;" class="action actionAdd"><?php echo __('ProdGasSyncronizeInsert-descri');?></div>
			</td>
			<td class="actionProdGasSyncronizen" style="background-color: rgb(255, 255, 255); border-radius: 0px;">
				<div style="padding-left:45px;width: 80%;" class="action actionClose"><?php echo __('ProdGasSyncronizeFlagPresenteArticlesorders-descri');?></div>
			</td>
			<td class="actionProdGasSyncronizen" style="background-color: rgb(255, 255, 255); border-radius: 0px;">
				<div style="padding-left:45px;width: 80%;" class="action actionDelete"><?php echo __('ProdGasSyncronizeDelete-descri');?></div>
			</td>
		</tr>
	</table>
	
	<script type="text/javascript">
	jQuery( ".actionProdGasSyncronizen" ).mouseenter(function () {
		jQuery(this).css("background-color","yellow").css("border-radius","15px 15px 15px 15px");
	});
	jQuery( ".actionProdGasSyncronizen" ).mouseleave(function () {
		jQuery(this).css("background-color","#ffffff");
	});
	</script>	
</div>