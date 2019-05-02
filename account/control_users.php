<?php

/*
	Скрипт для управления пользователями
	P.s. скрипт еще нуждается в доработке. Пока, что я остановился на редакторе профиля конкретного пользователя.
	Конкретно в редактор пользователей необходимо добавить редактирование количества станций для конретного пользователя.
	Также я добавил новую таблицу в БД, содержащую информацию о группах пользователях, существующих в рамках проекта.
	Так, что стоит подогнать проект под это новшество, а также добавить редактор прав групп пользователей в панель управления.
*/


require_once("config.php");
require_once("functions.php");


$select_options = "";

ob_start();

/*
	Обработка действий выполняемых с пользователями
*/

if (isset($_POST["confirm_the_changes"])){ // Если были изменены пользовательские данные
	
	// Сохранение изменных данных
	$user_id = $_POST["user_id"];
	$user_login = $_POST["user_login"];
	$user_password = $_POST["user_password"];
	$user_lvl = $_POST["user_group"];
	$user_email = $_POST["user_email"];
	$user_balance = $_POST["user_balance"];
	
	// Проверка того, что пользователь с таким id существует
	$check_id = QueryDB("SELECT $db_aUserID FROM $db_table_accounts WHERE $db_aUserID='$user_id' LIMIT 1", 1);
	
	// Проверка на то, что пользователь с таким именем и e-mail-ом уже не зарегистрирован
	$check_login = QueryDB("SELECT $db_aUserID FROM $db_table_accounts WHERE $db_aUsername='$user_login' AND $db_aUserID<>$user_id LIMIT 1", 1);
	$check_email = QueryDB("SELECT $db_aUserID FROM $db_table_accounts WHERE $db_aEmail='$user_email' AND $db_aUserID<>$user_id LIMIT 1", 1);
	
	// В приведенных ниже условиях есть проверка пароля на пустоту, так как в редакторе профиля пользователя не обязательно
	// изменение пароля, лишь по желанию. Поэтому это поле может оставаться пустым.
	if (empty($user_id) or empty($user_login) or empty($user_lvl) or empty($user_email) or empty($user_balance)){
		$message = "Ошибка! Не все поля заполнены!";
		$_POST["action"] = "edit_user";
	} elseif (!$check_id){
		$message = "Пользователя с переданным идентификатором не существует!";
		$_POST["action"] = "edit_user";
	} elseif ((strlen($user_login)<6) or (strlen($user_login)>32)){
		$message = "Логин недопустимой длины!";
		$_POST["action"] = "edit_user";
	} elseif ((strlen($user_password)<6) and (strlen($user_password)>32) and !empty($user_password)){
		$message = "Пароль недопустимой длины";
		$_POST["action"] = "edit_user";
	} elseif (!ValidLogin($user_login)){
		$message = "Логин содержит недопустимые символы!";
		$_POST["action"] = "edit_user";
	} elseif (!ValidPassword($user_password, "null") and !empty($user_password)){
		$message = "Пароль содержит недопустимые символы";
		$_POST["action"] = "edit_user";
	} elseif (!ValidEmail($user_email)){
		$message = "Введен некорректный e-mail!";
		$_POST["action"] = "edit_user";
	} elseif ($check_login) {
		$message = "Пользователь с таким именем уже зарегистрирован!";
		$_POST["action"] = "edit_user";
	} elseif ($check_email){
		$message = "Пользователь с таким e-mail-ом уже зарегистрирован!";
		$_POST["action"] = "edit_user";
	} else {
		
		// Все проверки пройдены. Выполнение загрузки данных в БД.
		// Для начала проверка того, что изменялся пароль. Если да - он также загружается в БД, нет - не загружается.
		if (!empty($user_password)){ // Пароль был изменен
			$user_password = GetHash($user_password);
			QueryDB("UPDATE $db_table_accounts SET $db_aUsername='$user_login', $db_aPassword='$user_password',
				$db_aLvl='$user_lvl', $db_aEmail='$user_email' WHERE $db_aUserID='$user_id'", 0);
			QueryDB("UPDATE $db_table_balance SET $db_bMoney='$user_balance' WHERE $db_bUserID='$user_id'", 0);
		} else { // Если пароль не изменялся
			QueryDB("UPDATE $db_table_accounts SET $db_aUsername='$user_login', $db_aLvl='$user_lvl', 
				$db_aEmail='$user_email' WHERE $db_aUserID='$user_id'", 0);
			QueryDB("UPDATE $db_table_balance SET $db_bMoney='$user_balance' WHERE $db_bUserID='$user_id'", 0);
		}
		
		// Так как произошли изменения с данными пользователя, требуется перезагрузка сессии.
		// Для начала необходимо получить идентификатор сессии пользователя из БД.
		$user_session = QueryDB("SELECT $db_aSession FROM $db_table_accounts WHERE $db_aUserID='$user_id' LIMIT 1", 1);
		
		// Проверка того, что пользователель не пытается перезагрузить собственную сессию
		if ($user->getId() == $user_id){
			
			session_destroy();
			session_commit();
			session_start();
			
		} elseif (strlen($user_session["$db_aSession"]) != 0){ // Если пользователь авторизирован, т.е. его сессия существует.
			
			// Сохранение идентификатора текущей сессии
			$current_session_id = session_id();
			session_commit();
			
			// Уничтожение необходимой сессии
			session_id($user_session["$db_aSession"]);
			session_start();
			session_destroy();
			session_commit();
		
			// Возврат к текущей сессии
			session_id($current_session_id);
			session_start();
		}
		
		$message = "Изменения успешно завершены!";
	}
}


/*
	Формирование навигационной панели
*/

// Получение информации о количестве пользователе
$users_count = QueryDB("SELECT COUNT(*) FROM $db_table_accounts", 1);
$users_count = (int)$users_count[0];

// Определение количества пользователей
if ($users_count == 0){
	$pages_count = 0;
} elseif ( ($users_count > 0) and ($users_count < 10) ){
	$pages_count = 1;
} elseif ( ($users_count % 10) != 0 ){
	$pages_count = ($users_count / 10) + 1;
} else {
	$pages_count = $users_count / 10;
}

// Вывод номеров страниц в select список
for ($i = 1; $i <= $pages_count; $i++){
	$select_options .= "<option value=\"$i\">$i</option>";
}

// Подпись к поисковой форме
$search_label = "Поиск пользователя:";


/*
	Формирование контента
*/

if ( isset($_POST["search"]) ){ // В случае поиска пользователя
	
	// Получение информации о искомом пользователе
	$desired_user = $_POST["search"]; // искомый пользователь
	$search_results = QueryDB("SELECT $db_aUserID, $db_aUsername FROM $db_table_accounts WHERE $db_aUsername LIKE '%$desired_user%'", 2);
	
	// Формирование таблицы
	$block_content = "<div class=\"content_table\">
						<table>
							<tr class=\"top_row\">
								<td class=\"id\">ID</td><td class=\"username\">Имя пользователя</td><td class=\"group\">Группа:</td><td class=\"action\">Действие</td>
							</tr>";
	
	// Проверка того, что в результате поиска были найдены какие-либо записи
	if ( mysql_num_rows($search_results) != 0 ){
		
		// Вывод найденных результатов
		while ( $line = mysql_fetch_array($search_results) ){
			$block_content .= "<tr>
									<td>$line[$db_aUserID]</td><td>$line[$db_aUsername]</td>
									<td>".GetUserGroup($line[$db_aUserID])."</td>
									<td class=\"action\">
										<form method=\"post\" action=\"\">
											<input type=\"hidden\" name=\"element_num\" value=\"$line[$db_aUserID]\">
											<select name=\"action\">
												<option value=\"edit_user\">Редактировать</option>
												<option value=\"ban_user\">Заблокировать</option>
												<option value=\"delete_user\">Удалить</option>
											</select>
											<button type=\"submit\">+</button>
										</form></td>
								</tr>";
		}
		
	} else {
		$block_content .= "<tr>\n<td colspan=\"4\"><p style=\"text-align:center\">Такого пользователя не существует!</p></td>\n</tr>\n";
	}
	$block_content .= "</table>\n</div>";
	
} elseif ( $_POST["action"] == "edit_user" ){ // Редактирование информации о пользователе
	
	// Проверка того, что страница не была перезагружена в результате ошибочных данных
	// В случае истины все данные о пользователе берутся из POST-а
	if (isset($_POST["user_id"])){
		
		$user_info["$db_aUserID"] = $_POST["user_id"];
		$user_info["$db_aUsername"] = $_POST["user_login"];
		$user_info["$db_aLvl"] = $_POST["user_group"];
		$user_info["$db_aEmail"] = $_POST["user_email"];
		$user_info["$db_bMoney"] = $_POST["user_balance"];
		
	} else { // Страница не перезагружалась - данные берутся из БД
		
		$user_id = $_POST["element_num"];
		
		// Получение информации о выбранном пользователе
		$user_info = QueryDB("SELECT $db_aUserID, $db_aUsername, $db_aEmail, $db_aLvl FROM $db_table_accounts WHERE $db_aUserID='$user_id' LIMIT 1", 1);
		$user_info += QueryDB("SELECT $db_bMoney FROM $db_table_balance WHERE $db_bUserID='$user_id' LIMIT 1", 1);
	}
	
	// Проверка того, что данные были получены, т.е. пользователь существует
	if (gettype($user_info) != "boolean"){
		
		// Получение списка групп пользователей
		$userGroupsList = QueryDB("SELECT * FROM $db_table_user_groups", 2);
		
		// Формирование списка из полученных групп
		$selectUserGroups = "<select class=\"select_group\" name=\"user_group\">\n";
		while ($line = mysql_fetch_array($userGroupsList)){
			
			if ($user_info["$db_aLvl"] == $line["$db_uGroupsLvl"]){ // Если данный пользователь относится к этой текущей группе
				$selectUserGroups .= "<option selected value=\"".$line["$db_uGroupsLvl"]."\">".$line["$db_uGroupsName"]."</option>\n";
			} elseif ($line["$db_uGroupsLvl"] != 0) { // Проверка того, что у данной группы статус - НЕ прохожий
				$selectUserGroups .= "<option value=\"".$line["$db_uGroupsLvl"]."\">".$line["$db_uGroupsName"]."</option>\n";
			}
		}
		$selectUserGroups .= "</select>";
		
		// Подгрузка страницы с макетом профиля
		ob_start();
		include $way_style."users_editor.html";
		$block_content = ob_get_clean();
		
		// Кнопки внизу управляющего блока
		$buttons_block = "<button type=\"submit\" name=\"confirm_the_changes\" form=\"user_info_edit\">Изменить</button>\n
						  <button type=\"submit\" name=\"cancel\" form=\"user_info_edit\">Отмена</button>\n";
	}
	
} elseif ( isset($_POST["new_post_add"]) ){ // Добавление нового пользователя

	
	
} else { // Обычный вывод списка пользователей
	
	// Получаем номер выбранной страницы
	if ( !isset($_POST["selected_number"]) ){ // Если страница не задана, то берется первая
		$select_page_num = 1;
	} else {
		$select_page_num = (int)$_POST["selected_number"];
	}
	
	// Формирование таблицы
	$block_content = "<div class=\"content_table\">
						<table>
							<tr class=\"top_row\">
								<td class=\"id\">ID</td><td class=\"username\">Имя пользователя</td><td class=\"group\">Группа:</td><td class=\"action\">Действие</td>
							</tr>";
	if ( ($select_page_num > $pages_count) or ($pages_count == 0) ){
		$block_content .= "<tr>\n<td colspan=\"3\"><p style=\"text-align:center\">База данных пуста!</p></td>\n</tr>\n";
	} else {
		
		// Если количество пользователtй не нулевое, делается вывод
		// Для начала находится диапазан из которого будут выводиться пользователи
		if ($select_page_num <= 10){
			$min_num = 1;
		} else {
			$min_num = ($select_page_num * 10) - 9;
		}
		$max_num = $min_num + 9;
		
		// Получение информации о пользователях из БД (по номеру записи в таблице)
		$users_arr = array();
		
		for ($i = $min_num-1; $i < $max_num; $i++){
			$users_arr[] = QueryDB("SELECT $db_aUserID, $db_aUsername FROM $db_table_accounts LIMIT ".$i.", 1", 1);
		}
		
		// Вывод публикаций в таблицу
		while ( $line = current($users_arr) ){
			$block_content .= "<tr>\n";
			$block_content .= "<td>$line[$db_aUserID]</td><td>$line[$db_aUsername]</td>
								<td>".GetUserGroup($line[$db_aUserID])."</td>
								<td class=\"action\">
									<form method=\"post\" action=\"\">
										<input type=\"hidden\" name=\"element_num\" value=\"$line[$db_aUserID]\">
										<select name=\"action\">
											<option value=\"edit_user\">Редактировать</option>
											<option value=\"ban_user\">Заблокировать</option>
											<option value=\"delete_post\">Удалить</option>
										</select>
										<button type=\"submit\">+</button>
									</form></td>";
			$block_content .= "</tr>\n";
			next($users_arr);
		}
	}
	$block_content .= "</table>\n</div>";
	
}


/* 
	Подгрузка навигационной панели и основных кнопок управления 
*/

if ($_POST["action"] != "edit_user"){
	
	// Подгрузка навигационной панели
	include $way_style."control_block_navigation.html";
	$block_navigation = ob_get_clean();

}

?>