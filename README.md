# Информация

При обновлении таблицы `services` реализована проверка по `tarif_group_id` и `tarif_id`, например если у пользователя сохранен `tarif_id`=1, то он его сможет поменять ТОЛЬКО на 2 или 3, т.е. только на те тарифы, которые выводим ему при запросе GET. Ну и конечно же `tarif_id`=1 нельзя обновить на `tarif_id`=1.
