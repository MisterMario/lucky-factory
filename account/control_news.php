<?php

/*
	Скрипт для управления новостными публикациями
	На данный момент необходимо сделать все записи в таблице с выводом (блока) ссылками. Все ссылки будут сформированы
	динамически. Формат ссылок: uri?news_post=<номер публикации><действие>
	Первая версия редактора новостей впринципе готова.
	Осталось лишь поработать над удалением публикаций и очисткой новостной ленты. Также над тем, чтобы вместо описания
	новостной публикации выводилась ее часть (примерно: 500-600 символов).
*/


require_once("config.php");
require_once("functions.php");


$select_options = "";

ob_start();

/*
	Обработка действий над новостями
*/

if ( isset($_POST["confirm_the_changes"]) ){ // Изменение публикации
	
	// Получение данных публикации
	$post_num = (int)$_POST["element_num"];
	$post_title = $_POST["post_title"];
	$post_content = $_POST["post_content"];
	
	// Проверка корректности получаемых данных
	if (strlen($post_title) == 0){
		$message = "Ошибка! Не задан заголовок публикации!";
		$_POST["action"] = "edit_post";
	} elseif (strlen($post_content) == 0){
		$message = "Ошибка! Не задан текст публикации!";
		$_POST["action"] = "edit_post";
	} elseif ( QueryDB("SELECT $db_newsID FROM $db_table_news  WHERE $db_newsTitle='$post_title' AND $db_newsID<>'$post_num' LIMIT 1", 0) ){
		$message = "Ошибка! Публикация с таким заголовком уже существует!";
		$_POST["action"] = "edit_post";
	} else {
		
		// Для начала необходимо проверить существование новости в БД
		if ( QueryDB("SELECT $db_newsID FROM $db_table_news WHERE $db_newsID='$post_num' LIMIT 1", 0) ){
			
			// В случае существования - замена публикации
			QueryDB("UPDATE $db_table_news SET $db_newsTitle='$post_title', $db_newsContent='$post_content' WHERE $db_newsID='$post_num'", 0);
			$message = "Публикация \"$post_title\" была успешно изменена!";
			
		} else {
			$message = "Ошибка! Такой публикации не сущетствует!";
		}
	}
		
} elseif ( isset($_POST["confirm_adding"]) ){ // Добавление публикации
	
	// Получение данных о посте
	$post_title = $_POST["post_title"];
	$post_content = $_POST["post_content"];
	$post_author = $user->getName();
	
	// Проверка корректности получаемых данных
	if (strlen($post_title) == 0){
		$message = "Ошибка! Не задан заголовок публикации!";
		$_POST["new_post_add"] = "";
	} elseif (strlen($post_content) == 0){
		$message = "Ошибка! Не задан текст публикации!";
		$_POST["new_post_add"] = "";
	} elseif ( QueryDB("SELECT $db_newsID FROM $db_table_news  WHERE $db_newsTitle='$post_title' LIMIT 1", 0) ){
		$message = "Ошибка! Публикация с таким заголовком уже существует!";
		$_POST["new_post_add"] = "";
	} else {
		
		// Добавление публикации в БД
		QueryDB("INSERT INTO $db_table_news($db_newsTitle, $db_newsContent, $db_newsAuthor) VALUES('$post_title', '$post_content', '$post_author') ", 0);
		$message = "Публикация \"$post_title\" была успешно создана!";			
		
	}
} elseif ( $_POST["action"] == "delete_post" ){ // Удаление публикации
	
	// Получение информации о публикации
	$post_num = (int)$_POST["element_num"];
	
	// Для начала проверяется существование публикации
	if ( QueryDB("SELECT $db_newsID FROM $db_table_news WHERE $db_newsID='$post_num'", 1) ){
		
		// Запрос на удаление публикации
		QueryDB("DELETE FROM $db_table_news WHERE $db_newsID='$post_num'", 0);
		$message = "Публикация с номером [$post_num] была успешно удалена!";
		
	} else {
		$message = "Публикации с таким номером не существует!";
	}
	
	
} elseif ( isset($_POST["delete_all"]) ){ // Очистка новостной ленты
	
	// Проверка на то, что лента не пуста
	$news_count = QueryDB("SELECT COUNT(*) FROM $db_table_news", 1);
	$news_count = (int)$news_count[0];
	
	if ($news_count != 0){
		
		// Удаление всей новостной ленты
		QueryDB("TRUNCATE TABLE $db_table_news", 0);
		$message = "Новостная лента успешно очищена!";
		
	} else {
		$message = "Ошибка! Новостная лента пуста!";
	}
	
}

