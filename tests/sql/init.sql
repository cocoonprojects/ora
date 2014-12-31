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

delete from tasks_members;
delete from tasks;
delete from streams;
delete from accounts;
delete from organizations;
delete from event_stream;
delete from estimations;
delete from users;

INSERT INTO users (id, status, createdAt, firstname, lastname, email) VALUES ('60000000-0000-0000-0000-000000000000', 1, '2014-10-09 11:33:45', 'Mark', 'Rogers', 'mark.rogers@ora.local');
INSERT INTO users (id, status, createdAt, firstname, lastname, email) VALUES ('70000000-0000-0000-0000-000000000000', 1, '2014-10-09 11:33:45', 'Phil', 'Toledo', 'phil.toledo@ora.local');
INSERT INTO users (id, status, createdAt, firstname, lastname, email) VALUES ('20000000-0000-0000-0000-000000000000', 1, '2014-10-09 11:33:45', 'Paul', 'Smith', 'paul.smith@ora.local');
INSERT INTO users (id, status, createdAt, firstname, lastname, email) VALUES ('80000000-0000-0000-0000-000000000000', 1, '2014-10-09 11:33:45', 'Bruce', 'Wayne', 'bruce.wayne@gotham.local');
INSERT INTO users (id, status, createdAt, firstname, lastname, email) VALUES ('90000000-0000-0000-0000-000000000000', 1, '2014-10-09 11:33:45', 'Peter', 'Parker', 'spidey.web@dailybugle.local');

# task 00000000-0000-0000-0000-000000000000, ongoing, Mark Rogers (owner)
INSERT INTO event_stream (eventId, version, eventName, payload, occurredOn, aggregate_type, aggregate_id) VALUES
('6126d983-20ad-47f2-9636-085395aa3b4b',1,'Ora\\TaskManagement\\TaskCreated','a:3:{s:6:\"status\";i:20;s:2:\"by\";s:36:\"60000000-0000-0000-0000-000000000000\";s:12:\"aggregate_id\";s:36:\"00000000-0000-0000-0000-000000000000\";}','2014-11-12T19:07:59.000000+0100','Ora\\TaskManagement\\Task','00000000-0000-0000-0000-000000000000'),
('334fa91f-62c9-4b34-827b-3e01bd7efe5c',2,'Ora\\TaskManagement\\TaskUpdated','a:3:{s:7:\"subject\";s:29:\"Development environment setup\";s:2:\"by\";s:36:\"60000000-0000-0000-0000-000000000000\";s:12:\"aggregate_id\";s:36:\"00000000-0000-0000-0000-000000000000\";}','2014-11-12T19:07:59.000000+0100','Ora\\TaskManagement\\Task','00000000-0000-0000-0000-000000000000'),
('6e943d08-7a9a-4a2a-a8b5-201bfeb57b62',3,'Ora\\TaskManagement\\StreamChanged','a:3:{s:8:\"streamId\";s:36:\"00000000-1000-0000-0000-000000000000\";s:2:\"by\";s:36:\"60000000-0000-0000-0000-000000000000\";s:12:\"aggregate_id\";s:36:\"00000000-0000-0000-0000-000000000000\";}','2014-11-12T19:07:59.000000+0100','Ora\\TaskManagement\\Task','00000000-0000-0000-0000-000000000000'),
('c033eb32-2ad4-49d5-b25c-3c0b600b7dec',4,'Ora\\TaskManagement\\MemberAdded','a:4:{s:6:\"userId\";s:36:\"60000000-0000-0000-0000-000000000000\";s:4:\"role\";s:5:\"owner\";s:2:\"by\";s:36:\"60000000-0000-0000-0000-000000000000\";s:12:\"aggregate_id\";s:36:\"00000000-0000-0000-0000-000000000000\";}','2014-11-12T19:07:59.000000+0100','Ora\\TaskManagement\\Task','00000000-0000-0000-0000-000000000000');

