delete from tasks;
delete from projects;
truncate event_stream;
delete from users;

INSERT INTO users (id, status, createdAt, firstname, lastname, email) VALUES ('60000000-0000-0000-0000-000000000000', 1, "2014-10-09 11:33:45", "Mark", "Rogers", "mark.rogers@ora.local");
INSERT INTO users (id, status, createdAt, firstname, lastname, email) VALUES ('70000000-0000-0000-0000-000000000000', 1, "2014-10-09 11:33:45", "Phil", "Toledo", "phil.toledo@ora.local");
INSERT INTO users (id, status, createdAt, firstname, lastname, email) VALUES ('20000000-0000-0000-0000-000000000000', 1, "2014-10-09 11:33:45", "Paul", "Smith", "paul.smith@ora.local");

INSERT INTO `event_stream` (`eventId`, `version`, `eventName`, `payload`, `occurredOn`, `aggregate_type`, `aggregate_id`) VALUES
('6126d983-20ad-47f2-9636-085395aa3b4b',1,'Ora\\TaskManagement\\TaskCreated','a:3:{s:6:\"status\";i:20;s:2:\"by\";s:36:\"60000000-0000-0000-0000-000000000000\";s:12:\"aggregate_id\";s:36:\"00000000-0000-0000-0000-000000000000\";}','2014-11-12T19:07:59.000000+0100','Ora\\TaskManagement\\Task','00000000-0000-0000-0000-000000000000');
INSERT INTO `event_stream` (`eventId`, `version`, `eventName`, `payload`, `occurredOn`, `aggregate_type`, `aggregate_id`) VALUES
('334fa91f-62c9-4b34-827b-3e01bd7efe5c',2,'Ora\\TaskManagement\\TaskUpdated','a:3:{s:7:\"subject\";s:33:\"Setup the development environment\";s:2:\"by\";s:36:\"60000000-0000-0000-0000-000000000000\";s:12:\"aggregate_id\";s:36:\"00000000-0000-0000-0000-000000000000\";}','2014-11-12T19:07:59.000000+0100','Ora\\TaskManagement\\Task','00000000-0000-0000-0000-000000000000');
INSERT INTO `event_stream` (`eventId`, `version`, `eventName`, `payload`, `occurredOn`, `aggregate_type`, `aggregate_id`) VALUES
('6e943d08-7a9a-4a2a-a8b5-201bfeb57b62',3,'Ora\\TaskManagement\\ProjectChanged','a:3:{s:9:\"projectId\";s:36:\"00000000-1000-0000-0000-000000000000\";s:2:\"by\";s:36:\"60000000-0000-0000-0000-000000000000\";s:12:\"aggregate_id\";s:36:\"00000000-0000-0000-0000-000000000000\";}','2014-11-12T19:07:59.000000+0100','Ora\\TaskManagement\\Task','00000000-0000-0000-0000-000000000000');
INSERT INTO `event_stream` (`eventId`, `version`, `eventName`, `payload`, `occurredOn`, `aggregate_type`, `aggregate_id`) VALUES
('c033eb32-2ad4-49d5-b25c-3c0b600b7dec',4,'Ora\\TaskManagement\\MemberAdded','a:4:{s:6:\"userId\";s:36:\"60000000-0000-0000-0000-000000000000\";s:4:\"role\";s:5:\"owner\";s:2:\"by\";s:36:\"60000000-0000-0000-0000-000000000000\";s:12:\"aggregate_id\";s:36:\"00000000-0000-0000-0000-000000000000\";}','2014-11-12T19:07:59.000000+0100','Ora\\TaskManagement\\Task','00000000-0000-0000-0000-000000000000');

