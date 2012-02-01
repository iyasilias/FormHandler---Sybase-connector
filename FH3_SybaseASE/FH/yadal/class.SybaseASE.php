<?php
sybase_min_server_severity(20);

/**
 * Yadal interface for the SybaseASE database type
 *
 * @package Yadal
 */


/**
 * class SybaseASE
 *
 * Yadal - Yet Another Database Abstraction Layer
 * SybaseASE class
 *
 * @author Muhammad Iyas
 * @package Yadal
 */
class SybaseASE extends Yadal
{
    /**
     * SybaseASE::Sybase()
     *
     * Constructor: set the database we should be using
     *
     * @param string $db: The database which should be used
     * @author Muhammad Iyas
     */
    function SybaseASE( $db )
    {
        $this->Yadal( $db );
        $this->_quoteNumbers = false;
        $this->_nameQuote = '';
    }

    /**
     * SybaseASE::connect()
     *
     * Make a connection with the database and
     * select the database.
     *
     * @param string host: the host to connect to
     * @param string username: the username which should be used to login
     * @param string password: the password which should be used to login
     * @return resource: The connection resource
     * @access public
     * @author Muhammad Iyas
     */
    function connect( $host = 'localhost', $username = '', $password = '' )
    {
    	// connect with the sybase database

    	$this->_conn = sybase_connect( $host, $username, $password );

    	// connection made?
    	if( $this->_conn )
    	{
	
    		// select the database
    	    if(sybase_select_db( $this->_db, $this->_conn ))
    	    {
    	    	$this->_isConnected = true;

    	    	// return the connection resource
    			return $this->_conn;
    	    }
    	}

    	return false;
    }


    /**
     * SybaseASE::close()
     *
     * Close the connection
     *
     * @return bool
     * @access public
     * @author Muhammad Iyas
     */
    function close()
    {
        if( $this->_isConnected )
        {
            $this->_isConnected = false;
            return sybase_close( $this->_conn );
        }

        return true;
    }

    /**
     * SybaseASE::query()
     *
     * Execute the query
     *
     * @param string $query: the query which should be executed
     * @return resource
     * @access public
     * @author Muhammad Iyas
     */
	function query( $query ) 
	{ 
		//Have to remove LAST semi-colon from INSERT statement in class.dbFormHandler.php (Line: 1649)
		$query =  trim($query,";"); 

		$this->_lastQuery = $query; 

		return sybase_query( $query, $this->_conn ); 
     
	} 
  
    /**
     * SybaseASE::getInsertId()
     *
     * Get the id of the last inserted record
     *
     * @return int
     * @access public
     * @author Muhammad Iyas
     */
    function getInsertId( $table )
    {

		//

    }

    /**
     * SybaseASE::result()
     *
     * Return a specific result of a sql resource
     *
     * @param resource $sql: The sql where you want to get a result from
     * @param int $row: The row where you want a result from
     * @param string $field: The field which result you want
     * @return string
     * @access public
     * @author Muhammad Iyas
     */
    function result( $sql, $row = 0, $field = null )
    {
    	return sybase_result( $sql, $row, $field );
    }

    /**
     * SybaseASE::getError()
     *
     * Return the last error
     *
     * @return string
     * @access public
     * @author Muhammad Iyas
     */
    function getError()
    {
        return sybase_get_last_message();
    }

    /**
     * SybaseASE::getErrorNo()
     *
     * Return the error number
     *
     * @return int
     * @access public
     * @author Muhammad Iyas
     */
    function getErrorNo()
    {
       // 
    }

    /**
     * SybaseASE::recordCount()
     *
     * Return the number of records found by the query
     *
     * @param resource $sql: The resource which should be counted
     * @return int
     * @access public
     * @author Muhammad Iyas
     */
    function recordCount( $sql )
    {
        return sybase_num_rows( $sql );
    }

    /**
     * SybaseASE::getRecord()
     *
     * Fetch a record in assoc mode and return it
     *
     * @param resource $sql: The resource which should be used to retireve a record from
     * @return assoc array or false when there are no records left
     * @access public
     * @author Muhammad Iyas
     */
    function getRecord( $sql )
    {
        return sybase_fetch_assoc( $sql );
    }

