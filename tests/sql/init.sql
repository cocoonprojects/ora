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

delete from task_members;
delete from tasks;
delete from streams;
delete from accounts;
delete from organizations;
delete from event_stream;
delete from users;

INSERT INTO users (id, status, createdAt, firstname, lastname, email) VALUES ('60000000-0000-0000-0000-000000000000', 1, '2014-10-09 11:33:45', 'Mark', 'Rogers', 'mark.rogers@ora.local');
INSERT INTO users (id, status, createdAt, firstname, lastname, email) VALUES ('70000000-0000-0000-0000-000000000000', 1, '2014-10-09 11:33:45', 'Phil', 'Toledo', 'phil.toledo@ora.local');
INSERT INTO users (id, status, createdAt, firstname, lastname, email) VALUES ('20000000-0000-0000-0000-000000000000', 1, '2014-10-09 11:33:45', 'Paul', 'Smith', 'paul.smith@ora.local');
INSERT INTO users (id, status, createdAt, firstname, lastname, email) VALUES ('80000000-0000-0000-0000-000000000000', 1, '2014-10-09 11:33:45', 'Bruce', 'Wayne', 'bruce.wayne@ora.local');
INSERT INTO users (id, status, createdAt, firstname, lastname, email) VALUES ('90000000-0000-0000-0000-000000000000', 1, '2014-10-09 11:33:45', 'Peter', 'Parker', 'spidey.web@dailybugle.local');

INSERT INTO `organizations` (id, name, createdAt, mostRecentEditAt, createdBy_id, mostRecentEditBy_id) VALUES
('00000000-0000-0000-1000-000000000000', 'O.R.A. Team','2014-11-06 13:11:05','2014-11-06 13:11:05', '60000000-0000-0000-0000-000000000000', '60000000-0000-0000-0000-000000000000');
INSERT INTO organization_members(member_id, organization_id, role, createdAt, mostRecentEditAt, createdBy_id, mostRecentEditBy_id) VALUES
#('4763751d-9039-44b9-a5ec-b47411b3a805', '00000000-0000-0000-1000-000000000000', 'admin', '2014-10-09 11:33:45', '2014-10-09 11:33:45', '4763751d-9039-44b9-a5ec-b47411b3a805', '4763751d-9039-44b9-a5ec-b47411b3a805');
('60000000-0000-0000-0000-000000000000', '00000000-0000-0000-1000-000000000000', 'admin', '2014-10-09 11:33:45', '2014-10-09 11:33:45', '60000000-0000-0000-0000-000000000000', '60000000-0000-0000-0000-000000000000');


insert into accounts(id, organization_id, createdAt, mostRecentEditAt, balance_value, balance_date, createdBy_id, mostRecentEditBy_id, type) values ('dcde992b-5aa9-4447-98ae-c8115906dcb7', '00000000-0000-0000-1000-000000000000', '2014-12-09 15:25:18', '2014-12-09 15:25:18', 0, '2014-12-09 15:25:18', '60000000-0000-0000-0000-000000000000', '60000000-0000-0000-0000-000000000000', 'organizationaccount');

INSERT INTO streams (id, subject, createdAt, mostRecentEditAt, organization_id, createdBy_id, mostRecentEditBy_id) VALUES ('00000000-1000-0000-0000-000000000000', 'O.R.A.: Organization Resource Aggregator','2014-11-06 13:11:05','2014-11-06 13:11:05', '00000000-0000-0000-1000-000000000000', '60000000-0000-0000-0000-000000000000', '60000000-0000-0000-0000-000000000000');
INSERT INTO streams (id, subject, createdAt, mostRecentEditAt, organization_id, createdBy_id, mostRecentEditBy_id) VALUES ('00000000-1100-0000-0000-000000000000', 'Open Goverance','2014-11-06 13:11:05','2014-11-06 13:11:05', '00000000-0000-0000-1000-000000000000', '60000000-0000-0000-0000-000000000000', '60000000-0000-0000-0000-000000000000');

