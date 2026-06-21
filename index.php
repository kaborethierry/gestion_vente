<!DOCTYPE html>
<html lang="fr">

<head>

    <meta charset="utf-8">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <meta name="viewport" content="width=device-width, initial-scale=1, shrink-to-fit=no">
    <meta name="description" content="DANFANIMENT POS - Système de Point de Vente pour Boutique de Mode Traditionnelle">
    <meta name="author" content="DANFANIMENT">

    <title>DANFANIMENT POS - Connexion</title>
    <link href="pages/img/logo-danfaniment.png" rel="icon">

    <!-- Custom fonts for this template-->
    <link href="vendor/fontawesome-free/css/all.min.css" rel="stylesheet" type="text/css">
    <link
        href="https://fonts.googleapis.com/css?family=Nunito:200,200i,300,300i,400,400i,600,600i,700,700i,800,800i,900,900i"
        rel="stylesheet">

    <!-- Custom styles for this template-->
    <link href="pages/css/sb-admin-2.css?version=1.2" rel="stylesheet">
    <!-- CSS SWEET ALERT 2 -->
    <link rel='stylesheet' href='pages/css/sweetalert2.min.css'>
    <!-- /CSS SWEET ALERT 2 -->
    <!-- Javascript SWEET ALERT 2 -->
    <script src="pages/js/sweetalert2.all.min.js"></script>
    <!-- /Javascript SWEET ALERT 2 -->

    <style>
        /* Couleurs DANFANIMENT : Rouge, Vert, Jaune */
        .bg-gradient-primary {
            background: linear-gradient(180deg, var(--primary-red) 10%, var(--primary-yellow) 100%);
            background-size: cover;
        }
        
        :root {
            --primary-red: #DC2626;
            --primary-green: #10B981;
            --primary-yellow: #F59E0B;
        }
        
        .btn-primary {
            background-color: var(--primary-red);
            border-color: var(--primary-red);
        }
        
        .btn-primary:hover {
            background-color: #B91C1C;
            border-color: #B91C1C;
        }
        
        .btn-primary:focus, .btn-primary:active {
            background-color: var(--primary-red);
            border-color: var(--primary-red);
        }
        
        .text-primary {
            color: var(--primary-red) !important;
        }
        
        .bg-login-image {
            background: linear-gradient(135deg, var(--primary-red) 0%, var(--primary-yellow) 100%);
            background-size: cover;
            position: relative;
        }
        
        .bg-login-image::after {
            content: "DANFANIMENT";
            position: absolute;
            bottom: 20px;
            left: 0;
            right: 0;
            text-align: center;
            color: white;
            font-weight: bold;
            font-size: 24px;
            text-shadow: 2px 2px 4px rgba(0,0,0,0.3);
        }
        
        .bg-login-image::before {
            content: "👘";
            position: absolute;
            top: 50%;
            left: 50%;
            transform: translate(-50%, -50%);
            font-size: 80px;
            opacity: 0.3;
            color: white;
        }
        
        .small, a.small {
            color: var(--primary-green) !important;
            font-weight: 600;
        }
        
        .small:hover, a.small:hover {
            color: var(--primary-red) !important;
        }
        
        .card-header-custom {
            background: linear-gradient(90deg, var(--primary-red), var(--primary-yellow));
            padding: 10px;
            border-radius: 10px 10px 0 0;
            text-align: center;
        }
        
        .logo-text {
            font-size: 28px;
            font-weight: bold;
            background: linear-gradient(135deg, var(--primary-red), var(--primary-green), var(--primary-yellow));
            -webkit-background-clip: text;
            -webkit-text-fill-color: transparent;
            background-clip: text;
        }
        
        .subtitle {
            color: var(--primary-green);
            font-weight: 600;
        }
    </style>

</head>

