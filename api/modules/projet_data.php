<?php 
session_start();
if (empty($_SESSION['id']) || ($_SESSION['type_compte'] != "Super Administrateur")) {
    session_unset();
    session_destroy();
    header('Location:./../index.php?erreur=3');
} else {
    $table = <<<EOT
    (
        SELECT ROW_NUMBER() OVER (ORDER BY id) AS num_row,
               id AS id,
               code_projet,
               nom_projet,
               budget,
               date_debut,
               date_fin,
               description,
               responsable,
               statut
        FROM projet
    ) tem
EOT;
    
    $primaryKey = 'id';

    $columns = array(
        array('db' => 'num_row',    'dt' => 0),
        array('db' => 'code_projet',  'dt' => 1),
        array('db' => 'nom_projet',   'dt' => 2),
        array('db' => 'budget',       'dt' => 3),
        array('db' => 'date_debut',   'dt' => 4),
        array('db' => 'date_fin',     'dt' => 5),
        array('db' => 'description',  'dt' => 6),
        array('db' => 'responsable',  'dt' => 7),
        array('db' => 'statut',       'dt' => 8),
        array('db' => 'id',           'dt' => 9)
    );

    include('connect_db_data.php');

    require('DataTables/examples/server_side/scripts/ssp.class.php');
    echo json_encode(
        SSP::simple($_POST, $sql_details, $table, $primaryKey, $columns)
    );
}
?>
