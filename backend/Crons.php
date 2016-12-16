<?php

define('PATH', '/var/www/documents/');
define('THIRTY_DAYS', 2592000);

$con = new PDO();
$con->setAttribute(PDO::ATTR_DEFAULT_FETCH_MODE, PDO::FETCH_ASSOC);

define('TIME', time());

# Clear expired tokens

$d = $con->prepare('delete from Token where expiry < ?');

$d->execute([TIME]);

# Clear THIRTY_DAYS old recycled documents
$con->beginTransaction();

$g = $con->prepare('select path from Attachments where recycle = 1 and recycle_time < ?');
$d = $con->prepare('delete from Attachments where recycle = 1 and recycle_time < ?');

$g->execute([TIME-THIRTY_DAYS]);

try {

	$d->execute([TIME-THIRTY_DAYS]);

	while($row = $g->fetch())
		unlink(PATH+$row['path']);

} catch (Exception $e) {

	$con->rollback();
}

# Should be able to commit 'nothing' should rollback fail
$con->commit();

