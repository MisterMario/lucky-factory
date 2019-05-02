<?php

/*

	Данный файл содержит конфигурацию движка.
	Движок разработал Mr.Mario для проекта Luc Factory.
	Все права на данный движок и шаблон сайта принадлежат разработчику.

*/


/*
	Немного о правах доступа:
	0 - прохожий
	1 - заблокированный
	2 - пользователь
	3 - администратор
*/


/* Внешние параметры */

// Меню сайта (topmenu)
$topmenu_arr = array(
	"Главная" => "/",
	"Новости" => "/news/",
	"Рейтинг" => "/rating/",
	"О проекте" => "/about/",
	"Личный кабинет" => "/account/statistics/",
	"Контакты" => "/contacts/",
);

// Меню в личном кабинете
$account_menu_arr = array(
	"Общая статистика" => array("link"=>"/account/statistics/", "lvl"=>"1"),
	"Машинное отделение" => array("link"=>"/account/farm/", "lvl"=>"1"),
	"Фабрика" => array("link"=>"/account/store/", "lvl"=>"1"),
	"Луцеколонка" => array("link"=>"/account/market/", "lvl"=>"1"),
	"Ежедневный бонус" => array("link"=>"/account/bonus/", "lvl"=>"1"),
	"Лотерея" => array("link"=>"/account/lottery/", "lvl"=>"1"),
	"Настройки" => array("link"=>"/account/settings/", "lvl"=>"1"),
	"Управление" => array("link"=>"/account/control/", "lvl"=>"3"),
);

/* Страницы сайта */

// Статические страницы
$static_pages_arr = array(
	"Это не Земля и не Африка, родной. Это планета Плюк, 215 в Тентуре." => array("link"=>"", "document"=>"start.html", "lvl"=>"0"),
	"Новости" => array("link"=>"news", "document"=>"news.php", "lvl"=>"0"),
	"Рейтинг" => array("link"=>"rating", "document"=>"rating.html", "lvl"=>"0"),
	"О проекте" => array("link"=>"about", "document"=>"about.html", "lvl"=>"0"),
	"Личный кабинет" => array("link"=>"account", "document"=>"account_statistics.html", "lvl"=>"1"),
	"Контакты" => array("link"=>"contacts", "document"=>"contacts.html", "lvl"=>"0"),
	"Регистрация"=> array("link"=>"registration", "document"=>"registration.html", "lvl"=>"0"),
);

// Страницы личного кабинета
$account_pages_arr = array(
	"Общая статистика" => array("link"=>"statistics", "document"=>"account_statistics.html", "lvl"=>"1"),
	"Машинное отделение" => array("link"=>"farm", "document"=>"farm.php", "lvl"=>"1"),
	"Фабрика" => array("link"=>"store", "document"=>"store.php", "lvl"=>"1"),
	"Луцеколонка" => array("link"=>"market", "document"=>"market.php", "lvl"=>"1"),
	"Ежедневный бонус" => array("link"=>"bonus", "document"=>"bonus.php", "lvl"=>"1"),
	"Лотерея" => array("link"=>"lottery", "document"=>"lottery.php", "lvl"=>"1"),
	"Настройки" => array("link"=>"settings", "document"=>"account_settings.html", "lvl"=>"1"),
	"Управление" => array("link"=>"control", "document"=>"control_panel.php", "lvl"=>"3"),
);

// Управляющие блоки на панели управления
$control_panel_blocks_arr = array(
	"Новости" => array("link"=>"news", "document"=>"control_news.php", "lvl"=>"3"),
	"Пользователи" => array("link"=>"users", "document"=>"control_users.php", "lvl"=>"3"),
);


/* Данные для доступа к БД */

$db_host = "127.0.0.1";
$db_user = "root";
$db_pass = "";
$db_name = "lucfactory";

$db_table_accounts = "accounts"; // таблица содержащая аккаунты пользователей
$db_table_balance = "balance"; // таблица содержащая баланс пользователя
$db_table_pumps  = "pumps"; // таблица содержащая насосные станции
$db_table_store = "store"; // таблица представляющая склад игрока
$db_table_bonus = "bonus"; // таблица содержащая ежедневные бонусы
$db_table_lottery = "lottery"; // таблица содержащая лотерейные билеты
$db_table_news = "news"; // таблица содержащая новостные публикации
$db_table_user_groups = "user_groups"; // таблица содержащая группы пользователей

// Поля таблицы с аккаунтами
$db_aUserID = "id";
$db_aUsername = "login";
$db_aPassword = "password";
$db_aEmail = "email";
$db_aLvl = "lvl";
$db_aSession = "session";
$db_aTmp = "tmp";
$db_aRegDate = "reg_date";

// Поля таблицы с балансом
$db_bUserID = "id";
$db_bMoney = "money";

// Поля таблицы с помпами
$db_pID = "id";
$db_pCategory = "category";
$db_pDescription = "description";
$db_pImage = "image";
$db_pPrice = "price";
$db_pPerformance = "performance";

// Поля таблицы со складом пользователей
$db_sID = "id";
$db_sUserID = "user_id";
$db_sCategory = "category";
$db_sCount = "count";
$db_sTank = "tank";
$db_sResource = "resource";

// Поля таблицы ежедневного бонуса
$db_bonusID = "id";
$db_bonusUserID = "user_id";
$db_bonusUsername = "username";
$db_bonusSum = "sum";
$db_bonusTime = "time";

// Поля таблицы лотереи
$db_lotteryID = "id";
$db_lotteryUserID = "user_id";
$db_lotteryUsername = "username";
$db_lotteryDate = "date";
$db_lotteryStatus = "status";

// Поля таблицы новостей
$db_newsID = "id";
$db_newsLink = "link";
$db_newsTitle = "title";
$db_newsDescription = "description";
$db_newsContent = "content";
$db_newsDate = "date";
$db_newsAuthor = "author";

// Поля таблицы групп пользователей
$db_uGroupsID = "id";
$db_uGroupsName = "name";
$db_uGroupsLvl = "lvl";


/* Пути до папок */
$way_style = "/site/";
$way_images = $way_style."images/";
$way_account_scripts = "/account/";
$way_user_avatars = "user_avatars/";
$way_js_scripts = "/js/";


/* Прочие настройки */
$illegal_characters = "!@#$%^&*()-/\+][";
$amoutOfTheNewsOnThePage = ""; // количество новостей на странице

?>