<?php

function hasProblemPermission($user, $problem) {
	if ($user == null) {
		return false;
	}
	if ($problem['is_contest'] == false && isSuperUser($user)) {
		return true;
	}
	
	return DB::selectFirst("select * from problems_permissions where username = '{$user['username']}' and problem_id = {$problem['id']}") != null;
}

function hasProblemViewPermission($user, $problem){
	if ($problem['is_hidden'])
		return false;
	
	if ($problem['is_all'])
		return true;

	return DB::selectFirst("select * from problems_auth where pid = {$problem['id']} and gid = {$user['userdefine']}");	
}

function hasViewPermission($str,$user,$problem,$submission) {
	if($str=='ALL')
		return true;
	if($str=='ALL_AFTER_AC')
		return hasAC($user,$problem);
	if($str=='SELF')
		return $submission['submitter']==$user['username'];
	return false;
}

function hasContestPermission($user, $contest) {
	if ($user == null) {
		return false;
	}
	if ($contest['creator'] == $user['username']) {
		return true;
	}
	return DB::selectFirst("select * from contests_permissions where username = '{$user['username']}' and contest_id = {$contest['id']}") != null;
}

function hasRegistered($user, $contest) {
	return DB::fetch(DB::query("select * from contests_registrants where username = '${user['username']}' and contest_id = ${contest['id']}")) != null;
	}
function hasAC($user, $problem) {
	return DB::fetch(DB::query("select * from best_ac_submissions where submitter = '${user['username']}' and problem_id = ${problem['id']}")) != null;
}

function queryGroup($groupname){
	if(!validateString($groupname)){
		return null;
	}
	return DB::selectAll("select * from usergroup where name = '$groupname'", MYSQL_ASSOC);
}

function queryUser($username) {
	if (!validateUsername($username)) {
		return null;
	}
	return DB::selectFirst("select * from user_info where username='$username'", MYSQL_ASSOC);
}

function queryProblemContent($id) {
	return DB::fetch(DB::query("select * from problems_contents where id = $id"), MYSQL_ASSOC);
}
function queryProblemBrief($id) {
	return DB::fetch(DB::query("select * from problems where id = $id"), MYSQL_ASSOC);
}

function queryProblemTags($id) {
	$tags = array();
	$result = DB::query("select tag from problems_tags where problem_id = $id order by id");
	while ($row = DB::fetch($result, MYSQL_NUM)) {
		$tags[] = $row[0];
	}
	return $tags;
}
function queryContestProblemRank($contest, $problem) {
	if (!DB::selectFirst("select * from contests_problems where contest_id = {$contest['id']} and problem_id = {$problem['id']}")) {
		return null;
	}
	return DB::selectCount("select count(*) from contests_problems where contest_id = {$contest['id']} and problem_id <= {$problem['id']}");
}
function querySubmission($id) {
	return DB::fetch(DB::query("select * from submissions where id = $id"), MYSQL_ASSOC);
}
function queryHack($id) {
	return DB::fetch(DB::query("select * from hacks where id = $id"), MYSQL_ASSOC);
}
function queryContest($id) {
	return DB::fetch(DB::query("select * from contests where id = $id"), MYSQL_ASSOC);
}
function queryContestProblem($id) {
	return DB::fetch(DB::query("select * from contest_problems where contest_id = $id"), MYSQL_ASSOC);
}

function queryZanVal($id, $type, $user) {
	if ($user == null) {
		return 0;
	}
	$esc_type = DB::escape($type);
	$row = DB::fetch(DB::query("select val from click_zans where username='{$user['username']}' and type='$esc_type' and target_id='$id'"));
	if ($row == null) {
		return 0;
	}
	return $row['val'];
}

function queryBlog($id) {
	return DB::fetch(DB::query("select * from blogs where id='$id'"), MYSQL_ASSOC);
}
function queryBlogTags($id) {
	$tags = array();
	$result = DB::select("select tag from blogs_tags where blog_id = $id order by id");
	while ($row = DB::fetch($result, MYSQL_NUM)) {
		$tags[] = $row[0];
	}
	return $tags;
}
function queryBlogComment($id) {
	return DB::fetch(DB::query("select * from blogs_comments where id='$id'"), MYSQL_ASSOC);
}

function isProblemVisibleToUser($problem, $user) {
	return hasProblemViewPermission($user, $problem) || hasProblemPermission($user, $problem);
}
function isContestProblemVisibleToUser($problem, $contest, $user) {
	if (isProblemVisibleToUser($problem, $user)) {
		return true;
	}
	if ($contest['cur_progress'] >= CONTEST_PENDING_FINAL_TEST) {
		return true;
	}
	if ($contest['cur_progress'] == CONTEST_NOT_STARTED) {
		return false;
	}
	return hasRegistered($user, $contest);
}

function isSubmissionVisibleToUser($submission, $problem, $user) {
	if (isSuperUser($user)) {
		return true;
	} else if (!$submission['is_hidden']) {
		return true;
	} else {
		return hasProblemPermission($user, $problem);
	}
}
function isHackVisibleToUser($hack, $problem, $user) {
	if (isSuperUser($user)) {
		return true;
	} elseif (!$hack['is_hidden']) {
		return true;
	} else {
		return hasProblemPermission($user, $problem);
	}
}

function isSubmissionFullVisibleToUser($submission, $contest, $problem, $user) {
	if (isSuperUser($user)) {
		return true;
	} elseif (!$contest) {
		return true;
	} elseif ($contest['cur_progress'] > CONTEST_IN_PROGRESS) {
		return true;
	} elseif ($submission['submitter'] == $user['username']) {
		return true;
	} else {
		return hasProblemPermission($user, $problem);
	}
}
function isHackFullVisibleToUser($hack, $contest, $problem, $user) {
	if (isSuperUser($user)) {
		return true;
	} elseif (!$contest) {
		return true;
	} elseif ($contest['cur_progress'] > CONTEST_IN_PROGRESS) {
		return true;
	} elseif ($hack['hacker'] == $user['username']) {
		return true;
	} else {
		return hasProblemPermission($user, $problem);
	}
}

function deleteBlog($id) {
	if (!validateUInt($id)) {
		return;
	}
	DB::delete("delete from click_zans where type = 'B' and target_id = $id");
	DB::delete("delete from click_zans where type = 'BC' and target_id in (select id from blogs_comments where blog_id = $id)");
	DB::delete("delete from blogs where id = $id");
	DB::delete("delete from blogs_comments where blog_id = $id");
	DB::delete("delete from important_blogs where blog_id = $id");
	DB::delete("delete from blogs_tags where blog_id = $id");
}

