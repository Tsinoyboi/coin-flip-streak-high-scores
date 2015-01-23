CREATE TABLE IF NOT EXISTS user
(
    id BIGINT(20) UNSIGNED NOT NULL AUTO_INCREMENT,
    username VARCHAR(20) NOT NULL,
    password VARCHAR(255) NOT NULL,
    PRIMARY KEY (id),
    UNIQUE KEY (id),
    UNIQUE KEY (username)
) ENGINE=InnoDB CHARSET=utf8 COLLATE=utf8_unicode_ci;

CREATE TABLE IF NOT EXISTS face
(
    id BIGINT(20) UNSIGNED NOT NULL AUTO_INCREMENT,
    code CHAR(1) NOT NULL,
    name CHAR(5) NOT NULL,
    PRIMARY KEY (id),
    UNIQUE KEY (id)
) ENGINE=InnoDB CHARSET=utf8 COLLATE=utf8_unicode_ci;

INSERT INTO face
(
    code,
    name
) VALUES
(
    'H',
    'heads'
),
(
    'T',
    'tails'
);

CREATE TABLE IF NOT EXISTS streak
(
    id BIGINT(20) UNSIGNED NOT NULL AUTO_INCREMENT,
    user_id BIGINT(20) UNSIGNED NOT NULL,
    face_id BIGINT(20) UNSIGNED NOT NULL,
    PRIMARY KEY (id),
    UNIQUE KEY (id),
    FOREIGN KEY (user_id) REFERENCES user (id),
    FOREIGN KEY (face_id) REFERENCES face (id)
) ENGINE=InnoDB CHARSET=utf8 COLLATE=utf8_unicode_ci;

CREATE TABLE IF NOT EXISTS flip
(
    id BIGINT(20) UNSIGNED NOT NULL AUTO_INCREMENT,
    streak_id BIGINT(20) UNSIGNED NOT NULL,
    time_flipped DATETIME NOT NULL,
    micro_time INT(10) UNSIGNED NOT NULL,
    PRIMARY KEY (id),
    UNIQUE KEY (id),
    FOREIGN KEY (streak_id) REFERENCES streak (id)
) ENGINE=InnoDB CHARSET=utf8 COLLATE=utf8_unicode_ci;
