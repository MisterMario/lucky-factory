// Подтверждение очистки новостной ленты
/* Скрипт работает неправильно. Нужно поработать над этим. */
function Confgirmation(){
	
	var answer;
	var sel = document.getElementsByName("action");
	var sel_text = sel.options[sel.selectedIndex].text;
	
	if ( sel_text == "delete_post" ){
		
		answer = confirm("Вы уверены в действиях ?\nСейчас будет очищена вся новостная лента.");
		
		return answer;
		
	} else {
		return false;
	}
	
}