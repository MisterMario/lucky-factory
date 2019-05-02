<?php

/*
	Скрипт загрузчик. Используется для загрузки аватара пользователя на сервер.
*/


require_once("config.php");
require_once("functions.php");


// Проверяем, что пользователь онлайн
if ($user and isset($_FILES["file"]["name"])){
	
	// Проверка того, что файл загружен POST запросом
	if (is_uploaded_file($_FILES["file"]["tmp_name"])){
		
		// Получаем данные о файле
		$filename = $_FILES["file"]["tmp_name"]; // имя файла на сервере
		$exp = Expansion($_FILES["file"]["name"]); // расширение файла
		$file_size = getimagesize($filename);
		
		if ( ($exp == "png") or ($exp == "jpg") ){
			
			if ( filesize($filename) > (1024 * 1024) ){
				
				$message = "Недопустимый размер загружаемого файла!";
				
			} elseif ( ($file_size[0] > 200) or ($file_size[1] > 200) ) {
				
				$message = "Недопустимые размеры изображения!";
				
			} else {
				
				// Создание папки с аватарками, если ее не существует
				if (!is_dir($way_user_avatars)){
					mkdir($way_user_avatars, 0777);
				}
				
				// Перемещение загруженнного файла
				if (move_uploaded_file($filename, $way_user_avatars.$user->getName().".".$exp)){
					
					// Установка прав доступа
					chmod($way_user_avatars.$user->getName().".".$exp, 0777);
					
					// Изменение пользовательской аватарки
					$user->setAvatar($user->getName().".".$exp);
					
					// Сообщение об успехе
					$message = "Файл был успешно загружен на сервер!";
					
				} else {
					$message = "Ошибка при загрузке файла!";
				}
			}
		
		}
	}
}

?>