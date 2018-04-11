<?php
/**
 * @author Caleb Milligan
 * Created 4/4/2018
 */

include_once "MusicPDO.php";
include_once "MusicConstants.php";
include_once "Exceptions.php";

class Album {
	private static $db;
	private $id;
	private $title;
	
	/**
	 * Album constructor.
	 * @param $id int
	 * @param $title string
	 */
	private function __construct($id, $title) {
		$this->id = $id;
		$this->title = $title;
	}
	
	/**
	 * @return int
	 */
	public function getId() {
		return $this->id;
	}
	
	/**
	 * @return string
	 */
	public function getTitle() {
		return $this->title;
	}
	
	/**
	 * @throws PDOException`
	 */
	private static function ensureDatabase() {
		if (!static::$db) {
			static::$db = new MusicPDO(
					MusicConstants::DB_HOST,
					MusicConstants::DB_PORT,
					MusicConstants::DB_USERNAME,
					MusicConstants::DB_PASSWORD,
					MusicConstants::DB_NAME
			);
		}
	}
	
	/**
	 * @param $title string
	 * @throws AlbumExistsException
	 * @throws PDOException
	 */
	public static function createAlbum($title) {
		static::ensureDatabase();
		if (static::albumExists($title)) {
			throw new AlbumExistsException();
		}
		$stmt = static::$db->prepare("INSERT INTO `albums` (`title`) VALUES (:title)");
		$stmt->bindParam(":title", $title, PDO::PARAM_STR);
		$stmt->execute();
	}
	
	/**
	 * @param $title string
	 * @return boolean
	 * @throws PDOException
	 */
	public static function albumExists($title) {
		static::ensureDatabase();
		$stmt = static::$db->prepare("SELECT COUNT(*) FROM `albums` WHERE LOWER(`title`)=LOWER(:title)");
		$stmt->bindParam(":title", $title, PDO::PARAM_STR);
		$stmt->execute();
		$results = $stmt->fetchAll(PDO::FETCH_COLUMN);
		return $results[0] | false;
	}
}