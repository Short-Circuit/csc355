<?php
/**
 * @author Caleb Milligan
 * Created 3/21/2018
 */
require_once "MusicConstants.php";

class MusicPDO extends PDO {
	public function __construct(string $host, int $port, string $username, string $password, string $database) {
		parent::__construct("mysql:host=$host;port=$port;dbname=$database;", $username, $password, array(PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION));
	}
}
