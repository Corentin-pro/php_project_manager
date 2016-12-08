BEGIN TRANSACTION;

-- --------------------------------------------------------

--
-- Table 'project'
--

CREATE TABLE IF NOT EXISTS `project` (
	`id` INTEGER PRIMARY KEY ASC,
	`name` TEXT UNIQUE NOT NULL,
	`description` TEXT DEFAULT NULL,
	`state` INTEGER NOT NULL,
	`progress_unit` INTEGER DEFAULT NULL,
	`progress_total` REAL NOT NULL DEFAULT '0',
	`progress_current` REAL NOT NULL DEFAULT '0',
	`date_creation` INTEGER NOT NULL DEFAULT CURRENT_TIMESTAMP,
	`date_modification` INTEGER NOT NULL DEFAULT CURRENT_TIMESTAMP,
	FOREIGN KEY(`state`) REFERENCES `reference_value` (`id`),
	FOREIGN KEY(`progress_unit`) REFERENCES `reference_value` (`id`) );
-- Triggers related to table 'project' --
CREATE TRIGGER IF NOT EXISTS `project_update` AFTER UPDATE ON `project` FOR EACH ROW
	BEGIN
		UPDATE `project` SET `date_modification` = CURRENT_TIMESTAMP WHERE `id` = old.id;
	END;
-- Indexes of the table 'project'
CREATE INDEX `project_state` ON `project` (`state`);
CREATE INDEX `project_progress_unit` ON `project` (`progress_unit`);



-- --------------------------------------------------------

	--
	-- Table 'project_task'
	--

CREATE TABLE IF NOT EXISTS `project_task` (
	`id` INTEGER PRIMARY KEY ASC,
	`parent_project` INTEGER NOT NULL,
	`name` TEXT UNIQUE NOT NULL,
	`description` TEXT DEFAULT NULL,
	`state` INTEGER NOT NULL,
	`progress_unit` INTEGER DEFAULT NULL,
	`progress_total` REAL NOT NULL DEFAULT '0',
	`progress_current` REAL NOT NULL DEFAULT '0',
	`date_creation` INTEGER NOT NULL DEFAULT CURRENT_TIMESTAMP,
	`date_modification` INTEGER NOT NULL DEFAULT CURRENT_TIMESTAMP,
	FOREIGN KEY(`parent_project`) REFERENCES `project` (`id`),
	FOREIGN KEY(`state`) REFERENCES `reference_value` (`id`),
	FOREIGN KEY(`progress_unit`) REFERENCES `reference_value` (`id`) );
-- Triggers related to table 'project_task' --
CREATE TRIGGER IF NOT EXISTS `project_task_update` AFTER UPDATE ON `project_task` FOR EACH ROW
	BEGIN
		UPDATE `project_task` SET `date_modification` = CURRENT_TIMESTAMP WHERE `id` = old.id;
	END;
-- Indexes of the table 'project_task'
CREATE INDEX `project_task_state` ON `project_task` (`state`);
CREATE INDEX `project_task_progress_unit` ON `project_task` (`progress_unit`);



-- --------------------------------------------------------

--
-- Table 'reference_value'
--

CREATE TABLE `reference_value` (
	`id` INTEGER PRIMARY KEY ASC,
	`code` TEXT NOT NULL,
	`value_int` INTEGER DEFAULT NULL,
	`value_float` REAL DEFAULT NULL,
	`text` TEXT DEFAULT NULL,
	`parent_reference_value` INTEGER DEFAULT NULL,
	`date_creation` INTEGER NOT NULL DEFAULT CURRENT_TIMESTAMP,
	`date_modification` INTEGER NOT NULL DEFAULT CURRENT_TIMESTAMP,
	FOREIGN KEY (`parent_reference_value`) REFERENCES `reference_value` (`id`) );
-- Triggers related to table 'reference_value' --
CREATE TRIGGER IF NOT EXISTS `reference_value_update` AFTER UPDATE ON `reference_value` FOR EACH ROW
	BEGIN
		UPDATE `reference_value` SET `date_modification` = CURRENT_TIMESTAMP WHERE `id` = old.id;
	END;
-- Indexes of the table 'reference_value'
CREATE INDEX `reference_value_parent_reference_value` ON `reference_value` (`parent_reference_value`);

--
-- Content of 'reference_value'
--

INSERT INTO `reference_value` (`id`, `code`, `value_int`, `value_float`, `text`, `parent_reference_value`) VALUES
(1, 'PROJECT_STATE', NULL, NULL, NULL, 1),
(2, 'PROJECT_STATE_CREATED', NULL, NULL, 'Project created', 1),
(3, 'PROJECT_STATE_READY', NULL, NULL, 'Project ready', 1),
(4, 'PROJECT_STATE_STARTED', NULL, NULL, 'Project started', 1),
(5, 'PROJECT_STATE_ALPHA', NULL, NULL, 'Alpha', 1),
(6, 'PROJECT_STATE_BETA', NULL, NULL, 'Beta', 1),
(7, 'PROJECT_STATE_FINISHED', NULL, NULL, 'Project finished', 1),
(8, 'PROGRESS_UNIT', NULL, NULL, NULL, 8),
(9, 'PROGRESS_UNIT_HOURS', NULL, NULL, 'h', 8),
(10, 'PROGRESS_UNIT_DAYS', NULL, NULL, 'd', 8),
(11, 'PROJECT_TASK_STATE', NULL, NULL, NULL, 11),
(12, 'PROJECT_TASK_STATE_CREATED', NULL, NULL, 'Task created', 11),
(13, 'PROJECT_TASK_STATE_STARTED', NULL, NULL, 'Task started', 11),
(14, 'PROJECT_TASK_STATE_FINISHED', NULL, NULL, 'Task finished', 11);



-- --------------------------------------------------------

--
-- Table 'user'
--

CREATE TABLE IF NOT EXISTS `user` (
		`id` INTEGER PRIMARY KEY ASC,
		`email` TEXT UNIQUE NOT NULL,
		`login` TEXT UNIQUE NOT NULL,
		`password` TEXT NOT NULL,
		`date_creation` INTEGER NOT NULL DEFAULT CURRENT_TIMESTAMP,
		`date_modification` INTEGER NOT NULL DEFAULT CURRENT_TIMESTAMP);
-- Triggers related to table 'user' --
CREATE TRIGGER IF NOT EXISTS `user_update` AFTER UPDATE ON `user` FOR EACH ROW
	BEGIN
		UPDATE `user` SET date_modification = CURRENT_TIMESTAMP WHERE `id` = old.id;
	END;

END TRANSACTION;
