CREATE DATABASE login_management;

CREATE DATABASE login_management_test;

CREATE TABLE users(
    id VARCHAR(100) PRIMARY KEY,
    name VARCHAR(100) NOT NULL,
    password VARCHAR(100) NOT NULL
)ENGINE InnoDB;

CREATE TABLE sessions(
    id VARCHAR(100) PRIMARY KEY,
    id_user VARCHAR(100) NOT NULL
)ENGINE InnoDB;

ALTER TABLE sessions
ADD CONSTRAINT fk_sessions_user
FOREIGN KEY (id_user) REFERENCES users(id);