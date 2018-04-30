<?php
/**
 * @author Caleb Milligan
 * Created 4/4/2018
 */

require_once "MusicPDO.php";
require_once "MusicConstants.php";
require_once "Exceptions.php";
require_once "dynamic_call.php";

class Track {
	private static $db;
	public $id;
	public $title;
	public $artist;
	public $genre;
	public $url;
	public $album_id;
	
	/**
	 * Track constructor.
	 * @param $id int
	 * @param $title string
	 * @param $artist string
	 * @param $genre string
	 * @param $url string
	 * @param $album_id int
	 */
	private function __construct(int $id, string $title, string $artist, string $genre, string $url, int $album_id) {
		$this->id = $id;
		$this->title = $title;
		$this->artist = $artist;
		$this->genre = $genre;
		$this->url = $url;
		$this->album_id = $album_id;
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
	 * @return string
	 */
	public function getArtist() {
		return $this->artist;
	}
	
	/**
	 * @return string
	 */
	public function getGenre() {
		return $this->genre;
	}
	
	/**
	 * @return string
	 */
	public function getUrl() {
		return $this->url;
	}
	
	/**
	 * @return int
	 */
	public function getAlbumId() {
		return $this->album_id;
	}
	
	/**
	 * @param $album_id int
	 * @throws PDOException
	 */
	public function setAlbumId(int $album_id) {
		static::ensureDatabase();
		$stmt = static::$db->prepare("UPDATE `tracks` SET `album_id`=:album_id WHERE `id`=:id");
		$stmt->bindParam(":id", $this->id, PDO::PARAM_INT);
		$stmt->bindParam(":album_id", $album_id, PDO::PARAM_INT);
		$stmt->execute();
		$this->album_id = $album_id;
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
	 * @param $artist string
	 * @param $genre string
	 * @param $url \http\Url|string
	 * @throws TrackExistsException
	 * @throws PDOException
	 */
	public static function createTrack(string $title, string $artist, string $genre, $url) {
		static::ensureDatabase();
		if (static::trackExists($title, $artist)) {
			throw new TrackExistsException();
		}
		if ($url instanceof \http\Url) {
			$url = $url->toString();
		}
		$stmt = static::$db->prepare("INSERT INTO `tracks` (`title`, `artist`, `genre`, `url`, `album_id`) VALUES "
				. "(:title, :artist, :genre, :url, NULL)");
		$stmt->bindParam(":title", $title, PDO::PARAM_STR);
		$stmt->bindParam(":artist", $artist, PDO::PARAM_STR);
		$stmt->bindParam(":genre", $genre, PDO::PARAM_STR);
		$stmt->bindParam(":url", $url == null ? null : $url, PDO::PARAM_STR);
		$stmt->execute();
	}
	
	/**
	 * @param $title string
	 * @param $artist string
	 * @return boolean
	 * @throws PDOException
	 */
	public static function trackExists(string $title, string $artist) {
		static::ensureDatabase();
		$stmt = static::$db->prepare("SELECT COUNT(*) FROM `tracks` WHERE LOWER(`title`)=LOWER(:title) AND LOWER(`artist`)=LOWER(:artist)");
		$stmt->bindParam(":title", $title, PDO::PARAM_STR);
		$stmt->bindParam(":artist", $artist, PDO::PARAM_STR);
		$stmt->execute();
		$results = $stmt->fetchAll(PDO::FETCH_COLUMN);
		return $results[0] | false;
	}
	
	/**
	 * @param int $id
	 * @return Track
	 * @throws PDOException
	 */
	public static function getTrack(int $id) {
		static::ensureDatabase();
		$stmt = static::$db->prepare("SELECT * FROM `tracks` WHERE `id`=:id");
		$stmt->bindParam(":id", $id, PDO::PARAM_INT);
		$results = $stmt->fetchAll(PDO::FETCH_ASSOC);
		if ($results) {
			return new Track($results[0]["id"], $results[0]["title"], $results[0]["artist"], $results[0]["genre"], $results[0]["url"], $results[0]["album_id"]);
		}
		return null;
	}
}

dynamicCall(Track::class);
