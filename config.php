<?php

// -------- DATABASE CONFIGURATION --------
define('DATABASE_GENERATION_FILE', 'Database/project_mysql.sql'); // File that can regenerate the database. Leave empty is not needed

// Using custom DSN
define("DATABASE_CUSTOM_DSN", ""); // Data source name : leave empty if not used
// Using easy DSN
define("DATABASE_TYPE", "mysql"); // mysql or sqlite (for now, or other with similar SQL syntaxe)
define("DATABASE_HOST", "localhost"); // location of the database
define("DATABASE_PORT", ""); // database port (mysql default is 3306)
define("DATABASE_NAME", "project"); // file name for sqlite, database name for mysql

define("DATABASE_LOGIN", "root"); // can be left empty
define("DATABASE_PASSWORD", ""); // can be left empty

// -------- OTHER CONFIGURATION --------

define('HOME_PAGE_PROJECT_MAX_NUMBER', 4);
define('BACKEND_RESULTS_PER_PAGE', 10); // please > 0 (not going to check everytime the value is used)

?>
