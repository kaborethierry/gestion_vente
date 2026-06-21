$(document).ready(function () {
    // Initialisation de DataTable pour la gestion des ventes
    if ($('#dataTableVentes').length) {
        var table = $('#dataTableVentes').DataTable({
            processing: true,
            serverSide: true,
            ajax: {
                url: '../api/modules/ventes_data.php',
                type: 'GET',
                error: function (xhr, status, error) {
                    console.error('DataTables AJAX Error:', status, error);
                    console.log('Response text:', xhr.responseText);
                }
            },
            dom: 'Blfrtip',
            buttons: [
                { extend: 'pdfHtml5', orientation: 'landscape', pageSize: 'LEGAL', title: 'Liste des ventes' },
                'copyHtml5',
                'excelHtml5',
                'csvHtml5',
                'print'
            ],
            columns: [
                { data: 'numero_vente' },
                { data: 'date_vente' },
                { data: 'caissier' },
                { data: 'client' },
                { data: 'total_ttc_formate' },
                { data: 'mode_paiement_label' },
                { data: 'statut_badge' },
                {
                    data: null,
                    orderable: false,
                    render: function (data) {
                        let buttons = '<div class="btn-group btn-group-sm">';
                        buttons += '<button class="btn btn-info view-sale" data-id="' + data.id_vente + '" title="Voir détails"><i class="fa fa-eye"></i></button>';
                        
                        if (data.statut === 'valide') {
                            buttons += '<button class="btn btn-warning cancel-sale" data-id="' + data.id_vente + '" title="Annuler"><i class="fa fa-times"></i></button>';
                            buttons += '<button class="btn btn-secondary refund-sale" data-id="' + data.id_vente + '" title="Rembourser"><i class="fa fa-undo"></i></button>';
                        }
                        
                        if (data.statut === 'annule' || data.statut === 'remboursee') {
                            buttons += '<button class="btn btn-danger delete-sale" data-id="' + data.id_vente + '" title="Supprimer définitivement"><i class="fa fa-trash"></i></button>';
                        }
                        
                        buttons += '<button class="btn btn-success print-ticket" data-id="' + data.id_vente + '" title="Réimprimer ticket"><i class="fa fa-print"></i></button>';
                        buttons += '</div>';
                        return buttons;
                    }
                }
            ],
            order: [[1, 'desc']],
            pageLength: 10,
            lengthMenu: [[10, 25, 50, 100, -1], [10, 25, 50, 100, 'Tout']],
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
        
        $('#dataTableVentes tbody').on('click', '.view-sale', function() {
            var data = table.row($(this).closest('tr')).data();
            viewSaleDetails(data.id_vente);
        });
        
        $('#dataTableVentes tbody').on('click', '.cancel-sale', function() {
            var data = table.row($(this).closest('tr')).data();
            Swal.fire({
                title: 'Annuler la vente',
                text: 'Veuillez indiquer le motif d\'annulation',
                input: 'textarea',
                inputPlaceholder: 'Motif de l\'annulation...',
                showCancelButton: true,
                confirmButtonText: 'Annuler la vente',
                cancelButtonText: 'Fermer',
                confirmButtonColor: '#DC2626'
            }).then((result) => {
                if (result.isConfirmed && result.value) {
                    window.location.href = '../api/modules/modifier_pos.php?action=annuler_vente&id_vente=' + data.id_vente + '&motif=' + encodeURIComponent(result.value);
                }
            });
        });
        
        $('#dataTableVentes tbody').on('click', '.refund-sale', function() {
            var data = table.row($(this).closest('tr')).data();
            Swal.fire({
                title: 'Rembourser la vente',
                text: 'Veuillez indiquer le motif du remboursement',
                input: 'textarea',
                inputPlaceholder: 'Motif du remboursement...',
                showCancelButton: true,
                confirmButtonText: 'Rembourser',
                cancelButtonText: 'Fermer',
                confirmButtonColor: '#F59E0B'
            }).then((result) => {
                if (result.isConfirmed && result.value) {
                    window.location.href = '../api/modules/modifier_pos.php?action=rembourser_vente&id_vente=' + data.id_vente + '&motif=' + encodeURIComponent(result.value);
                }
            });
        });
        
        $('#dataTableVentes tbody').on('click', '.delete-sale', function() {
            var data = table.row($(this).closest('tr')).data();
            Swal.fire({
                title: 'Suppression définitive',
                text: 'Cette action est irréversible. Voulez-vous vraiment supprimer cette vente ?',
                icon: 'warning',
                showCancelButton: true,
                confirmButtonText: 'Oui, supprimer',
                cancelButtonText: 'Annuler',
                confirmButtonColor: '#DC2626'
            }).then((result) => {
                if (result.isConfirmed) {
                    window.location.href = '../api/modules/supprimer_pos.php?id_vente=' + data.id_vente;
                }
            });
        });
        
        $('#dataTableVentes tbody').on('click', '.print-ticket', function() {
            var data = table.row($(this).closest('tr')).data();
            printSaleTicket(data.id_vente);
        });
    }
});

function viewSaleDetails(id_vente) {
    $.ajax({
        url: '../api/modules/vente_details.php',
        type: 'GET',
        data: { id_vente: id_vente },
        dataType: 'html',
        success: function(html) {
            Swal.fire({
                title: 'Détails de la vente',
                html: html,
                width: '800px',
                confirmButtonText: 'Fermer',
                confirmButtonColor: '#DC2626'
            });
        },
        error: function() {
            Swal.fire('Erreur', 'Impossible de charger les détails de la vente', 'error');
        }
    });
}

function printSaleTicket(id_vente) {
    $.ajax({
        url: '../api/modules/imprimer_ticket.php',
        type: 'GET',
        data: { id_vente: id_vente },
        dataType: 'html',
        success: function(html) {
            var printWindow = window.open('', '_blank');
            printWindow.document.write(html);
            printWindow.document.close();
            printWindow.print();
        },
        error: function() {
            Swal.fire('Erreur', 'Impossible d\'imprimer le ticket', 'error');
        }
    });
}