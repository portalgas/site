<p class="control-group">
	<label class="control-label">Opzioni articoli</label>
	<label class="radio-inline"><input type="radio" <?php if($articles_options=='options-articles-cart') echo 'checked=checked';?> name="articles-options<?php echo $order_id;?>_<?php echo $user_id;?>" id="options-articles-cart" value="options-users-cart">Solo articoli acquistati</label>
	<label class="radio-inline"><input type="radio" <?php if($articles_options=='options-articles-all') echo 'checked=checked';?> <?php if($user_id=='ALL') echo 'disabled=disabled';?> name="articles-options<?php echo $order_id;?>_<?php echo $user_id;?>" id="options-articles-all" value="options-users-all" >Tutti gli articoli</label>
</p>

<script type="text/javascript">
$(document).ready(function() {
	$("input[name='articles-options<?php echo $order_id;?>_<?php echo $user_id;?>']").change(function() {

		var articlesOptions = $("input[name='articles-options<?php echo $order_id;?>_<?php echo $user_id;?>']:checked").val();
		choiceArticlesOptions(<?php echo $order_id;?>, <?php echo $user_id;?>, articlesOptions);
	});

	<?php 
	if(!empty($articles_options)) {
	?>
	var articlesOptions = $("input[name='articles-options<?php echo $order_id;?>_<?php echo $user_id;?>']:checked").val();
	choiceArticlesOptions(<?php echo $order_id;?>, <?php echo $user_id;?>, articlesOptions);
	<?php 
	}
	?>		
});
</script>