    /**
     * SybaseASE::getFieldNames()
     *
     * Return the field names of the table
     *
     * @param string $table: The table where the field names should be collected from
     * @return array
     * @access public
     * @author Muhammad Iyas
     */
    function getFieldNames( $table )
    {
        $t = strtolower($table);

        // return the data from the cache if it exists
        if( isset( $this->_cache['fields'][$t] ) )
        {
            return $this->_cache['fields'][$t];
        }

        $result = array();

        $sql = $this->query("
			SELECT
			a.name AS columnname
			FROM syscolumns a 
			JOIN sysobjects b 
			ON (a.id = b.id) 
			WHERE b.type='U' 
			AND b.name = '".$table."'");
			//'U' user tables

        // query failed ?
		if( ! $sql )
		{
			trigger_error(
    		  "Could not fetch fieldnames of the table '".$table."'.\n".
    		  "Query: ".$this->getLastQuery()."\n".
    		  "Error: ".$this->getError(),
    		  E_USER_WARNING
    		);
    		return false;
		}

        while( $row = $this->getRecord($sql) )
        {
            $result[] = $row['columnname'];
        }

        // save the result in the cache
        $this->_cache['fields'][$t] = $result;

        return $result;
    }

    /**
     * SybaseASE::getTables()
     *
     * Return the tables from the database
     *
     * @return array
     * @access public
     * @author Muhammad Iyas
     */
    function getTables()
    {
        // return the data from the cache if it exists
        if( isset( $this->_cache['tables'] ) )
        {
            return $this->_cache['tables'];
        }

        $sql = $this->query("
			SELECT name 
			FROM sysobjects 
			WHERE type = 'U'"); //user defined table

        // query failed ?
        if( !$sql )
        {
            trigger_error(
    		  "Could not retrieve the tables from the database!\n".
    		  "Query: ".$this->getLastQuery()."\n".
    		  "Error: ".$this->getError(),
    		  E_USER_WARNING
    		);
    		return false;
        }

        // save the table names in an array and return them
        $result = array();
        $num = $this->recordCount( $sql );
        for( $i = 0; $i < $num; $i++ )
        {
            $result[] = $this->result( $sql, $i);
        }

        // save the result in the cache
    	$this->_cache['tables'] = $result;

        return $result;
    }

    /**
     * SybaseASE::getNotNullFields()
     *
     * Retrieve the fields that can not contain NULL
     *
     * @param string $table: The table which fields we should retrieve
     * @return array
     * @access public
     * @author Muhammad Iyas
     */
    function getNotNullFields ( $table )
    {
        $t = strtolower($table);

        // return the data from the cache if it exists
        if( isset( $this->_cache['notnull'][$t] ) )
        {
            return $this->_cache['notnull'][$t];
        }

    	$sql = $this->query("
			SELECT c.name AS columnName, c.status AS columnStatus 
			FROM sysobjects o
			INNER JOIN syscolumns c ON c.id = o.id
			WHERE o.type = 'U' AND o.name = '".$table."'" );

    	if( $sql )
    	{
    	    // save the not null fields in an array
	    	$result = array();
	    	while( $row = sybase_fetch_assoc( $sql ) ) {
	    		if( $row['columnStatus'] == 0 ) {
	    			$result[] = $row['columnName'];
	    		}
	    	}
    	}
    	else
    	{
    	    // display the error message when the not null fields could not be retrieved
    		trigger_error(
    		  "Could not retrieve the not-null-field from the table '".$table."'.\n".
    		  "Query: ".$this->getLastQuery()."\n".
    		  "Error: ".$this->getError(),
    		  E_USER_WARNING
    		);
    		return false;
    	}

    	// save the result in the cache
    	$this->_cache['notnull'][$t] = $result;

        return $result;
    }

    /**
     * SybaseASE::getFieldTypes()
     *
     * Retrieve the field types of the given table
     *
     * @param string $table: The table where we should fetch the fields and their types from
     * @return array
     * @access public
     * @author Muhammad Iyas
     */
function getFieldTypes( $table )
    {
        $t = strtolower($table);

        // return the data from the cache if it exists
        if( isset( $this->_cache['fieldtypes'][$t] ) )
        {
            return $this->_cache['fieldtypes'][$t];
        }

        // get the meta data
        $sql = $this->query("
          SELECT
            c.name AS fld,
            t.name AS type,
            c.length
          FROM syscolumns c
          JOIN systypes t ON t.usertype = c.usertype
          JOIN sysobjects o ON o.id = c.id
          WHERE o.name='".$table."'");

        // query failed ?
        if( !$sql )
        {
            trigger_error(
    		  "Could not fetch the meta data of the columns for table '".$table."'.\n".
    		  "Query: ".$this->getLastQuery()."\n".
    		  "Error: ".$this->getError(),
    		  E_USER_WARNING
    		);
    		return false;
        }

        // save the result in an array
        // TODO: load the default values in the 3rd place in the array
        $result = array();
        while( $row = $this->getRecord( $sql ) )
        {
            $result[ $row['fld'] ] = array(
              $row['type'],
              $row['length'],
              null // default value
            );
        }

        // save the result in the cache
    	$this->_cache['fieldtypes'][$t] = $result;

		return $result;
    }


    /**
     * SybaseASE::escapeString()
     *
     * Escape the string we are going to save from dangerous characters
     *
     * @param string $string: The string to escape
     * @return string
     * @access public
     * @author Muhammad Iyas
     */
    function escapeString( $string )
    {
	    // TODO: Not escaping from dangerous characters yet
		// If your mysql driver is working, you can use mysql_real_escape_string( $string ) instead
        return $string;
    }

    /**
     * SybaseASE::getPrKeys()
     *
     * Fetch the keys from the table
     *
     * @param string $table: The table where we should fetch the keys from
     * @return array of the keys which are found
     * @access public
     * @author Muhammad Iyas
     */
    function getPrKeys( $table )
    {
        $t = strtolower($table);

        // return the data from the cache if it exists
        if( isset( $this->_cache['keys'][$t] ) ) {
            return $this->_cache['keys'][$t];
        }

		$sql = $this->query("sp_helpconstraint ".$table);
		
        // query failed ?
		if( ! $sql )
		{
			trigger_error(
    		  "Could not fetch the PRIMARY fields for the table '".$table."'.\n".
    		  "Query: ".$this->getLastQuery()."\n".
    		  "Error: ".$this->getError(),
    		  E_USER_WARNING
    		);
    		return false;
		}

		while( $row = $this->getRecord($sql) )
		{
			if($c = $this->match("/^PRIMARY KEY INDEX \((.*?)\)/is", $row['definition'], 1))
			$keys = explode(",", str_replace(" ","",$c));
		}

		sybase_free_result($sql);

        // save the result in the cache
        $this->_cache['keys'][$t] = $keys;

        return $keys;
    }

    /**
     * SybaseASE::getUniqueFields()
     *
     * Fetch the unique fields from the table
     *
     * @param string $table: The table where the unique-value-field should be collected from
     * @return array: multidimensional array of the unique indexes on the table
     * @access public
     * @author Muhammad Iyas
     */
    function getUniqueFields( $table )
    {
        $t = strtolower( $table );

        // return the data from the cache if it exists
        if( isset( $this->_cache['unique'][$t] ) )
        {
            return $this->_cache['unique'][$t];
        }

        // fetch the unique fields
		$sql = $this->query("sp_helpconstraint ".$table);

        // query failed ?
		if( ! $sql )
		{
			trigger_error(
    		  "Could not fetch the unique fields for the table '".$table."'.\n".
    		  "Query: ".$this->getLastQuery()."\n".
    		  "Error: ".$this->getError(),
    		  E_USER_WARNING
    		);
    		return false;
		}

        // put the unique fields into an array and return them
		while( $row = $this->getRecord( $sql ) )
        {
 			if($c = $this->match("/^UNIQUE INDEX \((.*?)\)/is", $row['definition'], 1))
			$unique["'".$row['name']."'"] = explode(",", str_replace(" ","",$c));
		}

        sybase_free_result($sql);

        // save the result in the cache
        $this->_cache['unique'][$t] = $unique;

        return $unique;
    }
	
	function match($regex, $str, $i = 0)
	{
		if(preg_match($regex, $str, $match) == 1)
			return $match[$i];
		else
			return false;
	}

}

?>