<?php

namespace IssueApp

class DocumentManager {

	private const $path = '/var/www/documents/';
	private const $con;

	public function __construct($con) {

		$this->con = $con;

		# Move into creating class later on; this is for a reminder
		$this->con->setAttribute(PDO::ATTR_DEFAULT_FETCH_MODE, PDO::FETCH_ASSOC);

	}

	public function getDocument($id, $version=0) {

		if ($version === 0) {
			$q = $this->con->prepare('select mime, path from Attachments where id = ? and version = ?');
			$pl = [$id, $version];
		}
		else {
			$q = $this->con->prepare('select mime from Attachments where id = ? and primary = 1');
			$pl = [$id];
		}

		$q->execute($pl);

		$qf = $q->fetch();

		return [
			'mime'	=>	$qf['mime'],
			'title'	=>	$qf['title'],
			'path'	=>	$this->path+$qf['path']
		];
	}

	public function store($document, $title, $issue_id) {

		$q = $this->con->prepare('insert into Attachments (issue_id, version, title, md5, sha256, path, mime, primary) values (?, ?, ?, ?, ?, ?, ?, ?)');

		$md5 = hash_file('md5', $document);
		$sha256 = hash_file('sha256', $document);
		$path = time()+'-'+$md5;
		$mime = mime_content_type($document);

		$q->execute([$issue_id, 1, $title, $md5, $sha256, $path, $mime, 1]);

		$id = $this->con->lastInsertId();

		rename($document, $this->path+$path);

		return $id;

	}

	public function update($id, $version, $document) {

		$g->$this->con->prepare('select version, issue_id, title from Attachments where id = ?');
		$doc = $g->fetch();

		if ((int) $version !== (int) $doc['version'])
			return false;

		$this->con->beginTransaction();

		$u = $this->con->prepare('update Attachments set primary = 0 where id = ? and version = ?');

		try {
			$u->execute([$id, $version]);
		} catch (Exception $e) {
			$this->con->rollback();
			return false;
		}
		
		$i = $this->con->prepare('insert into Attachments (issue_id, version, title, md5, sha256, path, mime, primary) values (?, ?, ?, ?, ?, ?, ?, ?)');

		$new_version = ((int) $version ) + 1;
		$md5 = hash_file('md5', $document);
		$sha256 = hash_file('sha256', $document);
		$path = time()+'-'+$md5;
		$mime = mime_content_type($document);

		try {
			$i->execute([$doc['issue_id'], $new_version, $doc['title'], $md5, $sha256, $path, $mime, 1]);
		} catch (Exception $e) {
			$this->con->rollback();
			return false;
		}

		$this->con->commit();
		return true;
	}

	public function changeTitle($id, $version, $title) {

		$q = $this->con->prepare('update Attachments set title = ? where id = ? and version = ?');

		try {
			$q->execute([$title, $id, $version]);
		} catch(Exception $e) {
			return false;
		}

		return true;

	}

	public function getHistory($id) {

		$q = $this->con->prepare('select id, version, title, mime, primary from Attachments where id = ?');

		$q->execute([$id]);

		return $q->fetchAll();

	}

	public function delete($id, $version=0) {

		if ($version === 0) {
			$q = $this->con->prepare('update Attachments set recycle = 1, recycle_time = ? where id = ?');
			$pl = [time(), $id];
		}
		else {
			$q = $this->con->prepare('update Attachments set recycle = 1, recycle_time = ? where id = ? and version = ?');
			$pl = [time(), $id, $version];
		}

		$q->execute($pl);

		return true;
	}

	public function clearHistory($id) {

		$q = $this->con->prepare('update Attachments set recycle = 1, recycle_time = ? where id = ? and primary = 0');

		$q->execute(time(), $id);

		return true;
	}
}