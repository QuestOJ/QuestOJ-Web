<?php
	if ($myUser == null || !isSuperUser($myUser)) {
		become403Page();
	}
	
	header("Location: ".UOJConfig::$data['manage_platgorm']."/login");
	exit();
?>