# task 00000000-0000-0000-0000-000000000001, completed, Mark Rogers (owner)
INSERT INTO event_stream (eventId, version, eventName, payload, occurredOn, aggregate_type, aggregate_id) VALUES
('6126d983-20ad-47f2-9636-085395aa3b5b',1,'Ora\\TaskManagement\\TaskCreated','a:3:{s:6:\"status\";i:20;s:2:\"by\";s:36:\"60000000-0000-0000-0000-000000000000\";s:12:\"aggregate_id\";s:36:\"00000000-0000-0000-0000-000000000001\";}','2014-11-12T19:07:59.000000+0100','Ora\\TaskManagement\\Task','00000000-0000-0000-0000-000000000001'),
('334fa91f-62c9-4b34-827b-3e01bd7efe6c',2,'Ora\\TaskManagement\\TaskUpdated','a:3:{s:7:\"subject\";s:27:\"Continous integration setup\";s:2:\"by\";s:36:\"60000000-0000-0000-0000-000000000000\";s:12:\"aggregate_id\";s:36:\"00000000-0000-0000-0000-000000000001\";}','2014-11-12T19:07:59.000000+0100','Ora\\TaskManagement\\Task','00000000-0000-0000-0000-000000000001'),
('6e943d08-7a9a-4a2a-a8b5-201bfeb57b72',3,'Ora\\TaskManagement\\StreamChanged','a:3:{s:8:\"streamId\";s:36:\"00000000-1000-0000-0000-000000000000\";s:2:\"by\";s:36:\"60000000-0000-0000-0000-000000000000\";s:12:\"aggregate_id\";s:36:\"00000000-0000-0000-0000-000000000001\";}','2014-11-12T19:07:59.000000+0100','Ora\\TaskManagement\\Task','00000000-0000-0000-0000-000000000001'),
('c033eb32-2ad4-49d5-b25c-3c0b600b7dfc',4,'Ora\\TaskManagement\\MemberAdded','a:4:{s:6:\"userId\";s:36:\"60000000-0000-0000-0000-000000000000\";s:4:\"role\";s:5:\"owner\";s:2:\"by\";s:36:\"60000000-0000-0000-0000-000000000000\";s:12:\"aggregate_id\";s:36:\"00000000-0000-0000-0000-000000000001\";}','2014-11-12T19:07:59.000000+0100','Ora\\TaskManagement\\Task','00000000-0000-0000-0000-000000000001'),
('fbdfdd17-61ef-4f80-bcd4-7e6eb6990713',2,'Ora\\TaskManagement\\TaskCompleted','a:2:{s:12:\"aggregate_id\";s:36:\"00000000-0000-0000-0000-000000000001\";s:2:\"by\";s:36:\"60000000-0000-0000-0000-000000000000\";}','2014-10-31T10:44:30.000000+0100','Ora\\TaskManagement\\Task','00000000-0000-0000-0000-000000000001');

# task 00000000-0000-0000-0000-000000000002, accepted, Mark Rogers (owner)
INSERT INTO event_stream (eventId, version, eventName, payload, occurredOn, aggregate_type, aggregate_id) VALUES
('6126d983-20ad-47f2-9636-085395aa3b6b',1,'Ora\\TaskManagement\\TaskCreated','a:3:{s:6:\"status\";i:20;s:2:\"by\";s:36:\"60000000-0000-0000-0000-000000000000\";s:12:\"aggregate_id\";s:36:\"00000000-0000-0000-0000-000000000002\";}','2014-11-12T19:07:59.000000+0100','Ora\\TaskManagement\\Task','00000000-0000-0000-0000-000000000002'),
('334fa91f-62c9-4b34-827b-3e01bd7efe7c',2,'Ora\\TaskManagement\\TaskUpdated','a:3:{s:7:\"subject\";s:27:\"Technology stack definition\";s:2:\"by\";s:36:\"60000000-0000-0000-0000-000000000000\";s:12:\"aggregate_id\";s:36:\"00000000-0000-0000-0000-000000000002\";}','2014-11-12T19:07:59.000000+0100','Ora\\TaskManagement\\Task','00000000-0000-0000-0000-000000000002'),
('6e943d08-7a9a-4a2a-a8b5-201bfeb57b82',3,'Ora\\TaskManagement\\StreamChanged','a:3:{s:8:\"streamId\";s:36:\"00000000-1000-0000-0000-000000000000\";s:2:\"by\";s:36:\"60000000-0000-0000-0000-000000000000\";s:12:\"aggregate_id\";s:36:\"00000000-0000-0000-0000-000000000002\";}','2014-11-12T19:07:59.000000+0100','Ora\\TaskManagement\\Task','00000000-0000-0000-0000-000000000002'),
('c033eb32-2ad4-49d5-b25c-3c0b600b7d9c',4,'Ora\\TaskManagement\\MemberAdded','a:4:{s:6:\"userId\";s:36:\"60000000-0000-0000-0000-000000000000\";s:4:\"role\";s:5:\"owner\";s:2:\"by\";s:36:\"60000000-0000-0000-0000-000000000000\";s:12:\"aggregate_id\";s:36:\"00000000-0000-0000-0000-000000000002\";}','2014-11-12T19:07:59.000000+0100','Ora\\TaskManagement\\Task','00000000-0000-0000-0000-000000000002'),
('fbdfdd17-61ef-4f80-bcd4-7e6eb6990723',2,'Ora\\TaskManagement\\TaskCompleted','a:2:{s:12:\"aggregate_id\";s:36:\"00000000-0000-0000-0000-000000000002\";s:2:\"by\";s:36:\"60000000-0000-0000-0000-000000000000\";}','2014-10-31T10:44:30.000000+0100','Ora\\TaskManagement\\Task','00000000-0000-0000-0000-000000000002'),
('fbdfdd17-61ef-4f80-bcd4-7e6eb6990714',2,'Ora\\TaskManagement\\TaskAccepted','a:2:{s:12:\"aggregate_id\";s:36:\"00000000-0000-0000-0000-000000000002\";s:2:\"by\";s:36:\"60000000-0000-0000-0000-000000000000\";}','2014-10-31T10:44:30.000000+0100','Ora\\TaskManagement\\Task','00000000-0000-0000-0000-000000000002');

