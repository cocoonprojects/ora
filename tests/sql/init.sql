CREATE TABLE IF NOT EXISTS `event_stream` (
  `eventId`        VARCHAR(200)
                   COLLATE utf8_unicode_ci NOT NULL,
  `version`        INT(11)                 NOT NULL,
  `eventName`      TEXT
                   COLLATE utf8_unicode_ci NOT NULL,
  `payload`        TEXT
                   COLLATE utf8_unicode_ci NOT NULL,
  `occurredOn`     TEXT
                   COLLATE utf8_unicode_ci NOT NULL,
  `aggregate_id`   TEXT
                   COLLATE utf8_unicode_ci NOT NULL,
  `aggregate_type` TEXT
                   COLLATE utf8_unicode_ci NOT NULL,
  PRIMARY KEY (`eventId`)
)
  ENGINE = InnoDB
  DEFAULT CHARSET = utf8
  COLLATE = utf8_unicode_ci;

DELETE FROM task_members;
DELETE FROM tasks;
DELETE FROM streams;
DELETE FROM kanbanizetasks;
DELETE FROM kanbanizestreams;
DELETE FROM account_transactions;
DELETE FROM accounts;
DELETE FROM organizations;
DELETE FROM event_stream;
DELETE FROM users;


# user 60000000-0000-0000-0000-000000000000 Mark Rogers
INSERT INTO users (id, status, createdAt, mostRecentEditAt, firstname, lastname, email, role) VALUES
  ('60000000-0000-0000-0000-000000000000', 1, '2014-10-09 11:33:45', '2014-10-09 11:33:45', 'Mark', 'Rogers',
   'mark.rogers@ora.local', 'user');
# user 70000000-0000-0000-0000-000000000000 Phil Toledo
INSERT INTO users (id, status, createdAt, mostRecentEditAt, firstname, lastname, email, role) VALUES
  ('70000000-0000-0000-0000-000000000000', 1, '2014-10-09 11:33:45', '2014-10-09 11:33:45', 'Phil', 'Toledo',
   'phil.toledo@ora.local', 'user');

# organization 00000000-0000-0000-1000-000000000000
INSERT INTO event_stream (eventId, version, eventName, payload, occurredOn, aggregate_type, aggregate_id) VALUES
  ('907dd600-1e37-4e35-8045-7abfc5f60895', 1, 'People\\OrganizationCreated',
   'a:2:{s:2:"by";s:36:"60000000-0000-0000-0000-000000000000";s:12:"aggregate_id";s:36:"00000000-0000-0000-1000-000000000000";}',
   '2015-03-06T19:42:58.000000+0100', 'People\\Organization', '00000000-0000-0000-1000-000000000000'),
  ('dbc7ecb0-63b5-40c9-8ae4-09c06449a4ce', 2, 'People\\OrganizationUpdated',
   'a:3:{s:4:"name";s:11:"O.R.A. Team";s:2:"by";s:36:"60000000-0000-0000-0000-000000000000";s:12:"aggregate_id";s:36:"00000000-0000-0000-1000-000000000000";}',
   '2015-03-06T19:42:58.000000+0100', 'People\\Organization', '00000000-0000-0000-1000-000000000000'),
  ('74548e90-569c-4e1b-958f-5b644243210c', 3, 'People\\OrganizationMemberAdded',
   'a:4:{s:6:"userId";s:36:"60000000-0000-0000-0000-000000000000";s:4:"role";s:5:"admin";s:2:"by";s:36:"60000000-0000-0000-0000-000000000000";s:12:"aggregate_id";s:36:"00000000-0000-0000-1000-000000000000";}',
   '2015-03-11T13:35:32.000000+0100', 'People\\Organization', '00000000-0000-0000-1000-000000000000'),
  ('75548e90-569c-4e1b-958f-5b644243210c', 4, 'People\\OrganizationAccountChanged',
   'a:3:{s:9:"accountId";s:36:"dcde992b-5aa9-4447-98ae-c8115906dcb7";s:2:"by";s:36:"60000000-0000-0000-0000-000000000000";s:12:"aggregate_id";s:36:"00000000-0000-0000-1000-000000000000";}',
   '2015-03-11T13:35:32.000000+0100', 'People\\Organization', '00000000-0000-0000-1000-000000000000'),
('45f1b89a-156b-4dbf-b5c9-9a0540460a01', 5, 'People\\OrganizationUpdated',
   'a:4:{s:3:"key";s:15:"kanbanizeApiKey";s:5:"value";s:40:"aaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaa";s:2:"by";s:36:"7f3d6cd8-8fc1-4d88-8253-f95e7df25746";s:12:"aggregate_id";s:36:"00000000-0000-0000-1000-000000000000";}',
   '2014-12-29T17:32:07.000000+0100', 'People\\Organization', '00000000-0000-0000-1000-000000000000');
INSERT INTO `organizations` (id, name, createdAt, mostRecentEditAt, createdBy_id, mostRecentEditBy_id) VALUES
  ('00000000-0000-0000-1000-000000000000', 'O.R.A. Team', '2014-11-06 13:11:05', '2014-11-06 13:11:05',
   '60000000-0000-0000-0000-000000000000', '60000000-0000-0000-0000-000000000000');
INSERT INTO organization_members (member_id, organization_id, role, createdAt, mostRecentEditAt, createdBy_id, mostRecentEditBy_id)
VALUES
  ('60000000-0000-0000-0000-000000000000', '00000000-0000-0000-1000-000000000000', 'admin', '2014-10-09 11:33:45',
   '2014-10-09 11:33:45', '60000000-0000-0000-0000-000000000000', '60000000-0000-0000-0000-000000000000');

# organization account
INSERT INTO event_stream (eventId, version, eventName, payload, occurredOn, aggregate_type, aggregate_id) VALUES
  ('44f1b89a-156b-4dbf-b5c9-9a0540460a0b', 1, 'Accounting\\AccountCreated',
   'a:4:{s:7:\"balance\";i:0;s:12:"organization";s:36:"00000000-0000-0000-1000-000000000000";s:2:"by";s:36:"60000000-0000-0000-0000-000000000000";s:12:\"aggregate_id\";s:36:\"dcde992b-5aa9-4447-98ae-c8115906dcb7\";}',
   '2014-12-29T17:32:07.000000+0100', 'Accounting\\OrganizationAccount', 'dcde992b-5aa9-4447-98ae-c8115906dcb7'),
  ('0b3f93d2-811f-4a54-a72c-dedcce600a4d', 2, 'Accounting\\HolderAdded',
   'a:5:{s:2:"id";s:36:"60000000-0000-0000-0000-000000000000";s:9:"firstname";s:4:"Mark";s:8:"lastname";s:6:"Rogers";s:2:"by";s:36:"60000000-0000-0000-0000-000000000000";s:12:"aggregate_id";s:36:"dcde992b-5aa9-4447-98ae-c8115906dcb7";}',
   '2015-03-09T17:34:52.000000+0100', 'Accounting\\OrganizationAccount', 'dcde992b-5aa9-4447-98ae-c8115906dcb7');
INSERT INTO accounts (id, organization_id, createdAt, mostRecentEditAt, balance_value, balance_date, createdBy_id, mostRecentEditBy_id, type)
VALUES
  ('dcde992b-5aa9-4447-98ae-c8115906dcb7', '00000000-0000-0000-1000-000000000000', '2014-12-09 15:25:18',
   '2014-12-09 15:25:18', 0, '2014-12-09 15:25:18', '60000000-0000-0000-0000-000000000000',
   '60000000-0000-0000-0000-000000000000', 'organizationaccount');

# personal account
INSERT INTO event_stream (eventId, version, eventName, payload, occurredOn, aggregate_type, aggregate_id) VALUES
  ('45f1b89a-156b-4dbf-b5c9-9a0540460a0b', 1, 'Accounting\\AccountCreated',
   'a:4:{s:12:\"organization\";s:36:\"00000000-0000-0000-1000-000000000000\";s:7:\"balance\";i:0;s:7:\"holders\";a:1:{s:36:\"60000000-0000-0000-0000-000000000000\";s:11:\"Mark Rogers\";}s:12:\"aggregate_id\";s:36:\"ccde992b-5aa9-4447-98ae-c8115906dcb7\";}',
   '2014-12-29T17:32:07.000000+0100', 'Accounting\\Account', 'ccde992b-5aa9-4447-98ae-c8115906dcb7');
INSERT INTO accounts (id, organization_id, createdAt, mostRecentEditAt, balance_value, balance_date, createdBy_id, mostRecentEditBy_id, type)
VALUES
  ('ccde992b-5aa9-4447-98ae-c8115906dcb7', '00000000-0000-0000-1000-000000000000', '2014-12-29T17:32:07.000000+0100',
   '2014-12-29T17:32:07.000000+0100', 1000, '2014-12-29T17:32:07.000000+0100', '60000000-0000-0000-0000-000000000000',
   '60000000-0000-0000-0000-000000000000', 'personalaccount');
INSERT INTO account_holders (account_id, user_id) VALUES
  ('ccde992b-5aa9-4447-98ae-c8115906dcb7', '60000000-0000-0000-0000-000000000000');

