-- Create trickyanswers database
CREATE DATABASE IF NOT EXISTS trickyanswers;

USE trickyanswers;

-- Create ci_sessions table
CREATE TABLE IF NOT EXISTS `ci_sessions` (
    `id` VARCHAR(128) NOT NULL,
    `ip_address` VARCHAR(45) NOT NULL,
    `timestamp` TIMESTAMP DEFAULT CURRENT_TIMESTAMP NOT NULL,
    `data` BLOB NOT NULL,
    KEY `ci_sessions_timestamp` (`timestamp`)
);

-- Create user table
CREATE TABLE IF NOT EXISTS `user` (
    `user_id` INT AUTO_INCREMENT PRIMARY KEY,
    `username` VARCHAR(255) NOT NULL,
    `password` VARCHAR(255) NOT NULL,
    `email` VARCHAR(255) NOT NULL UNIQUE,
    `created_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP NOT NULL,
    `points` INT DEFAULT 0
);

-- Create question table
CREATE TABLE IF NOT EXISTS `question` (
    `question_id` INT AUTO_INCREMENT PRIMARY KEY,
    `title` VARCHAR(255) NOT NULL,
    `content` TEXT NOT NULL,
    `user_id` INT,
    `created_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP NOT NULL,
    `vote_count` INT DEFAULT 0,
    FOREIGN KEY (`user_id`) REFERENCES `user`(`user_id`)
);

-- Create the answer table
CREATE TABLE IF NOT EXISTS `answer` (
    `answer_id` INT AUTO_INCREMENT PRIMARY KEY,
    `content` TEXT NOT NULL,
    `question_id` INT,
    `user_id` INT,
    `created_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP NOT NULL,
    `vote_count` INT DEFAULT 0,
    FOREIGN KEY (`question_id`) REFERENCES `question`(`question_id`),
    FOREIGN KEY (`user_id`) REFERENCES `user`(`user_id`)
);

-- Create the tag table
CREATE TABLE IF NOT EXISTS `tag` (
    `tag_id` INT PRIMARY KEY AUTO_INCREMENT,
    `name` VARCHAR(255) NOT NULL
);

-- Create the question_tag table
CREATE TABLE IF NOT EXISTS `question_tag` (
    `question_id` INT,
    `tag_id` INT,
    PRIMARY KEY (`question_id`, `tag_id`),
    FOREIGN KEY (`question_id`) REFERENCES `question`(`question_id`),
    FOREIGN KEY (`tag_id`) REFERENCES `tag`(`tag_id`)
);
