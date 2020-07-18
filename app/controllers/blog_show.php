<?php
	if (!validateUInt($_GET['id']) || !(WP::checkPostStatus($_GET['id']))) {
		become404Page();
	}

    $blog = queryBlog($_GET['id']);
    $author = $blog["username"];

	redirectTo(UOJConfig::$data['wordpress']['address']."/{$author}/post/".$_GET["id"]);
?>