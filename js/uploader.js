// Функкция реализует нажатие по кнопке "Выбор файла"
function FindFile(){
	document.getElementById("hidden_file").click();
}

// Функция реализует нажатие по кнопке "Submit"
function HiddenLoad(content){
	
	document.getElementById("current_page_content").value = content;
	document.getElementById("hidden_submit").click();
}