<body class="bg-gradient-primary">
    <?php session_start(); ?>  
    
    <!-- Messages de réinitialisation de mot de passe -->
    <?php if (isset($_SESSION['password']) && $_SESSION['password']==1): ?>
    <script>
        Swal.fire({
            title: 'Email envoyé !',
            text: 'Vous recevrez un e-mail contenant des instructions de réinitialisation du mot de passe.',
            icon: 'success',
            confirmButtonColor: '#10B981',
            confirmButtonText: 'OK'
        });
    </script> 
    <?php $_SESSION['password']=0; endif; ?>
    
    <?php if (isset($_SESSION['password']) && $_SESSION['password']==2): ?>
    <script>
        Swal.fire({
            title: 'Succès !',
            text: 'Votre mot de passe a été réinitialisé avec succès.',
            icon: 'success',
            confirmButtonColor: '#10B981',
            confirmButtonText: 'OK'
        });
    </script> 
    <?php $_SESSION['password']=0; endif; ?>
  
    <!-- Messages d'erreur -->
    <?php if (isset($_SESSION['err']) && $_SESSION['err']==1): ?>
    <script>
        Swal.fire({
            title: 'Erreur de connexion',
            text: 'Nom d\'utilisateur ou mot de passe incorrect.',
            icon: 'error',
            confirmButtonColor: '#DC2626',
            confirmButtonText: 'OK'
        });
    </script> 
    <?php $_SESSION['err']=0; endif; ?>

    <?php if (isset($_SESSION['err']) && $_SESSION['err']==2): ?>
    <script>
        Swal.fire({
            title: 'Champs vides',
            text: 'Veuillez remplir tous les champs.',
            icon: 'warning',
            confirmButtonColor: '#F59E0B',
            confirmButtonText: 'OK'
        });
    </script> 
    <?php $_SESSION['err']=0; endif; ?>

    <?php if (isset($_SESSION['err']) && $_SESSION['err']==3): ?>
    <script>
        Swal.fire({
            title: 'Erreur technique',
            text: 'Erreur de connexion à la base de données. Veuillez réessayer plus tard.',
            icon: 'error',
            confirmButtonColor: '#DC2626',
            confirmButtonText: 'OK'
        });
    </script> 
    <?php $_SESSION['err']=0; endif; ?>

    <br>
    <br>

    <div class="container">

        <!-- Outer Row -->
        <div class="row justify-content-center">

            <div class="col-xl-10 col-lg-12 col-md-9">

                <div class="card o-hidden border-0 shadow-lg my-5">
                    <div class="card-header-custom">
                        <span class="logo-text">DANFANIMENT</span>
                        <small class="subtitle d-block">Boutique de Mode Traditionnelle</small>
                    </div>
                    <div class="card-body p-0">
                        <!-- Nested Row within Card Body -->
                        <div class="row">

                            <div class="col-lg-6 d-none d-lg-block bg-login-image"></div>
                            <div class="col-lg-6">
                                <div class="p-5">
                                    <br>

                                    <div class="text-center">
                                        <h1 style="color:#DC2626;font-weight: bold;" class="h3 text-gray-900 mb-2">
                                            <i class="fas fa-store"></i> CONNEXION
                                        </h1>
                                        <p class="mb-4 text-muted">Système de Point de Vente</p>
                                    </div>
                                    
                                    <form class="user" action="api/modules/connection.php" method="POST">
                                        <div class="form-group">
                                            <div class="input-group">
                                                <div class="input-group-prepend">
                                                    <span class="input-group-text" style="background-color:#DC2626; color:white;">
                                                        <i class="fas fa-user"></i>
                                                    </span>
                                                </div>
                                                <input type="text" class="form-control form-control-user"
                                                    name="username" id="username" 
                                                    placeholder="Nom d'utilisateur" 
                                                    style="text-align:center"
                                                    autocomplete="off"
                                                    autofocus
                                                    required>
                                            </div>
                                        </div>
                                        
                                        <div class="form-group">
                                            <div class="input-group">
                                                <div class="input-group-prepend">
                                                    <span class="input-group-text" style="background-color:#F59E0B; color:white;">
                                                        <i class="fas fa-lock"></i>
                                                    </span>
                                                </div>
                                                <input type="password" class="form-control form-control-user"
                                                    id="password" name="password" 
                                                    placeholder="Mot de passe"
                                                    style="text-align:center"
                                                    required>
                                                <div class="input-group-append">
                                                    <span class="input-group-text" id="togglePassword" style="cursor:pointer; background-color:#10B981; color:white;">
                                                        <i class="fas fa-eye"></i>
                                                    </span>
                                                </div>
                                            </div>
                                        </div>
                                        
                                        <button class="btn btn-primary btn-user btn-block">
                                            <i class="fas fa-sign-in-alt"></i> Se connecter
                                        </button>
                                    </form>
                                    
                                    <hr>
                                    
                                    <div class="text-center">
                                        <a class="small" href="pages/forgot-password.php">
                                            <i class="fas fa-key"></i> Mot de passe oublié ?
                                        </a>
                                    </div>
                                    
                                    <div class="text-center mt-3">
                                        <small class="text-muted">
                                            <i class="fas fa-tshirt"></i> Pagnes | 
                                            <i class="fas fa-user-tie"></i> Habits Traditionnels | 
                                            <i class="fas fa-cut"></i> Confection sur mesure
                                        </small>
                                    </div>
                                    
                                    <br>
                                    <br>
                                </div>
                            </div>
                        </div>
                    </div>
                    <div class="card-footer text-center" style="background-color:#f8f9fc;">
                        <small class="text-muted">
                            &copy; <?php echo date('Y'); ?> DANFANIMENT - Tous droits réservés
                        </small>
                    </div>
                </div>

            </div>

        </div>

    </div>

    <script>
        // Toggle password visibility
        const togglePassword = document.querySelector('#togglePassword');
        const password = document.querySelector('#password');
        
        if (togglePassword && password) {
            togglePassword.addEventListener('click', function() {
                // Toggle the type attribute
                const type = password.getAttribute('type') === 'password' ? 'text' : 'password';
                password.setAttribute('type', type);
                
                // Toggle the icon
                this.querySelector('i').classList.toggle('fa-eye');
                this.querySelector('i').classList.toggle('fa-eye-slash');
            });
        }
        
        // Message de bienvenue en console (pour le développement)
        console.log('%c DANFANIMENT POS - Système de Point de Vente ', 'background: #DC2626; color: #F59E0B; font-size: 16px; font-weight: bold;');
        console.log('%c Mode hybride Online/Offline activé ', 'background: #10B981; color: white; font-size: 12px;');
    </script>

    <!-- Inclusion du fichier de vérification offline -->
    <script src="assets/js/offline-check.js"></script>
</body>

</html>