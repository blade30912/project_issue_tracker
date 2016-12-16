<?php

function validateUser($con, $em_us, $password) {

	$q = $con->prepare('select hash from Users where username = ? or email = ?');

	$q->execute([$em_us, $em_us]);

	$qf = $q->fetch();

	if (count($qf) === 0)
		return false;

	return password_verify($qf['hash'], $password);
}

function getUserId($con, $em_us) {

	$q = $con->prepare('select id from Users where username = ? or email = ?');

	$q->execute([$em_us, $em_us]);

	return $q->fetch()['id'];
}

function getUserInfo($con, $id) {

	$q = $con->prepare('select name, username, email, permissions from Users where id = ?');

	$q->execute([$id]);

	return $q->fetch();
}

function getAllUserInfo($con) {

	$q = $con->prepare('select id, name, username, email, permissions from Users');

	$q->execute();

	return $q->fetchAll();
}

function checkUnique($con, $username_email) {

	$q = $con->prepare('select count(email) from Users where email = ? or username = ?');
	$q->execute([$username_email, $username_email]);

	return ($q->fetch()['count'] === 0);
}

function passwordStrength($pword) {}

function createUser($con, $email, $password) {

	$q = $con->prepare('insert into Users (email, hash, username, name) values (?, ?, ?, ?)');

	# validate email is unique
	if (checkUnique($con, $email) !== true)
		return false;

	$username = explode('@', $email)[0];

	# validate username is unique
	if (checkUnique($con, $username) !== true)
		return false;

	$hash = password_hash($password, PASSWORD_DEFAULT);

	$q->execute([$email, $hash, $username, $username]);

	return true;
}

function updateUsersEmail($con, $id, $email) {

	if (filter_var($email, FILTER_VALIDATE_EMAIL) === false or checkUnique($con, $email) === false)
		return false;

	$q = $con->prepare('update user set email = ? where id = ?');

	$q->execute([$email, $id]);

	return true;
}
/*
	TODO : Check difficulty of password
*/
function updateUsersPassword($con, $id, $password) {

	$hash = password_hash($password, PASSWORD_DEFAULT);

	$q = $con->prepare('update user set hash = ? where id = ?');

	$q->execute([$hash, $id]);

	return true;
}

function updateUsersName($con, $id, $name) {

	if (checkUnique($con, $name) === false)
		return false;

	$q = $con->prepare('update user set name = ? where id = ?');

	$q->execute([$name, $id]);

	return true;
}

function updateUsersUsername($con, $id, $username) {

	$q = $con->prepare('update user set username = ? where id = ?');

	$q->execute([$username, $id]);

	return true;
}

function toggleUsersPermission($con, $id) {

	$q = $con->prepare('update user set permission = IF(permission=1, 0, 1) where id = ?');

	$q->execute([$id]);

	return true;
}