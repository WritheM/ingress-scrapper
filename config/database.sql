-- phpMyAdmin SQL Dump
-- version 3.5.4
-- http://www.phpmyadmin.net
--
-- Host: memoria
-- Generation Time: Feb 26, 2013 at 10:37 PM
-- Server version: 5.1.63-0ubuntu0.11.10.1
-- PHP Version: 5.3.10-1ubuntu3.5

SET SQL_MODE="NO_AUTO_VALUE_ON_ZERO";
SET time_zone = "+00:00";

--
-- Database: `ingress`
--

-- --------------------------------------------------------

--
-- Table structure for table `api`
--

CREATE TABLE IF NOT EXISTS `api` (
  `key` varchar(32) NOT NULL,
  `hits` int(11) NOT NULL DEFAULT '0',
  `last_hit` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  `region` int(10) NOT NULL DEFAULT '1',
  `name` varchar(255) DEFAULT NULL,
  `email` text,
  `password` varchar(32) DEFAULT NULL,
  PRIMARY KEY (`key`)
) ENGINE=MyISAM DEFAULT CHARSET=latin1;

-- --------------------------------------------------------

--
-- Table structure for table `break_log`
--

CREATE TABLE IF NOT EXISTS `break_log` (
  `guid` varchar(40) NOT NULL,
  `user` varchar(40) NOT NULL,
  `portal1` varchar(40) NOT NULL,
  `portal2` varchar(40) NOT NULL,
  `datetime` int(11) DEFAULT NULL,
  `lastupdate` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  `region` int(10) NOT NULL DEFAULT '1',
  PRIMARY KEY (`guid`)
) ENGINE=MyISAM DEFAULT CHARSET=latin1;

-- --------------------------------------------------------

--
-- Table structure for table `capture_log`
--

CREATE TABLE IF NOT EXISTS `capture_log` (
  `guid` varchar(40) NOT NULL,
  `user` varchar(40) NOT NULL,
  `portal` varchar(40) NOT NULL,
  `datetime` int(11) DEFAULT NULL,
  `lastupdate` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  `region` int(10) NOT NULL DEFAULT '1',
  PRIMARY KEY (`guid`)
) ENGINE=MyISAM DEFAULT CHARSET=latin1;

-- --------------------------------------------------------

--
-- Table structure for table `chat_log`
--

CREATE TABLE IF NOT EXISTS `chat_log` (
  `guid` varchar(40) NOT NULL,
  `datetime` int(10) unsigned DEFAULT NULL,
  `user` varchar(40) NOT NULL,
  `text` text NOT NULL,
  `secure` tinyint(1) NOT NULL,
  `lastupdated` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  `region` int(10) NOT NULL DEFAULT '1',
  PRIMARY KEY (`guid`)
) ENGINE=MyISAM DEFAULT CHARSET=latin1;

-- --------------------------------------------------------

--
-- Table structure for table `control_log`
--

CREATE TABLE IF NOT EXISTS `control_log` (
  `guid` varchar(40) NOT NULL,
  `user` varchar(40) NOT NULL,
  `portal` varchar(40) NOT NULL,
  `mus` int(11) DEFAULT '0',
  `datetime` int(11) DEFAULT NULL,
  `lastupdate` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  `region` int(10) NOT NULL DEFAULT '1',
  PRIMARY KEY (`guid`)
) ENGINE=MyISAM DEFAULT CHARSET=latin1;

-- --------------------------------------------------------

--
-- Table structure for table `fielddecay_log`
--

CREATE TABLE IF NOT EXISTS `fielddecay_log` (
  `guid` varchar(40) NOT NULL,
  `portal` varchar(40) NOT NULL,
  `mus` int(11) DEFAULT '0',
  `datetime` int(11) DEFAULT NULL,
  `lastupdate` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  `region` int(10) NOT NULL DEFAULT '1',
  PRIMARY KEY (`guid`)
) ENGINE=MyISAM DEFAULT CHARSET=latin1;

-- --------------------------------------------------------

--
-- Table structure for table `linkdecay_log`
--

CREATE TABLE IF NOT EXISTS `linkdecay_log` (
  `guid` varchar(40) NOT NULL,
  `portal1` varchar(40) NOT NULL,
  `portal2` varchar(40) NOT NULL,
  `datetime` int(11) DEFAULT NULL,
  `lastupdate` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  `region` int(10) NOT NULL DEFAULT '1',
  PRIMARY KEY (`guid`)
) ENGINE=MyISAM DEFAULT CHARSET=latin1;

-- --------------------------------------------------------

--
-- Table structure for table `deploy_log`
--

CREATE TABLE IF NOT EXISTS `deploy_log` (
  `guid` varchar(40) NOT NULL,
  `user` varchar(40) NOT NULL,
  `portal` varchar(40) NOT NULL,
  `res` varchar(10) NOT NULL,
  `datetime` int(11) DEFAULT NULL,
  `lastupdate` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  `region` int(10) NOT NULL DEFAULT '1',
  PRIMARY KEY (`guid`)
) ENGINE=MyISAM DEFAULT CHARSET=latin1;

