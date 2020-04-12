angular.module('portalgas', [
  'ionic',
  'LocalStorageModule',
  'portalgas.controllers', 
  'portalgas.services'])
  .run(function($rootScope, $ionicPlatform, $http, localStorageService, Organization) {
  	
	$rootScope.device = '';
	$rootScope.organization_id = Organization.id();
	$rootScope.organization_label = Organization.label();
	$rootScope.user_organization_id = 0;
	$rootScope.user_name = localStorageService.get('user_name');;
	if(debug) console.log("App rootScope.organization_id "+$rootScope.organization_id+" rootScope.organization_label "+$rootScope.organization_label);
	
    $rootScope.isLogged = false;
	var authToken = localStorageService.get('authorizationToken');
	if(debug) console.log("App authToken "+authToken);
	
	if(authToken=='' || authToken==undefined || authToken==null) 
		$rootScope.isLogged = false;
	else {
		$http.defaults.headers.common.Authorization = authToken;
		$rootScope.isLogged = true;
    }

	$ionicPlatform.ready(function() {
		if (window.cordova && window.cordova.plugins.Keyboard) {
		  cordova.plugins.Keyboard.hideKeyboardAccessoryBar(true);
		}
	    if(window.StatusBar) {
      		StatusBar.styleDefault();
	    }
		
		$rootScope.device = device();
  	});

	// $stateChangeStart
    $rootScope.$on('$routeChangeStart', function (event) {

        var authToken = localStorageService.get('authorizationToken');
		if(debug) console.log("routeChangeStart authToken "+authToken);
		if(authToken=='' || authToken==undefined || authToken==null) {
            if(debug) console.log('DENY');
            event.preventDefault();
            $location.path('/home');
        }
        else {
            if(debug) console.log('ALLOW');
            $location.path('/deliveries');
        }
    }); 
 
    $rootScope.$on('event:routeChangeOrganization', function (event) {
		if(debug) console.log("event:routeChangeOrganization ");
		$rootScope.organization_id = Organization.id();
		$rootScope.organization_label = Organization.label();
    });  
	
	
	$rootScope.qtaLess = function(result) {
		result.msg = "";
        result.esitoCart = "";
		var qta = result.Cart_qta;
		if(result.ArticlesOrder_qta_multipli==1)
			qta--;
		else
			qta = (qta - result.ArticlesOrder_qta_multipli);
		if(qta<0) {
			if(result.ArticlesOrder_qta_multipli==1)
				qta++;
			else
				qta = (qta + result.ArticlesOrder_qta_multipli);
		}	
		result.Cart_qta = qta;
		
		if(result.ArticlesOrder_qta_minima > 1 && (parseInt(result.Cart_qta) < parseInt(result.ArticlesOrder_qta_minima))) {
			result.Cart_qta = 0;	
			result.msg = "un minimo di "+result.ArticlesOrder_qta_minima;			
		}
	};
	
	$rootScope.qtaAdd = function(result) {
		result.msg = "";
        result.esitoCart = "";
		if(result.ArticlesOrder_qta_multipli==1)
			result.Cart_qta++;
		else {
			result.Cart_qta = (parseInt(result.Cart_qta) + parseInt(result.ArticlesOrder_qta_multipli));			
			result.msg = "multipli di "+result.ArticlesOrder_qta_multipli;
		}

		if(result.ArticlesOrder_qta_minima > 1 && (parseInt(result.Cart_qta) < parseInt(result.ArticlesOrder_qta_minima))) {
			result.Cart_qta = result.ArticlesOrder_qta_minima;
			result.msg = "un minimo di "+result.ArticlesOrder_qta_minima;
		}
		
		if(result.ArticlesOrder_qta_massima > 0 && (parseInt(result.Cart_qta) > parseInt(result.ArticlesOrder_qta_massima))) {
			if(result.ArticlesOrder_qta_multipli==1)
				result.Cart_qta--;
			else
				result.Cart_qta = (parseInt(result.Cart_qta) - parseInt(result.ArticlesOrder_qta_multipli));
			result.msg = "un massimo di "+result.ArticlesOrder_qta_massima;
		}
		
		/* console.log(result.msg); */
	};
	
	$rootScope.ctrlCartSave = function(result) {
		/* console.log("ctrlCartSave - Cart_qta "+result.Cart_qta+' Cart_qta_orig '+result.Cart_qta_orig); */
		if(result.Cart_qta != result.Cart_qta_orig) 
			result.msgCartSave = "Non hai salvato!";
		
		/* console.log(result.msg); */
	};
})
.filter('arrotondaNumero', function ($filter) {
    return function (numero) {
		
		if (isNaN(numero)) return numero;
		
		var nDecimali = 2;
		var numero_arrotondato = Math.round(numero * Math.pow(10,nDecimali)) / Math.pow(10,nDecimali);
		return numero_arrotondato;
    };
})

