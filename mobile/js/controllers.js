var debug = false;
// punta sempre a /var/www/portalgas.com ma non ha blocchi di cross origin!!!!
var urlRest = "https://mobile.portalgas.com";
//var urlRest = "https://www.portalgas.com";
//var urlRest = "http://localhost/portalgas.com";

angular.module('portalgas.controllers', ['uiGmapgoogle-maps'])

.controller('AppCtrl', function($rootScope, $scope, $state, $timeout, $ionicModal, localStorageService, Organization) {
 
   $scope.exitApp = function() {
		ionic.Platform.exitApp(); 
	};

  
  $scope.toggleBlock = function(obj) {
    if ($scope.isBlockShown(obj)) {
      $scope.shownBlock = null;
    } else {
      $scope.shownBlock = obj;
    }
  };
  $scope.isBlockShown = function(obj) {
    return $scope.shownBlock === obj;
  };
 
 
  /*
   *   M O D A L - L O G I N
   */  
  $ionicModal.fromTemplateUrl('templates/login.html', function(modal) {
      $scope.modalLogin = modal;
    },
    {
      scope: $scope,
      animation: 'slide-in-up',
      focusFirstInput: true
    }
  );
  $scope.openModalLogin = function() {
	 $scope.modalLogin.show();
  };  
  $scope.closeModalLogin = function() {
	if(debug) console.log('Call closeModalLogin');
	$scope.modalLogin.hide();
  };
  $scope.$on('$destroy', function() {
    $scope.modalLogin.remove();
  });


  /*
   *   M O D A L - O R G A N I Z A T I O N S
   */
  $scope.organizationSelect = {};

	$ionicModal.fromTemplateUrl('templates/modal-organizations.html', {
		scope: $scope
	}).then(function(modal) {
		$scope.modalOrganizations = modal;
	});

	$scope.openModalOrganizations = function() {
		$scope.organizations=Organization.getAll();  
		$scope.modalOrganizations.show();
	};

	$scope.closeModalOrganizations = function() {
		// if(debug) console.log('Call closeModalOrganizations');
		$scope.modalOrganizations.hide();
	};
	
	$scope.selectModalOrganizations = function(organization_id) {
		if(debug) console.log('Call selectModalOrganizations ', organization_id);
		Organization.storage(organization_id);
		
		$timeout(function() {
			$rootScope.$broadcast('event:routeChangeOrganization');
			$scope.closeModalOrganizations();
		}, 500);
		 
        $state.go('app.deliveries', {}, {reload: true, inherit: false});
		// $location.path("/home");
	};	
})
  
