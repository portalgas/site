<?php
/**
 * This is core configuration file.
 *
 * Use it to configure core behavior of Cake.
 *
 * CakePHP(tm) : Rapid Development Framework (http://cakephp.org)
 * Copyright (c) Cake Software Foundation, Inc. (http://cakefoundation.org)
 *
 * Licensed under The MIT License
 * For full copyright and license information, please see the LICENSE.txt
 * Redistributions of files must retain the above copyright notice.
 *
 * @copyright     Copyright (c) Cake Software Foundation, Inc. (http://cakefoundation.org)
 * @link          http://cakephp.org CakePHP(tm) Project
 * @package       app.Config
 * @since         CakePHP(tm) v 0.2.9
 * @license       http://www.opensource.org/licenses/mit-license.php MIT License
 */

/**
 * CakePHP Debug Level:
 *
 * Production Mode:
 * 	0: No error messages, errors, or warnings shown. Flash messages redirect.
 *
 * Development Mode:
 * 	1: Errors and warnings shown, model caches refreshed, flash messages halted.
 * 	2: As in 1, but also with full debug messages and SQL output.
 *
 * In production mode, flash messages redirect after a time interval.
 * In development mode, you need to click the flash message to continue.
 *
 * Configure::write('debug', 2);
 */
	
/**
 * Configure the Error handler used to handle errors for your application.  By default
 * ErrorHandler::handleError() is used.  It will display errors using Debugger, when debug > 0
 * and log errors with CakeLog when debug = 0.
 *
 * Options:
 *
 * - `handler` - callback - The callback to handle errors. You can set this to any callable type,
 *    including anonymous functions.
 *   Make sure you add App::uses('MyHandler', 'Error'); when using a custom handler class
 * - `level` - integer - The level of errors you are interested in capturing.
 * - `trace` - boolean - Include stack traces for errors in log files.
 *
 * @see ErrorHandler for more information on error handling and configuration.
 */
	Configure::write('Error', array(
		'handler' => 'ErrorHandler::handleError',
		//'level' => E_ALL & ~E_DEPRECATED,
		'level' => E_ALL & ~E_NOTICE & ~E_STRICT & ~E_DEPRECATED,
		'trace' => true
	));

/**
 * Configure the Exception handler used for uncaught exceptions.  By default,
 * ErrorHandler::handleException() is used. It will display a HTML page for the exception, and
 * while debug > 0, framework errors like Missing Controller will be displayed.  When debug = 0,
 * framework errors will be coerced into generic HTTP errors.
 *
 * Options:
 *
 * - `handler` - callback - The callback to handle exceptions. You can set this to any callback type,
 *   including anonymous functions.
 *   Make sure you add App::uses('MyHandler', 'Error'); when using a custom handler class
 * - `renderer` - string - The class responsible for rendering uncaught exceptions.  If you choose a custom class you
 *   should place the file for that class in app/Lib/Error. This class needs to implement a render method.
 * - `log` - boolean - Should Exceptions be logged?
 * - `skipLog` - array - list of exceptions to skip for logging. Exceptions that
 *   extend one of the listed exceptions will also be skipped for logging.
 *   Example: `'skipLog' => array('NotFoundException', 'UnauthorizedException')`
 *
 * @see ErrorHandler for more information on exception handling and configuration.
 */
	Configure::write('Exception', array(
		'handler' => 'ErrorHandler::handleException',
		'renderer' => 'ExceptionRenderer',
		'log' => true
	));

/**
 * Application wide charset encoding
 */
	Configure::write('App.encoding', 'UTF-8');

/**
 * To configure CakePHP *not* to use mod_rewrite and to
 * use CakePHP pretty URLs, remove these .htaccess
 * files:
 *
 * /.htaccess
 * /app/.htaccess
 * /app/webroot/.htaccess
 *
 * And uncomment the App.baseUrl below. But keep in mind
 * that plugin assets such as images, CSS and JavaScript files
 * will not work without URL rewriting!
 * To work around this issue you should either symlink or copy
 * the plugin assets into you app's webroot directory. This is
 * recommended even when you are using mod_rewrite. Handling static
 * assets through the Dispatcher is incredibly inefficient and
 * included primarily as a development convenience - and
 * thus not recommended for production applications.
 */
	//Configure::write('App.component.base', env('SCRIPT_NAME'));

/**
 * To configure CakePHP to use a particular domain URL
 * for any URL generation inside the application, set the following
 * configuration variable to the http(s) address to your domain. This
 * will override the automatic detection of full base URL and can be
 * useful when generating links from the CLI (e.g. sending emails)
 */
	//Configure::write('App.fullBaseUrl', 'http://example.com');

/**
 * Web path to the public images directory under webroot.
 * If not set defaults to 'img/'
 */
	//Configure::write('App.imageBaseUrl', 'img/');

/**
 * Web path to the CSS files directory under webroot.
 * If not set defaults to 'css/'
 */
	//Configure::write('App.cssBaseUrl', 'css/');

