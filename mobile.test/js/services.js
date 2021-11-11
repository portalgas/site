angular.module('filters', []).filter('htmlToPlaintext', function() {
    return function(text) {
      return String(text).replace(/<[^>]+>/gm, '');
    }
  });
  
angular.module('portalgas.services', ['ngResource', 'http-auth-interceptor', 'ionic', 'filters'])
.factory('LoaderService', function($rootScope, $ionicLoading) {
  return {
        show : function() {
			// console.log('LoaderService.show()');
			
            $ionicLoading.show({

              content: '<i class="icon ion-looping"></i> Loading',
              animation: 'fade-in',
              showBackdrop: true,
              maxWidth: 200,
              showDelay: 10
            });
        },

        hide : function(){
            // console.log('LoaderService.hide()');
			
			$ionicLoading.hide();
        }
    }
})
.factory('Organization', function($resource, $http, $q, localStorageService) {
	return {
		 getAll: function() {
			var organizations = [];
	
			var res = $resource(urlRest+'/api/organizations.json',{}, {'query': { method: 'GET', params: {} }});	
		 	
			res.query(function (data) {
				angular.forEach(data.results, function(value, key) {
					// if(debug) console.log(key+" "+value.name);
					organizations.push(value);
				});
			});
								
			return organizations;
		},
		getItem: function(id) {
			  for (var i = 0; i < organizations.length; i++) {
				if (organizations[i].id === parseInt(id)) {
				  return organizations[i];
				}
			  }
			  return null;
		},	
		id: function() { 
			var organization_id = localStorageService.get('organization_id');
			if(organization_id==null) organization_id = 0;
			// if(debug) console.log("OrganizationService.id(), organization_id "+organization_id);
	
			return organization_id; 
		},
		label: function() { 
			var organization_label = localStorageService.get('organization_label');
			if(organization_label==null) organization_label = '';
			// if(debug) console.log("OrganizationService.label(), organization_label "+organization_label);
	
			return organization_label; 
		},
		storage: function(id) {
			if(id!=undefined && id!='') {
				var def = $q.defer();
				localStorageService.set('organization_id', id);

				if(debug) console.log("OrganizationService.storage() organization.organization_id "+localStorageService.get('organization_id'));
					
				$http({
					method: 'GET',
					url: urlRest+'/api/organizations/'+ id +'.json',
				})	  
				.success(function (data, status, headers, config) {
					angular.forEach(data.results, function(value, key) {
						localStorageService.set('organization_label', data.results['name']);
						if(debug) console.log("OrganizationService.storage() organization_label "+localStorageService.get('organization_label'));
					});
					def.resolve();
				})
				.error(function (data, status, headers, config) {
					def.reject("Errore... riprova!");
				});
				return def.promise;				
			}
		}	
	}
	
})
.factory('Deliveries', function ($http, $q, localStorageService, Organization) {
  
    return {    
      getAll: function () {
		var def = $q.defer();
		var deliveries = [];
	  
		var organization_id = Organization.id();
		if(debug) console.log("Deliveries.getAll - organization_id "+organization_id);
			
		$http({
			method: 'GET',
			url: urlRest+'/api/deliveries/'+organization_id+'.json',
		})	  
		.success(function (data, status, headers, config) {
			angular.forEach(data.results, function(value, key) {
				// if(debug) console.log(key+" "+value.luogo);
				deliveries.push(value);
			});
			def.resolve(deliveries);
		})
		.error(function (data, status, headers, config) {
			def.reject("Errore... riprova!");
		});
		return def.promise;
      },
      getItem: function (id) {
		var def = $q.defer();
		var delivery = [];
	  
		var organization_id = localStorageService.get('organization_id');
		if(debug) console.log("Deliveries. getItem - organization_id "+organization_id);

		$http({
			method: 'GET',
			url: urlRest+'/api/orders/view/'+organization_id+'/'+id+'.json',
		})	  
		.success(function (data, status, headers, config) {
			angular.forEach(data.results, function(value, key) {
				// if(debug) console.log(key+" "+value.data_inizio+" "+value.Supplier.name);
				delivery.push(value);
			});
			def.resolve(delivery);
		})
		.error(function (data, status, headers, config) {
			def.reject("Errore... riprova!");
		});
		
		return def.promise;
      }        
    };
})
.factory('DeliveriesCarts', function ($http, $q, localStorageService, Organization) {
  
    return {    
      getAll: function () {
		var def = $q.defer();
		var deliveries = [];
	  
		var organization_id = Organization.id();
		if(debug) console.log("DeliveriesCarts.getAll - organization_id "+organization_id);
			
		$http({
			method: 'GET',
			url: urlRest+'/api/deliveries_carts.json',
		})	  
		.success(function (data, status, headers, config) {
			angular.forEach(data.results, function(value, key) {
				// if(debug) console.log(key+" "+value.luogo);
				deliveries.push(value);
			});
			def.resolve(deliveries);
		})
		.error(function (data, status, headers, config) {
			def.reject("Errore... riprova!");
		});
		return def.promise;
      }    
	};
})
.factory('ArticlesOrders', function ($http, $q, localStorageService) {
  
    return {    
      getAll: function (order_id) {
		var def = $q.defer();
		var articlesOrders = [];
		var results = [];
		
		var organization_id = localStorageService.get('organization_id');
		if(debug) console.log("ArticlesOrders.getAll - organization_id "+organization_id);
		
		$http({
			method: 'GET',
			url: urlRest+'/api/articles_orders/view/'+organization_id+'/'+order_id+'.json',
		})	  
		.success(function (data, status, headers, config) {
			angular.forEach(data.results, function(value, key) {
				// if(debug) console.log(key+" "+value.name);
				articlesOrders.push(value);
			});
			
			results['isOrderToCart'] = data.isOrderToCart; // true se si possono effettuare acquisti
			results['articlesOrders'] = articlesOrders;
			
			def.resolve(results);
		})
		.error(function (data, status, headers, config) {
			def.reject("Errore... riprova!");
		});
		
		return def.promise;
      }		
    };
})
.factory('Orders', function ($http, $q, localStorageService) {
  
    return {    
      getAll: function () {
		var def = $q.defer();
		var orders = [];
		
		var organization_id = localStorageService.get('organization_id');
		if(debug) console.log("Orders.getAll - organization_id "+organization_id);
	  
		$http({
			method: 'GET',
			url: urlRest+'/api/orders/open/'+ organization_id +'.json',
		})	  
		.success(function (data, status, headers, config) {
			angular.forEach(data.results, function(value, key) {
				// if(debug) console.log(key+" "+value.name);
				orders.push(value);
			});
			def.resolve(orders);
		})
		.error(function (data, status, headers, config) {
			def.reject("Errore... riprova!");
		});
		
		return def.promise;
      }		
    };
})
.factory('Carts', function ($http, $q, localStorageService) {
  
    return {    
      getAll: function (delivery_id) {
		var def = $q.defer();
		var carts = [];
			  
		var organization_id = localStorageService.get('organization_id');
		if(debug) console.log("Carts.getAll - organization_id "+organization_id+" - delivery_id "+delivery_id);
		
		$http({
			method: 'GET',
			url: urlRest+'/api/carts/view/'+organization_id+'/'+delivery_id+'.json',
		})	  
		.success(function (data, status, headers, config) {
			angular.forEach(data.results, function(value, key) {
				// if(debug) console.log(key+" "+value.name);
				carts.push(value);
			});
			def.resolve(carts);
		})
		.error(function (data, status, headers, config) {
			def.reject("Errore... riprova!");
		});
		
		return def.promise;
      },
	  managementCart: function (order_id, article_organization_id, article_id, qta) {
		var def = $q.defer();
		var results = "";
			  
		var organization_id = localStorageService.get('organization_id');
		if(debug) console.log("Carts.managementCart - organization_id "+organization_id+" - order_id "+order_id+" - article_organization_id "+article_organization_id+" - article_id "+article_id+" - qta "+qta);
		
		$http({
			method: 'GET',
			url: urlRest+'/api/carts/management_cart/'+order_id+'/'+article_organization_id+'/'+article_id+'/'+qta+'.json',
		})	  
		.success(function (data, status, headers, config) {
			def.resolve(data.results);
		})
		.error(function (data, status, headers, config) {
			def.reject("Errore... riprova!");
		});
		
		return def.promise;
      },
    };
})
.factory('AuthenticationService', function($rootScope, $http, authService, localStorageService) {
  var service = {
    login: function(user) {
      
		var username = user.username;
		var password = user.password;
	
		if(debug) console.log('AuthenticationService::login() '+user.username);
			
		$http({
			method: 'POST',
			url: urlRest+'/api/users/login.json',
			data: 'username='+username+'&password='+password,
			headers: { 'Content-Type': 'application/x-www-form-urlencoded' },
			ignoreAuthModule: true
		})	  
        .success(function (data, status, headers, config) {
		  
			if(debug) console.log('AuthenticationService status '+status+' data.results.code '+data.results.code);
		
			if(data.results.code==200) {
				if(debug) console.log("AuthenticationService::login() authorizationToken "+data.results.token+' username '+data.results.name);

				$http.defaults.useXDomain = true;
				delete $http.defaults.headers.common.Authorization;		 
				$http.defaults.headers.common.Authorization = data.results.token;
				if(debug) console.log("http.defaults.headers.common.Authorization "+$http.defaults.headers.common.Authorization);

				localStorageService.set('authorizationToken', data.results.token);
				localStorageService.set('user_organization_id', data.results.organization_id);
				localStorageService.set('user_name', data.results.name);
				localStorageService.set('organization_id', data.results.organization_id);
	
				authService.loginConfirmed(data, function(config) {  // Step 2 & 3
				  config.headers.Authorization = data.results.token;
				  if(debug) console.log("AuthenticationService::login() persisto in localStorageService");
				  return config;
				});				
			}
			else {
				/* data.results.code==500 */
				$rootScope.$broadcast('event:auth-login-failed', status);
			}
		
      })
      .error(function (data, status, headers, config) {
        $rootScope.$broadcast('event:auth-login-failed', status);
      });
    },
    logout: function(user) {
		
	  localStorageService.remove('authorizationToken');
	  localStorageService.remove('user_organization_id');
	  localStorageService.remove('user_name');
	  delete $http.defaults.headers.common.Authorization;
	  $rootScope.$broadcast('event:auth-logout-complete');
		
      /*$http.post('https://logout', {}, { ignoreAuthModule: true })
      .finally(function(data) {
        localStorageService.remove('authorizationToken');
        delete $http.defaults.headers.common.Authorization;
        $rootScope.$broadcast('event:auth-logout-complete');
      });
	*/	  
    },	
    loginCancelled: function() {
      authService.loginCancelled();
    }
  };
  return service;
})
.factory('Suppliers', function($resource, $http, $q, localStorageService) {
	return {
      getAll: function () {
		var def = $q.defer();
		var suppliers = [];
	  
		if(debug) console.log("Suppliers.getAll");
			
		$http({
			method: 'GET',
			url: urlRest+'/api/suppliers.json',
		})	  
		.success(function (data, status, headers, config) {
			angular.forEach(data.results, function(value, key) {
				// if(debug) console.log(key+" "+value.luogo);
				suppliers.push(value);
			});
			def.resolve(suppliers);
		})
		.error(function (data, status, headers, config) {
			def.reject("Errore... riprova!");
		});
		return def.promise;
	},
	getItem: function(id) {
		var def = $q.defer();
		var supplier = [];
	  
		if(debug) console.log("Supplier.getItem - id "+id);

		$http({
			method: 'GET',
			url: urlRest+'/api/suppliers/'+id+'.json',
		})	  
		.success(function (data, status, headers, config) {
			supplier  = data.results;
			def.resolve(supplier);
		})
		.error(function (data, status, headers, config) {
			def.reject("Errore... riprova!");
		});
		
		return def.promise;		
	}
   }	
})
.factory('Users', function($resource, $http, $q, localStorageService) {
	return {
		  getAll: function () {
			var def = $q.defer();
			var users = [];
		  
			if(debug) console.log("Users.getAll");
				
			$http({
				method: 'GET',
				url: urlRest+'/api/users.json',
			})	  
			.success(function (data, status, headers, config) {
				angular.forEach(data.results, function(value, key) {
					// if(debug) console.log(key+" "+value.username);
					users.push(value);
				});
				def.resolve(users);
			})
			.error(function (data, status, headers, config) {
				def.reject("Errore... riprova!");
			});
			return def.promise;
		},
		getItem: function(id) {
			var def = $q.defer();
			var user = [];
		  
			if(debug) console.log("User.getItem - id "+id);

			$http({
				method: 'GET',
				url: urlRest+'/api/users/'+id+'.json',
			})	  
			.success(function (data, status, headers, config) {
				user  = data.results;
				def.resolve(user);
			})
			.error(function (data, status, headers, config) {
				def.reject("Errore... riprova!");
			});
			
			return def.promise;		
	}
	}
});