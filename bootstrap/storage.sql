CREATE TABLE Container (
	id TEXT PRIMARY KEY,
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
	id INTEGER PRIMARY KEY,
	link TEXT,
	name TEXT,
	newwindow BOOLEAN,
	category INTEGER,
	sortkey INTEGER,
	UNIQUE(category,sortkey)
);

CREATE TABLE User (
	id TEXT PRIMARY KEY,
	password TEXT,
	name TEXT
);

CREATE TABLE Access (
	id TEXT PRIMARY KEY,
	name TEXT,
	description TEXT
);

CREATE TABLE UserAccess (
	user TEXT,
	access TEXT,
	UNIQUE (user,access)
);

CREATE TABLE Permission (
	access TEXT,
	section TEXT,
	read INTEGER,
	write INTEGER,
	edit INTEGER,
	remove INTEGER,
	UNIQUE (access,section)
);

INSERT INTO User (id,password,name) VALUES ('blueprintit','ab9debd6b50c6d5b64c64f2c93a74580','Blueprint IT');
INSERT INTO Access (id,name,description) VALUES ('root',NULL,NULL);
INSERT INTO Access (id,name,description) VALUES ('admin','Full Access','Provides full control over the website.');
INSERT INTO UserAccess (user,access) VALUES ('blueprintit','root');

INSERT INTO Permission (access,section,read,write,edit,remove) VALUES ('admin','users',1,1,1,1);
INSERT INTO Permission (access,section,read,write,edit,remove) VALUES ('admin','categories',1,1,1,1);
INSERT INTO Permission (access,section,read,write,edit,remove) VALUES ('admin','documents',1,1,1,1);
INSERT INTO Permission (access,section,read,write,edit,remove) VALUES ('admin','filemanager',1,1,1,1);
INSERT INTO Permission (access,section,read,write,edit,remove) VALUES ('admin','statistics',1,1,1,1);
INSERT INTO Permission (access,section,read,write,edit,remove) VALUES ('admin','settings',1,1,1,1);
