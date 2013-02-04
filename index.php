<?php
    // main index file for SiteOfAwesome -> If this is reached there may
    // be a problem with the server config

if(defined("SOA_ROOT"))
{
    // this should not occur
    soa_error("Index.php included by something.");
}

// otherwise:: perhaps server rewrite disabled
require("page.php");
?>