# task 00000000-0000-0000-0000-000000000003, deleted, Mark Rogers (owner)
INSERT INTO event_stream (eventId, version, eventName, payload, occurredOn, aggregate_type, aggregate_id) VALUES
('6126d983-20ad-47f2-9636-085395aa3b7b',1,'Ora\\TaskManagement\\TaskCreated','a:3:{s:6:\"status\";i:20;s:2:\"by\";s:36:\"60000000-0000-0000-0000-000000000000\";s:12:\"aggregate_id\";s:36:\"00000000-0000-0000-0000-000000000003\";}','2014-11-12T19:07:59.000000+0100','Ora\\TaskManagement\\Task','00000000-0000-0000-0000-000000000003'),
('334fa91f-62c9-4b34-827b-3e01bd7efe8c',2,'Ora\\TaskManagement\\TaskUpdated','a:3:{s:7:\"subject\";s:33:\"Setup the development environment\";s:2:\"by\";s:36:\"60000000-0000-0000-0000-000000000000\";s:12:\"aggregate_id\";s:36:\"00000000-0000-0000-0000-000000000003\";}','2014-11-12T19:07:59.000000+0100','Ora\\TaskManagement\\Task','00000000-0000-0000-0000-000000000003'),
('6e943d08-7a9a-4a2a-a8b5-201bfeb57b92',3,'Ora\\TaskManagement\\StreamChanged','a:3:{s:8:\"streamId\";s:36:\"00000000-1000-0000-0000-000000000000\";s:2:\"by\";s:36:\"60000000-0000-0000-0000-000000000000\";s:12:\"aggregate_id\";s:36:\"00000000-0000-0000-0000-000000000003\";}','2014-11-12T19:07:59.000000+0100','Ora\\TaskManagement\\Task','00000000-0000-0000-0000-000000000003'),
('c033eb32-2ad4-49d5-b25c-3c0b600b7d10',4,'Ora\\TaskManagement\\MemberAdded','a:4:{s:6:\"userId\";s:36:\"60000000-0000-0000-0000-000000000000\";s:4:\"role\";s:5:\"owner\";s:2:\"by\";s:36:\"60000000-0000-0000-0000-000000000000\";s:12:\"aggregate_id\";s:36:\"00000000-0000-0000-0000-000000000003\";}','2014-11-12T19:07:59.000000+0100','Ora\\TaskManagement\\Task','00000000-0000-0000-0000-000000000003'),
('6b73772b-efa9-475d-b3d8-abbcf3a84889',5,'Ora\\TaskManagement\\TaskDeleted','a:3:{s:10:\"prevStatus\";i:20;s:2:\"by\";s:36:\"266616a1-3160-4caa-ae56-35f884324a5a\";s:12:\"aggregate_id\";s:36:\"00000000-0000-0000-0000-000000000003\";}','2014-12-31T09:33:15.000000+0100','Ora\\TaskManagement\\Task','00000000-0000-0000-0000-000000000003');

# task 00000000-0000-0000-0000-000000000004, ongoing, Mark Rogers (owner) [Unused]
INSERT INTO event_stream (eventId, version, eventName, payload, occurredOn, aggregate_type, aggregate_id) VALUES
('6126d983-20ad-47f2-9636-085395aa3b8b',1,'Ora\\TaskManagement\\TaskCreated','a:3:{s:6:\"status\";i:20;s:2:\"by\";s:36:\"60000000-0000-0000-0000-000000000000\";s:12:\"aggregate_id\";s:36:\"00000000-0000-0000-0000-000000000004\";}','2014-11-12T19:07:59.000000+0100','Ora\\TaskManagement\\Task','00000000-0000-0000-0000-000000000004'),
('334fa91f-62c9-4b34-827b-3e01bd7efe9c',2,'Ora\\TaskManagement\\TaskUpdated','a:3:{s:7:\"subject\";s:33:\"Setup the development environment\";s:2:\"by\";s:36:\"60000000-0000-0000-0000-000000000000\";s:12:\"aggregate_id\";s:36:\"00000000-0000-0000-0000-000000000004\";}','2014-11-12T19:07:59.000000+0100','Ora\\TaskManagement\\Task','00000000-0000-0000-0000-000000000004'),
('6e943d08-7a9a-4a2a-a8b5-201bfeb57b11',3,'Ora\\TaskManagement\\StreamChanged','a:3:{s:8:\"streamId\";s:36:\"00000000-1000-0000-0000-000000000000\";s:2:\"by\";s:36:\"60000000-0000-0000-0000-000000000000\";s:12:\"aggregate_id\";s:36:\"00000000-0000-0000-0000-000000000004\";}','2014-11-12T19:07:59.000000+0100','Ora\\TaskManagement\\Task','00000000-0000-0000-0000-000000000004'),
('c033eb32-2ad4-49d5-b25c-3c0b600b7d12',4,'Ora\\TaskManagement\\MemberAdded','a:4:{s:6:\"userId\";s:36:\"60000000-0000-0000-0000-000000000000\";s:4:\"role\";s:5:\"owner\";s:2:\"by\";s:36:\"60000000-0000-0000-0000-000000000000\";s:12:\"aggregate_id\";s:36:\"00000000-0000-0000-0000-000000000004\";}','2014-11-12T19:07:59.000000+0100','Ora\\TaskManagement\\Task','00000000-0000-0000-0000-000000000004');

