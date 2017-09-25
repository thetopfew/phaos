<?php 
// Show all information, defaults to INFO_ALL
#phpinfo();

// Show just the module information.
// phpinfo(8) yields identical results.
#phpinfo(INFO_MODULES); 

// Prints e.g. 'Current PHP version'
echo 'This application is currently running on PHP version: ' . phpversion();
?>
