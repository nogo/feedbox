CREATE TABLE IF NOT EXISTS `version` (
  `key` varchar(255),
  PRIMARY KEY (`key`)
) ENGINE=InnoDB;

INSERT INTO `version` VALUES ('1368823260');
INSERT INTO `version` VALUES ('1372086499');

CREATE TABLE IF NOT EXISTS `settings` (
  `id` int(11) AUTO_INCREMENT,
  `key` varchar(255) NOT NULL,
  `value` text,
  `created_at` datetime,
  `updated_at` datetime,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB;

INSERT INTO `settings` (`key`, `value`, `created_at`, `updated_at`) VALUES ('view.unread.sortby', 'oldest', 'NOW()', 'NOW()');
INSERT INTO `settings` (`key`, `value`, `created_at`, `updated_at`) VALUES ('view.unread.count', '50', 'NOW()', 'NOW()');
INSERT INTO `settings` (`key`, `value`, `created_at`, `updated_at`) VALUES ('view.read.sortby', 'newest', 'NOW()', 'NOW()');
INSERT INTO `settings` (`key`, `value`, `created_at`, `updated_at`) VALUES ('view.read.count', '50', 'NOW()', 'NOW()');
INSERT INTO `settings` (`key`, `value`, `created_at`, `updated_at`) VALUES ('view.starred.sortby', 'newest', 'NOW()', 'NOW()');
INSERT INTO `settings` (`key`, `value`, `created_at`, `updated_at`) VALUES ('view.starred.count', '50', 'NOW()', 'NOW()');


CREATE TABLE IF NOT EXISTS `tags` (
  `id` int(11) AUTO_INCREMENT ,
  `name` varchar(255) NOT NULL UNIQUE,
  `color` varchar(20),
  `unread` int(11) DEFAULT 0,
  `created_at` datetime,
  `updated_at` datetime,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB;

ALTER TABLE `sources` ADD COLUMN `tag_id` int(11);
ALTER TABLE `sources` ADD CONSTRAINT `fk_source_tag` FOREIGN KEY (`tag_id`) REFERENCES `tags` (`id`) ON DELETE SET NULL;