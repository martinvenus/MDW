-- phpMyAdmin SQL Dump
-- version 3.2.1
-- http://www.phpmyadmin.net
--
-- Počítač: porthos.wsolution.cz
-- Vygenerováno: Neděle 28. listopadu 2010, 23:32
-- Verze MySQL: 5.0.51
-- Verze PHP: 5.3.2-2

SET SQL_MODE="NO_AUTO_VALUE_ON_ZERO";


/*!40101 SET @OLD_CHARACTER_SET_CLIENT=@@CHARACTER_SET_CLIENT */;
/*!40101 SET @OLD_CHARACTER_SET_RESULTS=@@CHARACTER_SET_RESULTS */;
/*!40101 SET @OLD_COLLATION_CONNECTION=@@COLLATION_CONNECTION */;
/*!40101 SET NAMES utf8 */;

--
-- Databáze: `mdw_wsolution_cz`
--

-- --------------------------------------------------------

--
-- Struktura tabulky `acl`
--

CREATE TABLE IF NOT EXISTS `acl` (
  `id` int(10) unsigned NOT NULL auto_increment,
  `role_id` tinyint(4) NOT NULL,
  `privilege_id` tinyint(4) NOT NULL,
  `resource_id` tinyint(4) NOT NULL,
  `allowed` enum('Y','N') character set latin1 NOT NULL,
  PRIMARY KEY  (`id`),
  KEY `role_id` (`role_id`),
  KEY `privilege_id` (`privilege_id`),
  KEY `resource_id` (`resource_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_czech_ci AUTO_INCREMENT=1 ;

--
-- Vypisuji data pro tabulku `acl`
--


-- --------------------------------------------------------

--
-- Struktura tabulky `api`
--

CREATE TABLE IF NOT EXISTS `api` (
  `id` int(10) unsigned NOT NULL auto_increment,
  `key` varchar(128) collate utf8_czech_ci NOT NULL,
  `serverName` varchar(100) collate utf8_czech_ci default NULL,
  `responsibleStaffId` int(10) unsigned NOT NULL,
  `active` tinyint(1) unsigned default NULL,
  `created` int(10) unsigned NOT NULL,
  `updated` int(10) unsigned NOT NULL,
  PRIMARY KEY  (`id`),
  UNIQUE KEY `key_UNIQUE` (`key`),
  UNIQUE KEY `id_UNIQUE` (`id`),
  UNIQUE KEY `serverName_UNIQUE` (`serverName`),
  KEY `fk_api_1` (`responsibleStaffId`)
) ENGINE=InnoDB  DEFAULT CHARSET=utf8 COLLATE=utf8_czech_ci AUTO_INCREMENT=3 ;

--
-- Vypisuji data pro tabulku `api`
--

INSERT INTO `api` (`id`, `key`, `serverName`, `responsibleStaffId`, `active`, `created`, `updated`) VALUES
(1, '1234567890', NULL, 1, 1, 1290607200, 1290607200),
(2, 'JCFY7hzMZVFALhV8', NULL, 1, 1, 1290607200, 1290607200);

-- --------------------------------------------------------

--
-- Struktura tabulky `bonusAPI`
--

CREATE TABLE IF NOT EXISTS `bonusAPI` (
  `id` int(10) unsigned NOT NULL auto_increment,
  `userId` int(10) unsigned NOT NULL,
  `zajezdId` int(10) unsigned NOT NULL,
  `orderId` int(10) unsigned NOT NULL,
  PRIMARY KEY  (`id`),
  KEY `userId` (`userId`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_czech_ci AUTO_INCREMENT=1 ;

--
-- Vypisuji data pro tabulku `bonusAPI`
--


-- --------------------------------------------------------

--
-- Struktura tabulky `department`
--

CREATE TABLE IF NOT EXISTS `department` (
  `id` int(10) unsigned NOT NULL auto_increment,
  `name` varchar(150) collate utf8_czech_ci NOT NULL,
  `departmentManagerId` int(10) unsigned NOT NULL,
  `public` tinyint(1) unsigned default NULL,
  `signature` text collate utf8_czech_ci,
  PRIMARY KEY  (`id`),
  UNIQUE KEY `id_UNIQUE` (`id`),
  UNIQUE KEY `name_UNIQUE` (`name`),
  KEY `fk_department_1` (`departmentManagerId`)
) ENGINE=InnoDB  DEFAULT CHARSET=utf8 COLLATE=utf8_czech_ci AUTO_INCREMENT=4 ;

--
-- Vypisuji data pro tabulku `department`
--

INSERT INTO `department` (`id`, `name`, `departmentManagerId`, `public`, `signature`) VALUES
(1, 'Obchodní oddělení', 1, 1, NULL),
(2, 'Technické oddělení', 2, 1, NULL),
(3, 'Managment', 3, 0, NULL);

-- --------------------------------------------------------

--
-- Struktura tabulky `email`
--

CREATE TABLE IF NOT EXISTS `email` (
  `id` int(10) unsigned NOT NULL auto_increment,
  `departmentId` int(10) unsigned NOT NULL,
  `email` varchar(150) collate utf8_czech_ci NOT NULL,
  `username` varchar(150) collate utf8_czech_ci NOT NULL,
  `password` varchar(150) collate utf8_czech_ci NOT NULL,
  `server` varchar(150) collate utf8_czech_ci NOT NULL,
  `protocol` int(5) unsigned NOT NULL,
  `deleteEmail` tinyint(1) unsigned default NULL COMMENT 'emails are deleted after fetch in case deleteEmail=1',
  `lastFetch` int(20) unsigned default NULL,
  PRIMARY KEY  (`id`),
  UNIQUE KEY `id_UNIQUE` (`id`),
  UNIQUE KEY `email_UNIQUE` (`email`),
  KEY `fk_email_1` (`departmentId`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_czech_ci AUTO_INCREMENT=1 ;

--
-- Vypisuji data pro tabulku `email`
--


-- --------------------------------------------------------

--
-- Struktura tabulky `kb`
--

CREATE TABLE IF NOT EXISTS `kb` (
  `id` int(10) unsigned NOT NULL auto_increment,
  `createdByStaffId` int(10) unsigned NOT NULL,
  `departmentId` int(10) unsigned NOT NULL,
  `title` varchar(255) collate utf8_czech_ci NOT NULL,
  `information` text collate utf8_czech_ci NOT NULL,
  `created` int(10) unsigned NOT NULL,
  `updated` int(20) unsigned default NULL,
  PRIMARY KEY  (`id`),
  UNIQUE KEY `title` (`title`),
  KEY `fk_kb_1` (`createdByStaffId`),
  KEY `fk_kb_2` (`departmentId`)
) ENGINE=InnoDB  DEFAULT CHARSET=utf8 COLLATE=utf8_czech_ci AUTO_INCREMENT=7 ;

--
-- Vypisuji data pro tabulku `kb`
--

INSERT INTO `kb` (`id`, `createdByStaffId`, `departmentId`, `title`, `information`, `created`, `updated`) VALUES
(1, 1, 2, 'Zrušení přesměrování přes DNS', 'Jestliže jste nastavili přesměrování Vaší domény na naše předpřipravené stránky a chcete toto přesměrování zrušit je nutné nastavit "A" záznam z námi nastavené hodnoty (například 81.2.195.12) na jakoukoliv jinou.\r\n\r\nNa obrázku je příklad domény nasměrované na stránku "Stránky jsou v rekonstrukci". Aby se toto přesměrování zrušilo je nutné změnit nastavenou IP adresau 81.2.195.12 na IP adresu serveru, kde se Vaše stránky nyní nacházejí.\r\n\r\nV administraci klikněte na jméno domény a následně na volbu "Editace DNS záznamů". Vyberte A záznam pro doménu 2. řádu např.:  vzorova-domena.cz A 81.2.195.12  a klikněte na název domény (bod 1 na obrázku), poté změňte IP adresu (bod 2) a nakonec změnu uložte tlačítkem se šipkou (bod 3).\r\n\r\nProvedená změna se projeví do 30-ti minut. ', 1288077450, NULL),
(2, 1, 2, 'DNSSEC', 'Co je to DNSSEC ?\r\n\r\nDNSSEC je nadstavba DNS (Domain Name System) a má za úkol zvýšit bezpečnost doménových jmen. Ověřuje pravost informací získaných z DNS a předchází tak problémům např. s phishingem a dalším nepříjemným jevům současného internetu.\r\n\r\nS DNSSEC snížíte riziko:\r\n- zneužití vašich e-mailových schránek\r\n- obejití antispamové ochrany při kontrole DNS\r\n- prozrazení choulostivých dat (přístupová hesla, údaje o platebních kartách, atd.)\r\n- útoku na obsah vaší webové prezentace\r\n\r\nPodrobnější informace a technickou specifikaci DNSSECu naleznete na adrese : http://www.nic.cz/dnssec/\r\n\r\nKde a jak je možné DNSSEC nastavit ?\r\n\r\nZabezpečení zóny se v tuto chvíli týká pouze ccTLD .cz (domén s koncovkou .cz).\r\n\r\nFORPSI může DNSSEC nastavit jen u domén, u nichž je veden jako oprávněný registrátor INTERNET CZ, a.s. a doména využívá DNS FORPSI. Objednávka zřízení DNSSECu se provádí prostřednictvím kontaktního formuláře v zákaznické administraci.\r\nSlužba DNSSEC na DNS FORPSI je poskytována zdarma. O nastavení služby je nutné si požádat prostřednictvím kontaktního formuláře ze zákaznické administrace.\r\n\r\nVyužívá-li zákazník u své .cz domény (jejímž registrátorem je INTERNET CZ, a.s.) vlastní DNS, musí nejdříve jeho správce DNS vytvořit sadu klíčů pro doménu tzv. "keyset". Může ho vytvořit zde a tento keyset následně přidat k dané doméně přes formulář na změnu údajů k doméně.(Autorizace změny u domény se provádí heslem k doméně, které je možné si nechat zaslat na e-mail držitele či admin. kontaktu přes odkaz "nevim heslo").\r\n\r\nVe Whois databázi CZ.NICu se nastavení DNSSECu zobrazí okamžitě, prakticky se pak aplikuje během několika hodin.\r\n', 1288077450, NULL),
(3, 2, 1, 'Co je to dedikovaný server?', 'Jedná se o možnost pronájmu serveru FORPSI koncovým klientem. Zákazník si nekupuje vlastní stroj, ale vybírá si mezi námi nabízenými variantami předpřipravených serverů, jejichž přesné HW složení si může zvolit přímo v objednávce. Veškerý HW servis je vždy prováděn naší společností.Klient si pak zajistí pouze SW část provozu.', 1288077450, NULL),
(4, 3, 2, 'Jaký je limit pro měsíční přenos dat serveru?', 'U služby Private server závisí měsíční přenos na objednaném limitu, který si zákazník volí v objednávce služby.\r\n\r\nU dedikovaných serverů si lze objednat kromě omezeného datového limiitu také neomezený přenos dat s omezenou rychlostí bez agregace, nebo s agregací 1:10.', 1288077450, NULL),
(5, 3, 1, 'Co nastane, nebude-li faktura v termínu uhrazena?', 'Nebude-li faktura uhrazena v řádné době splatnosti deseti dnů, bude zákazníkovi zaslána upomínka a to v den splatnosti a v případě nutnosti ještě druhá upomínka. Po čtyřech dnech od zaslání druhé upomínky bude server odpojen. Znovu připojení serveru je realizováno až po řádném uhrazení všech pohledávek na náš účet. Řádné uhrazení znamená, že fakturovaná částka bude připsána na účet naší společnosti v plné výši.\r\n', 1288077450, NULL),
(6, 3, 1, 'Kde naleznu daňový doklad?', '\r\nDaňový doklad (fakturu) naleznete po přijetí platby na náš účet ve své zákaznické administraci.\r\n\r\nPřihlašte se k Vašemu zákaznickému účtu, kde je vedena doména či webhosting. Po přihlášení je v levém menu sekce Fakturace - dokumenty. Na hlavní stránce se vám zobrazí výpis všech zaplacených faktur. Máte možnost také hledat podle vybraných filtrů, například podle variabilního symbolu či typu služby.', 1288077450, NULL);

-- --------------------------------------------------------

--
-- Struktura tabulky `privileges`
--

CREATE TABLE IF NOT EXISTS `privileges` (
  `id` tinyint(4) NOT NULL auto_increment,
  `name` varchar(64) character set latin1 NOT NULL,
  PRIMARY KEY  (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_czech_ci ROW_FORMAT=COMPACT AUTO_INCREMENT=1 ;

--
-- Vypisuji data pro tabulku `privileges`
--


-- --------------------------------------------------------

--
-- Struktura tabulky `resources`
--

CREATE TABLE IF NOT EXISTS `resources` (
  `id` tinyint(4) NOT NULL auto_increment,
  `name` varchar(64) character set latin1 NOT NULL,
  PRIMARY KEY  (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_czech_ci ROW_FORMAT=COMPACT AUTO_INCREMENT=1 ;

--
-- Vypisuji data pro tabulku `resources`
--


-- --------------------------------------------------------

--
-- Struktura tabulky `roles`
--

CREATE TABLE IF NOT EXISTS `roles` (
  `id` tinyint(4) NOT NULL auto_increment,
  `parent_id` tinyint(4) NOT NULL,
  `name` varchar(64) character set latin1 NOT NULL,
  PRIMARY KEY  (`id`),
  UNIQUE KEY `name` (`name`)
) ENGINE=InnoDB  DEFAULT CHARSET=utf8 COLLATE=utf8_czech_ci AUTO_INCREMENT=3 ;

--
-- Vypisuji data pro tabulku `roles`
--

INSERT INTO `roles` (`id`, `parent_id`, `name`) VALUES
(1, 2, 'Administrator'),
(2, 0, 'User');

-- --------------------------------------------------------

--
-- Struktura tabulky `settings`
--

CREATE TABLE IF NOT EXISTS `settings` (
  `id` int(10) unsigned NOT NULL auto_increment,
  `systemName` varchar(255) collate utf8_czech_ci NOT NULL,
  `adminStaffId` int(10) unsigned NOT NULL,
  `timeZone` varchar(45) collate utf8_czech_ci NOT NULL,
  `active` tinyint(1) unsigned default NULL,
  PRIMARY KEY  (`id`),
  UNIQUE KEY `id_UNIQUE` (`id`),
  KEY `fk_settings_1` (`adminStaffId`)
) ENGINE=InnoDB  DEFAULT CHARSET=utf8 COLLATE=utf8_czech_ci AUTO_INCREMENT=2 ;

--
-- Vypisuji data pro tabulku `settings`
--

INSERT INTO `settings` (`id`, `systemName`, `adminStaffId`, `timeZone`, `active`) VALUES
(1, 'eManager', 1, 'GMT+2', 1);

-- --------------------------------------------------------

--
-- Struktura tabulky `ticket`
--

CREATE TABLE IF NOT EXISTS `ticket` (
  `id` int(10) unsigned NOT NULL auto_increment,
  `ticketID` varchar(100) collate utf8_czech_ci NOT NULL COMMENT 'radom generated ticket ID',
  `staffId` int(10) unsigned default NULL,
  `departmentId` int(10) unsigned NOT NULL,
  `priority` smallint(1) unsigned default NULL COMMENT '1-5, 5 highest priority',
  `name` varchar(150) collate utf8_czech_ci NOT NULL,
  `email` varchar(150) collate utf8_czech_ci NOT NULL,
  `phone` varchar(50) collate utf8_czech_ci default NULL,
  `ipAddress` varchar(50) collate utf8_czech_ci NOT NULL,
  `subject` varchar(150) collate utf8_czech_ci NOT NULL,
  `status` varchar(50) collate utf8_czech_ci NOT NULL,
  `source` enum('admin','email','phone','web','api') collate utf8_czech_ci NOT NULL,
  `closed` tinyint(1) unsigned default NULL,
  `created` int(20) unsigned NOT NULL,
  `updated` int(20) unsigned NOT NULL,
  PRIMARY KEY  (`id`),
  UNIQUE KEY `id_UNIQUE` (`id`),
  UNIQUE KEY `ticketID_UNIQUE` (`ticketID`),
  KEY `fk_ticket_1` (`departmentId`),
  KEY `fk_ticket_2` (`staffId`)
) ENGINE=InnoDB  DEFAULT CHARSET=utf8 COLLATE=utf8_czech_ci AUTO_INCREMENT=3 ;

--
-- Vypisuji data pro tabulku `ticket`
--

INSERT INTO `ticket` (`id`, `ticketID`, `staffId`, `departmentId`, `priority`, `name`, `email`, `phone`, `ipAddress`, `subject`, `status`, `source`, `closed`, `created`, `updated`) VALUES
(1, '2-1290982991-56427', NULL, 2, 1, 'Martin Venuš', 'martin.venus@gmail.com', '777341522', '78.80.152.66', 'Zkušební tiket', 'Otevřený', 'web', 0, 1290982960, 1290982960),
(2, '1-1290983099-10554', NULL, 1, 5, 'Jaroslav Líbal', 'libaljar@fit.cvut.cz', '728563841', '78.80.152.66', 'Tiket z administrace', 'Otevřený', 'admin', 0, 1290982985, 1290982985);

-- --------------------------------------------------------

--
-- Struktura tabulky `ticketBribe`
--

CREATE TABLE IF NOT EXISTS `ticketBribe` (
  `id` int(10) unsigned NOT NULL auto_increment,
  `ticketId` int(10) unsigned NOT NULL,
  `projectId` varchar(255) collate utf8_czech_ci NOT NULL,
  PRIMARY KEY  (`id`)
) ENGINE=InnoDB  DEFAULT CHARSET=utf8 COLLATE=utf8_czech_ci AUTO_INCREMENT=5 ;

--
-- Vypisuji data pro tabulku `ticketBribe`
--


-- --------------------------------------------------------

--
-- Struktura tabulky `ticketMessage`
--

CREATE TABLE IF NOT EXISTS `ticketMessage` (
  `id` int(10) unsigned NOT NULL auto_increment,
  `ticketId` int(10) unsigned NOT NULL,
  `name` varchar(150) collate utf8_czech_ci NOT NULL,
  `message` text collate utf8_czech_ci NOT NULL,
  `date` int(20) unsigned NOT NULL,
  `type` smallint(8) NOT NULL default '0' COMMENT '0 = original, 1 = reply, 2 = internal, 3 = system',
  PRIMARY KEY  (`id`),
  KEY `fk_ticketMessage_1` (`ticketId`)
) ENGINE=InnoDB  DEFAULT CHARSET=utf8 COLLATE=utf8_czech_ci AUTO_INCREMENT=146 ;

--
-- Vypisuji data pro tabulku `ticketMessage`
--

INSERT INTO `ticketMessage` (`id`, `ticketId`, `name`, `message`, `date`, `type`) VALUES
(144, 1, 'Martin Venuš', 'Zkušební zpráva', 1290982960, 0),
(145, 2, 'Jaroslav Líbal', 'Pokusný tiket pro ověření funkčnosti.', 1290982985, 0);

-- --------------------------------------------------------

--
-- Struktura tabulky `user`
--

CREATE TABLE IF NOT EXISTS `user` (
  `id` int(10) unsigned NOT NULL auto_increment COMMENT 'id',
  `userName` varchar(20) collate utf8_czech_ci NOT NULL COMMENT 'uživatelské jméno - minimálně 3 znaky, maximálně 20 znaků',
  `password` varchar(128) collate utf8_czech_ci NOT NULL COMMENT 'minimálně 8 znaků, musí obsahovat číslice a písmena',
  `time` int(20) unsigned NOT NULL COMMENT 'čas vytvoření uživatele v UNIX time',
  `firstName` varchar(50) collate utf8_czech_ci NOT NULL COMMENT 'jméno uživatele, povinné, max. 50 znaků',
  `surname` varchar(50) collate utf8_czech_ci NOT NULL COMMENT 'přijmení uživatele, povinné, max. 50 znaků',
  `title` varchar(10) collate utf8_czech_ci default NULL COMMENT 'nepovinný, max. 10 znaků',
  `email` varchar(100) collate utf8_czech_ci NOT NULL COMMENT 'e-mail, povinný, max. 100 znaků, kontrolovat formální platnost e-mailu',
  `icq` varchar(10) collate utf8_czech_ci default NULL COMMENT 'icq, nepovinné, max. 10 znaků, pouze čísla',
  `skype` varchar(50) collate utf8_czech_ci default NULL COMMENT 'skype, nepovinné, max. 50 znaků',
  `mobile` varchar(20) collate utf8_czech_ci default NULL COMMENT 'mobil, povinný, max. 20 znaků',
  `holiday` tinyint(1) NOT NULL default '0',
  `active` tinyint(1) NOT NULL default '1' COMMENT 'Informace o tom, zda je uživatel aktivní',
  PRIMARY KEY  (`id`),
  UNIQUE KEY `userName` (`userName`)
) ENGINE=InnoDB  DEFAULT CHARSET=utf8 COLLATE=utf8_czech_ci AUTO_INCREMENT=8 ;

--
-- Vypisuji data pro tabulku `user`
--

INSERT INTO `user` (`id`, `userName`, `password`, `time`, `firstName`, `surname`, `title`, `email`, `icq`, `skype`, `mobile`, `holiday`, `active`) VALUES
(1, 'venus', 'a72bf4374d03fac986e9c8b28e232e47c80802db9f8e8980e6c780d3837933501fea60b85b949b7b8a892dbe498a52e0ba2a7874c8ab03d6866f2e0fb9ff998d', 1273159061, 'Martin', 'Venuš', 'Bc.', 'venusmar@fit.cvut.cz', '159564113', 'martin.venus', '777341522', 1, 1),
(2, 'libal', 'faf070ab1181cc150d8513c3e48b6365fd84e8f231698dc3aa9bb4a5bc83777c2df3f069613f37b0a2899d6f1f7fe0fcfe80059e0bcfc86804be846ca696ad30', 1286572305, 'Jaroslav', 'Líbal', 'Bc.', 'libaljar@fit.cvut.cz', '225433217', NULL, '728563841', 1, 1),
(3, 'chervand', 'da43a4f7e103578982c5ed778ea3a37bbca869244cf57fd0ad970e7af41fd47fc1849e780b64d11fa42b19a7c7d26887fc1413acf60ee62f40d52d7e62ca0b37', 1287928380, 'Andrey', 'Chervinka', 'Bc.', 'chervand@fit.cvut.cz', '162213243', NULL, '732110286', 0, 1),
(5, 'admin', '52506f49a42d85be012c6115e3a7777fda54f6fc94a609106f5ff30a37d214eb1a3988387cbbb2f7b3d215c6ec79389afb07508b5a2246220f6e07ec3ffccab6', 1288447106, 'Jan', 'Novák', 'Ing.', 'jan.novak@nobody.cz', NULL, NULL, '777111222', 0, 1),
(6, 'user', '693862cbd26d8a7cea8d989a680e066b03f92eaa73c2ad9e5b3319323cdb65cfa1f8ab0c2bae2ec3391112a5161726fff21ddc6452b416747cb7f5977ab0afc4', 1288447180, 'František', 'Novák', 'Bc.', 'frantisek.novak@nobody.cz', NULL, NULL, '606606606', 0, 1),
(7, 'martinec', '417245975518c99b23cc598e9d9b99c0370f3090bf76dfd4ec1cac59c9b27511f1d61678bcba054814d691e632fd2ee11c20a6cbcd73a391776cded04518e242', 1290954146, 'Josef', 'Martinec', 'Bc.', 'asdds@gg.cc', NULL, NULL, '', 0, 1);

-- --------------------------------------------------------

--
-- Struktura tabulky `userDepartment`
--

CREATE TABLE IF NOT EXISTS `userDepartment` (
  `id` int(10) unsigned NOT NULL auto_increment,
  `staffId` int(10) unsigned NOT NULL,
  `departmentId` int(10) unsigned NOT NULL,
  PRIMARY KEY  (`id`),
  UNIQUE KEY `staffDepartment` (`staffId`,`departmentId`),
  KEY `fk_staffDepartment_1` (`staffId`),
  KEY `fk_staffDepartment_2` (`departmentId`)
) ENGINE=InnoDB  DEFAULT CHARSET=utf8 COLLATE=utf8_czech_ci AUTO_INCREMENT=15 ;

--
-- Vypisuji data pro tabulku `userDepartment`
--

INSERT INTO `userDepartment` (`id`, `staffId`, `departmentId`) VALUES
(1, 1, 1),
(8, 1, 2),
(3, 2, 2),
(6, 3, 2),
(9, 5, 1),
(10, 5, 2),
(11, 5, 3),
(12, 6, 1),
(13, 6, 2),
(14, 6, 3);

-- --------------------------------------------------------

--
-- Struktura tabulky `userRole`
--

CREATE TABLE IF NOT EXISTS `userRole` (
  `id` int(10) unsigned NOT NULL auto_increment COMMENT 'id',
  `userId` int(10) unsigned NOT NULL COMMENT 'id uživatele v tabulce user',
  `roleId` tinyint(4) NOT NULL COMMENT 'id role v tabulce roles',
  `active` int(1) NOT NULL,
  PRIMARY KEY  (`id`),
  UNIQUE KEY `role` (`userId`,`roleId`),
  KEY `userId` (`userId`),
  KEY `roleId` (`roleId`)
) ENGINE=InnoDB  DEFAULT CHARSET=utf8 COLLATE=utf8_czech_ci ROW_FORMAT=FIXED AUTO_INCREMENT=11 ;

--
-- Vypisuji data pro tabulku `userRole`
--

INSERT INTO `userRole` (`id`, `userId`, `roleId`, `active`) VALUES
(4, 2, 1, 1),
(5, 1, 1, 1),
(7, 3, 1, 1),
(8, 5, 1, 1),
(9, 6, 2, 1),
(10, 7, 2, 1);

--
-- Omezení pro exportované tabulky
--

--
-- Omezení pro tabulku `acl`
--
ALTER TABLE `acl`
  ADD CONSTRAINT `acl_ibfk_1` FOREIGN KEY (`role_id`) REFERENCES `roles` (`id`) ON DELETE NO ACTION ON UPDATE NO ACTION,
  ADD CONSTRAINT `acl_ibfk_2` FOREIGN KEY (`privilege_id`) REFERENCES `privileges` (`id`) ON DELETE NO ACTION ON UPDATE NO ACTION,
  ADD CONSTRAINT `acl_ibfk_3` FOREIGN KEY (`resource_id`) REFERENCES `resources` (`id`) ON DELETE NO ACTION ON UPDATE NO ACTION;

--
-- Omezení pro tabulku `api`
--
ALTER TABLE `api`
  ADD CONSTRAINT `api_ibfk_1` FOREIGN KEY (`responsibleStaffId`) REFERENCES `user` (`id`) ON DELETE NO ACTION ON UPDATE NO ACTION;

--
-- Omezení pro tabulku `bonusAPI`
--
ALTER TABLE `bonusAPI`
  ADD CONSTRAINT `bonusAPI_ibfk_1` FOREIGN KEY (`userId`) REFERENCES `user` (`id`);

--
-- Omezení pro tabulku `department`
--
ALTER TABLE `department`
  ADD CONSTRAINT `department_ibfk_1` FOREIGN KEY (`departmentManagerId`) REFERENCES `user` (`id`) ON DELETE NO ACTION ON UPDATE NO ACTION;

--
-- Omezení pro tabulku `email`
--
ALTER TABLE `email`
  ADD CONSTRAINT `fk_email_1` FOREIGN KEY (`departmentId`) REFERENCES `department` (`id`) ON DELETE NO ACTION ON UPDATE NO ACTION;

--
-- Omezení pro tabulku `kb`
--
ALTER TABLE `kb`
  ADD CONSTRAINT `fk_kb_2` FOREIGN KEY (`departmentId`) REFERENCES `department` (`id`) ON DELETE NO ACTION ON UPDATE NO ACTION,
  ADD CONSTRAINT `kb_ibfk_1` FOREIGN KEY (`createdByStaffId`) REFERENCES `user` (`id`) ON DELETE NO ACTION ON UPDATE NO ACTION;

--
-- Omezení pro tabulku `settings`
--
ALTER TABLE `settings`
  ADD CONSTRAINT `settings_ibfk_1` FOREIGN KEY (`adminStaffId`) REFERENCES `user` (`id`) ON DELETE NO ACTION ON UPDATE NO ACTION;

--
-- Omezení pro tabulku `ticket`
--
ALTER TABLE `ticket`
  ADD CONSTRAINT `fk_ticket_1` FOREIGN KEY (`departmentId`) REFERENCES `department` (`id`) ON DELETE NO ACTION ON UPDATE NO ACTION,
  ADD CONSTRAINT `ticket_ibfk_1` FOREIGN KEY (`staffId`) REFERENCES `user` (`id`) ON DELETE NO ACTION ON UPDATE NO ACTION;

--
-- Omezení pro tabulku `ticketBribe`
--
ALTER TABLE `ticketBribe`
  ADD CONSTRAINT `ticketBribe_ibfk_1` FOREIGN KEY (`id`) REFERENCES `ticket` (`id`) ON DELETE CASCADE;

--
-- Omezení pro tabulku `ticketMessage`
--
ALTER TABLE `ticketMessage`
  ADD CONSTRAINT `ticketMessage_ibfk_1` FOREIGN KEY (`ticketId`) REFERENCES `ticket` (`id`) ON DELETE CASCADE ON UPDATE NO ACTION;

--
-- Omezení pro tabulku `userDepartment`
--
ALTER TABLE `userDepartment`
  ADD CONSTRAINT `fk_staffDepartment_2` FOREIGN KEY (`departmentId`) REFERENCES `department` (`id`) ON DELETE NO ACTION ON UPDATE NO ACTION,
  ADD CONSTRAINT `userDepartment_ibfk_1` FOREIGN KEY (`staffId`) REFERENCES `user` (`id`) ON DELETE NO ACTION ON UPDATE NO ACTION;

--
-- Omezení pro tabulku `userRole`
--
ALTER TABLE `userRole`
  ADD CONSTRAINT `userRole_ibfk_1` FOREIGN KEY (`userId`) REFERENCES `user` (`id`) ON DELETE NO ACTION ON UPDATE NO ACTION,
  ADD CONSTRAINT `userRole_ibfk_2` FOREIGN KEY (`roleId`) REFERENCES `roles` (`id`) ON DELETE NO ACTION ON UPDATE NO ACTION;