.controller('LoginCtrl', function($rootScope, $scope, $http, $state, $ionicHistory , localStorageService, Organization, AuthenticationService) {

  $scope.message = "";
  
  $scope.user = {
    username: null,
    password: null
  };
 
  $scope.login = function() {
    AuthenticationService.login($scope.user);
  };

  $scope.$on('event:auth-loginRequired', function(e, rejection) {
    if(debug) console.log('handling login required');
    $scope.modalLogin.show();
  });
 
  $scope.$on('event:auth-loginConfirmed', function() {
	 if(debug) console.log("login complete");
	 $scope.username = null;
	 $scope.password = null;
     $scope.modalLogin.hide();

	 $rootScope.isLogged = true;	
	 $rootScope.organization_id = localStorageService.get('user_organization_id');
	 $rootScope.user_organization_id = localStorageService.get('user_organization_id');
	 $rootScope.user_name = localStorageService.get('user_name');
	 
	 Organization.storage($rootScope.organization_id)
		.then(function() {
			$rootScope.organization_label = localStorageService.get('organization_label');
		},
		function(data) {

		});
				
	 if(debug) console.log($rootScope.$viewHistory);
	 $ionicHistory.clearHistory();
	 $state.go('app.home', {}, {reload: true, inherit: false});		  	 
  });
  
  $scope.$on('event:auth-login-failed', function(e, status) {
    var error = "Autenticazione fallita!";
    if (status == 401) {
      error = "Username o Password non validi!";
    }
    $scope.message = error;
	$rootScope.isLogged = false;
	$rootScope.organization_id = 0;
	$rootScope.user_organization_id = 0;
	$rootScope.user_name = '';	
  });
 
  $scope.$on('event:auth-logout-complete', function() {
	  if($rootScope.isLogged) {
		if(debug) console.log("logout complete");
		$rootScope.isLogged = false;
		$rootScope.organization_id = 0;
		$rootScope.user_organization_id = 0;
		$rootScope.user_name = '';	

		$ionicHistory.clearHistory();		
		$state.go('app.home', {}, {reload: true, inherit: false});		  
	  }
  });    	
})
.controller('HomeCtrl', function($rootScope, $scope, Organization) {
	
	if(debug) console.log("HomeCtrl Organization.id "+Organization.id());
	
})
.controller('DeliveriesCtrl', function($rootScope, $scope, LoaderService, Organization, Deliveries) {	
	LoaderService.show();
	$scope.rowsFound = -1;
	
	if(debug) console.log("DeliveriesCtrl Organization.id "+Organization.id());
	
	if(Organization.id()>0)
		Deliveries.getAll()
				.then(function(deliveries) {
                    $scope.deliveries = deliveries;
					$scope.rowsFound = $scope.deliveries.length;
					LoaderService.hide();
                },
                function(data) {
                    if(debug) console.log('DeliveriesCtrl() Error' + data)
					LoaderService.hide();
                });
	else
		LoaderService.hide();
})
.controller('DeliveryCtrl', function($scope, $stateParams, LoaderService, Organization, Deliveries) {
	LoaderService.show();
	$scope.rowsFound = -1;
	
	if(debug) console.log("DeliveryCtrl Organization.id "+Organization.id());
	
	if(Organization.id()>0)
		Deliveries.getItem($stateParams.delivery_id)
				.then(function(delivery) {
                    $scope.delivery = delivery;
					$scope.rowsFound = $scope.delivery.length;
					LoaderService.hide();
                },
                function(data) {
                    if(debug) console.log('DeliveryCtrl() Error' + data)
					LoaderService.hide();
                });
	else
		LoaderService.hide();
})
.controller('ArticlesOrdersCtrl', function($scope, $stateParams, LoaderService, localStorageService, Organization, ArticlesOrders, Carts) {
	LoaderService.show();
	$scope.rowsFound = -1;
	$scope.isOrderToCart = false; // true se si possono effettuare acquisti
	
	if(debug) console.log("ArticlesOrdersCtrl Organization.id "+Organization.id());
	
	if(Organization.id()>0)
		ArticlesOrders.getAll($stateParams.order_id)
				.then(function(results) {
					$scope.isOrderToCart = results['isOrderToCart'];
                    $scope.articlesOrders = results['articlesOrders'];
					$scope.rowsFound = $scope.articlesOrders.length;
					LoaderService.hide();
					
					/* console.log("isOrderToCart "+$scope.isOrderToCart); */
                },
                function(data) {
                    if(debug) console.log('ArticlesOrdersCtrl() Error' + data)
					LoaderService.hide();
                });
	else
		LoaderService.hide();

	$scope.total = function() {
		var total = 0;
		angular.forEach($scope.articlesOrders, function(result) {
			if(result.Cart_qta!=null) 
				total += (result.Cart_qta * result.ArticlesOrder_prezzo);
		})

		if(total==0)
			$scope.cart_import_total=false;
		else
			$scope.cart_import_total=true;
		
		total = number_format(total,2,",",".");
		return total;
	}	
	
	$scope.managementCart = function(result) {
		LoaderService.show(); 
		Carts.managementCart(result.ArticlesOrder_order_id, result.ArticlesOrder_article_organization_id, result.ArticlesOrder_article_id, result.Cart_qta)
                .then(function(esitoCart) {
						LoaderService.hide(); 
                        result.msg = '';
						result.esitoCart = esitoCart;
                        if(result.esitoCart.esito=="OK") {
							result.Cart_qta_orig = result.Cart_qta;
							result.msgCartSave = '';
						}
                        /* console.log(result.esitoCart); */ 
                },
                function(data) {
                });	
		LoaderService.hide(); 
	};
})
.controller('OrdersCtrl', function($scope, $stateParams, LoaderService, localStorageService, Organization, Orders) {
	LoaderService.show();
	$scope.rowsFound = -1;
	
	if(debug) console.log("OrdersCtrl Organization.id "+Organization.id());
	
	if(Organization.id()>0)
		Orders.getAll()
				.then(function(orders) {
                    $scope.orders = orders;
					$scope.rowsFound = $scope.orders.length;
					LoaderService.hide();
                },
                function(data) {
                    if(debug) console.log('OrdersCtrl() Error' + data)
					LoaderService.hide();
                });
	else
		LoaderService.hide();
})
.controller('DeliveriesCartsCtrl', function($rootScope, $scope, LoaderService, Organization, DeliveriesCarts) {	
	LoaderService.show();
	$scope.rowsFound = -1;
	
	if(debug) console.log("DeliveriesCartsCtrl Organization.id "+Organization.id());
	
	if(Organization.id()>0)
		DeliveriesCarts.getAll()
				.then(function(deliveries) {
                    $scope.deliveries = deliveries;
					$scope.rowsFound = $scope.deliveries.length;
					LoaderService.hide();
                },
                function(data) {
                    if(debug) console.log('DeliveriesCartsCtrl() Error' + data)
					LoaderService.hide();
                });
	else
		LoaderService.hide();
})
.controller('CartsCtrl', function($rootScope, $scope, $stateParams, LoaderService, Organization, Carts) {	

	$scope.isVisibleComplete = true;
	
	$scope.visibleComplete = function() {
		if($scope.isVisibleComplete)
			$scope.isVisibleComplete = false;
		else
			$scope.isVisibleComplete = true;
	};
	
	LoaderService.show();
	$scope.rowsFound = -1;
	
	$scope.delivery_id = $stateParams.delivery_id;
	
	if(debug) console.log("CartsCtrl Organization.id "+Organization.id());
	
	if(Organization.id()>0)
		Carts.getAll($stateParams.delivery_id)
				.then(function(carts) {
                    $scope.carts = carts;
					$scope.rowsFound = $scope.carts.length;
					total();
					LoaderService.hide();
                },
                function(data) {
                    if(debug) console.log('CartsCtrl() Error' + data)
					LoaderService.hide();
                });
	else
		LoaderService.hide();

	$scope.managementCart = function(result) {
		LoaderService.show(); 
		Carts.managementCart(result.ArticlesOrder_order_id, result.ArticlesOrder_article_organization_id, result.ArticlesOrder_article_id, result.Cart_qta)
                .then(function(esitoCart) {
						LoaderService.hide();
						result.msg = '';
                        result.esitoCart = esitoCart;
                        if(result.esitoCart.esito=="OK") {
							result.Cart_qta_orig = result.Cart_qta;
							result.msgCartSave = '';
							total();
							/* console.log(result.esitoCart); */ 
						}
                },
                function(data) {
                });	
		LoaderService.hide();
	};	
	
	total = function() {
		var total = 0;
		angular.forEach($scope.carts, function(result) {
			angular.forEach(result.Carts, function(cart) {
				/* console.log(cart.Cart_importo_final); */
				var qta  = 0;
				
				if (cart.Cart_qta_forzato != '0')
					qta = cart.Cart_qta_forzato;
				else
					qta = cart.Cart_qta;
				/* console.log("qta "+qta); */
				
				var importo = 0;
				if (cart.Cart_importo_forzato != '0.00') {
					importo = parseFloat(cart.Cart_importo_forzato);
				}
				else {
					var prezzo = parseFloat(cart.ArticlesOrder_prezzo);
					/* console.log("prezzo "+prezzo); */
					importo = (qta * prezzo);
				}
				/* console.log(importo); */
				total += importo;				
			})

		})

		total = number_format(total,2,",",".");
		/* console.log("CartsCtrl - total "+total); */
		$scope.cart_import_total = total;
	}	
})

