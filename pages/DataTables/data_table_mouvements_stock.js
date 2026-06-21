$(document).ready(function() {
    if ($('#dataTableMouvements').length) {
        var table = $('#dataTableMouvements').DataTable({
            processing: true,
            serverSide: true,
            ajax: {
                url: '../api/modules/mouvements_stock_data.php',
                type: 'GET',
                data: function(d) {
                    d.type = $('#filtre_type').val();
                    d.produit = $('#filtre_produit').val();
                    d.date_debut = $('#filtre_date_debut').val();
                    d.date_fin = $('#filtre_date_fin').val();
                }
            },
            dom: 'Blfrtip',
            buttons: [
                { extend: 'pdfHtml5', orientation: 'landscape', pageSize: 'LEGAL', title: 'Mouvements de stock' },
                'copyHtml5',
                'excelHtml5',
                'csvHtml5',
                'print'
            ],
            columns: [
                { data: 'id_mouvement' },
                { data: 'date_mouvement' },
                { data: 'produit_nom' },
                { 
                    data: 'type_mouvement',
                    render: function(data) {
                        var classes = {
                            'entree': 'badge-entree',
                            'sortie': 'badge-sortie',
                            'vente': 'badge-vente',
                            'ajustement': 'badge-ajustement',
                            'retour': 'badge-retour'
                        };
                        var labels = {
                            'entree': 'Entrée',
                            'sortie': 'Sortie',
                            'vente': 'Vente',
                            'ajustement': 'Ajustement',
                            'retour': 'Retour'
                        };
                        return '<span class="badge ' + (classes[data] || 'badge-secondary') + '">' + (labels[data] || data) + '</span>';
                    }
                },
                { 
                    data: 'quantite',
                    render: function(data) {
                        var val = parseInt(data);
                        if (val > 0) return '<span class="text-success">+' + val.toLocaleString('fr-FR') + '</span>';
                        return '<span class="text-danger">' + val.toLocaleString('fr-FR') + '</span>';
                    }
                },
                { data: 'stock_avant' },
                { data: 'stock_apres' },
                { data: 'reference' },
                { data: 'utilisateur' },
                { data: 'motif' },
                {
                    data: null,
                    orderable: false,
                    render: function(data) {
                        return '<button class="btn btn-modifier btn-sm edit-mouvement" data-id="' + data.id_mouvement + '" title="Modifier"><i class="fas fa-edit"></i> Modifier</button>';
                    }
                },
                {
                    data: null,
                    orderable: false,
                    render: function(data) {
                        return '<button class="btn btn-supprimer btn-sm delete-mouvement" data-id="' + data.id_mouvement + '" title="Supprimer"><i class="fas fa-trash"></i> Supprimer</button>';
                    }
                }
            ],
            order: [[0, 'desc']],
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
        
        // Modifier un mouvement
        $('#dataTableMouvements tbody').on('click', '.edit-mouvement', function() {
            var data = table.row($(this).closest('tr')).data();
            $('#modif_id_mouvement').val(data.id_mouvement);
            $('#modif_produit_nom').val(data.produit_nom);
            $('#modif_created_at').val(data.date_mouvement);
            $('#modif_type_mouvement').val(data.type_mouvement);
            $('#modif_quantite').val(Math.abs(data.quantite));
            $('#modif_reference').val(data.reference || '');
            $('#modif_motif').val(data.motif || '');
            $('#modifier_mouvement').modal('show');
        });
        
        // Supprimer un mouvement
        $('#dataTableMouvements tbody').on('click', '.delete-mouvement', function() {
            var data = table.row($(this).closest('tr')).data();
            Swal.fire({
                title: 'Supprimer le mouvement',
                text: 'Êtes-vous sûr de vouloir supprimer ce mouvement ? Cette action rétablira le stock précédent.',
                icon: 'warning',
                showCancelButton: true,
                confirmButtonText: 'Oui, supprimer',
                cancelButtonText: 'Annuler',
                confirmButtonColor: '#DC2626'
            }).then((result) => {
                if (result.isConfirmed) {
                    window.location.href = '../api/modules/supprimer_mouvement_stock.php?id_mouvement=' + data.id_mouvement;
                }
            });
        });
    }
});

function applyFilters() {
    if ($.fn.DataTable.isDataTable('#dataTableMouvements')) {
        $('#dataTableMouvements').DataTable().ajax.reload();
    }
}