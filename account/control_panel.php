<?php

/*
	Скрипт для получения контента страницы "account_panel"
	Скрипт недоработан. Пока, что готовым является только меню блока, остальное еще в разработке.
*/


require_once("config.php");
require_once("functions.php");


$block_menu = "";
$block_content = "";


/* Функция для получения меню управляющих блоков */
function GetConrolBlocksMenu($menu_arr, $selected_element){
	
	$selected_element = DelSlashes($selected_element);
	
	$menu = "<div class=\"head_info_block\">
				<table>
					<tr>";
	while ( $line = current($menu_arr) ){
		
		if ( $line["link"] != $selected_element ){
			$menu .= "<td><a href=\"/account/control/".$line["link"]."/\">".key($menu_arr)."</a></td>\n";
		} else {
			$menu .= "<td><span><a href=\"/account/control/".$line["link"]."/\">".key($menu_arr)."</a></span></td>\n";
		}
		next($menu_arr);
	}
	$menu .= "</tr>
			  </table>
			  </div>";
	
	return $menu;
}


// Проверяем, что пользователь онлайн
if ($user and ($user->getLvl() >= 2)){
	
	// Проверка к какому блоку обращался пользователь
	$uri = GetURI();
	$selected_block = DefinitionOfTheLinkContent($uri);
	
	// В случае если не выбран конкретный блок - загружается первый из них
	if ( ComplicatedLink($selected_block) ){
		$selected_block = DefinitionOfTheLinkContent($selected_block);
	} else {
		$selected_block = "news";
	}
	
	// Проверка существования выбранного блока
	if ( PagesCheckerLink($control_panel_blocks_arr, $selected_block) ){
		
		// Подгрузка информации о блоке
		$arr_element = PagesElement($control_panel_blocks_arr, $selected_block);
		if ( $user->getLvl() >= $arr_element["lvl"] ){
			require_once($arr_element["document"]);
			
			// Подгрузка навигационного меню (или шапки) блока
			$block_menu = GetConrolBlocksMenu($control_panel_blocks_arr, $selected_block);
			
			// Подгрузка страницы с каркасом блока
			include $way_style."account_control.html";
			$content .= ob_get_clean();
		} else {
			$page_name = SetPageName('Ошибка 403');
			$content = ErorPrint(404);
		}
	} else {
		$page_name = SetPageName("Ошибка 404");
		$content = ErorPrint(404);
	}
	
}

?>