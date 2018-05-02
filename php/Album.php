<?php
/**
 * @author Caleb Milligan
 * Created 4/4/2018
 */

require_once "MusicPDO.php";
require_once "MusicConstants.php";
require_once "Exceptions.php";
require_once "dynamic_call.php";

class Album {
	private static $db;
	public $id;
	public $title;
	
	/**
	 * Album constructor.
	 * @param $id int
	 * @param $title string
	 */
	private function __construct(int $id, string $title) {
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
	public static function createAlbum(string $title) {
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
	public static function albumExists(string $title) {
		static::ensureDatabase();
		$stmt = static::$db->prepare("SELECT COUNT(*) FROM `albums` WHERE LOWER(`title`)=LOWER(:title)");
		$stmt->bindParam(":title", $title, PDO::PARAM_STR);
		$stmt->execute();
		$results = $stmt->fetchAll(PDO::FETCH_COLUMN);
		return $results[0] | false;
	}
	
	/**
	 * @param int $id
	 * @return Album
	 * @throws PDOException
	 */
	public static function getAlbum(int $id) {
		static::ensureDatabase();
		$stmt = static::$db->prepare("SELECT * FROM `albums` WHERE `id`=:id");
		$stmt->bindParam(":id", $id, PDO::PARAM_INT);
		$stmt->execute();
		$results = $stmt->fetchAll(PDO::FETCH_ASSOC);
		if ($results) {
			return new Album($results[0]["id"], $results[0]["title"]);
		}
		return null;
	}
	
	/**
	 * @return array
	 * @throws PDOException
	 */
	public static function listAlbums() {
		static::ensureDatabase();
		$stmt = static::$db->prepare("SELECT * FROM `albums` ORDER BY `title` ASC");
		$stmt->execute();
		return $stmt->fetchAll(PDO::FETCH_ASSOC);
	}
	
	/**
	 * @param string $title
	 * @return array
	 * @throws PDOException
	 */
	public static function searchAlbums(string $title) {
		static::ensureDatabase();
		$stmt = static::$db->prepare("SELECT * FROM `albums` WHERE LOWER(`title`) LIKE CONCAT('%', LOWER(:title), '%') ORDER BY LOWER(`title`) ASC");
		$stmt->bindParam(":title", $title, PDO::PARAM_STR);
		$stmt->execute();
		return $stmt->fetchAll(PDO::FETCH_ASSOC);
	}
}

dynamicCall(Album::class);
