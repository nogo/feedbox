CREATE TABLE IF NOT EXISTS `sources` (
  `id` int(11) AUTO_INCREMENT,
  `name` text NOT NULL,
  `uri` text NOT NULL,
  `icon` text,
  `active` tinyint(1) DEFAULT 1,
  `unread` int(11) DEFAULT 0,
  `errors` text,
  `period` text,
  `last_update` datetime,
  `created_at` datetime,
  `updated_at` datetime,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB;

CREATE TABLE IF NOT EXISTS `items` (
  `id` int(11) AUTO_INCREMENT,
  `source_id` int(11),
  `read` datetime DEFAULT NULL,
  `starred` tinyint(1) DEFAULT 0,
  `title` text NOT NULL,
  `pubdate` datetime NOT NULL,
  `content` text NOT NULL,
  `uid` text NOT NULL,
  `uri` text NOT NULL,
  `created_at` datetime,
  `updated_at` datetime,
  PRIMARY KEY (`id`),
  CONSTRAINT FOREIGN KEY (`source_id`) REFERENCES `sources` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB;
