-- phpMyAdmin SQL Dump
-- version 5.1.2
-- https://www.phpmyadmin.net/
--
-- Host: localhost:3306
-- Generation Time: Oct 27, 2026 at 11:14 PM
-- Server version: 5.7.24
-- PHP Version: 8.3.1

SET SQL_MODE = "NO_AUTO_VALUE_ON_ZERO";
START TRANSACTION;
SET time_zone = "+00:00";


/*!40101 SET @OLD_CHARACTER_SET_CLIENT=@@CHARACTER_SET_CLIENT */;
/*!40101 SET @OLD_CHARACTER_SET_RESULTS=@@CHARACTER_SET_RESULTS */;
/*!40101 SET @OLD_COLLATION_CONNECTION=@@COLLATION_CONNECTION */;
/*!40101 SET NAMES utf8mb4 */;

--
-- Database: `rihla`
--

-- --------------------------------------------------------

--
-- Table structure for table `administrator`
--

CREATE TABLE `administrator` (
  `userID` int(11) NOT NULL,
  `fullName` varchar(60) COLLATE utf8mb4_unicode_ci NOT NULL,
  `email` varchar(100) COLLATE utf8mb4_unicode_ci NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `administrator`
--

INSERT INTO `administrator` (`userID`, `fullName`, `email`) VALUES
(1, 'RIHLA Content Administrator', 'rihla.admin@example.com');

-- --------------------------------------------------------

--
-- Table structure for table `badge`
--

CREATE TABLE `badge` (
  `badgeID` int(11) NOT NULL,
  `title` varchar(50) COLLATE utf8mb4_unicode_ci NOT NULL,
  `description` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `iconPath` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `requiredPoints` int(11) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `badge`
--

INSERT INTO `badge` (`badgeID`, `title`, `description`, `iconPath`, `requiredPoints`) VALUES
(1, 'المستكشف المبتدئ', 'حصلت على أول نقاطك في رحلة.', 'assets/images/badge1.png', 10),
(2, 'المستكشف المثابر', 'جمعت خمسين نقطة.', 'assets/images/badge2.png', 50),
(3, 'خبير التراث', 'جمعت مئة نقطة.', 'assets/images/badge3.png', 100);

-- --------------------------------------------------------

--
-- Table structure for table `badgeaward`
--

CREATE TABLE `badgeaward` (
  `childID` int(11) NOT NULL,
  `badgeID` int(11) NOT NULL,
  `earnedDate` datetime NOT NULL DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Table structure for table `child`
--

CREATE TABLE `child` (
  `userID` int(11) NOT NULL,
  `displayName` varchar(30) COLLATE utf8mb4_unicode_ci NOT NULL,
  `avatar` varchar(50) COLLATE utf8mb4_unicode_ci NOT NULL,
  `totalPoints` int(11) NOT NULL DEFAULT '0'
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `child`
--

INSERT INTO `child` (`userID`, `displayName`, `avatar`, `totalPoints`) VALUES
(2, 'مستكشف', 'avatar1.png', 0);

-- --------------------------------------------------------

--
-- Table structure for table `choice`
--

CREATE TABLE `choice` (
  `choiceID` int(11) NOT NULL,
  `text` varchar(100) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `imagePath` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `isCorrect` tinyint(1) NOT NULL DEFAULT '0',
  `questionID` int(11) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `choice`
--

INSERT INTO `choice` (`choiceID`, `text`, `imagePath`, `isCorrect`, `questionID`) VALUES
(1, 'حي الطريف', NULL, 1, 1),
(2, 'جبل الفيل', NULL, 0, 1),
(3, 'الشعاب المرجانية', NULL, 0, 1),
(4, 'الزجاج', NULL, 0, 2),
(5, 'الطين', NULL, 1, 2),
(6, 'الحديد', NULL, 0, 2),
(7, 'العلا', NULL, 0, 3),
(8, 'جدة', NULL, 0, 3),
(9, 'الدرعية', NULL, 1, 3),
(10, 'العلا', NULL, 1, 4),
(11, 'جدة', NULL, 0, 4),
(12, 'الدرعية', NULL, 0, 4),
(13, 'الجمل', NULL, 0, 5),
(14, 'الفيل', NULL, 1, 5),
(15, 'الحصان', NULL, 0, 5),
(16, 'بيت نصيف', NULL, 0, 6),
(17, 'حي الطريف', NULL, 0, 6),
(18, 'الحِجر', NULL, 1, 6),
(19, 'الرواشين', NULL, 1, 7),
(20, 'الأبواب', NULL, 0, 7),
(21, 'السلالم', NULL, 0, 7),
(22, 'بيت نصيف', NULL, 1, 8),
(23, 'حي الطريف', NULL, 0, 8),
(24, 'الحِجر', NULL, 0, 8),
(25, 'الخليج العربي', NULL, 0, 9),
(26, 'البحر الأحمر', NULL, 1, 9),
(27, 'الخليج العربي', NULL, 0, 10),
(28, 'البحر الأحمر', NULL, 1, 10),
(29, 'البحر الأسود', NULL, 0, 10),
(30, 'الشعاب المرجانية', NULL, 1, 11),
(31, 'الرمال فقط', NULL, 0, 11),
(32, 'الأشجار', NULL, 0, 11),
(33, 'رحلة البر', NULL, 0, 12),
(34, 'رحلة البحر', NULL, 1, 12);



-- --------------------------------------------------------

--
-- Table structure for table `contentsection`
--

CREATE TABLE `contentsection` (
  `sectionID` int(11) NOT NULL,
  `title` varchar(100) COLLATE utf8mb4_unicode_ci NOT NULL,
  `text` text COLLATE utf8mb4_unicode_ci NOT NULL,
  `imagePath` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `source` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `landmarkID` int(11) NOT NULL,
  `adminID` int(11) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `contentsection`
--

INSERT INTO `contentsection` (`sectionID`, `title`, `text`, `imagePath`, `source`, `landmarkID`, `adminID`) VALUES
(1, 'حي الطريف', 'حي الطريف حي تاريخي في قلب الدرعية، بُني في منتصف القرن الثامن عشر الميلادي. بيوته وقصوره مبنية من الطين، لذلك يوصف بأنه من أكبر الأحياء الطينية في العالم. وفي عام 2010م سُجّل في قائمة التراث العالمي لليونسكو، وهو ثاني موقع سعودي يدخل هذه القائمة بعد الحِجر.', NULL, 'سعوديبيديا (الموسوعة السعودية): حي الطريف - https://saudipedia.com/حي-الطريف', 1, 1),
(2, 'قصر سلوى والعمارة النجدية', 'أشهر معالم حي الطريف قصر سلوى، وكان مقرًا لحكم الدولة السعودية الأولى. ويتميز الحي بالأسلوب المعماري النجدي الذي تنفرد به منطقة وسط المملكة العربية السعودية، وهذا من أسباب تسجيله في قائمة التراث العالمي.', NULL, 'سعوديبيديا (الموسوعة السعودية): لماذا أدرج حي الطريف في قائمة مواقع التراث العالمي؟ - https://saudipedia.com/لماذا-أدرج-حي-الطريف-في-قائمة-مواقع-التراث-العالمي؟', 1, 1),
(3, 'الحِجر', 'الحِجر موقع أثري يقع على بعد نحو 20 كيلومترًا شمال مدينة العلا. سكنه الأنباط قديمًا، ويُعد أكبر موقع محفوظ لحضارتهم. وفي عام 2008م أصبح الحِجر أول موقع سعودي يُسجَّل في قائمة التراث العالمي لليونسكو.', NULL, 'سعوديبيديا (الموسوعة السعودية): كم تبعد الحجر عن مدينة العلا؟ - https://saudipedia.com/كم-تبعد-الحجر-عن-مدينة-العلا؟', 2, 1),
(4, 'مدافن منحوتة في الجبال', 'في الحِجر مدافن كثيرة منحوتة في الجبال، وعلى واجهاتها نقوش ورسومات وكتابات نبطية. وتعود هذه الواجهات المزخرفة إلى الفترة بين القرن الأول قبل الميلاد والقرن الأول الميلادي.', NULL, 'سعوديبيديا (الموسوعة السعودية): مقابر الحجر الأثرية - https://saudipedia.com/مقابر-الحجر-الأثرية', 2, 1),
(5, 'بيت نصيف', 'بيت نصيف من أشهر البيوت التراثية في جدة التاريخية، ويعود تاريخ بنائه إلى عام 1289هـ (1872م) واستغرق بناؤه أربع سنوات. ونزل فيه الملك المؤسس عبدالعزيز عام 1344هـ (1925م)، واتخذه مقرًا لسكنه نحو عشر سنوات، وكان يستقبل فيه ضيوف الدولة.', NULL, 'سعوديبيديا (الموسوعة السعودية): بيت نصيف - https://saudipedia.com/بيت-نصيف', 3, 1),
(6, 'الرواشين', 'من أجمل ما في بيت نصيف الرواشين، وهي تغطية خشبية بارزة على النوافذ والفتحات، وتُعد من أهم عناصر العمارة في منطقة الحجاز قديمًا. ويضم البيت 40 غرفة على مساحة 900 متر مربع.', NULL, 'سعوديبيديا (الموسوعة السعودية): من مؤسس بيت نصيف؟ - https://saudipedia.com/من-مؤسس-بيت-نصيف؟', 3, 1),
(7, 'ما هي الشعاب المرجانية؟', 'الشعاب المرجانية صخور جيرية صلبة فيها تجاويف صغيرة تعيش فيها حيوانات المرجان. ويُعد البحر الأحمر من أغنى بحار العالم بالشعاب المرجانية، وتزداد كثافتها كلما اتجهنا جنوبًا في البحر.', NULL, 'سعوديبيديا (الموسوعة السعودية): الشعاب المرجانية في السعودية - https://saudipedia.com/الشعاب-المرجانية-في-السعودية', 4, 1),
(8, 'بيت للأسماك', 'الشعاب المرجانية بيئة تتكاثر فيها الأسماك. وتقدّر هيئة المساحة الجيولوجية السعودية وجود 200 نوع من الشعاب المرجانية في البحر الأحمر داخل حدود المملكة، بألوان وأشكال مختلفة.', NULL, 'سعوديبيديا (الموسوعة السعودية): كم عدد أنواع الشعاب المرجانية في السعودية؟ - https://saudipedia.com/كم-عدد-أنواع-الشعاب-المرجانية-في-السعودية؟', 4, 1),
(9, 'جبل الفيل', 'جبل الفيل، ويُعرف أيضًا بصخرة الفيل، من معالم محافظة العلا. يقع شرق موقع الحِجر الأثري، ويبعد نحو 20 كيلومترًا عن مركز المحافظة. سُمّي بهذا الاسم لأن شكله يشبه الفيل.', NULL, 'سعوديبيديا (الموسوعة السعودية): جبل الفيل - https://saudipedia.com/جبل-الفيل', 5, 1),
(10, 'كيف تشكّل جبل الفيل؟', 'يتكون جبل الفيل من الحجر الرملي الأحمر، ويبلغ ارتفاعه 52 مترًا. تشكّل على مدى ملايين السنين بفعل عوامل النحت والتعرية، فأصبح يشبه فيلًا يمتد خرطومه إلى الأرض.', NULL, 'سعوديبيديا (الموسوعة السعودية): جبل الفيل - https://saudipedia.com/جبل-الفيل', 5, 1),
(11, 'جدة على البحر الأحمر', 'تقع جدة التاريخية على ساحل البحر الأحمر، وكانت منذ القرن السابع الميلادي ميناءً للحجاج القادمين إلى مكة المكرمة. وفي عام 2014م سُجّلت في قائمة التراث العالمي لليونسكو، وهي ثالث موقع سعودي في القائمة.', NULL, 'سعوديبيديا: لماذا أدرجت جدة التاريخية في قائمة اليونسكو؟ + برنامج جدة التاريخية بوزارة الثقافة (صحيفة الوطن، 2024)', 3, 1);

-- --------------------------------------------------------

--
-- Table structure for table `destination`
--

CREATE TABLE `destination` (
  `destinationID` int(11) NOT NULL,
  `name` varchar(50) COLLATE utf8mb4_unicode_ci NOT NULL,
  `description` text COLLATE utf8mb4_unicode_ci NOT NULL,
  `imagePath` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `status` enum('draft','published','hidden') COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT 'draft',
  `journeyID` int(11) NOT NULL,
  `adminID` int(11) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `destination`
--

INSERT INTO `destination` (`destinationID`, `name`, `description`, `imagePath`, `status`, `journeyID`, `adminID`) VALUES
(1, 'الدرعية', 'الدرعية موقع تاريخي مهم في المملكة العربية السعودية.', 'assets/images/diriyah.jpg', 'published', 1, 1),
(2, 'العلا', 'العلا منطقة تتميز بتاريخها ومعالمها الأثرية.', 'assets/images/alula.jpg', 'published', 1, 1),
(3, 'جدة التاريخية', 'جدة التاريخية منطقة تراثية تتميز بمبانيها القديمة.', 'assets/images/jeddah.png', 'published', 1, 1),
(4, 'البحر الأحمر', 'البحر الأحمر يتميز ببيئته البحرية المتنوعة.', 'assets/images/redsea.jpg', 'published', 2, 1);

-- --------------------------------------------------------

--
-- Table structure for table `discovery`
--

CREATE TABLE `discovery` (
  `discoveryID` int(11) NOT NULL,
  `savedDate` datetime NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `notebookID` int(11) NOT NULL,
  `landmarkID` int(11) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Table structure for table `explorernotebook`
--

CREATE TABLE `explorernotebook` (
  `notebookID` int(11) NOT NULL,
  `childID` int(11) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `explorernotebook`
--

INSERT INTO `explorernotebook` (`notebookID`, `childID`) VALUES
(1, 2);

-- --------------------------------------------------------

--
-- Table structure for table `journey`
--

CREATE TABLE `journey` (
  `journeyID` int(11) NOT NULL,
  `name` varchar(30) COLLATE utf8mb4_unicode_ci NOT NULL,
  `type` enum('land','sea') COLLATE utf8mb4_unicode_ci NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `journey`
--

INSERT INTO `journey` (`journeyID`, `name`, `type`) VALUES
(1, 'رحلة البر', 'land'),
(2, 'رحلة البحر', 'sea');

-- --------------------------------------------------------

--
-- Table structure for table `landmark`
--

CREATE TABLE `landmark` (
  `landmarkID` int(11) NOT NULL,
  `name` varchar(80) COLLATE utf8mb4_unicode_ci NOT NULL,
  `description` text COLLATE utf8mb4_unicode_ci NOT NULL,
  `imagePath` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `status` enum('draft','published','hidden') COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT 'draft',
  `destinationID` int(11) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `landmark`
--

INSERT INTO `landmark` (`landmarkID`, `name`, `description`, `imagePath`, `status`, `destinationID`) VALUES
(1, 'حي الطريف', 'حي تاريخي يقع في الدرعية.', 'assets/images/turaif.jpg', 'published', 1),
(2, 'الحِجر', 'موقع أثري مشهور في العلا.', 'assets/images/hegra.jpg', 'published', 2),
(3, 'بيت نصيف', 'أحد المباني التاريخية في جدة.', 'assets/images/nassif.jpg', 'published', 3),
(4, 'الشعاب المرجانية', 'تعيش في الشعاب المرجانية كائنات بحرية متنوعة.', 'assets/images/coral.jpg', 'published', 4),
(5, 'جبل الفيل', 'صخرة كبيرة في العلا تشبه الفيل.', 'assets/images/alula.jpg', 'published', 2);

-- --------------------------------------------------------

--
-- Table structure for table `question`
--

CREATE TABLE `question` (
  `questionID` int(11) NOT NULL,
  `text` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `imagePath` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `quizID` int(11) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `question`
--

INSERT INTO `question` (`questionID`, `text`, `imagePath`, `quizID`) VALUES
(1, 'ما اسم هذا الحي التاريخي في الدرعية؟', 'assets/images/quiz/diriyah-q.jpg', 1),
(2, 'من ماذا بُنيت هذه البيوت؟', 'assets/images/quiz/diriyah-q.jpg', 1),
(3, 'في أي مدينة يوجد حي الطريف؟', 'assets/images/quiz/diriyah-q.jpg', 1),
(4, 'في أي منطقة توجد هذه الصخرة؟', 'assets/images/quiz/alula-q.jpg', 2),
(5, 'هذه الصخرة تشبه أي حيوان؟', 'assets/images/quiz/alula-q.jpg', 2),
(6, 'قرب هذه الصخرة يقع موقع أثري مشهور في العلا. ما اسمه؟', 'assets/images/quiz/alula-q.jpg', 2),
(7, 'ماذا نسمي النوافذ الخشبية في بيوت جدة التاريخية؟', 'assets/images/quiz/jeddah-q.png', 3),
(8, 'ما اسم البيت المشهور في جدة التاريخية؟', 'assets/images/quiz/jeddah-q.png', 3),
(9, 'على أي بحر تقع مدينة جدة؟', 'assets/images/quiz/jeddah-q.png', 3),
(10, 'ما اسم هذا البحر؟', 'assets/images/quiz/redsea-q.jpg', 4),
(11, 'ماذا نرى تحت الماء الأزرق الفاتح؟', 'assets/images/quiz/redsea-q.jpg', 4),
(12, 'في أي رحلة نزور البحر الأحمر؟', 'assets/images/quiz/redsea-q.jpg', 4);



-- --------------------------------------------------------

--
-- Table structure for table `quiz`
--

CREATE TABLE `quiz` (
  `quizID` int(11) NOT NULL,
  `title` varchar(80) COLLATE utf8mb4_unicode_ci NOT NULL,
  `totalPoints` int(11) NOT NULL,
  `destinationID` int(11) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `quiz`
--

INSERT INTO `quiz` (`quizID`, `title`, `totalPoints`, `destinationID`) VALUES
(1, 'تحدي الدرعية', 30, 1),
(2, 'تحدي العلا', 30, 2),
(3, 'تحدي جدة التاريخية', 30, 3),
(4, 'تحدي البحر الأحمر', 30, 4);



-- --------------------------------------------------------

--
-- Table structure for table `quizattempt`
--

CREATE TABLE `quizattempt` (
  `childID` int(11) NOT NULL,
  `quizID` int(11) NOT NULL,
  `score` int(11) NOT NULL,
  `pointsEarned` int(11) NOT NULL,
  `completedDate` datetime NOT NULL DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Table structure for table `user`
--

CREATE TABLE `user` (
  `userID` int(11) NOT NULL,
  `username` varchar(30) COLLATE utf8mb4_unicode_ci NOT NULL,
  `password` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `user`
--

INSERT INTO `user` (`userID`, `username`, `password`) VALUES
(1, 'admin', '$2y$10$k0Y64JM89QEzPZQUFsgheugtL2ua83St7Wety8KW3EHkzkAN.IpOS'),
(2, 'child', '$2y$10$afEYxsgHjMd6ACbYEYTYse1Iikkz3GxlnBAqTFmYQA34BEh4mOS/W');

--
-- Indexes for dumped tables
--

--
-- Indexes for table `administrator`
--
ALTER TABLE `administrator`
  ADD PRIMARY KEY (`userID`);

--
-- Indexes for table `badge`
--
ALTER TABLE `badge`
  ADD PRIMARY KEY (`badgeID`),
  ADD UNIQUE KEY `title` (`title`);

--
-- Indexes for table `badgeaward`
--
ALTER TABLE `badgeaward`
  ADD PRIMARY KEY (`childID`,`badgeID`),
  ADD KEY `badgeID` (`badgeID`);

--
-- Indexes for table `child`
--
ALTER TABLE `child`
  ADD PRIMARY KEY (`userID`);

--
-- Indexes for table `choice`
--
ALTER TABLE `choice`
  ADD PRIMARY KEY (`choiceID`),
  ADD KEY `questionID` (`questionID`);

--
-- Indexes for table `contentsection`
--
ALTER TABLE `contentsection`
  ADD PRIMARY KEY (`sectionID`),
  ADD KEY `landmarkID` (`landmarkID`),
  ADD KEY `adminID` (`adminID`);

--
-- Indexes for table `destination`
--
ALTER TABLE `destination`
  ADD PRIMARY KEY (`destinationID`),
  ADD UNIQUE KEY `name` (`name`),
  ADD KEY `journeyID` (`journeyID`),
  ADD KEY `adminID` (`adminID`);

--
-- Indexes for table `discovery`
--
ALTER TABLE `discovery`
  ADD PRIMARY KEY (`discoveryID`),
  ADD UNIQUE KEY `notebookID` (`notebookID`,`landmarkID`),
  ADD KEY `landmarkID` (`landmarkID`);

--
-- Indexes for table `explorernotebook`
--
ALTER TABLE `explorernotebook`
  ADD PRIMARY KEY (`notebookID`),
  ADD UNIQUE KEY `childID` (`childID`);

--
-- Indexes for table `journey`
--
ALTER TABLE `journey`
  ADD PRIMARY KEY (`journeyID`),
  ADD UNIQUE KEY `name` (`name`),
  ADD UNIQUE KEY `type` (`type`);

--
-- Indexes for table `landmark`
--
ALTER TABLE `landmark`
  ADD PRIMARY KEY (`landmarkID`),
  ADD KEY `destinationID` (`destinationID`);

--
-- Indexes for table `question`
--
ALTER TABLE `question`
  ADD PRIMARY KEY (`questionID`),
  ADD KEY `quizID` (`quizID`);

--
-- Indexes for table `quiz`
--
ALTER TABLE `quiz`
  ADD PRIMARY KEY (`quizID`),
  ADD UNIQUE KEY `destinationID` (`destinationID`);

--
-- Indexes for table `quizattempt`
--
ALTER TABLE `quizattempt`
  ADD PRIMARY KEY (`childID`,`quizID`),
  ADD KEY `quizID` (`quizID`);

--
-- Indexes for table `user`
--
ALTER TABLE `user`
  ADD PRIMARY KEY (`userID`),
  ADD UNIQUE KEY `username` (`username`);

--
-- AUTO_INCREMENT for dumped tables
--

--
-- AUTO_INCREMENT for table `badge`
--
ALTER TABLE `badge`
  MODIFY `badgeID` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=4;

--
-- AUTO_INCREMENT for table `choice`
--
ALTER TABLE `choice`
  MODIFY `choiceID` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=35;

--
-- AUTO_INCREMENT for table `contentsection`
--
ALTER TABLE `contentsection`
  MODIFY `sectionID` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=12;

--
-- AUTO_INCREMENT for table `destination`
--
ALTER TABLE `destination`
  MODIFY `destinationID` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=6;

--
-- AUTO_INCREMENT for table `discovery`
--
ALTER TABLE `discovery`
  MODIFY `discoveryID` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `explorernotebook`
--
ALTER TABLE `explorernotebook`
  MODIFY `notebookID` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=2;

--
-- AUTO_INCREMENT for table `journey`
--
ALTER TABLE `journey`
  MODIFY `journeyID` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=3;

--
-- AUTO_INCREMENT for table `landmark`
--
ALTER TABLE `landmark`
  MODIFY `landmarkID` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=6;

--
-- AUTO_INCREMENT for table `question`
--
ALTER TABLE `question`
  MODIFY `questionID` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=13;

--
-- AUTO_INCREMENT for table `quiz`
--
ALTER TABLE `quiz`
  MODIFY `quizID` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=5;

--
-- AUTO_INCREMENT for table `user`
--
ALTER TABLE `user`
  MODIFY `userID` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=3;

--
-- Constraints for dumped tables
--

--
-- Constraints for table `administrator`
--
ALTER TABLE `administrator`
  ADD CONSTRAINT `administrator_ibfk_1` FOREIGN KEY (`userID`) REFERENCES `user` (`userID`) ON DELETE CASCADE;

--
-- Constraints for table `badgeaward`
--
ALTER TABLE `badgeaward`
  ADD CONSTRAINT `badgeaward_ibfk_1` FOREIGN KEY (`childID`) REFERENCES `child` (`userID`) ON DELETE CASCADE,
  ADD CONSTRAINT `badgeaward_ibfk_2` FOREIGN KEY (`badgeID`) REFERENCES `badge` (`badgeID`) ON DELETE CASCADE;

--
-- Constraints for table `child`
--
ALTER TABLE `child`
  ADD CONSTRAINT `child_ibfk_1` FOREIGN KEY (`userID`) REFERENCES `user` (`userID`) ON DELETE CASCADE;

--
-- Constraints for table `choice`
--
ALTER TABLE `choice`
  ADD CONSTRAINT `choice_ibfk_1` FOREIGN KEY (`questionID`) REFERENCES `question` (`questionID`) ON DELETE CASCADE;

--
-- Constraints for table `contentsection`
--
ALTER TABLE `contentsection`
  ADD CONSTRAINT `contentsection_ibfk_1` FOREIGN KEY (`landmarkID`) REFERENCES `landmark` (`landmarkID`) ON DELETE CASCADE,
  ADD CONSTRAINT `contentsection_ibfk_2` FOREIGN KEY (`adminID`) REFERENCES `administrator` (`userID`);

--
-- Constraints for table `destination`
--
ALTER TABLE `destination`
  ADD CONSTRAINT `destination_ibfk_1` FOREIGN KEY (`journeyID`) REFERENCES `journey` (`journeyID`),
  ADD CONSTRAINT `destination_ibfk_2` FOREIGN KEY (`adminID`) REFERENCES `administrator` (`userID`);

--
-- Constraints for table `discovery`
--
ALTER TABLE `discovery`
  ADD CONSTRAINT `discovery_ibfk_1` FOREIGN KEY (`notebookID`) REFERENCES `explorernotebook` (`notebookID`) ON DELETE CASCADE,
  ADD CONSTRAINT `discovery_ibfk_2` FOREIGN KEY (`landmarkID`) REFERENCES `landmark` (`landmarkID`) ON DELETE CASCADE;

--
-- Constraints for table `explorernotebook`
--
ALTER TABLE `explorernotebook`
  ADD CONSTRAINT `explorernotebook_ibfk_1` FOREIGN KEY (`childID`) REFERENCES `child` (`userID`) ON DELETE CASCADE;

--
-- Constraints for table `landmark`
--
ALTER TABLE `landmark`
  ADD CONSTRAINT `landmark_ibfk_1` FOREIGN KEY (`destinationID`) REFERENCES `destination` (`destinationID`) ON DELETE CASCADE;

--
-- Constraints for table `question`
--
ALTER TABLE `question`
  ADD CONSTRAINT `question_ibfk_1` FOREIGN KEY (`quizID`) REFERENCES `quiz` (`quizID`) ON DELETE CASCADE;

--
-- Constraints for table `quiz`
--
ALTER TABLE `quiz`
  ADD CONSTRAINT `quiz_ibfk_1` FOREIGN KEY (`destinationID`) REFERENCES `destination` (`destinationID`) ON DELETE CASCADE;

--
-- Constraints for table `quizattempt`
--
ALTER TABLE `quizattempt`
  ADD CONSTRAINT `quizattempt_ibfk_1` FOREIGN KEY (`childID`) REFERENCES `child` (`userID`) ON DELETE CASCADE,
  ADD CONSTRAINT `quizattempt_ibfk_2` FOREIGN KEY (`quizID`) REFERENCES `quiz` (`quizID`) ON DELETE CASCADE;

--
-- CHECK constraints (from the data dictionary)
--
ALTER TABLE `child`
  ADD CONSTRAINT `child_points_check` CHECK (`totalPoints` >= 0);
ALTER TABLE `quiz`
  ADD CONSTRAINT `quiz_points_check` CHECK (`totalPoints` > 0);
ALTER TABLE `choice`
  ADD CONSTRAINT `choice_content_check` CHECK (`text` IS NOT NULL OR `imagePath` IS NOT NULL);
ALTER TABLE `quizattempt`
  ADD CONSTRAINT `quizattempt_score_check` CHECK (`score` >= 0),
  ADD CONSTRAINT `quizattempt_points_check` CHECK (`pointsEarned` >= 0);
ALTER TABLE `badge`
  ADD CONSTRAINT `badge_points_check` CHECK (`requiredPoints` > 0);
COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
