SET SQL_MODE = "NO_AUTO_VALUE_ON_ZERO";
SET AUTOCOMMIT = 0;
START TRANSACTION;
SET time_zone = "+00:00";

--
-- Structure de la table `project`
--

CREATE TABLE `project` (
	`id` int(11) NOT NULL,
	`name` varchar(255) COLLATE utf8_unicode_ci NOT NULL,
	`description` text COLLATE utf8_unicode_ci,
	`state` int(11) NOT NULL,
	`progress_unit` int(11) DEFAULT NULL,
	`progress_total` float NOT NULL DEFAULT '0',
	`progress_current` float NOT NULL DEFAULT '0',
	`date_creation` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
	`date_modification` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;

-- --------------------------------------------------------

--
-- Structure de la table `project_task`
--

CREATE TABLE `project_task` (
	`id` int(11) NOT NULL,
	`parent_project` int(11) NOT NULL,
	`name` varchar(255) COLLATE utf8_unicode_ci NOT NULL,
	`description` text COLLATE utf8_unicode_ci,
	`state` int(11) NOT NULL,
	`progress_unit` int(11) DEFAULT NULL,
	`progress_total` int(11) NOT NULL,
	`progress_current` int(11) NOT NULL,
	`date_creation` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
	`date_modification` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;

-- --------------------------------------------------------

--
-- Structure de la table `reference_value`
--

CREATE TABLE `reference_value` (
	`id` int(11) NOT NULL,
	`code` varchar(255) COLLATE utf8_unicode_ci NOT NULL,
	`value_int` int(11) DEFAULT NULL,
	`value_float` float DEFAULT NULL,
	`text` text COLLATE utf8_unicode_ci,
	`parent_reference_value` int(11) DEFAULT NULL,
	`date_creation` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
	`date_modification` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;

--
-- Contenu de la table `reference_value`
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
-- Structure de la table `user`
--

CREATE TABLE `user` (
	`id` int(11) NOT NULL,
	`name` varchar(255) COLLATE utf8_unicode_ci NOT NULL,
	`email` varchar(255) COLLATE utf8_unicode_ci NOT NULL,
	`login` varchar(255) COLLATE utf8_unicode_ci NOT NULL,
	`password` varchar(255) COLLATE utf8_unicode_ci NOT NULL,
	`date_creation` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
	`date_modification` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;

-- --------------------------------------------------------

--
-- Structure de la table `_relation_project_user`
--

CREATE TABLE `_relation_project_user` (
	`id` int(11) NOT NULL,
	`project` int(11) NOT NULL,
	`user` int(11) NOT NULL,
	`user_right` int(11) NOT NULL,
	`date_creation` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
	`date_modification` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;




-- --------------------------------------------------------

--
-- Index pour les tables exportées
--

--
-- Index pour la table `project`
--
ALTER TABLE `project`
	ADD PRIMARY KEY (`id`),
	ADD UNIQUE KEY `name` (`name`),
	ADD KEY `state` (`state`),
	ADD KEY `progress_unit` (`progress_unit`);

--
-- Index pour la table `project_task`
--
ALTER TABLE `project_task`
	ADD PRIMARY KEY (`id`),
	ADD KEY `parent_project` (`parent_project`),
	ADD KEY `state` (`state`),
	ADD KEY `progress_unit` (`progress_unit`);

--
-- Index pour la table `reference_value`
--
ALTER TABLE `reference_value`
	ADD PRIMARY KEY (`id`),
	ADD UNIQUE KEY `code` (`code`),
	ADD KEY `parent_reference_value` (`parent_reference_value`);

--
-- Index pour la table `user`
--
ALTER TABLE `user`
	ADD PRIMARY KEY (`id`),
	ADD UNIQUE KEY `login` (`login`),
	ADD UNIQUE KEY `email` (`email`);

--
-- Index pour la table `_relation_project_user`
--
ALTER TABLE `_relation_project_user`
	ADD PRIMARY KEY (`id`),
	ADD UNIQUE KEY `project_user` (`project`,`user`) USING BTREE,
	ADD KEY `project` (`project`) USING BTREE,
	ADD KEY `user` (`user`) USING BTREE,
	ADD KEY `user_right` (`user_right`) USING BTREE;


--
-- AUTO_INCREMENT pour les tables exportées
--

--
-- AUTO_INCREMENT pour la table `project`
--
ALTER TABLE `project`
	MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;
--
-- AUTO_INCREMENT pour la table `project_task`
--
ALTER TABLE `project_task`
	MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;
--
-- AUTO_INCREMENT pour la table `reference_value`
--
ALTER TABLE `reference_value`
	MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;
--
-- AUTO_INCREMENT pour la table `user`
--
ALTER TABLE `user`
	MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;
--
-- Contraintes pour les tables exportées
--

--
-- Contraintes pour la table `project`
--
ALTER TABLE `project`
	ADD CONSTRAINT `project_ibfk_1` FOREIGN KEY (`state`) REFERENCES `reference_value` (`id`),
	ADD CONSTRAINT `project_ibfk_2` FOREIGN KEY (`progress_unit`) REFERENCES `reference_value` (`id`);

--
-- Contraintes pour la table `project_task`
--
ALTER TABLE `project_task`
	ADD CONSTRAINT `project_task_ibfk_1` FOREIGN KEY (`parent_project`) REFERENCES `project` (`id`),
	ADD CONSTRAINT `project_task_ibfk_2` FOREIGN KEY (`state`) REFERENCES `reference_value` (`id`),
	ADD CONSTRAINT `project_task_ibfk_3` FOREIGN KEY (`progress_unit`) REFERENCES `reference_value` (`id`);

--
-- Contraintes pour la table `reference_value`
--
ALTER TABLE `reference_value`
	ADD CONSTRAINT `reference_value_ibfk_1` FOREIGN KEY (`parent_reference_value`) REFERENCES `reference_value` (`id`);

--
-- Contraintes pour la table `_relation_project_user`
--
ALTER TABLE `_relation_project_user`
	ADD CONSTRAINT `_relation_project_user_ibfk_1` FOREIGN KEY (`project`) REFERENCES `project` (`id`),
	ADD CONSTRAINT `_relation_project_user_ibfk_2` FOREIGN KEY (`user`) REFERENCES `user` (`id`),
	ADD CONSTRAINT `_relation_project_user_ibfk_3` FOREIGN KEY (`user_right`) REFERENCES `reference_value` (`id`);


COMMIT;