/**
 * Web path to the js files directory under webroot.
 * If not set defaults to 'js/'
 */
	//Configure::write('App.jsBaseUrl', 'js/');

/**
 * Uncomment the define below to use CakePHP prefix routes.
 *
 * The value of the define determines the names of the routes
 * and their associated controller actions:
 *
 * Set to an array of prefixes you want to use in your application. Use for
 * admin or other prefixed routes.
 *
 * 	Routing.prefixes = array('admin', 'manager');
 *
 * Enables:
 *	`admin_index()` and `/admin/controller/index`
 *	`manager_index()` and `/manager/controller/index`
 *
 */
	Configure::write('Routing.prefixes', array('admin'));

/**
 * Turn off all caching application-wide.
 *
 */
	//Configure::write('Cache.disable', true);

/**
 * Enable cache checking.
 *
 * If set to true, for view caching you must still use the controller
 * public $cacheAction inside your controllers to define caching settings.
 * You can either set it controller-wide by setting public $cacheAction = true,
 * or in each action using $this->cacheAction = true.
 *
 */
	//Configure::write('Cache.check', true);

/**
 * Enable cache view prefixes.
 *
 * If set it will be prepended to the cache name for view file caching. This is
 * helpful if you deploy the same application via multiple subdomains and languages,
 * for instance. Each version can then have its own view cache namespace.
 * Note: The final cache file name will then be `prefix_cachefilename`.
 */
	//Configure::write('Cache.viewPrefix', 'prefix');

/**
 * Session configuration.
 *
 * Contains an array of settings to use for session configuration. The defaults key is
 * used to define a default preset to use for sessions, any settings declared here will override
 * the settings of the default config.
 *
 * ## Options
 *
 * - `Session.cookie` - The name of the cookie to use. Defaults to 'CAKEPHP'
 * - `Session.timeout` - The number of minutes you want sessions to live for. This timeout is handled by CakePHP
 * - `Session.cookieTimeout` - The number of minutes you want session cookies to live for.
 * - `Session.checkAgent` - Do you want the user agent to be checked when starting sessions? You might want to set the
 *    value to false, when dealing with older versions of IE, Chrome Frame or certain web-browsing devices and AJAX
 * - `Session.defaults` - The default configuration set to use as a basis for your session.
 *    There are four builtins: php, cake, cache, database.
 * - `Session.handler` - Can be used to enable a custom session handler. Expects an array of callables,
 *    that can be used with `session_save_handler`.  Using this option will automatically add `session.save_handler`
 *    to the ini array.
 * - `Session.autoRegenerate` - Enabling this setting, turns on automatic renewal of sessions, and
 *    sessionids that change frequently. See CakeSession::$requestCountdown.
 * - `Session.ini` - An associative array of additional ini values to set.
 *
 * The built in defaults are:
 *
 * - 'php' - Uses settings defined in your php.ini.
 * - 'cake' - Saves session files in CakePHP's /tmp directory.
 * - 'database' - Uses CakePHP's database sessions.
 * - 'cache' - Use the Cache class to save sessions.
 *
 * To define a custom session handler, save it at /app/Model/Datasource/Session/<name>.php.
 * Make sure the class implements `CakeSessionHandlerInterface` and set Session.handler to <name>
 *
 * To use database sessions, run the app/Config/Schema/sessions.php schema using
 * the cake shell command: cake schema create Sessions
 *
 */
	Configure::write('Session', array(
		'defaults' => 'php'
	));

/**
 * A random string used in security hashing methods.
 */
	Configure::write('Security.salt', 'FrAs73!MO#');

/**
 * A random numeric string (digits only) used to encrypt/decrypt strings.
 */
	Configure::write('Security.cipherSeed', '17931798200021002810125');

/**
 * Apply timestamps with the last modified time to static assets (js, css, images).
 * Will append a querystring parameter containing the time the file was modified. This is
 * useful for invalidating browser caches.
 *
 * Set to `true` to apply timestamps when debug > 0. Set to 'force' to always enable
 * timestamping regardless of debug value.
 */
	//Configure::write('Asset.timestamp', true);

/**
 * Compress CSS output by removing comments, whitespace, repeating tags, etc.
 * This requires a/var/cache directory to be writable by the web server for caching.
 * and /vendors/csspp/csspp.php
 *
 * To use, prefix the CSS link URL with '/ccss/' instead of '/css/' or use HtmlHelper::css().
 */
	//Configure::write('Asset.filter.css', 'css.php');

/**
 * Plug in your own custom JavaScript compressor by dropping a script in your webroot to handle the
 * output, and setting the config below to the name of the script.
 *
 * To use, prefix your JavaScript link URLs with '/cjs/' instead of '/js/' or use JavaScriptHelper::link().
 */
	//Configure::write('Asset.filter.js', 'custom_javascript_output_filter.php');

/**
 * The classname and database used in CakePHP's
 * access control lists.
 */