.controller('CartsPrintCtrl', function($rootScope, $scope, $stateParams, LoaderService, Organization, Carts) {	

	$scope.isVisibleComplete = true;
	
	$scope.visibleComplete = function() {
		if($scope.isVisibleComplete)
			$scope.isVisibleComplete = false;
		else
			$scope.isVisibleComplete = true;
	};
	
	LoaderService.show();
	$scope.rowsFound = -1;
	
	$scope.delivery_id = $stateParams.delivery_id;
	
	if(debug) console.log("CartsPrintCtrl Organization.id "+Organization.id());
	
	if(Organization.id()>0)
		Carts.getAll($stateParams.delivery_id)
				.then(function(carts) {
                    $scope.carts = carts;
					$scope.rowsFound = $scope.carts.length;
					total();
					LoaderService.hide();
                },
                function(data) {
                    if(debug) console.log('CartsPrintCtrl() Error' + data)
					LoaderService.hide();
                });
	else
		LoaderService.hide();
	
	total = function() {
		var total = 0;
		angular.forEach($scope.carts, function(result) {
			angular.forEach(result.Carts, function(cart) {
				/* console.log(cart.Cart_importo_final); */
				var qta  = 0;
				
				if (cart.Cart_qta_forzato != '0')
					qta = cart.Cart_qta_forzato;
				else
					qta = cart.Cart_qta;
				/* console.log("qta "+qta); */
				
				var importo = 0;
				if (cart.Cart_importo_forzato != '0.00') {
					importo = parseFloat(cart.Cart_importo_forzato);
				}
				else {
					var prezzo = parseFloat(cart.ArticlesOrder_prezzo);
					/* console.log("prezzo "+prezzo); */
					importo = (qta * prezzo);
				}
				/* console.log(importo); */
				total += importo;				
			})

		})

		total = number_format(total,2,",",".");
		/* console.log("CartsCtrl - total "+total); */
		$scope.cart_import_total = total;
	}	
})
.controller('HomeCtrl', function($scope, Organization) {
	if(debug) console.log("HomeCtrl Organization.id "+Organization.id());
	
	/*
	 * richiedo sempre la login
	$scope.$on('$ionicView.enter', function() {
		$timeout(function(){
		  $rootScope.$broadcast('event:auth-loginRequired');
		});
	});	 
	*/
})
.controller('LogoutCtrl', function($scope, AuthenticationService) {
    $scope.$on('$ionicView.enter', function() {
      AuthenticationService.logout();
    });
})
.controller('SuppliersCtrl', function($rootScope, $scope, $ionicPopover, LoaderService, Suppliers) {	
	
	$scope.showPopSearch = false;
	
	$scope.openPopSearch = function() {
		if($scope.showPopSearch)
			$scope.showPopSearch = false;
		else
			$scope.showPopSearch = true;
	};

	LoaderService.show();
	$scope.rowsFound = -1;
	
	if(debug) console.log("SuppliersCtrl");
	
	Suppliers.getAll()
			.then(function(suppliers) {
				$scope.suppliers = suppliers;
				$scope.rowsFound = $scope.suppliers.length;
				LoaderService.hide();
			},
			function(data) {
				if(debug) console.log('SuppliersCtrl() Error' + data)
				LoaderService.hide();
			});
})
.controller('SupplierCtrl', function($rootScope, $scope, $stateParams, LoaderService, Suppliers, uiGmapGoogleMapApi) {
	
	LoaderService.show();
	$scope.rowsFound = -1;

	if(debug) console.log("SupplierCtrl id "+$stateParams.supplier_id);
	
	if($stateParams.supplier_id>0)
		Suppliers.getItem($stateParams.supplier_id)
				.then(function(supplier) {
                    $scope.supplier = supplier;
					$scope.rowsFound = $scope.supplier.length;
					
					if(debug) console.log("SupplierCtrl supplier.lat "+$scope.supplier.lat+" supplier.lng "+$scope.supplier.lng);
					
						if($scope.supplier.lat!='' && $scope.supplier.lng!='') {
							
			
								  $scope.map = {
									center: {
									  latitude: $scope.supplier.lat,
									  longitude: $scope.supplier.lng
									},
									zoom: 10,
									pan: 1
								  };

								  $scope.marker = {
									id: 0,
									coords: {
									  latitude: $scope.supplier.lat,
									  longitude: $scope.supplier.lng
									}
								  }; 
								  
								  $scope.marker.options = {
									draggable: false,
									labelContent: $scope.supplier.name,
									labelAnchor: "80 120",
									labelClass: "marker-labels"
								  };
						 
						} // if($scope.supplier.lat!='' && $scope.supplier.lng!='')					
										
					
					LoaderService.hide();
                },
                function(data) {
                    if(debug) console.log('SupplierCtrl() Error' + data)
					LoaderService.hide();
                });
	else
		LoaderService.hide();
})
.controller('UsersCtrl', function($rootScope, $scope, $ionicPopover, LoaderService, Users) {	
	
	$scope.showPopSearch = false;
	
	$scope.openPopSearch = function() {
		if($scope.showPopSearch)
			$scope.showPopSearch = false;
		else
			$scope.showPopSearch = true;
	};

	LoaderService.show();
	$scope.rowsFound = -1;
	
	if(debug) console.log("UsersCtrl");
	
	Users.getAll()
			.then(function(users) {
				$scope.users = users;
				$scope.rowsFound = $scope.users.length;
				LoaderService.hide();
			},
			function(data) {
				if(debug) console.log('UsersCtrl() Error' + data)
				LoaderService.hide();
			});
})
.controller('UserCtrl', function($rootScope, $scope, $stateParams, LoaderService, Users, uiGmapGoogleMapApi) {
	
	LoaderService.show();
	$scope.rowsFound = -1;

	if(debug) console.log("UserCtrl id "+$stateParams.user_id);
	
	if($stateParams.user_id>0)
		Users.getItem($stateParams.user_id)
				.then(function(user) {
                    $scope.user = user;
					$scope.rowsFound = $scope.user.length;
					
					if(debug) console.log("UserCtrl user.lat "+$scope.user.Profile['profile.lat']+" user.lng "+$scope.user.Profile['profile.lng']);
					
						if($scope.user.Profile['profile.lat']!='' && $scope.user.Profile['profile.lng']!='') {
							
			
								  $scope.map = {
									center: {
									  latitude: $scope.user.Profile['profile.lat'],
									  longitude: $scope.user.Profile['profile.lng']
									},
									zoom: 17,
									pan: 1
								  };

								  $scope.marker = {
									id: 0,
									coords: {
									  latitude: $scope.user.Profile['profile.lat'],
									  longitude: $scope.user.Profile['profile.lng']
									}
								  }; 
								  
								  $scope.marker.options = {
									draggable: false,
									labelContent: $scope.user.name,
									labelAnchor: "80 120",
									labelClass: "marker-labels"
								  };
						 
						} 			
										
					
					LoaderService.hide();
                },
                function(data) {
                    if(debug) console.log('UserCtrl() Error' + data)
					LoaderService.hide();
                });
	else
		LoaderService.hide();
})
;