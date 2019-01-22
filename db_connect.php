<?php 

/** Имя файла для импорта */
define('IMPORT_FILE_NAME', "west.xml");

/** Имя базы данных для WordPress */
define('DB_NAME', 'zilmet_wp');

/** Имя пользователя MySQL */
define('DB_USER', 'zilmet_ru');

/** Пароль к базе данных MySQL */
//define('DB_PASSWORD', 'eick39d');
define('DB_PASSWORD', 'eick39d');

/** Имя сервера MySQL */
define('DB_HOST', '127.0.0.1');

$hDB = new mysqli( DB_HOST, DB_USER, DB_PASSWORD, DB_NAME );