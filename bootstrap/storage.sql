CREATE TABLE User (
	id VARCHAR(20) PRIMARY KEY,
	password CHAR(32),
	name TEXT
);

CREATE TABLE Access (
	id VARCHAR(20) PRIMARY KEY,
	name VARCHAR(100),
	description TEXT
);

CREATE TABLE UserAccess (
	user VARCHAR(20),
	access VARCHAR(20),
	UNIQUE (user,access)
);

CREATE TABLE Permission (
	access VARCHAR(20),
	section VARCHAR(20),
	canread INTEGER,
	canwrite INTEGER,
	canedit INTEGER,
	canremove INTEGER,
	UNIQUE (access,section)
);

CREATE TABLE Item (
	id INTEGER AUTO_INCREMENT PRIMARY KEY,
	item INTEGER,
	variant VARCHAR(20),
	version INTEGER,
	modified INTEGER,
	owner VARCHAR(20),
	current INTEGER,
	complete INTEGER,
	class VARCHAR(30),
	UNIQUE (item,variant,version)
);

CREATE TABLE Field (
	item INTEGER,
	field VARCHAR(30),
	intValue INTEGER,
	textValue TEXT,
	dateValue INTEGER,
	UNIQUE (item,field)
);

CREATE TABLE Sequence (
	parent INTEGER,
	field VARCHAR(30),
	item INTEGER,
	position INTEGER,
	UNIQUE (parent,field,position)
);

INSERT INTO User (id,password,name) VALUES ('blueprintit','ab9debd6b50c6d5b64c64f2c93a74580','Blueprint IT');
INSERT INTO Access (id,name,description) VALUES ('root',NULL,NULL);
INSERT INTO Access (id,name,description) VALUES ('admin','Full Access','Provides full control over the website.');
INSERT INTO UserAccess (user,access) VALUES ('blueprintit','root');

INSERT INTO Permission (access,section,canread,canwrite,canedit,canremove) VALUES ('admin','users',1,1,1,1);
INSERT INTO Permission (access,section,canread,canwrite,canedit,canremove) VALUES ('admin','categories',1,1,1,1);
INSERT INTO Permission (access,section,canread,canwrite,canedit,canremove) VALUES ('admin','documents',1,1,1,1);
INSERT INTO Permission (access,section,canread,canwrite,canedit,canremove) VALUES ('admin','filemanager',1,1,1,1);
INSERT INTO Permission (access,section,canread,canwrite,canedit,canremove) VALUES ('admin','statistics',1,1,1,1);
INSERT INTO Permission (access,section,canread,canwrite,canedit,canremove) VALUES ('admin','settings',1,1,1,1);
