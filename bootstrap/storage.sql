CREATE TABLE Namespace (
	name TEXT PRIMARY KEY,
	rootcategory INTEGER
);

CREATE TABLE Category (
	id INTEGER PRIMARY KEY,
	name TEXT,
	parent INTEGER,
	sortkey INTEGER
);

CREATE TABLE PageCategory (
	page INTEGER,
	category INTEGER,
	sortkey INTEGER
);

CREATE TABLE LinkCategory (
	link TEXT,
	category INTEGER,
	sortkey INTEGER
);

INSERT INTO Category (name) VALUES ("Website");
INSERT INTO Namespace (name,rootcategory) VALUES ("website",last_insert_rowid());
