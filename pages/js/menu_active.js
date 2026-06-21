$(document).ready(function(){
    var url = window.location.pathname;
    //console.log(url);
    switch (url) 
    {
        case "/pages/index.php":
            $('body div ul li').removeClass("active");
            var active=document.getElementById("index");
            $(active).addClass("active");
        break;

        case "/pages/statistique.php":
            $('body div ul li').removeClass("active");
            var active=document.getElementById("statistique");
            $(active).addClass("active");
        break;

        case "/pages/carte2.php":
            $('body div ul li').removeClass("active");
            var active=document.getElementById("carte");
            $(active).addClass("active");
        break;

        case "/pages/cadrelogique.php":
            $('body div ul li').removeClass("active");
            var active=document.getElementById("cadre_logique");
            $(active).addClass("active");
        break;
        
        case "/pages/logistique.php":
            $('body div ul li').removeClass("active");
            var active=document.getElementById("logistique");
            $(active).addClass("active");
        break;

        case "/pages/pees.php":
            $('body div ul li').removeClass("active");
            var active=document.getElementById("pees");
            $(active).addClass("active");
        break;
        
        case "/pages/plainte.php":
            $('body div ul li').removeClass("active");
            var active=document.getElementById("plainte");
            $(active).addClass("active");
        break;
        
        case "/pages/projet.php":
            $('body div ul li').removeClass("active");
            var active=document.getElementById("projet");
            $(active).addClass("active");
            var active=document.getElementById("projet1");
            $(active).addClass("active");
        break;

        case "/pages/activite.php":
            $('body div ul li').removeClass("active");
            var active=document.getElementById("activite");
            $(active).addClass("active");
            var active=document.getElementById("projet");
            $(active).addClass("active");
        break;

        case "/pages/objectif.php":
            $('body div ul li').removeClass("active");
            var active=document.getElementById("objectif");
            $(active).addClass("active");
            var active=document.getElementById("projet");
            $(active).addClass("active");
        break;

        case "/pages/resultat.php":
            $('body div ul li').removeClass("active");
            var active=document.getElementById("resultat");
            $(active).addClass("active");
            var active=document.getElementById("projet");
            $(active).addClass("active");
        break;

        case "/pages/indicateur.php":
            $('body div ul li').removeClass("active");
            var active=document.getElementById("indicateur");
            $(active).addClass("active");
            var active=document.getElementById("projet");
            $(active).addClass("active");
        break;

        case "/pages/utilisateur.php":
            $('body div ul li').removeClass("active");
            var active=document.getElementById("utilisateur");
            $(active).addClass("active");
        break;

        case "/pages/profil.php":
            $('body div ul li').removeClass("active");
            var active=document.getElementById("autre");
            $(active).addClass("active");
        break;
        
        case "/pages/index_acf.php":
            $('body div ul li').removeClass("active");
            var active=document.getElementById("index_acf");
            $(active).addClass("active");
        break;
        
        case "/pages/carte_acf.php":
            $('body div ul li').removeClass("active");
            var active=document.getElementById("carte_acf");
            $(active).addClass("active");
        break;
        
        case "/pages/cadrelogique_acf.php":
            $('body div ul li').removeClass("active");
            var active=document.getElementById("cadrelogique_acf");
            $(active).addClass("active");
        break;
        
        case "/pages/logistique_acf.php":
            $('body div ul li').removeClass("active");
            var active=document.getElementById("logistique_acf");
            $(active).addClass("active");
        break;
        
        case "/pages/pees_acf.php":
            $('body div ul li').removeClass("active");
            var active=document.getElementById("pees_acf");
            $(active).addClass("active");
        break;
        
        case "/pages/plainte_acf.php":
            $('body div ul li').removeClass("active");
            var active=document.getElementById("plainte_acf");
            $(active).addClass("active");
        break;
        
        case "/pages/projet_acf.php":
            $('body div ul li').removeClass("active");
            var active=document.getElementById("projet_acf");
            $(active).addClass("active");
            var active=document.getElementById("projet1_acf");
            $(active).addClass("active");
        break;
        
        case "/pages/objectif_acf.php":
            $('body div ul li').removeClass("active");
            var active=document.getElementById("objectif_acf");
            $(active).addClass("active");
            var active=document.getElementById("projet_acf");
            $(active).addClass("active");
        break;
        
        case "/pages/resultat_acf.php":
            $('body div ul li').removeClass("active");
            var active=document.getElementById("resultat_acf");
            $(active).addClass("active");
            var active=document.getElementById("projet_acf");
            $(active).addClass("active");
        break;
        
        case "/pages/activite_acf.php":
            $('body div ul li').removeClass("active");
            var active=document.getElementById("activite_acf");
            $(active).addClass("active");
            var active=document.getElementById("projet_acf");
            $(active).addClass("active");
        break;
        
        case "/pages/indicateur_acf.php":
            $('body div ul li').removeClass("active");
            var active=document.getElementById("indicateur_acf");
            $(active).addClass("active");
            var active=document.getElementById("projet_acf");
            $(active).addClass("active");
        break;
        
        case "/pages/utilisateur_acf.php":
            $('body div ul li').removeClass("active");
            var active=document.getElementById("utilisateur_acf");
            $(active).addClass("active");
        break;
         
        case "/pages/index_afd.php":
            $('body div ul li').removeClass("active");
            var active=document.getElementById("index_afd");
            $(active).addClass("active");
        break;
        
        case "/pages/carte_afd.php":
            $('body div ul li').removeClass("active");
            var active=document.getElementById("carte_afd");
            $(active).addClass("active");
        break;
        
        case "/pages/cadrelogique_afd.php":
            $('body div ul li').removeClass("active");
            var active=document.getElementById("cadrelogique_afd");
            $(active).addClass("active");
        break;
        
        case "/pages/logistique_afd.php":
            $('body div ul li').removeClass("active");
            var active=document.getElementById("logistique_afd");
            $(active).addClass("active");
        break;
        
        case "/pages/pees_afd.php":
            $('body div ul li').removeClass("active");
            var active=document.getElementById("pees_afd");
            $(active).addClass("active");
        break;
        
        case "/pages/plainte_afd.php":
            $('body div ul li').removeClass("active");
            var active=document.getElementById("plainte_afd");
            $(active).addClass("active");
        break;
        
        case "/pages/projet_afd.php":
            $('body div ul li').removeClass("active");
            var active=document.getElementById("projet_afd");
            $(active).addClass("active");
            var active=document.getElementById("projet1_afd");
            $(active).addClass("active");
        break;
        
        case "/pages/objectif_afd.php":
            $('body div ul li').removeClass("active");
            var active=document.getElementById("objectif_afd");
            $(active).addClass("active");
            var active=document.getElementById("projet_afd");
            $(active).addClass("active");
        break;
        
        case "/pages/resultat_afd.php":
            $('body div ul li').removeClass("active");
            var active=document.getElementById("resultat_afd");
            $(active).addClass("active");
            var active=document.getElementById("projet_afd");
            $(active).addClass("active");
        break;
        
        case "/pages/activite_afd.php":
            $('body div ul li').removeClass("active");
            var active=document.getElementById("activite_afd");
            $(active).addClass("active");
            var active=document.getElementById("projet_afd");
            $(active).addClass("active");
        break;
        
        case "/pages/indicateur_afd.php":
            $('body div ul li').removeClass("active");
            var active=document.getElementById("indicateur_afd");
            $(active).addClass("active");
            var active=document.getElementById("projet_afd");
            $(active).addClass("active");
        break;
        
        case "/pages/utilisateur_afd.php":
            $('body div ul li').removeClass("active");
            var active=document.getElementById("utilisateur_afd");
            $(active).addClass("active");
        break;
        
        
        case "/pages/index_partenaire.php":
            $('body div ul li').removeClass("active");
            var active=document.getElementById("index_partenaire");
            $(active).addClass("active");
        break;
        
        case "/pages/statistique_partenaire.php":
            $('body div ul li').removeClass("active");
            var active=document.getElementById("statistique_partenaire");
            $(active).addClass("active");
        break;
        
        case "/pages/carte_partenaire.php":
            $('body div ul li').removeClass("active");
            var active=document.getElementById("carte_partenaire");
            $(active).addClass("active");
        break;
        
        case "/pages/cadrelogique_partenaire.php":
            $('body div ul li').removeClass("active");
            var active=document.getElementById("cadrelogique_partenaire");
            $(active).addClass("active");
        break;
        
        case "/pages/logistique_partenaire.php":
            $('body div ul li').removeClass("active");
            var active=document.getElementById("logistique_partenaire");
            $(active).addClass("active");
        break;
        
        case "/pages/pees_partenaire.php":
            $('body div ul li').removeClass("active");
            var active=document.getElementById("pees_partenaire");
            $(active).addClass("active");
        break;
        
        case "/pages/plainte_partenaire.php":
            $('body div ul li').removeClass("active");
            var active=document.getElementById("plainte_partenaire");
            $(active).addClass("active");
        break;
        
        case "/pages/projet_partenaire.php":
            $('body div ul li').removeClass("active");
            var active=document.getElementById("projet_partenaire");
            $(active).addClass("active");
            var active=document.getElementById("projet1_partenaire");
            $(active).addClass("active");
        break;
        
        case "/pages/objectif_partenaire.php":
            $('body div ul li').removeClass("active");
            var active=document.getElementById("objectif_partenaire");
            $(active).addClass("active");
            var active=document.getElementById("projet_partenaire");
            $(active).addClass("active");
        break;
        
        case "/pages/resultat_partenaire.php":
            $('body div ul li').removeClass("active");
            var active=document.getElementById("resultat_partenaire");
            $(active).addClass("active");
            var active=document.getElementById("projet_partenaire");
            $(active).addClass("active");
        break;
        
        case "/pages/activite_partenaire.php":
            $('body div ul li').removeClass("active");
            var active=document.getElementById("activite_partenaire");
            $(active).addClass("active");
            var active=document.getElementById("projet_partenaire");
            $(active).addClass("active");
        break;
        
        case "/pages/indicateur_partenaire.php":
            $('body div ul li').removeClass("active");
            var active=document.getElementById("indicateur_partenaire");
            $(active).addClass("active");
            var active=document.getElementById("projet_partenaire");
            $(active).addClass("active");
        break;
        
        case "/pages/utilisateur_partenaire.php":
            $('body div ul li').removeClass("active");
            var active=document.getElementById("utilisateur_partenaire");
            $(active).addClass("active");
        break;
        
        
        
    }
})