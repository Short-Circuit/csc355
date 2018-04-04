<?php
/**
 * @author Caleb Milligan
 * Created 4/4/2018
 */

include_once "MusicPDO.php";
include_once "MusicConstants.php";
include_once "Exceptions.php";

class Track {
	private static $db;
	private $id;
	private $title;
	private $artist;
	private $genre;
	private $url;
	private $album_id;
	
	/**
	 * Track constructor.
	 * @param $id int
	 * @param $title string
	 * @param $artist string
	 * @param $genre string
	 * @param $url string
	 * @param $album_id int
	 */
	private function __construct($id, $title, $artist, $genre, $url, $album_id) {
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
	 * @param $url \http\Url
	 * @throws TrackExistsException
	 */
	public static function createTrack($title, $artist, $genre, $url) {
		static::ensureDatabase();
		if (static::trackExists($title, $artist)) {
			throw new TrackExistsException();
		}
		$stmt = static::$db->prepare("INSERT INTO `tracks` (`title`, `artist`, `genre`, `url`, `album_id`) VALUES "
				. "(:title, :artist, :genre, :url, NULL)");
		$stmt->bindParam(":title", $title, PDO::PARAM_STR);
		$stmt->bindParam(":artist", $artist, PDO::PARAM_STR);
		$stmt->bindParam(":genre", $genre, PDO::PARAM_STR);
		$stmt->bindParam(":url", $url == null ? null : $url->toString(), PDO::PARAM_STR);
		$stmt->execute();
	}
	
	/**
	 * @param $title string
	 * @param $artist string
	 * @return boolean
	 */
	public static function trackExists($title, $artist) {
		static::ensureDatabase();
		$stmt = static::$db->prepare("SELECT COUNT(*) FROM `tracks` WHERE LOWER(`title`)=LOWER(:title) AND LOWER(`genre`)=LOWER(:genre)");
		$stmt->bindParam(":title", $title, PDO::PARAM_STR);
		$stmt->bindParam(":artist", $artist, PDO::PARAM_STR);
		$stmt->execute();
		$results = $stmt->fetchAll(PDO::FETCH_COLUMN);
		return $results[0] | false;
	}
}