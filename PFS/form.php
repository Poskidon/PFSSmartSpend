<?php
	if(count($_POST)>0){
		foreach($_POST as $key => $val)
			${$key}=trim($val);
	}	
	else{
		foreach($_GET as $key => $val)
			${$key}=trim($val);
	}
?>