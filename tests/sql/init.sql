set FOREIGN_KEY_CHECKS = 0;
delete from tasks;
delete from streams;
truncate event_stream;
delete from users;

INSERT INTO users (id, status, createdAt, firstname, lastname, email) VALUES ('60000000-0000-0000-0000-000000000000', 1, "2014-10-09 11:33:45", "Mark", "Rogers", "mark.rogers@ora.local");
INSERT INTO users (id, status, createdAt, firstname, lastname, email) VALUES ('70000000-0000-0000-0000-000000000000', 1, "2014-10-09 11:33:45", "Phil", "Toledo", "phil.toledo@ora.local");
INSERT INTO users (id, status, createdAt, firstname, lastname, email) VALUES ('20000000-0000-0000-0000-000000000000', 1, "2014-10-09 11:33:45", "Paul", "Smith", "paul.smith@ora.local");
INSERT INTO users (id, status, createdAt, firstname, lastname, email) VALUES ('80000000-0000-0000-0000-000000000000', 1, "2014-10-09 11:33:45", "Bruce", "Wayne", "bruce.wayne@gotham.local");
INSERT INTO users (id, status, createdAt, firstname, lastname, email) VALUES ('90000000-0000-0000-0000-000000000000', 1, "2014-10-09 11:33:45", "Peter", "Parker", "spidey.web@dailybugle.local");

INSERT INTO `event_stream` (`eventId`, `version`, `eventName`, `payload`, `occurredOn`, `aggregate_type`, `aggregate_id`) VALUES
('6126d983-20ad-47f2-9636-085395aa3b4b',1,'Ora\\TaskManagement\\TaskCreated','a:3:{s:6:\"status\";i:20;s:2:\"by\";s:36:\"60000000-0000-0000-0000-000000000000\";s:12:\"aggregate_id\";s:36:\"00000000-0000-0000-0000-000000000000\";}','2014-11-12T19:07:59.000000+0100','Ora\\TaskManagement\\Task','00000000-0000-0000-0000-000000000000');
INSERT INTO `event_stream` (`eventId`, `version`, `eventName`, `payload`, `occurredOn`, `aggregate_type`, `aggregate_id`) VALUES
('334fa91f-62c9-4b34-827b-3e01bd7efe5c',2,'Ora\\TaskManagement\\TaskUpdated','a:3:{s:7:\"subject\";s:33:\"Setup the development environment\";s:2:\"by\";s:36:\"60000000-0000-0000-0000-000000000000\";s:12:\"aggregate_id\";s:36:\"00000000-0000-0000-0000-000000000000\";}','2014-11-12T19:07:59.000000+0100','Ora\\TaskManagement\\Task','00000000-0000-0000-0000-000000000000');
INSERT INTO `event_stream` (`eventId`, `version`, `eventName`, `payload`, `occurredOn`, `aggregate_type`, `aggregate_id`) VALUES
('6e943d08-7a9a-4a2a-a8b5-201bfeb57b62',3,'Ora\\TaskManagement\\streamChanged','a:3:{s:9:\"streamId\";s:36:\"00000000-1000-0000-0000-000000000000\";s:2:\"by\";s:36:\"60000000-0000-0000-0000-000000000000\";s:12:\"aggregate_id\";s:36:\"00000000-0000-0000-0000-000000000000\";}','2014-11-12T19:07:59.000000+0100','Ora\\TaskManagement\\Task','00000000-0000-0000-0000-000000000000');
INSERT INTO `event_stream` (`eventId`, `version`, `eventName`, `payload`, `occurredOn`, `aggregate_type`, `aggregate_id`) VALUES
('c033eb32-2ad4-49d5-b25c-3c0b600b7dec',4,'Ora\\TaskManagement\\MemberAdded','a:4:{s:6:\"userId\";s:36:\"60000000-0000-0000-0000-000000000000\";s:4:\"role\";s:5:\"owner\";s:2:\"by\";s:36:\"60000000-0000-0000-0000-000000000000\";s:12:\"aggregate_id\";s:36:\"00000000-0000-0000-0000-000000000000\";}','2014-11-12T19:07:59.000000+0100','Ora\\TaskManagement\\Task','00000000-0000-0000-0000-000000000000');

