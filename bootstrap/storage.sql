CREATE TABLE Namespace (
	name TEXT PRIMARY KEY,
	rootcategory INTEGER
);

CREATE TABLE Category (
	id INTEGER PRIMARY KEY,
	name TEXT,
	parent INTEGER,
	sortkey INTEGER,
	UNIQUE(parent,sortkey)
);

CREATE TABLE PageCategory (
	page TEXT,
	category INTEGER,
	sortkey INTEGER,
	UNIQUE(category,sortkey)
);

CREATE TABLE LinkCategory (
	link TEXT,
	name TEXT,
	category INTEGER,
	sortkey INTEGER,
	UNIQUE(category,sortkey)
);

INSERT INTO Category (name) VALUES ("Website");
INSERT INTO Namespace (name,rootcategory) VALUES ("website",last_insert_rowid());