INSERT INTO event_stream (eventId, version, eventName, payload, occurredOn, aggregate_type, aggregate_id) VALUES
('6126d983-20ad-47f2-9636-085395aa3b40',1,'Ora\\TaskManagement\\TaskCreated','a:3:{s:6:\"status\";i:20;s:2:\"by\";s:36:\"60000000-0000-0000-0000-000000000000\";s:12:\"aggregate_id\";s:36:\"00000000-0000-0000-0000-000000000107\";}','2014-11-12T19:07:59.000000+0100','Ora\\TaskManagement\\Task','00000000-0000-0000-0000-000000000107');
INSERT INTO event_stream (eventId, version, eventName, payload, occurredOn, aggregate_type, aggregate_id) VALUES
('334fa91f-62c9-4b34-827b-3e01bd7efe50',2,'Ora\\TaskManagement\\TaskUpdated','a:3:{s:7:\"subject\";s:33:\"Setup the development environment\";s:2:\"by\";s:36:\"60000000-0000-0000-0000-000000000000\";s:12:\"aggregate_id\";s:36:\"00000000-0000-0000-0000-000000000107\";}','2014-11-12T19:07:59.000000+0100','Ora\\TaskManagement\\Task','00000000-0000-0000-0000-000000000107');
INSERT INTO event_stream (eventId, version, eventName, payload, occurredOn, aggregate_type, aggregate_id) VALUES
('6e943d08-7a9a-4a2a-a8b5-201bfeb57b63',3,'Ora\\TaskManagement\\StreamChanged','a:3:{s:8:\"streamId\";s:36:\"00000000-1000-0000-0000-000000000000\";s:2:\"by\";s:36:\"60000000-0000-0000-0000-000000000000\";s:12:\"aggregate_id\";s:36:\"00000000-0000-0000-0000-000000000107\";}','2014-11-12T19:07:59.000000+0100','Ora\\TaskManagement\\Task','00000000-0000-0000-0000-000000000107');
INSERT INTO event_stream (eventId, version, eventName, payload, occurredOn, aggregate_type, aggregate_id) VALUES
('c033eb32-2ad4-49d5-b25c-3c0b600b7de0',4,'Ora\\TaskManagement\\MemberAdded','a:4:{s:6:\"userId\";s:36:\"60000000-0000-0000-0000-000000000000\";s:4:\"role\";s:5:\"owner\";s:2:\"by\";s:36:\"60000000-0000-0000-0000-000000000000\";s:12:\"aggregate_id\";s:36:\"00000000-0000-0000-0000-000000000107\";}','2014-11-12T19:07:59.000000+0100','Ora\\TaskManagement\\Task','00000000-0000-0000-0000-000000000107');
INSERT INTO event_stream (eventId, version, eventName, payload, occurredOn, aggregate_type, aggregate_id) VALUES
('6126d983-20ad-47f2-9636-085395aa3b41',1,'Ora\\TaskManagement\\TaskCreated','a:3:{s:6:\"status\";i:20;s:2:\"by\";s:36:\"60000000-0000-0000-0000-000000000000\";s:12:\"aggregate_id\";s:36:\"00000000-0000-0000-0000-000000000108\";}','2014-11-12T19:07:59.000000+0100','Ora\\TaskManagement\\Task','00000000-0000-0000-0000-000000000108');
INSERT INTO event_stream (eventId, version, eventName, payload, occurredOn, aggregate_type, aggregate_id) VALUES
('334fa91f-62c9-4b34-827b-3e01bd7efe51',2,'Ora\\TaskManagement\\TaskUpdated','a:3:{s:7:\"subject\";s:33:\"Setup the development environment\";s:2:\"by\";s:36:\"60000000-0000-0000-0000-000000000000\";s:12:\"aggregate_id\";s:36:\"00000000-0000-0000-0000-000000000108\";}','2014-11-12T19:07:59.000000+0100','Ora\\TaskManagement\\Task','00000000-0000-0000-0000-000000000108');
INSERT INTO event_stream (eventId, version, eventName, payload, occurredOn, aggregate_type, aggregate_id) VALUES
('6e943d08-7a9a-4a2a-a8b5-201bfeb57b64',3,'Ora\\TaskManagement\\StreamChanged','a:3:{s:8:\"streamId\";s:36:\"00000000-1000-0000-0000-000000000000\";s:2:\"by\";s:36:\"60000000-0000-0000-0000-000000000000\";s:12:\"aggregate_id\";s:36:\"00000000-0000-0000-0000-000000000108\";}','2014-11-12T19:07:59.000000+0100','Ora\\TaskManagement\\Task','00000000-0000-0000-0000-000000000108');
INSERT INTO event_stream (eventId, version, eventName, payload, occurredOn, aggregate_type, aggregate_id) VALUES
('c033eb32-2ad4-49d5-b25c-3c0b600b7de1',4,'Ora\\TaskManagement\\MemberAdded','a:4:{s:6:\"userId\";s:36:\"60000000-0000-0000-0000-000000000000\";s:4:\"role\";s:5:\"owner\";s:2:\"by\";s:36:\"60000000-0000-0000-0000-000000000000\";s:12:\"aggregate_id\";s:36:\"00000000-0000-0000-0000-000000000108\";}','2014-11-12T19:07:59.000000+0100','Ora\\TaskManagement\\Task','00000000-0000-0000-0000-000000000108');
INSERT INTO event_stream (eventId, version, eventName, payload, occurredOn, aggregate_type, aggregate_id) VALUES
('6126d983-20ad-47f2-9636-085395aa3b42',1,'Ora\\TaskManagement\\TaskCreated','a:3:{s:6:\"status\";i:20;s:2:\"by\";s:36:\"60000000-0000-0000-0000-000000000000\";s:12:\"aggregate_id\";s:36:\"00000000-0000-0000-0000-000000000112\";}','2014-11-12T19:07:59.000000+0100','Ora\\TaskManagement\\Task','00000000-0000-0000-0000-000000000112');
INSERT INTO event_stream (eventId, version, eventName, payload, occurredOn, aggregate_type, aggregate_id) VALUES
('334fa91f-62c9-4b34-827b-3e01bd7efe52',2,'Ora\\TaskManagement\\TaskUpdated','a:3:{s:7:\"subject\";s:33:\"Setup the development environment\";s:2:\"by\";s:36:\"60000000-0000-0000-0000-000000000000\";s:12:\"aggregate_id\";s:36:\"00000000-0000-0000-0000-000000000112\";}','2014-11-12T19:07:59.000000+0100','Ora\\TaskManagement\\Task','00000000-0000-0000-0000-000000000112');
INSERT INTO event_stream (eventId, version, eventName, payload, occurredOn, aggregate_type, aggregate_id) VALUES
('6e943d08-7a9a-4a2a-a8b5-201bfeb57b65',3,'Ora\\TaskManagement\\StreamChanged','a:3:{s:8:\"streamId\";s:36:\"00000000-1000-0000-0000-000000000000\";s:2:\"by\";s:36:\"60000000-0000-0000-0000-000000000000\";s:12:\"aggregate_id\";s:36:\"00000000-0000-0000-0000-000000000112\";}','2014-11-12T19:07:59.000000+0100','Ora\\TaskManagement\\Task','00000000-0000-0000-0000-000000000112');
INSERT INTO event_stream (eventId, version, eventName, payload, occurredOn, aggregate_type, aggregate_id) VALUES
('c033eb32-2ad4-49d5-b25c-3c0b600b7de2',4,'Ora\\TaskManagement\\MemberAdded','a:4:{s:6:\"userId\";s:36:\"60000000-0000-0000-0000-000000000000\";s:4:\"role\";s:5:\"owner\";s:2:\"by\";s:36:\"60000000-0000-0000-0000-000000000000\";s:12:\"aggregate_id\";s:36:\"00000000-0000-0000-0000-000000000112\";}','2014-11-12T19:07:59.000000+0100','Ora\\TaskManagement\\Task','00000000-0000-0000-0000-000000000112');
INSERT INTO event_stream (eventId, version, eventName, payload, occurredOn, aggregate_id, aggregate_type) VALUES
('44c14de8-bd7f-4b1f-9b31-7bbd2c9f1740',1,'Ora\\TaskManagement\\TaskCreated','a:3:{s:6:\"status\";i:20;s:2:\"by\";s:36:\"60000000-0000-0000-0000-000000000000\";s:12:\"aggregate_id\";s:36:\"156eaec0-f997-4efe-94c9-b8c15da1f779\";}','2014-12-19T15:07:10.000000+0100','156eaec0-f997-4efe-94c9-b8c15da1f779','Ora\\TaskManagement\\Task'),
('4a57a016-737a-4935-9afc-78cfceea4a5f',4,'Ora\\TaskManagement\\MemberAdded','a:4:{s:6:\"userId\";s:36:\"60000000-0000-0000-0000-000000000000\";s:4:\"role\";s:5:\"owner\";s:2:\"by\";s:36:\"60000000-0000-0000-0000-000000000000\";s:12:\"aggregate_id\";s:36:\"156eaec0-f997-4efe-94c9-b8c15da1f779\";}','2014-12-19T15:07:10.000000+0100','156eaec0-f997-4efe-94c9-b8c15da1f779','Ora\\TaskManagement\\Task'),
('4d8d6441-6c49-4ffa-b353-f4f6335b0608',2,'Ora\\TaskManagement\\TaskUpdated','a:3:{s:7:\"subject\";s:4:\"est2\";s:2:\"by\";s:36:\"60000000-0000-0000-0000-000000000000\";s:12:\"aggregate_id\";s:36:\"2662e530-b58e-4dfc-9d0a-e140c2a62610\";}','2014-12-19T15:07:17.000000+0100','2662e530-b58e-4dfc-9d0a-e140c2a62610','Ora\\TaskManagement\\Task'),
('584b1748-9b4a-4971-b5bd-95f61f9415fb',1,'Ora\\TaskManagement\\TaskCreated','a:3:{s:6:\"status\";i:20;s:2:\"by\";s:36:\"60000000-0000-0000-0000-000000000000\";s:12:\"aggregate_id\";s:36:\"2662e530-b58e-4dfc-9d0a-e140c2a62610\";}','2014-12-19T15:07:17.000000+0100','2662e530-b58e-4dfc-9d0a-e140c2a62610','Ora\\TaskManagement\\Task'),
('5d4621fc-e543-4268-8f5a-8c30412b26fa',4,'Ora\\TaskManagement\\MemberAdded','a:4:{s:6:\"userId\";s:36:\"60000000-0000-0000-0000-000000000000\";s:4:\"role\";s:5:\"owner\";s:2:\"by\";s:36:\"60000000-0000-0000-0000-000000000000\";s:12:\"aggregate_id\";s:36:\"8924d278-4bb5-4f16-90d6-ee08aa639d88\";}','2014-12-19T15:07:24.000000+0100','8924d278-4bb5-4f16-90d6-ee08aa639d88','Ora\\TaskManagement\\Task'),
('8dfebe20-4018-41ed-8f73-2d22a88ec516',3,'Ora\\TaskManagement\\StreamChanged','a:3:{s:8:\"streamId\";s:36:\"00000000-1000-0000-0000-000000000000\";s:2:\"by\";s:36:\"60000000-0000-0000-0000-000000000000\";s:12:\"aggregate_id\";s:36:\"8924d278-4bb5-4f16-90d6-ee08aa639d88\";}','2014-12-19T15:07:24.000000+0100','8924d278-4bb5-4f16-90d6-ee08aa639d88','Ora\\TaskManagement\\Task'),
('a6b6e194-33f7-4186-bb7c-6edb0ff1f36e',1,'Ora\\TaskManagement\\TaskCreated','a:3:{s:6:\"status\";i:20;s:2:\"by\";s:36:\"60000000-0000-0000-0000-000000000000\";s:12:\"aggregate_id\";s:36:\"8924d278-4bb5-4f16-90d6-ee08aa639d88\";}','2014-12-19T15:07:24.000000+0100','8924d278-4bb5-4f16-90d6-ee08aa639d88','Ora\\TaskManagement\\Task'),
('a7ccb6ff-7c14-4bd5-88e0-a17d134cc5f8',2,'Ora\\TaskManagement\\TaskUpdated','a:3:{s:7:\"subject\";s:4:\"est3\";s:2:\"by\";s:36:\"60000000-0000-0000-0000-000000000000\";s:12:\"aggregate_id\";s:36:\"8924d278-4bb5-4f16-90d6-ee08aa639d88\";}','2014-12-19T15:07:24.000000+0100','8924d278-4bb5-4f16-90d6-ee08aa639d88','Ora\\TaskManagement\\Task'),
('aa83dcce-6390-4960-a0f7-d75da4e6681a',2,'Ora\\TaskManagement\\TaskUpdated','a:3:{s:7:\"subject\";s:4:\"est1\";s:2:\"by\";s:36:\"60000000-0000-0000-0000-000000000000\";s:12:\"aggregate_id\";s:36:\"156eaec0-f997-4efe-94c9-b8c15da1f779\";}','2014-12-19T15:07:10.000000+0100','156eaec0-f997-4efe-94c9-b8c15da1f779','Ora\\TaskManagement\\Task'),
('b23ffc6e-5566-446e-802f-574107860d5f',3,'Ora\\TaskManagement\\StreamChanged','a:3:{s:8:\"streamId\";s:36:\"00000000-1000-0000-0000-000000000000\";s:2:\"by\";s:36:\"60000000-0000-0000-0000-000000000000\";s:12:\"aggregate_id\";s:36:\"156eaec0-f997-4efe-94c9-b8c15da1f779\";}','2014-12-19T15:07:10.000000+0100','156eaec0-f997-4efe-94c9-b8c15da1f779','Ora\\TaskManagement\\Task'),
('b3686689-7410-4067-a4b6-4373c8cffafe',4,'Ora\\TaskManagement\\MemberAdded','a:4:{s:6:\"userId\";s:36:\"60000000-0000-0000-0000-000000000000\";s:4:\"role\";s:5:\"owner\";s:2:\"by\";s:36:\"60000000-0000-0000-0000-000000000000\";s:12:\"aggregate_id\";s:36:\"2662e530-b58e-4dfc-9d0a-e140c2a62610\";}','2014-12-19T15:07:17.000000+0100','2662e530-b58e-4dfc-9d0a-e140c2a62610','Ora\\TaskManagement\\Task'),
('fbcd63ed-5065-4e6d-9127-9fb5b8940a3e',3,'Ora\\TaskManagement\\StreamChanged','a:3:{s:8:\"streamId\";s:36:\"00000000-1000-0000-0000-000000000000\";s:2:\"by\";s:36:\"60000000-0000-0000-0000-000000000000\";s:12:\"aggregate_id\";s:36:\"2662e530-b58e-4dfc-9d0a-e140c2a62610\";}','2014-12-19T15:07:17.000000+0100','2662e530-b58e-4dfc-9d0a-e140c2a62610','Ora\\TaskManagement\\Task');


