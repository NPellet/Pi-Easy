
CREATE TABLE `<?php echo Sql::buildTable(T_CFG_CONFIG); ?>` (
  `key` varchar(100) collate utf8_unicode_ci NOT NULL,
  `label` varchar(250) collate utf8_unicode_ci NOT NULL,
  `value` varchar(250) collate utf8_unicode_ci NOT NULL,
  `idx_group` int(11) NOT NULL,
  `admin` tinyint(1) NOT NULL,
  `type` varchar(20) collate utf8_unicode_ci NOT NULL,
  UNIQUE KEY `key` (`key`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;


CREATE TABLE `<?php echo Sql::buildTable(T_CFG_CATEGORY); ?>` (
  `id` int(11) NOT NULL auto_increment,
  `label` varchar(200) collate utf8_unicode_ci NOT NULL,
  `order` int(11) NOT NULL,
  PRIMARY KEY  (`id`)
) ENGINE=MyISAM  DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;


CREATE TABLE `<?php echo Sql::buildTable(T_CFG_FIELD); ?>` (
  `id` int(11) NOT NULL auto_increment,
  `label` varchar(200) collate utf8_unicode_ci NOT NULL,
  `name` varchar(200) collate utf8_unicode_ci NOT NULL,
  `type` varchar(40) collate utf8_unicode_ci NOT NULL,
  `idx_module` int(11) NOT NULL,
  `idx_group` int(11) NOT NULL,
  `order` int(11) NOT NULL,
  `required` tinyint(1) NOT NULL,
  `multilang` tinyint(1) NOT NULL,
  `rss` tinyint(1) NOT NULL,
  `fblike_fieldtype` varchar(250) collate utf8_unicode_ci NOT NULL, 
  `sortable` tinyint(1) NOT NULL,
  `priority` tinyint(1) NOT NULL,
  `helper` varchar(250) collate utf8_unicode_ci NOT NULL,
  `placeholder` varchar(250) collate utf8_unicode_ci NOT NULL,
  `ext_idx_field_multiple` tinyint(1) NOT NULL,
  `ext_idx_field` varchar(250) collate utf8_unicode_ci NOT NULL,
  `num_float` tinyint(1) NOT NULL,
  `nb_files` tinyint(4) NOT NULL,
  `img_maxi_h` int(5) NOT NULL,
  `img_maxi_w` int(5) NOT NULL,
  `img_miniature` tinyint(1) NOT NULL,
  `img_min_h` int(4) NOT NULL,
  `img_min_w` int(4) NOT NULL,
  `text_mode` varchar(40) collate utf8_unicode_ci NOT NULL,
  `fb_post_infos` varchar(250) collate utf8_unicode_ci NOT NULL,
  `fb_event_infos` varchar(250) collate utf8_unicode_ci NOT NULL,
  PRIMARY KEY  (`id`)
) ENGINE=MyISAM  DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;


CREATE TABLE `<?php echo Sql::buildTable(T_CFG_MODULE); ?>` (
  `id` int(11) NOT NULL auto_increment,
  `label` varchar(200) collate utf8_unicode_ci NOT NULL,
  `name` varchar(200) collate utf8_unicode_ci NOT NULL,
  `idx_category` int(11) NOT NULL,
  `order` int(11) NOT NULL,
  `multilang` tinyint(1) NOT NULL,
  `fblike_objecttype` varchar(250) collate utf8_unicode_ci NOT NULL,
  `sortable` tinyint(1) NOT NULL,
  `rubriques` tinyint(1) NOT NULL,
  `lang1_name` varchar(50) collate utf8_unicode_ci NOT NULL,
  `lang1_abr` varchar(3) collate utf8_unicode_ci NOT NULL,
  `lang2_name` varchar(50) collate utf8_unicode_ci NOT NULL,
  `lang2_abr` varchar(3) collate utf8_unicode_ci NOT NULL,
  `lang3_name` varchar(50) collate utf8_unicode_ci NOT NULL,
  `lang3_abr` varchar(3) collate utf8_unicode_ci NOT NULL,
  PRIMARY KEY  (`id`)
) ENGINE=MyISAM  DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;


CREATE TABLE `<?php echo Sql::buildTable(T_CFG_GROUPFIELDS); ?>` (
  `id` int(11) NOT NULL auto_increment,
  `label` varchar(200) collate utf8_unicode_ci NOT NULL,
  `idx_module` int(11) NOT NULL,
  `order` int(11) NOT NULL,
  PRIMARY KEY  (`id`)
) ENGINE=MyISAM  DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;


CREATE TABLE `<?php echo Sql::buildTable(T_CFG_PLUGINS); ?>` (
  `id` int(11) NOT NULL auto_increment,
  `name` varchar(50) collate utf8_unicode_ci NOT NULL,
  `installed` tinyint(1) NOT NULL,
  PRIMARY KEY  (`id`)
) ENGINE=MyISAM  DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;


CREATE TABLE `<?php echo Sql::buildTable(T_CFG_PLUGINS_PARAMETERS); ?>` (
  `idx_plugin` int(11) NOT NULL,
  `key` varchar(200) collate utf8_unicode_ci NOT NULL,
  `value` varchar(200) collate utf8_unicode_ci NOT NULL,
  PRIMARY KEY  (`idx_plugin`,`key`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;


CREATE TABLE `<?php echo Sql::buildTable(T_CFG_RIGHTS); ?>` (
  `idx_user` int(11) NOT NULL,
  `idx_module` int(11) NOT NULL,
  `mode` varchar(20) collate utf8_unicode_ci NOT NULL,
  `right` tinyint(1) NOT NULL,
  PRIMARY KEY  (`idx_user`,`idx_module`,`mode`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;


CREATE TABLE `<?php echo Sql::buildTable(T_CFG_RUBRIQUE); ?>` (
  `id` int(11) NOT NULL auto_increment,
  `idx_module` int(11) NOT NULL,
  `order` int(11) NOT NULL,
  PRIMARY KEY  (`id`)
) ENGINE=MyISAM  DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;


CREATE TABLE `<?php echo Sql::buildTable(T_CFG_RUBRIQUE_LANGS); ?>` (
  `idx_rubrique` int(11) NOT NULL,
  `lang` varchar(10) collate utf8_unicode_ci NOT NULL,
  `label` varchar(200) collate utf8_unicode_ci NOT NULL,
  PRIMARY KEY  (`idx_rubrique`,`lang`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;


CREATE TABLE `<?php echo Sql::buildTable(T_CFG_SESSIONS); ?>` (
  `sessid` varchar(200) collate utf8_unicode_ci NOT NULL,
  `idx_user` int(11) NOT NULL,
  `timeout` int(10) NOT NULL,
  `ip` varchar(100) collate utf8_unicode_ci NOT NULL,
  PRIMARY KEY  (`sessid`(1)),
  KEY `idx_user` (`idx_user`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;


CREATE TABLE `<?php echo Sql::buildTable(T_CFG_USER); ?>` (
  `id` int(11) NOT NULL auto_increment,
  `username` varchar(100) collate utf8_unicode_ci NOT NULL,
  `password` varchar(100) collate utf8_unicode_ci NOT NULL,
  `email` varchar(100) collate utf8_unicode_ci NOT NULL,
  `admin` tinyint(1) NOT NULL,
  `moderator` tinyint(1) NOT NULL,
  PRIMARY KEY  (`id`)
) ENGINE=MyISAM  DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;


CREATE TABLE `<?php echo Sql::buildTable(T_CFG_BRUTEFORCE); ?>` (
  `ip` varchar(30) NOT NULL,
  `tries` tinyint(1) collate utf8_unicode_ci NOT NULL,
  `last` int(11) NOT NULL,
  PRIMARY KEY  (`ip`)
) ENGINE=MyISAM  DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;