INSERT INTO `event_stream` (`eventId`, `version`, `eventName`, `payload`, `occurredOn`, `aggregate_type`, `aggregate_id`) VALUES
('6126d983-20ad-47f2-9636-085395aa3b5b',1,'Ora\\TaskManagement\\TaskCreated','a:3:{s:6:\"status\";i:20;s:2:\"by\";s:36:\"60000000-0000-0000-0000-000000000000\";s:12:\"aggregate_id\";s:36:\"00000000-0000-0000-0000-000000000001\";}','2014-11-12T19:07:59.000000+0100','Ora\\TaskManagement\\Task','00000000-0000-0000-0000-000000000001');
INSERT INTO `event_stream` (`eventId`, `version`, `eventName`, `payload`, `occurredOn`, `aggregate_type`, `aggregate_id`) VALUES
('334fa91f-62c9-4b34-827b-3e01bd7efe6c',2,'Ora\\TaskManagement\\TaskUpdated','a:3:{s:7:\"subject\";s:33:\"Setup the development environment\";s:2:\"by\";s:36:\"60000000-0000-0000-0000-000000000000\";s:12:\"aggregate_id\";s:36:\"00000000-0000-0000-0000-000000000001\";}','2014-11-12T19:07:59.000000+0100','Ora\\TaskManagement\\Task','00000000-0000-0000-0000-000000000001');
INSERT INTO `event_stream` (`eventId`, `version`, `eventName`, `payload`, `occurredOn`, `aggregate_type`, `aggregate_id`) VALUES
('6e943d08-7a9a-4a2a-a8b5-201bfeb57b72',3,'Ora\\TaskManagement\\streamChanged','a:3:{s:9:\"streamId\";s:36:\"00000000-1000-0000-0000-000000000000\";s:2:\"by\";s:36:\"60000000-0000-0000-0000-000000000000\";s:12:\"aggregate_id\";s:36:\"00000000-0000-0000-0000-000000000001\";}','2014-11-12T19:07:59.000000+0100','Ora\\TaskManagement\\Task','00000000-0000-0000-0000-000000000001');
INSERT INTO `event_stream` (`eventId`, `version`, `eventName`, `payload`, `occurredOn`, `aggregate_type`, `aggregate_id`) VALUES
('c033eb32-2ad4-49d5-b25c-3c0b600b7dfc',4,'Ora\\TaskManagement\\MemberAdded','a:4:{s:6:\"userId\";s:36:\"60000000-0000-0000-0000-000000000000\";s:4:\"role\";s:5:\"owner\";s:2:\"by\";s:36:\"60000000-0000-0000-0000-000000000000\";s:12:\"aggregate_id\";s:36:\"00000000-0000-0000-0000-000000000001\";}','2014-11-12T19:07:59.000000+0100','Ora\\TaskManagement\\Task','00000000-0000-0000-0000-000000000001');
INSERT INTO `event_stream` (eventId, version, eventName, payload, occurredOn, aggregate_type, aggregate_id) VALUES 
('fbdfdd17-61ef-4f80-bcd4-7e6eb6990713',2,'Ora\\TaskManagement\\TaskCompleted','a:2:{s:12:\"aggregate_id\";s:36:\"00000000-0000-0000-0000-000000000001\";s:2:\"by\";s:36:\"60000000-0000-0000-0000-000000000000\";}','2014-10-31T10:44:30.000000+0100','Ora\\TaskManagement\\Task','00000000-0000-0000-0000-000000000001');

