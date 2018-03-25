DROP TABLE IF EXISTS `users`;
CREATE TABLE `users` (
	`id`             INTEGER      NOT NULL AUTO_INCREMENT,
	`username`       VARCHAR(32)  NOT NULL,
	`email`          VARCHAR(256) NOT NULL,
	`email_verified` TINYINT(1)   NOT NULL DEFAULT 0,
	UNIQUE KEY (`username`),
	PRIMARY KEY (`id`)
);

DROP TABLE IF EXISTS `email_verification`;
CREATE TABLE `email_verification` (
	`id`                INTEGER      NOT NULL AUTO_INCREMENT,
	`user_id`           INTEGER      NOT NULL,
	`verification_code` CHARACTER(8) NOT NULL,
	FOREIGN KEY (`user_id`) REFERENCES `users` (`id`),
	UNIQUE KEY (`user_id`),
	UNIQUE KEY (`verification_code`),
	PRIMARY KEY (`id`)
);

DROP TABLE IF EXISTS `albums`;
CREATE TABLE `albums` (
	`id`    INTEGER NOT NULL AUTO_INCREMENT,
	`title` VARCHAR(256)
);

DROP TABLE IF EXISTS `tracks`;
CREATE TABLE `tracks` (
	`id`       INTEGER      NOT NULL AUTO_INCREMENT,
	`title`    VARCHAR(256) NOT NULL,
	`artist`   VARCHAR(128) NOT NULL,
	`genre`    VARCHAR(64),
	`album_id` INTEGER,
	FOREIGN KEY (`album_id`) REFERENCES `albums` (`id`),
	PRIMARY KEY (`id`)
);

DROP TABLE IF EXISTS `playlists`;
CREATE TABLE `playlists` (
	`id`         INTEGER NOT NULL AUTO_INCREMENT,
	`title`      VARCHAR(255),
	`creator_id` INTEGER NOT NULL,
	`genre`      VARCHAR(64),
	FOREIGN KEY (`creator_id`) REFERENCES `users` (`id`),
	PRIMARY KEY (`id`)
);

DROP TABLE IF EXISTS `playlist_entries`;
CREATE TABLE `playlist_entries` (
	`id`          INTEGER NOT NULL AUTO_INCREMENT,
	`playlist_id` INTEGER NOT NULL,
	`track_id`    INTEGER NOT NULL,
	`index`       INTEGER NOT NULL,
	FOREIGN KEY (`playlist_id`) REFERENCES `playlists` (`id`),
	FOREIGN KEY (`track_id`) REFERENCES `tracks` (`id`),
	UNIQUE KEY (`playlist_id`, `index`),
	PRIMARY KEY (`id`)
);
