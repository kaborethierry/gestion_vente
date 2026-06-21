<?php 
// api/modules/connect_db.php
// DANFANIMENT POS - Connexion MySQLi

//CONNEXION A LA BASE DE DONNEES DANFANIMENT
$db_username = 'root';
$db_password = '';
$db_name     = 'danfaniment_bd';  // Changé de garage_bd à danfaniment_bd
$db_host     = 'localhost';

$db = mysqli_connect($db_host, $db_username, $db_password, $db_name)
or die('Erreur de connexion à la base de données DANFANIMENT !');
?>