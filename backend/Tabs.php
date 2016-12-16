<?php

function getTabs($con) {

	$q = $con->prepare('select id, name, ord from Tabs where recycle = 0');
}

function createTab($con, $user_id, $name) {
	
	$q = $con->prepare('insert into Tabs (name, user_id, timestamp) values (?, ?, ?)');

	$q->execute([$name, $user_id, time()]);

	return $con->lastInsertId();
}

function updateTabPrefix($con, $id, $prefix) {

	if (count($prefix) !== 3)
		return false;

	$q = $con->prepare('update Tabs set prefix = ? where id = ? and recycle = 0');

	$q->execute([$prefix, $id]);

	return true;
}

function updateTabName($con, $id, $name) {

	$q = $con->prepare('update Tabs set name = ? where id = ? and recycle = 0');

	$q->execute([$name, $id]);

	return true;
}

# Want to do a meta table?
function applyOrder($con, $ids) {

	$q = $con->prepare('update Tabs set ord = ? where id = ?');

	$con->beginTransaction();

	try {
		for($i=0; $i<count($ids); $i++)
			$q->execute([$i, $id]);
	} catch (Exception $e) {
		$con->rollback();
		return false;
	}

	$con->commit();

	return true;
}

function toggleTabDelete($con, $id) {

	$q = $con->prepare('update Tabs set recycle = IF(recyle=1, 0, 1), recycle_time = (recycle_time=0, ?, 0) where id = ?');

	$q->execute([time(), $id]);

	return true;
}