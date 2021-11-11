Dear system administrator, 
we took the liberty of saving all items from Joomla's recycle bin 
into this folder.

They were exported and reimported in SQL format using UTF-8 encoding (or the database default).

In case you emptied the trash accidentally, you can get your data back in several ways:

1. Command line mysql
	from a shell (ssh) type:
	cd tmp/recycle_bin_backup
	mysql -u {DBUSER} --password="your_password" {DBNAME} < backup_littlehelper_trash_2013-03-21---02-33-19.sql

2. PHPMyAdmin
	http://www.phpmyadmin.net
	Login with {DBUSER} or another user with sufficient privileges
	Select the Joomla! database {DBNAME}, choose "Import" on the menubar, then click Browse 
	and choose:
		tmp/recycle_bin_backup/backup_littlehelper_trash_2013-03-21---02-33-19.sql

3. Mysql WorkBench
	http://www.mysql.it/products/workbench/
	

You can find more info at the project's homepage http://fasterjoomla.com/littlehelper
	