INSERT INTO event_stream (eventId, version, eventName, payload, occurredOn, aggregate_type, aggregate_id) VALUES
  ('74548e90-569c-4e1b-958f-5b744243210c', 5, 'People\\OrganizationMemberAdded',
   'a:4:{s:6:"userId";s:36:"70000000-0000-0000-0000-000000000000";s:4:"role";s:6:"member";s:2:"by";s:36:"70000000-0000-0000-0000-000000000000";s:12:"aggregate_id";s:36:"00000000-0000-0000-1000-000000000000";}',
   '2015-03-11T13:35:32.000000+0100', 'People\\Organization', '00000000-0000-0000-1000-000000000000'),
  ('45f1b89b-156b-4dbf-b5c9-9a0540460a0b', 1, 'Accounting\\AccountCreated',
   'a:4:{s:12:\"organization\";s:36:\"00000000-0000-0000-1000-000000000000\";s:7:\"balance\";i:0;s:7:\"holders\";a:1:{s:36:\"70000000-0000-0000-0000-000000000000\";s:11:\"Phil Toledo\";}s:12:\"aggregate_id\";s:36:\"cdde992b-5aa9-4447-98ae-c8115906dcb7\";}',
   '2014-12-29T17:32:07.000000+0100', 'Accounting\\Account', 'cdde992b-5aa9-4447-98ae-c8115906dcb7');
INSERT INTO organization_members (member_id, organization_id, role, createdAt, mostRecentEditAt, createdBy_id, mostRecentEditBy_id)
VALUES
  ('70000000-0000-0000-0000-000000000000', '00000000-0000-0000-1000-000000000000', 'member', '2014-10-09 11:33:45',
   '2014-10-09 11:33:45', '70000000-0000-0000-0000-000000000000', '70000000-0000-0000-0000-000000000000');
INSERT INTO accounts (id, organization_id, createdAt, mostRecentEditAt, balance_value, balance_date, createdBy_id, mostRecentEditBy_id, type)
VALUES
  ('cdde992b-5aa9-4447-98ae-c8115906dcb7', '00000000-0000-0000-1000-000000000000', '2014-12-29T17:32:07.000000+0100',
   '2014-12-29T17:32:07.000000+0100', 0, '2014-12-29T17:32:07.000000+0100', '70000000-0000-0000-0000-000000000000',
   '70000000-0000-0000-0000-000000000000', 'personalaccount');
INSERT INTO account_holders (account_id, user_id) VALUES
  ('cdde992b-5aa9-4447-98ae-c8115906dcb7', '70000000-0000-0000-0000-000000000000');

#Bruce Wayne Personal Account  
INSERT INTO users (id, status, createdAt, mostRecentEditAt, firstname, lastname, email, role) VALUES
  ('80000000-0000-0000-0000-000000000000', 1, '2014-10-09 11:33:45', '2014-10-09 11:33:45', 'Bruce', 'Wayne',
   'bruce.wayne@ora.local', 'user');  
INSERT INTO event_stream (eventId, version, eventName, payload, occurredOn, aggregate_type, aggregate_id) VALUES
  ('74548e90-569c-4e1b-958f-5b744243210f', 5, 'People\\OrganizationMemberAdded',
   'a:4:{s:6:"userId";s:36:"80000000-0000-0000-0000-000000000000";s:4:"role";s:6:"member";s:2:"by";s:36:"80000000-0000-0000-0000-000000000000";s:12:"aggregate_id";s:36:"00000000-0000-0000-1000-000000000000";}',
   '2015-03-11T13:35:32.000000+0100', 'People\\Organization', '00000000-0000-0000-1000-000000000000'),
  ('45f1b89b-156b-4dbf-b5c9-9a0540460a0c', 1, 'Accounting\\AccountCreated',
   'a:4:{s:12:\"organization\";s:36:\"00000000-0000-0000-1000-000000000000\";s:7:\"balance\";i:0;s:7:\"holders\";a:1:{s:36:\"80000000-0000-0000-0000-000000000000\";s:11:\"Bruce Wayne\";}s:12:\"aggregate_id\";s:36:\"cdde992b-5aa9-4447-98ae-c8115906dcb9\";}',
   '2014-12-29T17:32:07.000000+0100', 'Accounting\\Account', 'cdde992b-5aa9-4447-98ae-c8115906dcb9');
INSERT INTO organization_members (member_id, organization_id, role, createdAt, mostRecentEditAt, createdBy_id, mostRecentEditBy_id)
VALUES
  ('80000000-0000-0000-0000-000000000000', '00000000-0000-0000-1000-000000000000', 'member', '2014-10-09 11:33:45',
   '2014-10-09 11:33:45', '80000000-0000-0000-0000-000000000000', '80000000-0000-0000-0000-000000000000');
INSERT INTO accounts (id, organization_id, createdAt, mostRecentEditAt, balance_value, balance_date, createdBy_id, mostRecentEditBy_id, type)
VALUES
  ('cdde992b-5aa9-4447-98ae-c8115906dcb9', '00000000-0000-0000-1000-000000000000', '2014-12-29T17:32:07.000000+0100',
   '2014-12-29T17:32:07.000000+0100', 1000, '2014-12-29T17:32:07.000000+0100', '80000000-0000-0000-0000-000000000000',
   '80000000-0000-0000-0000-000000000000', 'personalaccount');
INSERT INTO account_holders (account_id, user_id) VALUES
  ('cdde992b-5aa9-4447-98ae-c8115906dcb9', '80000000-0000-0000-0000-000000000000');


INSERT INTO users (id, status, createdAt, mostRecentEditAt, firstname, lastname, email, role) VALUES
  ('20000000-0000-0000-0000-000000000000', 1, '2014-10-09 11:33:45', '2014-10-09 11:33:45', 'Paul', 'Smith',
   'paul.smith@ora.local', 'user');
INSERT INTO users (id, status, createdAt, mostRecentEditAt, firstname, lastname, email, role) VALUES
  ('90000000-0000-0000-0000-000000000000', 1, '2014-10-09 11:33:45', '2014-10-09 11:33:45', 'Peter', 'Parker',
   'spidey.web@dailybugle.local', 'user');

# streams 00000000-1000-0000-0000-000000000000
INSERT INTO event_stream (eventId, version, eventName, payload, occurredOn, aggregate_type, aggregate_id) VALUES
  ('fac7de07-0580-421c-94ca-21842f676a33', 1, 'TaskManagement\\StreamCreated',
   'a:3:{s:14:"organizationId";s:36:"00000000-0000-0000-1000-000000000000";s:2:"by";s:36:"60000000-0000-0000-0000-000000000000";s:12:"aggregate_id";s:36:"00000000-1000-0000-0000-000000000000";}',
   '2015-03-11T01:43:03.000000+0100', 'TaskManagement\\Stream', '00000000-1000-0000-0000-000000000000'),
  ('4a0385c4-780b-46b2-b8be-7fd0118be87d', 2, 'TaskManagement\\StreamUpdated',
   'a:3:{s:7:"subject";N;s:2:"by";s:36:"60000000-0000-0000-0000-000000000000";s:12:"aggregate_id";s:36:"00000000-1000-0000-0000-000000000000";}',
   '2015-03-11T01:43:03.000000+0100', 'TaskManagement\\Stream', '00000000-1000-0000-0000-000000000000'),
  ('6e943d09-7a9a-4a2a-a8b5-201bfeb57b63', 3, 'TaskManagement\\StreamOrganizationChanged',
   'a:3:{s:14:\"organizationId\";s:36:\"00000000-0000-0000-1000-000000000000\";s:2:\"by\";s:36:\"60000000-0000-0000-0000-000000000000\";s:12:\"aggregate_id\";s:36:\"00000000-1000-0000-0000-000000000000\";}',
   '2014-11-12T19:07:59.000000+0100', 'TaskManagement\\Stream', '00000000-1000-0000-0000-000000000000');
INSERT INTO streams (id, subject, createdAt, mostRecentEditAt, organization_id, createdBy_id, mostRecentEditBy_id, type)
VALUES ('00000000-1000-0000-0000-000000000000', 'O.R.A.: Organization Resource Aggregator', '2014-11-06 13:11:05',
        '2014-11-06 13:11:05', '00000000-0000-0000-1000-000000000000', '60000000-0000-0000-0000-000000000000',
        '60000000-0000-0000-0000-000000000000', 'stream');
INSERT INTO streams (id, subject, createdAt, mostRecentEditAt, organization_id, createdBy_id, mostRecentEditBy_id, type)
VALUES ('00000000-1100-0000-0000-000000000000', 'Open Goverance', '2014-11-06 13:11:05', '2014-11-06 13:11:05',
        '00000000-0000-0000-1000-000000000000', '60000000-0000-0000-0000-000000000000',
        '60000000-0000-0000-0000-000000000000', 'stream');