.filter('getArticlePrezzoUM', function ($filter) {
    return function (ArticleOrder_prezzo, Article_qta, Article_um, Article_um_riferimento) {

		var debug = false;
		
		if(debug) {
			console.log("ArticleOrder_prezzo "+ArticleOrder_prezzo);
			console.log("Article_qta "+Article_qta);
			console.log("Article_um "+Article_um);
			console.log("Article_um_riferimento "+Article_um_riferimento);
		}
		
		var tmp = "";
		var prezzo_um_riferimento = '';
		
		if(ArticleOrder_prezzo=='' || Article_qta=='' || Article_qta == '0,00' || Article_qta == '0.00')
            prezzo_um_riferimento = '0,00';
		else {
			prezzo_um_riferimento = (ArticleOrder_prezzo / Article_qta);
		
			if(debug) 
				console.log("prezzo_um_riferimento "+prezzo_um_riferimento);

			if (Article_um == 'GR' && Article_um_riferimento == 'HG')
                prezzo_um_riferimento = (prezzo_um_riferimento * 100);
            else
            if (Article_um == 'GR' && Article_um_riferimento == 'KG')
                prezzo_um_riferimento = (prezzo_um_riferimento * 1000);
            else
            if (Article_um == 'HG' && Article_um_riferimento == 'GR')
                prezzo_um_riferimento = (prezzo_um_riferimento / 100);
            else
            if (Article_um == 'HG' && Article_um_riferimento == 'KG')
                prezzo_um_riferimento = (prezzo_um_riferimento * 10);
            else
            if (Article_um == 'KG' && Article_um_riferimento == 'GR')
                prezzo_um_riferimento = (prezzo_um_riferimento / 1000);
            else
            if (Article_um == 'KG' && Article_um_riferimento == 'HG')
                prezzo_um_riferimento = (prezzo_um_riferimento / 100);
            else
            if (Article_um == 'ML' && Article_um_riferimento == 'DL')
                prezzo_um_riferimento = (prezzo_um_riferimento * 10);
            else
            if (Article_um == 'ML' && Article_um_riferimento == 'LT')
                prezzo_um_riferimento = (prezzo_um_riferimento * 1000);
            else
            if (Article_um == 'DL' && Article_um_riferimento == 'ML')
                prezzo_um_riferimento = (prezzo_um_riferimento / 100);
            else
            if (Article_um == 'DL' && Article_um_riferimento == 'LT')
                prezzo_um_riferimento = (prezzo_um_riferimento * 10);
            else
            if (Article_um == 'LT' && Article_um_riferimento == 'ML')
                prezzo_um_riferimento = (prezzo_um_riferimento / 1000);
            else
            if (Article_um == 'LT' && Article_um_riferimento == 'DL')
                prezzo_um_riferimento = (prezzo_um_riferimento / 100);

            prezzo_um_riferimento = number_format(prezzo_um_riferimento, 2, ",", ".");
        }

        if (Article_um_riferimento!='') {
           // Article_um_riferimento = $this->traslateEnum(Article_um_riferimento); 

            tmp += prezzo_um_riferimento;
            tmp += ' €';
            tmp += ' al ' + Article_um_riferimento;
        } else {
            tmp += prezzo_um_riferimento;
        }

        return tmp;		
    };
})

