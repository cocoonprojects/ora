-- phpMyAdmin SQL Dump
-- version 3.4.11.1deb2+deb7u1
-- http://www.phpmyadmin.net
--
-- Host: localhost
-- Generato il: Dic 16, 2014 alle 11:27
-- Versione del server: 5.5.40
-- Versione PHP: 5.4.35-0+deb7u2

SET SQL_MODE="NO_AUTO_VALUE_ON_ZERO";
SET time_zone = "+00:00";

SET foreign_key_checks = 0;
/*!40101 SET @OLD_CHARACTER_SET_CLIENT=@@CHARACTER_SET_CLIENT */;
/*!40101 SET @OLD_CHARACTER_SET_RESULTS=@@CHARACTER_SET_RESULTS */;
/*!40101 SET @OLD_COLLATION_CONNECTION=@@COLLATION_CONNECTION */;
/*!40101 SET NAMES utf8 */;

--
-- Database: `oradb`
--

-- --------------------------------------------------------

--
-- Struttura della tabella `creditsAccounts`
--

CREATE TABLE IF NOT EXISTS `creditsAccounts` (
  `id` varchar(255) COLLATE utf8_unicode_ci NOT NULL,
  `createdAt` datetime NOT NULL,
  `mostRecentEditAt` datetime NOT NULL,
  `createdBy_id` varchar(255) COLLATE utf8_unicode_ci DEFAULT NULL,
  `mostRecentEditBy_id` varchar(255) COLLATE utf8_unicode_ci DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `IDX_FC9034733174800F` (`createdBy_id`),
  KEY `IDX_FC90347398F6127B` (`mostRecentEditBy_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;

-- --------------------------------------------------------

--
-- Struttura della tabella `domainEvents`
--

CREATE TABLE IF NOT EXISTS `domainEvents` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `firedAt` datetime NOT NULL,
  `aggregateId` varchar(255) COLLATE utf8_unicode_ci DEFAULT NULL,
  `attributes` longtext COLLATE utf8_unicode_ci NOT NULL COMMENT '(DC2Type:json_array)',
  `event_type` varchar(255) COLLATE utf8_unicode_ci NOT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci AUTO_INCREMENT=1 ;

-- --------------------------------------------------------

--
-- Struttura della tabella `estimation`
--

CREATE TABLE IF NOT EXISTS `estimation` (
  `id` varchar(255) COLLATE utf8_unicode_ci NOT NULL,
  `value` decimal(10,2) NOT NULL,
  `createdAt` datetime NOT NULL,
  `mostRecentEditAt` datetime NOT NULL,
  `createdBy_id` varchar(255) COLLATE utf8_unicode_ci DEFAULT NULL,
  `mostRecentEditBy_id` varchar(255) COLLATE utf8_unicode_ci DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `IDX_D05270243174800F` (`createdBy_id`),
  KEY `IDX_D052702498F6127B` (`mostRecentEditBy_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;

--
-- Dump dei dati per la tabella `estimation`
--

INSERT INTO `estimation` (`id`, `value`, `createdAt`, `mostRecentEditAt`, `createdBy_id`, `mostRecentEditBy_id`) VALUES
('asdfghjkl-srhsha', 10.50, '2014-12-05 12:21:30', '2014-12-05 12:21:30', 'f23f44eb-6ddd-4376-8bd4-b0a8371b78aa', 'f23f44eb-6ddd-4376-8bd4-b0a8371b78aa'),
('ertyuio-djsj', -1.00, '2014-12-05 12:25:07', '2014-12-05 12:25:07', 'f23f44eb-6ddd-4376-8bd4-b0a8371b78aa', 'f23f44eb-6ddd-4376-8bd4-b0a8371b78aa'),
('lkjghfd-shdh', -1.00, '2014-12-05 12:25:07', '2014-12-05 12:25:07', 'fdf5567a-09a4-4d3d-8015-a677795afd25', 'fdf5567a-09a4-4d3d-8015-a677795afd25'),
('mmmmmmm-zzzzz', 11.00, '2014-12-05 12:28:47', '2014-12-05 12:28:47', 'cd3f1219-2a15-49f3-8d2f-712509bd4a40', 'cd3f1219-2a15-49f3-8d2f-712509bd4a40'),
('qwertyui-shda', 50.20, '2014-12-05 12:23:06', '2014-12-05 12:23:06', 'fdf5567a-09a4-4d3d-8015-a677795afd25', 'fdf5567a-09a4-4d3d-8015-a677795afd25'),
('qwertyui-shda-final', 50.20, '2014-12-05 12:23:06', '2014-12-05 12:23:06', '8b839d5d-8745-4e17-af34-069f294a6ebe', '8b839d5d-8745-4e17-af34-069f294a6ebe');

-- --------------------------------------------------------

--
-- Struttura della tabella `event_stream`
--

CREATE TABLE IF NOT EXISTS `event_stream` (
  `eventId` varchar(200) COLLATE utf8_unicode_ci NOT NULL,
  `version` int(11) NOT NULL,
  `eventName` text COLLATE utf8_unicode_ci NOT NULL,
  `payload` text COLLATE utf8_unicode_ci NOT NULL,
  `occurredOn` text COLLATE utf8_unicode_ci NOT NULL,
  `aggregate_id` text COLLATE utf8_unicode_ci NOT NULL,
  `aggregate_type` text COLLATE utf8_unicode_ci NOT NULL,
  PRIMARY KEY (`eventId`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;

--
-- Dump dei dati per la tabella `event_stream`
--

INSERT INTO `event_stream` (`eventId`, `version`, `eventName`, `payload`, `occurredOn`, `aggregate_id`, `aggregate_type`) VALUES
('0360bc50-6e33-4ae0-ba9e-8ecf1ed304d5', 2, 'Ora\\TaskManagement\\TaskUpdated', 'a:3:{s:7:"subject";s:15:"task di roberta";s:2:"by";s:36:"fdf5567a-09a4-4d3d-8015-a677795afd25";s:12:"aggregate_id";s:36:"48bf3325-5fb0-4707-b64f-bc3c871273ab";}', '2014-11-21T14:33:36.000000+0000', '48bf3325-5fb0-4707-b64f-bc3c871273ab', 'Ora\\TaskManagement\\Task'),
('080455e1-7137-4a80-9192-351162b5c54a', 5, 'Ora\\TaskManagement\\TaskDeleted', 'a:3:{s:10:"prevStatus";i:20;s:2:"by";s:36:"8b839d5d-8745-4e17-af34-069f294a6ebe";s:12:"aggregate_id";s:36:"18fd61fe-b905-4213-ab5b-7fb62a8795da";}', '2014-11-21T11:06:39.000000+0000', '18fd61fe-b905-4213-ab5b-7fb62a8795da', 'Ora\\TaskManagement\\Task'),
('0c2155fd-b738-427b-a03a-c701a62fa9c5', 2, 'Ora\\TaskManagement\\TaskUpdated', 'a:3:{s:7:"subject";s:28:"un ulteriore task da stimare";s:2:"by";s:36:"8b839d5d-8745-4e17-af34-069f294a6ebe";s:12:"aggregate_id";s:36:"6c1d52ab-29fa-4792-ba96-2947fc24c2d4";}', '2014-12-16T09:12:49.000000+0000', '6c1d52ab-29fa-4792-ba96-2947fc24c2d4', 'Ora\\TaskManagement\\Task'),
('0e3d973b-312d-44e8-a069-c5db61a85be0', 3, 'Ora\\TaskManagement\\StreamChanged', 'a:3:{s:8:"streamId";s:36:"00000000-1000-0000-0000-000000000000";s:2:"by";s:36:"8b839d5d-8745-4e17-af34-069f294a6ebe";s:12:"aggregate_id";s:36:"cc1f1dad-334c-434d-aa31-ba69beef2fdf";}', '2014-12-05T13:41:35.000000+0000', 'cc1f1dad-334c-434d-aa31-ba69beef2fdf', 'Ora\\TaskManagement\\Task'),
('10020380-b1c0-4b91-9e79-d45861b8d0b8', 3, 'Ora\\TaskManagement\\StreamChanged', 'a:3:{s:8:"streamId";s:36:"00000000-1000-0000-0000-000000000000";s:2:"by";s:36:"cd3f1219-2a15-49f3-8d2f-712509bd4a40";s:12:"aggregate_id";s:36:"9f0f412e-91ef-49f2-bf57-5e5e4b1153e2";}', '2014-12-05T12:16:32.000000+0000', '9f0f412e-91ef-49f2-bf57-5e5e4b1153e2', 'Ora\\TaskManagement\\Task'),
('10098e7a-c1b1-4654-80fb-659d779f7968', 6, 'Ora\\TaskManagement\\TaskDeleted', 'a:3:{s:10:"prevStatus";i:20;s:2:"by";s:36:"fdf5567a-09a4-4d3d-8015-a677795afd25";s:12:"aggregate_id";s:36:"0a882d6e-d40f-4a25-8343-3fb5d3c2ee43";}', '2014-11-21T14:32:02.000000+0000', '0a882d6e-d40f-4a25-8343-3fb5d3c2ee43', 'Ora\\TaskManagement\\Task'),
('13caea1b-2f46-46da-98a0-621792f4a79d', 1, 'Ora\\TaskManagement\\TaskCreated', 'a:3:{s:6:"status";i:20;s:2:"by";s:36:"cd3f1219-2a15-49f3-8d2f-712509bd4a40";s:12:"aggregate_id";s:36:"9f0f412e-91ef-49f2-bf57-5e5e4b1153e2";}', '2014-12-05T12:16:32.000000+0000', '9f0f412e-91ef-49f2-bf57-5e5e4b1153e2', 'Ora\\TaskManagement\\Task'),
('151fcf60-f50b-49c9-980b-b609f3d2a0b5', 2, 'Ora\\TaskManagement\\TaskUpdated', 'a:3:{s:7:"subject";s:13:"Tutti stimano";s:2:"by";s:36:"cd3f1219-2a15-49f3-8d2f-712509bd4a40";s:12:"aggregate_id";s:36:"13136e9a-db97-42c0-835b-e4655561da4f";}', '2014-12-05T12:18:54.000000+0000', '13136e9a-db97-42c0-835b-e4655561da4f', 'Ora\\TaskManagement\\Task'),
('2556c8d6-883a-4984-b186-b335eade9bd7', 1, 'Ora\\TaskManagement\\TaskCreated', 'a:3:{s:6:"status";i:20;s:2:"by";s:36:"8b839d5d-8745-4e17-af34-069f294a6ebe";s:12:"aggregate_id";s:36:"b8f2b3d8-4851-429b-a591-f8b9666fa96f";}', '2014-12-03T12:00:58.000000+0000', 'b8f2b3d8-4851-429b-a591-f8b9666fa96f', 'Ora\\TaskManagement\\Task'),
('29d99ce5-22fe-4b6e-a5d7-972633b3219e', 3, 'Ora\\TaskManagement\\StreamChanged', 'a:3:{s:8:"streamId";s:36:"00000000-1000-0000-0000-000000000000";s:2:"by";s:36:"cd3f1219-2a15-49f3-8d2f-712509bd4a40";s:12:"aggregate_id";s:36:"411385de-30aa-4978-9d48-d18e79524a6c";}', '2014-12-05T12:18:05.000000+0000', '411385de-30aa-4978-9d48-d18e79524a6c', 'Ora\\TaskManagement\\Task'),
('2d68cf96-5a50-4c61-b5fe-baffee23541d', 4, 'Ora\\TaskManagement\\MemberAdded', 'a:4:{s:6:"userId";s:36:"8b839d5d-8745-4e17-af34-069f294a6ebe";s:4:"role";s:5:"owner";s:2:"by";s:36:"8b839d5d-8745-4e17-af34-069f294a6ebe";s:12:"aggregate_id";s:36:"cc1f1dad-334c-434d-aa31-ba69beef2fdf";}', '2014-12-05T13:41:35.000000+0000', 'cc1f1dad-334c-434d-aa31-ba69beef2fdf', 'Ora\\TaskManagement\\Task'),
('30d1d209-91a9-4280-b28e-dc0f12f0bad2', 2, 'Ora\\TaskManagement\\TaskUpdated', 'a:3:{s:7:"subject";s:19:"testing estimations";s:2:"by";s:36:"8b839d5d-8745-4e17-af34-069f294a6ebe";s:12:"aggregate_id";s:36:"cc1f1dad-334c-434d-aa31-ba69beef2fdf";}', '2014-12-05T13:41:35.000000+0000', 'cc1f1dad-334c-434d-aa31-ba69beef2fdf', 'Ora\\TaskManagement\\Task'),
('3c6f2614-adde-4064-9017-5f5af9d18662', 2, 'Ora\\TaskManagement\\TaskUpdated', 'a:3:{s:7:"subject";s:5:"prova";s:2:"by";s:36:"f23f44eb-6ddd-4376-8bd4-b0a8371b78aa";s:12:"aggregate_id";s:36:"18fd61fe-b905-4213-ab5b-7fb62a8795da";}', '2014-11-17T14:27:42.000000+0000', '18fd61fe-b905-4213-ab5b-7fb62a8795da', 'Ora\\TaskManagement\\Task'),
('3c72f70c-db68-4852-be81-f8715c9a5234', 5, 'Ora\\TaskManagement\\MemberAdded', 'a:4:{s:6:"userId";s:36:"30ad6ff5-146c-4e84-bd5f-a81f324610e5";s:4:"role";s:6:"member";s:2:"by";s:36:"30ad6ff5-146c-4e84-bd5f-a81f324610e5";s:12:"aggregate_id";s:36:"9f0f412e-91ef-49f2-bf57-5e5e4b1153e2";}', '2014-12-05T12:18:15.000000+0000', '9f0f412e-91ef-49f2-bf57-5e5e4b1153e2', 'Ora\\TaskManagement\\Task'),
('3dded08a-bd49-4ff5-8bea-9c82ace3addb', 1, 'Ora\\TaskManagement\\TaskCreated', 'a:3:{s:6:"status";i:20;s:2:"by";s:36:"f23f44eb-6ddd-4376-8bd4-b0a8371b78aa";s:12:"aggregate_id";s:36:"18fd61fe-b905-4213-ab5b-7fb62a8795da";}', '2014-11-17T14:27:42.000000+0000', '18fd61fe-b905-4213-ab5b-7fb62a8795da', 'Ora\\TaskManagement\\Task'),
('3e8e74c2-aa1e-486c-8543-d563922b5337', 5, 'Ora\\TaskManagement\\TaskUpdated', 'a:3:{s:7:"subject";s:29:"task sprint review modificato";s:2:"by";s:36:"fdf5567a-09a4-4d3d-8015-a677795afd25";s:12:"aggregate_id";s:36:"0a882d6e-d40f-4a25-8343-3fb5d3c2ee43";}', '2014-11-21T14:31:05.000000+0000', '0a882d6e-d40f-4a25-8343-3fb5d3c2ee43', 'Ora\\TaskManagement\\Task'),
('3f63655b-ed60-4bfa-aa07-40ea7d72916d', 4, 'Ora\\TaskManagement\\MemberAdded', 'a:4:{s:6:"userId";s:36:"fdf5567a-09a4-4d3d-8015-a677795afd25";s:4:"role";s:5:"owner";s:2:"by";s:36:"fdf5567a-09a4-4d3d-8015-a677795afd25";s:12:"aggregate_id";s:36:"48bf3325-5fb0-4707-b64f-bc3c871273ab";}', '2014-11-21T14:33:36.000000+0000', '48bf3325-5fb0-4707-b64f-bc3c871273ab', 'Ora\\TaskManagement\\Task'),
('4445c4f1-89de-4887-880b-176af0438223', 3, 'Ora\\TaskManagement\\StreamChanged', 'a:3:{s:8:"streamId";s:36:"00000000-1000-0000-0000-000000000000";s:2:"by";s:36:"8b839d5d-8745-4e17-af34-069f294a6ebe";s:12:"aggregate_id";s:36:"0a193097-04f5-4b05-b2b4-a08ec013df01";}', '2014-12-05T14:22:41.000000+0000', '0a193097-04f5-4b05-b2b4-a08ec013df01', 'Ora\\TaskManagement\\Task'),
('48270899-a7c4-423c-9162-2814d246fe99', 5, 'Ora\\TaskManagement\\MemberRemoved', 'a:3:{s:6:"userId";s:36:"8b839d5d-8745-4e17-af34-069f294a6ebe";s:2:"by";s:36:"8b839d5d-8745-4e17-af34-069f294a6ebe";s:12:"aggregate_id";s:36:"ee744bc5-3798-40ef-a53f-f120523807df";}', '2014-11-21T11:06:29.000000+0000', 'ee744bc5-3798-40ef-a53f-f120523807df', 'Ora\\TaskManagement\\Task'),
('48a8d580-b220-4333-ac0a-25dfe8a12089', 3, 'Ora\\TaskManagement\\StreamChanged', 'a:3:{s:8:"streamId";s:36:"00000000-1000-0000-0000-000000000000";s:2:"by";s:36:"8b839d5d-8745-4e17-af34-069f294a6ebe";s:12:"aggregate_id";s:36:"ee744bc5-3798-40ef-a53f-f120523807df";}', '2014-11-21T11:06:17.000000+0000', 'ee744bc5-3798-40ef-a53f-f120523807df', 'Ora\\TaskManagement\\Task'),
('4a732348-8fec-4828-b7a6-b4f8346cb49e', 2, 'Ora\\TaskManagement\\TaskUpdated', 'a:3:{s:7:"subject";s:13:"Nessuna stima";s:2:"by";s:36:"cd3f1219-2a15-49f3-8d2f-712509bd4a40";s:12:"aggregate_id";s:36:"9f0f412e-91ef-49f2-bf57-5e5e4b1153e2";}', '2014-12-05T12:16:32.000000+0000', '9f0f412e-91ef-49f2-bf57-5e5e4b1153e2', 'Ora\\TaskManagement\\Task'),
('4a878ce7-e554-44cc-a7cd-cc9e1dc82502', 6, 'Ora\\TaskManagement\\MemberRemoved', 'a:3:{s:6:"userId";s:36:"8b839d5d-8745-4e17-af34-069f294a6ebe";s:2:"by";s:36:"8b839d5d-8745-4e17-af34-069f294a6ebe";s:12:"aggregate_id";s:36:"48bf3325-5fb0-4707-b64f-bc3c871273ab";}', '2014-11-21T14:35:29.000000+0000', '48bf3325-5fb0-4707-b64f-bc3c871273ab', 'Ora\\TaskManagement\\Task'),
('4daac4d4-2f86-47fd-a9f8-d6851ea4ed96', 5, 'Ora\\TaskManagement\\TaskDeleted', 'a:3:{s:10:"prevStatus";i:20;s:2:"by";s:36:"8b839d5d-8745-4e17-af34-069f294a6ebe";s:12:"aggregate_id";s:36:"0a193097-04f5-4b05-b2b4-a08ec013df01";}', '2014-12-05T16:16:38.000000+0000', '0a193097-04f5-4b05-b2b4-a08ec013df01', 'Ora\\TaskManagement\\Task'),
('5085baf8-23b4-4170-bdcc-d6815c39c018', 8, 'Ora\\TaskManagement\\TaskDeleted', 'a:3:{s:10:"prevStatus";i:20;s:2:"by";s:36:"8b839d5d-8745-4e17-af34-069f294a6ebe";s:12:"aggregate_id";s:36:"ee744bc5-3798-40ef-a53f-f120523807df";}', '2014-12-05T16:16:46.000000+0000', 'ee744bc5-3798-40ef-a53f-f120523807df', 'Ora\\TaskManagement\\Task'),
('58c863c3-6173-4477-80e3-87dfe1009369', 7, 'Ora\\TaskManagement\\MemberAdded', 'a:4:{s:6:"userId";s:36:"8b839d5d-8745-4e17-af34-069f294a6ebe";s:4:"role";s:6:"member";s:2:"by";s:36:"8b839d5d-8745-4e17-af34-069f294a6ebe";s:12:"aggregate_id";s:36:"48bf3325-5fb0-4707-b64f-bc3c871273ab";}', '2014-11-21T14:36:59.000000+0000', '48bf3325-5fb0-4707-b64f-bc3c871273ab', 'Ora\\TaskManagement\\Task'),
('5943bf1e-3158-46e5-b879-d4d7ebe7fab4', 1, 'Ora\\TaskManagement\\TaskCreated', 'a:3:{s:6:"status";i:20;s:2:"by";s:36:"cd3f1219-2a15-49f3-8d2f-712509bd4a40";s:12:"aggregate_id";s:36:"411385de-30aa-4978-9d48-d18e79524a6c";}', '2014-12-05T12:18:05.000000+0000', '411385de-30aa-4978-9d48-d18e79524a6c', 'Ora\\TaskManagement\\Task'),
('6c976b3b-0e4f-4a26-9ce5-0989da3a92bc', 6, 'Ora\\TaskManagement\\MemberAdded', 'a:4:{s:6:"userId";s:36:"fdf5567a-09a4-4d3d-8015-a677795afd25";s:4:"role";s:6:"member";s:2:"by";s:36:"fdf5567a-09a4-4d3d-8015-a677795afd25";s:12:"aggregate_id";s:36:"ee744bc5-3798-40ef-a53f-f120523807df";}', '2014-11-21T14:32:38.000000+0000', 'ee744bc5-3798-40ef-a53f-f120523807df', 'Ora\\TaskManagement\\Task'),
('6ef1cc4b-c655-4216-ad3c-eb138c7f35c3', 4, 'Ora\\TaskManagement\\MemberAdded', 'a:4:{s:6:"userId";s:36:"8b839d5d-8745-4e17-af34-069f294a6ebe";s:4:"role";s:5:"owner";s:2:"by";s:36:"8b839d5d-8745-4e17-af34-069f294a6ebe";s:12:"aggregate_id";s:36:"b8f2b3d8-4851-429b-a591-f8b9666fa96f";}', '2014-12-03T12:00:58.000000+0000', 'b8f2b3d8-4851-429b-a591-f8b9666fa96f', 'Ora\\TaskManagement\\Task'),
('6f5c679f-be42-4630-b2f2-d7ebbf333b6f', 1, 'Ora\\TaskManagement\\TaskCreated', 'a:3:{s:6:"status";i:20;s:2:"by";s:36:"cd3f1219-2a15-49f3-8d2f-712509bd4a40";s:12:"aggregate_id";s:36:"13136e9a-db97-42c0-835b-e4655561da4f";}', '2014-12-05T12:18:54.000000+0000', '13136e9a-db97-42c0-835b-e4655561da4f', 'Ora\\TaskManagement\\Task'),
('7aca0038-426f-417c-84ef-6b42f46f8291', 8, 'Ora\\TaskManagement\\MemberRemoved', 'a:3:{s:6:"userId";s:36:"8b839d5d-8745-4e17-af34-069f294a6ebe";s:2:"by";s:36:"8b839d5d-8745-4e17-af34-069f294a6ebe";s:12:"aggregate_id";s:36:"48bf3325-5fb0-4707-b64f-bc3c871273ab";}', '2014-11-21T14:37:37.000000+0000', '48bf3325-5fb0-4707-b64f-bc3c871273ab', 'Ora\\TaskManagement\\Task'),
('7e0d25ee-b32d-4751-8de3-8fdb2d2d3568', 5, 'Ora\\TaskManagement\\MemberAdded', 'a:4:{s:6:"userId";s:36:"8b839d5d-8745-4e17-af34-069f294a6ebe";s:4:"role";s:6:"member";s:2:"by";s:36:"8b839d5d-8745-4e17-af34-069f294a6ebe";s:12:"aggregate_id";s:36:"48bf3325-5fb0-4707-b64f-bc3c871273ab";}', '2014-11-21T14:35:01.000000+0000', '48bf3325-5fb0-4707-b64f-bc3c871273ab', 'Ora\\TaskManagement\\Task'),
('7e5222ce-bc1a-4ad4-a1ef-b955a8766958', 3, 'Ora\\TaskManagement\\StreamChanged', 'a:3:{s:8:"streamId";s:36:"00000000-1000-0000-0000-000000000000";s:2:"by";s:36:"fdf5567a-09a4-4d3d-8015-a677795afd25";s:12:"aggregate_id";s:36:"0a882d6e-d40f-4a25-8343-3fb5d3c2ee43";}', '2014-11-21T14:28:28.000000+0000', '0a882d6e-d40f-4a25-8343-3fb5d3c2ee43', 'Ora\\TaskManagement\\Task'),
('84fcddf1-0be6-44eb-8505-bf922d9f5d44', 1, 'Ora\\TaskManagement\\TaskCreated', 'a:3:{s:6:"status";i:20;s:2:"by";s:36:"fdf5567a-09a4-4d3d-8015-a677795afd25";s:12:"aggregate_id";s:36:"0a882d6e-d40f-4a25-8343-3fb5d3c2ee43";}', '2014-11-21T14:28:28.000000+0000', '0a882d6e-d40f-4a25-8343-3fb5d3c2ee43', 'Ora\\TaskManagement\\Task'),
('85287bc2-0fe7-45fa-bfc7-3f27a2ef78e1', 4, 'Ora\\TaskManagement\\MemberAdded', 'a:4:{s:6:"userId";s:36:"cd3f1219-2a15-49f3-8d2f-712509bd4a40";s:4:"role";s:5:"owner";s:2:"by";s:36:"cd3f1219-2a15-49f3-8d2f-712509bd4a40";s:12:"aggregate_id";s:36:"9f0f412e-91ef-49f2-bf57-5e5e4b1153e2";}', '2014-12-05T12:16:32.000000+0000', '9f0f412e-91ef-49f2-bf57-5e5e4b1153e2', 'Ora\\TaskManagement\\Task'),
('8b613a28-c2a6-4d42-864d-641104b3af03', 3, 'Ora\\TaskManagement\\StreamChanged', 'a:3:{s:8:"streamId";s:36:"00000000-1000-0000-0000-000000000000";s:2:"by";s:36:"cd3f1219-2a15-49f3-8d2f-712509bd4a40";s:12:"aggregate_id";s:36:"13136e9a-db97-42c0-835b-e4655561da4f";}', '2014-12-05T12:18:54.000000+0000', '13136e9a-db97-42c0-835b-e4655561da4f', 'Ora\\TaskManagement\\Task'),
('93309a9a-53de-4061-b63c-2c6ed7d76301', 1, 'Ora\\TaskManagement\\TaskCreated', 'a:3:{s:4:"task";C:23:"Ora\\TaskManagement\\Task":112:{a:3:{s:2:"id";s:36:"00000000-0000-0000-0000-000000000001";s:7:"subject";s:13:"My First Task";s:6:"status";i:20;}}s:9:"createdBy";C:13:"Ora\\User\\User":170:{a:5:{s:2:"id";s:36:"60000000-0000-0000-0000-000000000000";s:5:"email";s:21:"mark.rogers@ora.local";s:9:"firstname";s:4:"Mark";s:8:"lastname";s:6:"Rogers";s:6:"status";N;}}s:12:"aggregate_id";s:36:"00000000-0000-0000-0000-000000000001";}', '2014-11-02T13:24:41.000000+0100', '00000000-0000-0000-0000-000000000001', 'Ora\\TaskManagement\\Task'),
('93309a9a-53de-4061-b63c-2c6ed7d76302', 1, 'Ora\\TaskManagement\\TaskCreated', 'a:3:{s:4:"task";C:23:"Ora\\TaskManagement\\Task":112:{a:3:{s:2:"id";s:36:"00000000-0000-0000-0000-000000000000";s:7:"subject";s:13:"My First Task";s:6:"status";i:20;}}s:9:"createdBy";C:13:"Ora\\User\\User":170:{a:5:{s:2:"id";s:36:"60000000-0000-0000-0000-000000000000";s:5:"email";s:21:"mark.rogers@ora.local";s:9:"firstname";s:4:"Mark";s:8:"lastname";s:6:"Rogers";s:6:"status";N;}}s:12:"aggregate_id";s:36:"00000000-0000-0000-0000-000000000000";}', '2014-11-02T13:24:41.000000+0100', '00000000-0000-0000-0000-000000000000', 'Ora\\TaskManagement\\Task'),
('93309a9a-53de-4061-b63c-2c6ed7d76303', 1, 'Ora\\TaskManagement\\TaskCreated', 'a:3:{s:4:"task";C:23:"Ora\\TaskManagement\\Task":112:{a:3:{s:2:"id";s:36:"00000000-0000-0000-0000-000000000002";s:7:"subject";s:13:"My First Task";s:6:"status";i:20;}}s:9:"createdBy";C:13:"Ora\\User\\User":170:{a:5:{s:2:"id";s:36:"60000000-0000-0000-0000-000000000000";s:5:"email";s:21:"mark.rogers@ora.local";s:9:"firstname";s:4:"Mark";s:8:"lastname";s:6:"Rogers";s:6:"status";N;}}s:12:"aggregate_id";s:36:"00000000-0000-0000-0000-000000000002";}', '2014-11-02T13:24:41.000000+0100', '00000000-0000-0000-0000-000000000002', 'Ora\\TaskManagement\\Task'),
('93309a9a-53de-4061-b63c-2c6ed7d76304', 1, 'Ora\\TaskManagement\\TaskCreated', 'a:3:{s:4:"task";C:23:"Ora\\TaskManagement\\Task":112:{a:3:{s:2:"id";s:36:"00000000-0000-0000-0000-000000000003";s:7:"subject";s:13:"My First Task";s:6:"status";i:20;}}s:9:"createdBy";C:13:"Ora\\User\\User":170:{a:5:{s:2:"id";s:36:"70000000-0000-0000-0000-000000000004";s:5:"email";s:21:"mark.rogers@ora.local";s:9:"firstname";s:4:"Mark";s:8:"lastname";s:6:"Rogers";s:6:"status";N;}}s:12:"aggregate_id";s:36:"00000000-0000-0000-0000-000000000003";}', '2014-11-02T13:24:41.000000+0100', '00000000-0000-0000-0000-000000000003', 'Ora\\TaskManagement\\Task'),
('93309a9a-53de-4061-b63c-2c6ed7d76305', 1, 'Ora\\TaskManagement\\TaskCreated', 'a:3:{s:4:"task";C:23:"Ora\\TaskManagement\\Task":112:{a:3:{s:2:"id";s:36:"00000000-0000-0000-0000-000000000004";s:7:"subject";s:13:"My First Task";s:6:"status";i:20;}}s:9:"createdBy";C:13:"Ora\\User\\User":170:{a:5:{s:2:"id";s:36:"70000000-0000-0000-0000-000000000004";s:5:"email";s:21:"mark.rogers@ora.local";s:9:"firstname";s:4:"Mark";s:8:"lastname";s:6:"Rogers";s:6:"status";N;}}s:12:"aggregate_id";s:36:"00000000-0000-0000-0000-000000000004";}', '2014-11-02T13:24:41.000000+0100', '00000000-0000-0000-0000-000000000004', 'Ora\\TaskManagement\\Task'),
('9a8f1c68-be28-436d-9fec-5f071481145a', 11, 'Ora\\TaskManagement\\TaskDeleted', 'a:3:{s:10:"prevStatus";i:20;s:2:"by";s:36:"8b839d5d-8745-4e17-af34-069f294a6ebe";s:12:"aggregate_id";s:36:"48bf3325-5fb0-4707-b64f-bc3c871273ab";}', '2014-12-05T16:17:11.000000+0000', '48bf3325-5fb0-4707-b64f-bc3c871273ab', 'Ora\\TaskManagement\\Task'),
('a0d5bd28-511d-40f7-9a73-8bcb48e32d35', 2, 'Ora\\TaskManagement\\StreamChanged', 'a:4:{s:4:"task";C:23:"Ora\\TaskManagement\\Task":112:{a:3:{s:2:"id";s:36:"00000000-0000-0000-0000-000000000004";s:7:"subject";s:13:"My First Task";s:6:"status";i:20;}}s:6:"stream";C:29:"Ora\\StreamManagement\\Stream":94:{a:2:{s:2:"id";s:36:"00000000-1000-0000-0000-000000000000";s:7:"subject";s:12:"First stream";}}s:9:"updatedBy";C:13:"Ora\\User\\User":170:{a:5:{s:2:"id";s:36:"70000000-0000-0000-0000-000000000004";s:5:"email";s:21:"mark.rogers@ora.local";s:9:"firstname";s:4:"Mark";s:8:"lastname";s:6:"Rogers";s:6:"status";N;}}s:12:"aggregate_id";s:36:"00000000-0000-0000-0000-000000000004";}', '2014-11-02T13:24:41.000000+0100', '00000000-0000-0000-0000-000000000004', 'Ora\\TaskManagement\\Task'),
('a0d5bd28-511d-40f7-9a73-8bcb48e32d36', 2, 'Ora\\TaskManagement\\StreamChanged', 'a:4:{s:4:"task";C:23:"Ora\\TaskManagement\\Task":112:{a:3:{s:2:"id";s:36:"00000000-0000-0000-0000-000000000003";s:7:"subject";s:13:"My First Task";s:6:"status";i:20;}}s:6:"stream";C:29:"Ora\\StreamManagement\\Stream":94:{a:2:{s:2:"id";s:36:"00000000-1000-0000-0000-000000000000";s:7:"subject";s:12:"First stream";}}s:9:"updatedBy";C:13:"Ora\\User\\User":170:{a:5:{s:2:"id";s:36:"70000000-0000-0000-0000-000000000004";s:5:"email";s:21:"mark.rogers@ora.local";s:9:"firstname";s:4:"Mark";s:8:"lastname";s:6:"Rogers";s:6:"status";N;}}s:12:"aggregate_id";s:36:"00000000-0000-0000-0000-000000000003";}', '2014-11-02T13:24:41.000000+0100', '00000000-0000-0000-0000-000000000003', 'Ora\\TaskManagement\\Task'),
('a0d5bd28-511d-40f7-9a73-8bcb48e32d37', 2, 'Ora\\TaskManagement\\StreamChanged', 'a:4:{s:4:"task";C:23:"Ora\\TaskManagement\\Task":112:{a:3:{s:2:"id";s:36:"00000000-0000-0000-0000-000000000002";s:7:"subject";s:13:"My First Task";s:6:"status";i:20;}}s:6:"stream";C:29:"Ora\\StreamManagement\\Stream":94:{a:2:{s:2:"id";s:36:"00000000-1000-0000-0000-000000000000";s:7:"subject";s:12:"First stream";}}s:9:"updatedBy";C:13:"Ora\\User\\User":170:{a:5:{s:2:"id";s:36:"60000000-0000-0000-0000-000000000000";s:5:"email";s:21:"mark.rogers@ora.local";s:9:"firstname";s:4:"Mark";s:8:"lastname";s:6:"Rogers";s:6:"status";N;}}s:12:"aggregate_id";s:36:"00000000-0000-0000-0000-000000000002";}', '2014-11-02T13:24:41.000000+0100', '00000000-0000-0000-0000-000000000002', 'Ora\\TaskManagement\\Task'),
('a0d5bd28-511d-40f7-9a73-8bcb48e32d38', 2, 'Ora\\TaskManagement\\StreamChanged', 'a:4:{s:4:"task";C:23:"Ora\\TaskManagement\\Task":112:{a:3:{s:2:"id";s:36:"00000000-0000-0000-0000-000000000001";s:7:"subject";s:13:"My First Task";s:6:"status";i:20;}}s:6:"stream";C:29:"Ora\\StreamManagement\\Stream":94:{a:2:{s:2:"id";s:36:"00000000-1000-0000-0000-000000000000";s:7:"subject";s:12:"First stream";}}s:9:"updatedBy";C:13:"Ora\\User\\User":170:{a:5:{s:2:"id";s:36:"60000000-0000-0000-0000-000000000000";s:5:"email";s:21:"mark.rogers@ora.local";s:9:"firstname";s:4:"Mark";s:8:"lastname";s:6:"Rogers";s:6:"status";N;}}s:12:"aggregate_id";s:36:"00000000-0000-0000-0000-000000000001";}', '2014-11-02T13:24:41.000000+0100', '00000000-0000-0000-0000-000000000001', 'Ora\\TaskManagement\\Task'),
('a0d5bd28-511d-40f7-9a73-8bcb48e32d39', 2, 'Ora\\TaskManagement\\StreamChanged', 'a:4:{s:4:"task";C:23:"Ora\\TaskManagement\\Task":112:{a:3:{s:2:"id";s:36:"00000000-0000-0000-0000-000000000000";s:7:"subject";s:13:"My First Task";s:6:"status";i:20;}}s:6:"stream";C:29:"Ora\\StreamManagement\\Stream":94:{a:2:{s:2:"id";s:36:"00000000-1000-0000-0000-000000000000";s:7:"subject";s:13:"First stream";}}s:9:"updatedBy";C:13:"Ora\\User\\User":170:{a:5:{s:2:"id";s:36:"60000000-0000-0000-0000-000000000000";s:5:"email";s:21:"mark.rogers@ora.local";s:9:"firstname";s:4:"Mark";s:8:"lastname";s:6:"Rogers";s:6:"status";N;}}s:12:"aggregate_id";s:36:"00000000-0000-0000-0000-000000000000";}', '2014-11-02T13:24:41.000000+0100', '00000000-0000-0000-0000-000000000000', 'Ora\\TaskManagement\\Task'),
('a1a560df-9e37-45d6-8cc1-df4fff87feb8', 1, 'Ora\\TaskManagement\\TaskCreated', 'a:3:{s:6:"status";i:20;s:2:"by";s:36:"8b839d5d-8745-4e17-af34-069f294a6ebe";s:12:"aggregate_id";s:36:"6c1d52ab-29fa-4792-ba96-2947fc24c2d4";}', '2014-12-16T09:12:49.000000+0000', '6c1d52ab-29fa-4792-ba96-2947fc24c2d4', 'Ora\\TaskManagement\\Task'),
('a4152c0c-6d9d-4f73-b96e-d0edcde060d8', 2, 'Ora\\TaskManagement\\TaskUpdated', 'a:3:{s:7:"subject";s:18:"task sprint review";s:2:"by";s:36:"fdf5567a-09a4-4d3d-8015-a677795afd25";s:12:"aggregate_id";s:36:"0a882d6e-d40f-4a25-8343-3fb5d3c2ee43";}', '2014-11-21T14:28:28.000000+0000', '0a882d6e-d40f-4a25-8343-3fb5d3c2ee43', 'Ora\\TaskManagement\\Task'),
('a65c18e7-37c4-4e59-a0fd-d0a6e6b2cbe6', 10, 'Ora\\TaskManagement\\MemberRemoved', 'a:3:{s:6:"userId";s:36:"8b839d5d-8745-4e17-af34-069f294a6ebe";s:2:"by";s:36:"8b839d5d-8745-4e17-af34-069f294a6ebe";s:12:"aggregate_id";s:36:"48bf3325-5fb0-4707-b64f-bc3c871273ab";}', '2014-12-03T12:02:04.000000+0000', '48bf3325-5fb0-4707-b64f-bc3c871273ab', 'Ora\\TaskManagement\\Task'),
('a7c86646-392c-4727-a965-9a62223cfbb4', 2, 'Ora\\TaskManagement\\TaskUpdated', 'a:3:{s:7:"subject";s:19:"task for estimation";s:2:"by";s:36:"8b839d5d-8745-4e17-af34-069f294a6ebe";s:12:"aggregate_id";s:36:"0a193097-04f5-4b05-b2b4-a08ec013df01";}', '2014-12-05T14:22:41.000000+0000', '0a193097-04f5-4b05-b2b4-a08ec013df01', 'Ora\\TaskManagement\\Task'),
('a97536e9-dec3-437f-a542-e2ed0d853546', 3, 'Ora\\TaskManagement\\StreamChanged', 'a:3:{s:8:"streamId";s:36:"00000000-1100-0000-0000-000000000000";s:2:"by";s:36:"f23f44eb-6ddd-4376-8bd4-b0a8371b78aa";s:12:"aggregate_id";s:36:"18fd61fe-b905-4213-ab5b-7fb62a8795da";}', '2014-11-17T14:27:42.000000+0000', '18fd61fe-b905-4213-ab5b-7fb62a8795da', 'Ora\\TaskManagement\\Task'),
('a9e1a093-43ba-42e0-9a0a-bb50ac65e0a9', 1, 'Ora\\TaskManagement\\TaskCreated', 'a:3:{s:6:"status";i:20;s:2:"by";s:36:"8b839d5d-8745-4e17-af34-069f294a6ebe";s:12:"aggregate_id";s:36:"cc1f1dad-334c-434d-aa31-ba69beef2fdf";}', '2014-12-05T13:41:35.000000+0000', 'cc1f1dad-334c-434d-aa31-ba69beef2fdf', 'Ora\\TaskManagement\\Task'),
('afb67d9e-362b-44c4-90d4-581ea7b3d8bb', 4, 'Ora\\TaskManagement\\MemberAdded', 'a:4:{s:6:"userId";s:36:"8b839d5d-8745-4e17-af34-069f294a6ebe";s:4:"role";s:5:"owner";s:2:"by";s:36:"8b839d5d-8745-4e17-af34-069f294a6ebe";s:12:"aggregate_id";s:36:"6c1d52ab-29fa-4792-ba96-2947fc24c2d4";}', '2014-12-16T09:12:49.000000+0000', '6c1d52ab-29fa-4792-ba96-2947fc24c2d4', 'Ora\\TaskManagement\\Task'),
('b02371b2-7168-4d34-83ff-ac6c8ba75784', 3, 'Ora\\TaskManagement\\StreamChanged', 'a:3:{s:8:"streamId";s:36:"00000000-1000-0000-0000-000000000000";s:2:"by";s:36:"8b839d5d-8745-4e17-af34-069f294a6ebe";s:12:"aggregate_id";s:36:"b8f2b3d8-4851-429b-a591-f8b9666fa96f";}', '2014-12-03T12:00:58.000000+0000', 'b8f2b3d8-4851-429b-a591-f8b9666fa96f', 'Ora\\TaskManagement\\Task'),
('b11f4529-46f2-4849-8ad7-9ea801b96b7e', 2, 'Ora\\TaskManagement\\TaskUpdated', 'a:3:{s:7:"subject";s:13:"task giovanni";s:2:"by";s:36:"8b839d5d-8745-4e17-af34-069f294a6ebe";s:12:"aggregate_id";s:36:"b8f2b3d8-4851-429b-a591-f8b9666fa96f";}', '2014-12-03T12:00:58.000000+0000', 'b8f2b3d8-4851-429b-a591-f8b9666fa96f', 'Ora\\TaskManagement\\Task'),
('b40cb642-ed51-4500-8f90-6721057c915b', 1, 'Ora\\TaskManagement\\TaskCreated', 'a:3:{s:6:"status";i:20;s:2:"by";s:36:"fdf5567a-09a4-4d3d-8015-a677795afd25";s:12:"aggregate_id";s:36:"48bf3325-5fb0-4707-b64f-bc3c871273ab";}', '2014-11-21T14:33:36.000000+0000', '48bf3325-5fb0-4707-b64f-bc3c871273ab', 'Ora\\TaskManagement\\Task'),
('b80fd21d-4288-4041-b283-24a20c6b1a10', 3, 'Ora\\TaskManagement\\MemberAdded', 'a:4:{s:4:"task";C:23:"Ora\\TaskManagement\\Task":112:{a:3:{s:2:"id";s:36:"00000000-0000-0000-0000-000000000000";s:7:"subject";s:13:"My First Task";s:6:"status";i:20;}}s:4:"user";C:13:"Ora\\User\\User":170:{a:5:{s:2:"id";s:36:"60000000-0000-0000-0000-000000000000";s:5:"email";s:21:"mark.rogers@ora.local";s:9:"firstname";s:4:"Mark";s:8:"lastname";s:6:"Rogers";s:6:"status";N;}}s:7:"addedBy";r:7;s:12:"aggregate_id";s:36:"00000000-0000-0000-0000-000000000000";}', '2014-11-02T13:24:41.000000+0100', '00000000-0000-0000-0000-000000000000', 'Ora\\TaskManagement\\Task'),
('b80fd21d-4288-4041-b283-24a20c6b1a11', 3, 'Ora\\TaskManagement\\MemberAdded', 'a:4:{s:4:"task";C:23:"Ora\\TaskManagement\\Task":112:{a:3:{s:2:"id";s:36:"00000000-0000-0000-0000-000000000001";s:7:"subject";s:13:"My First Task";s:6:"status";i:20;}}s:4:"user";C:13:"Ora\\User\\User":170:{a:5:{s:2:"id";s:36:"60000000-0000-0000-0000-000000000000";s:5:"email";s:21:"mark.rogers@ora.local";s:9:"firstname";s:4:"Mark";s:8:"lastname";s:6:"Rogers";s:6:"status";N;}}s:7:"addedBy";r:7;s:12:"aggregate_id";s:36:"00000000-0000-0000-0000-000000000001";}', '2014-11-02T13:24:41.000000+0100', '00000000-0000-0000-0000-000000000001', 'Ora\\TaskManagement\\Task'),
('b80fd21d-4288-4041-b283-24a20c6b1a12', 3, 'Ora\\TaskManagement\\MemberAdded', 'a:4:{s:4:"task";C:23:"Ora\\TaskManagement\\Task":112:{a:3:{s:2:"id";s:36:"00000000-0000-0000-0000-000000000002";s:7:"subject";s:13:"My First Task";s:6:"status";i:20;}}s:4:"user";C:13:"Ora\\User\\User":170:{a:5:{s:2:"id";s:36:"60000000-0000-0000-0000-000000000000";s:5:"email";s:21:"mark.rogers@ora.local";s:9:"firstname";s:4:"Mark";s:8:"lastname";s:6:"Rogers";s:6:"status";N;}}s:7:"addedBy";r:7;s:12:"aggregate_id";s:36:"00000000-0000-0000-0000-000000000002";}', '2014-11-02T13:24:41.000000+0100', '00000000-0000-0000-0000-000000000002', 'Ora\\TaskManagement\\Task'),
('b80fd21d-4288-4041-b283-24a20c6b1a13', 3, 'Ora\\TaskManagement\\MemberAdded', 'a:4:{s:4:"task";C:23:"Ora\\TaskManagement\\Task":112:{a:3:{s:2:"id";s:36:"00000000-0000-0000-0000-000000000003";s:7:"subject";s:13:"My First Task";s:6:"status";i:20;}}s:4:"user";C:13:"Ora\\User\\User":170:{a:5:{s:2:"id";s:36:"70000000-0000-0000-0000-000000000004";s:5:"email";s:21:"mark.rogers@ora.local";s:9:"firstname";s:4:"Mark";s:8:"lastname";s:6:"Rogers";s:6:"status";N;}}s:7:"addedBy";r:7;s:12:"aggregate_id";s:36:"00000000-0000-0000-0000-000000000003";}', '2014-11-02T13:24:41.000000+0100', '00000000-0000-0000-0000-000000000003', 'Ora\\TaskManagement\\Task'),
('b80fd21d-4288-4041-b283-24a20c6b1a15', 3, 'Ora\\TaskManagement\\MemberAdded', 'a:4:{s:4:"task";C:23:"Ora\\TaskManagement\\Task":112:{a:3:{s:2:"id";s:36:"00000000-0000-0000-0000-000000000004";s:7:"subject";s:13:"My First Task";s:6:"status";i:20;}}s:4:"user";C:13:"Ora\\User\\User":170:{a:5:{s:2:"id";s:36:"70000000-0000-0000-0000-000000000004";s:5:"email";s:21:"mark.rogers@ora.local";s:9:"firstname";s:4:"Mark";s:8:"lastname";s:6:"Rogers";s:6:"status";N;}}s:7:"addedBy";r:7;s:12:"aggregate_id";s:36:"00000000-0000-0000-0000-000000000004";}', '2014-11-02T13:24:41.000000+0100', '00000000-0000-0000-0000-000000000004', 'Ora\\TaskManagement\\Task'),
('bae9184b-fee5-4347-b314-c3dbe1cca779', 5, 'Ora\\TaskManagement\\TaskDeleted', 'a:3:{s:10:"prevStatus";i:20;s:2:"by";s:36:"8b839d5d-8745-4e17-af34-069f294a6ebe";s:12:"aggregate_id";s:36:"b8f2b3d8-4851-429b-a591-f8b9666fa96f";}', '2014-12-03T12:01:15.000000+0000', 'b8f2b3d8-4851-429b-a591-f8b9666fa96f', 'Ora\\TaskManagement\\Task'),
('c1733de3-f22e-42bb-b242-914f7c270104', 1, 'Ora\\TaskManagement\\TaskCreated', 'a:3:{s:6:"status";i:20;s:2:"by";s:36:"8b839d5d-8745-4e17-af34-069f294a6ebe";s:12:"aggregate_id";s:36:"0a193097-04f5-4b05-b2b4-a08ec013df01";}', '2014-12-05T14:22:41.000000+0000', '0a193097-04f5-4b05-b2b4-a08ec013df01', 'Ora\\TaskManagement\\Task'),
('c7321e23-8ec0-4288-bec6-5980a76bb194', 5, 'Ora\\TaskManagement\\MemberAdded', 'a:4:{s:6:"userId";s:36:"30ad6ff5-146c-4e84-bd5f-a81f324610e5";s:4:"role";s:6:"member";s:2:"by";s:36:"30ad6ff5-146c-4e84-bd5f-a81f324610e5";s:12:"aggregate_id";s:36:"411385de-30aa-4978-9d48-d18e79524a6c";}', '2014-12-05T12:18:35.000000+0000', '411385de-30aa-4978-9d48-d18e79524a6c', 'Ora\\TaskManagement\\Task'),
('c8f318a9-0637-4d51-8a57-29292fad831a', 5, 'Ora\\TaskManagement\\MemberAdded', 'a:4:{s:6:"userId";s:36:"30ad6ff5-146c-4e84-bd5f-a81f324610e5";s:4:"role";s:6:"member";s:2:"by";s:36:"30ad6ff5-146c-4e84-bd5f-a81f324610e5";s:12:"aggregate_id";s:36:"13136e9a-db97-42c0-835b-e4655561da4f";}', '2014-12-05T12:19:08.000000+0000', '13136e9a-db97-42c0-835b-e4655561da4f', 'Ora\\TaskManagement\\Task'),
('cccbf8f6-8280-4a76-9e07-0ccccb662058', 4, 'Ora\\TaskManagement\\MemberAdded', 'a:4:{s:6:"userId";s:36:"cd3f1219-2a15-49f3-8d2f-712509bd4a40";s:4:"role";s:5:"owner";s:2:"by";s:36:"cd3f1219-2a15-49f3-8d2f-712509bd4a40";s:12:"aggregate_id";s:36:"13136e9a-db97-42c0-835b-e4655561da4f";}', '2014-12-05T12:18:54.000000+0000', '13136e9a-db97-42c0-835b-e4655561da4f', 'Ora\\TaskManagement\\Task'),
('d3370dc8-f72a-4894-9752-d7a6cf53a6fb', 1, 'Ora\\TaskManagement\\TaskCreated', 'a:3:{s:6:"status";i:20;s:2:"by";s:36:"8b839d5d-8745-4e17-af34-069f294a6ebe";s:12:"aggregate_id";s:36:"ee744bc5-3798-40ef-a53f-f120523807df";}', '2014-11-21T11:06:17.000000+0000', 'ee744bc5-3798-40ef-a53f-f120523807df', 'Ora\\TaskManagement\\Task'),
('d39f8c89-f259-4769-9249-7b79ebfa8b0c', 4, 'Ora\\TaskManagement\\MemberAdded', 'a:4:{s:6:"userId";s:36:"fdf5567a-09a4-4d3d-8015-a677795afd25";s:4:"role";s:5:"owner";s:2:"by";s:36:"fdf5567a-09a4-4d3d-8015-a677795afd25";s:12:"aggregate_id";s:36:"0a882d6e-d40f-4a25-8343-3fb5d3c2ee43";}', '2014-11-21T14:28:28.000000+0000', '0a882d6e-d40f-4a25-8343-3fb5d3c2ee43', 'Ora\\TaskManagement\\Task'),
('d6108809-2ddc-483b-9735-276d79e5a5e6', 3, 'Ora\\TaskManagement\\StreamChanged', 'a:3:{s:8:"streamId";s:36:"00000000-1000-0000-0000-000000000000";s:2:"by";s:36:"8b839d5d-8745-4e17-af34-069f294a6ebe";s:12:"aggregate_id";s:36:"6c1d52ab-29fa-4792-ba96-2947fc24c2d4";}', '2014-12-16T09:12:49.000000+0000', '6c1d52ab-29fa-4792-ba96-2947fc24c2d4', 'Ora\\TaskManagement\\Task'),
('d638c1f8-fc04-4434-90a8-5cca8f804db5', 3, 'Ora\\TaskManagement\\StreamChanged', 'a:3:{s:8:"streamId";s:36:"00000000-1000-0000-0000-000000000000";s:2:"by";s:36:"fdf5567a-09a4-4d3d-8015-a677795afd25";s:12:"aggregate_id";s:36:"48bf3325-5fb0-4707-b64f-bc3c871273ab";}', '2014-11-21T14:33:36.000000+0000', '48bf3325-5fb0-4707-b64f-bc3c871273ab', 'Ora\\TaskManagement\\Task'),
('dfa0f352-1dfe-4020-9b21-1cf42726c676', 7, 'Ora\\TaskManagement\\MemberRemoved', 'a:3:{s:6:"userId";s:36:"fdf5567a-09a4-4d3d-8015-a677795afd25";s:2:"by";s:36:"fdf5567a-09a4-4d3d-8015-a677795afd25";s:12:"aggregate_id";s:36:"ee744bc5-3798-40ef-a53f-f120523807df";}', '2014-11-21T14:33:02.000000+0000', 'ee744bc5-3798-40ef-a53f-f120523807df', 'Ora\\TaskManagement\\Task'),
('e62f6cb7-e3ab-4caf-9dc4-94b151e04bf3', 4, 'Ora\\TaskManagement\\MemberAdded', 'a:4:{s:6:"userId";s:36:"f23f44eb-6ddd-4376-8bd4-b0a8371b78aa";s:4:"role";s:5:"owner";s:2:"by";s:36:"f23f44eb-6ddd-4376-8bd4-b0a8371b78aa";s:12:"aggregate_id";s:36:"18fd61fe-b905-4213-ab5b-7fb62a8795da";}', '2014-11-17T14:27:42.000000+0000', '18fd61fe-b905-4213-ab5b-7fb62a8795da', 'Ora\\TaskManagement\\Task'),
('e6a77dc1-be54-448d-87b9-a3f1e88b0502', 9, 'Ora\\TaskManagement\\MemberAdded', 'a:4:{s:6:"userId";s:36:"8b839d5d-8745-4e17-af34-069f294a6ebe";s:4:"role";s:6:"member";s:2:"by";s:36:"8b839d5d-8745-4e17-af34-069f294a6ebe";s:12:"aggregate_id";s:36:"48bf3325-5fb0-4707-b64f-bc3c871273ab";}', '2014-12-03T12:00:20.000000+0000', '48bf3325-5fb0-4707-b64f-bc3c871273ab', 'Ora\\TaskManagement\\Task'),
('eeb49158-106c-49de-90d1-c8afbc9215d6', 4, 'Ora\\TaskManagement\\MemberAdded', 'a:4:{s:6:"userId";s:36:"cd3f1219-2a15-49f3-8d2f-712509bd4a40";s:4:"role";s:5:"owner";s:2:"by";s:36:"cd3f1219-2a15-49f3-8d2f-712509bd4a40";s:12:"aggregate_id";s:36:"411385de-30aa-4978-9d48-d18e79524a6c";}', '2014-12-05T12:18:05.000000+0000', '411385de-30aa-4978-9d48-d18e79524a6c', 'Ora\\TaskManagement\\Task'),
('ef66d798-bd14-4485-81c8-a043a3a9dd9a', 4, 'Ora\\TaskManagement\\MemberAdded', 'a:4:{s:6:"userId";s:36:"8b839d5d-8745-4e17-af34-069f294a6ebe";s:4:"role";s:5:"owner";s:2:"by";s:36:"8b839d5d-8745-4e17-af34-069f294a6ebe";s:12:"aggregate_id";s:36:"0a193097-04f5-4b05-b2b4-a08ec013df01";}', '2014-12-05T14:22:41.000000+0000', '0a193097-04f5-4b05-b2b4-a08ec013df01', 'Ora\\TaskManagement\\Task'),
('f492f730-ba5f-4c51-af53-3d3ce13de7ba', 2, 'Ora\\TaskManagement\\TaskUpdated', 'a:3:{s:7:"subject";s:29:"Tutti decidono di non stimare";s:2:"by";s:36:"cd3f1219-2a15-49f3-8d2f-712509bd4a40";s:12:"aggregate_id";s:36:"411385de-30aa-4978-9d48-d18e79524a6c";}', '2014-12-05T12:18:05.000000+0000', '411385de-30aa-4978-9d48-d18e79524a6c', 'Ora\\TaskManagement\\Task'),
('f503d8c9-8a5e-4b9f-94cc-3b284729e4fe', 2, 'Ora\\TaskManagement\\TaskUpdated', 'a:3:{s:7:"subject";s:26:"test primo task su staging";s:2:"by";s:36:"8b839d5d-8745-4e17-af34-069f294a6ebe";s:12:"aggregate_id";s:36:"ee744bc5-3798-40ef-a53f-f120523807df";}', '2014-11-21T11:06:17.000000+0000', 'ee744bc5-3798-40ef-a53f-f120523807df', 'Ora\\TaskManagement\\Task'),
('f5a41c23-16d2-467b-b07c-4e6b3426e426', 4, 'Ora\\TaskManagement\\MemberAdded', 'a:4:{s:6:"userId";s:36:"8b839d5d-8745-4e17-af34-069f294a6ebe";s:4:"role";s:5:"owner";s:2:"by";s:36:"8b839d5d-8745-4e17-af34-069f294a6ebe";s:12:"aggregate_id";s:36:"ee744bc5-3798-40ef-a53f-f120523807df";}', '2014-11-21T11:06:17.000000+0000', 'ee744bc5-3798-40ef-a53f-f120523807df', 'Ora\\TaskManagement\\Task'),
('fb836e1e-a236-4851-b5a8-4891ae6f9ffc', 5, 'Ora\\TaskManagement\\TaskDeleted', 'a:3:{s:10:"prevStatus";i:20;s:2:"by";s:36:"8b839d5d-8745-4e17-af34-069f294a6ebe";s:12:"aggregate_id";s:36:"cc1f1dad-334c-434d-aa31-ba69beef2fdf";}', '2014-12-05T16:16:53.000000+0000', 'cc1f1dad-334c-434d-aa31-ba69beef2fdf', 'Ora\\TaskManagement\\Task'),
('fbdfdd17-61ef-4f80-bcd4-7e6eb6990703', 2, 'Ora\\TaskManagement\\TaskCompleted', 'a:2:{s:12:"aggregate_id";s:36:"00000000-0000-0000-0000-000000000001";s:11:"completedBy";C:13:"Ora\\User\\User":155:{a:4:{s:2:"id";s:36:"60000000-0000-0000-0000-000000000000";s:5:"email";s:21:"mark.rogers@ora.local";s:9:"firstname";s:4:"Mark";s:8:"lastname";s:6:"Rogers";}}}', '2014-10-31T10:44:30.000000+0100', '00000000-0000-0000-0000-000000000001', 'Ora\\TaskManagement\\Task'),
('fbdfdd17-61ef-4f80-bcd4-7e6eb6990713', 2, 'Ora\\TaskManagement\\TaskCompleted', 'a:2:{s:12:"aggregate_id";s:36:"00000000-0000-0000-0000-000000000002";s:11:"completedBy";C:13:"Ora\\User\\User":155:{a:4:{s:2:"id";s:36:"60000000-0000-0000-0000-000000000000";s:5:"email";s:21:"mark.rogers@ora.local";s:9:"firstname";s:4:"Mark";s:8:"lastname";s:6:"Rogers";}}}', '2014-10-31T10:44:30.000000+0100', '00000000-0000-0000-0000-000000000002', 'Ora\\TaskManagement\\Task'),
('fbdfdd17-61ef-4f80-bcd4-7e6eb6990714', 2, 'Ora\\TaskManagement\\TaskAccepted', 'a:2:{s:12:"aggregate_id";s:36:"00000000-0000-0000-0000-000000000002";s:10:"acceptedBy";C:13:"Ora\\User\\User":155:{a:4:{s:2:"id";s:36:"60000000-0000-0000-0000-000000000000";s:5:"email";s:21:"mark.rogers@ora.local";s:9:"firstname";s:4:"Mark";s:8:"lastname";s:6:"Rogers";}}}', '2014-10-31T10:44:30.000000+0100', '00000000-0000-0000-0000-000000000002', 'Ora\\TaskManagement\\Task');

-- --------------------------------------------------------

--
-- Struttura della tabella `organizations`
--

CREATE TABLE IF NOT EXISTS `organizations` (
  `id` varchar(255) COLLATE utf8_unicode_ci NOT NULL,
  `createdAt` datetime NOT NULL,
  `mostRecentEditAt` datetime NOT NULL,
  `subject` varchar(255) COLLATE utf8_unicode_ci NOT NULL,
  `mostRecentEditBy_id` varchar(255) COLLATE utf8_unicode_ci DEFAULT NULL,
  `createdBy_id` varchar(255) COLLATE utf8_unicode_ci DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `IDX_427C1C7F98F6127B` (`mostRecentEditBy_id`),
  KEY `IDX_427C1C7F3174800F` (`createdBy_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;

-- --------------------------------------------------------

--
-- Struttura della tabella `organization_users`
--

CREATE TABLE IF NOT EXISTS `organization_users` (
  `user_id` varchar(255) COLLATE utf8_unicode_ci NOT NULL,
  `organization_id` varchar(255) COLLATE utf8_unicode_ci NOT NULL,
  `organizationRole` varchar(255) COLLATE utf8_unicode_ci NOT NULL,
  `createdAt` datetime NOT NULL,
  `createdBy_id` varchar(255) COLLATE utf8_unicode_ci DEFAULT NULL,
  `id` varchar(255) COLLATE utf8_unicode_ci NOT NULL,
  `mostRecentEditAt` datetime NOT NULL,
  `mostRecentEditBy_id` varchar(255) COLLATE utf8_unicode_ci DEFAULT NULL,
  PRIMARY KEY (`user_id`,`organization_id`,`id`),
  KEY `IDX_9A04432E3174800F` (`createdBy_id`),
  KEY `IDX_9A04432EA76ED395` (`user_id`),
  KEY `IDX_9A04432E32C8A3DE` (`organization_id`),
  KEY `IDX_9A04432E98F6127B` (`mostRecentEditBy_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;

-- --------------------------------------------------------

--
-- Struttura della tabella `streams`
--

CREATE TABLE IF NOT EXISTS `streams` (
  `id` varchar(255) COLLATE utf8_unicode_ci NOT NULL,
  `createdAt` datetime NOT NULL,
  `subject` varchar(255) COLLATE utf8_unicode_ci NOT NULL,
  `mostRecentEditAt` datetime NOT NULL,
  `mostRecentEditBy_id` varchar(255) COLLATE utf8_unicode_ci DEFAULT NULL,
  `createdBy_id` varchar(255) COLLATE utf8_unicode_ci DEFAULT NULL,
  `organization_id` varchar(255) COLLATE utf8_unicode_ci NOT NULL,
  PRIMARY KEY (`id`),
  KEY `IDX_5C93B3A498F6127B` (`mostRecentEditBy_id`),
  KEY `IDX_5C93B3A43174800F` (`createdBy_id`),
  KEY `IDX_5C93B3A432C8A3DE` (`organization_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;

--
-- Dump dei dati per la tabella `streams`
--

INSERT INTO `streams` (`id`, `createdAt`, `subject`, `mostRecentEditAt`, `mostRecentEditBy_id`, `createdBy_id`, `organization_id`) VALUES
('00000000-1000-0000-0000-000000000000', '2014-11-06 13:11:05', 'O.R.A.: Organization Resource Aggregator', '2014-11-06 13:11:05', NULL, NULL, ''),
('00000000-1100-0000-0000-000000000000', '2014-11-06 13:11:05', 'Open Goverance', '2014-11-06 13:11:05', NULL, NULL, '');

-- --------------------------------------------------------

--
-- Struttura della tabella `tasks`
--

CREATE TABLE IF NOT EXISTS `tasks` (
  `id` varchar(255) COLLATE utf8_unicode_ci NOT NULL,
  `createdAt` datetime NOT NULL,
  `subject` varchar(255) COLLATE utf8_unicode_ci NOT NULL,
  `status` int(11) NOT NULL,
  `mostRecentEditAt` datetime NOT NULL,
  `mostRecentEditBy_id` varchar(255) COLLATE utf8_unicode_ci DEFAULT NULL,
  `stream_id` varchar(255) COLLATE utf8_unicode_ci NOT NULL,
  `type` varchar(255) COLLATE utf8_unicode_ci NOT NULL,
  `createdBy_id` varchar(255) COLLATE utf8_unicode_ci DEFAULT NULL,
  `boardId` varchar(255) COLLATE utf8_unicode_ci DEFAULT NULL,
  `taskId` varchar(255) COLLATE utf8_unicode_ci DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `IDX_5058659798F6127B` (`mostRecentEditBy_id`),
  KEY `IDX_50586597166D1F9C` (`stream_id`),
  KEY `IDX_505865973174800F` (`createdBy_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;

--
-- Dump dei dati per la tabella `tasks`
--

INSERT INTO `tasks` (`id`, `createdAt`, `subject`, `status`, `mostRecentEditAt`, `mostRecentEditBy_id`, `stream_id`, `type`, `createdBy_id`, `boardId`, `taskId`) VALUES
('00000000-0000-0000-0000-000000000101', '2014-11-06 13:11:05', 'BATMAN', 0, '2014-11-06 13:11:05', NULL, '00000000-1000-0000-0000-000000000000', 'kanbanizetask', NULL, '3', '111'),
('00000000-0000-0000-0000-000000000102', '2014-11-06 13:11:45', 'JOKER', 0, '2014-11-06 13:11:45', NULL, '00000000-1000-0000-0000-000000000000', 'kanbanizetask', NULL, '3', '112'),
('00000000-0000-0000-0000-000000000103', '2014-11-06 13:12:14', 'POISONIVY', 0, '2014-11-06 13:12:14', NULL, '00000000-1000-0000-0000-000000000000', 'kanbanizetask', NULL, '3', '113'),
('00000000-0000-0000-0000-000000000104', '2014-11-06 13:12:50', 'wrongbatman', 0, '2014-11-06 13:12:50', NULL, '00000000-1000-0000-0000-000000000000', 'kanbanizetask', NULL, '4', '1'),
('00000000-0000-0000-0000-000000000105', '2014-11-06 13:13:15', 'wrongbatmanagain', 0, '2014-11-06 13:13:15', NULL, '00000000-1000-0000-0000-000000000000', 'kanbanizetask', NULL, '3', '69'),
('00000000-0000-0000-0000-0000000001060', '2014-11-06 14:32:44', 'acceptedTask', 40, '2014-11-06 14:32:44', NULL, '00000000-1000-0000-0000-000000000000', 'kanbanizetask', NULL, '3', '114'),
('00000000-0000-0000-0000-000000000107', '2014-11-06 14:43:28', 'completedTask', 30, '2014-11-06 14:43:28', NULL, '00000000-1000-0000-0000-000000000000', 'kanbanizetask', NULL, '3', '115'),
('00000000-0000-0000-0000-000000000108', '2014-11-06 14:51:12', 'ongoingTask', 20, '2014-11-06 14:51:12', NULL, '00000000-1000-0000-0000-000000000000', 'kanbanizetask', NULL, '3', '116'),
('00000000-0000-0000-0000-000000000109', '2014-11-06 15:03:02', 'completedTask', 30, '2014-11-06 15:03:02', NULL, '00000000-1000-0000-0000-000000000000', 'kanbanizetask', NULL, '3', '117'),
('00000000-0000-0000-0000-000000000110', '2014-11-06 15:39:13', 'completedTask', 30, '2014-11-06 15:39:13', NULL, '00000000-1000-0000-0000-000000000000', 'kanbanizetask', NULL, '3', '119'),
('00000000-0000-0000-0000-000000000111', '2014-11-06 15:48:17', 'completedTask', 40, '2014-11-06 15:48:17', NULL, '00000000-1000-0000-0000-000000000000', 'kanbanizetask', NULL, '3', '120'),
('00000000-0000-0000-0000-000000000112', '2014-11-06 15:56:56', 'ongoingTask', 20, '2014-11-06 15:56:56', NULL, '00000000-1000-0000-0000-000000000000', 'kanbanizetask', NULL, '3', '118'),
('1', '2014-10-09 11:33:45', 'UNA ROTONDA SUL MARE', 20, '0000-00-00 00:00:00', NULL, '00000000-1000-0000-0000-000000000000', 'task', '1', NULL, NULL),
('13136e9a-db97-42c0-835b-e4655561da4f', '2014-12-05 12:18:54', 'Non abbastanza stime', 20, '2014-12-05 12:19:08', '30ad6ff5-146c-4e84-bd5f-a81f324610e5', '00000000-1000-0000-0000-000000000000', 'task', 'cd3f1219-2a15-49f3-8d2f-712509bd4a40', NULL, NULL),
('2', '2014-08-12 14:22:55', 'THIS TASK HAVE RANDOM SUBJECT', 20, '0000-00-00 00:00:00', NULL, '00000000-1000-0000-0000-000000000000', 'task', '2', NULL, NULL),
('3', '2014-08-12 14:22:55', 'JUST ANOTHER SIMPLE TASK', 20, '0000-00-00 00:00:00', NULL, '00000000-1000-0000-0000-000000000000', 'task', '3', NULL, NULL),
('4', '2014-08-12 14:22:55', 'REMEMBER TO SOLVE ALL TODO', 40, '0000-00-00 00:00:00', NULL, '00000000-1000-0000-0000-000000000000', 'task', '4', NULL, NULL),
('411385de-30aa-4978-9d48-d18e79524a6c', '2014-12-05 12:18:05', 'Tutti decidono di non stimare', 20, '2014-12-05 12:18:35', '30ad6ff5-146c-4e84-bd5f-a81f324610e5', '00000000-1000-0000-0000-000000000000', 'task', 'cd3f1219-2a15-49f3-8d2f-712509bd4a40', NULL, NULL),
('6c1d52ab-29fa-4792-ba96-2947fc24c2d4', '2014-12-16 09:12:49', 'un ulteriore task da stimare', 20, '2014-12-16 09:12:49', '8b839d5d-8745-4e17-af34-069f294a6ebe', '00000000-1000-0000-0000-000000000000', 'task', '8b839d5d-8745-4e17-af34-069f294a6ebe', NULL, NULL),
('9f0f412e-91ef-49f2-bf57-5e5e4b1153e2', '2014-12-05 12:16:32', 'Tutti stimano', 20, '2014-12-05 12:18:15', '30ad6ff5-146c-4e84-bd5f-a81f324610e5', '00000000-1000-0000-0000-000000000000', 'task', 'cd3f1219-2a15-49f3-8d2f-712509bd4a40', NULL, NULL);

-- --------------------------------------------------------

--
-- Struttura della tabella `tasks_members`
--

CREATE TABLE IF NOT EXISTS `tasks_members` (
  `task_id` varchar(255) COLLATE utf8_unicode_ci NOT NULL,
  `member_id` varchar(255) COLLATE utf8_unicode_ci NOT NULL,
  `role` varchar(255) COLLATE utf8_unicode_ci NOT NULL,
  `estimation_id` varchar(255) COLLATE utf8_unicode_ci DEFAULT NULL,
  PRIMARY KEY (`task_id`,`member_id`),
  UNIQUE KEY `UNIQ_15EDA879F35F62F2` (`estimation_id`),
  KEY `IDX_15EDA8798DB60186` (`task_id`),
  KEY `IDX_15EDA8797597D3FE` (`member_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;

--
-- Dump dei dati per la tabella `tasks_members`
--

INSERT INTO `tasks_members` (`task_id`, `member_id`, `role`, `estimation_id`) VALUES
('13136e9a-db97-42c0-835b-e4655561da4f', '30ad6ff5-146c-4e84-bd5f-a81f324610e5', 'member', 'mmmmmmm-zzzzz'),
('13136e9a-db97-42c0-835b-e4655561da4f', 'cd3f1219-2a15-49f3-8d2f-712509bd4a40', 'owner', NULL),
('411385de-30aa-4978-9d48-d18e79524a6c', '30ad6ff5-146c-4e84-bd5f-a81f324610e5', 'member', 'lkjghfd-shdh'),
('411385de-30aa-4978-9d48-d18e79524a6c', 'cd3f1219-2a15-49f3-8d2f-712509bd4a40', 'owner', 'ertyuio-djsj'),
('6c1d52ab-29fa-4792-ba96-2947fc24c2d4', '30ad6ff5-146c-4e84-bd5f-a81f324610e5', 'member', NULL),
('6c1d52ab-29fa-4792-ba96-2947fc24c2d4', '8b839d5d-8745-4e17-af34-069f294a6ebe', 'owner', 'qwertyui-shda-final'),
('9f0f412e-91ef-49f2-bf57-5e5e4b1153e2', '30ad6ff5-146c-4e84-bd5f-a81f324610e5', 'member', 'asdfghjkl-srhsha'),
('9f0f412e-91ef-49f2-bf57-5e5e4b1153e2', 'cd3f1219-2a15-49f3-8d2f-712509bd4a40', 'owner', 'qwertyui-shda');

-- --------------------------------------------------------

--
-- Struttura della tabella `task_users`
--

CREATE TABLE IF NOT EXISTS `task_users` (
  `task_id` varchar(255) COLLATE utf8_unicode_ci NOT NULL,
  `user_id` varchar(255) COLLATE utf8_unicode_ci NOT NULL,
  PRIMARY KEY (`task_id`,`user_id`),
  KEY `IDX_D327BEC98DB60186` (`task_id`),
  KEY `IDX_D327BEC9A76ED395` (`user_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;

-- --------------------------------------------------------

--
-- Struttura della tabella `teams`
--

CREATE TABLE IF NOT EXISTS `teams` (
  `task_id` varchar(255) COLLATE utf8_unicode_ci NOT NULL,
  `user_id` varchar(255) COLLATE utf8_unicode_ci NOT NULL,
  PRIMARY KEY (`task_id`,`user_id`),
  KEY `IDX_96C222588DB60186` (`task_id`),
  KEY `IDX_96C22258A76ED395` (`user_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;

--
-- Dump dei dati per la tabella `teams`
--

INSERT INTO `teams` (`task_id`, `user_id`) VALUES
('1', '1'),
('1', '2'),
('2', '1'),
('2', '2'),
('2', '4'),
('3', '3'),
('4', '4');

-- --------------------------------------------------------

--
-- Struttura della tabella `users`
--

CREATE TABLE IF NOT EXISTS `users` (
  `id` varchar(255) COLLATE utf8_unicode_ci NOT NULL,
  `createdAt` datetime NOT NULL,
  `mostRecentEditAt` datetime NOT NULL,
  `mostRecentEditBy_id` varchar(255) COLLATE utf8_unicode_ci DEFAULT NULL,
  `createdBy_id` varchar(255) COLLATE utf8_unicode_ci DEFAULT NULL,
  `firstname` varchar(100) COLLATE utf8_unicode_ci DEFAULT NULL,
  `lastname` varchar(100) COLLATE utf8_unicode_ci DEFAULT NULL,
  `email` varchar(200) COLLATE utf8_unicode_ci NOT NULL,
  `status` int(11) NOT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `UNIQ_1483A5E9E7927C74` (`email`),
  KEY `IDX_1483A5E998F6127B` (`mostRecentEditBy_id`),
  KEY `IDX_1483A5E93174800F` (`createdBy_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;

--
-- Dump dei dati per la tabella `users`
--

INSERT INTO `users` (`id`, `createdAt`, `mostRecentEditAt`, `mostRecentEditBy_id`, `createdBy_id`, `firstname`, `lastname`, `email`, `status`) VALUES
('04a1c762-44dc-404c-ac33-3c0723a39c8e', '2014-11-07 11:37:58', '2014-11-07 11:37:58', '04a1c762-44dc-404c-ac33-3c0723a39c8e', '04a1c762-44dc-404c-ac33-3c0723a39c8e', 'Roberta', 'D''Amico', 'roberta.damico5@gmail.com', 1),
('1', '2014-10-09 11:33:45', '0000-00-00 00:00:00', NULL, '1', NULL, NULL, 'fabio.giannotti@carmati.it', 0),
('1e7f697b-a478-40eb-88de-5ce00b153184', '2014-11-24 09:28:39', '2014-11-24 09:28:39', NULL, NULL, 'Pierfrancesco', 'Raimondo', 'pierfrancesco.raimondo@gmail.com', 1),
('2', '2014-10-09 11:33:45', '0000-00-00 00:00:00', NULL, '1', NULL, NULL, 'mario.tillia@carmati.it', 0),
('20000000-0000-0000-0000-000000000000', '2014-11-17 00:00:00', '2014-11-17 00:00:00', '04a1c762-44dc-404c-ac33-3c0723a39c8e', '04a1c762-44dc-404c-ac33-3c0723a39c8e', 'TEST', 'TEST', '', 0),
('3', '2014-06-15 07:11:42', '0000-00-00 00:00:00', NULL, '1', NULL, NULL, 'roberta.damico3@carmati.it', 0),
('30ad6ff5-146c-4e84-bd5f-a81f324610e5', '2014-11-21 14:42:19', '2014-11-21 14:42:19', NULL, NULL, 'Abdon', 'Serianni', 'serianniabdon@gmail.com', 1),
('4', '2014-07-13 08:23:13', '0000-00-00 00:00:00', NULL, '1', NULL, NULL, 'giovanni.dicampli@carmati.it', 0),
('525b53d3-f3e7-46e7-b018-30c354e91cec', '2014-11-22 00:49:38', '2014-11-22 00:49:38', NULL, NULL, 'Andrea', 'Bandera', 'dottorbabba@gmail.com', 1),
('6f6e8120-b16a-480d-91ab-40c22e6a86b4', '2014-11-24 12:40:48', '2014-11-24 12:40:48', NULL, NULL, 'Pierfrancesco', 'Raimondo', 'sephiroth.91584@gmail.com', 1),
('8b839d5d-8745-4e17-af34-069f294a6ebe', '2014-11-07 14:52:46', '2014-11-07 14:52:46', '8b839d5d-8745-4e17-af34-069f294a6ebe', '8b839d5d-8745-4e17-af34-069f294a6ebe', 'Mario', 'Tilli', 'mario.tilli@carmati.it', 1),
('cd3f1219-2a15-49f3-8d2f-712509bd4a40', '2014-11-21 11:12:58', '2014-11-21 11:12:58', NULL, NULL, 'Andrea', 'Lupia', 'trk.andie@gmail.com', 1),
('f23f44eb-6ddd-4376-8bd4-b0a8371b78aa', '2014-11-07 11:09:03', '2014-11-07 11:09:03', 'f23f44eb-6ddd-4376-8bd4-b0a8371b78aa', 'f23f44eb-6ddd-4376-8bd4-b0a8371b78aa', 'Mario', 'Tilli', 'mariotilli@gmail.com', 1),
('fdf5567a-09a4-4d3d-8015-a677795afd25', '2014-11-07 13:26:05', '2014-11-07 13:26:05', 'fdf5567a-09a4-4d3d-8015-a677795afd25', 'fdf5567a-09a4-4d3d-8015-a677795afd25', 'Roberta', 'D''Amico', 'roberta.damico@carmati.it', 1);

-- --------------------------------------------------------

--
-- Struttura della tabella `user_organizations`
--

CREATE TABLE IF NOT EXISTS `user_organizations` (
  `user_id` varchar(255) COLLATE utf8_unicode_ci NOT NULL,
  `organization_id` varchar(255) COLLATE utf8_unicode_ci NOT NULL,
  `organizationRole` varchar(255) COLLATE utf8_unicode_ci NOT NULL,
  `createdAt` datetime NOT NULL,
  `createdBy_id` varchar(255) COLLATE utf8_unicode_ci DEFAULT NULL,
  PRIMARY KEY (`user_id`,`organization_id`),
  KEY `IDX_ACF2B12F3174800F` (`createdBy_id`),
  KEY `IDX_ACF2B12FA76ED395` (`user_id`),
  KEY `IDX_ACF2B12F32C8A3DE` (`organization_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;

--
-- Limiti per le tabelle scaricate
--

--
-- Limiti per la tabella `creditsAccounts`
--
ALTER TABLE `creditsAccounts`
  ADD CONSTRAINT `FK_FC9034733174800F` FOREIGN KEY (`createdBy_id`) REFERENCES `users` (`id`),
  ADD CONSTRAINT `FK_FC90347398F6127B` FOREIGN KEY (`mostRecentEditBy_id`) REFERENCES `users` (`id`);

--
-- Limiti per la tabella `task_users`
--
ALTER TABLE `task_users`
  ADD CONSTRAINT `FK_D327BEC98DB60186` FOREIGN KEY (`task_id`) REFERENCES `tasks` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `FK_D327BEC9A76ED395` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE CASCADE;

--
-- Limiti per la tabella `teams`
--
ALTER TABLE `teams`
  ADD CONSTRAINT `FK_96C222588DB60186` FOREIGN KEY (`task_id`) REFERENCES `tasks` (`id`),
  ADD CONSTRAINT `FK_96C22258A76ED395` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`);

--
-- Limiti per la tabella `user_organizations`
--
ALTER TABLE `user_organizations`
  ADD CONSTRAINT `FK_ACF2B12F3174800F` FOREIGN KEY (`createdBy_id`) REFERENCES `users` (`id`),
  ADD CONSTRAINT `FK_ACF2B12F32C8A3DE` FOREIGN KEY (`organization_id`) REFERENCES `organizations` (`id`),
  ADD CONSTRAINT `FK_ACF2B12FA76ED395` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`);

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;

SET foreign_key_checks = 1;
