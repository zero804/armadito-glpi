
DROP TABLE IF EXISTS `glpi_plugin_armadito_armaditos`;

CREATE TABLE `glpi_plugin_armadito_armaditos` (
   `id` int(11) NOT NULL auto_increment,
   `entities_id` int(11) NOT NULL default 0,
   `computers_id` int(11) NOT NULL,
   `plugin_fusioninventory_agents_id` int(11) NOT NULL,
   `version_av` varchar(255) collate utf8_unicode_ci NOT NULL,
   `version_agent` varchar(255) collate utf8_unicode_ci default NULL,
   `agent_port` varchar(6) collate utf8_unicode_ci default NULL,	
   `device_id` varchar(255) collate utf8_unicode_ci default NULL,
   `last_contact` datetime default NULL, 
   PRIMARY KEY (`id`)
) ENGINE=MyISAM  DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;


DROP TABLE IF EXISTS `glpi_plugin_armadito_configs`;

CREATE TABLE `glpi_plugin_armadito_configs` (
   `id` int(11) NOT NULL AUTO_INCREMENT,
   `type` varchar(255) COLLATE utf8_unicode_ci DEFAULT NULL,
   `value` varchar(255) COLLATE utf8_unicode_ci DEFAULT NULL,
   PRIMARY KEY (`id`),
   UNIQUE KEY `unicity` (`type`)
) ENGINE=MyISAM  DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci AUTO_INCREMENT=1;
