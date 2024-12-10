<?php
// Aung Paing Soe and Yeng Her
// 12.8.24
// CS174 Final Decryptiod

// Miscellaneous setup
require_once 'loginDB.php';
mysqli_report(MYSQLI_REPORT_ERROR | MYSQLI_REPORT_STRICT);
session_start();

const MONTH = 2592000;
const ERROR_MESSAGE = <<<'ERROR'
<p>Something went wrong... Press this <a href="https://www.youtube.com/watch?v=dQw4w9WgXcQ" target="_blank">link</a> to troubleshoot.</p>
ERROR;

function mysql_entities_fix_string($connection, $string){
        return htmlentities(mysql_fix_string($connection, $string));
}

function mysql_fix_string($connection, $string){
        $string = stripslashes($string);
        return $connection->real_escape_string($string);
}

?>
