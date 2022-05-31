<?php 
class SuperServiceArticlesBehavior extends ModelBehavior {

	public function setup(Model $Model, $settings = []) {
		if (!isset($this->settings[$Model->alias])) {
			$this->settings[$Model->alias] = [
					'option1_key' => 'option1_default_value',
					'option2_key' => 'option2_default_value',
					'option3_key' => 'option3_default_value',
			];
		}
		$this->settings[$Model->alias] = array_merge($this->settings[$Model->alias], (array)$settings);
	}

    /*
     * articoli filtrati per ordine
     *
     * Order.owner_articles REFERENT / DES / SUPPLIER
     * Order.owner_organization_id
     * Order.owner_supplier_organization_id
     *
     * return ArticlesOrder / Article
     */
    public function getArticlesByOrder(Model $Model, $user, $order, $opts=[], $debug=false)
    {
        $esito = [];

        if (empty($order)) {
            $esito['CODE'] = "500";
            $esito['MSG'] = "Parametri errati";
            return $esito;
        }

        if(!is_array($order))
            $order = $Model->_getOrderById($user, $order, $debug);

        /*
         * conditions
         */
        $options = [];
        $options['conditions'] = ['Article.organization_id' => $order['Order']['owner_organization_id'],
            'Article.supplier_organization_id' => $order['Order']['owner_supplier_organization_id'],
           'ArticlesOrder.organization_id' => $order['Order']['organization_id'],
           'ArticlesOrder.order_id' => $order['Order']['id']];

        $Model::d($opts, $debug);
        if(isset($opts['conditions']))
            $options['conditions'] = array_merge($options['conditions'], $opts['conditions']);
        if(isset($opts['order']))
            $options['order'] = $opts['order'];
        else
            $options['order'] = ['Article.name'];
        $options['recursive'] = 0;
        $Model::d($options, $debug);

        /*
         * get ArticlesOrder
         */
        App::import('Model', 'ArticlesOrder');
        $ArticlesOrder = new ArticlesOrder;
        $ArticlesOrder->unbindModel(['belongsTo' => ['Order', 'Cart']]);

        if (isset($opts['conditions']) && array_key_exists('Article.id', $opts['conditions']))
            $results = $ArticlesOrder->find('first', $options);
        else
            $results = $ArticlesOrder->find('all', $options);

        $Model::d($results, $debug);

        return $results;
    }

    /*
     * articoli filtrati per produttore
     * quando associo articoli ad ordine nuovo
     *
     * SuppliersOrganization.owner_articles REFERENT / DES / SUPPLIER
     * SuppliersOrganization.owner_organization_id
     * SuppliersOrganization.owner_supplier_organization_id
     *
     * return Article
     */
    public function getArticlesBySuppliersOrganization(Model $Model, $user, $suppliersOrganization, $opts=[], $debug=false)
    {
        $esito = [];

        if (empty($suppliersOrganization)) {
            $esito['CODE'] = "500";
            $esito['MSG'] = "Parametri errati";
            return $esito;
        }

        if(!is_array($suppliersOrganization))
            $suppliersOrganization = $Model->_getSuppliersOrganizationById($user, $suppliersOrganization, $debug);

        /*
         * conditions, includere anche SuppliersOrganization
         */
        $options = [];
        $options['conditions'] = [
            'SuppliersOrganization.id' => $suppliersOrganization['SuppliersOrganization']['owner_supplier_organization_id'],
            'SuppliersOrganization.organization_id' => $suppliersOrganization['SuppliersOrganization']['owner_organization_id'],
            'Article.organization_id' => $suppliersOrganization['SuppliersOrganization']['owner_organization_id'],
            'Article.supplier_organization_id' => $suppliersOrganization['SuppliersOrganization']['owner_supplier_organization_id']];

        $Model::d($opts, $debug);
        if(isset($opts['conditions']))
            $options['conditions'] = array_merge($options['conditions'], $opts['conditions']);
        if(isset($opts['order']))
            $options['order'] = $opts['order'];
        else
            $options['order'] = ['Article.name'];
        $options['recursive'] = 1;
        $Model::d($options, $debug);

        /*
         * get Articles
        */
        App::import('Model', 'ArticlesArticlesType');
        $ArticlesArticlesType = new ArticlesArticlesType;

        App::import('Model', 'Article');
        $Article = new Article;
        // $Article->unbindModel(['belongsTo' => ['CategoriesArticle']]);
        $Article->unbindModel(['hasOne' => ['ArticlesOrder', 'ArticlesArticlesType']]);
        $Article->unbindModel(['hasMany' => ['ArticlesOrder', 'ArticlesArticlesType']]);
        $Article->unbindModel(['hasAndBelongsToMany' => ['Order', 'ArticlesType']]);

        if (isset($opts['conditions']) && array_key_exists('Article.id', $opts['conditions']))
            $results = $Article->find('first', $options);
        else
            $results = $Article->find('all', $options);
        // debug($options);
        // debug($results);
        if(!empty($results))
            foreach($results as $numResult => $result) {
                $articlesTypeResults = $ArticlesArticlesType->getArticlesArticlesTypes($user, $result['Article']['organization_id'], $result['Article']['id']);
                if (!empty($articlesTypeResults))
                    $results[$numResult]['ArticlesType'] = $articlesTypeResults;
            }
        // $Model::d($results, $debug);

        return $results;
    }

	/*
	 * trasforma il results ottenuto da find('first') in results ottenuto da find('all') => $results[0]
	 */
	protected function _arrayConvertingToFindAll($results) {
		
		$results2 = []; 
		array_push($results2, $results);
		
		return $results2;		
	}
	
	/*
	 * trasforma il results ottenuto da find('all') in results ottenuto da find('first') => $results
	 */
	protected function _arrayConvertingToFindFirst($results) {
		
		return current($results);
	}
}	
?>