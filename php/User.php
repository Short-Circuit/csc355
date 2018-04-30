<?php
/**
 * @author Caleb Milligan
 * Created 3/26/2018
 */

require_once "MusicPDO.php";
require_once "MusicConstants.php";
require_once "Exceptions.php";
require_once "dynamic_call.php";

class User {
	private static $db;
	public $id;
	public $username;
	public $email;
	public $email_verified;
	
	/**
	 * User constructor.
	 * @param $id int
	 * @param $username string
	 * @param $email string
	 * @param $email_verified bool
	 */
	private function __construct(int $id, string $username, string $email, bool $email_verified) {
		$this->id = $id;
		$this->username = $username;
		$this->email = $email;
		$this->email_verified = $email_verified;
	}
	
	/**
	 * @throws PDOException
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
	 * @return int
	 */
	public function getId() {
		return $this->id;
	}
	
	/**
	 * @return string
	 */
	public function getUsername() {
		return $this->username;
	}
	
	/**
	 * @return string
	 */
	public function getEmail() {
		return $this->email;
	}
	
	/**
	 * @return bool
	 */
	public function getEmailVerified() {
		return $this->email_verified;
	}
	
	/**
	 * @param $new_password string
	 * @throws PDOException
	 * @throws Exception
	 */
	public function updatePassword(string $new_password) {
		static::ensureDatabase();
		$salt = random_bytes(32);
		$new_password = static::hashPassword($new_password, $salt);
		$stmt = static::$db->prepare("UPDATE `users` SET `password_hash`=:password_hash, `password_salt`=:password_salt WHERE `id`=:id");
		$stmt->bindParam(":id", $this->id, PDO::PARAM_INT);
		$stmt->bindParam(":password_hash", $new_password, PDO::PARAM_LOB);
		$stmt->bindParam(":password_salt", $salt, PDO::PARAM_LOB);
		$stmt->execute();
	}
	
	/**
	 * @param $new_email string
	 * @throws PDOException
	 * @throws EmailInUseException
	 */
	public function updateEmail(string $new_email) {
		static::ensureDatabase();
		if (static::emailExists($new_email)) {
			throw new EmailInUseException();
		}
		$stmt = static::$db->prepare("UPDATE `users` SET `email`=:email, `email_verified`=0 WHERE `id`=:id");
		$stmt->bindParam(":id", $this->id, PDO::PARAM_INT);
		$stmt->bindParam(":email", $new_email, PDO::PARAM_STR);
		$stmt->execute();
		$this->email = $new_email;
		$this->email_verified = false;
	}
	
	/**
	 * @param $new_username string
	 * @throws PDOException
	 * @throws UsernameInUseException
	 */
	public function updateUsername(string $new_username) {
		static::ensureDatabase();
		if (static::usernameExists($new_username)) {
			throw new UsernameInUseException();
		}
		$stmt = static::$db->prepare("UPDATE `users` SET `username`=:username WHERE `id`=:id");
		$stmt->bindParam(":username", $new_username, PDO::PARAM_STR);
		try {
			$stmt->bindParam(":id", $this->id, PDO::PARAM_INT);
		}
		catch (PDOException $e) {
			if ($e->errorInfo[0] == 23000) {
				throw new UsernameInUseException();
			}
			throw $e;
		}
	}
	
	/**
	 * @throws PDOException
	 */
	public function deleteUser() {
		static::ensureDatabase();
		$stmt = static::$db->prepare("DELETE FROM `users` WHERE `id`=:id");
		$stmt->bindParam(":id", $this->id, PDO::PARAM_INT);
		$stmt->execute();
	}
	
	/**
	 * @param $password string
	 * @param $salt string
	 * @return string
	 */
	public static function hashPassword(string $password, string $salt) {
		return hash_pbkdf2("sha512", $password, $salt, 65535, 64, true);
	}
	
	/**
	 * @param $username string
	 * @param $email string
	 * @param $password string
	 * @throws PDOException
	 * @throws EmailInUseException
	 * @throws UsernameInUseException
	 * @throws Exception
	 */
	public static function createUser(string $username, string $email, string $password) {
		static::ensureDatabase();
		if (static::usernameExists($username)) {
			throw new UsernameInUseException();
		}
		if (static::emailExists($email)) {
			throw new EmailInUseException();
		}
		$salt = random_bytes(32);
		$password = static::hashPassword($password, $salt);
		$stmt = static::$db->prepare("INSERT INTO `users` (`username`, `email`, `password_hash`, `password_salt`) "
				. "VALUES (:username, :email, :password_hash, :password_salt)");
		$stmt->bindParam(":username", $username, PDO::PARAM_STR);
		$stmt->bindParam(":email", $email, PDO::PARAM_STR);
		$stmt->bindParam(":password_hash", $password, PDO::PARAM_LOB);
		$stmt->bindParam(":password_salt", $salt, PDO::PARAM_LOB);
		$stmt->execute();
	}
	
	/**
	 * @param $username_or_email string
	 * @param $password string
	 * @return User
	 * @throws PDOException
	 * @throws InvalidCredentialsException
	 */
	public static function loadUser(string $username_or_email, string $password) {
		static::ensureDatabase();
		$stmt = static::$db->prepare("SELECT `id`, `username`, `email`, `email_verified`, `password_hash`, "
				. "`password_salt` FROM `users` WHERE LOWER(`email`)=LOWER(:username) OR LOWER(`username`)=LOWER(:username)");
		$stmt->bindParam(":username", $username_or_email, PDO::PARAM_STR);
		$stmt->execute();
		$results = $stmt->fetchAll(PDO::FETCH_ASSOC);
		if ($results) {
			$user = $results[0];
			$check_hash = static::hashPassword($password, $user["password_salt"]);
			if ($check_hash != $user["password_hash"]) {
				throw new InvalidCredentialsException();
			}
			return new User($user["id"], $user["username"], $user["email"], $user["email_verified"]);
		}
		throw new InvalidCredentialsException();
	}
	
	/**
	 * @param $username_or_email string
	 * @return bool
	 * @throws PDOException
	 */
	public static function userExists(string $username_or_email) {
		static::ensureDatabase();
		$stmt = static::$db->prepare("SELECT COUNT(*) FROM `users` WHERE LOWER(`email`)=LOWER(:username) OR "
				. "LOWER(`username`)=LOWER(:username)");
		$stmt->bindParam(":username", $username_or_email, PDO::PARAM_STR);
		$stmt->execute();
		$results = $stmt->fetchAll(PDO::FETCH_COLUMN);
		return $results[0] | false;
	}
	
	/**
	 * @param $username string
	 * @return bool
	 * @throws PDOException
	 */
	public static function usernameExists(string $username) {
		static::ensureDatabase();
		$stmt = static::$db->prepare("SELECT COUNT(*) FROM `users` WHERE LOWER(`username`)=LOWER(:username)");
		$stmt->bindParam(":username", $username, PDO::PARAM_STR);
		$stmt->execute();
		$results = $stmt->fetchAll(PDO::FETCH_COLUMN);
		return $results[0] | false;
	}
	
	/**
	 * @param $email string
	 * @return bool
	 * @throws PDOException
	 */
	public static function emailExists(string $email) {
		static::ensureDatabase();
		$stmt = static::$db->prepare("SELECT COUNT(*) FROM `users` WHERE LOWER(`email`)=LOWER(:email)");
		$stmt->bindParam(":email", $email, PDO::PARAM_STR);
		$stmt->execute();
		$results = $stmt->fetchAll(PDO::FETCH_COLUMN);
		return $results[0] | false;
	}
}

dynamicCall(User::class);
