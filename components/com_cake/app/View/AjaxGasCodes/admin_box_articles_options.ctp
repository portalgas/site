<p>
	<div class="articles-options-label left label">Opzioni articoli</div>
	<div class="articles-options left radio">
		<input <?php if($articles_options=='options-articles-cart') echo 'checked=checked';?>  type="radio" name="articles-options" id="options-articles-cart" value="options-users-cart"><label for="options-articles-cart">Solo articoli acquistati</label>
		<input <?php if($articles_options=='options-articles-all') echo 'checked=checked';?>  <?php if($user_id=='ALL') echo 'disabled=disabled';?> type="radio" name="articles-options" id="options-articles-all" value="options-users-all"><label for="options-articles-all">Tutti gli articoli</label>
	</div>	
</p>
<div class="clearfix"></div>
<p>
	<div class="articles-sort-label left label">Ordinamento</div>
	<div class="articles-sort left radio">
		<input <?php if($articles_sort=='articles_users') echo 'checked=checked';?>  type="radio" name="articles-sort" id="sort-articles-users" value="articles_users"><label for="sort-articles-users">Articoli e utenti</label>
		<input <?php if($user_id!='ALL') echo 'disabled=disabled';?> <?php if($articles_sort=='users_articles') echo 'checked=checked';?>  type="radio" name="articles-sort" id="sort-users_articles" value="users_articles"><label for="sort-users-articles">Utenti e articoli</label>
		<input <?php if($user_id!='ALL') echo 'disabled=disabled';?> <?php if($articles_sort=='cart_date') echo 'checked=checked';?>  		  type="radio" name="articles-sort" id="sort-cart_date" value="date"><label for="sort-cart_date">Acquistato il</label>
	</div>	
</p>

<script type="text/javascript">
jQuery(document).ready(function() {
	jQuery("input[name='articles-options']").change(function() {
		choiceArticlesOptions();
	});

	jQuery("input[name='articles-sort']").change(function() {
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