# task 00000000-0000-0000-0000-000000000000, ongoing, Mark Rogers (owner), Paul Smith (member)
INSERT INTO event_stream (eventId, version, eventName, payload, occurredOn, aggregate_type, aggregate_id) VALUES
('6126d983-20ad-47f2-9636-085395aa3b4b',1,'Ora\\TaskManagement\\TaskCreated','a:3:{s:6:\"status\";i:20;s:2:\"by\";s:36:\"60000000-0000-0000-0000-000000000000\";s:12:\"aggregate_id\";s:36:\"00000000-0000-0000-0000-000000000000\";}','2014-11-12T19:07:59.000000+0100','Ora\\TaskManagement\\Task','00000000-0000-0000-0000-000000000000'),
('334fa91f-62c9-4b34-827b-3e01bd7efe5c',2,'Ora\\TaskManagement\\TaskUpdated','a:3:{s:7:\"subject\";s:29:\"Development environment setup\";s:2:\"by\";s:36:\"60000000-0000-0000-0000-000000000000\";s:12:\"aggregate_id\";s:36:\"00000000-0000-0000-0000-000000000000\";}','2014-11-12T19:07:59.000000+0100','Ora\\TaskManagement\\Task','00000000-0000-0000-0000-000000000000'),
('6e943d08-7a9a-4a2a-a8b5-201bfeb57b62',3,'Ora\\TaskManagement\\StreamChanged','a:3:{s:8:\"streamId\";s:36:\"00000000-1000-0000-0000-000000000000\";s:2:\"by\";s:36:\"60000000-0000-0000-0000-000000000000\";s:12:\"aggregate_id\";s:36:\"00000000-0000-0000-0000-000000000000\";}','2014-11-12T19:07:59.000000+0100','Ora\\TaskManagement\\Task','00000000-0000-0000-0000-000000000000'),
('c033eb32-2ad4-49d5-b25c-3c0b600b7dec',4,'Ora\\TaskManagement\\MemberAdded','a:4:{s:6:\"userId\";s:36:\"60000000-0000-0000-0000-000000000000\";s:4:\"role\";s:5:\"owner\";s:2:\"by\";s:36:\"60000000-0000-0000-0000-000000000000\";s:12:\"aggregate_id\";s:36:\"00000000-0000-0000-0000-000000000000\";}','2014-11-12T19:07:59.000000+0100','Ora\\TaskManagement\\Task','00000000-0000-0000-0000-000000000000'),
('c133eb32-2ad4-49d5-b25c-3c0b600b7dec',5,'Ora\\TaskManagement\\MemberAdded','a:4:{s:6:\"userId\";s:36:\"20000000-0000-0000-0000-000000000000\";s:4:\"role\";s:6:\"member\";s:2:\"by\";s:36:\"20000000-0000-0000-0000-000000000000\";s:12:\"aggregate_id\";s:36:\"00000000-0000-0000-0000-000000000000\";}','2014-11-12T19:07:59.000000+0100','Ora\\TaskManagement\\Task','00000000-0000-0000-0000-000000000000');
INSERT INTO `tasks` (id, stream_id, subject, status, createdAt, mostRecentEditAt, createdBy_id, mostRecentEditBy_id, type) VALUES
('00000000-0000-0000-0000-000000000000','00000000-1000-0000-0000-000000000000','Development environment setup',20,'2014-11-12 19:07:59','2014-11-12 19:07:59','60000000-0000-0000-0000-000000000000','60000000-0000-0000-0000-000000000000','task');
INSERT INTO task_members(task_id, member_id,role) VALUES
('00000000-0000-0000-0000-000000000000','60000000-0000-0000-0000-000000000000','owner');

# task 00000000-0000-0000-0000-000000000001, completed, Mark Rogers (owner), Paul Smit (member)
INSERT INTO event_stream (eventId, version, eventName, payload, occurredOn, aggregate_type, aggregate_id) VALUES
('6126d983-20ad-47f2-9636-085395aa3b5b',1,'Ora\\TaskManagement\\TaskCreated','a:3:{s:6:\"status\";i:20;s:2:\"by\";s:36:\"60000000-0000-0000-0000-000000000000\";s:12:\"aggregate_id\";s:36:\"00000000-0000-0000-0000-000000000001\";}','2014-11-12T19:07:59.000000+0100','Ora\\TaskManagement\\Task','00000000-0000-0000-0000-000000000001'),
('334fa91f-62c9-4b34-827b-3e01bd7efe6c',2,'Ora\\TaskManagement\\TaskUpdated','a:3:{s:7:\"subject\";s:27:\"Continous integration setup\";s:2:\"by\";s:36:\"60000000-0000-0000-0000-000000000000\";s:12:\"aggregate_id\";s:36:\"00000000-0000-0000-0000-000000000001\";}','2014-11-12T19:07:59.000000+0100','Ora\\TaskManagement\\Task','00000000-0000-0000-0000-000000000001'),
('6e943d08-7a9a-4a2a-a8b5-201bfeb57b72',3,'Ora\\TaskManagement\\StreamChanged','a:3:{s:8:\"streamId\";s:36:\"00000000-1000-0000-0000-000000000000\";s:2:\"by\";s:36:\"60000000-0000-0000-0000-000000000000\";s:12:\"aggregate_id\";s:36:\"00000000-0000-0000-0000-000000000001\";}','2014-11-12T19:07:59.000000+0100','Ora\\TaskManagement\\Task','00000000-0000-0000-0000-000000000001'),
('c033eb32-2ad4-49d5-b25c-3c0b600b7dfc',4,'Ora\\TaskManagement\\MemberAdded','a:4:{s:6:\"userId\";s:36:\"60000000-0000-0000-0000-000000000000\";s:4:\"role\";s:5:\"owner\";s:2:\"by\";s:36:\"60000000-0000-0000-0000-000000000000\";s:12:\"aggregate_id\";s:36:\"00000000-0000-0000-0000-000000000001\";}','2014-11-12T19:07:59.000000+0100','Ora\\TaskManagement\\Task','00000000-0000-0000-0000-000000000001'),
('c233eb32-2ad4-49d5-b25c-3c0b600b7dec',5,'Ora\\TaskManagement\\MemberAdded','a:4:{s:6:\"userId\";s:36:\"20000000-0000-0000-0000-000000000000\";s:4:\"role\";s:6:\"member\";s:2:\"by\";s:36:\"20000000-0000-0000-0000-000000000000\";s:12:\"aggregate_id\";s:36:\"00000000-0000-0000-0000-000000000001\";}','2014-11-12T19:07:59.000000+0100','Ora\\TaskManagement\\Task','00000000-0000-0000-0000-000000000001'),
('fbdfdd17-61ef-4f80-bcd4-7e6eb6990713',6,'Ora\\TaskManagement\\TaskCompleted','a:2:{s:12:\"aggregate_id\";s:36:\"00000000-0000-0000-0000-000000000001\";s:2:\"by\";s:36:\"60000000-0000-0000-0000-000000000000\";}','2014-10-31T10:44:30.000000+0100','Ora\\TaskManagement\\Task','00000000-0000-0000-0000-000000000001');
INSERT INTO `tasks` (id, stream_id, subject, status, createdAt, mostRecentEditAt, createdBy_id, mostRecentEditBy_id, type) VALUES
('00000000-0000-0000-0000-000000000001','00000000-1000-0000-0000-000000000000','Continous integration setup',30,'2014-11-12 19:07:59','2014-11-12 19:07:59','60000000-0000-0000-0000-000000000000','60000000-0000-0000-0000-000000000000','task');
INSERT INTO task_members(task_id, member_id,role) VALUES
('00000000-0000-0000-0000-000000000001','60000000-0000-0000-0000-000000000000','owner');

