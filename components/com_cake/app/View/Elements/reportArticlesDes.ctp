<?php
   foreach($desOrganizationResults as $desOrganizationResult) {
        
        $desSuppliers = $desSupplierResults[$desOrganizationResult['De']['id']];
        
        echo '<tr>';
	echo '<td>';
        if($type=='FE') 
            echo '<a style="cursor:pointer;" data-toggle="collapse" data-target="#trConfigId-ArticlesDes'.$desOrganizationResult['De']['id'].'" action="ArticlesDes'.$desOrganizationResult['De']['id'].'" title="'.__('Href_title_expand_config').'"><i class="fa fa-cogs fa-2x"></i></a>';
        else 
           echo '<a action="ArticlesDes'.$desOrganizationResult['De']['id'].'" class="actionTrConfig openTrConfig" href="#" title="'.__('Href_title_expand_config').'"></a>';
        echo '</td>';
        
        echo '<td>Tutti gli <b>articoli</b> del produttore D.E.S. <b>'.$desOrganizationResult['De']['name'].'</b></td>';
	echo '<td>';
        $options = array('label' => false, 
                                         'id' => 'des_supplier_id_'.$desOrganizationResult['De']['id'], 'options' => $desSuppliers,
                                          'empty' => 'Scegli per produttore D.E.S.','escape' => false);
        if(count($desSuppliers) > Configure::read('HtmlSelectWithSearchNum')) {
            if($type=='FE')
                $options += array('data-live-search' => 'true', 'size' => '1', 'class' => 'selectpicker-report dropup orders_select', 'data-width' => '100%', 'escape' => false); 
            else
                $options += array('class'=> 'selectpicker', 'data-live-search' => true); 
        }
        echo $this->Form->input('des_supplier_id_'.$desOrganizationResult['De']['id'],$options);
	echo '</td>';
	echo '<td><a class="exportArticlesDes'.$desOrganizationResult['De']['id'].'" data-action="articlesSupplierDes-PDF" style="cursor:pointer;" rel="nofollow" title="stampa gli articoli del produttore D.E.S. '.$desOrganizationResult['De']['name'].' '.__('formatFilePdf').'">';
        echo ($type=='FE') ? '<i class="fa fa-file-pdf-o fa-2x"></i>' : '<img alt="PDF" src="'.Configure::read('App.img.cake').'/minetypes/32x32/pdf.png">';
        echo '</a></td>';
	echo '<td><a class="exportArticlesDes'.$desOrganizationResult['De']['id'].'" data-action="articlesSupplierDes-CSV" style="cursor:pointer;" rel="nofollow" title="stampa gli articoli del produttore D.E.S. '.$desOrganizationResult['De']['name'].' '.__('formatFileCsv').'">';
        echo ($type=='FE') ? '<i class="fa fa-file-text-o fa-2x"></i>' : '<img alt="CSV" src="'.Configure::read('App.img.cake').'/minetypes/32x32/spreadsheet.png">';
        echo '</a></td>';
	echo '<td>';
        if(Configure::read('developer.mode'))
                echo 'No in developer mode';
        else {
            echo '<a class="exportArticlesDes'.$desOrganizationResult['De']['id'].'" data-action="articlesSupplierDes-EXCEL" style="cursor:pointer;" rel="nofollow" title="stampa gli articoli del produttore D.E.S. '.$desOrganizationResult['De']['name'].' '.__('formatFileExcel').'">';
            echo ($type=='FE') ? '<i class="fa fa-file-excel-o fa-2x"></i>' : '<img alt="EXCEL" src="'.Configure::read('App.img.cake').'/minetypes/32x32/vcalendar.png">';
            echo '</a>';
        }
	echo '</td>';		
    echo '</tr>';
    
    echo '<tr class="';
    echo ($type=='FE') ? 'collapse': 'trConfig';
    echo '" id="trConfigId-ArticlesDes'.$desOrganizationResult['De']['id'].'">';
    echo '<td></td>';
    echo '<td colspan="5" id="tdConfigId-ArticlesDes'.$desOrganizationResult['De']['id'].'">';
    
    if($type=='FE') {
    ?>
            <div class="form-group">
                    <label class="control-label col-xs-3">Visualizza le tipologie</label>
                    <div class="col-xs-2">
                            <label class="radio-inline">
                                    <input type="radio" id="filterTypeY<?php echo $desOrganizationResult['De']['id'];?>" name="filterTypeDes<?php echo $desOrganizationResult['De']['id'];?>" value="Y" checked /> Si
                            </label>
                    </div>
                    <div class="col-xs-2">
                            <label class="radio-inline">
                                    <input type="radio" id="filterTypeN<?php echo $desOrganizationResult['De']['id'];?>" name="filterTypeDes<?php echo $desOrganizationResult['De']['id'];?>" value="N" /> No
                            </label>
                    </div>
            </div>

            <div class="form-group">
                    <label class="control-label col-xs-3">Visualizza le categorie</label>
                    <div class="col-xs-2">
                            <label class="radio-inline">
                                    <input type="radio" id="filterCategoryY<?php echo $desOrganizationResult['De']['id'];?>" name="filterCategoryDes<?php echo $desOrganizationResult['De']['id'];?>" value="Y" checked /> Si
                            </label>
                    </div>
                    <div class="col-xs-2">
                            <label class="radio-inline">
                                    <input type="radio" id="filterCategoryN<?php echo $desOrganizationResult['De']['id'];?>" name="filterCategoryDes<?php echo $desOrganizationResult['De']['id'];?>" value="N" /> No
                            </label>
                    </div>
            </div>

            <div class="form-group">
                    <label class="control-label col-xs-3">Visualizza le note</label>
                    <div class="col-xs-2">
                            <label class="radio-inline">
                                    <input type="radio" id="filterNotaY<?php echo $desOrganizationResult['De']['id'];?>" name="filterNotaDes<?php echo $desOrganizationResult['De']['id'];?>" value="Y" checked /> Si
                            </label>
                    </div>
                    <div class="col-xs-2">
                            <label class="radio-inline">
                                    <input type="radio" id="filterNotaN<?php echo $desOrganizationResult['De']['id'];?>" name="filterNotaDes<?php echo $desOrganizationResult['De']['id'];?>" value="N" /> No
                            </label>
                    </div>
            </div>

            <div class="form-group">
                    <label class="control-label col-xs-3">Visualizza gli ingredienti</label>
                    <div class="col-xs-2">
                            <label class="radio-inline">
                                    <input type="radio" id="filterIngredientiY<?php echo $desOrganizationResult['De']['id'];?>" name="filterIngredientiDes<?php echo $desOrganizationResult['De']['id'];?>" value="Y" checked /> Si
                            </label>
                    </div>
                    <div class="col-xs-2">
                            <label class="radio-inline">
                                    <input type="radio" id="filterIngredientiN<?php echo $desOrganizationResult['De']['id'];?>" name="filterIngredientiDes<?php echo $desOrganizationResult['De']['id'];?>" value="N" /> No
                            </label>
                    </div>
            </div>				
        <?php
        }
        else {
        /*
         *  BACKOFFICE
         */
        ?>
            <div class="left label" style="width:125px !important;">Opzioni stampa</div>
            <div class="left radio">
                    <p>
                            <label for="filterTypeDes<?php echo $desOrganizationResult['De']['id'];?>">Visualizza le tipologie</label>
                            <input type="radio" id="filterTypeYDes<?php echo $desOrganizationResult['De']['id'];?>" name="filterTypeDes<?php echo $desOrganizationResult['De']['id'];?>" value="Y" checked /><label for="filterTypeYDes<?php echo $desOrganizationResult['De']['id'];?>">Si</label>
                            <input type="radio" id="filterTypeNDes<?php echo $desOrganizationResult['De']['id'];?>" name="filterTypeDes<?php echo $desOrganizationResult['De']['id'];?>" value="N" /><label for="filterTypeNDes<?php echo $desOrganizationResult['De']['id'];?>">No</label>
                    </p>
                    <p>
                            <label for="filterCategoryDes<?php echo $desOrganizationResult['De']['id'];?>">Visualizza le categorie</label>
                            <input type="radio" id="filterCategoryYDes<?php echo $desOrganizationResult['De']['id'];?>" name="filterCategoryDes<?php echo $desOrganizationResult['De']['id'];?>" value="Y" checked /><label for="filterCategoryYDes<?php echo $desOrganizationResult['De']['id'];?>">Si</label>
                            <input type="radio" id="filterCategoryNDes<?php echo $desOrganizationResult['De']['id'];?>" name="filterCategoryDes<?php echo $desOrganizationResult['De']['id'];?>" value="N" /><label for="filterCategoryNDes<?php echo $desOrganizationResult['De']['id'];?>">No</label>
                    </p>	
                    <p>
                            <label for="filterNotaDes<?php echo $desOrganizationResult['De']['id'];?>">Visualizza le note</label>
                            <input type="radio" id="filterNotaYDes<?php echo $desOrganizationResult['De']['id'];?>" name="filterNotaDes<?php echo $desOrganizationResult['De']['id'];?>" value="Y" checked /><label for="filterNotaDes<?php echo $desOrganizationResult['De']['id'];?>">Si</label>
                            <input type="radio" id="filterNotaNDes<?php echo $desOrganizationResult['De']['id'];?>" name="filterNotaDes<?php echo $desOrganizationResult['De']['id'];?>" value="N" /><label for="filterNotaNDes<?php echo $desOrganizationResult['De']['id'];?>">No</label>
                    </p>
                    <p>
                            <label for="filterIngredientiDes<?php echo $desOrganizationResult['De']['id'];?>">Visualizza gli ingredienti</label>
                            <input type="radio" id="filterIngredientiYDes<?php echo $desOrganizationResult['De']['id'];?>" name="filterIngredientiDes<?php echo $desOrganizationResult['De']['id'];?>" value="Y" checked /><label for="filterIngredientiYDes<?php echo $desOrganizationResult['De']['id'];?>">Si</label>
                            <input type="radio" id="filterIngredientiNDes<?php echo $desOrganizationResult['De']['id'];?>" name="filterIngredientiDes<?php echo $desOrganizationResult['De']['id'];?>" value="N" /><label for="filterIngredientiNDes<?php echo $desOrganizationResult['De']['id'];?>">No</label>
                    </p>
            </div>	
        <?php
        }
	echo '</td>';
    echo '</tr>';

    } // foreach($desOrganizationResults as $desOrganizationResult) 