# task 00000000-0000-0000-0000-000000000000, ongoing, Mark Rogers (owner), Paul Smith (member)
INSERT INTO event_stream (eventId, version, eventName, payload, occurredOn, aggregate_type, aggregate_id) VALUES
  ('6126d983-20ad-47f2-9636-085395aa3b4b', 1, 'TaskManagement\\TaskCreated',
   'a:5:{s:8:\"streamId\";s:36:\"00000000-1000-0000-0000-000000000000\";s:14:\"organizationId\";s:36:\"00000000-0000-0000-1000-000000000000\";s:6:\"status\";i:20;s:2:\"by\";s:36:\"60000000-0000-0000-0000-000000000000\";s:12:\"aggregate_id\";s:36:\"00000000-0000-0000-0000-000000000000\";}',
   '2014-01-12T19:07:59.000000+0100', 'TaskManagement\\Task', '00000000-0000-0000-0000-000000000000'),
  ('334fa91f-62c9-4b34-827b-3e01bd7efe5c', 2, 'TaskManagement\\TaskUpdated',
   'a:3:{s:7:\"subject\";s:29:\"Development environment setup\";s:2:\"by\";s:36:\"60000000-0000-0000-0000-000000000000\";s:12:\"aggregate_id\";s:36:\"00000000-0000-0000-0000-000000000000\";}',
   '2014-01-12T19:07:59.000000+0100', 'TaskManagement\\Task', '00000000-0000-0000-0000-000000000000'),
  ('c033eb32-2ad4-49d5-b25c-3c0b600b7dec', 4, 'TaskManagement\\TaskMemberAdded',
   'a:4:{s:6:\"userId\";s:36:\"60000000-0000-0000-0000-000000000000\";s:4:\"role\";s:5:\"owner\";s:2:\"by\";s:36:\"60000000-0000-0000-0000-000000000000\";s:12:\"aggregate_id\";s:36:\"00000000-0000-0000-0000-000000000000\";}',
   '2014-01-12T19:07:59.000000+0100', 'TaskManagement\\Task', '00000000-0000-0000-0000-000000000000'),
  ('c133eb32-2ad4-49d5-b25c-3c0b600b7dec', 5, 'TaskManagement\\TaskMemberAdded',
   'a:4:{s:6:\"userId\";s:36:\"20000000-0000-0000-0000-000000000000\";s:4:\"role\";s:6:\"member\";s:2:\"by\";s:36:\"20000000-0000-0000-0000-000000000000\";s:12:\"aggregate_id\";s:36:\"00000000-0000-0000-0000-000000000000\";}',
   '2014-11-12T19:07:59.000000+0100', 'TaskManagement\\Task', '00000000-0000-0000-0000-000000000000');
INSERT INTO `tasks` (id, stream_id, subject, status, createdAt, mostRecentEditAt, createdBy_id, mostRecentEditBy_id, type)
VALUES
  ('00000000-0000-0000-0000-000000000000', '00000000-1000-0000-0000-000000000000', 'Development environment setup', 20,
   '2014-01-12 19:07:59', '2014-11-12 19:07:59', '60000000-0000-0000-0000-000000000000',
   '60000000-0000-0000-0000-000000000000', 'task');
INSERT INTO task_members (task_id, member_id, role, createdAt, mostRecentEditAt) VALUES
  ('00000000-0000-0000-0000-000000000000', '60000000-0000-0000-0000-000000000000', 'owner', '2014-11-12 19:07:59',
   '2014-01-12 19:07:59'),
  ('00000000-0000-0000-0000-000000000000', '20000000-0000-0000-0000-000000000000', 'member', '2014-11-12 19:07:59',
   '2014-11-12 19:07:59');

# task 00000000-0000-0000-0000-000000000001, completed, Mark Rogers (owner), Paul Smith (member)
INSERT INTO event_stream (eventId, version, eventName, payload, occurredOn, aggregate_type, aggregate_id) VALUES
  ('6126d983-20ad-47f2-9636-085395aa3b5b', 1, 'TaskManagement\\TaskCreated',
   'a:5:{s:8:\"streamId\";s:36:\"00000000-1000-0000-0000-000000000000\";s:14:\"organizationId\";s:36:\"00000000-0000-0000-1000-000000000000\";s:6:\"status\";i:20;s:2:\"by\";s:36:\"60000000-0000-0000-0000-000000000000\";s:12:\"aggregate_id\";s:36:\"00000000-0000-0000-0000-000000000001\";}',
   '2014-01-31T19:07:59.000000+0100', 'TaskManagement\\Task', '00000000-0000-0000-0000-000000000001'),
  ('334fa91f-62c9-4b34-827b-3e01bd7efe6c', 2, 'TaskManagement\\TaskUpdated',
   'a:3:{s:7:\"subject\";s:27:\"Continous integration setup\";s:2:\"by\";s:36:\"60000000-0000-0000-0000-000000000000\";s:12:\"aggregate_id\";s:36:\"00000000-0000-0000-0000-000000000001\";}',
   '2014-01-31T19:07:59.000000+0100', 'TaskManagement\\Task', '00000000-0000-0000-0000-000000000001'),
  ('c033eb32-2ad4-49d5-b25c-3c0b600b7dfc', 4, 'TaskManagement\\TaskMemberAdded',
   'a:4:{s:6:\"userId\";s:36:\"60000000-0000-0000-0000-000000000000\";s:4:\"role\";s:5:\"owner\";s:2:\"by\";s:36:\"60000000-0000-0000-0000-000000000000\";s:12:\"aggregate_id\";s:36:\"00000000-0000-0000-0000-000000000001\";}',
   '2014-01-31T19:07:59.000000+0100', 'TaskManagement\\Task', '00000000-0000-0000-0000-000000000001'),
  ('c233eb32-2ad4-49d5-b25c-3c0b600b7dec', 5, 'TaskManagement\\TaskMemberAdded',
   'a:4:{s:6:\"userId\";s:36:\"20000000-0000-0000-0000-000000000000\";s:4:\"role\";s:6:\"member\";s:2:\"by\";s:36:\"20000000-0000-0000-0000-000000000000\";s:12:\"aggregate_id\";s:36:\"00000000-0000-0000-0000-000000000001\";}',
   '2014-01-31T19:07:59.000000+0100', 'TaskManagement\\Task', '00000000-0000-0000-0000-000000000001'),
  ('fbdfdd17-61ef-4f80-bcd4-7e6eb6990713', 6, 'TaskManagement\\TaskCompleted',
   'a:2:{s:12:\"aggregate_id\";s:36:\"00000000-0000-0000-0000-000000000001\";s:2:\"by\";s:36:\"60000000-0000-0000-0000-000000000000\";}',
   '2014-10-31T10:44:30.000000+0100', 'TaskManagement\\Task', '00000000-0000-0000-0000-000000000001');
INSERT INTO `tasks` (id, stream_id, subject, status, createdAt, mostRecentEditAt, createdBy_id, mostRecentEditBy_id, type)
VALUES
  ('00000000-0000-0000-0000-000000000001', '00000000-1000-0000-0000-000000000000', 'Continous integration setup', 30,
   '2014-01-31 19:07:59', '2014-11-12 19:07:59', '60000000-0000-0000-0000-000000000000',
   '60000000-0000-0000-0000-000000000000', 'task');
INSERT INTO task_members (task_id, member_id, role, createdAt, mostRecentEditAt) VALUES
  ('00000000-0000-0000-0000-000000000001', '60000000-0000-0000-0000-000000000000', 'owner', '2014-11-12 19:07:59',
   '2014-01-31 19:07:59'),
  ('00000000-0000-0000-0000-000000000001', '20000000-0000-0000-0000-000000000000', 'member', '2014-11-12 19:07:59',
   '2014-11-12 19:07:59');

# task 00000000-0000-0000-0000-000000000002, accepted, Mark Rogers (owner), Paul Smith (member)
INSERT INTO event_stream (eventId, version, eventName, payload, occurredOn, aggregate_type, aggregate_id) VALUES
  ('6126d983-20ad-47f2-9636-085395aa3b6b', 1, 'TaskManagement\\TaskCreated',
   'a:5:{s:8:\"streamId\";s:36:\"00000000-1000-0000-0000-000000000000\";s:14:\"organizationId\";s:36:\"00000000-0000-0000-1000-000000000000\";s:6:\"status\";i:20;s:2:\"by\";s:36:\"60000000-0000-0000-0000-000000000000\";s:12:\"aggregate_id\";s:36:\"00000000-0000-0000-0000-000000000002\";}',
   '2014-02-07T19:07:59.000000+0100', 'TaskManagement\\Task', '00000000-0000-0000-0000-000000000002'),
  ('334fa91f-62c9-4b34-827b-3e01bd7efe7c', 2, 'TaskManagement\\TaskUpdated',
   'a:3:{s:7:\"subject\";s:27:\"Technology stack definition\";s:2:\"by\";s:36:\"60000000-0000-0000-0000-000000000000\";s:12:\"aggregate_id\";s:36:\"00000000-0000-0000-0000-000000000002\";}',
   '2014-02-07T19:07:59.000000+0100', 'TaskManagement\\Task', '00000000-0000-0000-0000-000000000002'),
  ('c033eb32-2ad4-49d5-b25c-3c0b600b7d9c', 4, 'TaskManagement\\TaskMemberAdded',
   'a:4:{s:6:\"userId\";s:36:\"60000000-0000-0000-0000-000000000000\";s:4:\"role\";s:5:\"owner\";s:2:\"by\";s:36:\"60000000-0000-0000-0000-000000000000\";s:12:\"aggregate_id\";s:36:\"00000000-0000-0000-0000-000000000002\";}',
   '2014-02-07T19:07:59.000000+0100', 'TaskManagement\\Task', '00000000-0000-0000-0000-000000000002'),
  ('fbdfdd17-61ef-4f80-bcd4-7e6eb6990723', 5, 'TaskManagement\\TaskCompleted',
   'a:2:{s:12:\"aggregate_id\";s:36:\"00000000-0000-0000-0000-000000000002\";s:2:\"by\";s:36:\"60000000-0000-0000-0000-000000000000\";}',
   '2014-10-31T10:44:30.000000+0100', 'TaskManagement\\Task', '00000000-0000-0000-0000-000000000002'),
  ('c333eb32-2ad4-49d5-b25c-3c0b600b7dec', 6, 'TaskManagement\\TaskMemberAdded',
   'a:4:{s:6:\"userId\";s:36:\"20000000-0000-0000-0000-000000000000\";s:4:\"role\";s:6:\"member\";s:2:\"by\";s:36:\"20000000-0000-0000-0000-000000000000\";s:12:\"aggregate_id\";s:36:\"00000000-0000-0000-0000-000000000002\";}',
   '2014-11-12T19:07:59.000000+0100', 'TaskManagement\\Task', '00000000-0000-0000-0000-000000000002'),
  ('65697b0b-0790-48b7-a122-ff2078c5bf20', 7, 'TaskManagement\\EstimationAdded',
   'a:3:{s:2:"by";s:36:"60000000-0000-0000-0000-000000000000";s:5:"value";s:2:"-1";s:12:"aggregate_id";s:36:"00000000-0000-0000-0000-000000000002";}',
   '2015-02-02T21:22:11.000000+0100', 'TaskManagement\\Task', '00000000-0000-0000-0000-000000000002'),
  ('66697b0b-0790-48b7-a122-ff2078c5bf20', 8, 'TaskManagement\\EstimationAdded',
   'a:3:{s:2:"by";s:36:"20000000-0000-0000-0000-000000000000";s:5:"value";s:2:"-1";s:12:"aggregate_id";s:36:"00000000-0000-0000-0000-000000000002";}',
   '2015-02-02T21:22:11.000000+0100', 'TaskManagement\\Task', '00000000-0000-0000-0000-000000000002'),
  ('fbdfdd17-61ef-4f80-bcd4-7e6eb6990714', 9, 'TaskManagement\\TaskAccepted',
   'a:2:{s:12:\"aggregate_id\";s:36:\"00000000-0000-0000-0000-000000000002\";s:2:\"by\";s:36:\"60000000-0000-0000-0000-000000000000\";}',
   '2014-10-31T10:44:30.000000+0100', 'TaskManagement\\Task', '00000000-0000-0000-0000-000000000002');
