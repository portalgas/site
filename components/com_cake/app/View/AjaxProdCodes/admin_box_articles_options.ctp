<div class="articles-options-label left label">Opzioni articoli</div>
<div class="articles-options left radio">
	<input <?php if($articles_options=='options-articles-cart') echo 'checked=checked';?>  type="radio" name="articles-options" id="options-articles-cart" value="options-users-cart"><label for="options-articles-cart">Solo articoli acquistati</label>
	<input <?php if($articles_options=='options-articles-all') echo 'checked=checked';?>  <?php if($user_id=='ALL') echo 'disabled=disabled';?> type="radio" name="articles-options" id="options-articles-all" value="options-users-all"><label for="options-articles-all">Tutti gli articoli</label>
</div>	

<script type="text/javascript">
jQuery(document).ready(function() {
	jQuery("input[name='articles-options']").change(function() {
		choiceArticlesOptions();
	});

	<?php 
	if(!empty($articles_options)) {
	?>
	choiceArticlesOptions();
	<?php 
	}
	?>		
});
</script>