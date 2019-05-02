<?php

require_once("config.php");

/* Функция для получения URI */
function GetURI(){
	return strTok($_SERVER["REQUEST_URI"], "?");
}

/* Функция удаляет слеши из строки */
function DelSlashes($str){
	$str = str_replace("/", "", $str);
	return $str;
}

/* Функция возвращает блок ошибки по коду */
function ErorPrint($code){
	global $way_style;

	ob_start();
	switch($code){ // Подгружаем ошибку по коду
		case 401:
			include $way_style."401.html";
			break;
		case 403:
			include $way_style."403.html";
			break;
		case 404:
			include $way_style."404.html";
			break;
	}
	$eror = ob_get_clean();
	return $eror;
}

/* Функция для задания имени страницы */
function SetPageName($name){
	return "<h1 class=\"page_name\">".$name."</h1>";
}

/*  Функция проверки ссылки на сложность:
	true - ссылка сложная, false - простая. */
function ComplicatedLink($uri){
	if ($uri == "/news/"){
		return true;
	} else {
		// Ищем слеш в ссылке. Первый символ не учитывается, так как он и так слеш.
		for ($i = 1; $i < strlen($uri); $i++){
			if ($uri[$i] == "/"){
				// Если строка на данном символе закончилась - ссылка простая, нет - сложная.
				if ((strlen($uri)-1) == $i){
					return false;
				} else {
					return true;
				}
			}
		}
		return false; // В случае, если слеш не найден - ссылка также простая.
	}
}

/* Функция определения секции (или раздела) к которой относится сложная ссылка */
function DefinitionOfTheLinkSection($uri){
	// Ищем слеш в ссылке. Первый символ не учитывается, так как является слешем.
	for ($i = 1; $i < strlen($uri); $i++){
		if ($uri[$i] == "/"){
			return substr($uri, 1, $i-1);
		}
	}
}

/* Функция определения контента к которому обращается сложная ссылка */
function DefinitionOfTheLinkContent($uri){
	for ($i = 1; $i < strlen($uri); $i++){
		if ($uri[$i] == "/"){
			return substr($uri, $i, strlen($uri)-$i);
		}
	}
}

/* Функция для построения меню из массива. Массив содержит ключ (имя ссылки) и значение (ссылку) */
function MenuConstructor($menu_arr){
	// Перебираем массив, последовательно получая значения и их ключи.
	$menu = "<ul>\n";
	while($element_arr = current($menu_arr)){
		$menu .= "<li><a href=\"".$element_arr."\">".key($menu_arr)."</a></li>\n";
		next($menu_arr);
	}
	$menu .= "</ul>\n";
	return $menu;
}

/* Функция для построения меню в личном кабинете. Данные функция получает из массива. */
function AccountMenuConstructor($menu_arr, $lvl){
	$menu = "<div id=\"account_menu\">\n<ul>\n";
	// Перебираем массив меню, собирая все доступные пункты
	while($element_arr = current($menu_arr)){
		if ($lvl >= $element_arr["lvl"]){ // Проверка хватает ли прав на данный пункт меню
			$menu .= "<li><a href=\"".$element_arr["link"]."\">".key($menu_arr)."</a></li>\n";
		}
		next($menu_arr);
	}
	$menu .= "</ul>\n</div>\n";
	return $menu;
}

/* Функция проверяет содежит ли меню переданную ссылку */
function PagesCheckerLink($pages_arr, $link){
	// Для начала отбросим слеши из полученной ссылки
	$link = DelSlashes($link);

	// Перебираем массив и сравниваем ключи (ссылки)
	while($element_arr = current($pages_arr)){
		if ($element_arr["link"] == $link){
			return true;
		}
		next($pages_arr);
	}
	return false; // В случае, если ключ не был найден
}

/* Функция возвращет кокретный элемент массива пользовательского меню */
function PagesElement($pages_arr, $link){
	// Для начала отбросим слеши из полученной ссылки
	$link = DelSlashes($link);
	while($element_arr = current($pages_arr)){
		if ($element_arr["link"] == $link){
			$pages_element = array("name" => key($pages_arr),
								   "document" => $element_arr["document"],
								   "lvl" => $element_arr["lvl"]);
			return $pages_element;
		}
		next($pages_arr);
	}
	return false;
}

/* Функция для установки соединения с БД */
function ConnectDB(){
	global $db_host, $db_user, $db_pass, $db_name;
	$connect = mysql_connect($db_host, $db_user, $db_pass) or die("ОШИБКА СЕРВЕРА! НЕВОЗМОЖНО ПОДКЛЮЧИТЬСЯ К БАЗЕ ДАННЫХ!");
			   mysql_select_db($db_name, $connect) or die("ОШИБКА СЕРВЕРА! НЕВОЗМОЖНО ПОДКЛЮЧИТЬСЯ К БАЗЕ ДАННЫХ!");
			   mysql_set_charset("UTF-8", $connect);
	return $connect;
}

