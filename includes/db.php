<?php
/*
 * Database configuration.
 * The default values work with a standard local XAMPP installation.
 * On Vesta, replace the username, password, and database name with the
 * credentials supplied by the university.
 */
$host = getenv('UCLAN_DB_HOST') ?: 'localhost';
$user = getenv('UCLAN_DB_USER') ?: 'root';
$pass = getenv('UCLAN_DB_PASS') ?: '';
$dbname = getenv('UCLAN_DB_NAME') ?: 'uclan_shop';

$connection = mysqli_connect($host, $user, $pass, $dbname);

if (!$connection) {
    error_log('Database connection failed: ' . mysqli_connect_error());
    die('The shop could not connect to the database. Check includes/db.php and confirm that the SQL file has been imported.');
}

if (!mysqli_set_charset($connection, 'utf8mb4')) {
    error_log('Could not set database character set: ' . mysqli_error($connection));
}
