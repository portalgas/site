<div class="docs">
<?php echo $this->Form->create();?>
	<fieldset style="min-height:600px;">
	<table cellpadding = "0" cellspacing = "0">
	<tr>
		<th colspan="2">Tipologia di documento</th>
		<th>Filtro</th>
		<th>Formato pdf</th>
		<th>Formato csv</th>
		<th>Formato excel</th>
	</tr>
        <?php
            echo $this->element('reportArticles', ['suppliersOrganization' => $suppliersOrganization, 'type' => 'BO']);

            if($user->organization['Organization']['hasDes']=='Y') 
                echo $this->element('reportArticlesDes', ['desOrganizationResults' => $desOrganizationResults, 'type' => 'BO']);
        ?>
	</table>
	</fieldset>
</div>