<div class="legenda">

<p>organization_id <input class="from-control" type="text" value="<?php echo $max_id;?>" id="organizationId" /></p>

<pre class="shell no_prod" rel="sql per inserire nuovo produttore">
INSERT INTO "<?php echo Configure::read('DB.prefix');?>"organizations 
(id, name,type,img1,paramsConfig,paramsFields,paramsPay,stato,created,modified) VALUES 
(<?php echo $max_id;?>,%NOME-PRODUTTORE%,'PRODGAS','prodgas-<?php echo $max_id;?>.jpg',
 '{\"hasBookmarsArticles\":\"N\",\"hasArticlesOrder\":\"Y\",\"hasVisibility\":\"N\",\"hasUsersRegistrationFE\":\"N\"}','{\"hasFieldArticleCodice\":\"Y\",\"hasFieldArticleIngredienti\":\"Y\",\"hasFieldArticleCategoryId\":\"Y\"}','{}',
 'Y','".date("Y-m-d")." 00:00:00','".date("Y-m-d")." 00:00:00');
</pre>

<?php
echo '<pre class="shell no_prod" rel="script per inserire le categorie e permessi cartelle">';
echo '/var/portalgas/cron/config.conf settare la variabile '.$max_id.'<br />';
echo 'eseguire /var/portalgas/org_prodgas_new.sh '.$max_id.'<br />';
echo '<br />';
echo 'Directory articles, users e permessi<br />';
echo 'crea k_categories_articles.name = \'Generale\'<br />';
echo '</pre>';

echo '<pre class="shell no_prod" rel="img">';
echo '/var/www/portalgas/images/organizations/contents/prodgas-'.$max_id.'.jpg<br />';
echo '</pre>';

echo '</div>';