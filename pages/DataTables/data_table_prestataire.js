// pages/DataTables/data_table_prestataire.js
$(document).ready(function () {
    console.log('DataTable script chargé');
    
    // Fonction pour initialiser un tableau
    function initDataTable(tableId, prestataireType) {
        // Définir les colonnes selon le type
        var columns = [
            { data: 'id_prestataire' },
            { data: 'nom_complet' },
            { data: 'telephone' },
            { data: 'specialites' }
        ];
        
        // Colonnes spécifiques selon le type
        if (prestataireType === 'couturier' || prestataireType === 'tisseuse') {
            columns.push({ data: 'productions_semaine' });
            columns.push({ data: 'montant_du' });
            columns.push({ data: 'total_paye' });
        } else if (prestataireType === 'vendeuse') {
            columns.push({ data: 'ca_semaine' });
            columns.push({ data: 'montant_du' });
            columns.push({ data: 'total_paye' });
        } else {
            // brodeur, perleuse, mercerie
            columns.push({ data: 'productions_semaine' });
            columns.push({ data: 'montant_du' });
            columns.push({ data: 'total_paye' });
        }
        
        // Colonnes communes à tous (boutons)
        columns.push(
            { data: 'statut_badge' },
            { 
                data: null,
                orderable: false,
                render: function(data) {
                    return '<button class="btn btn-sm btn-warning edit-prestataire" data-id="' + data.id_prestataire + '"><i class="fa fa-pencil"></i> Modifier</button>';
                }
            },
            { 
                data: null,
                orderable: false,
                render: function(data) {
                    return '<button class="btn btn-sm btn-danger delete-prestataire" data-id="' + data.id_prestataire + '"><i class="fa fa-trash"></i> Supprimer</button>';
                }
            },
            { 
                data: null,
                orderable: false,
                render: function(data) {
                    var montantValue = data.montant_du_valeur || 0;
                    return '<button class="btn btn-sm btn-success payer-prestataire" data-id="' + data.id_prestataire + '" data-montant="' + montantValue + '" data-nom="' + data.nom_complet + '"><i class="fa fa-money"></i> Payer</button>';
                }
            }
        );
        
        return $(tableId).DataTable({
            processing: true,
            serverSide: true,
            ajax: {
                url: '../api/modules/prestataire_data.php',
                type: 'GET',
                data: { type_prestataire: prestataireType },
                dataSrc: 'data'
            },
            columns: columns,
            order: [[0, 'desc']],
            pageLength: 10,
            language: {
                sProcessing: "Traitement en cours...",
                sSearch: "Rechercher :",
                sLengthMenu: "Afficher _MENU_ éléments",
                sInfo: "Affichage _START_ à _END_ sur _TOTAL_ éléments",
                sInfoEmpty: "Affichage 0 à 0 sur 0 éléments",
                sZeroRecords: "Aucun " + prestataireType + " trouvé"
            }
        });
    }
    
    // Initialisation des tableaux (vérifier l'existence avant d'initialiser)
    if ($('#tableCouturiers').length) {
        initDataTable('#tableCouturiers', 'couturier');
    }
    if ($('#tableTisseuses').length) {
        initDataTable('#tableTisseuses', 'tisseuse');
    }
    if ($('#tableBrodeurs').length) {
        initDataTable('#tableBrodeurs', 'brodeur');
    }
    if ($('#tablePerleuses').length) {
        initDataTable('#tablePerleuses', 'perleuse');
    }
    if ($('#tableMerceries').length) {
        initDataTable('#tableMerceries', 'mercerie');
    }
    if ($('#tableVendeuses').length) {
        initDataTable('#tableVendeuses', 'vendeuse');
    }
    
    // Gestion des événements - Utiliser delegation sur document
    $(document).on('click', '.edit-prestataire', function() {
        var id = $(this).data('id');
        $.ajax({
            url: '../api/modules/get_prestataire.php',
            type: 'GET',
            data: { id: id },
            dataType: 'json',
            success: function(data) {
                if (data && data.id_prestataire) {
                    $('#id_prestataire_modif').val(data.id_prestataire);
                    $('#nom_modif').val(data.nom);
                    $('#prenom_modif').val(data.prenom);
                    $('#telephone_modif').val(data.telephone);
                    $('#email_modif').val(data.email || '');
                    $('#adresse_modif').val(data.adresse || '');
                    $('#type_prestataire_modif').val(data.type_prestataire);
                    $('#frequence_paiement_modif').val(data.frequence_paiement || 'hebdomadaire');
                    $('#tarif_par_tenue_modif').val(data.tarif_par_tenue || 0);
                    $('#tarif_par_pagne_modif').val(data.tarif_par_pagne || 0);
                    $('#taux_horaire_modif').val(data.taux_horaire || 0);
                    $('#commission_pourcentage_modif').val(data.commission_pourcentage || 0);
                    $('#specialites_modif').val(data.specialites || '');
                    $('#notes_modif').val(data.notes || '');
                    $('#actif_modif').val(data.actif);
                    
                    $('#type_prestataire_modif').trigger('change');
                    $('#modifier_prestataire').modal('show');
                } else {
                    Swal.fire('Erreur', 'Impossible de charger les données du prestataire', 'error');
                }
            },
            error: function() {
                Swal.fire('Erreur', 'Erreur lors du chargement des données', 'error');
            }
        });
    });
    
    // Suppression prestataire
    $(document).on('click', '.delete-prestataire', function() {
        var id = $(this).data('id');
        Swal.fire({
            title: 'Êtes-vous sûr ?',
            text: "Cette action est irréversible.",
            icon: 'warning',
            showCancelButton: true,
            confirmButtonColor: '#DC2626',
            confirmButtonText: "Oui, supprimer",
            cancelButtonText: "Annuler"
        }).then((result) => {
            if (result.isConfirmed) {
                window.location.href = '../api/modules/supprimer_prestataire.php?id_prestataire=' + id;
            }
        });
    });
    
    // Paiement prestataire
    $(document).on('click', '.payer-prestataire', function() {
        var prestataireId = $(this).data('id');
        var montant = $(this).data('montant');
        var nom = $(this).data('nom');
        
        $('#paiement_id_prestataire').val(prestataireId);
        $('#montant_a_payer_input').val(montant);
        $('#paiement_nom').text(nom);
        $('#mode_paiement').val('especes');
        $('#reference_transaction_paiement').val('');
        $('#remarques_paiement_text').val('');
        
        $('#valider_paiement').modal('show');
    });
    
    // Générer paiement
    $(document).on('click', '.generer-paiement', function() {
        var type = $(this).data('type');
        var titre = '';
        switch(type) {
            case 'couturier': titre = 'Générer les paiements du samedi pour tous les couturiers ?'; break;
            case 'tisseuse': titre = 'Générer les paiements pour toutes les tisseuses ?'; break;
            case 'brodeur': titre = 'Générer les paiements pour tous les brodeurs ?'; break;
            case 'perleuse': titre = 'Générer les paiements pour toutes les perleuses ?'; break;
            case 'mercerie': titre = 'Générer les paiements pour toutes les merceries ?'; break;
            case 'vendeuse': titre = 'Générer les paiements pour toutes les vendeuses ?'; break;
        }
        
        Swal.fire({
            title: 'Confirmation',
            text: titre,
            icon: 'question',
            showCancelButton: true,
            confirmButtonText: 'Oui, générer',
            cancelButtonText: 'Annuler'
        }).then((result) => {
            if (result.isConfirmed) {
                window.location.href = '../api/modules/generer_paiement_prestataires.php?type=' + type;
            }
        });
    });
    
    // Rafraîchir les tableaux après certaines actions
    $(document).on('hidden.bs.modal', '#ajouter_prestataire, #modifier_prestataire, #valider_paiement', function() {
        // Rafraîchir tous les tableaux
        $('.prestataire-table').each(function() {
            if ($.fn.DataTable.isDataTable(this)) {
                $(this).DataTable().ajax.reload(null, false);
            }
        });
    });
});