<?php
// pages/reset-password.php
// DANFANIMENT POS - Page de réinitialisation du mot de passe

session_start();

// Récupération du token depuis l'URL
$token = isset($_GET['token']) ? trim($_GET['token']) : '';
?>
<!DOCTYPE html>
<html lang="fr">

<head>
  <meta charset="utf-8">
  <meta http-equiv="X-UA-Compatible" content="IE=edge">
  <meta name="viewport" content="width=device-width, initial-scale=1, shrink-to-fit=no">
  <title>DANFANIMENT POS – Réinitialisation du mot de passe</title>

  <!-- Favicon -->
  <link href="img/logo_danfaniment.png" rel="icon">

  <!-- Font Awesome -->
  <link href="../vendor/fontawesome-free/css/all.min.css" rel="stylesheet" type="text/css">

  <!-- Google Fonts -->
  <link
    href="https://fonts.googleapis.com/css?family=Nunito:200,200i,300,300i,400,400i,600,600i,700,700i,800,800i,900,900i"
    rel="stylesheet">

  <!-- SB Admin 2 CSS -->
  <link href="css/sb-admin-2.css?version=1.4" rel="stylesheet">

  <!-- SweetAlert2 -->
  <link rel="stylesheet" href="css/sweetalert2.min.css">
  <script src="js/sweetalert2.all.min.js"></script>
  
  <style>
    .bg-gradient-danfaniment {
      background: linear-gradient(135deg, #DC2626 0%, #F59E0B 100%);
    }
    .btn-danfaniment {
      background: linear-gradient(135deg, #DC2626, #F59E0B);
      border: none;
      color: white;
      transition: transform 0.2s;
    }
    .btn-danfaniment:hover {
      transform: translateY(-2px);
      color: white;
    }
    .bg-password-image {
      background: linear-gradient(135deg, #DC2626, #F59E0B);
      background-size: cover;
      display: flex;
      align-items: center;
      justify-content: center;
    }
    .bg-password-image:before {
      content: "\f084";
      font-family: "Font Awesome 6 Free";
      font-weight: 900;
      font-size: 80px;
      color: rgba(255, 255, 255, 0.3);
    }
  </style>
</head>

<body class="bg-gradient-danfaniment">

<?php
// Alertes succès/erreur venant du backend
if (isset($_SESSION['reset']) && $_SESSION['reset'] == 1) {
    echo "<script>
      Swal.fire({
        title: 'Mot de passe réinitialisé !',
        text: 'Vous pouvez maintenant vous connecter avec votre nouveau mot de passe.',
        icon: 'success',
        confirmButtonColor: '#10B981',
        confirmButtonText: 'OK'
      }).then((result) => {
        if (result.isConfirmed) {
          window.location.href = '../index.php';
        }
      });
    </script>";
    $_SESSION['reset'] = 0;
}

if (isset($_SESSION['reset_err']) && $_SESSION['reset_err'] == 1) {
    echo "<script>
      Swal.fire({
        title: 'Lien invalide ou expiré',
        text: 'Veuillez refaire une demande de réinitialisation.',
        icon: 'error',
        confirmButtonColor: '#DC2626',
        confirmButtonText: 'OK'
      }).then((result) => {
        if (result.isConfirmed) {
          window.location.href = 'forgot-password.php';
        }
      });
    </script>";
    $_SESSION['reset_err'] = 0;
}

if (isset($_SESSION['reset_err']) && $_SESSION['reset_err'] == 2) {
    echo "<script>
      Swal.fire({
        title: 'Erreur',
        text: 'Les mots de passe ne correspondent pas.',
        icon: 'error',
        confirmButtonColor: '#DC2626',
        confirmButtonText: 'OK'
      });
    </script>";
    $_SESSION['reset_err'] = 0;
}

if (isset($_SESSION['reset_err']) && $_SESSION['reset_err'] == 3) {
    echo "<script>
      Swal.fire({
        title: 'Erreur',
        text: 'Le mot de passe doit contenir au moins 6 caractères.',
        icon: 'error',
        confirmButtonColor: '#DC2626',
        confirmButtonText: 'OK'
      });
    </script>";
    $_SESSION['reset_err'] = 0;
}
?>

  <div class="container">

    <div class="row justify-content-center">

      <div class="col-xl-10 col-lg-12 col-md-9">

        <div class="card o-hidden border-0 shadow-lg my-5">
          <div class="card-body p-0">
            <div class="row">
              <div class="col-lg-6 d-none d-lg-block bg-password-image"></div>
              <div class="col-lg-6">
                <div class="p-5">

                  <div class="text-center">
                    <h1 class="h4 mb-2" style="color:#DC2626;font-weight:bold;">
                      <i class="fas fa-key"></i> Réinitialiser le mot de passe
                    </h1>
                    <p class="mb-4 text-muted">
                      Choisissez un nouveau mot de passe sécurisé.
                    </p>
                  </div>

                  <?php if (!empty($token)) : ?>
                    <form class="user" action="../api/modules/reset-password.php" method="POST" novalidate>
                      <input type="hidden" name="token" value="<?php echo htmlspecialchars($token, ENT_QUOTES, 'UTF-8'); ?>">
                      
                      <div class="form-group">
                        <input
                          type="password"
                          class="form-control form-control-user"
                          name="password"
                          id="password"
                          placeholder="Nouveau mot de passe"
                          required
                        >
                      </div>
                      <div class="form-group">
                        <input
                          type="password"
                          class="form-control form-control-user"
                          name="confirm_password"
                          id="confirm_password"
                          placeholder="Confirmer le mot de passe"
                          required
                        >
                      </div>
                      <button type="submit" class="btn btn-danfaniment btn-user btn-block">
                        <i class="fas fa-save"></i> Réinitialiser le mot de passe
                      </button>
                    </form>
                  <?php else: ?>
                    <div class="alert alert-danger text-center">
                      <i class="fas fa-exclamation-triangle"></i> Lien de réinitialisation invalide.
                    </div>
                  <?php endif; ?>

                  <hr>
                  <div class="text-center">
                    <a class="small" href="../index.php">
                      <i class="fas fa-arrow-left"></i> Retour à la connexion
                    </a>
                  </div>

                </div>
              </div>
            </div>
          </div>
        </div>

      </div>

    </div>

  </div>

  <!-- Bootstrap core JavaScript -->
  <script src="../vendor/jquery/jquery.min.js"></script>
  <script src="../vendor/bootstrap/js/bootstrap.bundle.min.js"></script>
  <script src="../vendor/jquery-easing/jquery.easing.min.js"></script>
  <script src="js/sb-admin-2.min.js"></script>

</body>
</html>