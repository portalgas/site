<?php 
echo $this->Form->create('FilterArticle', ['id'=>'formGasFilter', 'type'=>'get']);
echo '<fieldset class="filter">';
echo '<legend>'.__('Filter Articles').'</legend>';

if(!empty($FilterArticleOrderById)) {
    echo '<div class="row">';
    echo '<div class="col-md-12">';
    echo $this->Form->input('order_id', ['label' => '&nbsp;', 'class' => 'form-control', 'empty' => Configure::read('option.empty'), 'name'=>'FilterArticleOrderById' ,'default' => $FilterArticleOrderById]);
    echo '</div>';
    echo '</div>';
}	
echo '<div class="row">';
echo '<div class="col-md-12">';
echo $this->Form->drawFormCheckbox('Article', 'FilterArticleArticleTypeIds', ['options' => $ArticlesTypeResults, 'class' => 'form-control', 'selected'=> $FilterArticleArticleIds, 'label'=> ' ', 'name'=>'FilterArticleArticleTypeIds']);
echo '</div>';
echo '</div>';

if(($user->organization['Organization']['type']=='GAS' || $user->organization['Organization']['type']=='PRODGAS') && $user->organization['Organization']['hasFieldArticleCategoryId']=='Y') { 
    echo '<div class="row">';
    echo '<div class="col-md-8">';
    echo $this->Form->input('category_article_id', ['label' => '&nbsp;', 'class' => 'form-control', 'options' => $categories, 'empty' => 'Filtra per categoria','name'=>'FilterArticleCategoryArticleId','default'=>$FilterArticleCategoryArticleId,'escape' => false]);
    echo '</div>';
    echo '<div class="col-md-4">';
    echo $this->Form->input('flag_presente_articlesorders',['label' => __('FlagPresenteArticlesorders'), 'class' => 'form-control', 'options' => $flag_presente_articlesorders,'name'=>'FilterArticleFlagPresenteArticlesorders','default'=>$FilterArticleFlagPresenteArticlesorders,'escape' => false]);
    echo '</div>';
    echo '</div>';
}	
else {
    if(($user->organization['Organization']['type']=='GAS' || $user->organization['Organization']['type']=='PRODGAS') && $user->organization['Organization']['hasFieldArticleCategoryId']=='Y') { 
        echo '<div class="row">';
        echo '<div class="col-md-2 col-md-offset-10">';
        echo $this->Form->input('flag_presente_articlesorders',['label' => __('FlagPresenteArticlesorders'), 'class' => 'form-control', 'options' => $flag_presente_articlesorders,'name'=>'FilterArticleFlagPresenteArticlesorders','default'=>$FilterArticleFlagPresenteArticlesorders,'escape' => false]);
        echo '</div>';
        echo '</div>';
    }	
}
                
echo '<div class="row">';
echo '<div class="col-md-8">';
if($user->organization['Organization']['type']=='GAS') {
    $options = ['label' => '&nbsp;', 
                'options' => $ACLsuppliersOrganization,
                'name'=>'FilterArticleSupplierId', 'default' => $FilterArticleSupplierId, 'escape' => false];
    if(count($ACLsuppliersOrganization) > 1) 
        $options += ['data-placeholder'=> __('FilterToSuppliers'), 'empty' => __('FilterToSuppliers')];								
    if(count($ACLsuppliersOrganization) > Configure::read('HtmlSelectWithSearchNum')) 
        $options += ['class'=> 'selectpicker', 'data-live-search' => true];
    echo $this->Form->input('supplier_organization_id',$options);					
}
else
    echo $this->Form->input('supplier_organization_id', ['label' => '&nbsp;', 'class' => 'form-control', 
                                        'options' => $ACLsuppliersOrganization, 
                                        'name' => 'FilterArticleSupplierId',
                                        'default' => $FilterArticleSupplierId,
                                        'escape' => false]);
echo '</div>';

echo '<div class="col-md-2">';
echo $this->Form->input('um',['label' => __('Um'), 'class' => 'form-control', 'options' => $um,'name'=>'FilterArticleUm','empty'=>'-----','default'=>$FilterArticleUm,'escape' => false]);
echo '</div>';			
echo '<div class="col-md-2">';
echo $this->Form->input('stato', ['label' => __('Stato'), 'class' => 'form-control', 'options' => $stato, 'name' => 'FilterArticleStato', 'default' => $FilterArticleStato, 'escape' => false]); 
echo '</div>';	
echo '</div>';	

echo '<div class="row">';
if($user->organization['Organization']['hasFieldArticleCodice']=='Y') {
    echo '<div class="col-md-2">';				 
    echo $this->Ajax->autoComplete('FilterArticleCode', 
                            Configure::read('App.server').'/administrator/index.php?option=com_cake&controller=Ajax&action=autoCompleteContextArticlesArticles_codice&format=notmpl',
                            ['label' => 'Codice', 'class' => 'form-control', 'name' => 'FilterArticleCodice','value' => $FilterArticleCodice, 'escape' => false]);
    echo '</div>';    
}
($user->organization['Organization']['hasFieldArticleCodice']=='Y') ? $colspan = '6': $colspan = '8';
echo '<div class="col-md-'.$colspan.'">';				 
echo $this->Ajax->autoComplete('FilterArticleName', 
                        Configure::read('App.server').'/administrator/index.php?option=com_cake&controller=Ajax&action=autoCompleteContextArticlesArticles_name&format=notmpl',
                        ['label' => 'Nome', 'class' => 'form-control', 'name' => 'FilterArticleName','value' => $FilterArticleName, 'escape' => false]);
echo '</div>';
echo '<div class="col-md-2">';
echo $this->Form->input('orderby', ['label' => __('Sort'), 'class' => 'form-control', 'options' => $orderbys, 'name' => 'FilterArticleOrderBy', 'default' => $FilterArticleOrderBy, 'escape' => false]); 
echo '</div>';					
echo '<div class="col-md-1">';	
echo $this->Form->reset('Reset', ['value' => 'Reimposta','class' => 'reset']);
echo '</div>';	
echo '<div class="col-md-1">';	
echo $this->Form->submit(__('Filter'), ['class' => 'filter', 'div' => ['class' => 'submit filter', 'style' => 'display:none']]); 
echo '</div>';		
echo '</div>';
echo '</fieldset>';
echo $this->Form->end(); 