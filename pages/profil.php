<?php
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
session_start();

// Contrôle d'accès : uniquement rôle Admin
if (empty($_SESSION['id']) || ($_SESSION['role'] ?? '') !== 'admin') {
    session_unset();
    session_destroy();
    header('Location: ./../index.php?erreur=3');
    exit();
}
?>
<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="utf-8">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <meta name="viewport" content="width=device-width, initial-scale=1, shrink-to-fit=no">
    <title>DANFANIMENT - Mon profil</title>

    <?php include('inclusion_haut.php'); ?>
    <style>
        .profile-img {
            width: 120px;
            height: 120px;
            border-radius: 50%;
            object-fit: cover;
            border: 3px solid #DC2626;
            box-shadow: 0 4px 8px rgba(0,0,0,0.1);
            margin-bottom: 15px;
        }
        .profile-card {
            border-radius: 15px;
            overflow: hidden;
            box-shadow: 0 10px 25px rgba(0,0,0,0.1);
        }
        .profile-header {
            background: linear-gradient(135deg, #DC2626, #F59E0B);
            padding: 20px;
            text-align: center;
            color: white;
        }
        .info-row {
            padding: 12px 0;
            border-bottom: 1px solid #e3e6f0;
        }
        .info-label {
            font-weight: bold;
            color: #DC2626;
        }
    </style>
</head>

<body id="page-top">
    <?php include('alert_profil.php'); ?>

    <?php
        // Récupération des données de l'utilisateur
        require_once "../api/modules/connect_db_pdo.php";

        $requete = $bdd->prepare('SELECT * FROM utilisateurs WHERE id_utilisateur = ? LIMIT 1');
        $requete->execute([$_SESSION['id']]);
        
        if ($donnee = $requete->fetch(PDO::FETCH_ASSOC)) {
            // Initialisation/rafraîchissement des variables de session
            $_SESSION['username'] = $donnee['nom_utilisateur'];
            $_SESSION['role']     = $donnee['role'] ?? ($_SESSION['role'] ?? 'admin');
            $_SESSION['dernier_acces'] = $donnee['dernier_acces'];
            $_SESSION['actif']    = $donnee['actif'];
            $_SESSION['nom_complet'] = $donnee['nom_complet'];
            $_SESSION['email'] = $donnee['email'];
            $_SESSION['telephone'] = $donnee['telephone'];
        }
    ?>

    <div id="wrapper">
        <?php include('menu_admin.php'); ?>

        <div id="content-wrapper" class="d-flex flex-column">
            <div id="content">
                <?php include('entete.php'); ?>

                <div class="container-fluid">
                    <h1 class="h3 mb-4 text-gray-800">Mon profil</h1>

                    <div class="row">
                        <div class="col-lg-3"></div>
                        <div class="col-lg-6">
                            <div class="card profile-card shadow mb-4">
                                <div class="profile-header">
                                    <i class="fas fa-user-circle fa-5x"></i>
                                    <h4 class="mt-2 mb-0"><?= htmlspecialchars($donnee['nom_complet'] ?? $_SESSION['nom_complet'] ?? 'Utilisateur') ?></h4>
                                    <p class="mb-0"><?= htmlspecialchars($_SESSION['role'] ?? '') ?></p>
                                </div>
                                <div class="card-body">
                                    <div class="info-row">
                                        <div class="row">
                                            <div class="col-5 info-label">Nom d'utilisateur :</div>
                                            <div class="col-7"><?= htmlspecialchars($_SESSION['username'] ?? '') ?></div>
                                        </div>
                                    </div>
                                    <div class="info-row">
                                        <div class="row">
                                            <div class="col-5 info-label">Email :</div>
                                            <div class="col-7"><?= htmlspecialchars($donnee['email'] ?? $_SESSION['email'] ?? 'Non renseigné') ?></div>
                                        </div>
                                    </div>
                                    <div class="info-row">
                                        <div class="row">
                                            <div class="col-5 info-label">Téléphone :</div>
                                            <div class="col-7"><?= htmlspecialchars($donnee['telephone'] ?? $_SESSION['telephone'] ?? 'Non renseigné') ?></div>
                                        </div>
                                    </div>
                                    <div class="info-row">
                                        <div class="row">
                                            <div class="col-5 info-label">Rôle :</div>
                                            <div class="col-7">
                                                <span class="badge badge-<?= $_SESSION['role'] == 'admin' ? 'danger' : 'info' ?>">
                                                    <?= htmlspecialchars($_SESSION['role'] ?? '') ?>
                                                </span>
                                            </div>
                                        </div>
                                    </div>
                                    <div class="info-row">
                                        <div class="row">
                                            <div class="col-5 info-label">Dernier accès :</div>
                                            <div class="col-7"><?= htmlspecialchars($_SESSION['dernier_acces'] ?? 'Jamais') ?></div>
                                        </div>
                                    </div>
                                    <div class="info-row">
                                        <div class="row">
                                            <div class="col-5 info-label">Statut :</div>
                                            <div class="col-7">
                                                <?php if (($_SESSION['actif'] ?? 0) == 1): ?>
                                                    <span class="badge badge-success">Actif</span>
                                                <?php else: ?>
                                                    <span class="badge badge-danger">Inactif</span>
                                                <?php endif; ?>
                                            </div>
                                        </div>
                                    </div>
                                    <div class="info-row">
                                        <div class="row">
                                            <div class="col-5 info-label">Date création :</div>
                                            <div class="col-7"><?= htmlspecialchars($donnee['date_creation'] ?? '-') ?></div>
                                        </div>
                                    </div>
                                </div>
                                <div class="card-footer text-center">
                                    <button class="btn btn-primary btn-sm" data-toggle="modal" data-target="#modifier_profil">
                                        <i class="fas fa-edit"></i> Modifier le profil
                                    </button>
                                    <button class="btn btn-warning btn-sm" data-toggle="modal" data-target="#modifier_password">
                                        <i class="fas fa-lock"></i> Modifier le mot de passe
                                    </button>
                                </div>
                            </div>
                        </div>
                        <div class="col-lg-3"></div>
                    </div>
                </div>
            </div>

            <?php include('footer.php'); ?>
        </div>
    </div>

    <a class="scroll-to-top rounded" href="#page-top">
        <i class="fas fa-angle-up"></i>
    </a>

    <?php include('inclusion_bas.php'); ?>

    <!-- Modals -->
    <?php include('modals/modal_profil.php'); ?>
    <?php include('modals/modal_deconnexion.php'); ?>

</body>
</html>