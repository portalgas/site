<?php
App::uses('AppController', 'Controller');

class MenusController extends AppController {
	
    public function beforeFilter() {
    	$this->ctrlHttpReferer();
    	 
    	parent::beforeFilter();
    }
    
	public function admin_delivery($id) {
	
        if (empty($id)) {
            $this->Session->setFlash(__('msg_error_params'));
            $this->myRedirect(Configure::read('routes_msg_exclamation'));
        }
        	
        App::import('Model', 'Delivery');
        $Delivery = new Delivery;
        
        $options = [];
        $options['conditions'] = ['Delivery.organization_id' => $this->user->organization['Organization']['id'],
        						 'Delivery.id' => $id];
        $options['recursive'] = -1;
        $results = $Delivery->find('first', $options);
        if(empty($results)) {
            $this->Session->setFlash(__('msg_error_params'));
            $this->myRedirect(Configure::read('routes_msg_exclamation'));
        }
        
        $menus = [];
        $i=0;
        $menus[$i]['label'] = __('View Calendar Delivery');
        $menus[$i]['controller'] = 'Deliveries';
        $menus[$i]['action'] = 'calendar_view';
        $menus[$i]['params'] = ['delivery_id' => $id];
        $menus[$i]['class'] = 'actionDeliveryCalendar';
        
        if($results['Delivery']['sys']=='N') {
	        $i++;
	        $menus[$i]['label'] = __('Copy');
	        $menus[$i]['controller'] = 'Deliveries';
	        $menus[$i]['action'] = 'copy';
	        $menus[$i]['params'] = ['delivery_id' => $id];
	        $menus[$i]['class'] = 'actionCopy';
	        $i++;
	        $menus[$i]['label'] = __('Edit');
	        $menus[$i]['controller'] = 'Deliveries';
	        $menus[$i]['action'] = 'edit';
	        $menus[$i]['params'] = ['delivery_id' => $id];
	        $menus[$i]['class'] = 'actionEdit';
	        $i++;
	        $menus[$i]['label'] = __('Print Delivery');
	        $menus[$i]['controller'] = 'Pages';
	        $menus[$i]['action'] = 'export_docs_delivery';
	        $menus[$i]['params'] = ['delivery_id' => $id];
	        $menus[$i]['class'] = 'actionPrinter';
	        $i++;
	        $menus[$i]['label'] = __('Delete');
	        $menus[$i]['controller'] = 'Deliveries';
	        $menus[$i]['action'] = 'delete';
	        $menus[$i]['params'] = ['delivery_id' => $id];
	        $menus[$i]['class'] = 'actionDelete';
        } // end if($results['Delivery']['sys']=='N')
        
        $this->set('menus', $menus);
        
      	$this->layout = 'ajax';
        $this->render('/Menus/admin_index');        
	}
	 
	public function admin_delivery_history($id) {
	
        if (empty($id)) {
            $this->Session->setFlash(__('msg_error_params'));
            $this->myRedirect(Configure::read('routes_msg_exclamation'));
        }
        
        $menus = [];
        $i=0;
        $menus[$i]['label'] = __('Copy');
        $menus[$i]['controller'] = 'Deliveries';
        $menus[$i]['action'] = 'copy';
        $menus[$i]['params'] = ['delivery_id' => $id];
        $menus[$i]['class'] = 'actionCopy';
        $i++;
        $menus[$i]['label'] = __('Print Delivery');
        $menus[$i]['controller'] = 'Pages';
        $menus[$i]['action'] = 'export_docs_delivery';
        $menus[$i]['params'] = ['delivery_id' => $id];
        $menus[$i]['class'] = 'actionPrinter';
        $i++;
        $menus[$i]['label'] = __('Delete');
        $menus[$i]['controller'] = 'Deliveries';
        $menus[$i]['action'] = 'delete';
        $menus[$i]['params'] = ['delivery_id' => $id];
        $menus[$i]['class'] = 'actionDelete';
        
        $this->set('menus', $menus);
        
      	$this->layout = 'ajax';
        $this->render('/Menus/admin_index');        
	}
		
	public function admin_article($article_organization_id, $id) {
	
        if (empty($article_organization_id) || empty($id)) {
            $this->Session->setFlash(__('msg_error_params'));
            $this->myRedirect(Configure::read('routes_msg_exclamation'));
        }
        	
        App::import('Model', 'Article');
        $Article = new Article;

		$menus = [];        
        $this->set('menus', $menus);
        
      	$this->layout = 'ajax';
        $this->render('/Menus/admin_index');        
	}	
}