INSERT INTO `tasks` (id, stream_id, subject, status, createdAt, mostRecentEditAt, createdBy_id, mostRecentEditBy_id, type)
VALUES
  ('00000000-0000-0000-0000-000000000002', '00000000-1000-0000-0000-000000000000', 'Technology stack definition', 40,
   '2014-02-07 19:07:59', '2014-11-12 19:07:59', '60000000-0000-0000-0000-000000000000',
   '60000000-0000-0000-0000-000000000000', 'task');
INSERT INTO task_members (task_id, member_id, role, createdAt, mostRecentEditAt, estimation_value, estimation_createdAt)
VALUES
  ('00000000-0000-0000-0000-000000000002', '60000000-0000-0000-0000-000000000000', 'owner', '2014-11-12 19:07:59',
   '2014-02-07 19:07:59', '-1', '2014-11-07 11:37:58'),
  ('00000000-0000-0000-0000-000000000002', '20000000-0000-0000-0000-000000000000', 'member', '2014-11-12 19:07:59',
   '2014-11-12 19:07:59', '-1', '2014-11-07 11:37:58');

# task 00000000-0000-0000-0000-000000000003, deleted, Mark Rogers (owner)
INSERT INTO event_stream (eventId, version, eventName, payload, occurredOn, aggregate_type, aggregate_id) VALUES
  ('6126d983-20ad-47f2-9636-085395aa307b', 1, 'TaskManagement\\TaskCreated',
   'a:5:{s:8:\"streamId\";s:36:\"00000000-1000-0000-0000-000000000000\";s:14:\"organizationId\";s:36:\"00000000-0000-0000-1000-000000000000\";s:6:\"status\";i:20;s:2:\"by\";s:36:\"60000000-0000-0000-0000-000000000000\";s:12:\"aggregate_id\";s:36:\"00000000-0000-0000-0000-000000000003\";}',
   '2014-03-23T19:07:59.000000+0100', 'TaskManagement\\Task', '00000000-0000-0000-0000-000000000003'),
  ('334fa91f-62c9-4b34-827b-3e01bd7eee8c', 2, 'TaskManagement\\TaskUpdated',
   'a:3:{s:7:\"subject\";s:33:\"Setup the development environment\";s:2:\"by\";s:36:\"60000000-0000-0000-0000-000000000000\";s:12:\"aggregate_id\";s:36:\"00000000-0000-0000-0000-000000000003\";}',
   '2014-03-23T19:07:59.000000+0100', 'TaskManagement\\Task', '00000000-0000-0000-0000-000000000003'),
  ('c033eb32-2ad4-49d5-b25c-3c0b600b7d10', 4, 'TaskManagement\\TaskMemberAdded',
   'a:4:{s:6:\"userId\";s:36:\"60000000-0000-0000-0000-000000000000\";s:4:\"role\";s:5:\"owner\";s:2:\"by\";s:36:\"60000000-0000-0000-0000-000000000000\";s:12:\"aggregate_id\";s:36:\"00000000-0000-0000-0000-000000000003\";}',
   '2014-03-23T19:07:59.000000+0100', 'TaskManagement\\Task', '00000000-0000-0000-0000-000000000003'),
  ('6b73772b-efa9-475d-b3d8-abbcf3a84889', 5, 'TaskManagement\\TaskDeleted',
   'a:3:{s:10:\"prevStatus\";i:20;s:2:\"by\";s:36:\"266616a1-3160-4caa-ae56-35f884324a5a\";s:12:\"aggregate_id\";s:36:\"00000000-0000-0000-0000-000000000003\";}',
   '2014-12-31T09:33:15.000000+0100', 'TaskManagement\\Task', '00000000-0000-0000-0000-000000000003');

# task 00000000-0000-0000-0000-000000000101, accepted, Mark Rogers (owner)
INSERT INTO event_stream (eventId, version, eventName, payload, occurredOn, aggregate_type, aggregate_id) VALUES
 ('6126d983-20ad-47f2-9636-085395aa3b7b', 1, 'TaskManagement\\TaskCreated',
   'a:5:{s:8:\"streamId\";s:36:\"00000000-1000-0000-0000-000000000000\";s:14:\"organizationId\";s:36:\"00000000-0000-0000-1000-000000000000\";s:6:\"status\";i:20;s:2:\"by\";s:36:\"60000000-0000-0000-0000-000000000000\";s:12:\"aggregate_id\";s:36:\"00000000-0000-0000-0000-000000000101\";}',
   '2014-02-07T19:07:59.000000+0100', 'TaskManagement\\Task', '00000000-0000-0000-0000-000000000101'),
  ('334fa91f-62c9-4b34-827b-3e01bd7efe8c', 2, 'TaskManagement\\TaskUpdated',
   'a:3:{s:7:\"subject\";s:27:\"Technology stack definition\";s:2:\"by\";s:36:\"60000000-0000-0000-0000-000000000000\";s:12:\"aggregate_id\";s:36:\"00000000-0000-0000-0000-000000000101\";}',
   '2014-02-07T19:07:59.000000+0100', 'TaskManagement\\Task', '00000000-0000-0000-0000-000000000101'),
  ('c033eb32-2ad4-49d5-b25c-3c0b600b7d0c', 4, 'TaskManagement\\TaskMemberAdded',
   'a:4:{s:6:\"userId\";s:36:\"60000000-0000-0000-0000-000000000000\";s:4:\"role\";s:5:\"owner\";s:2:\"by\";s:36:\"60000000-0000-0000-0000-000000000000\";s:12:\"aggregate_id\";s:36:\"00000000-0000-0000-0000-000000000101\";}',
   '2014-02-07T19:07:59.000000+0100', 'TaskManagement\\Task', '00000000-0000-0000-0000-000000000101'),
  ('fbdfdd17-61ef-4f80-bcd4-7e6eb6990733', 5, 'TaskManagement\\TaskCompleted',
   'a:2:{s:12:\"aggregate_id\";s:36:\"00000000-0000-0000-0000-000000000101\";s:2:\"by\";s:36:\"60000000-0000-0000-0000-000000000000\";}',
   '2014-10-31T10:44:30.000000+0100', 'TaskManagement\\Task', '00000000-0000-0000-0000-000000000101'),
  ('c333eb32-2ad4-49d5-b25c-3c0b600b7ddc', 6, 'TaskManagement\\TaskMemberAdded',
   'a:4:{s:6:\"userId\";s:36:\"20000000-0000-0000-0000-000000000000\";s:4:\"role\";s:6:\"member\";s:2:\"by\";s:36:\"20000000-0000-0000-0000-000000000000\";s:12:\"aggregate_id\";s:36:\"00000000-0000-0000-0000-000000000101\";}',
   '2014-11-12T19:07:59.000000+0100', 'TaskManagement\\Task', '00000000-0000-0000-0000-000000000101'),
  ('65697b0b-0790-48b7-a122-ff2078c5bf40', 7, 'TaskManagement\\EstimationAdded',
   'a:3:{s:2:"by";s:36:"60000000-0000-0000-0000-000000000000";s:5:"value";s:2:"-1";s:12:"aggregate_id";s:36:"00000000-0000-0000-0000-000000000101";}',
   '2015-02-02T21:22:11.000000+0100', 'TaskManagement\\Task', '00000000-0000-0000-0000-000000000101'),
  ('66697b0b-0790-48b7-a122-ff2078c5bf40', 8, 'TaskManagement\\EstimationAdded',
   'a:3:{s:2:"by";s:36:"20000000-0000-0000-0000-000000000000";s:5:"value";s:2:"-1";s:12:"aggregate_id";s:36:"00000000-0000-0000-0000-000000000101";}',
   '2015-02-02T21:22:11.000000+0100', 'TaskManagement\\Task', '00000000-0000-0000-0000-000000000101'),
  ('fbdfdd17-61ef-4f80-bcd4-7e6eb6990754', 9, 'TaskManagement\\TaskAccepted',
   'a:2:{s:12:\"aggregate_id\";s:36:\"00000000-0000-0000-0000-000000000101\";s:2:\"by\";s:36:\"60000000-0000-0000-0000-000000000000\";}',
   '2014-10-31T10:44:30.000000+0100', 'TaskManagement\\Task', '00000000-0000-0000-0000-000000000101');

