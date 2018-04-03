DROP TABLE IF EXISTS `featured_playlists`;
DROP TABLE IF EXISTS `featured_albums`;
DROP TABLE IF EXISTS `featured_tracks`;
DROP TABLE IF EXISTS `playlist_entries`;
DROP TABLE IF EXISTS `playlists`;
DROP TABLE IF EXISTS `tracks`;
DROP TABLE IF EXISTS `albums`;
DROP TABLE IF EXISTS `email_verification`;
DROP TABLE IF EXISTS `users`;

CREATE TABLE `users` (
	`id`             INTEGER      NOT NULL AUTO_INCREMENT,
	`username`       VARCHAR(32)  NOT NULL,
	`email`          VARCHAR(256) NOT NULL,
	`email_verified` TINYINT(1)   NOT NULL DEFAULT 0,
	`password_hash`  BINARY(64)   NOT NULL,
	`password_salt`  BINARY(32)   NOT NULL,
	UNIQUE KEY (`username`),
	UNIQUE KEY (`email`),
	PRIMARY KEY (`id`)
);

DROP TABLE IF EXISTS `email_verification`;
CREATE TABLE `email_verification` (
	`id`                INTEGER      NOT NULL AUTO_INCREMENT,
	`user_id`           INTEGER      NOT NULL,
	`verification_code` CHARACTER(8) NOT NULL,
	FOREIGN KEY (`user_id`) REFERENCES `users` (`id`)
		ON DELETE CASCADE,
	UNIQUE KEY (`user_id`),
	UNIQUE KEY (`verification_code`),
	PRIMARY KEY (`id`)
);

DROP TABLE IF EXISTS `albums`;
CREATE TABLE `albums` (
	`id`    INTEGER NOT NULL AUTO_INCREMENT,
	`title` VARCHAR(256),
	PRIMARY KEY (`id`)
);

DROP TABLE IF EXISTS `tracks`;
CREATE TABLE `tracks` (
	`id`       INTEGER      NOT NULL AUTO_INCREMENT,
	`title`    VARCHAR(256) NOT NULL,
	`artist`   VARCHAR(128) NOT NULL,
	`genre`    VARCHAR(64),
	`url`      TEXT,
	`album_id` INTEGER,
	FOREIGN KEY (`album_id`) REFERENCES `albums` (`id`)
		ON DELETE SET NULL,
	PRIMARY KEY (`id`)
);

DROP TABLE IF EXISTS `playlists`;
CREATE TABLE `playlists` (
	`id`         INTEGER NOT NULL AUTO_INCREMENT,
	`title`      VARCHAR(255),
	`creator_id` INTEGER NOT NULL,
	`genre`      VARCHAR(64),
	FOREIGN KEY (`creator_id`) REFERENCES `users` (`id`)
		ON DELETE CASCADE,
	PRIMARY KEY (`id`)
);

CREATE TABLE `playlist_entries` (
	`id`          INTEGER NOT NULL AUTO_INCREMENT,
	`playlist_id` INTEGER NOT NULL,
	`track_id`    INTEGER NOT NULL,
	`index`       INTEGER NOT NULL,
	FOREIGN KEY (`playlist_id`) REFERENCES `playlists` (`id`)
		ON DELETE CASCADE,
	FOREIGN KEY (`track_id`) REFERENCES `tracks` (`id`)
		ON DELETE CASCADE,
	UNIQUE KEY (`playlist_id`, `index`),
	PRIMARY KEY (`id`)
);

CREATE TABLE `featured_tracks` (
	`id`         INTEGER  NOT NULL AUTO_INCREMENT,
	`track_id`   INTEGER  NOT NULL,
	`start_date` DATETIME NOT NULL DEFAULT NOW(),
	`end_date`   DATETIME,
	FOREIGN KEY (`track_id`) REFERENCES `tracks` (`id`)
		ON DELETE CASCADE,
	UNIQUE KEY (`track_id`),
	PRIMARY KEY (`id`)
);

CREATE TABLE `featured_albums` (
	`id`         INTEGER  NOT NULL AUTO_INCREMENT,
	`album_id`   INTEGER  NOT NULL,
	`start_date` DATETIME NOT NULL DEFAULT NOW(),
	`end_date`   DATETIME,
	FOREIGN KEY (`album_id`) REFERENCES `albums` (`id`)
		ON DELETE CASCADE,
	UNIQUE KEY (`album_id`),
	PRIMARY KEY (`id`)
);

CREATE TABLE `featured_playlists` (
	`id`          INTEGER  NOT NULL AUTO_INCREMENT,
	`playlist_id` INTEGER  NOT NULL,
	`start_date`  DATETIME NOT NULL DEFAULT NOW(),
	`end_date`    DATETIME,
	FOREIGN KEY (`playlist_id`) REFERENCES `playlists` (`id`)
		ON DELETE CASCADE,
	UNIQUE KEY (`playlist_id`),
	PRIMARY KEY (`id`)
);
