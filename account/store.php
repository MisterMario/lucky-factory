<?php

/*
	Скрипт для получения контента страницы "account_store"
*/


require_once("config.php");
require_once("functions.php");


$tanks = "";


// Проверяем, что пользователь онлайн
if ($user){
	
	$user_id = $user->getId();
	// Подгружаем необходимые элементы из БД
	$pumps_image_arr = QueryDB("SELECT $db_pImage FROM $db_table_pumps ORDER by $db_pID DESC", 2);
	$tank_arr = QueryDB("SELECT $db_sCategory, $db_sCount, $db_sTank FROM $db_table_store WHERE $db_sUserID='$user_id' ORDER by $db_sCategory DESC ", 2);

	// Вставляем элементы в контент
	while ($row = mysql_fetch_array($pumps_image_arr)){ // перебор массива с информацией о насосах
		$row_tank = mysql_fetch_array($tank_arr); // массив с количеством
		// Проверка были ли переданы результаты переработки ресурсов через POST
		if (isset($_POST["ex_resource_out"][ $row_tank[$db_sCategory] ])){
			$count_resource = $_POST["ex_resource_out"][ $row_tank[$db_sCategory] ];
		} else {
			$count_resource = 0;
		}
		// Формирование страницы
		$tanks .="<div id=\"tank\">
					<div class=\"image\">
						<img src=\"".$way_images.$row[$db_pImage]."\">
					</div>
					<div class=\"description\">
						<p>
							Кол-во станций $row_tank[$db_sCategory] кат.: $row_tank[$db_sCount]<br />
							Воды в баке: $row_tank[$db_sTank] gal<br />
							Луца получено: ".$count_resource." gm<br />
						</p>
					</div>
				</div>";
	}
	include $way_style."account_store.html";
	$content .= ob_get_clean();
}

?>