INSERT INTO `event_stream` (`eventId`, `version`, `eventName`, `payload`, `occurredOn`, `aggregate_type`, `aggregate_id`) VALUES
('6126d983-20ad-47f2-9636-085395aa3b5b',1,'Ora\\TaskManagement\\TaskCreated','a:3:{s:6:\"status\";i:20;s:2:\"by\";s:36:\"60000000-0000-0000-0000-000000000000\";s:12:\"aggregate_id\";s:36:\"00000000-0000-0000-0000-000000000001\";}','2014-11-12T19:07:59.000000+0100','Ora\\TaskManagement\\Task','00000000-0000-0000-0000-000000000001');
INSERT INTO `event_stream` (`eventId`, `version`, `eventName`, `payload`, `occurredOn`, `aggregate_type`, `aggregate_id`) VALUES
('334fa91f-62c9-4b34-827b-3e01bd7efe6c',2,'Ora\\TaskManagement\\TaskUpdated','a:3:{s:7:\"subject\";s:33:\"Setup the development environment\";s:2:\"by\";s:36:\"60000000-0000-0000-0000-000000000000\";s:12:\"aggregate_id\";s:36:\"00000000-0000-0000-0000-000000000001\";}','2014-11-12T19:07:59.000000+0100','Ora\\TaskManagement\\Task','00000000-0000-0000-0000-000000000001');
INSERT INTO `event_stream` (`eventId`, `version`, `eventName`, `payload`, `occurredOn`, `aggregate_type`, `aggregate_id`) VALUES
('6e943d08-7a9a-4a2a-a8b5-201bfeb57b72',3,'Ora\\TaskManagement\\ProjectChanged','a:3:{s:9:\"projectId\";s:36:\"00000000-1000-0000-0000-000000000000\";s:2:\"by\";s:36:\"60000000-0000-0000-0000-000000000000\";s:12:\"aggregate_id\";s:36:\"00000000-0000-0000-0000-000000000001\";}','2014-11-12T19:07:59.000000+0100','Ora\\TaskManagement\\Task','00000000-0000-0000-0000-000000000001');
INSERT INTO `event_stream` (`eventId`, `version`, `eventName`, `payload`, `occurredOn`, `aggregate_type`, `aggregate_id`) VALUES
('c033eb32-2ad4-49d5-b25c-3c0b600b7dfc',4,'Ora\\TaskManagement\\MemberAdded','a:4:{s:6:\"userId\";s:36:\"60000000-0000-0000-0000-000000000000\";s:4:\"role\";s:5:\"owner\";s:2:\"by\";s:36:\"60000000-0000-0000-0000-000000000000\";s:12:\"aggregate_id\";s:36:\"00000000-0000-0000-0000-000000000001\";}','2014-11-12T19:07:59.000000+0100','Ora\\TaskManagement\\Task','00000000-0000-0000-0000-000000000001');
INSERT INTO `event_stream` (eventId, version, eventName, payload, occurredOn, aggregate_type, aggregate_id) VALUES 
('fbdfdd17-61ef-4f80-bcd4-7e6eb6990713',2,'Ora\\TaskManagement\\TaskCompleted','a:2:{s:12:\"aggregate_id\";s:36:\"00000000-0000-0000-0000-000000000001\";s:2:\"by\";s:36:\"60000000-0000-0000-0000-000000000000\";}','2014-10-31T10:44:30.000000+0100','Ora\\TaskManagement\\Task','00000000-0000-0000-0000-000000000001');

INSERT INTO `event_stream` (`eventId`, `version`, `eventName`, `payload`, `occurredOn`, `aggregate_type`, `aggregate_id`) VALUES
('6126d983-20ad-47f2-9636-085395aa3b6b',1,'Ora\\TaskManagement\\TaskCreated','a:3:{s:6:\"status\";i:20;s:2:\"by\";s:36:\"60000000-0000-0000-0000-000000000000\";s:12:\"aggregate_id\";s:36:\"00000000-0000-0000-0000-000000000002\";}','2014-11-12T19:07:59.000000+0100','Ora\\TaskManagement\\Task','00000000-0000-0000-0000-000000000002');
INSERT INTO `event_stream` (`eventId`, `version`, `eventName`, `payload`, `occurredOn`, `aggregate_type`, `aggregate_id`) VALUES
('334fa91f-62c9-4b34-827b-3e01bd7efe7c',2,'Ora\\TaskManagement\\TaskUpdated','a:3:{s:7:\"subject\";s:33:\"Setup the development environment\";s:2:\"by\";s:36:\"60000000-0000-0000-0000-000000000000\";s:12:\"aggregate_id\";s:36:\"00000000-0000-0000-0000-000000000002\";}','2014-11-12T19:07:59.000000+0100','Ora\\TaskManagement\\Task','00000000-0000-0000-0000-000000000002');
INSERT INTO `event_stream` (`eventId`, `version`, `eventName`, `payload`, `occurredOn`, `aggregate_type`, `aggregate_id`) VALUES
('6e943d08-7a9a-4a2a-a8b5-201bfeb57b82',3,'Ora\\TaskManagement\\ProjectChanged','a:3:{s:9:\"projectId\";s:36:\"00000000-1000-0000-0000-000000000000\";s:2:\"by\";s:36:\"60000000-0000-0000-0000-000000000000\";s:12:\"aggregate_id\";s:36:\"00000000-0000-0000-0000-000000000002\";}','2014-11-12T19:07:59.000000+0100','Ora\\TaskManagement\\Task','00000000-0000-0000-0000-000000000002');
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
('6e943d08-7a9a-4a2a-a8b5-201bfeb57b92',3,'Ora\\TaskManagement\\ProjectChanged','a:3:{s:9:\"projectId\";s:36:\"00000000-1000-0000-0000-000000000000\";s:2:\"by\";s:36:\"60000000-0000-0000-0000-000000000000\";s:12:\"aggregate_id\";s:36:\"00000000-0000-0000-0000-000000000003\";}','2014-11-12T19:07:59.000000+0100','Ora\\TaskManagement\\Task','00000000-0000-0000-0000-000000000003');
INSERT INTO `event_stream` (`eventId`, `version`, `eventName`, `payload`, `occurredOn`, `aggregate_type`, `aggregate_id`) VALUES
('c033eb32-2ad4-49d5-b25c-3c0b600b7d10',4,'Ora\\TaskManagement\\MemberAdded','a:4:{s:6:\"userId\";s:36:\"60000000-0000-0000-0000-000000000000\";s:4:\"role\";s:5:\"owner\";s:2:\"by\";s:36:\"60000000-0000-0000-0000-000000000000\";s:12:\"aggregate_id\";s:36:\"00000000-0000-0000-0000-000000000003\";}','2014-11-12T19:07:59.000000+0100','Ora\\TaskManagement\\Task','00000000-0000-0000-0000-000000000003');