INSERT INTO event_stream (eventId, version, eventName, payload, occurredOn, aggregate_type, aggregate_id) VALUES
('44f1b89a-156b-4dbf-b5c9-9a0540460a0b',1,'Ora\\Accounting\\AccountCreated','a:3:{s:7:\"balance\";i:0;s:7:\"holders\";a:1:{s:36:\"60000000-0000-0000-0000-000000000000\";s:11:\"Mark Rogers\";}s:12:\"aggregate_id\";s:36:\"dcde992b-5aa9-4447-98ae-c8115906dcb7\";}','2014-12-29T17:32:07.000000+0100','Ora\\Accounting\\OrganizationAccount','dcde992b-5aa9-4447-98ae-c8115906dcb7');

INSERT INTO `organizations` (id, name, createdAt, mostRecentEditAt) VALUES ('00000000-0000-0000-1000-000000000000', 'O.R.A. Team','2014-11-06 13:11:05','2014-11-06 13:11:05');
insert into accounts(id, organization_id, createdAt, mostRecentEditAt, balance_value, balance_date, createdBy_id, mostRecentEditBy_id, type) values ('dcde992b-5aa9-4447-98ae-c8115906dcb7', '00000000-0000-0000-1000-000000000000', '2014-12-09 15:25:18', '2014-12-09 15:25:18', 0, '2014-12-09 15:25:18', '60000000-0000-0000-0000-000000000000', '60000000-0000-0000-0000-000000000000', 'organizationaccount');