INSERT INTO tasks (id, stream_id, subject, status, createdAt, mostRecentEditAt, type) VALUES
  ('00000000-0000-0000-0000-000000000101', '00000000-1000-0000-0000-000000000000', 'Accepted Task', 40,
   '2014-11-06 14:32:44', '2014-11-06 14:32:44', 'task');
INSERT INTO task_members (task_id, member_id, role, createdAt, mostRecentEditAt, estimation_value, estimation_createdAt)
VALUES
  ('00000000-0000-0000-0000-000000000101', '60000000-0000-0000-0000-000000000000', 'owner', '2014-11-12 19:07:59',
   '2014-03-23 19:07:59', '-1', '2014-11-07 11:37:58'),
  ('00000000-0000-0000-0000-000000000101', '20000000-0000-0000-0000-000000000000', 'member', '2014-11-12 19:07:59',
   '2014-11-12 19:07:59', '-1', '2014-11-07 11:37:58');


# kanbanizeStream
INSERT INTO event_stream (eventId, version, eventName, payload, occurredOn, aggregate_type, aggregate_id) VALUES
  ('fac7de07-0580-421c-94ca-21842f676a30', 1, 'TaskManagement\\StreamCreated',
   'a:3:{s:14:"organizationId";s:36:"00000000-0000-0000-1000-000000000000";s:2:"by";s:36:"60000000-0000-0000-0000-000000000000";s:12:"aggregate_id";s:36:"11111111-1000-0000-0000-000000000000";}',
   '2015-03-11T01:43:03.000000+0100', 'TaskManagement\\Stream', '11111111-1000-0000-0000-000000000000'),
  ('4a0385c4-780b-46b2-b8be-7fd0118be870', 2, 'TaskManagement\\StreamUpdated',
   'a:3:{s:7:"subject";N;s:2:"by";s:36:"60000000-0000-0000-0000-000000000000";s:12:"aggregate_id";s:36:"11111111-1000-0000-0000-000000000000";}',
   '2015-03-11T01:43:03.000000+0100', 'TaskManagement\\Stream', '11111111-1000-0000-0000-000000000000'),
  ('6e943d09-7a9a-4a2a-a8b5-201bfeb57b60', 3, 'TaskManagement\\StreamOrganizationChanged',
   'a:3:{s:14:\"organizationId\";s:36:\"00000000-0000-0000-1000-000000000000\";s:2:\"by\";s:36:\"60000000-0000-0000-0000-000000000000\";s:12:\"aggregate_id\";s:36:\"11111111-1000-0000-0000-000000000000\";}',
   '2014-11-12T19:07:59.000000+0100', 'TaskManagement\\Stream', '11111111-1000-0000-0000-000000000000');
INSERT INTO streams (id, subject, createdAt, mostRecentEditAt, organization_id, createdBy_id, mostRecentEditBy_id, type)
VALUES ('11111111-1000-0000-0000-000000000000', 'O.R.A.: Kanbanize Stream', '2014-11-06 13:11:05',
        '2014-11-06 13:11:05', '00000000-0000-0000-1000-000000000000', '60000000-0000-0000-0000-000000000000',
        '60000000-0000-0000-0000-000000000000', 'kanbanizestream');
INSERT INTO kanbanizestreams (id, boardId, projectId)
VALUES ('11111111-1000-0000-0000-000000000000', '150', '100');

# kanbanizeTask 00000000-0000-0000-0000-000000000106, accepted, Mark Rogers (owner)
INSERT INTO event_stream (eventId, version, eventName, payload, occurredOn, aggregate_type, aggregate_id) VALUES
('6126d983-20ad-47f2-9636-085395aa3b8b',1,'TaskManagement\\TaskCreated','a:9:{s:6:\"status\";i:-1;s:6:\"taskid\";s:3:\"114\";s:14:\"organizationId\";s:36:\"00000000-0000-0000-1000-000000000000\";s:8:\"streamId\";s:36:\"11111111-1000-0000-0000-000000000000\";s:2:\"by\";s:36:\"60000000-0000-0000-0000-000000000000\";s:8:\"assignee\";N;s:10:\"columnname\";s:11:\"In Progress\";s:12:\"aggregate_id\";s:36:\"00000000-0000-0000-0000-000000000106\";s:7:\"subject\";s:9:\"subject 0\";}','2015-11-20T16:07:44.000000+0000','Kanbanize\\KanbanizeTask','00000000-0000-0000-0000-000000000106'),
('334fa91f-62c9-4b34-827b-3e01bd7efe9c', 2, 'TaskManagement\\TaskUpdated', 'a:3:{s:7:"subject";s:18:"un altro - cambio5";s:2:"by";s:36:"60000000-0000-0000-0000-000000000000";s:12:"aggregate_id";s:36:"00000000-0000-0000-0000-000000000106";}',
   '2014-11-12T19:07:59.000000+0100', 'Kanbanize\\KanbanizeTask', '00000000-0000-0000-0000-000000000106'), 
  ('c033eb32-2ad4-49d5-b25c-3c0b600b7d12', 4, 'TaskManagement\\TaskMemberAdded',
   'a:4:{s:6:\"userId\";s:36:\"60000000-0000-0000-0000-000000000000\";s:4:\"role\";s:5:\"owner\";s:2:\"by\";s:36:\"60000000-0000-0000-0000-000000000000\";s:12:\"aggregate_id\";s:36:\"00000000-0000-0000-0000-000000000106\";}',
   '2014-03-23T19:07:59.000000+0100', 'Kanbanize\\KanbanizeTask', '00000000-0000-0000-0000-000000000106'),
  ('fbdfdd17-61ef-4f80-bcd4-7e6eb6991723', 5, 'TaskManagement\\TaskCompleted',
   'a:2:{s:12:\"aggregate_id\";s:36:\"00000000-0000-0000-0000-000000000106\";s:2:\"by\";s:36:\"60000000-0000-0000-0000-000000000000\";}',
   '2014-10-31T10:44:30.000000+0100', 'Kanbanize\\KanbanizeTask', '00000000-0000-0000-0000-000000000106'),
  ('fbdfdd17-61ef-4f80-bcd4-7e6eb6991714', 6, 'TaskManagement\\TaskAccepted',
   'a:2:{s:12:\"aggregate_id\";s:36:\"00000000-0000-0000-0000-000000000106\";s:2:\"by\";s:36:\"60000000-0000-0000-0000-000000000000\";}',
   '2014-10-31T10:44:31.000000+0100', 'Kanbanize\\KanbanizeTask', '00000000-0000-0000-0000-000000000106');

INSERT INTO tasks (id, stream_id, subject, status, createdAt, mostRecentEditAt, type) VALUES
  ('00000000-0000-0000-0000-000000000106', '11111111-1000-0000-0000-000000000000', 'Accepted Kanbanize Task', 40,
   '2014-11-06 14:32:44', '2014-11-06 14:32:44', 'kanbanizetask');
INSERT INTO kanbanizetasks (id, taskId)
VALUES ('00000000-0000-0000-0000-000000000106', '114');
INSERT INTO task_members (task_id, member_id, role, createdAt, mostRecentEditAt, estimation_value, estimation_createdAt)
VALUES
  ('00000000-0000-0000-0000-000000000106', '60000000-0000-0000-0000-000000000000', 'owner', '2014-11-12 19:07:59',
   '2014-03-23 19:07:59', '-1', '2014-11-07 11:37:58');

