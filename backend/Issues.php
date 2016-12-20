<?php

function getIssuesByTabId($con, $tab_id) {

	$q = $con->prepare('select id, title from Issues where recycle = 0 and tab_id = ?');

	$q->execute([$tab_id]);

	return $q->fetchAll();
}

function getIssueById($con, $id) {

	$q = $con->prepare('select title, datestamp, priority, state, user_id, timestamp from Issues where id = ?');

	$q->execute([$id]);

	return $q->fetch();
}

function createIssue($con, $tab_id, $user_id) {

	$q = $con->prepare('insert into issues (tab_id, datestamp, user_id, timestamp) values (?, ?, ?, ?)');

	$q->execute([$tab_id, time(), $user_id, time()]);

	return true;
}

function changeIssueName($con, $id, $title) {

	$q = $con->prepare('update issues set title = ? where id = ?');

	$q->execute([$title, $id]);

	return true;
}

function changeIssueDate($con, $id, $date) {

	$q = $con->prepare('update Issues set datestamp = ? where id = ?');

	$q->execute([$date, $id]);

	return true;
}

function changeIssuePriority($con, $id, $priority) {

	if ((int) $priority < 0 or (int) $priority > 3)
		return false;

	$q = $con->prepare('update Issues set priority = ? where id = ?');

	$q->execute([$priority, $id]);

	return true;
}

function toggleIssueState($con, $id) {

	$q = $con->prepare('update Issues set state = IF(state=1, 0, 1) where id = ?');

	$q->execute([$id]);

	return true;
}

function toggleIssueDelete($con, $id) {

	$q = $con->prepare('update Issues set recycle = IF(recyle=1, 0, 1), recycle_time = (recycle_time=0, ?, 0) where id = ?');

	$q->execute([time(), $id]);

	return true;
}