//	Configure::write('Acl.classname', 'DbAcl');
//	Configure::write('Acl.database', 'default');

/**
 * Uncomment this line and correct your server timezone to fix 
 * any date & time related errors.
 */
	//date_default_timezone_set('UTC');
	date_default_timezone_set('Europe/Rome');

/**
 * `Config.timezone` is available in which you can set users' timezone string.
 * If a method of CakeTime class is called with $timezone parameter as null and `Config.timezone` is set,
 * then the value of `Config.timezone` will be used. This feature allows you to set users' timezone just
 * once instead of passing it each time in function calls.
 */
	Configure::write('Config.timezone', 'Europe/Rome');

/**
 *
 * Cache Engine Configuration
 * Default settings provided below
 *
 * File storage engine.
 *
 * 	 Cache::config('default', array(
 *		'engine' => 'File', //[required]
 *		'duration' => 3600, //[optional]
 *		'probability' => 100, //[optional]
 * 		'path' => CACHE, //[optional] use system tmp directory - remember to use absolute path
 * 		'prefix' => 'cake_', //[optional]  prefix every cache file with this string
 * 		'lock' => false, //[optional]  use file locking
 * 		'serialize' => true, //[optional]
 * 		'mask' => 0664, //[optional]
 *	));
 *
 * APC (http://pecl.php.net/package/APC)
 *
 * 	 Cache::config('default', array(
 *		'engine' => 'Apc', //[required]
 *		'duration' => 3600, //[optional]
 *		'probability' => 100, //[optional]
 * 		'prefix' => Inflector::slug(APP_DIR) . '_', //[optional]  prefix every cache file with this string
 *	));
 *
 * Xcache (http://xcache.lighttpd.net/)
 *
 * 	 Cache::config('default', array(
 *		'engine' => 'Xcache', //[required]
 *		'duration' => 3600, //[optional]
 *		'probability' => 100, //[optional]
 *		'prefix' => Inflector::slug(APP_DIR) . '_', //[optional] prefix every cache file with this string
 *		'user' => 'user', //user from xcache.admin.user settings
 *		'password' => 'password', //plaintext password (xcache.admin.pass)
 *	));
 *
 * Memcached (http://www.danga.com/memcached/)
 *
 * Uses the memcached extension. See http://php.net/memcached
 *
 * 	 Cache::config('default', array(
 *		'engine' => 'Memcached', //[required]
 *		'duration' => 3600, //[optional]
 *		'probability' => 100, //[optional]
 * 		'prefix' => Inflector::slug(APP_DIR) . '_', //[optional]  prefix every cache file with this string
 * 		'servers' => array(
 * 			'127.0.0.1:11211' // localhost, default port 11211
 * 		), //[optional]
 * 		'persistent' => 'my_connection', // [optional] The name of the persistent connection.
 * 		'compress' => false, // [optional] compress data in Memcached (slower, but uses less memory)
 *	));
 *
 *  Wincache (http://php.net/wincache)
 *
 * 	 Cache::config('default', array(
 *		'engine' => 'Wincache', //[required]
 *		'duration' => 3600, //[optional]
 *		'probability' => 100, //[optional]
 *		'prefix' => Inflector::slug(APP_DIR) . '_', //[optional]  prefix every cache file with this string
 *	));
 */

Configure::write('EmailConfig', 'localhost');
Configure::write('SOC.name', 'PortAlGas');
Configure::write('SOC.descrizione', "Gestionale per Gruppi di Acquisto Solidale");
Configure::write('SOC.site', 'www.portalgas.it');
Configure::write('SOC.mail', 'info@portalgas.it');
Configure::write('SOC.mail-assistenza', 'info@portalgas.it'); // utilizzato in default.po

Configure::write('Filter.prefix', 'Filter');        // in database.php (cron per il dump delle tabelle)
Configure::write('DB.prefix', '');        // in database.php (cron per il dump delle tabelle)
Configure::write('DB.portalPrefix', '');  // (cron per il dump delle tabelle)
Configure::write('DB.tableJoomlaWithPassword', 'jos_users'); // nome della tabella per la migrazione degli utenti, contiente la password
Configure::write('DB.field.date.empty', '0000-00-00');

/*
 * requests_payments.stato
*/
Configure::write('DAPAGARE', 'DAPAGARE');
Configure::write('SOLLECITO1', 'SOLLECITO1');
Configure::write('SOLLECITO2', 'SOLLECITO2');
Configure::write('SOSPESO', 'SOSPESO');
Configure::write('PAGATO', 'PAGATO');

