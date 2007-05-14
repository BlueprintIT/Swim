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
	root INTEGER,
	section VARCHAR(20),
	class VARCHAR(30),
	path VARCHAR(50),
	created INTEGER,
	archived INTEGER
);

CREATE TABLE ItemVariant (
	id INTEGER AUTO_INCREMENT PRIMARY KEY,
	item INTEGER,
	variant VARCHAR(20),
	UNIQUE (item,variant)
);

CREATE TABLE VariantVersion (
	id INTEGER AUTO_INCREMENT PRIMARY KEY,
	itemvariant INTEGER,
	version INTEGER,
	view VARCHAR(30),
	created INTEGER,
	modified INTEGER,
	published INTEGER,
	owner VARCHAR(20),
	current INTEGER,
	complete INTEGER,
	UNIQUE (itemvariant,version)
);

CREATE TABLE Field (
	itemversion INTEGER,
	basefield VARCHAR(30),
	pos INTEGER,
	field VARCHAR(30),
	intValue INTEGER,
	textValue TEXT,
	dateValue INTEGER,
	UNIQUE (itemversion,basefield,pos,field)
);

CREATE TABLE Sequence (
	parent INTEGER,
	field VARCHAR(30),
	item INTEGER,
	position INTEGER,
	UNIQUE (parent,field,position)
);

CREATE TABLE File (
	itemversion INTEGER,
	file VARCHAR(100),
	description VARCHAR(200),
	UNIQUE (itemversion,file)
);

CREATE TABLE OptionSet (
	id INTEGER AUTO_INCREMENT PRIMARY KEY,
	optionset VARCHAR(30),
	name VARCHAR(100),
	value VARCHAR(100),
	UNIQUE (optionset,value)
);

CREATE TABLE Keywords (
	word VARCHAR(30),
	item INTEGER,
	weight FLOAT,
	UNIQUE (word, item)
);

CREATE TABLE Mailing (
	id VARCHAR(30) PRIMARY KEY,
	section VARCHAR(20),
	contacts INTEGER,
	lastsent INTEGER,
	intro TEXT
);

INSERT INTO User (id,password,name) VALUES ('blueprintit','ab9debd6b50c6d5b64c64f2c93a74580','Blueprint IT');
INSERT INTO Access (id,name,description) VALUES ('root',NULL,NULL);
INSERT INTO Access (id,name,description) VALUES ('admin','Full Access','Provides full control over the website.');
INSERT INTO Access (id,name,description) VALUES ('content','Content Editor','Allows editing of website content.');
INSERT INTO Access (id,name,description) VALUES ('mailing','Mailer','Allows creating and sending mails.');
INSERT INTO UserAccess (user,access) VALUES ('blueprintit','root');

INSERT INTO Permission (access,section,canread,canwrite,canedit,canremove) VALUES ('admin','users',1,1,1,1);
INSERT INTO Permission (access,section,canread,canwrite,canedit,canremove) VALUES ('admin','categories',1,1,1,1);
INSERT INTO Permission (access,section,canread,canwrite,canedit,canremove) VALUES ('admin','documents',1,1,1,1);
INSERT INTO Permission (access,section,canread,canwrite,canedit,canremove) VALUES ('admin','filemanager',1,1,1,1);
INSERT INTO Permission (access,section,canread,canwrite,canedit,canremove) VALUES ('admin','statistics',1,1,1,1);
INSERT INTO Permission (access,section,canread,canwrite,canedit,canremove) VALUES ('admin','settings',1,1,1,1);
INSERT INTO Permission (access,section,canread,canwrite,canedit,canremove) VALUES ('admin','contacts',1,1,1,1);
INSERT INTO Permission (access,section,canread,canwrite,canedit,canremove) VALUES ('content','users',1,1,1,1);
INSERT INTO Permission (access,section,canread,canwrite,canedit,canremove) VALUES ('content','categories',1,1,1,1);
INSERT INTO Permission (access,section,canread,canwrite,canedit,canremove) VALUES ('content','documents',1,1,1,1);
INSERT INTO Permission (access,section,canread,canwrite,canedit,canremove) VALUES ('content','filemanager',1,1,1,1);
INSERT INTO Permission (access,section,canread,canwrite,canedit,canremove) VALUES ('content','statistics',1,1,1,1);
INSERT INTO Permission (access,section,canread,canwrite,canedit,canremove) VALUES ('content','settings',1,1,1,1);
INSERT INTO Permission (access,section,canread,canwrite,canedit,canremove) VALUES ('mailing','contacts',1,1,1,1);