INSERT INTO streams (id, subject, createdAt, mostRecentEditAt, organization_id) VALUES ('00000000-1000-0000-0000-000000000000', 'O.R.A.: Organization Resource Aggregator','2014-11-06 13:11:05','2014-11-06 13:11:05', '00000000-0000-0000-1000-000000000000');
INSERT INTO streams (id, subject, createdAt, mostRecentEditAt, organization_id) VALUES ('00000000-1100-0000-0000-000000000000', 'Open Goverance','2014-11-06 13:11:05','2014-11-06 13:11:05', '00000000-0000-0000-1000-000000000000');

INSERT INTO tasks (id, stream_id, subject, status, createdAt, mostRecentEditAt, type, boardId, taskId) VALUES
('00000000-0000-0000-0000-000000000101','00000000-1000-0000-0000-000000000000','BATMAN',0,'2014-11-06 13:11:05','2014-11-06 13:11:05','kanbanizetask',3,111);
INSERT INTO tasks (id, stream_id, subject, status, createdAt, mostRecentEditAt, type, boardId, taskId) VALUES
('00000000-0000-0000-0000-000000000102','00000000-1000-0000-0000-000000000000','JOKER',0,'2014-11-06 13:11:45','2014-11-06 13:11:45','kanbanizetask',3,112);
INSERT INTO tasks (id, stream_id, subject, status, createdAt, mostRecentEditAt, type, boardId, taskId) VALUES
('00000000-0000-0000-0000-000000000103','00000000-1000-0000-0000-000000000000','POISONIVY',0,'2014-11-06 13:12:14','2014-11-06 13:12:14','kanbanizetask',3,113);
INSERT INTO tasks (id, stream_id, subject, status, createdAt, mostRecentEditAt, type, boardId, taskId) VALUES
('00000000-0000-0000-0000-000000000104','00000000-1000-0000-0000-000000000000','wrongbatman',0,'2014-11-06 13:12:50','2014-11-06 13:12:50','kanbanizetask',4,1);
INSERT INTO tasks (id, stream_id, subject, status, createdAt, mostRecentEditAt, type, boardId, taskId) VALUES
('00000000-0000-0000-0000-000000000105','00000000-1000-0000-0000-000000000000','wrongbatmanagain',0,'2014-11-06 13:13:15','2014-11-06 13:13:15','kanbanizetask',3,69);
INSERT INTO tasks (id, stream_id, subject, status, createdAt, mostRecentEditAt, type, boardId, taskId) VALUES
('00000000-0000-0000-0000-000000000106','00000000-1000-0000-0000-000000000000','acceptedTask',40,'2014-11-06 14:32:44','2014-11-06 14:32:44','kanbanizetask',3,114);
INSERT INTO tasks (id, stream_id, subject, status, createdAt, mostRecentEditAt, type, boardId, taskId) VALUES
('00000000-0000-0000-0000-000000000107','00000000-1000-0000-0000-000000000000','completedTask',30,'2014-11-06 14:43:28','2014-11-06 14:43:28','kanbanizetask',3,115);
INSERT INTO tasks (id, stream_id, subject, status, createdAt, mostRecentEditAt, type, boardId, taskId) VALUES
('00000000-0000-0000-0000-000000000108','00000000-1000-0000-0000-000000000000','ongoingTask',20,'2014-11-06 14:51:12','2014-11-06 14:51:12','kanbanizetask',3,116);
INSERT INTO tasks (id, stream_id, subject, status, createdAt, mostRecentEditAt, type, boardId, taskId) VALUES
('00000000-0000-0000-0000-000000000109','00000000-1000-0000-0000-000000000000','completedTask',30,'2014-11-06 15:03:02','2014-11-06 15:03:02','kanbanizetask',3,117);
INSERT INTO tasks (id, stream_id, subject, status, createdAt, mostRecentEditAt, type, boardId, taskId) VALUES
('00000000-0000-0000-0000-000000000110','00000000-1000-0000-0000-000000000000','completedTask',30,'2014-11-06 15:39:13','2014-11-06 15:39:13','kanbanizetask',3,119);
INSERT INTO tasks (id, stream_id, subject, status, createdAt, mostRecentEditAt, type, boardId, taskId) VALUES
('00000000-0000-0000-0000-000000000111','00000000-1000-0000-0000-000000000000','completedTask',40,'2014-11-06 15:48:17','2014-11-06 15:48:17','kanbanizetask',3,120);
INSERT INTO tasks (id, stream_id, subject, status, createdAt, mostRecentEditAt, type, boardId, taskId) VALUES
('00000000-0000-0000-0000-000000000112','00000000-1000-0000-0000-000000000000','ongoingTask',20,'2014-11-06 15:56:56','2014-11-06 15:56:56','kanbanizetask',3,118);
INSERT INTO `tasks` (id, stream_id, subject, status, createdAt, mostRecentEditAt, createdBy_id, mostRecentEditBy_id, type) VALUES
('156eaec0-f997-4efe-94c9-b8c15da1f779','00000000-1000-0000-0000-000000000000','est1',20,'2014-12-19 15:07:10','2014-12-19 15:07:10','60000000-0000-0000-0000-000000000000','60000000-0000-0000-0000-000000000000','task'),
('2662e530-b58e-4dfc-9d0a-e140c2a62610','00000000-1000-0000-0000-000000000000','est2',20,'2014-12-19 15:07:17','2014-12-19 15:07:17','60000000-0000-0000-0000-000000000000','60000000-0000-0000-0000-000000000000','task'),
('8924d278-4bb5-4f16-90d6-ee08aa639d88','00000000-1000-0000-0000-000000000000','est3',20,'2014-12-19 15:07:24','2014-12-19 15:07:24','60000000-0000-0000-0000-000000000000','60000000-0000-0000-0000-000000000000','task'),
('00000000-0000-0000-0000-000000000000','00000000-1000-0000-0000-000000000000','Development environment setup',20,'2014-11-12 19:07:59','2014-11-12 19:07:59','60000000-0000-0000-0000-000000000000','60000000-0000-0000-0000-000000000000','task'),
('00000000-0000-0000-0000-000000000001','00000000-1000-0000-0000-000000000000','Continous integration setup',30,'2014-11-12 19:07:59','2014-11-12 19:07:59','60000000-0000-0000-0000-000000000000','60000000-0000-0000-0000-000000000000','task'),
('00000000-0000-0000-0000-000000000002','00000000-1000-0000-0000-000000000000','Technology stack definition',40,'2014-11-12 19:07:59','2014-11-12 19:07:59','60000000-0000-0000-0000-000000000000','60000000-0000-0000-0000-000000000000','task');