INSERT INTO `event_stream` (`eventId`, `version`, `eventName`, `payload`, `occurredOn`, `aggregate_type`, `aggregate_id`) VALUES
('6126d983-20ad-47f2-9636-085395aa3b8b',1,'Ora\\TaskManagement\\TaskCreated','a:3:{s:6:\"status\";i:20;s:2:\"by\";s:36:\"60000000-0000-0000-0000-000000000000\";s:12:\"aggregate_id\";s:36:\"00000000-0000-0000-0000-000000000004\";}','2014-11-12T19:07:59.000000+0100','Ora\\TaskManagement\\Task','00000000-0000-0000-0000-000000000004');
INSERT INTO `event_stream` (`eventId`, `version`, `eventName`, `payload`, `occurredOn`, `aggregate_type`, `aggregate_id`) VALUES
('334fa91f-62c9-4b34-827b-3e01bd7efe9c',2,'Ora\\TaskManagement\\TaskUpdated','a:3:{s:7:\"subject\";s:33:\"Setup the development environment\";s:2:\"by\";s:36:\"60000000-0000-0000-0000-000000000000\";s:12:\"aggregate_id\";s:36:\"00000000-0000-0000-0000-000000000004\";}','2014-11-12T19:07:59.000000+0100','Ora\\TaskManagement\\Task','00000000-0000-0000-0000-000000000004');
INSERT INTO `event_stream` (`eventId`, `version`, `eventName`, `payload`, `occurredOn`, `aggregate_type`, `aggregate_id`) VALUES
('6e943d08-7a9a-4a2a-a8b5-201bfeb57b11',3,'Ora\\TaskManagement\\ProjectChanged','a:3:{s:9:\"projectId\";s:36:\"00000000-1000-0000-0000-000000000000\";s:2:\"by\";s:36:\"60000000-0000-0000-0000-000000000000\";s:12:\"aggregate_id\";s:36:\"00000000-0000-0000-0000-000000000004\";}','2014-11-12T19:07:59.000000+0100','Ora\\TaskManagement\\Task','00000000-0000-0000-0000-000000000004');
INSERT INTO `event_stream` (`eventId`, `version`, `eventName`, `payload`, `occurredOn`, `aggregate_type`, `aggregate_id`) VALUES
('c033eb32-2ad4-49d5-b25c-3c0b600b7d12',4,'Ora\\TaskManagement\\MemberAdded','a:4:{s:6:\"userId\";s:36:\"60000000-0000-0000-0000-000000000000\";s:4:\"role\";s:5:\"owner\";s:2:\"by\";s:36:\"60000000-0000-0000-0000-000000000000\";s:12:\"aggregate_id\";s:36:\"00000000-0000-0000-0000-000000000004\";}','2014-11-12T19:07:59.000000+0100','Ora\\TaskManagement\\Task','00000000-0000-0000-0000-000000000004');

