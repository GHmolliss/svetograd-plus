<?php

/**
 * При обновлении таблицы `services` реализована проверка по `tarif_group_id` и `tarif_id`, т.е. если у пользователя
 * сохранен `tarif_id`=1, то он его сможет поменять ТОЛЬКО на 2 или 3.
 */

use App\API;

//ini_set('error_reporting',E_ALL);
//ini_set('display_errors',1);
//ini_set('display_startup_errors',1);

set_include_path(get_include_path()
    . PATH_SEPARATOR . $_SERVER['DOCUMENT_ROOT'] . '/App/'
);
spl_autoload_extensions('.php');
spl_autoload_register();

//define('DB_HOST', '127.0.0.1');
//define('DB_USER', 'root');
//define('DB_PASSWORD', 'root');
//define('DB_NAME', 'skynet');

(new API);