# task 00000000-0000-0000-0000-000000000002, accepted, Mark Rogers (owner), Paul Smit (member)
INSERT INTO event_stream (eventId, version, eventName, payload, occurredOn, aggregate_type, aggregate_id) VALUES
('6126d983-20ad-47f2-9636-085395aa3b6b',1,'Ora\\TaskManagement\\TaskCreated','a:3:{s:6:\"status\";i:20;s:2:\"by\";s:36:\"60000000-0000-0000-0000-000000000000\";s:12:\"aggregate_id\";s:36:\"00000000-0000-0000-0000-000000000002\";}','2014-11-12T19:07:59.000000+0100','Ora\\TaskManagement\\Task','00000000-0000-0000-0000-000000000002'),
('334fa91f-62c9-4b34-827b-3e01bd7efe7c',2,'Ora\\TaskManagement\\TaskUpdated','a:3:{s:7:\"subject\";s:27:\"Technology stack definition\";s:2:\"by\";s:36:\"60000000-0000-0000-0000-000000000000\";s:12:\"aggregate_id\";s:36:\"00000000-0000-0000-0000-000000000002\";}','2014-11-12T19:07:59.000000+0100','Ora\\TaskManagement\\Task','00000000-0000-0000-0000-000000000002'),
('6e943d08-7a9a-4a2a-a8b5-201bfeb57b82',3,'Ora\\TaskManagement\\StreamChanged','a:3:{s:8:\"streamId\";s:36:\"00000000-1000-0000-0000-000000000000\";s:2:\"by\";s:36:\"60000000-0000-0000-0000-000000000000\";s:12:\"aggregate_id\";s:36:\"00000000-0000-0000-0000-000000000002\";}','2014-11-12T19:07:59.000000+0100','Ora\\TaskManagement\\Task','00000000-0000-0000-0000-000000000002'),
('c033eb32-2ad4-49d5-b25c-3c0b600b7d9c',4,'Ora\\TaskManagement\\MemberAdded','a:4:{s:6:\"userId\";s:36:\"60000000-0000-0000-0000-000000000000\";s:4:\"role\";s:5:\"owner\";s:2:\"by\";s:36:\"60000000-0000-0000-0000-000000000000\";s:12:\"aggregate_id\";s:36:\"00000000-0000-0000-0000-000000000002\";}','2014-11-12T19:07:59.000000+0100','Ora\\TaskManagement\\Task','00000000-0000-0000-0000-000000000002'),
('fbdfdd17-61ef-4f80-bcd4-7e6eb6990723',5,'Ora\\TaskManagement\\TaskCompleted','a:2:{s:12:\"aggregate_id\";s:36:\"00000000-0000-0000-0000-000000000002\";s:2:\"by\";s:36:\"60000000-0000-0000-0000-000000000000\";}','2014-10-31T10:44:30.000000+0100','Ora\\TaskManagement\\Task','00000000-0000-0000-0000-000000000002'),
('fbdfdd17-61ef-4f80-bcd4-7e6eb6990714',6,'Ora\\TaskManagement\\TaskAccepted','a:2:{s:12:\"aggregate_id\";s:36:\"00000000-0000-0000-0000-000000000002\";s:2:\"by\";s:36:\"60000000-0000-0000-0000-000000000000\";}','2014-10-31T10:44:30.000000+0100','Ora\\TaskManagement\\Task','00000000-0000-0000-0000-000000000002'),
('c333eb32-2ad4-49d5-b25c-3c0b600b7dec',7,'Ora\\TaskManagement\\MemberAdded','a:4:{s:6:\"userId\";s:36:\"20000000-0000-0000-0000-000000000000\";s:4:\"role\";s:6:\"member\";s:2:\"by\";s:36:\"20000000-0000-0000-0000-000000000000\";s:12:\"aggregate_id\";s:36:\"00000000-0000-0000-0000-000000000002\";}','2014-11-12T19:07:59.000000+0100','Ora\\TaskManagement\\Task','00000000-0000-0000-0000-000000000002');
INSERT INTO `tasks` (id, stream_id, subject, status, createdAt, mostRecentEditAt, createdBy_id, mostRecentEditBy_id, type) VALUES
('00000000-0000-0000-0000-000000000002','00000000-1000-0000-0000-000000000000','Technology stack definition',40,'2014-11-12 19:07:59','2014-11-12 19:07:59','60000000-0000-0000-0000-000000000000','60000000-0000-0000-0000-000000000000','task');
INSERT INTO task_members(task_id, member_id,role) VALUES
('00000000-0000-0000-0000-000000000002','60000000-0000-0000-0000-000000000000','owner'),
('00000000-0000-0000-0000-000000000002','20000000-0000-0000-0000-000000000000','member');