-- --------------------------------------------------------

--
-- Table structure for table `destroy_log`
--

CREATE TABLE IF NOT EXISTS `destroy_log` (
  `guid` varchar(40) NOT NULL,
  `user` varchar(40) NOT NULL,
  `portal` varchar(40) NOT NULL,
  `res` varchar(10) NOT NULL,
  `datetime` int(11) DEFAULT NULL,
  `lastupdate` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  `region` int(10) NOT NULL DEFAULT '1',
  PRIMARY KEY (`guid`)
) ENGINE=MyISAM DEFAULT CHARSET=latin1;

-- --------------------------------------------------------

--
-- Table structure for table `liberate_log`
--

CREATE TABLE IF NOT EXISTS `liberate_log` (
  `guid` varchar(40) NOT NULL,
  `user` varchar(40) NOT NULL,
  `portal` varchar(40) NOT NULL,
  `mus` int(11) DEFAULT '0',
  `datetime` int(11) DEFAULT NULL,
  `lastupdate` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  `region` int(10) NOT NULL DEFAULT '1',
  PRIMARY KEY (`guid`)
) ENGINE=MyISAM DEFAULT CHARSET=latin1;

-- --------------------------------------------------------

--
-- Table structure for table `linked_log`
--

CREATE TABLE IF NOT EXISTS `linked_log` (
  `guid` varchar(40) NOT NULL,
  `user` varchar(40) NOT NULL,
  `portal1` varchar(40) NOT NULL,
  `portal2` varchar(40) NOT NULL,
  `datetime` int(11) DEFAULT NULL,
  `lastupdate` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  `region` int(10) NOT NULL DEFAULT '1',
  PRIMARY KEY (`guid`)
) ENGINE=MyISAM DEFAULT CHARSET=latin1;

-- --------------------------------------------------------

--
-- Table structure for table `players`
--

CREATE TABLE IF NOT EXISTS `players` (
  `guid` varchar(40) NOT NULL,
  `name` varchar(255) NOT NULL,
  `team` varchar(20) NOT NULL,
  `lastupdate` timestamp NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  `region` int(10) NOT NULL DEFAULT '1',
  PRIMARY KEY (`guid`)
) ENGINE=MyISAM DEFAULT CHARSET=latin1;

-- --------------------------------------------------------

--
-- Table structure for table `pmoddestroy_log`
--

CREATE TABLE IF NOT EXISTS `pmoddestroy_log` (
  `guid` varchar(40) NOT NULL,
  `user` varchar(40) NOT NULL,
  `portal` varchar(40) NOT NULL,
  `mod` varchar(20) NOT NULL,
  `datetime` int(10) NOT NULL,
  `lastupdate` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  `region` int(10) NOT NULL,
  PRIMARY KEY (`guid`)
) ENGINE=MyISAM DEFAULT CHARSET=latin1;

-- --------------------------------------------------------

--
-- Table structure for table `portals`
--

CREATE TABLE IF NOT EXISTS `portals` (
  `guid` varchar(40) NOT NULL,
  `address` text,
  `latE6` varchar(15) NOT NULL,
  `lngE6` varchar(15) NOT NULL,
  `name` varchar(255) NOT NULL,
  `team` varchar(20) DEFAULT NULL,
  `lastupdate` timestamp NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  `region` int(10) NOT NULL DEFAULT '1',
  PRIMARY KEY (`guid`)
) ENGINE=MyISAM DEFAULT CHARSET=latin1;

-- --------------------------------------------------------

--
-- Table structure for table `pingback`
--

CREATE TABLE IF NOT EXISTS `pingback` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `url` varchar(255) NOT NULL,
  `region` int(10) NOT NULL DEFAULT '1',
  PRIMARY KEY (`id`),
  UNIQUE KEY `url` (`url`)
) ENGINE=MyISAM  DEFAULT CHARSET=latin1 AUTO_INCREMENT=2 ;

-- --------------------------------------------------------

--
-- Table structure for table `regions`
--

CREATE TABLE IF NOT EXISTS `regions` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `name` varchar(255) NOT NULL,
  `scrapper` varchar(32) NOT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `scrapper` (`scrapper`)
) ENGINE=MyISAM  DEFAULT CHARSET=latin1 AUTO_INCREMENT=3 ;

--
-- Dumping data for table `regions`
--

INSERT INTO `regions` (`id`, `name`, `scrapper`) VALUES
(1, 'calgary', '01234'),
(2, 'edmonton', '56789');

-- --------------------------------------------------------

--
-- Table structure for table `teams`
--

CREATE TABLE IF NOT EXISTS `teams` (
  `id` int(10) NOT NULL AUTO_INCREMENT,
  `name` varchar(11) NOT NULL,
  PRIMARY KEY (`id`)
) ENGINE=MyISAM  DEFAULT CHARSET=latin1 AUTO_INCREMENT=4 ;

--
-- Dumping data for table `teams`
--

INSERT INTO `teams` (`id`, `name`) VALUES
(1, 'RESISTANCE'),
(2, 'ENLIGHTENED'),
(3, 'UNCLAIMED');
