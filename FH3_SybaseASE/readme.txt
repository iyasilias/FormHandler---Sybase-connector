Change class.yadal.php.
line 56+
Add:
	  // Sybase
	  case 'sybase_ase':
	    include_once dirname(__FILE__).'/class.SybaseASE.php';
	    return new SybaseASE( $database );
	    break;

Copy class.SybaseASE.php in the yadal folder.

Make your Sybase ASE form like demo/index.php.

You need the php_sybase_ct.dll to connect to Sybase ASE Databases.