?>
<script type="text/javascript">
jQuery(document).ready(function() {
        <?php 
        foreach($desOrganizationResults as $desOrganizationResult) {
        ?>
	jQuery('.exportArticlesDes<?php echo $desOrganizationResult['De']['id'];?>').click(function() {
		var des_supplier_id = jQuery('#des_supplier_id_<?php echo $desOrganizationResult['De']['id'];?>').val();
		if(des_supplier_id=="") {
			alert("Devi scegliere il produttore D.E.S. <?php echo $desOrganizationResult['De']['name'];?>");
			return false;
		}
		
		var id =  jQuery(this).attr('data-action');
		idArray = id.split('-');
		var action      = idArray[0];
		var doc_formato = idArray[1];

		/*
		 * filtri
		 */
		var filterType = jQuery("input[name='filterTypeDes<?php echo $desOrganizationResult['De']['id'];?>']:checked").val();
		var filterCategory = jQuery("input[name='filterCategoryDes<?php echo $desOrganizationResult['De']['id'];?>']:checked").val();
		var filterNota = jQuery("input[name='filterNotaDes<?php echo $desOrganizationResult['De']['id'];?>']:checked").val();
                var filterIngredienti = jQuery("input[name='filterIngredientiDes<?php echo $desOrganizationResult['De']['id'];?>']:checked").val();	
		        
                <?php
                if($type=='FE')
                    echo 'window.open("/?option=com_cake&controller=ExportDocs&action="+action+"&des_id='.$desOrganizationResult['De']['id'].'&des_supplier_id="+des_supplier_id+"&filterType="+filterType+"&filterCategory="+filterCategory+"&filterNota="+filterNota+"&filterIngredienti="+filterIngredienti+"&doc_formato="+doc_formato+"&format=notmpl","win2","status=no,toolbar=no,scrollbars=yes,titlebar=no,menubar=no,resizable=yes,width=640,height=480,directories=no,location=no");';
                else
                    echo 'window.open("/administrator/index.php?option=com_cake&controller=ExportDocs&action="+action+"&des_id='.$desOrganizationResult['De']['id'].'&des_supplier_id="+des_supplier_id+"&filterType="+filterType+"&filterCategory="+filterCategory+"&filterNota="+filterNota+"&filterIngredienti="+filterIngredienti+"&doc_formato="+doc_formato+"&format=notmpl","win2","status=no,toolbar=no,scrollbars=yes,titlebar=no,menubar=no,resizable=yes,width=640,height=480,directories=no,location=no");';
	echo '});';
        }
        ?>
});
</script>