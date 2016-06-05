-- database twindoo_user;

CREATE USER 'twindoo_user'@'%' IDENTIFIED BY 'H{kg-ar]';

GRANT USAGE ON * . * TO 'twindoo_user'@'%' IDENTIFIED BY 'H{kg-ar]' WITH MAX_QUERIES_PER_HOUR 0 MAX_CONNECTIONS_PER_HOUR 0 MAX_UPDATES_PER_HOUR 0 MAX_USER_CONNECTIONS 0 ;

CREATE DATABASE IF NOT EXISTS `twindoo_user` ;

GRANT ALL PRIVILEGES ON `twindoo_user` . * TO 'twindoo_user'@'%';

USE `twindoo_user`;

CREATE TABLE IF NOT EXISTS `USER` (
  `USER_ID` bigint(20) NOT NULL auto_increment,
  `USER_MAIL` varchar(200) NOT NULL,
  `USER_NAME` varchar(200) NOT NULL,
  `USER_FIRSTNAME` varchar(200) NOT NULL,
  `USER_PASSWORD` varchar(32) NOT NULL,
  `USER_IP` varchar(15) NOT NULL,
  `USER_SECRET` varchar(32) NOT NULL,
  `USER_LOGIN` varchar(200) NOT NULL,
  `USER_ACTIVE` varchar(1) NOT NULL,
  `USER_DELETED` bigint(20) NULL,
  PRIMARY KEY  (`USER_ID`)
) ENGINE=InnoDB  DEFAULT CHARSET=utf8 AUTO_INCREMENT=1 ;
