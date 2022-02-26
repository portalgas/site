<div class="legenda">

<p>organization_id <input class="from-control" type="text" value="<?php echo $max_id;?>" id="organizationId" /></p>

<pre class="shell no_prod" rel="sql per inserire nuovo produttore">
INSERT INTO <?php echo Configure::read('DB.prefix');?>organizations 
(id, name,type,descrizione,indirizzo,localita,cap,provincia,
www2,sede_logistica_1,banca_iban,lat,lng,
sede_logistica_2,sede_logistica_3,sede_logistica_4,
template_id,j_group_registred,j_seo,
img1,paramsConfig,paramsFields,paramsPay,stato,created,modified) VALUES 
(<?php echo $max_id;?>,%NOME-PRODUTTORE%,'PRODGAS',
'','','','','','','','','','','','','',0,0,'',
'prodgas-<?php echo $max_id;?>.jpg',
 '{"hasBookmarsArticles":"N","hasArticlesOrder":"Y","hasVisibility":"N","hasUsersRegistrationFE":"N","hasPromotionGas":"N","hasPromotionGasUsers":"N","hasArticlesGdxp":"Y"}','{"hasFieldArticleCodice":"Y","hasFieldArticleIngredienti":"Y","hasFieldArticleCategoryId":"Y"}','{}',
 'Y','<?php echo date("Y-m-d");?> 00:00:00','<?php echo date("Y-m-d");?> 00:00:00');
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
echo Configure::read('App.root').'/images/organizations/contents/prodgas-'.$max_id.'.jpg<br />';
echo '</pre>';

echo $this->element('legendaTemplate');

echo '</div>';