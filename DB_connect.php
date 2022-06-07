<?php
$dbhost = "************";
$dbuser = "************";
$dbpassword = "************";
$dbname = "************";

$DB_connection = @new mysqli($dbhost, $dbuser, $dbpassword, $dbname) or die("Błąd połączenia z $dbname");
mysqli_set_charset($DB_connection, "UTF-8");
if ($DB_connection->connect_errno != 0) {
    echo "Error: " . $DB_connection->connect_errno . "Opis bledu: " . $db_connect->connect_error;
} else {
    echo "<p class='none' >Połączono z bazą!</p>";
}
