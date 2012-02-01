<?php
include("../FH/class.dbFormHandler.php"); 

/**
 * Sample CREATE TABLE
 *
 *
 *	CREATE TABLE table_test (
 *	id numeric IDENTITY,
 *	unik varchar(25) NULL,
 *	comp_unik1 varchar(25) NULL,
 *	comp_unik2 varchar(25) NULL,
 *	intwajib int NOT NULL,
 *	PRIMARY KEY (id),
 *  UNIQUE (unik),
 *  UNIQUE (comp_unik1, comp_unik2)
 *	);
 */

// create a new form 
$form = new dbFormHandler(); 

// set the database info 
$form -> dbInfo( "db_name", "table_name", "sybase_ase" ); 
$form -> dbConnect( "host_name", "user_name", "user_password" );

$form -> textField("unik","unik",_FH_STRING);
$form -> textField("comp_unik1","comp_unik1",_FH_STRING);
$form -> textField("comp_unik2","comp_unik2",_FH_STRING);
$form -> textField("intwajib","intwajib",FH_DIGIT);
$form -> submitButton("Submit");

$form -> onSaved("doSomething"); 

// display the form 
$form -> flush(); 

// the data handler... 
// NOTE the two arguments!!!! 
function doSomething( $id, $data )  
{ 
    echo "Saved.";
} 

?>