# task 00000000-0000-0000-0000-000000000003, deleted, Mark Rogers (owner)
INSERT INTO event_stream (eventId, version, eventName, payload, occurredOn, aggregate_type, aggregate_id) VALUES
('6126d983-20ad-47f2-9636-085395aa3b7b',1,'Ora\\TaskManagement\\TaskCreated','a:3:{s:6:\"status\";i:20;s:2:\"by\";s:36:\"60000000-0000-0000-0000-000000000000\";s:12:\"aggregate_id\";s:36:\"00000000-0000-0000-0000-000000000003\";}','2014-11-12T19:07:59.000000+0100','Ora\\TaskManagement\\Task','00000000-0000-0000-0000-000000000003'),
('334fa91f-62c9-4b34-827b-3e01bd7efe8c',2,'Ora\\TaskManagement\\TaskUpdated','a:3:{s:7:\"subject\";s:33:\"Setup the development environment\";s:2:\"by\";s:36:\"60000000-0000-0000-0000-000000000000\";s:12:\"aggregate_id\";s:36:\"00000000-0000-0000-0000-000000000003\";}','2014-11-12T19:07:59.000000+0100','Ora\\TaskManagement\\Task','00000000-0000-0000-0000-000000000003'),
('6e943d08-7a9a-4a2a-a8b5-201bfeb57b92',3,'Ora\\TaskManagement\\StreamChanged','a:3:{s:8:\"streamId\";s:36:\"00000000-1000-0000-0000-000000000000\";s:2:\"by\";s:36:\"60000000-0000-0000-0000-000000000000\";s:12:\"aggregate_id\";s:36:\"00000000-0000-0000-0000-000000000003\";}','2014-11-12T19:07:59.000000+0100','Ora\\TaskManagement\\Task','00000000-0000-0000-0000-000000000003'),
('c033eb32-2ad4-49d5-b25c-3c0b600b7d10',4,'Ora\\TaskManagement\\MemberAdded','a:4:{s:6:\"userId\";s:36:\"60000000-0000-0000-0000-000000000000\";s:4:\"role\";s:5:\"owner\";s:2:\"by\";s:36:\"60000000-0000-0000-0000-000000000000\";s:12:\"aggregate_id\";s:36:\"00000000-0000-0000-0000-000000000003\";}','2014-11-12T19:07:59.000000+0100','Ora\\TaskManagement\\Task','00000000-0000-0000-0000-000000000003'),
('6b73772b-efa9-475d-b3d8-abbcf3a84889',5,'Ora\\TaskManagement\\TaskDeleted','a:3:{s:10:\"prevStatus\";i:20;s:2:\"by\";s:36:\"266616a1-3160-4caa-ae56-35f884324a5a\";s:12:\"aggregate_id\";s:36:\"00000000-0000-0000-0000-000000000003\";}','2014-12-31T09:33:15.000000+0100','Ora\\TaskManagement\\Task','00000000-0000-0000-0000-000000000003');

