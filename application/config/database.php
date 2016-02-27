<?php  if ( ! defined('BASEPATH')) exit('No direct script access allowed');
/*
| -------------------------------------------------------------------
| DATABASE CONNECTIVITY SETTINGS
| -------------------------------------------------------------------
| This file will contain the settings needed to access your database.
|
| For complete instructions please consult the 'Database Connection'
| page of the User Guide.
|
| -------------------------------------------------------------------
| EXPLANATION OF VARIABLES
| -------------------------------------------------------------------
|
|	['hostname'] The hostname of your database server.
|	['username'] The username used to connect to the database
|	['password'] The password used to connect to the database
|	['database'] The name of the database you want to connect to
|	['dbdriver'] The database type. ie: mysql.  Currently supported:
				 mysql, mysqli, postgre, odbc, mssql, sqlite, oci8
|	['dbprefix'] You can add an optional prefix, which will be added
|				 to the table name when using the  Active Record class
|	['pconnect'] TRUE/FALSE - Whether to use a persistent connection
|	['db_debug'] TRUE/FALSE - Whether database errors should be displayed.
|	['cache_on'] TRUE/FALSE - Enables/disables query caching
|	['cachedir'] The path to the folder where cache files should be stored
|	['char_set'] The character set used in communicating with the database
|	['dbcollat'] The character collation used in communicating with the database
|				 NOTE: For MySQL and MySQLi databases, this setting is only used
| 				 as a backup if your server is running PHP < 5.2.3 or MySQL < 5.0.7.
| 				 There is an incompatibility in PHP with mysql_real_escape_string() which
| 				 can make your site vulnerable to SQL injection if you are using a
| 				 multi-byte character set and are running versions lower than these.
| 				 Sites using Latin-1 or UTF-8 database character set and collation are unaffected.
|	['swap_pre'] A default table prefix that should be swapped with the dbprefix
|	['autoinit'] Whether or not to automatically initialize the database.
|	['stricton'] TRUE/FALSE - forces 'Strict Mode' connections
|							- good for ensuring strict SQL while developing
|
| The $active_group variable lets you choose which connection group to
| make active.  By default there is only one group (the 'default' group).
|
| The $active_record variables lets you determine whether or not to load
| the active record class
*/

$active_group = 'ticketingsystem';
$active_record = TRUE;

$db['ticketingsystem']['hostname'] = 'localhost';
$db['ticketingsystem']['username'] = 'root';
$db['ticketingsystem']['password'] = '';
$db['ticketingsystem']['database'] = 'ticketingsystem';
$db['ticketingsystem']['dbdriver'] = 'mysql';
$db['ticketingsystem']['dbprefix'] = '';
$db['ticketingsystem']['pconnect'] = TRUE;
$db['ticketingsystem']['db_debug'] = TRUE;
$db['ticketingsystem']['cache_on'] = FALSE;
$db['ticketingsystem']['cachedir'] = '';
$db['ticketingsystem']['char_set'] = 'utf8';
$db['ticketingsystem']['dbcollat'] = 'utf8_general_ci';
$db['ticketingsystem']['swap_pre'] = '';
$db['ticketingsystem']['autoinit'] = TRUE;
$db['ticketingsystem']['stricton'] = TRUE;

$db['employee_db']['hostname'] = 'localhost';
$db['employee_db']['username'] = 'root';
$db['employee_db']['password'] = 'theia';
$db['employee_db']['database'] = 'employee_db';
$db['employee_db']['dbdriver'] = 'mysql';
$db['employee_db']['dbprefix'] = '';
$db['employee_db']['pconnect'] = TRUE;
$db['employee_db']['db_debug'] = TRUE;
$db['employee_db']['cache_on'] = FALSE;
$db['employee_db']['cachedir'] = '';
$db['employee_db']['char_set'] = 'utf8';
$db['employee_db']['dbcollat'] = 'utf8_general_ci';
$db['employee_db']['swap_pre'] = '';
$db['employee_db']['autoinit'] = FALSE;
$db['employee_db']['stricton'] = FALSE;

$db['library']['hostname'] = 'localhost';
$db['library']['username'] = 'root';
$db['library']['password'] = 'theia';
$db['library']['database'] = 'library';
$db['library']['dbdriver'] = 'mysql';
$db['library']['dbprefix'] = '';
$db['library']['pconnect'] = TRUE;
$db['library']['db_debug'] = TRUE;
$db['library']['cache_on'] = FALSE;
$db['library']['cachedir'] = '';
$db['library']['char_set'] = 'utf8';
$db['library']['dbcollat'] = 'utf8_general_ci';
$db['library']['swap_pre'] = '';
$db['library']['autoinit'] = FALSE;
$db['library']['stricton'] = FALSE;


/* End of file database.php */
/* Location: ./application/config/database.php */