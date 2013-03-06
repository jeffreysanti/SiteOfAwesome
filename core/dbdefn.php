<?php
// defines database structures
// SEE: soa.sql for more info
/*
CREATE TABLE IF NOT EXISTS `soa_users` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `username` varchar(20) NOT NULL,
  `password` varchar(32) NOT NULL,
  `type` smallint(6) NOT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=latin1 AUTO_INCREMENT=1 ;
*/

function CreateDBTablesMySql(PDO $dbConnection, $prefix)
{
    try{
	$dbConnection->exec(					    // USERS
	    "CREATE TABLE IF NOT EXISTS `".$prefix."_users` (
	    `id` int(11) NOT NULL AUTO_INCREMENT,
	    `username` varchar(20) NOT NULL,
	    `password` varchar(32) NOT NULL,
	    `type` smallint(6) NOT NULL,
	    PRIMARY KEY (`id`)
	    )	ENGINE=InnoDB DEFAULT CHARSET=latin1 AUTO_INCREMENT=1");
        
        $dbConnection->exec(					    // USERS
	    "CREATE TABLE IF NOT EXISTS `soa_siteparam` (
                `paramname` varchar(10) NOT NULL,
                `keyval` int(11) NOT NULL,
                `val` varchar(32) NOT NULL
                 ) ENGINE=InnoDB DEFAULT CHARSET=latin1;");
        
        
    }catch(PDOException $e){
	return $e->getMessage();
    }
    return "";
}

function EstablishDataBaseConnection() // establish database connection with prepared queries
{
    try {
	$dbConnection = new PDO("mysql:dbname=".DB_NAME.";host=".DB_HOST.";charset=utf8", DB_USER, DB_PASS);
	$dbConnection->setAttribute(PDO::ATTR_EMULATE_PREPARES, false);
	$dbConnection->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
    }catch(PDOException $e)
    {
	soa_error("Database Connection Failure [Check config.php]: ".$e->getMessage());
    }
    return $dbConnection;
}

?>