INSERT INTO `projects` (id, subject, createdAt, mostRecentEditAt) VALUES ('00000000-1000-0000-0000-000000000000', 'O.R.A.: Organization Resource Aggregator','2014-11-06 13:11:05','2014-11-06 13:11:05');
INSERT INTO `tasks` (id, project_id, subject, status, createdAt, mostRecentEditAt, type, boardId, taskId) VALUES
('00000000-0000-0000-0000-000000000101','00000000-1000-0000-0000-000000000000','BATMAN',0,'2014-11-06 13:11:05','2014-11-06 13:11:05','kanbanizetask',3,111);
INSERT INTO `tasks` (id, project_id, subject, status, createdAt, mostRecentEditAt, type, boardId, taskId) VALUES
('00000000-0000-0000-0000-000000000102','00000000-1000-0000-0000-000000000000','JOKER',0,'2014-11-06 13:11:45','2014-11-06 13:11:45','kanbanizetask',3,112);
INSERT INTO `tasks` (id, project_id, subject, status, createdAt, mostRecentEditAt, type, boardId, taskId) VALUES
('00000000-0000-0000-0000-000000000103','00000000-1000-0000-0000-000000000000','POISONIVY',0,'2014-11-06 13:12:14','2014-11-06 13:12:14','kanbanizetask',3,113);
INSERT INTO `tasks` (id, project_id, subject, status, createdAt, mostRecentEditAt, type, boardId, taskId) VALUES
('00000000-0000-0000-0000-000000000104','00000000-1000-0000-0000-000000000000','wrongbatman',0,'2014-11-06 13:12:50','2014-11-06 13:12:50','kanbanizetask',4,1);
INSERT INTO `tasks` (id, project_id, subject, status, createdAt, mostRecentEditAt, type, boardId, taskId) VALUES
('00000000-0000-0000-0000-000000000105','00000000-1000-0000-0000-000000000000','wrongbatmanagain',0,'2014-11-06 13:13:15','2014-11-06 13:13:15','kanbanizetask',3,69);
INSERT INTO `tasks` (id, project_id, subject, status, createdAt, mostRecentEditAt, type, boardId, taskId) VALUES
('00000000-0000-0000-0000-000000000106','00000000-1000-0000-0000-000000000000','acceptedTask',40,'2014-11-06 14:32:44','2014-11-06 14:32:44','kanbanizetask',3,114);
INSERT INTO `tasks` (id, project_id, subject, status, createdAt, mostRecentEditAt, type, boardId, taskId) VALUES
('00000000-0000-0000-0000-000000000107','00000000-1000-0000-0000-000000000000','completedTask',30,'2014-11-06 14:43:28','2014-11-06 14:43:28','kanbanizetask',3,115);
INSERT INTO `tasks` (id, project_id, subject, status, createdAt, mostRecentEditAt, type, boardId, taskId) VALUES
('00000000-0000-0000-0000-000000000108','00000000-1000-0000-0000-000000000000','ongoingTask',20,'2014-11-06 14:51:12','2014-11-06 14:51:12','kanbanizetask',3,116);
INSERT INTO `tasks` (id, project_id, subject, status, createdAt, mostRecentEditAt, type, boardId, taskId) VALUES
('00000000-0000-0000-0000-000000000109','00000000-1000-0000-0000-000000000000','completedTask',30,'2014-11-06 15:03:02','2014-11-06 15:03:02','kanbanizetask',3,117);
INSERT INTO `tasks` (id, project_id, subject, status, createdAt, mostRecentEditAt, type, boardId, taskId) VALUES
('00000000-0000-0000-0000-000000000110','00000000-1000-0000-0000-000000000000','completedTask',30,'2014-11-06 15:39:13','2014-11-06 15:39:13','kanbanizetask',3,119);
INSERT INTO `tasks` (id, project_id, subject, status, createdAt, mostRecentEditAt, type, boardId, taskId) VALUES
('00000000-0000-0000-0000-000000000111','00000000-1000-0000-0000-000000000000','completedTask',40,'2014-11-06 15:48:17','2014-11-06 15:48:17','kanbanizetask',3,120);
INSERT INTO `tasks` (id, project_id, subject, status, createdAt, mostRecentEditAt, type, boardId, taskId) VALUES
('00000000-0000-0000-0000-000000000112','00000000-1000-0000-0000-000000000000','ongoingTask',20,'2014-11-06 15:56:56','2014-11-06 15:56:56','kanbanizetask',3,118);