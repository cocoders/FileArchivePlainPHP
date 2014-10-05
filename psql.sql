DROP TABLE IF EXISTS archive_file;
DROP TABLE IF EXISTS archive;

CREATE TABLE archive (
    name VARCHAR(255) PRIMARY KEY,
    is_uploaded BOOLEAN
);

CREATE TABLE archive_file (
    id SERIAL,
    path VARCHAR(255),
    archive_name VARCHAR(255) NOT NULL,
    CONSTRAINT achives
        FOREIGN KEY (archive_name) REFERENCES archive(name)
        ON DELETE RESTRICT
);