Configure::write('Config.language', 'ita');
Configure::write('traslateEnum',array('Y'=>'si',
									  'N'=>'no',
									  'NO'=>'No',
									  'T'=>'temporaneo',
									  'PG'=>'pagina',
									  'MESSAGE'=>'messaggio',
									  'NOTICE'=>'avviso',
									  'ALERT'=>'attenzione',
									  'PZ'=>'Pz',
									  'GR'=>'Gr',
									  'HG'=>'Hg',
									  'KG'=>'Kg',
									  'ML'=>'ML',
									  'DL'=>'DL',
									  'LT'=>'Lt',
									  'DAY'=>'Giorno',
									  'WEEK'=>'Settimana',
									  'MONTH'=>'Mese',
									  'DAYS'=>'Giorni',
									  'WEEKS'=>'Settimane',
									  'MONTHS'=>'Mesi',
									  'FIRST'=>'Primo',
									  'SECOND'=>'Secondo',
									  'THIRD'=>'Terzo',
									  'FOURTH'=>'Quarto',
									  'LAST'=>'Ultimo',
									  'SUN' => 'Domenica',
									  'MON' => 'Lunedì',
									  'TUE' => 'Martedì',
									  'WED' => 'Mercoledì',
									  'THU' => 'Giovedì',
									  'FRI' => 'Venerdì',
									  'SAT' => 'Sabato',
									  'OPEN'=>'Aperto',
									  'WAIT'=>'In attesa',
									  'CLOSE'=>'Chiuso',
									  'LOCK'=>'Bloccato',
									  'QTAMAXORDER'=>'Quantità massima raggiunta',
									  'REQUEST_PAYMENT_STATO_ELABORAZIONE_WAIT'=>'In lavorazione',
									  'REQUEST_PAYMENT_STATO_ELABORAZIONE_OPEN'=>'Aperta per richiedere il pagamento',
									  'REQUEST_PAYMENT_STATO_ELABORAZIONE_CLOSE'=>'Chiuso',
									  'REFERENTE'=>'Referente',
									  'COREFERENTE'=>'Co-referente',
									  'TESORIERE'=>'Tesoriere',
									  'DEFINED' => 'Da definire',
									  'CONTANTI' => 'Contanti',
									  'BANCOMAT' => 'Bancomat',
									  'POS' => 'P.O.S.',
									  'BONIFICO' => 'Bonifico',
									  'DAPAGARE' => 'Da pagare',
									  'SOLLECITO1' => 'Sollecito 1',
									  'SOLLECITO2' => 'Sollecito 2',
									  'SOSPESO' => 'Sospeso',
									  'PAGATO' => 'Pagato',
									  'USERS' => 'Utenti',
									  'USERS_CART' => 'Utenti che hanno acquistato',
									  'SUPPLIERS' => 'Produttori',
									  'REFERENTI' => 'Referenti',
									  'ALL' => 'Tutti',
									  'SOME' => 'Alcuni',
									  'ERROR_EMPTY' => 'Campo obbligatorio',
									  'ERROR_FORMAT' => 'Formato errato',
									  'ERROR_NUM_MAX_ZERO' => 'Num maggiore di zero',
									  'ERROR_FORMAT_ARRAY' => 'Valore non permesso',
									  'ERROR_FORMAT_EMAIL' => 'Email non corretta',
									  'ProdGasSupplierSUPPLIER' => 'Il produttore',
									  'ProdGasSupplierREFERENT' => 'Il referente del G.A.S.'));
												
Configure::write('templates', array('1' => 'Template UNO: pagamento POST consegna - Tesoriere', 
									'2' => 'Template DUE: pagamento ON consegna - Cassiere', 
									'3' => 'Template TRE: pagamento ON consegna - Gestione arrivo merce - Cassiere', 
									'4' => 'Template QUATTRO: pagamento ON consegna e POST consegna - Gestione arrivo merce - Cassiere e Tesoriere'));

Configure::write('option.empty', '----------');
Configure::write('separatoreDecimali', ',');
Configure::write('separatoreMigliaia', '.');
Configure::write('bio', 'Agricoltura biologica o biodinamica');

/* j_usergroups , in menu sono ripetuti */
Configure::write('group_id_root',8);
Configure::write('group_id_root_supplier',24);
Configure::write('group_id_manager',10);  
Configure::write('group_id_manager_delivery',20);
// Per raggruppare utenti per uso statistico
Configure::write('group_id_generic',60);

Configure::write('group_id_referent',18);
Configure::write('group_id_super_referent',19);

// referente cassa (pagamento degli utenti alla consegna)
Configure::write('group_id_cassiere',21);
Configure::write('group_id_referent_cassiere',41);

Configure::write('group_id_manager_des',36);
Configure::write('group_id_super_referent_des',38);
Configure::write('group_id_referent_des',37);
Configure::write('group_id_titolare_des_supplier',39);
Configure::write('group_id_des_supplier_all_gas', 51);

/*
 * referente tesoriere (pagamento con richiesta degli utenti dopo consegna)
 * 		gestisce anche il pagamento del suo produttore
 */ 
Configure::write('group_id_referent_tesoriere',23); 

// tesoriere (pagamento ai fornitori)
Configure::write('group_id_tesoriere',11);
Configure::write('group_id_storeroom',9); 
Configure::write('group_id_user',2);  

