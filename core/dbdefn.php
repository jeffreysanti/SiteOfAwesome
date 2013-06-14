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
            `owner` int(11) NOT NULL DEFAULT '-1',
            `name` varchar(30) NOT NULL,
	    PRIMARY KEY (`id`)
	    )	ENGINE=InnoDB DEFAULT CHARSET=latin1 AUTO_INCREMENT=1");
        
        $dbConnection->exec(					    // USERS
	    "CREATE TABLE IF NOT EXISTS `".$prefix."_siteparam` (
                `paramname` varchar(10) NOT NULL,
                `keyval` int(11) NOT NULL,
                `val` varchar(32) NOT NULL
                 ) ENGINE=InnoDB DEFAULT CHARSET=latin1;");
        
        $dbConnection->exec(					    // GROUPS
           "
                CREATE TABLE IF NOT EXISTS `".$prefix."_groups` (
               `id` int(11) NOT NULL AUTO_INCREMENT,
               `name` varchar(20) NOT NULL,
               `owner` int(11) NOT NULL,
               PRIMARY KEY (`id`)
             ) ENGINE=InnoDB DEFAULT CHARSET=latin1 AUTO_INCREMENT=1");
        
        $dbConnection->exec(					    // GROUP-Client connection
           "
                CREATE TABLE IF NOT EXISTS `".$prefix."_grp_cl` (
                    `uid` int(11) NOT NULL,
                    `gid` int(11) NOT NULL
                    ) ENGINE=InnoDB DEFAULT CHARSET=latin1");
        
        $dbConnection->exec(					    // Client info
           "CREATE TABLE IF NOT EXISTS `".$prefix."_client_info` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
    `uid` int(11) NOT NULL,
  `ft` int(11) NOT NULL,
  `field` varchar(30) NOT NULL,
  `info` text NOT NULL,
  `public` tinyint(4) NOT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=latin1 AUTO_INCREMENT=1");
        
        $dbConnection->exec(					    // Client info
           "CREATE TABLE IF NOT EXISTS `".$prefix."_art` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `uid` int(11) NOT NULL,
  `name` varchar(40) NOT NULL,
  `text` text NOT NULL,
  `pub` tinyint(4) NOT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=latin1 AUTO_INCREMENT=1 ;");
        
          $dbConnection->exec(					    // tags
           "CREATE TABLE IF NOT EXISTS `".$prefix."_tags` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `uid` int(11) NOT NULL,
  `text` varchar(40) NOT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=latin1 AUTO_INCREMENT=1 ;");
          
           $dbConnection->exec(					    // tag connector
           "CREATE TABLE IF NOT EXISTS `".$prefix."_tagcon` (
  `tid` int(11) NOT NULL,
  `aid` int(11) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=latin1;");
           
           $dbConnection->exec(					    // article permissions
           "CREATE TABLE IF NOT EXISTS `".$prefix."_acon` (
  `aid` int(11) NOT NULL,
  `type` tinyint(4) NOT NULL,
  `id` int(11) NOT NULL
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
        $dbConnection->setAttribute(PDO::MYSQL_ATTR_USE_BUFFERED_QUERY, true);
    }catch(PDOException $e)
    {
	soa_error("Database Connection Failure [Check config.php]: ".$e->getMessage());
    }
    return $dbConnection;
}

?>
