<style type="text/css">
ul li {
	list-style: disc outside none;
}
.migration {
	font-size:14px;
}
</style>
<div class="migration form">
<?php 

if(isset($introTitle) && isset($introNote)) {
	echo '<h1>Organizzazione <i>'.$organization['Organization']['name'].'</i></h1>';
	echo $this->Form->create('Migration',array('id' => 'formGas', 'method'=>'post'));
	echo '<fieldset>';
	echo '<legend>'.__('Migrazione').': '.$introTitle.'</legend>';
	echo '<p>'.$introNote.'</p>';	
	echo '</fieldset>';
	echo $this->Form->end(__('inizia migrazione'));
}
?>
</div>
<div class="actions">
	<h3><?php echo __('Actions'); ?></h3>
		<ul>
			<li><a href="index.php?option=com_cake&amp;controller=DatabaseDate&amp;action=migration_eg3_index" class="action actionList">Home</a></li>
			<li><a href="index.php?option=com_cake&amp;controller=DatabaseDate&amp;action=migration_eg3_categories_articles" class="action actionConfig">Categorie degli articoli</a></li>
			<li><a href="index.php?option=com_cake&amp;controller=DatabaseDate&amp;action=migration_eg3_suppliers" class="action actionConfig">Produttori</a></li>
			<li><a href="index.php?option=com_cake&amp;controller=DatabaseDate&amp;action=migration_eg3_users" class="action actionRun">Utenti Eg3</a></li>
			<li><a href="index.php?option=com_cake&amp;controller=DatabaseDate&amp;action=migration_eg3_users_pwd" class="action actionRun">Utenti pwd Joomla</a></li>
			<li><a href="index.php?option=com_cake&amp;controller=DatabaseDate&amp;action=migration_eg3_suppliers_organizations" class="action actionRun">Produttori dell'organizzazione</a></li>
			<li><a href="index.php?option=com_cake&amp;controller=DatabaseDate&amp;action=migration_eg3_suppliers_organizations_referents" class="action actionRun">Referenti</a></li>
			<li><a href="index.php?option=com_cake&amp;controller=DatabaseDate&amp;action=migration_eg3_articles" class="action actionRun">Articoli</a></li>
			<li><a href="index.php?option=com_cake&amp;controller=DatabaseDate&amp;action=migration_eg3_drop_field" class="action actionLog">Drop tmp_migration_codice</a></li>
		</ul>
</div>
