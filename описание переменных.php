<?php
Список глобальных переменных:

$page_name                                   // имя страницы
$login_form                                  // форма авторизации в шапке сайта
$content                                     // контент отображаемый на странице
$message                                     // Блок информационных сообщений
$user                                        // Класс, представляющий пользователя
$topmenu                                     // Верхнее меню сайта
$message                                     // Информационное сообщение
$topmenu_arr                                 // Массив, содержащий главное меню сайта
$account_menu_arr                            // Массив, содержащий меню личного кабинета пользователя
$static_pages_arr                            // Массив, содержащий статические страницы сайта
$account_pages_arr                           // Массив, содержащий страницы личного кабинета
$control_panel_blocks_arr                    // Массив, содержащий блоки панели управления

$db_host = "127.0.0.1";
$db_user = "root";
$db_pass = "";
$db_name = "lucfactory";

$db_table_accounts                           // таблица содержащая аккаунты пользователей
$db_table_balance                            // таблица содержащая баланс пользователя
$db_table_pumps                              // таблица содержащая насосные станции
$db_table_store                              // таблица представляющая склад игрока
$db_table_bonus                              // таблица содержащая ежедневные бонусы
$db_table_lottery                            // таблица содержащая лотерейные билеты
$db_table_news                               // таблица содержащая новостные публикации

// Поля таблицы с аккаунтами
$db_aUserID
$db_aUsername
$db_aPassword
$db_aEmail
$db_aLvl
$db_aTmp
$db_aRegDate

// Поля таблицы с балансом
$db_bUserID
$db_bMoney

// Поля таблицы с помпами
$db_pID
$db_pCategory
$db_pDescription
$db_pImage
$db_pPrice
$db_pPerformance

// Поля таблицы со складом пользователей
$db_sID
$db_sUserID
$db_sCategory
$db_sCount
$db_sTank
$db_sResource

// Поля таблицы ежедневного бонуса
$db_bonusID
$db_bonusUserID
$db_bonusUsername
$db_bonusSum
$db_bonusTime

// Поля таблицы лотереи
$db_lotteryID
$db_lotteryUserID
$db_lotteryUsername
$db_lotteryDate
$db_lotteryStatus

// Поля таблицы новостей
$db_newsID
$db_newsLink
$db_newsTitle
$db_newsDescription
$db_newsContent;
$db_newsDate
$db_newsAuthor


/* Пути до папок */
$way_style = "/site/";
$way_images = $way_style."images/";
$way_account_scripts = "/account/";
$way_user_avatars = "user_avatars/";
$way_js_scripts = "/js/";
?>