/*
	Формирование навигационной панели
*/

// Получение информации о количестве новостных публикаций
$news_count = QueryDB("SELECT COUNT(*) FROM $db_table_news", 1);
$news_count = (int)$news_count[0];

// Определение количества страниц
if ($news_count == 0){
	$pages_count = 0;
} elseif ( ($news_count > 0) and ($news_count < 10) ){
	$pages_count = 1;
} elseif ( ($news_count % 10) != 0 ){
	$pages_count = ($news_count / 10) + 1;
} else {
	$pages_count = $news_count / 10;
}

// Вывод номеров страниц в select список
for ($i = 1; $i <= $pages_count; $i++){
	$select_options .= "<option value=\"$i\">$i</option>";
}

// Подпись к поисковой форме
$search_label = "Поиск публикации:";


/*
	Формирование контента
*/

if ( isset($_POST["search"]) ){ // В случае поиска публикации
	
	// В данном случае подгружаем искомую новость, если конечно она имеется
	$desired_post = $_POST["search"]; // искомая новость
	$search_results = QueryDB("SELECT $db_newsID, $db_newsTitle FROM $db_table_news WHERE $db_newsTitle LIKE '%$desired_post%'", 2);
	
	// Формирование таблицы
	$block_content = "<div class=\"content_table\">
						<table>
							<tr class=\"top_row\">
								<td class=\"id\">ID</td><td class=\"title\">Название публикации</td><td class=\"action\">Действие</td>
							</tr>";
	
	// Проверка того, что в результате поиска были найдены какие-либо записи
	if ( mysql_num_rows($search_results) != 0 ){
		
		// Вывод найденных результатов
		while ( $line = mysql_fetch_array($search_results) ){
			$block_content .= "<tr>
									<td>$line[$db_newsID]</td><td>$line[$db_newsTitle]</td>
									<td class=\"action\">
										<form method=\"post\" action=\"\">
											<input type=\"hidden\" name=\"element_num\" value=\"$line[$db_newsID]\">
											<select name=\"action\">
												<option value=\"edit_post\">Редактировать</option>
												<option value=\"delete_post\">Удалить</option>
											</select>
											<button type=\"submit\">+</button>
										</form></td>
								</tr>";
		}
		
	} else {
		$block_content .= "<tr>\n<td colspan=\"3\"><p style=\"text-align:center\">Новостная публикация не найдена!</p></td>\n</tr>\n";
	}
	$block_content .= "</table>\n</div>";
	
} elseif ( $_POST["action"] == "edit_post" ){ // Редактирование новостной публикации
	
	// Получение номера публикации
	$post_num = (int)$_POST["element_num"];
	// Получаем данные публикации из БД
	$post_info = QueryDB("SELECT $db_newsTitle, $db_newsContent FROM $db_table_news WHERE $db_newsID='$post_num' LIMIT 1", 1);
	
	// Проверка результата
	if ($post_info){
		
		// Добавление новой новостной публикации
		$block_content = "<div class=\"news_editor\">
							<form id=\"news_post_edit\" method=\"post\" action=\"\">
								<input type=\"hidden\" name=\"element_num\" value=\"$post_num\">
								<input type=\"text\" name=\"post_title\" placeholder=\"Заголовок новости\" value=\"$post_info[$db_newsTitle]\" />
								<textarea name=\"post_content\" placeholder=\"Текст новостной публикации\">$post_info[$db_newsContent]</textarea></td>
							</form>
						</div>";
		// Кнопки внизу управляющего блока
		$buttons_block = "<button type=\"submit\" name=\"confirm_the_changes\" form=\"news_post_edit\">Изменить</button>\n
						  <button type=\"submit\" name=\"cancel\" form=\"news_post_edit\">Отмена</button>\n";
		
	} else {
		$block_content = "<p>Ошибка! Выбранной публикации не существует!</p>";
	}
	
} elseif ( isset($_POST["new_post_add"]) ){ // Добавление новой новостной публикации

	// Получение данных (если страница была перезагружена)
	$post_title = $_POST["post_title"];
	$post_content = $_POST["post_content"];
	
	// Добавление новой новостной публикации
	$block_content = "<div class=\"news_editor\">
						<form id=\"news_post_edit\" method=\"post\" action=\"\">
							<input type=\"text\" name=\"post_title\" placeholder=\"Заголовок новости\" value=\"$post_title\" />
							<textarea name=\"post_content\" placeholder=\"Текст новостной публикации\">$post_content</textarea></td>
						</form>
						</div>";
	// Кнопки внизу управляющего блока
	$buttons_block = "<button type=\"submit\" name=\"confirm_adding\" form=\"news_post_edit\">Добавить</button>\n
					  <button type=\"submit\" name=\"cancel\" form=\"news_post_edit\">Отмена</button>\n";
	
} else { // Обычный вывод новостей
	
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
								<td class=\"id\">ID</td><td class=\"title\">Название публикации</td><td class=\"action\">Действие</td>
							</tr>";
	if ( ($select_page_num > $pages_count) or ($pages_count == 0) ){
		$block_content .= "<tr>\n<td colspan=\"3\"><p style=\"text-align:center\">Новостная лента пуста!</p></td>\n</tr>\n";
	} else {
		
		// Если лента не пуста - делаем вывод публикаций
		// Для начала находится диапазан из которого будут выводиться публикации
		if ($select_page_num <= 10){
			$min_num = 1;
		} else {
			$min_num = ($select_page_num * 10) - 9;
		}
		$max_num = $min_num + 9;
		
		// Получение публикаций из БД (по номеру записи в таблице)
		$news_post_arr = array();
		
		for ($i = $min_num-1; $i < $max_num; $i++){
			$news_post_arr[] = QueryDB("SELECT $db_newsID, $db_newsTitle FROM $db_table_news LIMIT ".$i.", 1", 1);
		}
		
		// Вывод публикаций в таблицу
		while ( $line = current($news_post_arr) ){
			$block_content .= "<tr>\n";
			$block_content .= "<td>$line[$db_newsID]</td><td>$line[$db_newsTitle]</td>
								<td class=\"action\">
									<form method=\"post\" action=\"\">
										<input type=\"hidden\" name=\"element_num\" value=\"$line[$db_newsID]\">
										<select name=\"action\">
											<option value=\"edit_post\">Редактировать</option>
											<option value=\"delete_post\">Удалить</option>
										</select>
										<button type=\"submit\">+</button>
									</form></td>";
			$block_content .= "</tr>\n";
			next($news_post_arr);
		}
	}
	$block_content .= "</table>\n</div>";
	
}


/* 
	Подгрузка навигационной панели и основных кнопок управления 
*/

if ( ($_POST["action"] != "edit_post") and !isset($_POST["new_post_add"]) ){
	
	// Кнопки внизу управляющего блока
	$buttons_block = "<form method=\"post\" action=\"\">\n
						<button type=\"submit\" name=\"new_post_add\">Добавить новость</button>\n
						<button type=\"submit\" name=\"delete_all\">Очистить ленту</button>\n
					</form>";
					// Здесь необходимо добавить скрытый submit, который будет активироваться при подверждении 
								
	
	// Подгрузка навигационной панели
	include $way_style."control_block_navigation.html";
	$block_navigation = ob_get_clean();

}

?>