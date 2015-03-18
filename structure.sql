CREATE TABLE IF NOT EXISTS users (
    id INTEGER NOT NULL AUTO_INCREMENT,
    role VARCHAR(50) DEFAULT 'member',
    email VARCHAR(255) NOT NULL,
    password VARCHAR(255) NULL,
    display_name VARCHAR(255) NULL,
    created_at DATETIME DEFAULT CURRENT_TIMESTAMP,
    updated_at DATETIME ON UPDATE CURRENT_TIMESTAMP,
    PRIMARY KEY (id),
    UNIQUE KEY unique_email (email)
) ENGINE=InnoDB AUTO_INCREMENT=0 DEFAULT CHARSET=utf8;

CREATE TABLE games (
    id INTEGER NOT NULL AUTO_INCREMENT,
    user1_id INTEGER NOT NULL,
    user2_id INTEGER,
    state VARCHAR(75) NOT NULL,
    winner_id INTEGER,
    created_at DATETIME DEFAULT CURRENT_TIMESTAMP,
    updated_at DATETIME ON UPDATE CURRENT_TIMESTAMP,
    PRIMARY KEY(id),
    FOREIGN KEY (user1_id) REFERENCES users(id),
    FOREIGN KEY (user2_id) REFERENCES users(id),
    FOREIGN KEY (winner_id) REFERENCES users(id)
) ENGINE=InnoDB AUTO_INCREMENT=0 DEFAULT CHARSET=utf8;

CREATE TABLE ships (
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

CREATE TABLE ship_hits (
    id INTEGER NOT NULL AUTO_INCREMENT,
    x INTEGER NOT NULL,
    y INTEGER NOT NULL,
    ship_id INTEGER NOT NULL,
    PRIMARY KEY (id),
    FOREIGN KEY (ship_id) REFERENCES ships(id),
    UNIQUE KEY (x,y,ship_id)
) ENGINE=InnoDB AUTO_INCREMENT=0 DEFAULT CHARSET=utf8;