# kanbanizeTask 00000000-0000-0000-0000-000000000107, completed, Mark Rogers (owner), Bruce Wayne (member)
INSERT INTO event_stream (eventId, version, eventName, payload, occurredOn, aggregate_type, aggregate_id) VALUES
('6126d983-20ad-47f2-9636-085395aa3b40',1,'TaskManagement\\TaskCreated','a:9:{s:6:\"status\";i:-1;s:6:\"taskid\";s:3:\"115\";s:14:\"organizationId\";s:36:\"00000000-0000-0000-1000-000000000000\";s:8:\"streamId\";s:36:\"11111111-1000-0000-0000-000000000000\";s:2:\"by\";s:36:\"60000000-0000-0000-0000-000000000000\";s:8:\"assignee\";N;s:10:\"columnname\";s:11:\"In Progress\";s:12:\"aggregate_id\";s:36:\"00000000-0000-0000-0000-000000000107\";s:7:\"subject\";s:9:\"subject 0\";}','2015-11-20T16:07:44.000000+0000','Kanbanize\\KanbanizeTask','00000000-0000-0000-0000-000000000107'),
('334fa91f-62c9-4b34-827b-3e01bd7efe50', 2, 'TaskManagement\\TaskUpdated', 'a:3:{s:7:"subject";s:18:"un altro - cambio4";s:2:"by";s:36:"60000000-0000-0000-0000-000000000000";s:12:"aggregate_id";s:36:"00000000-0000-0000-0000-000000000107";}',
   '2014-11-12T19:07:59.000000+0100', 'Kanbanize\\KanbanizeTask', '00000000-0000-0000-0000-000000000107'), 
  ('c033eb32-2ad4-49d5-b25c-3c0b600b7de0', 4, 'TaskManagement\\TaskMemberAdded',
   'a:4:{s:6:\"userId\";s:36:\"60000000-0000-0000-0000-000000000000\";s:4:\"role\";s:5:\"owner\";s:2:\"by\";s:36:\"60000000-0000-0000-0000-000000000000\";s:12:\"aggregate_id\";s:36:\"00000000-0000-0000-0000-000000000107\";}',
   '2014-04-08T19:07:59.000000+0100', 'TaskManagement\\Task', '00000000-0000-0000-0000-000000000107'),
  ('c033eb32-2ad4-49d5-b25c-3c0b610b7de0', 5, 'TaskManagement\\TaskMemberAdded',
   'a:4:{s:6:\"userId\";s:36:\"80000000-0000-0000-0000-000000000000\";s:4:\"role\";s:6:\"member\";s:2:\"by\";s:36:\"80000000-0000-0000-0000-000000000000\";s:12:\"aggregate_id\";s:36:\"00000000-0000-0000-0000-000000000107\";}',
   '2014-11-12T19:07:59.000000+0100', 'TaskManagement\\Task', '00000000-0000-0000-0000-000000000107'),
  ('65696b0b-0790-48b7-a122-ff2078c5bf20', 6, 'TaskManagement\\EstimationAdded',
   'a:3:{s:2:"by";s:36:"60000000-0000-0000-0000-000000000000";s:5:"value";s:2:"-1";s:12:"aggregate_id";s:36:"00000000-0000-0000-0000-000000000107";}',
   '2015-02-02T21:22:11.000000+0100', 'Kanbanize\\KanbanizeTask', '00000000-0000-0000-0000-000000000107'),
  ('65606b0b-0790-48b7-a122-ff2078c5bf20', 7, 'TaskManagement\\EstimationAdded',
   'a:3:{s:2:"by";s:36:"80000000-0000-0000-0000-000000000000";s:5:"value";s:4:"1500";s:12:"aggregate_id";s:36:"00000000-0000-0000-0000-000000000107";}',
   '2015-02-02T21:22:11.000000+0100', 'Kanbanize\\KanbanizeTask', '00000000-0000-0000-0000-000000000107'),
  ('fbdfdd17-61ef-4f80-bcd4-7e6eb6901723', 8, 'TaskManagement\\TaskCompleted',
   'a:2:{s:12:\"aggregate_id\";s:36:\"00000000-0000-0000-0000-000000000107\";s:2:\"by\";s:36:\"60000000-0000-0000-0000-000000000000\";}',
   '2014-10-31T10:44:30.000000+0100', 'Kanbanize\\KanbanizeTask', '00000000-0000-0000-0000-000000000107');
INSERT INTO tasks (id, stream_id, subject, status, createdAt, mostRecentEditAt, type) VALUES
  ('00000000-0000-0000-0000-000000000107', '11111111-1000-0000-0000-000000000000', 'Kanbanize Task 2', 30,
   '2014-11-06 14:43:28', '2014-11-06 14:43:28', 'kanbanizetask');
INSERT INTO kanbanizetasks (id, taskId)
VALUES ('00000000-0000-0000-0000-000000000107', '115');
INSERT INTO task_members (task_id, member_id, role, createdAt, mostRecentEditAt, estimation_value, estimation_createdAt)
VALUES
  ('00000000-0000-0000-0000-000000000107', '80000000-0000-0000-0000-000000000000', 'member', '2014-11-12 19:07:59',
   '2014-04-08 19:07:59', '1500.00', '2014-11-07 11:37:58'),
  ('00000000-0000-0000-0000-000000000107', '60000000-0000-0000-0000-000000000000', 'owner', '2014-11-12 19:07:59',
   '2014-11-12 19:07:59', '-1', '2014-11-07 11:37:58');

# kanbanizeTask 00000000-0000-0000-0000-000000000108, ongoing, Mark Rogers (owner), Bruce Wayne (member)
INSERT INTO event_stream (eventId, version, eventName, payload, occurredOn, aggregate_type, aggregate_id) VALUES
('6126d984-20ad-47f2-9636-085395aa3b41',1,'TaskManagement\\TaskCreated','a:9:{s:6:\"status\";i:20;s:6:\"taskid\";s:3:\"116\";s:14:\"organizationId\";s:36:\"00000000-0000-0000-1000-000000000000\";s:8:\"streamId\";s:36:\"11111111-1000-0000-0000-000000000000\";s:2:\"by\";s:36:\"60000000-0000-0000-0000-000000000000\";s:8:\"assignee\";N;s:10:\"columnname\";s:11:\"In Progress\";s:12:\"aggregate_id\";s:36:\"00000000-0000-0000-0000-000000000108\";s:7:\"subject\";s:9:\"subject 0\";}','2015-11-20T16:07:44.000000+0000','Kanbanize\\KanbanizeTask','00000000-0000-0000-0000-000000000108'),
('334fa91e-62c9-4b34-827b-3e01bd7efe51', 2, 'TaskManagement\\TaskUpdated', 'a:3:{s:7:"subject";s:18:"un altro - cambio3";s:2:"by";s:36:"60000000-0000-0000-0000-000000000000";s:12:"aggregate_id";s:36:"00000000-0000-0000-0000-000000000108";}',
   '2014-11-12T19:07:59.000000+0100', 'Kanbanize\\KanbanizeTask', '00000000-0000-0000-0000-000000000108'),
  ('c033eb32-2ad4-49d5-b25c-3c0b600b7de1', 4, 'TaskManagement\\TaskMemberAdded',
   'a:4:{s:6:\"userId\";s:36:\"60000000-0000-0000-0000-000000000000\";s:4:\"role\";s:5:\"owner\";s:2:\"by\";s:36:\"60000000-0000-0000-0000-000000000000\";s:12:\"aggregate_id\";s:36:\"00000000-0000-0000-0000-000000000108\";}',
   '2014-05-16T19:07:59.000000+0100', 'Kanbanize\\KanbanizeTask', '00000000-0000-0000-0000-000000000108'),
  ('68696c0b-0790-48b7-a122-ff2078c5bf20', 5, 'TaskManagement\\EstimationAdded',
   'a:3:{s:2:"by";s:36:"60000000-0000-0000-0000-000000000000";s:5:"value";s:2:"-1";s:12:"aggregate_id";s:36:"00000000-0000-0000-0000-000000000108";}',
   '2015-02-02T21:22:11.000000+0100', 'Kanbanize\\KanbanizeTask', '00000000-0000-0000-0000-000000000108');
INSERT INTO tasks (id, stream_id, subject, status, createdAt, mostRecentEditAt, type) VALUES
  ('00000000-0000-0000-0000-000000000108', '11111111-1000-0000-0000-000000000000', 'Kanbanize Task 3', 20,
   '2014-11-06 14:51:12', '2014-11-06 14:51:12', 'kanbanizetask');
INSERT INTO kanbanizetasks (id, taskId)
VALUES ('00000000-0000-0000-0000-000000000108', '116');
INSERT INTO task_members (task_id, member_id, role, createdAt, mostRecentEditAt, estimation_value, estimation_createdAt)
VALUES
  ('00000000-0000-0000-0000-000000000108', '60000000-0000-0000-0000-000000000000', 'owner', '2014-11-12 19:07:59',
   '2014-05-16 19:07:59', '1500.00', '2014-11-07 11:37:58'),
  ('00000000-0000-0000-0000-000000000108', '80000000-0000-0000-0000-000000000000', 'member', '2014-11-12 19:07:59',
   '2014-11-12 19:07:59', NULL, NULL);

