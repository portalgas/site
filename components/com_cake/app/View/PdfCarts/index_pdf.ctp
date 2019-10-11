<div class="pdf-carts" id="ajaxContent">

	<?php
        echo '<div class="well">'; 
        echo '<form role="select" class="navbar-form" accept-charset="utf-8" method="get" id="pdfForm" action="'.$_SERVER['REQUEST_URI'].'">';
        echo '<fieldset class="filter">'; 
        
        echo '<div class="row">'; 
        echo '<div class="col-md-6">';  
		$options =  array('name' => 'supplier_organization_id', 'label' => false,
                                'empty' => Configure::read('option.empty'),
                                'options' => $supplier_organizations, 
                                'default' => $supplier_organization_id,
                                'class'=> 'selectpicker chosen-select form-control', 'style' => 'width:350px;',
                                'data-live-search' => 'true');
		echo $this->Form->input('supplier_organization_id',$options);
        echo '</div>';  
        echo '<div class="col-md-3">';          
        	$options =  array('name' => 'year_id', 'label' => false,
                                'options' => $years,
                                'default' => $year_id,
                                'class' => 'selectpicker form-control');
		echo $this->Form->input('year_id',$options);
        echo '</div>';  
        echo '<div class="col-md-3">';          
        echo $this->Form->button('Filtra', array('type' => 'submit', 'class' => 'btn btn-primary'));
        echo '</div>';  
        echo '</div>';  
        echo '</fieldset>';
        echo $this->Form->end(); 
        echo '</div>';
         
     	$this->App->d($results);
     	
	if(!empty($results)) {
	?>
	<div class="table">
	<table class="table table-hover">
		<thead>
			<tr>
				<th colspan="2"><?php echo __('Delivery');?></th>
				<th></th>
				<th></th>
				<th><?php echo __('importo');?></th>
				<th><?php echo __('Pdf');?></th>
			</tr>
		</thead>
		<tbody>
	<?php
	foreach ($results as $numResult => $result): 

		echo '<tr>';
		echo '<td>';
		echo $this->Time->i18nFormat($result['PdfCart']['delivery_data'],"%A %e %B %Y");
		// ;
		echo '</td>';
		echo '<td>';
		echo $result['PdfCart']['delivery_luogo'];
		echo '</td>';
		echo '<td></td>';
		echo '<td></td>';
		echo '<td>';
		echo $result['PdfCart']['delivery_importo_e'];
		echo '</td>';
		echo '<td>';
		echo '<a href="'.Configure::read('App.server').Configure::read('App.web.doc.upload.pdf.carts').'/'.$result['PdfCart']['organization_id'].'/'.$result['PdfCart']['user_id'].'/'.$result['PdfCart']['uuid'].'.pdf" target="_blank" />';
		echo '<i class="fa fa-file-pdf-o fa-2x"></i>';
		echo '</a>';
		echo '</td>';
		echo '</tr>';
		
		if(isset($result['PdfCartsOrder']))
		foreach ($result['PdfCartsOrder'] as $numResult2 => $pdfCartsOrder) {
		
			echo '<tr>';
			echo '<td></td>';
			echo '<td></td>';
			echo '<td>';
			if(!empty($pdfCartsOrder['PdfCartsOrder']['supplier_img1']) && file_exists(Configure::read('App.root').Configure::read('App.img.upload.content').'/'.$pdfCartsOrder['PdfCartsOrder']['supplier_img1']))
				echo '<img width="50" class="img-responsive-disabled userAvatar" src="'.Configure::read('App.server').Configure::read('App.web.img.upload.content').'/'.$pdfCartsOrder['PdfCartsOrder']['supplier_img1'].'" />';
			echo '</td>';
			echo '<td>';
			echo $pdfCartsOrder['PdfCartsOrder']['supplier_organizations_name'];
			echo '</td>';
			echo '<td>';
			echo $pdfCartsOrder['PdfCartsOrder']['order_importo_e'];
			echo '</td>';
			echo '<td></td>';
			echo '</tr>';
		
		} 
		
	endforeach; 
	
	echo '</tbody></table></div>';
	
	}
	else {
		$msg = "Non ci sono ancora acquisti archiviati";
		echo $this->element('boxMsgFrontEnd',array('class_msg' => 'notice', 'msg' => $msg));	
	}
	?>
	
</div>
