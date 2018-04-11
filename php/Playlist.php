<?php
/**
 * @author Caleb Milligan
 * Created 4/4/2018
 */

include_once "MusicPDO.php";
include_once "MusicConstants.php";
include_once "Exceptions.php";

class Playlist {
	private static $db;
	private $id;
	private $title;
	private $artist;
	private $genre;
	private $url;
	private $album_id;
	
	/**
	 * Playlist constructor.
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
	 * @param $track_id int
	 * @param int $index
	 * @throws PDOException
	 */
	public function addTrackToPlaylist($track_id, $index = -1) {
		static::ensureDatabase();
		if ($index < 0) {
			$stmt = static::$db->prepare("SELECT MAX(`index`) FROM `playlist_entries` WHERE `playlist_id`=:playlist_id");
			$stmt->bindParam(":playlist_id", $this->id, PDO::PARAM_INT);
			$stmt->execute();
			$results = $stmt->fetchAll(PDO::FETCH_COLUMN);
			$index = $results[0] + 1;
		}
		$stmt = static::$db->prepare("REPLACE INTO `playlist_entries` (`playlist_id`, `track_id`, `index`) VALUES "
				. "(:playlist_id, :track_id, :index)");
		$stmt->bindParam(":playlist_id", $this->id, PDO::PARAM_INT);
		$stmt->bindParam(":track_id", $track_id, PDO::PARAM_INT);
		$stmt->bindParam(":index", $index, PDO::PARAM_INT);
		$stmt->execute();
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
	 * @param $creator_id int
	 * @param $genre string
	 * @throws PlaylistExistsException
	 * @throws PDOException
	 */
	public static function createPlaylist($title, $creator_id, $genre) {
		static::ensureDatabase();
		if (static::playlistExists($title, $creator_id)) {
			throw new PlaylistExistsException();
		}
		$stmt = static::$db->prepare("INSERT INTO `playlists` (`title`, `creator_id`, `genre`) VALUES (:title, :creator_id, :genre)");
		$stmt->bindParam(":title", $title, PDO::PARAM_STR);
		$stmt->bindParam(":creator_id", $creator_id, PDO::PARAM_INT);
		$stmt->bindParam(":genre", $genre, PDO::PARAM_STR);
		$stmt->execute();
	}
	
	/**
	 * @param $title string
	 * @param $creator_id int
	 * @return boolean
	 * @throws PDOException
	 */
	public static function playlistExists($title, $creator_id) {
		static::ensureDatabase();
		$stmt = static::$db->prepare("SELECT COUNT(*) FROM `playlists` WHERE LOWER(`title`)=LOWER(:title) AND `creator_id`=:creator_id");
		$stmt->bindParam(":title", $title, PDO::PARAM_STR);
		$stmt->bindParam(":creator_id", $creator_id, PDO::PARAM_INT);
		$stmt->execute();
		$results = $stmt->fetchAll(PDO::FETCH_COLUMN);
		return $results[0] | false;
	}
}