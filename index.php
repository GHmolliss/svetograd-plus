<?php

/**
 * При обновлении таблицы `services` реализована проверка по `tarif_group_id` и `tarif_id`, т.е. если у пользователя
 * сохранен `tarif_id`=1, то он его сможет поменять ТОЛЬКО на 2 или 3.
 */

//define('DB_HOST', '127.0.0.1');
//define('DB_USER', 'root');
//define('DB_PASSWORD', 'root');
//define('DB_NAME', 'skynet');

require_once './folder_name/index.php';
