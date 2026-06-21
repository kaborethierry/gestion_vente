<?php 
// pages/menu_admin.php
// DANFANIMENT POS - Menu latéral pour l'administrateur

// Récupère le nom du fichier courant pour gérer l'état "active"
$currentPage = basename($_SERVER['PHP_SELF']);
?> 

<style>
    :root {
        --primary-red: #DC2626;
        --primary-green: #10B981;
        --primary-yellow: #F59E0B;
    }
    
    .bg-gradient-primary {
        background: linear-gradient(180deg, var(--primary-red) 10%, var(--primary-yellow) 100%);
    }
    
    .sidebar .nav-item .nav-link.active {
        color: var(--primary-yellow) !important;
        font-weight: bold;
    }
    
    .sidebar .nav-item .nav-link:hover {
        color: var(--primary-yellow) !important;
    }
    
    .sidebar .sidebar-brand {
        background: rgba(0,0,0,0.2);
    }
    
    .sidebar .sidebar-brand .sidebar-brand-text {
        color: white;
        font-weight: bold;
    }
    
    .sidebar-dark .nav-item .nav-link i {
        color: rgba(255,255,255,0.8);
    }
    
    .sidebar .sidebar-heading {
        color: var(--primary-yellow);
        font-weight: bold;
    }
</style>

<!-- Sidebar -->
<ul class="navbar-nav bg-gradient-primary sidebar sidebar-dark accordion" id="accordionSidebar">

    <!-- Sidebar - Brand -->
    <a class="sidebar-brand d-flex align-items-center justify-content-center" href="tableau_de_bord.php">
        <div class="sidebar-brand-icon">
            <i class="fas fa-store"></i>
        </div>
        <div class="sidebar-brand-text mx-3">DANFANIMENT</div>
    </a>

    <!-- Divider -->
    <hr class="sidebar-divider my-0">

    <!-- Nav Item - Dashboard -->
    <li class="nav-item <?= $currentPage === 'tableau_de_bord.php' ? 'active' : '' ?>">
        <a class="nav-link" href="tableau_de_bord.php">
            <i class="fas fa-fw fa-tachometer-alt"></i>
            <span>Tableau de bord</span>
        </a>
    </li>

    <!-- Divider -->
    <hr class="sidebar-divider">

    <!-- Heading - Opérations principales -->
    <div class="sidebar-heading">
        Opérations
    </div>

    <!-- Point de Vente (POS) - pour vendre -->
    <li class="nav-item <?= $currentPage === 'pos.php' ? 'active' : '' ?>">
        <a class="nav-link" href="pos.php">
            <i class="fas fa-fw fa-shopping-cart"></i>
            <span>Point de Vente (POS)</span>
        </a>
    </li>

    <!-- Gestion des sessions de caisse -->
    <li class="nav-item <?= $currentPage === 'caisse.php' ? 'active' : '' ?>">
        <a class="nav-link" href="caisse.php">
            <i class="fas fa-fw fa-cash-register"></i>
            <span>Gestion des sessions de caisse</span>
        </a>
    </li>

    <!-- Divider -->
    <hr class="sidebar-divider">

    <!-- Heading - Gestion commerciale -->
    <div class="sidebar-heading">
        Gestion commerciale
    </div>

    <!-- Gestion des Produits -->


    <!-- Gestion des Clients -->
    <li class="nav-item <?= $currentPage === 'clients.php' ? 'active' : '' ?>">
        <a class="nav-link" href="clients.php">
            <i class="fas fa-fw fa-users"></i>
            <span>Gestion des Clients</span>
        </a>
    </li>

    <!-- Gestion des Mesures -->
    <li class="nav-item <?= $currentPage === 'mesures.php' ? 'active' : '' ?>">
        <a class="nav-link" href="mesures.php">
            <i class="fas fa-fw fa-ruler-combined"></i>
            <span>Gestion des Mesures</span>
        </a>
    </li>

    <!-- Gestion des Confections -->
    <li class="nav-item <?= $currentPage === 'confections.php' ? 'active' : '' ?>">
        <a class="nav-link" href="confections.php">
            <i class="fas fa-fw fa-cut"></i>
            <span>Gestion des Confections</span>
        </a>
    </li>

    <!-- Gestion des Paiements -->
    <li class="nav-item <?= $currentPage === 'paiements.php' ? 'active' : '' ?>">
        <a class="nav-link" href="paiements.php">
            <i class="fas fa-fw fa-hand-holding-usd"></i>
            <span>Gestion des Paiements</span>
        </a>
    </li>

    <!-- Divider -->
    <hr class="sidebar-divider">

    <!-- Heading - Gestion des stocks et prestataires -->
    <div class="sidebar-heading">
         Prestataires
    </div>


    <!-- Mouvements de stock -->


    <!-- Gestion des Prestataires -->
    <li class="nav-item <?= $currentPage === 'prestataires.php' ? 'active' : '' ?>">
        <a class="nav-link" href="prestataires.php">
            <i class="fas fa-fw fa-user-friends"></i>
            <span>Gestion des Prestataires</span>
        </a>
    </li>



    <!-- Divider -->
    <hr class="sidebar-divider">

    <!-- Heading - Finance & Administration -->
    <div class="sidebar-heading">
        Finance & Admin
    </div>

    <!-- Gestion des Dépenses -->
    <li class="nav-item <?= $currentPage === 'depenses.php' ? 'active' : '' ?>">
        <a class="nav-link" href="depenses.php">
            <i class="fas fa-fw fa-chart-line"></i>
            <span>Gestion des Dépenses</span>
        </a>
    </li>



    <!-- Rapports PDF -->
    <li class="nav-item <?= $currentPage === 'rapports.php' ? 'active' : '' ?>">
        <a class="nav-link" href="rapports.php">
            <i class="fas fa-fw fa-file-pdf"></i>
            <span>Rapports PDF</span>
        </a>
    </li>
    <!-- Gestion des Factures -->
