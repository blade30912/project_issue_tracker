<?php

# 5 min
define('EXP_OFFSET', 300);

function processToken($con, $token) {

	$q = $con->prepare('select Tokens.user_id, Users.permission, Tokens.expiry, Tokens.cookie from Tokens, Users where Tokens.user_id = Users.id and Tokens.token = ?');

	$q->execute([$token]);

	$qf = $q->fetch();

	if($qf === null or $qf['expiry'] < time())
		return null;
	else
		return $qf['user_id'];
}

function generateToken($con, $user_id, $cookie) {

	$q = $con->prepare('insert into Tokens (token, user_id, cookie, expiry) values (?, ?, ?, ?)');

	$token = bin2hex(random_bytes($length));
	$expiry = time() + EXP_OFFSET;

	$q->execute([$token, $user_id, $cookie, $expiry]);

	return ['token'	=>	$token, 'expiry' =>	$expiry];
}
