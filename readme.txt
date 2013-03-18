URL Shortener

Copyright (c) 2011 Mark Roland.

Written by Mark Roland, mark [at] mark roland dot com

This code may be distributed and used for free. The author makes
no guarantee for this software and offers no support.

This project is documented at http://markroland.com/project/url-shortener

MySQL Tables:

CREATE TABLE `short_url` (
	`shortcut_id` smallint(3) unsigned NOT NULL AUTO_INCREMENT,
	`shortcut` varchar(32) NOT NULL DEFAULT '',
	`destination_url` varchar(255) NOT NULL DEFAULT '',
	`date_created` datetime NOT NULL DEFAULT '0000-00-00 00:00:00',
	`set_referrer` tinyint(3) unsigned NOT NULL DEFAULT '0',
	PRIMARY KEY (`shortcut_id`),
	UNIQUE KEY `shortcut` (`shortcut`)
) ENGINE=MyISAM AUTO_INCREMENT=4 DEFAULT CHARSET=utf8;

CREATE TABLE `short_url_tracking` (
	`shortcut_id` smallint(3) unsigned DEFAULT '0',
	`request_time` datetime NOT NULL DEFAULT '0000-00-00 00:00:00',
	`ip_address` int(10) unsigned NOT NULL DEFAULT '0',
	`source` varchar(32) NOT NULL DEFAULT ''
) ENGINE=MyISAM DEFAULT CHARSET=utf8;