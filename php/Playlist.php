<?php
/**
 * @author Caleb Milligan
 * Created 4/4/2018
 */

require_once "MusicPDO.php";
require_once "MusicConstants.php";
require_once "Exceptions.php";
require_once "dynamic_call.php";

class Playlist {
	private static $db;
	public $id;
	public $title;
	public $creator_id;
	public $genre;
	
	/**
	 * Playlist constructor.
	 * @param $id int
	 * @param $title string
	 * @param int $creator_id
	 * @param $genre string
	 */
	private function __construct(int $id, string $title, int $creator_id, string $genre) {
		$this->id = $id;
		$this->title = $title;
		$this->genre = $genre;
		$this->creator_id = $creator_id;
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
	 * @return int
	 */
	public function getCreatorId() {
		return $this->creator_id;
	}
	
	/**
	 * @return string
	 */
	public function getGenre() {
		return $this->genre;
	}
	
	/**
	 * @param $track_id int
	 * @param int $index
	 * @throws PDOException
	 */
	public function addTrackToPlaylist(int $track_id, int $index = -1) {
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
	public static function createPlaylist(string $title, int $creator_id, string $genre) {
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
	public static function playlistExists(string $title, int $creator_id) {
		static::ensureDatabase();
		$stmt = static::$db->prepare("SELECT COUNT(*) FROM `playlists` WHERE LOWER(`title`)=LOWER(:title) AND `creator_id`=:creator_id");
		$stmt->bindParam(":title", $title, PDO::PARAM_STR);
		$stmt->bindParam(":creator_id", $creator_id, PDO::PARAM_INT);
		$stmt->execute();
		$results = $stmt->fetchAll(PDO::FETCH_COLUMN);
		return $results[0] | false;
	}
	
	/**
	 * @param int $id
	 * @return Playlist
	 * @throws PDOException
	 */
	public static function getPlaylist(int $id) {
		static::ensureDatabase();
		$stmt = static::$db->prepare("SELECT * FROM `playlists` WHERE `id`=:id");
		$stmt->bindParam(":id", $id, PDO::PARAM_INT);
		$results = $stmt->fetchAll(PDO::FETCH_ASSOC);
		if ($results) {
			return new Playlist($results[0]["id"], $results[0]["title"], $results[0]["creator_id"], $results[0]["genre"]);
		}
		return null;
	}
}

dynamicCall(Playlist::class);
