<?php
/**
 * Test file for the sybase_ase "layer"
 *
 * @package Yadal
 */

include('../class.Yadal.php');

// the test table (can be any table)
$table = "iyas_grandtest";

echo "<pre>";

// create a new connection
$db = newYadal("db_name", "sybase_ase");
$db -> connect( 'host_name', 'user_name', 'user_password' );

// start the test secuence
include( 'test.php' );

?>
