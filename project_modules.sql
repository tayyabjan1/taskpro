-- Project Goals Table
CREATE TABLE IF NOT EXISTS `project_goals` (
  `id` INT(11) NOT NULL AUTO_INCREMENT,
  `project_id` INT(11) NOT NULL,
  `title` VARCHAR(255) NOT NULL,
  `description` TEXT,
  `deadline` DATE,
  `status` ENUM('planned', 'in_progress', 'completed', 'missed') DEFAULT 'planned',
  `created_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  FOREIGN KEY (`project_id`) REFERENCES `projects`(`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- Project KPIs Table
CREATE TABLE IF NOT EXISTS `project_kpis` (
  `id` INT(11) NOT NULL AUTO_INCREMENT,
  `project_id` INT(11) NOT NULL,
  `name` VARCHAR(255) NOT NULL,
  `target_value` DECIMAL(10,2) NOT NULL,
  `achieved_value` DECIMAL(10,2) DEFAULT 0,
  `unit` VARCHAR(50) DEFAULT '',
  `status` ENUM('on_track', 'at_risk', 'off_track', 'completed') DEFAULT 'on_track',
  `created_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  FOREIGN KEY (`project_id`) REFERENCES `projects`(`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- Project Milestones Table
CREATE TABLE IF NOT EXISTS `project_milestones` (
  `id` INT(11) NOT NULL AUTO_INCREMENT,
  `project_id` INT(11) NOT NULL,
  `title` VARCHAR(255) NOT NULL,
  `due_date` DATE NOT NULL,
  `status` ENUM('upcoming', 'completed', 'overdue') DEFAULT 'upcoming',
  `created_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  FOREIGN KEY (`project_id`) REFERENCES `projects`(`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- Project Achievements Table
CREATE TABLE IF NOT EXISTS `project_achievements` (
  `id` INT(11) NOT NULL AUTO_INCREMENT,
  `project_id` INT(11) NOT NULL,
  `title` VARCHAR(255) NOT NULL,
  `description` TEXT,
  `date_achieved` DATE DEFAULT NULL,
  `created_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  FOREIGN KEY (`project_id`) REFERENCES `projects`(`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;