INSERT INTO estimations (id, value, createdAt) VALUES
('60000000-0000-0000-1111-000000000000','1500,00', '2014-11-07 11:37:58');
INSERT INTO estimations (id, value, createdAt) VALUES
('04a1c762-44dc-404c-ac33-3c0723a39c8e','1500,00', '2014-11-07 11:37:58');
INSERT INTO estimations (id, value, createdAt) VALUES
('04a1c762-44dc-404c-ac33-3c0723a39c8f','1500,00', '2014-11-07 11:37:58');
INSERT INTO estimations (id, value, createdAt) VALUES
('04a1c762-44dc-404c-ac33-3c0723a39c8h','-1', '2014-11-07 11:37:58');
INSERT INTO estimations (id, value, createdAt) VALUES
('05a1c762-44dc-404c-ac33-3c0723a39c8h','-1', '2014-11-07 11:37:58');


INSERT INTO tasks_members (task_id, member_id, estimation_id, role) VALUES
('00000000-0000-0000-0000-000000000107','20000000-0000-0000-0000-000000000000', '60000000-0000-0000-1111-000000000000', 'OWNER');
INSERT INTO tasks_members(task_id, member_id,role) VALUES('00000000-0000-0000-0000-000000000107','80000000-0000-0000-0000-000000000000','MEMBER');
INSERT INTO tasks_members(task_id, member_id,estimation_id,role) VALUES('00000000-0000-0000-0000-000000000108','90000000-0000-0000-0000-000000000000','04a1c762-44dc-404c-ac33-3c0723a39c8e','MEMBER');
INSERT INTO tasks_members(task_id, member_id,estimation_id,role) VALUES('00000000-0000-0000-0000-000000000108','80000000-0000-0000-0000-000000000000','04a1c762-44dc-404c-ac33-3c0723a39c8h','OWNER');
INSERT INTO tasks_members(task_id, member_id,estimation_id,role) VALUES('00000000-0000-0000-0000-000000000112','80000000-0000-0000-0000-000000000000',NULL,'OWNER');
INSERT INTO tasks_members(task_id, member_id,estimation_id,role) VALUES('00000000-0000-0000-0000-000000000104','20000000-0000-0000-0000-000000000000','05a1c762-44dc-404c-ac33-3c0723a39c8h','OWNER');
INSERT INTO tasks_members(task_id, member_id,estimation_id,role) VALUES('156eaec0-f997-4efe-94c9-b8c15da1f779','60000000-0000-0000-0000-000000000000',NULL,'owner'),
('2662e530-b58e-4dfc-9d0a-e140c2a62610','60000000-0000-0000-0000-000000000000',NULL,'owner'),
('8924d278-4bb5-4f16-90d6-ee08aa639d88','60000000-0000-0000-0000-000000000000',NULL,'owner'),
('00000000-0000-0000-0000-000000000000','60000000-0000-0000-0000-000000000000',NULL,'owner');

