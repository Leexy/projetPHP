-- As root:
-- CREATE DATABASE `battleship` CHARACTER SET utf8 COLLATE utf8_general_ci;
-- GRANT ALL ON `battleship`.* TO `battleship`@localhost IDENTIFIED BY 'battleship';
-- FLUSH PRIVILEGES;

CREATE TABLE IF NOT EXISTS users (
    id INTEGER NOT NULL AUTO_INCREMENT,
    role VARCHAR(50) DEFAULT 'member',
    email VARCHAR(255) NOT NULL,
    password VARCHAR(255) NULL,
    display_name VARCHAR(255) NOT NULL,
    PRIMARY KEY (id),
    UNIQUE KEY unique_email (email),
    UNIQUE KEY unique_display_name (display_name)
) ENGINE=InnoDB AUTO_INCREMENT=0 DEFAULT CHARSET=utf8;

CREATE TABLE IF NOT EXISTS games (
    id INTEGER NOT NULL AUTO_INCREMENT,
    user1_id INTEGER NOT NULL,
    user2_id INTEGER,
    state VARCHAR(75) NOT NULL,
    playing_user_id INTEGER,
    last_hit_id INTEGER,
    winner_id INTEGER,
    PRIMARY KEY(id),
    FOREIGN KEY (user1_id) REFERENCES users(id),
    FOREIGN KEY (user2_id) REFERENCES users(id),
    FOREIGN KEY (playing_user_id) REFERENCES users(id),
    FOREIGN KEY (winner_id) REFERENCES users(id)
) ENGINE=InnoDB AUTO_INCREMENT=0 DEFAULT CHARSET=utf8;

CREATE TABLE IF NOT EXISTS ships (
    id INTEGER NOT NULL AUTO_INCREMENT,
    size INTEGER NOT NULL,
    x INTEGER NOT NULL,
    y INTEGER NOT NULL,
    orientation ENUM('HORIZONTAL','VERTICAL') NOT NULL,
    user_id INTEGER NOT NULL,
    game_id INTEGER NOT NULL,
    PRIMARY KEY (id),
    FOREIGN KEY (user_id) REFERENCES users(id),
    FOREIGN KEY (game_id) REFERENCES games(id),
    UNIQUE KEY (x,y,user_id,game_id)
) ENGINE=InnoDB AUTO_INCREMENT=0 DEFAULT CHARSET=utf8;

CREATE TABLE IF NOT EXISTS hits (
    id INTEGER NOT NULL AUTO_INCREMENT,
    x INTEGER NOT NULL,
    y INTEGER NOT NULL,
    success tinyint(4) NOT NULL,
    game_id INTEGER NOT NULL,
    user_id INTEGER NOT NULL,
    PRIMARY KEY (id),
    FOREIGN KEY (game_id) REFERENCES games(id),
    FOREIGN KEY (user_id) REFERENCES users(id),
    UNIQUE KEY (x,y,game_id,user_id)
) ENGINE=InnoDB AUTO_INCREMENT=0 DEFAULT CHARSET=utf8;
ALTER TABLE games ADD CONSTRAINT FOREIGN KEY (last_hit_id) REFERENCES hits(id);
