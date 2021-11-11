<div class="legenda">
	<table cellspacing="0" cellpadding="0" border="0">
		<tr>
			<td><h3><?php echo __('ProdGasSyncronizeArticlesOrdersUpdate');?></h3></td>
			<td><h3><?php echo __('ProdGasSyncronizeArticlesOrdersInsert');?></h3></td>
			<td><h3><?php echo __('ProdGasSyncronizeArticlesOrdersDelete');?></h3></td>
		</tr>
		<tr>
			<td class="actionProdGasSyncronizen" style="background-color: rgb(255, 255, 255); border-radius: 0px;">
				<div style="padding-left:45px;width: 80%;" class="action actionSyncronize"><?php echo __('ProdGasSyncronizeArticlesOrdersUpdate-descri');?></div>
			</td>
			<td class="actionProdGasSyncronizen" style="background-color: rgb(255, 255, 255); border-radius: 0px;">
				<div style="padding-left:45px;width: 80%;" class="action actionAdd"><?php echo __('ProdGasSyncronizeArticlesOrdersInsert-descri');?></div>
			</td>
			<td class="actionProdGasSyncronizen" style="background-color: rgb(255, 255, 255); border-radius: 0px;">
				<div style="padding-left:45px;width: 80%;" class="action actionDelete"><?php echo __('ProdGasSyncronizeArticlesOrdersDelete-descri');?></div>
			</td>
		</tr>
	</table>
	
	<script type="text/javascript">
	$( ".actionProdGasSyncronizen" ).mouseenter(function () {
		$(this).css("background-color","yellow").css("border-radius","15px 15px 15px 15px");
	});
	$( ".actionProdGasSyncronizen" ).mouseleave(function () {
		$(this).css("background-color","#ffffff");
	});
	</script>	
</div>