# kanbanizeTask 00000000-0000-0000-0000-000000000106, accepted, Mark Rogers (owner)
INSERT INTO event_stream (eventId, version, eventName, payload, occurredOn, aggregate_type, aggregate_id) VALUES
('6126d983-20ad-47f2-9636-085395aa3b8b',1,'Ora\\TaskManagement\\TaskCreated','a:5:{s:6:\"status\";i:20;s:2:\"by\";s:36:\"60000000-0000-0000-0000-000000000000\";s:16:\"kanbanizeBoardId\";i:3;s:15:\"kanbanizeTaskId\";i:114;s:12:\"aggregate_id\";s:36:\"00000000-0000-0000-0000-000000000106\";}','2014-11-12T19:07:00.000000+0100','Ora\\Kanbanize\\KanbanizeTask','00000000-0000-0000-0000-000000000106'),
('334fa91f-62c9-4b34-827b-3e01bd7efe9c',2,'Ora\\TaskManagement\\TaskUpdated','a:3:{s:7:\"subject\";s:16:\"Kanbanize Task 1\";s:2:\"by\";s:36:\"60000000-0000-0000-0000-000000000000\";s:12:\"aggregate_id\";s:36:\"00000000-0000-0000-0000-000000000106\";}','2014-11-12T19:07:59.000000+0100','Ora\\Kanbanize\\KanbanizeTask','00000000-0000-0000-0000-000000000106'),
('6e943d08-7a9a-4a2a-a8b5-201bfeb57b11',3,'Ora\\TaskManagement\\StreamChanged','a:3:{s:8:\"streamId\";s:36:\"00000000-1100-0000-0000-000000000000\";s:2:\"by\";s:36:\"60000000-0000-0000-0000-000000000000\";s:12:\"aggregate_id\";s:36:\"00000000-0000-0000-0000-000000000106\";}','2014-11-12T19:07:59.000000+0100','Ora\\Kanbanize\\KanbanizeTask','00000000-0000-0000-0000-000000000106'),
('c033eb32-2ad4-49d5-b25c-3c0b600b7d12',4,'Ora\\TaskManagement\\MemberAdded','a:4:{s:6:\"userId\";s:36:\"60000000-0000-0000-0000-000000000000\";s:4:\"role\";s:5:\"owner\";s:2:\"by\";s:36:\"60000000-0000-0000-0000-000000000000\";s:12:\"aggregate_id\";s:36:\"00000000-0000-0000-0000-000000000106\";}','2014-11-12T19:07:59.000000+0100','Ora\\Kanbanize\\KanbanizeTask','00000000-0000-0000-0000-000000000106'),
('fbdfdd17-61ef-4f80-bcd4-7e6eb6991723',5,'Ora\\TaskManagement\\TaskCompleted','a:2:{s:12:\"aggregate_id\";s:36:\"00000000-0000-0000-0000-000000000106\";s:2:\"by\";s:36:\"60000000-0000-0000-0000-000000000000\";}','2014-10-31T10:44:30.000000+0100','Ora\\Kanbanize\\KanbanizeTask','00000000-0000-0000-0000-000000000106'),
('fbdfdd17-61ef-4f80-bcd4-7e6eb6991714',6,'Ora\\TaskManagement\\TaskAccepted','a:2:{s:12:\"aggregate_id\";s:36:\"00000000-0000-0000-0000-000000000106\";s:2:\"by\";s:36:\"60000000-0000-0000-0000-000000000000\";}','2014-10-31T10:44:31.000000+0100','Ora\\Kanbanize\\KanbanizeTask','00000000-0000-0000-0000-000000000106');
INSERT INTO tasks (id, stream_id, subject, status, createdAt, mostRecentEditAt, type, boardId, taskId) VALUES
('00000000-0000-0000-0000-000000000106','00000000-1000-0000-0000-000000000000','Accepted Kanbanize Task',40,'2014-11-06 14:32:44','2014-11-06 14:32:44','kanbanizetask',3,114);
INSERT INTO task_members(task_id, member_id, estimation_value, estimation_createdAt, role) VALUES
('00000000-0000-0000-0000-000000000106','60000000-0000-0000-0000-000000000000','-1','2014-11-07 11:37:58','owner');

# kanbanizeTask 00000000-0000-0000-0000-000000000107, completed, Mark Rogers (owner), Bruce Wayne (member)
INSERT INTO event_stream (eventId, version, eventName, payload, occurredOn, aggregate_type, aggregate_id) VALUES
('6126d983-20ad-47f2-9636-085395aa3b40',1,'Ora\\TaskManagement\\TaskCreated','a:5:{s:6:\"status\";i:20;s:2:\"by\";s:36:\"60000000-0000-0000-0000-000000000000\";s:16:\"kanbanizeBoardId\";i:3;s:15:\"kanbanizeTaskId\";i:115;s:12:\"aggregate_id\";s:36:\"00000000-0000-0000-0000-000000000107\";}','2014-11-12T19:07:59.000000+0100','Ora\\TaskManagement\\Task','00000000-0000-0000-0000-000000000107'),
('334fa91f-62c9-4b34-827b-3e01bd7efe50',2,'Ora\\TaskManagement\\TaskUpdated','a:3:{s:7:\"subject\";s:16:\"Kanbanize Task 2\";s:2:\"by\";s:36:\"60000000-0000-0000-0000-000000000000\";s:12:\"aggregate_id\";s:36:\"00000000-0000-0000-0000-000000000107\";}','2014-11-12T19:07:59.000000+0100','Ora\\TaskManagement\\Task','00000000-0000-0000-0000-000000000107'),
('6e943d08-7a9a-4a2a-a8b5-201bfeb57b63',3,'Ora\\TaskManagement\\StreamChanged','a:3:{s:8:\"streamId\";s:36:\"00000000-1000-0000-0000-000000000000\";s:2:\"by\";s:36:\"60000000-0000-0000-0000-000000000000\";s:12:\"aggregate_id\";s:36:\"00000000-0000-0000-0000-000000000107\";}','2014-11-12T19:07:59.000000+0100','Ora\\TaskManagement\\Task','00000000-0000-0000-0000-000000000107'),
('c033eb32-2ad4-49d5-b25c-3c0b600b7de0',4,'Ora\\TaskManagement\\MemberAdded','a:4:{s:6:\"userId\";s:36:\"60000000-0000-0000-0000-000000000000\";s:4:\"role\";s:5:\"owner\";s:2:\"by\";s:36:\"60000000-0000-0000-0000-000000000000\";s:12:\"aggregate_id\";s:36:\"00000000-0000-0000-0000-000000000107\";}','2014-11-12T19:07:59.000000+0100','Ora\\TaskManagement\\Task','00000000-0000-0000-0000-000000000107'),
('c033eb32-2ad4-49d5-b25c-3c0b610b7de0',5,'Ora\\TaskManagement\\MemberAdded','a:4:{s:6:\"userId\";s:36:\"80000000-0000-0000-0000-000000000000\";s:4:\"role\";s:6:\"member\";s:2:\"by\";s:36:\"80000000-0000-0000-0000-000000000000\";s:12:\"aggregate_id\";s:36:\"00000000-0000-0000-0000-000000000107\";}','2014-11-12T19:07:59.000000+0100','Ora\\TaskManagement\\Task','00000000-0000-0000-0000-000000000107'),
('fbdfdd17-61ef-4f80-bcd4-7e6eb6901723',6,'Ora\\TaskManagement\\TaskCompleted','a:2:{s:12:\"aggregate_id\";s:36:\"00000000-0000-0000-0000-000000000107\";s:2:\"by\";s:36:\"60000000-0000-0000-0000-000000000000\";}','2014-10-31T10:44:30.000000+0100','Ora\\Kanbanize\\KanbanizeTask','00000000-0000-0000-0000-000000000107'),
('65696b0b-0790-48b7-a122-ff2078c5bf20',7,'Ora\\TaskManagement\\EstimationAdded','a:3:{s:2:"by";s:36:"60000000-0000-0000-0000-000000000000";s:5:"value";s:2:"-1";s:12:"aggregate_id";s:36:"00000000-0000-0000-0000-000000000107";}','2015-02-02T21:22:11.000000+0100','Ora\\Kanbanize\\KanbanizeTask','00000000-0000-0000-0000-000000000107'),
('65606b0b-0790-48b7-a122-ff2078c5bf20',8,'Ora\\TaskManagement\\EstimationAdded','a:3:{s:2:"by";s:36:"80000000-0000-0000-0000-000000000000";s:5:"value";s:4:"1500";s:12:"aggregate_id";s:36:"00000000-0000-0000-0000-000000000107";}','2015-02-02T21:22:11.000000+0100','Ora\\Kanbanize\\KanbanizeTask','00000000-0000-0000-0000-000000000107');
INSERT INTO tasks (id, stream_id, subject, status, createdAt, mostRecentEditAt, type, boardId, taskId) VALUES
('00000000-0000-0000-0000-000000000107','00000000-1000-0000-0000-000000000000','Kanbanize Task 2',30,'2014-11-06 14:43:28','2014-11-06 14:43:28','kanbanizetask',3,115);
INSERT INTO task_members (task_id, member_id, estimation_value, estimation_createdAt, role) VALUES
('00000000-0000-0000-0000-000000000107','80000000-0000-0000-0000-000000000000','1500,00', '2014-11-07 11:37:58','member'),
('00000000-0000-0000-0000-000000000107','60000000-0000-0000-0000-000000000000','-1', '2014-11-07 11:37:58','owner');

