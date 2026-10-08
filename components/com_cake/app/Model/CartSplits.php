<?php
App::uses('AppModel', 'Model');

class CartSplits extends AppModel {

    public $tablePrefix = '';
	public $useTable = 'cart_splits';

    public function initialize(Controller $controller) 
    {
    
    }
		
    /*
     * se modifico la qta di un articolo elimino eventuali suddivisioni
     * */
    public function deleteArticle($user, $organization_id, $order_id, $user_id, $article_organization_id, $article_id) {
        
        $where = ['organization_id' => $organization_id,
                'order_id' => $order_id,
                'user_id' => $user_id,
                'article_organization_id' => $article_organization_id,
                'article_id' => $article_id];
        $this->deleteAll($where);

        return true;
    }
}
?>