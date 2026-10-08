<?php
App::uses('AppModel', 'Model');

class CartSplits extends AppModel {

    public $tablePrefix = '';
	public $useTable = 'cart_splits';
		
    /*
     * se modifico la qta di un articolo elimino eventuali suddivisioni
     * mededimo metodo in neo
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