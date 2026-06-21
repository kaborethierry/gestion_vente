$(document).ready(function() {
    // Table des paiements
    if ($('#dataTablePaiements').length) {
        var tablePaiements = $('#dataTablePaiements').DataTable({
            processing: true,
            serverSide: true,
            ajax: {
                url: '../api/modules/paiement_data.php',
                type: 'GET',
                data: function(d) {
                    d.action = 'get_paiements';
                    d.mode_paiement = $('#filtre_mode').val();
                    d.date_debut = $('#filtre_date_debut').val();
                    d.date_fin = $('#filtre_date_fin').val();
                }
            },
            dom: 'Blfrtip',
            buttons: [
                { extend: 'pdfHtml5', orientation: 'landscape', pageSize: 'LEGAL', title: 'Liste des paiements' },
                'copyHtml5',
                'excelHtml5',
                'csvHtml5',
                'print'
            ],
            columns: [
                { data: 'id_paiement', title: 'N°' },
                { data: 'numero_commande', title: 'N° Commande' },
                { data: 'client_nom', title: 'Client' },
                { 
                    data: 'montant', title: 'Montant',
                    render: function(data) {
                        return parseInt(data).toLocaleString('fr-FR') + ' CFA';
                    }
                },
                { 
                    data: 'type_paiement', title: 'Type',
                    render: function(data) {
                        if (data === 'avance') return '<span class="badge badge-info">Avance</span>';
                        if (data === 'acompte_supplementaire') return '<span class="badge badge-warning">Acompte</span>';
                        return '<span class="badge badge-success">Solde</span>';
                    }
                },
                { 
                    data: 'mode_paiement', title: 'Mode',
                    render: function(data) {
                        if (data === 'especes') return '<i class="fas fa-money-bill-wave"></i> Espèces';
                        if (data === 'carte') return '<i class="fas fa-credit-card"></i> Carte';
                        if (data === 'mobile_money') return '<i class="fas fa-mobile-alt"></i> Mobile Money';
                        return '<i class="fas fa-university"></i> Virement';
                    }
                },
                { data: 'reference_transaction', title: 'Référence' },
                { data: 'caissier', title: 'Caissier' },
                { data: 'date_paiement', title: 'Date' },
                { 
                    data: null, title: 'Modifier', orderable: false,
                    render: function(data) {
                        if (data.user_role === 'admin') {
                            return '<button class="btn btn-warning btn-sm edit-paiement" data-id="' + data.id_paiement + '" title="Modifier"><i class="fa fa-pencil"></i></button>';
                        }
                        return '<button class="btn btn-warning btn-sm" disabled><i class="fa fa-pencil"></i></button>';
                    }
                },
                { 
                    data: null, title: 'Supprimer', orderable: false,
                    render: function(data) {
                        if (data.user_role === 'admin') {
                            return '<button class="btn btn-danger btn-sm delete-paiement" data-id="' + data.id_paiement + '" title="Supprimer"><i class="fa fa-trash"></i></button>';
                        }
                        return '<button class="btn btn-danger btn-sm" disabled><i class="fa fa-trash"></i></button>';
                    }
                },
                { 
                    data: null, title: 'Imprimer', orderable: false,
                    render: function(data) {
                        return '<button class="btn btn-info btn-sm print-recu" data-id="' + data.id_paiement + '" title="Imprimer reçu"><i class="fa fa-print"></i></button>';
                    }
                }
            ],
            order: [[8, 'desc']],
            pageLength: 10,
            language: {
                sProcessing: "Traitement en cours...",
                sSearch: "Rechercher :",
                sLengthMenu: "Afficher _MENU_ éléments",
                sInfo: "Affichage _START_ à _END_ sur _TOTAL_ éléments",
                sInfoEmpty: "Affichage 0 à 0 sur 0 éléments",
                sInfoFiltered: "(filtré de _MAX_ éléments au total)",
                sLoadingRecords: "Chargement en cours...",
                sZeroRecords: "Aucun élément à afficher",
                sEmptyTable: "Aucune donnée disponible dans le tableau",
                oPaginate: {
                    sFirst: "Premier",
                    sPrevious: "Précédent",
                    sNext: "Suivant",
                    sLast: "Dernier"
                }
            }
        });
        
        // Modifier un paiement
        $('#dataTablePaiements tbody').on('click', '.edit-paiement', function() {
            var data = tablePaiements.row($(this).closest('tr')).data();
            $('#modif_id_paiement').val(data.id_paiement);
            $('#modif_numero_commande').val(data.numero_commande);
            $('#modif_client_nom').val(data.client_nom);
            $('#modif_montant').val(data.montant);
            $('#modif_type_paiement').val(data.type_paiement);
            $('#modif_mode_paiement').val(data.mode_paiement);
            $('#modif_reference_transaction').val(data.reference_transaction || '');
            $('#modif_remarques').val(data.remarques || '');
            $('#modifier_paiement').modal('show');
        });
        
        // Supprimer un paiement
        $('#dataTablePaiements tbody').on('click', '.delete-paiement', function() {
            var data = tablePaiements.row($(this).closest('tr')).data();
            Swal.fire({
                title: 'Supprimer le paiement',
                text: 'Êtes-vous sûr de vouloir supprimer ce paiement ? Cette action est irréversible et recalculera le solde de la commande.',
                icon: 'warning',
                showCancelButton: true,
                confirmButtonText: 'Oui, supprimer',
                cancelButtonText: 'Annuler',
                confirmButtonColor: '#DC2626'
            }).then((result) => {
                if (result.isConfirmed) {
                    window.location.href = '../api/modules/supprimer_paiement.php?id_paiement=' + data.id_paiement;
                }
            });
        });
        
        // IMPRIMER - Ouvre une nouvelle fenêtre avec SweetAlert
        $('#dataTablePaiements tbody').on('click', '.print-recu', function(e) {
            e.preventDefault();
            var data = tablePaiements.row($(this).closest('tr')).data();
            
            Swal.fire({
                title: 'Impression du reçu',
                text: 'Voulez-vous imprimer le reçu de paiement N° ' + data.id_paiement + ' ?',
                icon: 'question',
                showCancelButton: true,
                confirmButtonText: 'Oui, imprimer',
                cancelButtonText: 'Annuler',
                confirmButtonColor: '#10B981',
                cancelButtonColor: '#DC2626'
            }).then((result) => {
                if (result.isConfirmed) {
                    // Ouvrir une nouvelle fenêtre avec le reçu
                    var printWindow = window.open('../api/modules/imprimer_recu.php?id_paiement=' + data.id_paiement, '_blank', 'width=500,height=600');
                    if (!printWindow) {
                        Swal.fire({
                            title: 'Popup bloqué',
                            text: 'Veuillez autoriser les popups pour ce site.',
                            icon: 'error',
                            confirmButtonColor: '#DC2626'
                        });
                    }
                }
            });
        });
    }
    
    // Table des commandes
    if ($('#dataTableCommandes').length) {
        var tableCommandes = $('#dataTableCommandes').DataTable({
            processing: true,
            serverSide: true,
            ajax: {
                url: '../api/modules/paiement_data.php',
                type: 'GET',
                data: function(d) {
                    d.action = 'get_commandes';
                    d.statut_paiement = $('#filtre_statut').val();
                }
            },
            dom: 'Blfrtip',
            buttons: [
                { extend: 'pdfHtml5', orientation: 'landscape', pageSize: 'LEGAL', title: 'Situation des commandes' },
                'copyHtml5',
                'excelHtml5',
                'csvHtml5'
            ],
            columns: [
                { data: 'numero_commande', title: 'N° Commande' },
                { data: 'client_nom', title: 'Client' },
                { 
                    data: 'montant_total', title: 'Montant total',
                    render: function(data) {
                        return parseInt(data).toLocaleString('fr-FR') + ' CFA';
                    }
                },
                { 
                    data: 'montant_avance', title: 'Avance versée',
                    render: function(data) {
                        return parseInt(data).toLocaleString('fr-FR') + ' CFA';
                    }
                },
                { 
                    data: 'solde_restant', title: 'Solde restant',
                    render: function(data) {
                        var solde = parseFloat(data);
                        if (solde === 0) return '<span class="text-success">Soldé</span>';
                        if (solde < 0) return '<span class="text-danger">' + solde.toLocaleString('fr-FR') + ' CFA (Dépassement)</span>';
                        return '<span class="text-warning">' + solde.toLocaleString('fr-FR') + ' CFA</span>';
                    }
                },
                { 
                    data: 'statut_paiement', title: 'Statut paiement',
                    render: function(data) {
                        if (data === 'Soldé') return '<span class="badge badge-success">Soldé</span>';
                        if (data === 'Partiellement payé') return '<span class="badge badge-warning">Partiel</span>';
                        return '<span class="badge badge-danger">Aucun paiement</span>';
                    }
                },
                { 
                    data: 'statut_commande', title: 'Statut commande',
                    render: function(data) {
                        if (data === 'termine') return '<span class="badge badge-success">Terminée</span>';
                        if (data === 'livre') return '<span class="badge badge-info">Livrée</span>';
                        if (data === 'en_cours') return '<span class="badge badge-primary">En cours</span>';
                        if (data === 'annule') return '<span class="badge badge-danger">Annulée</span>';
                        return '<span class="badge badge-secondary">En attente</span>';
                    }
                },
                {
                    data: null, title: 'Action', orderable: false,
                    render: function(data) {
                        if (parseFloat(data.solde_restant) > 0) {
                            return '<button class="btn btn-sm btn-success encaisser-solde" data-id="' + data.id_commande + '" data-solde="' + data.solde_restant + '" title="Encaisser le solde">' +
                                   '<i class="fas fa-money-bill-wave"></i> Encaisser solde</button>';
                        }
                        return '<button class="btn btn-sm btn-secondary" disabled><i class="fas fa-check"></i> Soldé</button>';
                    }
                }
            ],
            order: [[4, 'desc']],
            pageLength: 10,
            language: {
                sProcessing: "Traitement en cours...",
                sSearch: "Rechercher :",
                sLengthMenu: "Afficher _MENU_ éléments",
                sInfo: "Affichage _START_ à _END_ sur _TOTAL_ éléments",
                sInfoEmpty: "Affichage 0 à 0 sur 0 éléments",
                sInfoFiltered: "(filtré de _MAX_ éléments au total)",
                sLoadingRecords: "Chargement en cours...",
                sZeroRecords: "Aucun élément à afficher",
                sEmptyTable: "Aucune donnée disponible dans le tableau"
            }
        });
        
        // Encaisser le solde d'une commande
        $('#dataTableCommandes tbody').on('click', '.encaisser-solde', function() {
            var data = tableCommandes.row($(this).closest('tr')).data();
            $('#id_commande').val(data.id_commande);
            $('#montant').val(parseFloat(data.solde_restant));
            $('#type_paiement').val('solde');
            $('#ajouter_paiement').modal('show');
            
            setTimeout(function() {
                var event = new Event('change');
                document.getElementById('id_commande').dispatchEvent(event);
            }, 100);
        });
    }
});

function applyFilters() {
    if ($.fn.DataTable.isDataTable('#dataTableCommandes')) {
        $('#dataTableCommandes').DataTable().ajax.reload();
    }
    if ($.fn.DataTable.isDataTable('#dataTablePaiements')) {
        $('#dataTablePaiements').DataTable().ajax.reload();
    }
}