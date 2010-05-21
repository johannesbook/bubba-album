BEGIN;

ALTER TABLE access DROP INDEX ,
                   DROP COLUMN user,
                   ADD COLUMN username varchar(255) NOT NULL,
                   ADD PRIMARY KEY (username, album),
                   ENGINE=InnoDB DEFAULT CHARACTER SET utf8;

ALTER TABLE album ADD COLUMN modified timestamp on update CURRENT_TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
                  ADD COLUMN created timestamp NOT NULL DEFAULT '0000-00-00 00:00:00';

ALTER TABLE sessions ENGINE=InnoDB DEFAULT CHARACTER SET utf8;

DROP TABLE users;


COMMIT;

