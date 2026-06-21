<?php 
// api/modules/connect_db_pdo.php
// DANFANIMENT POS - Connexion PDO

//CONNEXION PDO A LA BASE DE DONNEES DANFANIMENT
try {
    $bdd = new PDO(
        'mysql:host=localhost;dbname=danfaniment_bd;charset=utf8mb4',  // Changé de garage_bd à danfaniment_bd
        'root',
        '',
        array(
            PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
            PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
            PDO::MYSQL_ATTR_INIT_COMMAND => "SET NAMES utf8mb4"
        )
    );
} catch (PDOException $e) {
    die('Erreur de connexion à la base de données DANFANIMENT : ' . $e->getMessage());
}
?>