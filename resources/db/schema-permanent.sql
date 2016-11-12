DROP TABLE IF EXISTS `report`;
CREATE TABLE `report` (
  `id_report` int(11) NOT NULL AUTO_INCREMENT COMMENT 'id reportu',
  `id_team` int(11) NOT NULL COMMENT 'id tymu dle FKSDB',
  `team` varchar(150) COLLATE utf8_czech_ci NOT NULL COMMENT 'nazev tymu',
  `year_rank` int(11) NOT NULL COMMENT 'rocnik FoLu',
  `text` text COLLATE utf8_czech_ci NOT NULL COMMENT 'obsah reportu',
  `lang` enum('cs','en') COLLATE utf8_czech_ci NOT NULL COMMENT 'jazyk',
  `header` varchar(150) COLLATE utf8_czech_ci DEFAULT NULL COMMENT 'titulek reportu',
  `inserted` datetime NOT NULL COMMENT 'cas vlozeni',
  `updated` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP COMMENT 'cas posledni upravy',
  `publisher` varchar(150) COLLATE utf8_czech_ci DEFAULT NULL COMMENT 'jmeno publikujiciho',
  `published` datetime DEFAULT NULL COMMENT 'cas publikace',
  `year_date` date NOT NULL COMMENT 'datum konani',
  PRIMARY KEY (`id_report`),
  UNIQUE KEY `id_team_year_rank_lang` (`id_team`,`year_rank`,`lang`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_czech_ci COMMENT='reporty tymu';


DROP TABLE IF EXISTS `report_image`;
CREATE TABLE `report_image` (
  `id_report_image` int(11) NOT NULL AUTO_INCREMENT,
  `id_report` int(11) NOT NULL COMMENT 'id reportu',
  `image_hash` char(40) COLLATE utf8_czech_ci NOT NULL COMMENT 'sha1 hash obrazku',
  `caption` varchar(150) COLLATE utf8_czech_ci DEFAULT NULL COMMENT 'popisek obrazku',
  PRIMARY KEY (`id_report_image`),
  UNIQUE KEY `id_report_image_hash` (`id_report`,`image_hash`),
  CONSTRAINT `report_image_ibfk_1` FOREIGN KEY (`id_report`) REFERENCES `report` (`id_report`) ON DELETE NO ACTION
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_czech_ci COMMENT='M:N mapovani obrazku a reportu';
