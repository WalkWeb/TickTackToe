CREATE TABLE `status` (
  `id` INT AUTO_INCREMENT PRIMARY KEY,
  `name` VARCHAR(20) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

CREATE TABLE `game` (
  `id` INT AUTO_INCREMENT PRIMARY KEY,
  `date` TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  `hash` VARCHAR(30) NOT NULL,
  `status_id` INT DEFAULT 1,
  FOREIGN KEY (`status_id`) REFERENCES `status`(`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

CREATE TABLE `stroke` (
  `id` INT AUTO_INCREMENT PRIMARY KEY,
  `game_id` INT NOT NULL,
  `cell` TINYINT NOT NULL,
  `value` TINYINT NOT NULL,
  `date` TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  FOREIGN KEY (`game_id`) REFERENCES `game`(`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

INSERT INTO `status` (`id`, `name`) VALUES
  (1, 'Игра в процессе'),
  (2, 'Паладин победил'),
  (3, 'Диабло победил'),
  (4, 'Ничья'),
  (5, 'Игрок сдался'),
  (6, 'Время вышло');