INSERT INTO `event_stream` (`eventId`, `version`, `eventName`, `payload`, `occurredOn`, `aggregate_type`, `aggregate_id`) VALUES
('6126d983-20ad-47f2-9636-085395aa3b6b',1,'Ora\\TaskManagement\\TaskCreated','a:3:{s:6:\"status\";i:20;s:2:\"by\";s:36:\"60000000-0000-0000-0000-000000000000\";s:12:\"aggregate_id\";s:36:\"00000000-0000-0000-0000-000000000002\";}','2014-11-12T19:07:59.000000+0100','Ora\\TaskManagement\\Task','00000000-0000-0000-0000-000000000002');
INSERT INTO `event_stream` (`eventId`, `version`, `eventName`, `payload`, `occurredOn`, `aggregate_type`, `aggregate_id`) VALUES
('334fa91f-62c9-4b34-827b-3e01bd7efe7c',2,'Ora\\TaskManagement\\TaskUpdated','a:3:{s:7:\"subject\";s:33:\"Setup the development environment\";s:2:\"by\";s:36:\"60000000-0000-0000-0000-000000000000\";s:12:\"aggregate_id\";s:36:\"00000000-0000-0000-0000-000000000002\";}','2014-11-12T19:07:59.000000+0100','Ora\\TaskManagement\\Task','00000000-0000-0000-0000-000000000002');
INSERT INTO `event_stream` (`eventId`, `version`, `eventName`, `payload`, `occurredOn`, `aggregate_type`, `aggregate_id`) VALUES
('6e943d08-7a9a-4a2a-a8b5-201bfeb57b82',3,'Ora\\TaskManagement\\streamChanged','a:3:{s:9:\"streamId\";s:36:\"00000000-1000-0000-0000-000000000000\";s:2:\"by\";s:36:\"60000000-0000-0000-0000-000000000000\";s:12:\"aggregate_id\";s:36:\"00000000-0000-0000-0000-000000000002\";}','2014-11-12T19:07:59.000000+0100','Ora\\TaskManagement\\Task','00000000-0000-0000-0000-000000000002');
INSERT INTO `event_stream` (`eventId`, `version`, `eventName`, `payload`, `occurredOn`, `aggregate_type`, `aggregate_id`) VALUES
('c033eb32-2ad4-49d5-b25c-3c0b600b7d9c',4,'Ora\\TaskManagement\\MemberAdded','a:4:{s:6:\"userId\";s:36:\"60000000-0000-0000-0000-000000000000\";s:4:\"role\";s:5:\"owner\";s:2:\"by\";s:36:\"60000000-0000-0000-0000-000000000000\";s:12:\"aggregate_id\";s:36:\"00000000-0000-0000-0000-000000000002\";}','2014-11-12T19:07:59.000000+0100','Ora\\TaskManagement\\Task','00000000-0000-0000-0000-000000000002');
INSERT INTO `event_stream` (eventId, version, eventName, payload, occurredOn, aggregate_type, aggregate_id) VALUES
('fbdfdd17-61ef-4f80-bcd4-7e6eb6990723',2,'Ora\\TaskManagement\\TaskCompleted','a:2:{s:12:\"aggregate_id\";s:36:\"00000000-0000-0000-0000-000000000002\";s:2:\"by\";s:36:\"60000000-0000-0000-0000-000000000000\";}','2014-10-31T10:44:30.000000+0100','Ora\\TaskManagement\\Task','00000000-0000-0000-0000-000000000002');
INSERT INTO `event_stream` (eventId, version, eventName, payload, occurredOn, aggregate_type, aggregate_id) VALUES
('fbdfdd17-61ef-4f80-bcd4-7e6eb6990714',2,'Ora\\TaskManagement\\TaskAccepted','a:2:{s:12:\"aggregate_id\";s:36:\"00000000-0000-0000-0000-000000000002\";s:2:\"by\";s:36:\"60000000-0000-0000-0000-000000000000\";}','2014-10-31T10:44:30.000000+0100','Ora\\TaskManagement\\Task','00000000-0000-0000-0000-000000000002');

