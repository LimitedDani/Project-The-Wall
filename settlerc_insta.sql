-- phpMyAdmin SQL Dump
-- version 4.6.5.2
-- https://www.phpmyadmin.net/
--
-- Host: localhost
-- Generation Time: Mar 06, 2017 at 09:59 AM
-- Server version: 5.5.31
-- PHP Version: 5.6.30

SET SQL_MODE = "NO_AUTO_VALUE_ON_ZERO";
SET time_zone = "+00:00";


/*!40101 SET @OLD_CHARACTER_SET_CLIENT=@@CHARACTER_SET_CLIENT */;
/*!40101 SET @OLD_CHARACTER_SET_RESULTS=@@CHARACTER_SET_RESULTS */;
/*!40101 SET @OLD_COLLATION_CONNECTION=@@COLLATION_CONNECTION */;
/*!40101 SET NAMES utf8mb4 */;

--
-- Database: `settlerc_insta`
--

-- --------------------------------------------------------

--
-- Table structure for table `pco_pageview`
--

CREATE TABLE `pco_pageview` (
  `ID` int(11) NOT NULL,
  `date` varchar(2000) NOT NULL,
  `count` int(11) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=latin1;

-- --------------------------------------------------------

--
-- Table structure for table `pco_posts`
--

CREATE TABLE `pco_posts` (
  `ID` int(11) NOT NULL,
  `user_id` longtext NOT NULL,
  `post_text` varchar(200) NOT NULL,
  `post_image` longtext NOT NULL,
  `post_likes` longtext NOT NULL,
  `posted_on` varchar(100) NOT NULL,
  `deleted` int(11) NOT NULL DEFAULT '0'
) ENGINE=InnoDB DEFAULT CHARSET=latin1;

-- --------------------------------------------------------

--
-- Table structure for table `pco_ranks`
--

CREATE TABLE `pco_ranks` (
  `rank` int(10) NOT NULL,
  `prefix` varchar(2000) NOT NULL,
  `color` varchar(2000) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=latin1;

-- --------------------------------------------------------

--
-- Table structure for table `pco_reaction`
--

CREATE TABLE `pco_reaction` (
  `ID` int(11) NOT NULL,
  `article_id` int(11) NOT NULL,
  `uuid` longtext,
  `reaction` longtext NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=latin1;

-- --------------------------------------------------------

--
-- Table structure for table `pco_settings`
--

CREATE TABLE `pco_settings` (
  `ID` int(11) NOT NULL,
  `variable` varchar(2002) NOT NULL,
  `data` varchar(2002) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=latin1;

-- --------------------------------------------------------

--
-- Table structure for table `pco_staff`
--

CREATE TABLE `pco_staff` (
  `ID` int(11) NOT NULL,
  `UUID` longtext NOT NULL,
  `rank` int(11) NOT NULL,
  `can_use_staffpanel` int(11) NOT NULL DEFAULT '0',
  `can_manage_parkrequests` int(11) NOT NULL DEFAULT '0',
  `can_manage_users` int(11) NOT NULL DEFAULT '0',
  `can_manage_parks` int(11) NOT NULL DEFAULT '0',
  `can_manage_comments` int(11) NOT NULL DEFAULT '0',
  `can_send_mail` int(11) NOT NULL DEFAULT '0',
  `can_manage_posts` int(11) NOT NULL DEFAULT '0',
  `can_write_tutorials` int(11) NOT NULL DEFAULT '0',
  `can_write_pvdw` int(11) NOT NULL DEFAULT '0',
  `can_write_pvdm` int(11) NOT NULL DEFAULT '0',
  `can_manage_applications` int(11) NOT NULL DEFAULT '0'
) ENGINE=InnoDB DEFAULT CHARSET=latin1;

-- --------------------------------------------------------

--
-- Table structure for table `pco_users`
--

CREATE TABLE `pco_users` (
  `ID` int(11) NOT NULL,
  `UUID` longtext NOT NULL,
  `name` varchar(200) NOT NULL,
  `email` varchar(2083) NOT NULL,
  `password` longtext NOT NULL,
  `changepassword` varchar(2000) NOT NULL DEFAULT '0',
  `rank` int(11) NOT NULL DEFAULT '0',
  `access` int(10) NOT NULL DEFAULT '1',
  `activated` varchar(2000) NOT NULL,
  `sessionID` tinytext NOT NULL,
  `last_execution` varchar(2000) NOT NULL,
  `news_email` int(11) NOT NULL DEFAULT '1'
) ENGINE=InnoDB DEFAULT CHARSET=latin1;

--
-- Indexes for dumped tables
--

--
-- Indexes for table `pco_pageview`
--
ALTER TABLE `pco_pageview`
  ADD PRIMARY KEY (`ID`);

--
-- Indexes for table `pco_posts`
--
ALTER TABLE `pco_posts`
  ADD PRIMARY KEY (`ID`);

--
-- Indexes for table `pco_ranks`
--
ALTER TABLE `pco_ranks`
  ADD PRIMARY KEY (`rank`);

--
-- Indexes for table `pco_reaction`
--
ALTER TABLE `pco_reaction`
  ADD PRIMARY KEY (`ID`);

--
-- Indexes for table `pco_settings`
--
ALTER TABLE `pco_settings`
  ADD PRIMARY KEY (`ID`);

--
-- Indexes for table `pco_staff`
--
ALTER TABLE `pco_staff`
  ADD PRIMARY KEY (`ID`);

--
-- Indexes for table `pco_users`
--
ALTER TABLE `pco_users`
  ADD PRIMARY KEY (`ID`);

--
-- AUTO_INCREMENT for dumped tables
--

--
-- AUTO_INCREMENT for table `pco_pageview`
--
ALTER TABLE `pco_pageview`
  MODIFY `ID` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=5;
--
-- AUTO_INCREMENT for table `pco_posts`
--
ALTER TABLE `pco_posts`
  MODIFY `ID` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=16;
--
-- AUTO_INCREMENT for table `pco_reaction`
--
ALTER TABLE `pco_reaction`
  MODIFY `ID` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=14;
--
-- AUTO_INCREMENT for table `pco_settings`
--
ALTER TABLE `pco_settings`
  MODIFY `ID` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=2;
--
-- AUTO_INCREMENT for table `pco_staff`
--
ALTER TABLE `pco_staff`
  MODIFY `ID` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=2;
--
-- AUTO_INCREMENT for table `pco_users`
--
ALTER TABLE `pco_users`
  MODIFY `ID` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=8;
/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
