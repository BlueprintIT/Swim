CREATE TABLE Container (
	id TEXT PRIMARY KEY,
	date INTEGER,
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

INSERT INTO Category (name) VALUES ("Website");
INSERT INTO Container (id,rootcategory) VALUES ("id",last_insert_rowid());
INSERT INTO User (id,password,name) VALUES ('blueprintit','ab9debd6b50c6d5b64c64f2c93a74580','Blueprint IT');
INSERT INTO Access (id,name,description) VALUES ('root',NULL,NULL);
INSERT INTO Access (id,name,description) VALUES ('admin','Full Access','Provides full control over the website.');
INSERT INTO Access (id,name,description) VALUES ('sales','Sales','Controls the sales aspect of the website.');
INSERT INTO Access (id,name,description) VALUES ('dispatch','Dispatcher','Allows access to the shipping and read access to customers and orders.');
INSERT INTO Access (id,name,description) VALUES ('invoice','Invoicer','Allows full access to orders and customers.');
INSERT INTO UserAccess (user,access) VALUES ('blueprintit','root');

INSERT INTO Permission (access,section,read,write,edit,remove) VALUES ('admin','users',1,1,1,1);
INSERT INTO Permission (access,section,read,write,edit,remove) VALUES ('admin','products',1,1,1,1);
INSERT INTO Permission (access,section,read,write,edit,remove) VALUES ('admin','categories',1,1,1,1);
INSERT INTO Permission (access,section,read,write,edit,remove) VALUES ('admin','documents',1,1,1,1);
INSERT INTO Permission (access,section,read,write,edit,remove) VALUES ('admin','customers',1,1,1,1);
INSERT INTO Permission (access,section,read,write,edit,remove) VALUES ('admin','shipping',1,1,1,1);
INSERT INTO Permission (access,section,read,write,edit,remove) VALUES ('admin','gateways',1,1,1,1);
INSERT INTO Permission (access,section,read,write,edit,remove) VALUES ('admin','filemanager',1,1,1,1);
INSERT INTO Permission (access,section,read,write,edit,remove) VALUES ('admin','statistics',1,1,1,1);
INSERT INTO Permission (access,section,read,write,edit,remove) VALUES ('admin','settings',1,1,1,1);
INSERT INTO Permission (access,section,read,write,edit,remove) VALUES ('admin','orders',1,1,1,1);

INSERT INTO Permission (access,section,read,write,edit,remove) VALUES ('sales','products',1,1,1,1);
INSERT INTO Permission (access,section,read,write,edit,remove) VALUES ('sales','categories',1,1,1,1);
INSERT INTO Permission (access,section,read,write,edit,remove) VALUES ('sales','customers',1,1,1,1);
INSERT INTO Permission (access,section,read,write,edit,remove) VALUES ('sales','statistics',1,1,1,1);
INSERT INTO Permission (access,section,read,write,edit,remove) VALUES ('sales','orders',1,1,1,1);

INSERT INTO Permission (access,section,read,write,edit,remove) VALUES ('dispatch','customers',1,0,0,0);
INSERT INTO Permission (access,section,read,write,edit,remove) VALUES ('dispatch','shipping',1,1,1,1);
INSERT INTO Permission (access,section,read,write,edit,remove) VALUES ('dispatch','orders',1,0,0,0);

INSERT INTO Permission (access,section,read,write,edit,remove) VALUES ('invoice','customers',1,1,1,1);
INSERT INTO Permission (access,section,read,write,edit,remove) VALUES ('invoice','orders',1,1,1,1);