INSERT INTO `event_stream` (`eventId`, `version`, `eventName`, `payload`, `occurredOn`, `aggregate_type`, `aggregate_id`) VALUES
('6126d983-20ad-47f2-9636-085395aa3b7b',1,'Ora\\TaskManagement\\TaskCreated','a:3:{s:6:\"status\";i:20;s:2:\"by\";s:36:\"60000000-0000-0000-0000-000000000000\";s:12:\"aggregate_id\";s:36:\"00000000-0000-0000-0000-000000000003\";}','2014-11-12T19:07:59.000000+0100','Ora\\TaskManagement\\Task','00000000-0000-0000-0000-000000000003');
INSERT INTO `event_stream` (`eventId`, `version`, `eventName`, `payload`, `occurredOn`, `aggregate_type`, `aggregate_id`) VALUES
('334fa91f-62c9-4b34-827b-3e01bd7efe8c',2,'Ora\\TaskManagement\\TaskUpdated','a:3:{s:7:\"subject\";s:33:\"Setup the development environment\";s:2:\"by\";s:36:\"60000000-0000-0000-0000-000000000000\";s:12:\"aggregate_id\";s:36:\"00000000-0000-0000-0000-000000000003\";}','2014-11-12T19:07:59.000000+0100','Ora\\TaskManagement\\Task','00000000-0000-0000-0000-000000000003');
INSERT INTO `event_stream` (`eventId`, `version`, `eventName`, `payload`, `occurredOn`, `aggregate_type`, `aggregate_id`) VALUES
('6e943d08-7a9a-4a2a-a8b5-201bfeb57b92',3,'Ora\\TaskManagement\\streamChanged','a:3:{s:9:\"streamId\";s:36:\"00000000-1000-0000-0000-000000000000\";s:2:\"by\";s:36:\"60000000-0000-0000-0000-000000000000\";s:12:\"aggregate_id\";s:36:\"00000000-0000-0000-0000-000000000003\";}','2014-11-12T19:07:59.000000+0100','Ora\\TaskManagement\\Task','00000000-0000-0000-0000-000000000003');
INSERT INTO `event_stream` (`eventId`, `version`, `eventName`, `payload`, `occurredOn`, `aggregate_type`, `aggregate_id`) VALUES
('c033eb32-2ad4-49d5-b25c-3c0b600b7d10',4,'Ora\\TaskManagement\\MemberAdded','a:4:{s:6:\"userId\";s:36:\"60000000-0000-0000-0000-000000000000\";s:4:\"role\";s:5:\"owner\";s:2:\"by\";s:36:\"60000000-0000-0000-0000-000000000000\";s:12:\"aggregate_id\";s:36:\"00000000-0000-0000-0000-000000000003\";}','2014-11-12T19:07:59.000000+0100','Ora\\TaskManagement\\Task','00000000-0000-0000-0000-000000000003');

INSERT INTO `event_stream` (`eventId`, `version`, `eventName`, `payload`, `occurredOn`, `aggregate_type`, `aggregate_id`) VALUES
('6126d983-20ad-47f2-9636-085395aa3b8b',1,'Ora\\TaskManagement\\TaskCreated','a:3:{s:6:\"status\";i:20;s:2:\"by\";s:36:\"60000000-0000-0000-0000-000000000000\";s:12:\"aggregate_id\";s:36:\"00000000-0000-0000-0000-000000000004\";}','2014-11-12T19:07:59.000000+0100','Ora\\TaskManagement\\Task','00000000-0000-0000-0000-000000000004');
INSERT INTO `event_stream` (`eventId`, `version`, `eventName`, `payload`, `occurredOn`, `aggregate_type`, `aggregate_id`) VALUES
('334fa91f-62c9-4b34-827b-3e01bd7efe9c',2,'Ora\\TaskManagement\\TaskUpdated','a:3:{s:7:\"subject\";s:33:\"Setup the development environment\";s:2:\"by\";s:36:\"60000000-0000-0000-0000-000000000000\";s:12:\"aggregate_id\";s:36:\"00000000-0000-0000-0000-000000000004\";}','2014-11-12T19:07:59.000000+0100','Ora\\TaskManagement\\Task','00000000-0000-0000-0000-000000000004');
INSERT INTO `event_stream` (`eventId`, `version`, `eventName`, `payload`, `occurredOn`, `aggregate_type`, `aggregate_id`) VALUES
('6e943d08-7a9a-4a2a-a8b5-201bfeb57b11',3,'Ora\\TaskManagement\\streamChanged','a:3:{s:9:\"streamId\";s:36:\"00000000-1000-0000-0000-000000000000\";s:2:\"by\";s:36:\"60000000-0000-0000-0000-000000000000\";s:12:\"aggregate_id\";s:36:\"00000000-0000-0000-0000-000000000004\";}','2014-11-12T19:07:59.000000+0100','Ora\\TaskManagement\\Task','00000000-0000-0000-0000-000000000004');
INSERT INTO `event_stream` (`eventId`, `version`, `eventName`, `payload`, `occurredOn`, `aggregate_type`, `aggregate_id`) VALUES
('c033eb32-2ad4-49d5-b25c-3c0b600b7d12',4,'Ora\\TaskManagement\\MemberAdded','a:4:{s:6:\"userId\";s:36:\"60000000-0000-0000-0000-000000000000\";s:4:\"role\";s:5:\"owner\";s:2:\"by\";s:36:\"60000000-0000-0000-0000-000000000000\";s:12:\"aggregate_id\";s:36:\"00000000-0000-0000-0000-000000000004\";}','2014-11-12T19:07:59.000000+0100','Ora\\TaskManagement\\Task','00000000-0000-0000-0000-000000000004');

