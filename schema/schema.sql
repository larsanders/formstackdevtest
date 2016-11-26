
/*

    SCHEMA FOR USERS TABLE

*/

CREATE TABLE IF NOT EXISTS `my_app`.`users` (
`u_id` INT( 10 ) UNIQUE NOT NULL AUTO_INCREMENT PRIMARY KEY ,
`email` VARCHAR( 100 ) UNIQUE NOT NULL ,
`first_name` VARCHAR( 40 ) NOT NULL ,
`last_name` VARCHAR( 40 ) NOT NULL ,
`password` VARCHAR( 64 ) NOT NULL
) ENGINE = InnoDB;

/*

    SCHEMA FOR USERS TEST TABLE

*/

CREATE TABLE IF NOT EXISTS `my_app`.`users_test` (
`u_id` INT( 10 ) UNIQUE NOT NULL AUTO_INCREMENT PRIMARY KEY ,
`email` VARCHAR( 100 ) UNIQUE NOT NULL ,
`first_name` VARCHAR( 40 ) NOT NULL ,
`last_name` VARCHAR( 40 ) NOT NULL ,
`password` VARCHAR( 64 ) NOT NULL
) ENGINE = InnoDB;

/*
    TEST USER SEED DATA
*/

INSERT INTO `my_app`.`users_test` (`email`, `first_name`, `last_name`, `password`) VALUES ('a@b.io','test','user','ca6d8d3efe5ad313b5e0c6d4dab7f3cd3a1ad03b1eaf829cc6bd6b91106cf1e5');