# kanbanizeTask 00000000-0000-0000-0000-000000000108, ongoing, Mark Rogers (owner), Bruce Wayne (member)
INSERT INTO event_stream (eventId, version, eventName, payload, occurredOn, aggregate_type, aggregate_id) VALUES
('6126d983-20ad-47f2-9636-085395aa3b41',1,'Ora\\TaskManagement\\TaskCreated','a:5:{s:6:\"status\";i:20;s:2:\"by\";s:36:\"60000000-0000-0000-0000-000000000000\";s:16:\"kanbanizeBoardId\";i:3;s:15:\"kanbanizeTaskId\";i:116;s:12:\"aggregate_id\";s:36:\"00000000-0000-0000-0000-000000000108\";}','2014-11-12T19:07:59.000000+0100','Ora\\TaskManagement\\Task','00000000-0000-0000-0000-000000000108'),
('334fa91f-62c9-4b34-827b-3e01bd7efe51',2,'Ora\\TaskManagement\\TaskUpdated','a:3:{s:7:\"subject\";s:16:\"Kanbanize Task 3\";s:2:\"by\";s:36:\"60000000-0000-0000-0000-000000000000\";s:12:\"aggregate_id\";s:36:\"00000000-0000-0000-0000-000000000108\";}','2014-11-12T19:07:59.000000+0100','Ora\\TaskManagement\\Task','00000000-0000-0000-0000-000000000108'),
('6e943d08-7a9a-4a2a-a8b5-201bfeb57b64',3,'Ora\\TaskManagement\\StreamChanged','a:3:{s:8:\"streamId\";s:36:\"00000000-1000-0000-0000-000000000000\";s:2:\"by\";s:36:\"60000000-0000-0000-0000-000000000000\";s:12:\"aggregate_id\";s:36:\"00000000-0000-0000-0000-000000000108\";}','2014-11-12T19:07:59.000000+0100','Ora\\TaskManagement\\Task','00000000-0000-0000-0000-000000000108'),
('c033eb32-2ad4-49d5-b25c-3c0b600b7de1',4,'Ora\\TaskManagement\\MemberAdded','a:4:{s:6:\"userId\";s:36:\"60000000-0000-0000-0000-000000000000\";s:4:\"role\";s:5:\"owner\";s:2:\"by\";s:36:\"60000000-0000-0000-0000-000000000000\";s:12:\"aggregate_id\";s:36:\"00000000-0000-0000-0000-000000000108\";}','2014-11-12T19:07:59.000000+0100','Ora\\TaskManagement\\Task','00000000-0000-0000-0000-000000000108');
INSERT INTO tasks (id, stream_id, subject, status, createdAt, mostRecentEditAt, type, boardId, taskId) VALUES
('00000000-0000-0000-0000-000000000108','00000000-1000-0000-0000-000000000000','Kanbanize Task 3',20,'2014-11-06 14:51:12','2014-11-06 14:51:12','kanbanizetask',3,116);
INSERT INTO task_members(task_id, member_id, estimation_value, estimation_createdAt, role) VALUES
('00000000-0000-0000-0000-000000000108','60000000-0000-0000-0000-000000000000','1500,00','2014-11-07 11:37:58', 'owner'),
('00000000-0000-0000-0000-000000000108','80000000-0000-0000-0000-000000000000',NULL, NULL, 'member');