INSERT INTO `streams` (id, subject, createdAt, mostRecentEditAt) VALUES ('00000000-1000-0000-0000-000000000000', 'O.R.A.: Organization Resource Aggregator','2014-11-06 13:11:05','2014-11-06 13:11:05');
INSERT INTO `streams` (id, subject, createdAt, mostRecentEditAt) VALUES ('00000000-1100-0000-0000-000000000000', 'Open Goverance','2014-11-06 13:11:05','2014-11-06 13:11:05');
INSERT INTO `tasks` (id, stream_id, subject, status, createdAt, mostRecentEditAt, type, boardId, taskId) VALUES
('00000000-0000-0000-0000-000000000101','00000000-1000-0000-0000-000000000000','BATMAN',0,'2014-11-06 13:11:05','2014-11-06 13:11:05','kanbanizetask',3,111);
INSERT INTO `tasks` (id, stream_id, subject, status, createdAt, mostRecentEditAt, type, boardId, taskId) VALUES
('00000000-0000-0000-0000-000000000102','00000000-1000-0000-0000-000000000000','JOKER',0,'2014-11-06 13:11:45','2014-11-06 13:11:45','kanbanizetask',3,112);
INSERT INTO `tasks` (id, stream_id, subject, status, createdAt, mostRecentEditAt, type, boardId, taskId) VALUES
('00000000-0000-0000-0000-000000000103','00000000-1000-0000-0000-000000000000','POISONIVY',0,'2014-11-06 13:12:14','2014-11-06 13:12:14','kanbanizetask',3,113);
INSERT INTO `tasks` (id, stream_id, subject, status, createdAt, mostRecentEditAt, type, boardId, taskId) VALUES
('00000000-0000-0000-0000-000000000104','00000000-1000-0000-0000-000000000000','wrongbatman',0,'2014-11-06 13:12:50','2014-11-06 13:12:50','kanbanizetask',4,1);
INSERT INTO `tasks` (id, stream_id, subject, status, createdAt, mostRecentEditAt, type, boardId, taskId) VALUES
('00000000-0000-0000-0000-000000000105','00000000-1000-0000-0000-000000000000','wrongbatmanagain',0,'2014-11-06 13:13:15','2014-11-06 13:13:15','kanbanizetask',3,69);
INSERT INTO `tasks` (id, stream_id, subject, status, createdAt, mostRecentEditAt, type, boardId, taskId) VALUES
('00000000-0000-0000-0000-000000000106','00000000-1000-0000-0000-000000000000','acceptedTask',40,'2014-11-06 14:32:44','2014-11-06 14:32:44','kanbanizetask',3,114);
INSERT INTO `tasks` (id, stream_id, subject, status, createdAt, mostRecentEditAt, type, boardId, taskId) VALUES
('00000000-0000-0000-0000-000000000107','00000000-1000-0000-0000-000000000000','completedTask',30,'2014-11-06 14:43:28','2014-11-06 14:43:28','kanbanizetask',3,115);
INSERT INTO `tasks` (id, stream_id, subject, status, createdAt, mostRecentEditAt, type, boardId, taskId) VALUES
('00000000-0000-0000-0000-000000000108','00000000-1000-0000-0000-000000000000','ongoingTask',20,'2014-11-06 14:51:12','2014-11-06 14:51:12','kanbanizetask',3,116);
INSERT INTO `tasks` (id, stream_id, subject, status, createdAt, mostRecentEditAt, type, boardId, taskId) VALUES
('00000000-0000-0000-0000-000000000109','00000000-1000-0000-0000-000000000000','completedTask',30,'2014-11-06 15:03:02','2014-11-06 15:03:02','kanbanizetask',3,117);
INSERT INTO `tasks` (id, stream_id, subject, status, createdAt, mostRecentEditAt, type, boardId, taskId) VALUES
('00000000-0000-0000-0000-000000000110','00000000-1000-0000-0000-000000000000','completedTask',30,'2014-11-06 15:39:13','2014-11-06 15:39:13','kanbanizetask',3,119);
INSERT INTO `tasks` (id, stream_id, subject, status, createdAt, mostRecentEditAt, type, boardId, taskId) VALUES
('00000000-0000-0000-0000-000000000111','00000000-1000-0000-0000-000000000000','completedTask',40,'2014-11-06 15:48:17','2014-11-06 15:48:17','kanbanizetask',3,120);
INSERT INTO `tasks` (id, stream_id, subject, status, createdAt, mostRecentEditAt, type, boardId, taskId) VALUES
('00000000-0000-0000-0000-000000000112','00000000-1000-0000-0000-000000000000','ongoingTask',20,'2014-11-06 15:56:56','2014-11-06 15:56:56','kanbanizetask',3,118);

