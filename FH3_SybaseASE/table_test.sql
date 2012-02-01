 	CREATE TABLE table_test (
 	id numeric IDENTITY,
 	unik varchar(25) NULL,
 	comp_unik1 varchar(25) NULL,
 	comp_unik2 varchar(25) NULL,
 	intwajib int NOT NULL,
 	PRIMARY KEY (id),
   	UNIQUE (unik),
   	UNIQUE (comp_unik1, comp_unik2)
 	);