/* Функция для запросов в БД
   Методы:
   0 - запрос не является выборкой,
   1 - запрос является выборкой, подразумевающей получение одной строки данных,
   2 - запрос является выборкой, подразумевающей получение большого числа данных */
function QueryDB($query, $method){
	$connect = ConnectDB();
	$result = mysql_query($query, $connect);
	mysql_close($connect);
	if ($result){
		if ($method == 0){
			// На случай, если результат не имеет тип MySQL
			if ( gettype($result) != "boolean" ){
				return mysql_fetch_array($result);
			} else {
				return $result;
			}
		} elseif ($method == 1){
			// Возвращаем одиночную строку
			return mysql_fetch_array($result);
		} elseif ($method == 2){
			return $result;
		}
	} else {
		return false;
	}
}

/* Функция возвращает всю информацию о пользователе */
function GetAllUserData($id){
	global $db_table_accounts, $db_aUserID, $db_aUsername, $db_aEmail, $db_aRegDate, $db_aLvl, $db_aAvatar, $db_table_balance, $db_bUserID, $db_bMoney;

	$result = QueryDB("SELECT * FROM $db_table_accounts WHERE $db_aUserID='$id' LIMIT 1", 1);
	if ($result){
		// Получаем данные о пользователе
		$line = $result;
		$user_data = array("user_id" => $line[$db_aUserID],
						   "user_name" => $line[$db_aUsername],
						   "user_lvl" => $line[$db_aLvl],
						   "user_avatar" => GetUserAvatar($line[$db_aUsername]),
						   "user_email" => $line[$db_aEmail],
						   "user_reg_date" => TimestampToDate($line[$db_aRegDate]),
						   "user_money" => "0");
		// Получаем баланс пользователя
		$result = QueryDB("SELECT $db_bMoney FROM $db_table_balance WHERE $db_bUserID='$id' LIMIT 1", 1);
		if ($result){
			$user_data["user_money"] = $result[$db_bMoney];
		}
		return $user_data; // Возврат массива с данными о пользователе
	} else {
		return false;
	}
}

/* Функция проверяет логин на наличие запрещенных символов */
function ValidLogin($login){
	$pattern = "/[a-zA-Z][a-zA-Z0-9_]+$/";

	if (preg_match($pattern, $login)){
		return true;
	} else {
		return false;
	}
}

/* Функция проверяет пароли на наличие запрещенных символов */
function ValidPassword($pass, $repass){
	$pattern = "/^[a-zA-Z0-9_!\"#$%&'()*+,-.\/:;<=>?@\[\]^_`{|}]+$/";

	if (((preg_match($pattern, $pass)) and (preg_match($pattern, $repass))) or ((preg_match($pattern, $pass))
			and ($repass == "null"))){
		return true;
	} else {
		return false;
	}
}

/* Функция проверяет e-mail на валидность */
function ValidEmail($email){
	// Шаблоны для проверки данных
	$pattern_username = "/^[a-zA-Z0-9][a-zA-Z0-9_.-]+[a-zA-Z0-9]+$/";
	$pattern_hostname = "/^[a-z0-9]{1,}[.]+[a-z]{2,}+$/";

	$res = strpos($email, "@");
	if ($res){
		// Получаем имя пользователя и хоста
		$username = substr($email, 0, $res);
		$hostname = substr($email, $res+1, strlen($email)-$res);

		// Обработка полученных данных
		if (preg_match($pattern_username, $username) and preg_match($pattern_hostname, $hostname)){
			return true;
		} else {
			return false;
		}
	} else {
		return false;
	}
}

/* Функция возвращает расширение файла, полученное из его имени */
function Expansion($filename){
	// Ищем точку, так как после нее находится расширение файла
	$pos = strpos($filename, ".");

	if ($pos){
		return substr($filename, $pos+1, strlen($filename)-$pos);
	} else {
		return false;
	}
}

/* Функция возвращает блок с сообщением об ошибке */
function ShowMessage($message){
	if (!empty($message)){
		return "<div id=\"message\">
						<p>".$message."</p>
					</div>";
	}
}

/* Функция получает время формата h:m из Timestamp */
function TimestampToStr($timestamp){

	for ($i = 0; $i < strlen($timestamp); $i++){
		if ($timestamp{$i} == " "){
			return substr($timestamp, $i+1, 5);
		}
	}
}

/* Функция получает дату из Timestamp */
function TimestampToDate($timestamp){

	// Получаем год, месяц и день из timestamp
	$date = array("year" => substr($timestamp, 0, 4),
		   "month" => substr($timestamp, 5, 2),
		   "day" => substr($timestamp, 8, 2));

	return $date["day"].".".$date["month"].".".$date["year"];
}

/* Функция определяет состояние лотереи */
function CheckLotteryStatus($status){

	switch ($status){
		case 0: return "Не разыгран";
				break;
		case 1: return "Неудача";
				break;
		case 2: return "Выйгрыш";
				break;
	}
}

