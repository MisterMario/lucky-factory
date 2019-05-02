<?php

header("Content Type: text/html; charset=UTF-8");
require_once("config.php");
require_once("functions.php");
require_once("system.php");


$page_name = ""; // имя страницы
$login_form = ""; // форма авторизации в шапке сайта
$content = ""; // контент отображаемый на странице
$message = ""; // Блок информационных сообщений


/*
	Проверка не было ли передано каких-либо данных POST методом
	В данном блоке подключаются обработчики для различных данных.
*/

if ($_SERVER["REQUEST_METHOD"] == "POST"){

	if (isset($_POST["login_name"])){ // Авторизация
		require_once("login.php");
	} elseif (isset($_POST["logout"])){ // Выход из профиля
		require_once("logout.php");
	} elseif (isset($_POST["registration_name"])){ // Регистрация
		require_once("register.php");
	} elseif (isset($_POST["buy_pump"])){ // Приобретение станции в машинном отделении
		require_once($way_account_scripts."buy_pump.php");
	} elseif (isset($_POST["exchange_of_resources"])){ // Фабрика. Переработка воды в луц
		require_once($way_account_scripts."exchange_of_resources.php");
	} elseif (isset($_POST["sale_of_resource"])){ // Луцеколонка. Продажа луца
		require_once($way_account_scripts."sale_of_resource.php");
	} elseif (isset($_POST["get_bonus"])){ // Ежедневный бонус
		require_once($way_account_scripts."get_bonus.php");
	} elseif (isset($_POST["buy_lottery"])){ // Приобретение лотерейного билета
		require_once($way_account_scripts."buy_lottery.php");
	} elseif (isset($_POST["avatar_upload"])){ // Смена автара
		require_once($way_account_scripts."avatar_uploader.php");
	} elseif (isset($_POST["email_change"])){ // Смена e-mail
		require_once($way_account_scripts."email_change.php");
	} elseif (isset($_POST["password_change"])){ // Смена пароля от аккаунта
		require_once($way_account_scripts."password_change.php");
	} elseif (isset($_POST["avatar_load"])){ // Загрузка нового аватара профиля
		require_once($way_account_scripts."avatar_uploader.php");
	}

}


/*
	Построение шапки сайта
*/

// Форма авторизации или мини профиль, в зависимости от того, авторизирован пользователь или нет
ob_start();

if (!$user){
	include $way_style."login_form.html";
	$login_form = ob_get_clean();
} else {
	include $way_style."mini_profile.html";
	$login_form = ob_get_clean();
}

// Построение главного меню (topmenu) при помощи конструктора меню
$topmenu = MenuConstructor($topmenu_arr);


/*
	Построение блока контента
*/

$uri = GetURI(); // Получение чистого URI

ob_start();

if ($uri == "/" or !ComplicatedLink($uri)){ // В случае, если ссылка простая.

	if (PagesCheckerLink($static_pages_arr, $uri)){ // Проверка существования страницы
		$arr_element = PagesElement($static_pages_arr, $uri);
		// Проверка того, что у пользователя есть права на просмотр страницы
		if ($arr_element["lvl"] == 0){ // Если страница общедоступна
			$page_name = SetPageName($arr_element["name"]);
			include $way_style.$arr_element["document"];
			$content = ob_get_clean();
		} elseif ($user){
			if ($user->getLvl() >= $arr_element["lvl"]){ // Если страница для определенных уровней
				$page_name = SetPageName($arr_element["name"]);
				include $way_style.$arr_element["document"];
				$content = ob_get_clean();
			} else { // Если у пользователя не хватает прав для просмотра
				$page_name = SetPageName("Ошибка 403");
				$content = ErorPrint(403);
			}
		} else { // Если пользователь не авторизирован
			$page_name = SetPageName("Ошибка 401");
			$content = ErorPrint(401);
		}
	} else { // Страница не найдена
		$page_name = SetPageName("Ошибка 404");
		$content = ErorPrint(404);
	}

} else { // В случае если ссылка сложная.

	$uri_section = DefinitionOfTheLinkSection($uri);
	$uri_content = DefinitionOfTheLinkContent($uri);

	if ($uri_section == "news"){ // Новостной пост

		// Проверка существования новостного модуля
		if (PagesCheckerLink($static_pages_arr, $uri_section)){

			// Получаем информацию о странице
			$arr_element = PagesElement($static_pages_arr, $uri_section);
			$page_name = SetPageName($arr_element["name"]);

			// Определение каким методм подгружать контент
			if (Expansion($arr_element["document"]) == "html"){ // html
				include $way_style.$arr_element["document"];
				$content .= ob_get_clean();
			} elseif (Expansion($arr_element["document"]) == "php"){ // php
				require_once($arr_element["document"]);
			}

		} else {
			$page_name = SetPageName("Ошибка 404");
			$content = ErorPrint(404);
		}

	} elseif ($uri_section == "account"){ // Одна из страниц аккаунта
		if ($user){ // Проверка того, что пользователь авторизирован

			// Проверка не является ли контент ссылки достаточно сложным
			// Данное условие на случай ссылок формата account/conrol/...
			if ( ComplicatedLink($uri_content) ){
				// Переопределение контента к которому обращался пользователь
				$uri_content = DefinitionOfTheLinkSection($uri_content);
			}

			if (PagesCheckerLink($account_pages_arr, $uri_content)){ // Проверка есть ли такая страница

				// Получаем всю необходимую информацию о странице
				$arr_element = PagesElement($account_pages_arr, $uri_content);

				if ($user->getLvl() >= $arr_element["lvl"]){

					$account_page_name = SetPageName($arr_element["name"]);
					// Получаем пользовательское меню
					$content = AccountMenuConstructor($account_menu_arr, $user->getLvl());

					// Загружаем документ определенным методом в зависимости от его типа
					if (Expansion($arr_element["document"]) == "html"){ // html
						include $way_style.$arr_element["document"];
						$content .= ob_get_clean();
					} elseif (Expansion($arr_element["document"]) == "php"){ // php
						require_once($way_account_scripts.$arr_element["document"]);
					}

				} else {
					$page_name = SetPageName("Ошибка 403");
					$content = ErorPrint(403);
				}
			} else { // Страница не найдена
				$page_name = SetPageName("Ошибка 404");
				$content = ErorPrint(404);
			}

		} else { // Ошибка доступа
			$page_name = SetPageName("Ошибка 401");
			$content = ErorPrint(401);
		}
	} else { // Страница не найдена
		$page_name = SetPageName("Ошибка 404");
		$content = ErorPrint(404);
	}

}


/*
	Вывод сообщений (если были переданы).
	В основном служит для отображени ошибок.

*/

$message = ShowMessage($message);

/*
	Подгрузка готовой страницы
*/

include_once $way_style."index.html"; // Подгружаем страницу

?>
