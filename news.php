<?php

/*
	Скрипт вывода новостей
	Переработать краткое описание новостей.
*/


require_once("config.php");
require_once("functions.php");


$news = "";
$pages_number = "";


// Проверка к какой секции производилось обращение
if (strpos($uri_content, "post")){ // Конкретная новостная публикация
	// Определение к какому посту обращаются
	$post_num = DefinitionOfTheLinkContent($uri_content);
	$post_num = DelSlashes($post_num);
	
	// Получение информации о посте
	$news_post = QueryDB("SELECT * FROM $db_table_news WHERE $db_newsID='$post_num' LIMIT 1", 1);
	
	if ($news_post){
		
		// Вывод информации о посте
		$news = "<div id=\"news_post_page\">
					<div class=\"post_name\">
						<h2 class=\"name\">$news_post[$db_newsTitle]</h2>
					</div>
					<div class=\"post_content\">\n".
						NewsHandler($news_post[$db_newsContent])
					."\n</div>
					<div class=\"post_info\">
						<p class=\"author\">Автор: ".$news_post[$db_newsAuthor]."</p><p class=\"date\">Дата: ".TimestampToDate($news_post[$db_newsDate])."</p>
					</div>
				  </div>";
		
	} else {
		$page_name = SetPageName("Ошибка 404");
		$content = ErorPrint(404);
	}
} elseif (CheckForANumber(DelSlashes($uri_content)) or ($uri_content == "/")){ // Новостная лента
	
	// Определение к какой странице ленты было обращение
	if ($uri_content == "/"){
		$num = 1;
		$min_num = 1;
	} else {
		// Получение номера страницы
		$num = (int)DelSlashes($uri_content);
		$min_num = ($num * 5) - 4;
	}
	$max_num = $min_num + 4;
	
	// Получение количетсва публикаций
			$news_count = QueryDB("SELECT COUNT(*) FROM $db_table_news", 1);
			$news_count = (int)$news_count[0];
	
	/* Проверка того, что в качестве номера страницы не было передано отрицательное число, или ноль.
	   Или того, что переданной страницы вовсе не может существовать, так как для данного условия 
	   недостаточно публикаций .*/
	if (($min_num > 0) and ($max_num > 0) and ($min_num <= $news_count)){
		
		// Получение публикаций из БД (по номеру записи в таблице)
		$news_arr = array();
		
		for ($i = $min_num-1; $i < $max_num; $i++){
			$news_arr[] = QueryDB("SELECT $db_newsID, $db_newsTitle, $db_newsContent, $db_newsDate FROM $db_table_news LIMIT ".$i.", 1", 1);
		}
		
		// Вывод новостей в контент
		while ($row = current($news_arr)){
			$news .= "<div id=\"news_post\">
						<div class=\"post_name\">
							<h2 class=\"name\"><a href=\"/news/post/$row[$db_newsID]/\">$row[$db_newsTitle]</a></h2>
							<h2 class=\"date\">".TimestampToDate($row[$db_newsDate])."</h2>
						</div>
						<div class=\"post_content\">
							".NewsPostDescription($row[$db_newsContent])."
							<a href=\"/news/post/$row[$db_newsID]/\" class=\"read_more\">Читать далее...</a>
						</div>
					</div>";
					next($news_arr);
		}
	
		// Если новостная лента не пуста - вывод номеров страниц
		if (!empty($news)){
			
			// Расчет количества страниц
			if ($news_count < 5){
				$pages_count = 1;
			} else {
				$pages_count = (int)($news_count / 5);
				if ( ($news_count % 5) != 0 ) $pages_count++;				
			}
			
			// Построение блока постраничной навигации
			$news .= "<div id=\"page_numbers\">
						<ul>\n";
			
			// Построение списка номеров страниц
			if ( ($num != 1) and ($num < $pages_count) ){ // Если номер в средине
				
				$news .= PageNumberBlockBuilder($num, $num-1, $num+1, $pages_count);
				
			} elseif (($num == 1) and ($pages_count == 1)){ // Если только одна страница
				
				$news .= PageNumberBlockBuilder($num, $num, $num, $pages_count);
			
			} elseif (($num != 1) and ($num == $pages_count)){ // Если номер последний
				
				$news .= PageNumberBlockBuilder($num, $num-1, $num, $pages_count);
				
			} elseif (($num == 1) and ($num < $pages_count)){ // Если номер первый, но не последний
				
				$news .= PageNumberBlockBuilder($num, $num, $num+1, $pages_count);
				
			}
			
			$news .= "</ul>
					</div>";
			
		} else {
			$news = "<p>Новостная лента пуста!</p>";
		}
		
	} elseif ($news_count == 0){ // Новостная лента пуста
		$news = "<p>Новостная лента пуста!</p>";
	} else { // Страница не найдена
		$page_name = SetPageName("Ошибка 404");
		$content = ErorPrint(404);
	}
	
} else { // Страница не найдена
	$page_name = SetPageName("Ошибка 404");
	$content = ErorPrint(404);
}


/* Проверка был ли загружен какой-либо контент.
   На тот случай, если произошла ошибка 404. */
if (!empty($news)){
	include $way_style."news.html";
	$content = ob_get_clean();
}

?>