# kanbanizeTask 00000000-0000-0000-0000-000000000110, ongoing, Mark Rogers (owner)
INSERT INTO event_stream (eventId, version, eventName, payload, occurredOn, aggregate_type, aggregate_id) VALUES
('6126d984-20ad-47f2-9636-085395aa3b40',1,'Ora\\TaskManagement\\TaskCreated','a:5:{s:6:\"status\";i:20;s:2:\"by\";s:36:\"60000000-0000-0000-0000-000000000000\";s:16:\"kanbanizeBoardId\";i:3;s:15:\"kanbanizeTaskId\";i:119;s:12:\"aggregate_id\";s:36:\"00000000-0000-0000-0000-000000000110\";}','2014-11-12T19:07:59.000000+0100','Ora\\TaskManagement\\Task','00000000-0000-0000-0000-000000000110'),
('334fa91e-62c9-4b34-827b-3e01bd7efe50',2,'Ora\\TaskManagement\\TaskUpdated','a:3:{s:7:\"subject\";s:16:\"Kanbanize Task 2\";s:2:\"by\";s:36:\"60000000-0000-0000-0000-000000000000\";s:12:\"aggregate_id\";s:36:\"00000000-0000-0000-0000-000000000110\";}','2014-11-12T19:07:59.000000+0100','Ora\\TaskManagement\\Task','00000000-0000-0000-0000-000000000110'),
('6e943d09-7a9a-4a2a-a8b5-201bfeb57b63',3,'Ora\\TaskManagement\\StreamChanged','a:3:{s:8:\"streamId\";s:36:\"00000000-1000-0000-0000-000000000000\";s:2:\"by\";s:36:\"60000000-0000-0000-0000-000000000000\";s:12:\"aggregate_id\";s:36:\"00000000-0000-0000-0000-000000000110\";}','2014-11-12T19:07:59.000000+0100','Ora\\TaskManagement\\Task','00000000-0000-0000-0000-000000000110'),
('c033eb33-2ad4-49d5-b25c-3c0b600b7de0',4,'Ora\\TaskManagement\\MemberAdded','a:4:{s:6:\"userId\";s:36:\"60000000-0000-0000-0000-000000000000\";s:4:\"role\";s:5:\"owner\";s:2:\"by\";s:36:\"60000000-0000-0000-0000-000000000000\";s:12:\"aggregate_id\";s:36:\"00000000-0000-0000-0000-000000000110\";}','2014-11-12T19:07:59.000000+0100','Ora\\TaskManagement\\Task','00000000-0000-0000-0000-000000000110'),
('fbdfdd18-61ef-4f80-bcd4-7e6eb6901723',5,'Ora\\TaskManagement\\TaskCompleted','a:2:{s:12:\"aggregate_id\";s:36:\"00000000-0000-0000-0000-000000000110\";s:2:\"by\";s:36:\"60000000-0000-0000-0000-000000000000\";}','2014-10-31T10:44:30.000000+0100','Ora\\Kanbanize\\KanbanizeTask','00000000-0000-0000-0000-000000000110');
INSERT INTO tasks (id, stream_id, subject, status, createdAt, mostRecentEditAt, type, boardId, taskId) VALUES
('00000000-0000-0000-0000-000000000110','00000000-1000-0000-0000-000000000000','completedTask',30,'2014-11-06 15:39:13','2014-11-06 15:39:13','kanbanizetask',3,119);
INSERT INTO task_members(task_id, member_id,role) VALUES
('00000000-0000-0000-0000-000000000110','60000000-0000-0000-0000-000000000000','owner');