INSERT INTO `estimation` (id, value, createdAt, mostRecentEditAt, mostRecentEditBy_id, createdBy_id) VALUES
('60000000-0000-0000-1111-000000000000','1500,00', '2014-11-07 11:37:58', '2014-11-07 11:37:58', '20000000-0000-0000-0000-000000000000', '20000000-0000-0000-0000-000000000000');

INSERT INTO `tasks_members` (task_id, member_id, estimation_id, role) VALUES
('00000000-0000-0000-0000-000000000112','20000000-0000-0000-0000-000000000000', '60000000-0000-0000-1111-000000000000', 'OWNER');


INSERT INTO `estimation` (id, value, createdAt, mostRecentEditAt, mostRecentEditBy_id, createdBy_id) VALUES
('04a1c762-44dc-404c-ac33-3c0723a39c8e','1500,00', '2014-11-07 11:37:58', '2014-11-07 11:37:58', '90000000-0000-0000-0000-000000000000', '90000000-0000-0000-0000-000000000000');
INSERT INTO `estimation` (id, value, createdAt, mostRecentEditAt, mostRecentEditBy_id, createdBy_id) VALUES
('04a1c762-44dc-404c-ac33-3c0723a39c8f','1500,00', '2014-11-07 11:37:58', '2014-11-07 11:37:58', '80000000-0000-0000-0000-000000000000', '80000000-0000-0000-0000-000000000000‭‭');
INSERT INTO `estimation` (id, value, createdAt, mostRecentEditAt, mostRecentEditBy_id, createdBy_id) VALUES
('04a1c762-44dc-404c-ac33-3c0723a39c8h','-1', '2014-11-07 11:37:58', '2014-11-07 11:37:58', '80000000-0000-0000-0000-000000000000', '80000000-0000-0000-0000-000000000000‭‭');



INSERT INTO `tasks_members`(task_id, member_id,estimation_id,role) VALUES('00000000-0000-0000-0000-000000000103','80000000-0000-0000-0000-000000000000','04a1c762-44dc-404c-ac33-3c0723a39c8f','OWNER');
INSERT INTO `tasks_members`(task_id, member_id,estimation_id,role) VALUES('00000000-0000-0000-0000-000000000103','90000000-0000-0000-0000-000000000000','04a1c762-44dc-404c-ac33-3c0723a39c8e','MEMBER');
INSERT INTO `tasks_members`(task_id, member_id,estimation_id,role) VALUES('00000000-0000-0000-0000-000000000104','80000000-0000-0000-0000-000000000000','04a1c762-44dc-404c-ac33-3c0723a39c8h','OWNER');
INSERT INTO `tasks_members`(task_id, member_id,estimation_id,role) VALUES('00000000-0000-0000-0000-000000000105','80000000-0000-0000-0000-000000000000',NULL,'OWNER');

