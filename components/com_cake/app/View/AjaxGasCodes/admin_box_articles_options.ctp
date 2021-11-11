<p class="control-group">
	<label class="control-label">Opzioni articoli</label>
	<label class="radio-inline"><input type="radio" <?php if($articles_options=='options-articles-cart') echo 'checked=checked';?> name="articles-options" id="options-articles-cart" value="options-users-cart">Solo articoli acquistati</label>
	<label class="radio-inline"><input type="radio" <?php if($articles_options=='options-articles-all') echo 'checked=checked';?> name="articles-options" id="options-articles-all" value="options-users-all" <?php if($user_id=='ALL') echo 'disabled=disabled';?> >Tutti gli articoli</label>
</p>

<p class="control-group">
	<label class="control-label">Ordinamento</label>
		<label class="radio-inline">
			<input <?php if($articles_sort=='articles_users') echo 'checked=checked';?> type="radio" name="articles-sort" id="sort-articles-users" value="articles_users">Articoli e utenti
		</label>
		<label class="radio-inline">
			<input <?php if($user_id!='ALL') echo 'disabled=disabled';?> <?php if($articles_sort=='users_articles') echo 'checked=checked';?> type="radio" name="articles-sort" id="sort-users_articles" value="users_articles">Utenti e articoli
		</label>
		<label class="radio-inline">
			<input <?php if($user_id!='ALL') echo 'disabled=disabled';?> <?php if($articles_sort=='cart_date') echo 'checked=checked';?> type="radio" name="articles-sort" id="sort-cart_date" value="date">Acquistato il
		</label>
		<label class="radio-inline">
			<input <?php if($user_id!='ALL') echo 'disabled=disabled';?> <?php if($articles_sort=='article_cart_date') echo 'checked=checked';?> type="radio" name="articles-sort" id="sort-article_cart_date" value="article_cart_date">Articoli e data di acquisto
		</label>
</p>

<script type="text/javascript">
$(document).ready(function() {
	$("input[name='articles-options']").change(function() {
		choiceArticlesOptions();
	});

	$("input[name='articles-sort']").change(function() {
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