# kanbanizeTask 00000000-0000-0000-0000-000000000110, completed, Mark Rogers (owner)
INSERT INTO event_stream (eventId, version, eventName, payload, occurredOn, aggregate_type, aggregate_id) VALUES
('6126d984-20ad-47f2-9636-085395aa3b40',1,'TaskManagement\\TaskCreated','a:9:{s:6:\"status\";i:30;s:6:\"taskid\";s:2:\"17\";s:14:\"organizationId\";s:36:\"00000000-0000-0000-1000-000000000000\";s:8:\"streamId\";s:36:\"11111111-1000-0000-0000-000000000000\";s:2:\"by\";s:36:\"60000000-0000-0000-0000-000000000000\";s:8:\"assignee\";N;s:10:\"columnname\";s:11:\"In Progress\";s:12:\"aggregate_id\";s:36:\"00000000-0000-0000-0000-000000000110\";s:7:\"subject\";s:9:\"subject 0\";}','2015-11-20T16:07:44.000000+0000','Kanbanize\\KanbanizeTask','00000000-0000-0000-0000-000000000110'),
('334fa91e-62c9-4b34-827b-3e01bd7efe50', 2, 'TaskManagement\\TaskUpdated', 'a:3:{s:7:"subject";s:18:"un altro - cambio2";s:2:"by";s:36:"60000000-0000-0000-0000-000000000000";s:12:"aggregate_id";s:36:"00000000-0000-0000-0000-000000000110";}',
   '2014-11-12T19:07:59.000000+0100', 'Kanbanize\\KanbanizeTask', '00000000-0000-0000-0000-000000000110'),
  ('c033eb33-2ad4-49d5-b25c-3c0b600b7de0', 4, 'TaskManagement\\TaskMemberAdded',
   'a:4:{s:6:\"userId\";s:36:\"60000000-0000-0000-0000-000000000000\";s:4:\"role\";s:5:\"owner\";s:2:\"by\";s:36:\"60000000-0000-0000-0000-000000000000\";s:12:\"aggregate_id\";s:36:\"00000000-0000-0000-0000-000000000110\";}',
   '2014-06-03T19:07:59.000000+0100', 'Kanbanize\\KanbanizeTask', '00000000-0000-0000-0000-000000000110'),
  ('fbdfdd18-61ef-4f80-bcd4-7e6eb6901723', 5, 'TaskManagement\\TaskCompleted',
   'a:2:{s:12:\"aggregate_id\";s:36:\"00000000-0000-0000-0000-000000000110\";s:2:\"by\";s:36:\"60000000-0000-0000-0000-000000000000\";}',
   '2014-10-31T10:44:30.000000+0100', 'Kanbanize\\KanbanizeTask', '00000000-0000-0000-0000-000000000110');
INSERT INTO tasks (id, stream_id, subject, status, createdAt, mostRecentEditAt, type) VALUES
  ('00000000-0000-0000-0000-000000000110', '11111111-1000-0000-0000-000000000000', 'completedTask', 30,
   '2014-11-06 15:39:13', '2014-11-06 15:39:13', 'kanbanizetask');
INSERT INTO kanbanizetasks (id, taskId)
VALUES ('00000000-0000-0000-0000-000000000110', '119');
INSERT INTO task_members (task_id, member_id, role, createdAt, mostRecentEditAt) VALUES
  ('00000000-0000-0000-0000-000000000110', '60000000-0000-0000-0000-000000000000', 'owner', '2014-11-12 19:07:59',
   '2014-06-03 19:07:59');

# kanbanizeTask 00000000-0000-0000-0000-000000000111, accepted, Mark Rogers (owner)
INSERT INTO event_stream (eventId, version, eventName, payload, occurredOn, aggregate_type, aggregate_id) VALUES
('6136d983-20ad-47f2-9636-085395aa3b8b',1,'TaskManagement\\TaskCreated','a:9:{s:6:\"status\";i:-1;s:6:\"taskid\";s:2:\"17\";s:14:\"organizationId\";s:36:\"00000000-0000-0000-1000-000000000000\";s:8:\"streamId\";s:36:\"11111111-1000-0000-0000-000000000000\";s:2:\"by\";s:36:\"60000000-0000-0000-0000-000000000000\";s:8:\"assignee\";N;s:10:\"columnname\";s:11:\"In Progress\";s:12:\"aggregate_id\";s:36:\"00000000-0000-0000-0000-000000000111\";s:7:\"subject\";s:9:\"subject 0\";}','2015-11-20T16:07:44.000000+0000','Kanbanize\\KanbanizeTask','00000000-0000-0000-0000-000000000111'),
('335fa91f-62c9-4b34-827b-3e01bd7efe9c', 2, 'TaskManagement\\TaskUpdated', 'a:3:{s:7:"subject";s:17:"un altro - cambio";s:2:"by";s:36:"60000000-0000-0000-0000-000000000000";s:12:"aggregate_id";s:36:"00000000-0000-0000-0000-000000000111";}',
   '2014-11-12T19:07:59.000000+0100', 'Kanbanize\\KanbanizeTask', '00000000-0000-0000-0000-000000000111'),
  ('c043eb32-2ad4-49d5-b25c-3c0b600b7d12', 4, 'TaskManagement\\TaskMemberAdded',
   'a:4:{s:6:\"userId\";s:36:\"60000000-0000-0000-0000-000000000000\";s:4:\"role\";s:5:\"owner\";s:2:\"by\";s:36:\"60000000-0000-0000-0000-000000000000\";s:12:\"aggregate_id\";s:36:\"00000000-0000-0000-0000-000000000111\";}',
   '2014-07-07T19:07:59.000000+0100', 'Kanbanize\\KanbanizeTask', '00000000-0000-0000-0000-000000000111'),
  ('68696b0b-0790-48b7-a122-ff2078c5bf20', 5, 'TaskManagement\\EstimationAdded',
   'a:3:{s:2:"by";s:36:"60000000-0000-0000-0000-000000000000";s:5:"value";s:2:"-1";s:12:"aggregate_id";s:36:"00000000-0000-0000-0000-000000000111";}',
   '2015-02-02T21:22:11.000000+0100', 'Kanbanize\\KanbanizeTask', '00000000-0000-0000-0000-000000000111'),
  ('fbefdd17-61ef-4f80-bcd4-7e6eb6991723', 6, 'TaskManagement\\TaskCompleted',
   'a:2:{s:12:\"aggregate_id\";s:36:\"00000000-0000-0000-0000-000000000111\";s:2:\"by\";s:36:\"60000000-0000-0000-0000-000000000000\";}',
   '2014-10-31T10:44:30.000000+0100', 'Kanbanize\\KanbanizeTask', '00000000-0000-0000-0000-000000000111'),
  ('fbefdd17-61ef-4f80-bcd4-7e6eb6991714', 7, 'TaskManagement\\TaskAccepted',
   'a:2:{s:12:\"aggregate_id\";s:36:\"00000000-0000-0000-0000-000000000111\";s:2:\"by\";s:36:\"60000000-0000-0000-0000-000000000000\";}',
   '2014-10-31T10:44:31.000000+0100', 'Kanbanize\\KanbanizeTask', '00000000-0000-0000-0000-000000000111');
INSERT INTO tasks (id, stream_id, subject, status, createdAt, mostRecentEditAt, type) VALUES
  ('00000000-0000-0000-0000-000000000111', '11111111-1000-0000-0000-000000000000', 'acceptedTask', 40,
   '2014-11-06 15:48:17', '2014-11-06 15:48:17', 'kanbanizetask');
INSERT INTO kanbanizetasks (id, taskId)
VALUES ('00000000-0000-0000-0000-000000000111', '120');
INSERT INTO task_members (task_id, member_id, role, createdAt, mostRecentEditAt) VALUES
  ('00000000-0000-0000-0000-000000000111', '60000000-0000-0000-0000-000000000000', 'owner', '2014-11-12 19:07:59',
   '2014-07-07 19:07:59');

# task 00000000-0000-0000-0000-000000000004, ongoing, Mark Rogers (owner), Paul Smith (member)
INSERT INTO event_stream (eventId, version, eventName, payload, occurredOn, aggregate_type, aggregate_id) VALUES
('6126d983-20ad-47f2-9636-085395aa3b4c', 1, 'TaskManagement\\TaskCreated',
   'a:5:{s:8:\"streamId\";s:36:\"00000000-1000-0000-0000-000000000000\";s:14:\"organizationId\";s:36:\"00000000-0000-0000-1000-000000000000\";s:6:\"status\";i:20;s:2:\"by\";s:36:\"60000000-0000-0000-0000-000000000000\";s:12:\"aggregate_id\";s:36:\"00000000-0000-0000-0000-000000000004\";}',
   '2014-08-12T19:07:59.000000+0100', 'TaskManagement\\Task', '00000000-0000-0000-0000-000000000004'),
  ('334fa91f-62c9-4b34-827b-3e01bd7efe5d', 2, 'TaskManagement\\TaskUpdated',
   'a:3:{s:7:\"subject\";s:29:\"Development environment setup\";s:2:\"by\";s:36:\"60000000-0000-0000-0000-000000000000\";s:12:\"aggregate_id\";s:36:\"00000000-0000-0000-0000-000000000004\";}',
   '2014-08-12T19:07:59.000000+0100', 'TaskManagement\\Task', '00000000-0000-0000-0000-000000000004'),
  ('c033eb32-2ad4-49d5-b25c-3c0b600b7dez', 4, 'TaskManagement\\TaskMemberAdded',
   'a:4:{s:6:\"userId\";s:36:\"60000000-0000-0000-0000-000000000000\";s:4:\"role\";s:5:\"owner\";s:2:\"by\";s:36:\"60000000-0000-0000-0000-000000000000\";s:12:\"aggregate_id\";s:36:\"00000000-0000-0000-0000-000000000004\";}',
   '2014-08-12T19:07:59.000000+0100', 'TaskManagement\\Task', '00000000-0000-0000-0000-000000000004'),
  ('c133eb32-2ad4-49d5-b25c-3c0b600b7dey', 5, 'TaskManagement\\TaskMemberAdded',
   'a:4:{s:6:\"userId\";s:36:\"20000000-0000-0000-0000-000000000000\";s:4:\"role\";s:6:\"member\";s:2:\"by\";s:36:\"20000000-0000-0000-0000-000000000000\";s:12:\"aggregate_id\";s:36:\"00000000-0000-0000-0000-000000000004\";}',
   '2014-11-12T19:07:59.000000+0100', 'TaskManagement\\Task', '00000000-0000-0000-0000-000000000004');