INSERT INTO `event_stream` VALUES ('26a4fcb5-e726-4f42-846b-42b018a3d692',4,'Ora\\TaskManagement\\MemberAdded','a:4:{s:6:\"userId\";s:36:\"60000000-0000-0000-0000-000000000000\";s:4:\"role\";s:5:\"owner\";s:2:\"by\";s:36:\"60000000-0000-0000-0000-000000000000\";s:12:\"aggregate_id\";s:36:\"3f9c3c4a-7e48-4765-9400-95f64baba7da\";}','2014-12-19T12:15:19.000000+0100','3f9c3c4a-7e48-4765-9400-95f64baba7da','Ora\\TaskManagement\\Task'),('2a8bb507-b51e-4b82-8539-cd1ba429b454',1,'Ora\\TaskManagement\\TaskCreated','a:3:{s:6:\"status\";i:20;s:2:\"by\";s:36:\"60000000-0000-0000-0000-000000000000\";s:12:\"aggregate_id\";s:36:\"29a83bd7-a40c-4300-b06e-fb20ca05d754\";}','2014-12-19T12:15:12.000000+0100','29a83bd7-a40c-4300-b06e-fb20ca05d754','Ora\\TaskManagement\\Task'),('381a6674-c352-454b-bee2-d2e078eeb81c',2,'Ora\\TaskManagement\\TaskUpdated','a:3:{s:7:\"subject\";s:11:\"estimation1\";s:2:\"by\";s:36:\"60000000-0000-0000-0000-000000000000\";s:12:\"aggregate_id\";s:36:\"e720064f-670e-41c0-900e-a6f1315622c0\";}','2014-12-19T12:14:40.000000+0100','e720064f-670e-41c0-900e-a6f1315622c0','Ora\\TaskManagement\\Task'),('6955369e-3b7a-46a8-82c7-c249a2bcc6f6',3,'Ora\\TaskManagement\\streamChanged','a:3:{s:9:\"streamId\";s:36:\"00000000-1000-0000-0000-000000000000\";s:2:\"by\";s:36:\"60000000-0000-0000-0000-000000000000\";s:12:\"aggregate_id\";s:36:\"29a83bd7-a40c-4300-b06e-fb20ca05d754\";}','2014-12-19T12:15:12.000000+0100','29a83bd7-a40c-4300-b06e-fb20ca05d754','Ora\\TaskManagement\\Task'),('861bae01-55f9-43dd-b611-ea6935763e78',1,'Ora\\TaskManagement\\TaskCreated','a:3:{s:6:\"status\";i:20;s:2:\"by\";s:36:\"60000000-0000-0000-0000-000000000000\";s:12:\"aggregate_id\";s:36:\"3f9c3c4a-7e48-4765-9400-95f64baba7da\";}','2014-12-19T12:15:19.000000+0100','3f9c3c4a-7e48-4765-9400-95f64baba7da','Ora\\TaskManagement\\Task'),('b2bac373-c007-4b59-a3eb-6683d38d387e',1,'Ora\\TaskManagement\\TaskCreated','a:3:{s:6:\"status\";i:20;s:2:\"by\";s:36:\"60000000-0000-0000-0000-000000000000\";s:12:\"aggregate_id\";s:36:\"e720064f-670e-41c0-900e-a6f1315622c0\";}','2014-12-19T12:14:40.000000+0100','e720064f-670e-41c0-900e-a6f1315622c0','Ora\\TaskManagement\\Task'),('b5f73742-5798-403c-913b-5f2f78b955c7',3,'Ora\\TaskManagement\\streamChanged','a:3:{s:9:\"streamId\";s:36:\"00000000-1000-0000-0000-000000000000\";s:2:\"by\";s:36:\"60000000-0000-0000-0000-000000000000\";s:12:\"aggregate_id\";s:36:\"e720064f-670e-41c0-900e-a6f1315622c0\";}','2014-12-19T12:14:40.000000+0100','e720064f-670e-41c0-900e-a6f1315622c0','Ora\\TaskManagement\\Task'),('b7f33648-5df0-40da-8b3b-2af71997eac5',2,'Ora\\TaskManagement\\TaskUpdated','a:3:{s:7:\"subject\";s:11:\"estimation3\";s:2:\"by\";s:36:\"60000000-0000-0000-0000-000000000000\";s:12:\"aggregate_id\";s:36:\"3f9c3c4a-7e48-4765-9400-95f64baba7da\";}','2014-12-19T12:15:19.000000+0100','3f9c3c4a-7e48-4765-9400-95f64baba7da','Ora\\TaskManagement\\Task'),('c0a20e5f-46c5-4ac2-999b-587341a80740',4,'Ora\\TaskManagement\\MemberAdded','a:4:{s:6:\"userId\";s:36:\"60000000-0000-0000-0000-000000000000\";s:4:\"role\";s:5:\"owner\";s:2:\"by\";s:36:\"60000000-0000-0000-0000-000000000000\";s:12:\"aggregate_id\";s:36:\"e720064f-670e-41c0-900e-a6f1315622c0\";}','2014-12-19T12:14:40.000000+0100','e720064f-670e-41c0-900e-a6f1315622c0','Ora\\TaskManagement\\Task'),('e3c370cd-3b25-4fd2-9b76-364bb00b230e',4,'Ora\\TaskManagement\\MemberAdded','a:4:{s:6:\"userId\";s:36:\"60000000-0000-0000-0000-000000000000\";s:4:\"role\";s:5:\"owner\";s:2:\"by\";s:36:\"60000000-0000-0000-0000-000000000000\";s:12:\"aggregate_id\";s:36:\"29a83bd7-a40c-4300-b06e-fb20ca05d754\";}','2014-12-19T12:15:12.000000+0100','29a83bd7-a40c-4300-b06e-fb20ca05d754','Ora\\TaskManagement\\Task'),('e83ac1b0-a322-4dc1-9ec9-e42ae80b74c7',3,'Ora\\TaskManagement\\streamChanged','a:3:{s:9:\"streamId\";s:36:\"00000000-1000-0000-0000-000000000000\";s:2:\"by\";s:36:\"60000000-0000-0000-0000-000000000000\";s:12:\"aggregate_id\";s:36:\"3f9c3c4a-7e48-4765-9400-95f64baba7da\";}','2014-12-19T12:15:19.000000+0100','3f9c3c4a-7e48-4765-9400-95f64baba7da','Ora\\TaskManagement\\Task'),('f2d7d203-3378-4a8b-a659-2b175383b532',2,'Ora\\TaskManagement\\TaskUpdated','a:3:{s:7:\"subject\";s:11:\"estimation2\";s:2:\"by\";s:36:\"60000000-0000-0000-0000-000000000000\";s:12:\"aggregate_id\";s:36:\"29a83bd7-a40c-4300-b06e-fb20ca05d754\";}','2014-12-19T12:15:12.000000+0100','29a83bd7-a40c-4300-b06e-fb20ca05d754','Ora\\TaskManagement\\Task');

