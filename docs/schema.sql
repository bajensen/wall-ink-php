SET FOREIGN_KEY_CHECKS=0;

CREATE TABLE `device` (
  `device_id` int(11) unsigned NOT NULL AUTO_INCREMENT,
  `mac_address` char(12) NOT NULL,
  `orientation` int(11) unsigned NOT NULL DEFAULT '0',
  `batteries_replaced_date` datetime NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `source_plugin` varchar(20) DEFAULT NULL,
  `source_options` text,
  `resource` int(11) DEFAULT NULL,
  `layout_type` varchar(20) DEFAULT NULL,
  `layout_options` text,
  `notes` text,
  `width` int(11) unsigned NOT NULL DEFAULT '640',
  `height` int(11) unsigned NOT NULL DEFAULT '384',
  `is_production` tinyint(1) NOT NULL DEFAULT '1',
  `check_in_id` int(211) unsigned DEFAULT NULL,
  PRIMARY KEY (`device_id`),
  UNIQUE KEY `mac_address` (`mac_address`),
  KEY `check_in_id` (`check_in_id`),
  CONSTRAINT `device_ibfk_1` FOREIGN KEY (`check_in_id`) REFERENCES `check_in` (`check_in_id`) ON DELETE SET NULL ON UPDATE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=latin1;

CREATE TABLE `check_in` (
  `check_in_id` int(11) unsigned NOT NULL AUTO_INCREMENT,
  `device_id` int(11) unsigned NOT NULL,
  `check_in_dt` datetime NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `next_check_in_dt` datetime DEFAULT NULL,
  `voltage` float NOT NULL DEFAULT '0',
  `firmware_version` varchar(10) DEFAULT NULL,
  `error_code` int(11) NOT NULL DEFAULT '0',
  `remote_address` varchar(40) DEFAULT NULL,
  PRIMARY KEY (`check_in_id`),
  KEY `device_id` (`device_id`),
  CONSTRAINT `check_in_ibfk_1` FOREIGN KEY (`device_id`) REFERENCES `device` (`device_id`) ON DELETE CASCADE ON UPDATE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=latin1;

SET FOREIGN_KEY_CHECKS=1;