// prodGasSupplier
Configure::write('prod_gas_supplier_manager',62);
// calendar events gasEvents
Configure::write('group_id_events',65);
// system info@gas.portalgas.it dispensa@gas.portalgas.it 
Configure::write('group_system',66);
	
Configure::write('orderUser', 'User.name');

Configure::write('cart_msg_stato_N', "L'articolo %s non e' acquistabile, lo stato e' chiuso!");
Configure::write('cart_msg_block_stop',  "L'articolo %s e' bloccato, non si possono effettuare successivi acquisti.");
Configure::write('cart_msg_qtamax_order_stop',  "Per l'articolo %s si e' raggiunta la quantita' massima di %s pezzi!");
Configure::write('cart_msg_qtamax_order', "Per l'articolo %s si e' raggiunta la quantita' massima di %s pezzi! Hai potuto ordinarne solo %s pezzi.");
Configure::write('cart_msg_qtamax', "Per l'articolo %s la quantita' massima per ogni gasista e' %s! Hai indicato %s");
Configure::write('cart_msg_qtamin', "Per l'articolo %s la quantita' minima e' %s! Hai indicato %s");
Configure::write('label_payment_pos', "+ %s commissione POS");

Configure::write('sys_function_not_implement', "Funzione non ancora implementata.");
Configure::write('sys_report_not_implement', "Estrazione del file non ancora implementata.");
Configure::write('sys_send_mail_error', 'N');

Configure::write('routes_default',array('controller' => 'Pages', 'action' => 'home','admin' => true));
Configure::write('routes_msg_stop',array('controller' => 'Pages', 'action' => 'msg_stop','admin' => false));
Configure::write('routes_msg_question',array('controller' => 'Pages', 'action' => 'msg_question','admin' => false));
Configure::write('routes_msg_exclamation',array('controller' => 'Pages', 'action' => 'msg_exclamation','admin' => false));
Configure::write('routes_msg_frontend_cart_preview',array('controller' => 'Pages', 'action' => 'msg_frontend_cart_preview','admin' => false));
Configure::write('routes_msg_frontend_prod_user_group_not',array('controller' => 'Pages', 'action' => 'msg_frontend_prod_user_group_not','admin' => false));

Configure::write('doc_export_author', 'PortAlGas');
Configure::write('doc_export_title', 'PortAlGas');
Configure::write('doc_export_subject', 'PortAlGas');
Configure::write('doc_export_keywork', 'PortAlGas');
Configure::write('doc_export_logo', '150h50.png');  // in xtcpdf.php $logo = Configure::read('App.root').DS.Configure::read('App.img.loghi').DS.$organization['Organization']['id'].Configure::read('doc_export_logo');

// https://code.google.com/apis/console
Configure::write('GoogleClient_id',' ');
Configure::write('GoogleClient_secret',' '); 
Configure::write('GoogleEmail', '');  
Configure::write('GoogleEmailGmail','');
Configure::write('GoogleApi_key',''); 
Configure::write('GoogleService_client_id','');
Configure::write('GoogleService_email',''); 
Configure::write('GooglePrivateKeyLocation','');  

Configure::write('OrderNotaMaxLen', 50);
Configure::write('SupplierArticleMinLen', 250);
Configure::write('SupplierArticleIntroMinLen', 251);

Configure::write('LatLngNotFound', '0.0');
Configure::write('HtmlSelectWithSearchNum', 3);     // se la <select> ha + ti options attivo $(chosen-select)
Configure::write('LatLngDistanceAbsolute', '1000'); // per calcolare la distanza in %
Configure::write('JCategoryIdRoot', 30);
Configure::write('TabsDeliveriesSmallLabel', 5);  // in consegne se ho + di enne tabs compare la data ridotta
Configure::write('GGOrganizationsPayment', 60);   // dopo quanto far vedere i dati del pagamento
Configure::write('GGinMenoPerEstrarreDeliveriesInTabs', 5);
Configure::write('GGinMenoPerEstrarreDeliveriesCartInTabs', 35);
Configure::write('GGMailToAlertOrderOpen', 0);   // perche' eseguito dopo mezzanotte: oggi si aprono
Configure::write('GGMailToAlertOrderClose', 2);  // perche' eseguito dopo mezzanotte: tra n+1 si chiuderanno
Configure::write('GGMailToAlertDeliveryOn', 1);  // perche' eseguito dopo mezzanotte: tra n+1 c'e' la consegna
Configure::write('GGEventGCalendarToAlertDeliveryOn', 2);  // perche' eseguito dopo mezzanotte: tra n+1 c'e' la consegna
Configure::write('GGAlertCassiereDeliveriesToClose', 5);  // dopo quanti GG avvisare il Cassiere che ci sono consegne da chiudere
Configure::write('GGOrderCloseNext', -3);        // giorni che mancano alla chiusura dell'ordine
Configure::write('GGDeliveryCloseNext', -3);     // giorni che mancano alla chiusura della consegna (non utilizzato)
Configure::write('GGDeliveryCassiereClose', 35);     // dopo quanti giorni il Cron::deliveriesCassiereClose() porta le consegne a CLOSE
Configure::write('GGDesOrdersOld', 30);       // gg dopo la DesOrsers.data_fine_max per considerare un DesOrders vecchio
Configure::write('GGArchiveStatics', 35);     // dopo quanti giorni il Cron::archiveStatistics() cancella le consegne / richieste di pagamento
Configure::write('GGDeleteLogs', 6);          // dopo quanti giorni il Cron::filesystemLogDelete() cancella i log dei cron
Configure::write('GGDeleteBackup', 5);        // dopo quanti giorni il Cron::filesystemLogDelete() cancella i backup del codice
Configure::write('GGDeleteDump', 5);         // dopo quanti giorni il Cron::filesystemLogDelete() cancella i dump del DATABASE
Configure::write('CartLimitPreview', 5);  // numero di ultimi articoli acquistati 
Configure::write('ArticlesOrderToTypeDrawComplete', 100);  // numero articoli in un ordine per la modalita' COMPLETE
Configure::write('ArticlesOrderWithImgToTypeDrawComplete', 80);  // % di articoli con IMG in un ordine per la modalita' COMPLETE: se - del 80% non ha img e' SIMPLE 
Configure::write('DeliveryToDefinedDate', '2025-01-01');
Configure::write('DeliveryToDefinedLabel', 'Da definire');
Configure::write('LayoutBootstrap', true);

