<?php
    /* Aung Paing Soe and Yeng Her
       12/17/2024, CS 174-03
       Final Project - Decryptiod
    */

    // Miscellaneous setup
    require_once 'loginDB.php';
    mysqli_report(MYSQLI_REPORT_ERROR | MYSQLI_REPORT_STRICT);
    session_start();

    const MONTH = 2592000;
    const ERROR_MESSAGE = <<<'ERROR'
    <p>Something went wrong... Press this <a href="https://www.youtube.com/watch?v=dQw4w9WgXcQ" target="_blank">link</a> to troubleshoot.</p>
    ERROR;

    // Sanitization Function for both HTML & SQL injections
    function sanitization($conn, $var) {
        $var = $conn->real_escape_string($var);
        $var = stripslashes($var);
        $var = strip_tags($var);
        $var = htmlentities($var);
        return $var;
    }

?>