INSERT INTO `tasks` VALUES ('e720064f-670e-41c0-900e-a6f1315622c0','00000000-1000-0000-0000-000000000000','estimation1',20,'2014-12-19 12:14:40','2014-12-19 12:14:40','60000000-0000-0000-0000-000000000000','60000000-0000-0000-0000-000000000000','task',NULL,NULL),('29a83bd7-a40c-4300-b06e-fb20ca05d754','00000000-1000-0000-0000-000000000000','estimation2',20,'2014-12-19 12:15:12','2014-12-19 12:15:12','60000000-0000-0000-0000-000000000000','60000000-0000-0000-0000-000000000000','task',NULL,NULL),('3f9c3c4a-7e48-4765-9400-95f64baba7da','00000000-1000-0000-0000-000000000000','estimation3',30,'2014-12-19 12:15:19','2014-12-19 12:15:19','60000000-0000-0000-0000-000000000000','60000000-0000-0000-0000-000000000000','task',NULL,NULL);

INSERT INTO `tasks_members` VALUES ('29a83bd7-a40c-4300-b06e-fb20ca05d754','60000000-0000-0000-0000-000000000000',NULL,'owner'),('3f9c3c4a-7e48-4765-9400-95f64baba7da','60000000-0000-0000-0000-000000000000',NULL,'owner'),('e720064f-670e-41c0-900e-a6f1315622c0','60000000-0000-0000-0000-000000000000',NULL,'owner');

set FOREIGN_KEY_CHECKS = 1;