Configure::write('urlFrontEndToRewriteCakeRequest',array(
		array('controller'=>'Deliveries','action'=>'tabsAjaxEcommDeliveries','admin'=>false),
		array('controller'=>'Deliveries','action'=>'tabsAjaxEcommArticlesOrder','admin'=>false),
		array('controller'=>'Deliveries','action'=>'tabsAjaxEcommCartsValidation','admin'=>false),
		array('controller'=>'Deliveries','action'=>'tabsAjaxUserCartDeliveries','admin'=>false),
		array('controller'=>'Deliveries','action'=>'tabsAjaxDeliveries','admin'=>false),
		array('controller'=>'Deliveries','action'=>'calendar_view','admin'=>false),
		array('controller'=>'Storerooms','action'=>'userToStoreroom','admin'=>false),  // chiamate da storerooms/index in ajax
		array('controller'=>'Storerooms','action'=>'storeroomToUser','admin'=>false),  // chiamate da storerooms/index in ajax
		array('controller'=>'Storerooms','action'=>'export','admin'=>false),  
		array('controller'=>'PopUp','action'=>'delivery_info','admin'=>false),
		array('controller'=>'PopUp','action'=>'order_mail_open_testo','admin'=>false),
		array('controller'=>'Carts','action'=>'cart_to_user_preview','admin'=>false),
		array('controller'=>'BookmarksArticles','action'=>'add','admin'=>false),  // chiamate da bookmarks_articles/index in ajax
		array('controller'=>'BookmarksArticles','action'=>'index_articles','admin'=>false), 
		array('controller'=>'BookmarksArticles','action'=>'managementCartSimple','admin'=>false), 
		array('controller'=>'ProdCarts','action'=>'cart_to_user_preview','admin'=>false),
		array('controller'=>'Ajax','action'=>'autoCompleteArticlesName','admin'=>false),
		array('controller'=>'Ajax','action'=>'view_articles','admin'=>false),
		array('controller'=>'Ajax','action'=>'view_articles_order','admin'=>false),
		array('controller'=>'Ajax','action'=>'view_articles_order_no_img','admin'=>false),
		array('controller'=>'Ajax','action'=>'view_prod_deliveries_articles','admin'=>false),
		array('controller'=>'Ajax','action'=>'view_prod_deliveries_articles_no_img','admin'=>false),
		array('controller'=>'Ajax','action'=>'modules_suppliers_organization_details','admin'=>false),
		array('controller'=>'Ajax','action'=>'modules_supplier_details','admin'=>false),
		array('controller'=>'Ajax','action'=>'modules_supplier_articles','admin'=>false),
		array('controller'=>'Ajax','action'=>'view_cashes_histories','admin'=>false),
		array('controller'=>'AjaxGasCarts','action'=>'managementCartSimple','admin'=>false),
		array('controller'=>'AjaxGasCarts','action'=>'managementCartValidationSimple','admin'=>false),
		array('controller'=>'AjaxGasCarts','action'=>'managementCartComplete','admin'=>false),
		array('controller'=>'AjaxProdCarts','action'=>'managementCartSimple','admin'=>false),
		array('controller'=>'AjaxProdCarts','action'=>'managementCartComplete','admin'=>false),
		array('controller'=>'ExportDocs','action'=>'userCart','admin'=>false),
		array('controller'=>'ExportDocs','action'=>'usersDelivery','admin'=>false),
		array('controller'=>'ExportDocs','action'=>'articlesSupplierOrganization','admin'=>false),
		array('controller'=>'ExportDocs','action'=>'articlesOrders','admin'=>false),
		array('controller'=>'ExportDocs','action'=>'suppliersOrganizations','admin'=>false),
		array('controller'=>'ExportDocs','action'=>'usersData','admin'=>false),
		array('controller'=>'ExportDocs','action'=>'referentsData','admin'=>false),
		array('controller'=>'ExportDocs','action'=>'userRequestPayment','admin'=>false),
		array('controller'=>'ExportDocs','action'=>'articlesSupplierDes','admin'=>false),
		array('controller'=>'WsExportDocs','action'=>'exportToReferent','admin'=>false),
		array('controller'=>'Rests','action'=>'autentication','admin'=>false),
		array('controller'=>'Rests','action'=>'organizations','admin'=>false),
		array('controller'=>'Rests','action'=>'organization','admin'=>false),
		array('controller'=>'Rests','action'=>'deliveries','admin'=>false),
		array('controller'=>'Rests','action'=>'orders','admin'=>false),
		array('controller'=>'Rests','action'=>'articles_orders','admin'=>false),
		array('controller'=>'Pages','action'=>'msg_stop','admin'=>false),
		array('controller'=>'Pages','action'=>'msg_question','admin'=>false),
		array('controller'=>'Pages','action'=>'msg_exclamation','admin'=>false),
		array('controller'=>'Pages','action'=>'msg_frontend_cart_preview','admin'=>false),
		array('controller'=>'Pages','action'=>'msg_frontend_prod_user_group_not','admin'=>false),
		array('controller'=>'Pages','action'=>'msg_frontend_prod_delivery_not','admin'=>false),
		array('controller'=>'Deliveries','action'=>'tabs','admin'=>false,'SEO'=>'consegne-gas-'),
		array('controller'=>'Deliveries','action'=>'tabsUserCart','admin'=>false,'SEO'=>'carrello-gas-'),
		array('controller'=>'Deliveries','action'=>'tabsUserCartPreview','admin'=>false,'SEO'=>'preview-carrello-gas-'),
		array('controller'=>'Storerooms','action'=>'index','admin'=>false,'SEO'=>'dispensa-gas-'),
        array('controller'=>'ProdDeliveries','action'=>'ecomm','admin'=>false,'SEO'=>'fai-la-spesa-prod-'),
		array('controller'=>'Deliveries','action'=>'tabsEcomm','admin'=>false,'SEO'=>'fai-la-spesa-gas-'),
		array('controller'=>'Deliveries','action'=>'tabsEcommTabOrdersDelivery','admin'=>false),
		array('controller'=>'Deliveries','action'=>'tabsEcommTabAllOrders','admin'=>false),
		array('controller'=>'Pages','action'=>'exportDocsUserIntro','admin'=>false,'SEO'=>'stampe-gas-'),
		array('controller'=>'Users','action'=>'profile','admin'=>false,'SEO'=>'my-profile'),
		array('controller'=>'Users','action'=>'gmaps','admin'=>false,'SEO'=>'gmaps'),
		array('controller'=>'Organizations','action'=>'gmaps','admin'=>false,'SEO'=>'gmaps-gas'),
		array('controller'=>'Suppliers','action'=>'gmaps','admin'=>false,'SEO'=>'gmaps-produttori'),
		array('controller'=>'Users','action'=>'bookmarks_mails','admin'=>false,'SEO'=>'bookmarks-mails'),
		array('controller'=>'Users','action'=>'bookmarks_mails_update','admin'=>false),
		array('controller'=>'BookmarksArticles','action'=>'index','admin'=>false,'SEO'=>'bookmarks-articles'),
		array('controller'=>'Events','action'=>'index','admin'=>false,'SEO'=>'events'),	
		array('controller'=>'PdfCarts','action'=>'index','admin'=>false,'SEO'=>'carts-history'),	
	//	ecommprod  
		));