<li class="nav-item <?= $currentPage === 'facture.php' ? 'active' : '' ?>">
    <a class="nav-link" href="facture.php">
        <i class="fas fa-fw fa-file-invoice"></i>
        <span>Gestion des Factures</span>
    </a>
</li>

    <!-- Divider -->
    <hr class="sidebar-divider">

    <!-- Heading - Administration Système -->
    <div class="sidebar-heading">
        Administration
    </div>

    <!-- Gestion des Utilisateurs -->
    <li class="nav-item <?= $currentPage === 'utilisateur.php' ? 'active' : '' ?>">
        <a class="nav-link" href="utilisateur.php">
            <i class="fas fa-fw fa-users-cog"></i>
            <span>Gestion des Utilisateurs</span>
        </a>
    </li>

    <!-- Paramètres -->


    <!-- Synchronisation -->




    <!-- Historique -->




    <!-- Logs -->


    <!-- Divider -->
    <hr class="sidebar-divider">

    <!-- Heading - Mon compte -->
    <div class="sidebar-heading">
        Mon compte
    </div>

    <!-- Profil -->
    <li class="nav-item <?= $currentPage === 'profil.php' ? 'active' : '' ?>">
        <a class="nav-link" href="profil.php">
            <i class="fas fa-fw fa-user-circle"></i>
            <span>Mon Profil</span>
        </a>
    </li>

    <!-- Changer mot de passe -->
    <li class="nav-item <?= $currentPage === 'reset-password.php' ? 'active' : '' ?>">
        <a class="nav-link" href="reset-password.php">
            <i class="fas fa-fw fa-key"></i>
            <span>Changer mot de passe</span>
        </a>
    </li>

    <!-- Déconnexion -->
    <li class="nav-item">
        <a class="nav-link" href="../api/modules/deconnexion.php">
            <i class="fas fa-fw fa-sign-out-alt"></i>
            <span>Déconnexion</span>
        </a>
    </li>

    <!-- Sidebar Toggler -->
    <div class="text-center d-none d-md-inline">
        <button class="rounded-circle border-0" id="sidebarToggle"></button>
    </div>

</ul>
<!-- End of Sidebar -->