<?php

function updateSession($con, $user_id, $last_tab, $last_issue) {

	$q = $con->prepare('update Sessions set last_tab = ?, last_issue = ? where user_id = ?');

	$q->execute([$last_tab, $last_issue, $user_id]);

	return true;
}

function clearSession($con, $user_id) {

	return updateSession($con, $user_id, '', '');
}