/* Функция проверяет может ли являться строка целым числом */
function CheckForANumber($input){

	for ($i = 0; $i < strlen($input); $i++){
		if ((ord($input[$i]) < 48) and (ord($input[$i]) > 57)){
			return false;
		}
	}
	return true;
}

/* Функция возвращает части блока постраничной навигации */
function PageNumberBlockBuilder($num, $min, $max, $pages_count){

	$news_block = "";

	// Проверка того, что предыдущий номер не первый
	if ( (($num - 1) != 1) and (($num - 1) > 0) ) {
		$news_block = "<li><a href=\"/news/1/\">1</a></li>\n";
		$news_block .= "<li><span>...</span></li>\n";
	}

	// Вывод основных номеров (включает, текущий, и др.
	for ($i = $min; $i <= $max; $i++){
		if ($i != $num){ // Невыбранная страница
			$news_block .="<li><a href=\"/news/$i/\">$i</a></li>\n";
		} else { // Если выбрана данная страница
			$news_block .= "<li><span class=\"select\">$i</span></li>\n";
		}
	}

	// Проверка того, что следующий номер не последний
	if ( ($num + 1) < $pages_count ){
		$news_block .= "<li><span>...</span></li>\n";
		$news_block .= "<li><a href=\"/news/$pages_count/\">$pages_count</a></li>\n";
	}

	return $news_block;
}

/* Функция вовзращает имя файла представляющего из себя аватар пользователя */
function GetUserAvatar($username){
	global $way_user_avatars;

	if (file_exists($way_user_avatars.$username.".png")){
		return "/".$way_user_avatars.$username.".png";
	} elseif (file_exists($way_user_avatars.$username.".jpg")){
		return "/".$way_user_avatars.$username.".jpg";
	} else {
		return "/".$way_user_avatars."default.jpg";
	}
}

/* Функция строит таблицы из получаемого mysql ответа
	input - mysql ответ, titles - заголовки таблицы, properties - свойства mysql ответа,
	cells_count - количество ячеек таблицы*/
function MySQLTableConstructor($input, $titles, $properties, $cells_count){

	if ($input){

		$table = "<table>
					<tr>\n";
		// Построение заголовков ячеек таблицы
		for($i = 0; $i < $cells_count-1; $i++){
			$table .= "<td>".$titles[$i]."</td>";
		}
		$table .= "</tr>";

		// Построение самой таблицы
		while ($line = mysql_fetch_array($input)){

			$table .= "<tr>";
			for($i = 0; $i < $cells_count-1; $i++){
				$table .= "<td>".$input[ $properties[$i] ]."</td>";
			}
			$table .="</tr>";

		}
		$table .="</table>";

		return $table;

	} else {
		return false;
	}
}

/* Функция обрабатывает текст новостной публикации перед выводом */
function NewsHandler($text){

	// Корректировочный код
	$text = "<p>".$text."</p>";
	$text = str_replace("\r\n\r\n", "</p>\n<p>\n", $text);
	$text = str_replace("\r\n", "<br />\n", $text);

	return $text;
}

/* Функция обрабатывает текст новостной публикации, выдавая краткое описание */
function NewsPostDescription($text){

	// Необходимо ограничить количество отображаемых в описании публикации строк.
	// Определяем длину публикации
	if ( strlen($text) >= 600){
		// Так как публикация содержит нужное кол-во символов - урезаем ее
		$text = substr($text, 0, 600);
	}

	// Здесь должен быть подсчет количества строк в публикации

	// Обработка текста
	$text = "<p>\n".$text."...\n</p>";
	$text = str_replace("\r\n", "<br />\n", $text);

	return $text;
}

/* Функция возвращает группу пользователя по имени */
function GetUserGroup($user_id){
	global $db_table_accounts, $db_aUserID, $db_aLvl, $db_table_user_groups, $db_uGroupsName, $db_uGroupsLvl;

	// Получение данных из БД
	$user_lvl = QueryDB("SELECT $db_aLvl FROM $db_table_accounts WHERE $db_aUserID='$user_id' LIMIT 1", 1);
	$userGroupsList = QueryDB("SELECT $db_uGroupsName, $db_uGroupsLvl FROM $db_table_user_groups", 2);

	if ( gettype($user_lvl) != "boolean" ){

		// Перебор всех групп и поиск нужной
		while($line = mysql_fetch_array($userGroupsList)){

			if ($line["$db_uGroupsLvl"] == $user_lvl["$db_aLvl"]){
				return $line["$db_uGroupsName"];
			}
		}

		return "Ошибка!"; // В случае, если группа с таким уровнем прав не найдена
	} else {
		return "Ошибка!";
	}
}

/* Функция получает хеш-сумму переданной в качестве аргумента строки */
function GetHash($input){
	return md5(md5($input));
}
?>