INSERT INTO `tasks` (id, stream_id, subject, status, createdAt, mostRecentEditAt, createdBy_id, mostRecentEditBy_id, type)
VALUES
  ('00000000-0000-0000-0000-000000000004', '00000000-1000-0000-0000-000000000000', 'Development environment setup', 20,
   '2014-08-12 19:07:59', '2014-11-12 19:07:59', '60000000-0000-0000-0000-000000000000',
   '60000000-0000-0000-0000-000000000000', 'task');
INSERT INTO task_members (task_id, member_id, role, createdAt, mostRecentEditAt) VALUES
  ('00000000-0000-0000-0000-000000000004', '60000000-0000-0000-0000-000000000000', 'owner', '2014-11-12 19:07:59',
   '2014-08-12 19:07:59'),
  ('00000000-0000-0000-0000-000000000004', '20000000-0000-0000-0000-000000000000', 'member', '2014-11-12 19:07:59',
   '2014-11-12 19:07:59');

# task 00000000-0000-0000-0000-000000000901, shares completed, Mark Rogers (owner)
INSERT INTO event_stream (eventId, version, eventName, payload, occurredOn, aggregate_type, aggregate_id) VALUES
 ('7126d983-20ad-47f2-9636-085395aa3b7b', 1, 'TaskManagement\\TaskCreated',
   'a:5:{s:8:\"streamId\";s:36:\"00000000-1000-0000-0000-000000000000\";s:14:\"organizationId\";s:36:\"00000000-0000-0000-1000-000000000000\";s:6:\"status\";i:20;s:2:\"by\";s:36:\"60000000-0000-0000-0000-000000000000\";s:12:\"aggregate_id\";s:36:\"00000000-0000-0000-0000-000000000901\";}',
   '2014-02-07T19:07:59.000000+0100', 'TaskManagement\\Task', '00000000-0000-0000-0000-000000000901'),
  ('734fa91f-62c9-4b34-827b-3e01bd7efe8c', 2, 'TaskManagement\\TaskUpdated',
   'a:3:{s:7:\"subject\";s:27:\"Technology stack definition\";s:2:\"by\";s:36:\"60000000-0000-0000-0000-000000000000\";s:12:\"aggregate_id\";s:36:\"00000000-0000-0000-0000-000000000901\";}',
   '2014-02-07T19:07:59.000000+0100', 'TaskManagement\\Task', '00000000-0000-0000-0000-000000000901'),
  ('7033eb32-2ad4-49d5-b25c-3c0b600b7d0c', 4, 'TaskManagement\\TaskMemberAdded',
   'a:4:{s:6:\"userId\";s:36:\"60000000-0000-0000-0000-000000000000\";s:4:\"role\";s:5:\"owner\";s:2:\"by\";s:36:\"60000000-0000-0000-0000-000000000000\";s:12:\"aggregate_id\";s:36:\"00000000-0000-0000-0000-000000000901\";}',
   '2014-02-07T19:07:59.000000+0100', 'TaskManagement\\Task', '00000000-0000-0000-0000-000000000901'),
  ('7bdfdd17-61ef-4f80-bcd4-7e6eb6990733', 5, 'TaskManagement\\TaskCompleted',
   'a:2:{s:12:\"aggregate_id\";s:36:\"00000000-0000-0000-0000-000000000901\";s:2:\"by\";s:36:\"60000000-0000-0000-0000-000000000000\";}',
   '2014-10-31T10:44:30.000000+0100', 'TaskManagement\\Task', '00000000-0000-0000-0000-000000000901'),
  ('75697b0b-0790-48b7-a122-ff2078c5bf40', 7, 'TaskManagement\\EstimationAdded',
   'a:3:{s:2:"by";s:36:"60000000-0000-0000-0000-000000000000";s:5:"value";s:2:"-1";s:12:"aggregate_id";s:36:"00000000-0000-0000-0000-000000000901";}',
   '2015-02-02T21:22:11.000000+0100', 'TaskManagement\\Task', '00000000-0000-0000-0000-000000000901'),
  ('7bdfdd17-61ef-4f80-bcd4-7e6eb6990754', 9, 'TaskManagement\\TaskAccepted',
   'a:2:{s:12:\"aggregate_id\";s:36:\"00000000-0000-0000-0000-000000000901\";s:2:\"by\";s:36:\"60000000-0000-0000-0000-000000000000\";}',
   '2014-10-31T10:44:30.000000+0100', 'TaskManagement\\Task', '00000000-0000-0000-0000-000000000901'),
('7526d983-20ad-47f2-9636-085395aa3b7b', 10, 'TaskManagement\\SharesAssigned',
   'a:3:{s:6:"shares";a:1:{s:36:"60000000-0000-0000-0000-000000000000";d:1.00000000000000000;}s:2:"by";s:36:"60000000-0000-0000-0000-000000000000";s:12:"aggregate_id";s:36:"00000000-0000-0000-0000-000000000901";}',
   '2014-02-07T19:07:59.000000+0100', 'TaskManagement\\Task', '00000000-0000-0000-0000-000000000901');

INSERT INTO tasks (id, stream_id, subject, status, createdAt, mostRecentEditAt, type) VALUES
  ('00000000-0000-0000-0000-000000000901', '00000000-1000-0000-0000-000000000000', 'Shares assignment completed Task', 40,
   '2014-11-06 14:32:44', '2014-11-06 14:32:44', 'task');
INSERT INTO task_members (task_id, member_id, role, createdAt, mostRecentEditAt, estimation_value, estimation_createdAt)
VALUES
  ('00000000-0000-0000-0000-000000000901', '60000000-0000-0000-0000-000000000000', 'owner', '2014-11-12 19:07:59',
   '2014-03-23 19:07:59', '-1', '2014-11-07 11:37:58');
INSERT INTO shares(id, evaluator_id, task_id, valued_id, value, createdAt)
VALUES (100, '60000000-0000-0000-0000-000000000000', '00000000-0000-0000-0000-000000000901', '60000000-0000-0000-0000-000000000000','1.0', '2014-11-06 14:32:44');
#account_transactions for UserProfileAPITest   
INSERT INTO account_transactions (id, payer_id, payee_id, amount, description, balance, createdAt, createdBy_id, type ) 
VALUES 
('1', 'ccde992b-5aa9-4447-98ae-c8115906dcb7', 'cdde992b-5aa9-4447-98ae-c8115906dcb9', 500, 'Description', 500, '2015-02-18 10:48:13', '80000000-0000-0000-0000-000000000000', 'transfer');

INSERT INTO account_transactions (id, payer_id,payee_id, amount, description, balance, createdAt, createdBy_id, type ) 
VALUES 
('2', 'ccde992b-5aa9-4447-98ae-c8115906dcb7', 'cdde992b-5aa9-4447-98ae-c8115906dcb9', 1000, 'Description', 1000, '2015-06-18 10:48:13', '80000000-0000-0000-0000-000000000000', 'transfer');

INSERT INTO account_transactions (id, payer_id,payee_id, amount, description, balance, createdAt, createdBy_id, type ) 
VALUES 
('3', 'ccde992b-5aa9-4447-98ae-c8115906dcb7', 'cdde992b-5aa9-4447-98ae-c8115906dcb9', 100, 'Description', 100, '2015-10-18 10:48:13', '80000000-0000-0000-0000-000000000000', 'transfer');
   
INSERT INTO account_transactions (id, payer_id,payee_id, amount, description, balance, createdAt, createdBy_id, type ) 
VALUES 
('4', 'ccde992b-5aa9-4447-98ae-c8115906dcb7', 'cdde992b-5aa9-4447-98ae-c8115906dcb9', 2000, 'Description', 2000,  '2014-02-18 10:48:13', '80000000-0000-0000-0000-000000000000', 'transfer');

#organization account transactions
INSERT INTO account_transactions (id, payer_id, payee_id, amount, description, balance, createdAt, createdBy_id, type )
VALUES
  (5, NULL, 'dcde992b-5aa9-4447-98ae-c8115906dcb7', 10000, 'Initial Budget', 10000, '2015-02-10 10:48:13', '60000000-0000-0000-0000-000000000000', 'deposit');

INSERT INTO account_transactions (id, payer_id,payee_id, amount, description, balance, createdAt, createdBy_id, type )
VALUES
  (6, 'dcde992b-5aa9-4447-98ae-c8115906dcb7', NULL, -100, 'Budget Reduction', 9900, '2015-02-11 10:48:13', '60000000-0000-0000-0000-000000000000', 'withdrawal');

INSERT INTO account_transactions (id, payer_id,payee_id, amount, description, balance, createdAt, createdBy_id, type )
VALUES
  (7, 'dcde992b-5aa9-4447-98ae-c8115906dcb7', 'cdde992b-5aa9-4447-98ae-c8115906dcb9', -500, 'Item share', 9400, '2015-02-12 10:48:13', '60000000-0000-0000-0000-000000000000', 'transfer');

INSERT INTO account_transactions (id, payer_id,payee_id, amount, description, balance, createdAt, createdBy_id, type )
VALUES
  (8, 'cdde992b-5aa9-4447-98ae-c8115906dcb9', 'dcde992b-5aa9-4447-98ae-c8115906dcb7', 2000, 'Payment request', 7400,  '2015-02-13 10:48:13', '60000000-0000-0000-0000-000000000000', 'transfer');