# kanbanizeTask 00000000-0000-0000-0000-000000000111, accepted, Mark Rogers (owner)
INSERT INTO event_stream (eventId, version, eventName, payload, occurredOn, aggregate_type, aggregate_id) VALUES
('6136d983-20ad-47f2-9636-085395aa3b8b',1,'Ora\\TaskManagement\\TaskCreated','a:5:{s:6:\"status\";i:20;s:2:\"by\";s:36:\"60000000-0000-0000-0000-000000000000\";s:16:\"kanbanizeBoardId\";i:3;s:15:\"kanbanizeTaskId\";i:120;s:12:\"aggregate_id\";s:36:\"00000000-0000-0000-0000-000000000111\";}','2014-11-12T19:07:00.000000+0100','Ora\\Kanbanize\\KanbanizeTask','00000000-0000-0000-0000-000000000111'),
('335fa91f-62c9-4b34-827b-3e01bd7efe9c',2,'Ora\\TaskManagement\\TaskUpdated','a:3:{s:7:\"subject\";s:16:\"Kanbanize Task 1\";s:2:\"by\";s:36:\"60000000-0000-0000-0000-000000000000\";s:12:\"aggregate_id\";s:36:\"00000000-0000-0000-0000-000000000111\";}','2014-11-12T19:07:59.000000+0100','Ora\\Kanbanize\\KanbanizeTask','00000000-0000-0000-0000-000000000111'),
('6e043d08-7a9a-4a2a-a8b5-201bfeb57b11',3,'Ora\\TaskManagement\\StreamChanged','a:3:{s:8:\"streamId\";s:36:\"00000000-1100-0000-0000-000000000000\";s:2:\"by\";s:36:\"60000000-0000-0000-0000-000000000000\";s:12:\"aggregate_id\";s:36:\"00000000-0000-0000-0000-000000000111\";}','2014-11-12T19:07:59.000000+0100','Ora\\Kanbanize\\KanbanizeTask','00000000-0000-0000-0000-000000000111'),
('c043eb32-2ad4-49d5-b25c-3c0b600b7d12',4,'Ora\\TaskManagement\\MemberAdded','a:4:{s:6:\"userId\";s:36:\"60000000-0000-0000-0000-000000000000\";s:4:\"role\";s:5:\"owner\";s:2:\"by\";s:36:\"60000000-0000-0000-0000-000000000000\";s:12:\"aggregate_id\";s:36:\"00000000-0000-0000-0000-000000000111\";}','2014-11-12T19:07:59.000000+0100','Ora\\Kanbanize\\KanbanizeTask','00000000-0000-0000-0000-000000000111'),
('fbefdd17-61ef-4f80-bcd4-7e6eb6991723',5,'Ora\\TaskManagement\\TaskCompleted','a:2:{s:12:\"aggregate_id\";s:36:\"00000000-0000-0000-0000-000000000111\";s:2:\"by\";s:36:\"60000000-0000-0000-0000-000000000000\";}','2014-10-31T10:44:30.000000+0100','Ora\\Kanbanize\\KanbanizeTask','00000000-0000-0000-0000-000000000111'),
('fbefdd17-61ef-4f80-bcd4-7e6eb6991714',6,'Ora\\TaskManagement\\TaskAccepted','a:2:{s:12:\"aggregate_id\";s:36:\"00000000-0000-0000-0000-000000000111\";s:2:\"by\";s:36:\"60000000-0000-0000-0000-000000000000\";}','2014-10-31T10:44:31.000000+0100','Ora\\Kanbanize\\KanbanizeTask','00000000-0000-0000-0000-000000000111');
INSERT INTO tasks (id, stream_id, subject, status, createdAt, mostRecentEditAt, type, boardId, taskId) VALUES
('00000000-0000-0000-0000-000000000111','00000000-1000-0000-0000-000000000000','completedTask',40,'2014-11-06 15:48:17','2014-11-06 15:48:17','kanbanizetask',3,120);
INSERT INTO task_members(task_id, member_id,role) VALUES
('00000000-0000-0000-0000-000000000111','60000000-0000-0000-0000-000000000000','owner');

# account
INSERT INTO event_stream (eventId, version, eventName, payload, occurredOn, aggregate_type, aggregate_id) VALUES
('44f1b89a-156b-4dbf-b5c9-9a0540460a0b',1,'Ora\\Accounting\\AccountCreated','a:3:{s:7:\"balance\";i:0;s:7:\"holders\";a:1:{s:36:\"60000000-0000-0000-0000-000000000000\";s:11:\"Mark Rogers\";}s:12:\"aggregate_id\";s:36:\"dcde992b-5aa9-4447-98ae-c8115906dcb7\";}','2014-12-29T17:32:07.000000+0100','Ora\\Accounting\\OrganizationAccount','dcde992b-5aa9-4447-98ae-c8115906dcb7');

# stream -> organization
INSERT INTO event_stream (eventId, version, eventName, payload, occurredOn, aggregate_type, aggregate_id) VALUES
('6e943d09-7a9a-4a2a-a8b5-201bfeb57b63',3,'Ora\\StreamManagement\\OrganizationUpdated','a:3:{s:14:\"organizationId\";s:36:\"00000000-0000-0000-1000-000000000000\";s:2:\"by\";s:36:\"60000000-0000-0000-0000-000000000000\";s:12:\"aggregate_id\";s:36:\"00000000-0000-0000-0000-000000000110\";}','2014-11-12T19:07:59.000000+0100','Ora\\StreamManagement\\Stream','00000000-1000-0000-0000-000000000000');

# organization
INSERT INTO event_stream (eventId, version, eventName, payload, occurredOn, aggregate_type, aggregate_id) VALUES
('6e943d09-7a9a-4a2a-a8b5-201bfeb57b63',3,'Ora\\Organization\\OrganizationCreated','a:3:{s:14:\"organizationId\";s:36:\"00000000-0000-0000-1000-000000000000\";s:2:\"by\";s:36:\"60000000-0000-0000-0000-000000000000\";s:12:\"aggregate_id\";s:36:\"00000000-0000-0000-0000-000000000110\";}','2014-11-12T19:07:59.000000+0100','Ora\\Organization\\Organization','00000000-1000-0000-0000-000000000000');