.config(function($stateProvider, $urlRouterProvider) {

  $stateProvider
  
    .state('app', {
      url: "/app",
      abstract: true,
      templateUrl: "templates/menu.html",
      controller: 'AppCtrl'
    })
    .state('app.home', {
      url: "/home",
	    views: {
	      'menuContent' :{
	          controller:  "HomeCtrl",
	          templateUrl: "templates/home.html"            	
	      }
	  }      	  
    })
    .state('app.home/:organization_id', {
      url: "/home",
	    views: {
	      'menuContent' :{
	          controller:  "HomeCtrl",
	          templateUrl: "templates/home.html"            	
	      }
	  }      	  
    })
    .state('app.deliveries', {
	  cache: false,	
      url: "/deliveries",
      views: {
        'menuContent': {
          templateUrl: "templates/deliveries.html",
          controller: 'DeliveriesCtrl'
        }
      }
    })
  .state('app.delivery', {
	 cache: false,	
     url: "/delivery/:delivery_id",
     views: {
      'menuContent': {
        templateUrl: "templates/delivery.html",
        controller: 'DeliveryCtrl'
      }
    }
   })
  .state('app.articlesOrders', {
	 cache: false,	
     url: "/articles-orders/:order_id",
     views: {
      'menuContent': {
        templateUrl: "templates/articles-orders.html",
        controller: 'ArticlesOrdersCtrl'
      }
    }
   })
  .state('app.orders', {
	 cache: false,	
     url: "/orders",
     views: {
      'menuContent': {
        templateUrl: "templates/orders.html",
        controller: 'OrdersCtrl'
      }
    }
   })
    .state('app.deliveriesCarts', {
	  cache: false,	
      url: "/deliveriesCarts",
      views: {
        'menuContent': {
          templateUrl: "templates/deliveries-carts.html",
          controller: 'DeliveriesCartsCtrl'
        }
      }
    })   
  .state('app.carts', {
	 cache: false,	
     url: "/carts/:delivery_id",
     views: {
      'menuContent': {
        templateUrl: "templates/carts.html",
        controller: 'CartsCtrl'
      }
    }  
   })   
   .state('app.cartsPrint', {
	 cache: false,	
     url: "/cartsPrint/:delivery_id",
     views: {
      'menuContent': {
        templateUrl: "templates/carts-print.html",
        controller: 'CartsPrintCtrl'
      }
    }
   })	
    .state('app.logout', {
      url: "/logout",
      views: {
    	   'menuContent' :{
    		   controller: "LogoutCtrl",
           templateUrl: "templates/delivery.html"
         }
      } 	
  })
    .state('app.suppliers', {
      url: "/suppliers",
      views: {
    	   'menuContent' :{
    	   controller: "SuppliersCtrl",
           templateUrl: "templates/suppliers.html"
         }
      } 	
  })
    .state('app.supplier', {
      url: "/supplier/:supplier_id",
      views: {
    	   'menuContent' :{
    	   controller: "SupplierCtrl",
           templateUrl: "templates/supplier.html"
         }
      } 	
  })
    .state('app.users', {
      url: "/users",
      views: {
    	   'menuContent' :{
    	   controller: "UsersCtrl",
           templateUrl: "templates/users.html"
         }
      } 	
  })
    .state('app.user', {
      url: "/user/:user_id",
      views: {
    	   'menuContent' :{
    	   controller: "UserCtrl",
           templateUrl: "templates/user.html"
         }
      } 	
  })
  ;

  $urlRouterProvider.otherwise('/app/home'); 

});

function number_format( number, decimals, dec_point, thousands_sep ) {
	 /* da 1000.5678 in 1.000,57 */
	 /* da 1000 in 1.000,00 */
	 
	 var n = number, c = isNaN(decimals = Math.abs(decimals)) ? 2 : decimals;
	 var d = dec_point == undefined ? "." : dec_point;
	 var t = thousands_sep == undefined ? "," : thousands_sep, s = n < 0 ? "-" : "";
	 var i = parseInt(n = Math.abs(+n || 0).toFixed(c)) + "", j = (j = i.length) > 3 ? j % 3 : 0;
	 
	 return s + (j ? i.substr(0, j) + t : "") + i.substr(j).replace(/(\d{3})(?=\d)/g, "$1" + t) + (c ? d + Math.abs(n - i).toFixed(c).slice(2) : "");
}

function device() {
	var device = '';
	if( /iPhone|iPad|iPod/i.test(navigator.userAgent) ) 
		device = 'IOS';
	else
		device = 'OTHER';
	
	/* console.log(navigator.userAgent+' => device '+device); */
	return device;	
}