Configure::write('Mail.body_header', "Salve %s, <br />\n");
Configure::write('Mail.body_footer_simple', "\nhttp://".Configure::read('SOC.site')." <br />\n%s");
Configure::write('Mail.body_footer', "\nhttp://".Configure::read('SOC.site'));
Configure::write('Mail.body_footer_no_reply', "Non rispondere a questo messaggio in quanto generato automaticamente.<br /> \n <br /> \nhttp://".Configure::read('SOC.site')."<br />\n%s");
Configure::write('Mail.body_footer_no_reply_simple', "Non rispondere a questo messaggio in quanto generato automaticamente.<br /> \n<br />\n%s");
Configure::write('Mail.no_reply_mail', "no-reply@portalgas.it");
Configure::write('Mail.no_reply_name', "Non rispondere a questa mail");
Configure::write('Mail.logo', '150h50.png');  
Configure::write('Mail.body_carts_validation', "Per la consegna di <b>%s</b> verr&agrave; riaperto l'ordine di <b>%s</b> fino al <b>%s</b> perchè ci sono articoli che non hanno raggiunto la quantit&agrave; necessaria per completare un <b>collo</b><br />\nDi seguito l'elenco: %s");

//Configure::write('App.component.base', env('SCRIPT_NAME'));
//Configure::write('App.component.base', 'components/com_cake/app');

