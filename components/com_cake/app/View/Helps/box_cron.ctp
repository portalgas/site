<h1>Cron</h1><pre class="shell" rel="crontab -e">8 23 * * * /var/portalgas/cron/articlesFromCartToStoreroom.sh >> /var/portalgas/cron/log/$(date +\%Y\%m\%d)_articlesFromCartToStoreroom.log 2>&15 0 * * * /var/portalgas/cron/ordersStatoElaborazione.sh >> /var/portalgas/cron/log/$(date +\%Y\%m\%d)_ordersStatoElaborazione.log 2>&110 0 * * * /var/portalgas/cron/loopsOrders.sh >> /var/portalgas/cron/log/$(date +\%Y\%m\%d)_loopsOrders.log 2>&115 0 * * * /var/portalgas/cron/mailUsersOrdersOpen.sh >> /var/portalgas/cron/log/$(date +\%Y\%m\%d)_mailUsersOrdersOpen.log 2>&120 0 * * * /var/portalgas/cron/mailUsersOrdersClose.sh >> /var/portalgas/cron/log/$(date +\%Y\%m\%d)_mailUsersOrdersClose.log 2>&125 0 * * * /var/portalgas/cron/mailUsersDelivery.sh >> /var/portalgas/cron/log/$(date +\%Y\%m\%d)_mailUsersDelivery.log 2>&135 0 * * * /var/portalgas/cron/mailEvents.sh >> /var/portalgas/cron/log/$(date +\%Y\%m\%d)_mailEvents.log 2>&140 0 * * * /var/portalgas/cron/mailMonitoringSuppliersOrganizationsOrdersDataFine.sh >> /var/portalgas/cron/log/$(date +\%Y\%m\%d)_mailMonitoringSuppliersOrg$30 7 * * * /var/portalgas/cron/gcalendarUsersDeliveryInsert.sh >> /var/portalgas/cron/log/$(date +\%Y\%m\%d)_gcalendarUsersDeliveryInsert.log 2>&150 7 * * * /var/portalgas/cron/gcalendarUsersDeliveryUpdate.sh >> /var/portalgas/cron/log/$(date +\%Y\%m\%d)_gcalendarUsersDeliveryUpdate.log 2>&120 8,13,20 * * * /var/portalgas/cron/mailReferentiOrderQtaMax.sh >> /var/portalgas/cron/log/$(date +\%Y\%m\%d)_mailReferentiOrderQtaMax.log 2>&140 8,13,20 * * * /var/portalgas/cron/mailReferentiOrderImportoMax.sh >> /var/portalgas/cron/log/$(date +\%Y\%m\%d)_mailReferentiOrderImportoMax.log 2>&110 8,13,20 * * * /var/portalgas/cron/mailReferentiQtaMax.sh >> /var/portalgas/cron/log/$(date +\%Y\%m\%d)_mailReferentiQtaMax.log 2>&135 0 * * * /var/portalgas/cron/loopsDeliveries.sh >> /var/portalgas/cron/log/$(date +\%Y\%m\%d)_loopsDeliveries.log 2>&140 0 * * * /var/portalgas/cron/articlesOrdersQtaCart.sh >> /var/portalgas/cron/log/$(date +\%Y\%m\%d)_articlesOrdersQtaCart.log 2>&110 1 * * * /var/portalgas/cron/articlesBio.sh >> /var/portalgas/cron/log/$(date +\%Y\%m\%d)_articlesBio.log 2>&10 1 * * * /var/portalgas/cron/deliveriesStatoElaborazione.sh >> /var/portalgas/cron/log/$(date +\%Y\%m\%d)_deliveriesStatoElaborazione.log 2>&1# non + utilizzato 10 1 * * * /var/portalgas/cron/deliveriesCassiereClose.sh >> /var/portalgas/cron/log/$(date +\%Y\%m\%d)_deliveriesCassiereClose.log 2>&130 1 * * * /var/portalgas/cron/requestPaymentStatoElaborazione.sh >> /var/portalgas/cron/log/$(date +\%Y\%m\%d)_requestPaymentStatoElaborazione.log 2>&145 1 * * * /var/portalgas/cron/archiveStatistics.sh >> /var/portalgas/cron/log/$(date +\%Y\%m\%d)_archiveStatistics.log 2>&10 5 * * * /var/portalgas/cron/database_dump.sh >> /var/portalgas/cron/log/$(date +\%Y\%m\%d)_database_dump.log 2>&130 5 * * * /var/portalgas/cron/backup.sh  >> /var/portalgas/cron/log/$(date +\%Y\%m\%d)_backup.log 2>&10 7 * * * /var/portalgas/cron/filesystemLogDelete.sh >> /var/portalgas/cron/log/$(date +\%Y\%m\%d)_filesystemLogDelete.log 2>&115 7 * * * /var/portalgas/cron/mails.sh30 * * * * /var/portalgas/cron/rss.sh >> /var/portalgas/cron/log/$(date +\%Y\%m\%d)_rss.log 2>&125 5,6,7,10,22,23 * * * /var/portalgas/cron/usersGmaps.sh >> /var/portalgas/cron/log/$(date +\%Y\%m\%d)_usersGmaps.log 2>&138 5,6,22,23 * * * /var/portalgas/cron/suppliersGmaps.sh >> /var/portalgas/cron/log/$(date +\%Y\%m\%d)_suppliersGmaps.log 2>&155 2 1 * * /var/portalgas/cron/database_alliena_test_con_prod.sh  >> /var/portalgas/cron/log/$(date +\%Y\%m\%d)_database_alliena_test_con_prod.log 2>&130 5 * * * /var/portalgas/cron/desSetSupplierOrganizationOwnerArticles.sh >> /var/portalgas/cron/log/$(date +\%Y\%m\%d)_desSetSupplierOrganizationOwnerArticl$## pila#30 7 * * * /var/pila/cron/database_dump.sh >> /var/pila/cron/log/$(date +\%Y\%m\%d)_database_dump.log 2>&1</pre>