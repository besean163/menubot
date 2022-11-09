sudo mysql

CREATE DATABASE `menubot`;

CREATE USER 'menubot'@'localhost' IDENTIFIED WITH mysql_native_password BY 'menubot_pass';

GRANT ALL PRIVILEGES ON menubot.* TO 'menubot'@'localhost' WITH GRANT OPTION;

FLUSH PRIVILEGES;