// path fisici
Configure::write('App.component.base', DS.'components'.DS.'com_cake'.DS.'app');

Configure::write('App.log', Configure::read('App.component.base').DS.'tmp'.DS.'logs');
Configure::write('App.log_joomla', DS.'logs');

// path fisico per upload
Configure::write('App.img.upload.article', DS.'images'.DS.'articles');
Configure::write('App.img.upload.prod_gas_article', DS.'images'.DS.'prod_gas_articles');
Configure::write('App.img.upload.prod_gas_promotions', DS.'images'.DS.'prod_gas_promotions');
Configure::write('App.img.upload.user', DS.'images'.DS.'users');
Configure::write('App.img.upload.content', DS.'images'.DS.'organizations'.DS.'contents');  // articoli e produttori
Configure::write('App.doc.upload.tesoriere', DS.'images'.DS.'tesoriere');
Configure::write('App.doc.upload.organizations.pays', DS.'images'.DS.'pays');
Configure::write('App.img.upload.pdf.carts', DS.'images'.DS.'pdf'.DS.'carts');
Configure::write('App.prefix.upload.content', 'tmp-');  // quando creo un nuovo produttore (tmp-supplier_id), dopo id dell'articolo di joomla

// path virtuale per web
Configure::write('App.web.img.upload.article', '/images/articles');
Configure::write('App.web.img.upload.user', '/images/users');
Configure::write('App.web.img.upload.content', '/images/organizations/contents');
Configure::write('App.web.img.upload.prod_gas_article', '/images/prod_gas_articles');
Configure::write('App.web.img.upload.prod_gas_promotions', '/images/prod_gas_promotions');
Configure::write('App.web.doc.upload.tesoriere', '/images/tesoriere');
Configure::write('App.web.doc.upload.organizations.pays', '/images/pays');
Configure::write('App.web.doc.upload.pdf.carts', '/images/pdf/carts');

Configure::write('App.web.img.upload.width.article', '175');
Configure::write('App.web.img.upload.width.supplier', '250');
Configure::write('App.web.img.upload.width.prod_gas_promotion', '250');
Configure::write('App.web.img.upload.width.userview', '50'); /* dimensione img in tag <img> */
Configure::write('App.web.img.upload.width.user', '250');   /* resize dell'img dello user */
Configure::write('App.web.img.upload.width.content', '225');
Configure::write('App.web.img.upload.extension', array('jpg','jpeg','gif','png'));
Configure::write('App.web.pdf.upload.extension', array('pdf'));
Configure::write('App.web.csv.upload.extension', array('csv'));
Configure::write('App.web.zip.upload.extension', array('zip'));
Configure::write('App.img.upload.tmp', DS.'tmp'); // utilizzato per gli allegati delle mail, CsvImport

Configure::write('CsvImportDelimiterDefault', '|');
Configure::write('CsvImportRowsMaxUsers', '80');
Configure::write('CsvImportRowsMaxArticles', '80');

Configure::write('ContentType.img', array('image/jpeg','image/jpg','image/png','image/gif'));
Configure::write('ContentType.pdf', array('application/pdf'));
Configure::write('ContentType.zip', array('application/x-zip-compressed','application/zip'));
Configure::write('ContentType.csv', array('text/csv','text/plain','application/csv','text/comma-separated-values','application/excel','application/vnd.ms-excel','application/vnd.msexcel','text/anytext','application/octet-stream','application/txt'));

Configure::write('OrganizationPayImportMax', 80); // massimo importo per il canone annuo
Configure::write('costToUser', 1); // quanti euro costa ad utente

// path web
Configure::write('App.img.cake', '/images/cake');
Configure::write('App.img.loghi', '/images/cake/loghi');
Configure::write('App.baseUrl', '/'.Configure::read('App.dominio').'/components/com_cake/app');  // variabile d'ambiente $this->Html->script()
 
Configure::write('App.cron.log', '/log');
Configure::write('App.cron.dump', '/dump');
Configure::write('App.cron.backup', '/backup');
 
/*
 *  $_SERVER['SERVER_NAME'] non e' valorizzato da Cron
 */ 
if(!isset($_SERVER['SERVER_NAME']) || empty($_SERVER['SERVER_NAME']))
	$_SERVER['SERVER_NAME'] = 'www.portalgas.it';
	
Configure::write('App.server', 'http://'.$_SERVER['SERVER_NAME']);
/*
 * override developer.mode, App.root, App.cron.log, App.cron.dump, App.cron.backup
 */
if($_SERVER['SERVER_NAME']!='www.portalgas.it' && 
	$_SERVER['SERVER_NAME']!='portalgas.it' &&
	$_SERVER['SERVER_NAME']!='localhost'
) 
	die("");
include(dirname(__FILE__). DS . 'core.'.$_SERVER['SERVER_NAME'].'.php');