<?php

/*
	Скрипт для получения контента страницы "account_farm"
*/


require_once("config.php");
require_once("functions.php");


$pumps = "";


// Проверяем, что пользователь онлайн
if ($user){
	
	$user_id = $user->getId();
	// Подгружаем необходимые элементы из БД
	$pumps_arr = QueryDB("SELECT $db_pCategory, $db_pDescription, $db_pImage, $db_pPrice, $db_pPerformance FROM $db_table_pumps ORDER by $db_pID DESC", 2);
	$count_arr = QueryDB("SELECT $db_sCount FROM $db_table_store WHERE $db_sUserID='$user_id' ORDER by $db_sCategory DESC ", 2);

	// Вставляем элементы в контент
	while ($row = mysql_fetch_array($pumps_arr)){ // перебор массива с информацией о насосах
		$row_count = mysql_fetch_array($count_arr); // массив с количеством
		$pumps .="<div id=\"pump\">
					<div class=\"pump_image\">
						<img src=\"".$way_images.$row["image"]."\">
					</div>
					<div class=\"pump_description\">
						<h2>$row[$db_pCategory] категория<br />
							($row[$db_pDescription])
						</h2>
						<p>
							Производительность: $row[$db_pPerformance] галонов в час<br />
							Стоимость: $row[$db_pPrice] чатлов<br />
							Куплено: $row_count[$db_sCount] шт.
						</p>
						<form method=\"post\" action=\"\">
							<input type=\"hidden\" name=\"category\" value=\"$row[$db_pCategory]\">
							<button name=\"buy_pump\">Приобрести</button>
						</form>
					</div>
				  </div>";
	}
	
	include $way_style."account_farm.html";
	$content .= ob_get_clean();
}

?>