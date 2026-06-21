$(document).ready(function () {
    // 1. Initialisation de DataTable
    var table = $('#dataTable').DataTable({
        processing: true,
        serverSide: true,
        ajax: {
            url: '../api/modules/produit_data.php',
            type: 'GET',
            data: function(d) {
                d.filtre_categorie = $('#filtre_categorie').val();
                d.filtre_statut = $('#filtre_statut').val();
                d.filtre_stock_min = $('#filtre_stock_min').val();
                d.filtre_stock_max = $('#filtre_stock_max').val();
            },
            error: function (xhr, status, error) {
                console.error('DataTables AJAX Error:', status, error);
                console.log('Response text:', xhr.responseText);
            }
        },
        dom: 'Blfrtip',
        buttons: [
            { extend: 'pdfHtml5', orientation: 'landscape', pageSize: 'LEGAL', title: 'Liste des produits' },
            'copyHtml5',
            'excelHtml5',
            'csvHtml5'
        ],
        columns: [
            { data: 'photo_html', orderable: false },
            { data: 'code_produit' },
            { data: 'nom' },
            { data: 'categorie_libelle' },
            { data: 'prix_achat_formate' },
            { data: 'prix_vente_formate' },
            { data: 'stock_actuel' },
            { data: 'statut_badge' },
            {
                data: null,
                orderable: false,
                render: function (data) {
                    return '<button class="btn btn-sm btn-warning edit-btn" data-id="' + data.id_produit + '"><i class="fa fa-pencil"></i> Modifier</button>';
                }
            },
            {
                data: null,
                orderable: false,
                render: function (data) {
                    return '<button class="btn btn-sm btn-danger delete-btn" data-id="' + data.id_produit + '"><i class="fa fa-trash"></i> Supprimer</button>';
                }
            },
            {
                data: null,
                orderable: false,
                render: function (data) {
                    return '<button class="btn btn-sm btn-info details-btn" data-id="' + data.id_produit + '"><i class="fa fa-eye"></i> Détail</button>';
                }
            },
            {
                data: null,
                orderable: false,
                render: function (data) {
                    return '<button class="btn btn-sm btn-secondary codebarres-btn" data-id="' + data.id_produit + '" data-code="' + data.code_produit + '"><i class="fa fa-barcode"></i> Code</button>';
                }
            }
        ],
        order: [[1, 'asc']],
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
            },
            oAria: {
                sSortAscending: ": activer pour trier la colonne par ordre croissant",
                sSortDescending: ": activer pour trier la colonne par ordre décroissant"
            }
        }
    });

    // Rafraîchir la table quand les filtres changent
    $('#filtre_categorie, #filtre_statut, #filtre_stock_min, #filtre_stock_max').on('change', function() {
        table.ajax.reload();
    });

    // 2. Import CSV
    $('#btn_import_csv').on('click', function() {
        $('#csv_file_input').click();
    });

    $('#csv_file_input').on('change', function(e) {
        var file = e.target.files[0];
        if (!file) return;
        
        var formData = new FormData();
        formData.append('csv_file', file);
        
        Swal.fire({
            title: 'Import en cours...',
            text: 'Veuillez patienter',
            allowOutsideClick: false,
            didOpen: () => {
                Swal.showLoading();
            }
        });
        
        $.ajax({
            url: '../api/modules/importer_csv_produits.php',
            type: 'POST',
            data: formData,
            processData: false,
            contentType: false,
            success: function(response) {
                Swal.close();
                if (response.success) {
                    Swal.fire('Succès!', response.message, 'success');
                    table.ajax.reload();
                } else {
                    Swal.fire('Erreur!', response.message, 'error');
                }
            },
            error: function() {
                Swal.close();
                Swal.fire('Erreur!', 'Erreur lors de l\'import du fichier CSV', 'error');
            }
        });
        
        $(this).val('');
    });

    // 3. Déséchappement HTML
    function unEscape(html) {
        if (!html) return '';
        return html
            .replace(/&lt;/g, '<')
            .replace(/&gt;/g, '>')
            .replace(/&quot;/g, '"')
            .replace(/&#039;/g, "'")
            .replace(/&amp;/g, '&')
            .replace(/<br>/g, '\n');
    }

    // 4. Clic sur « Modifier » - CORRECTION DU CHEMIN DE LA PHOTO
    $('#dataTable tbody').on('click', '.edit-btn', function () {
        var rowData = table.row($(this).closest('tr')).data();
        
        $('#id_produit_modif').val(rowData.id_produit);
        $('#code_produit_modif').val(rowData.code_produit);
        $('#nom_modif').val(unEscape(rowData.nom));
        $('#description_modif').val(unEscape(rowData.description || ''));
        $('#categorie_modif').val(rowData.categorie);
        $('#sous_categorie_modif').val(rowData.sous_categorie || '');
        $('#prix_achat_modif').val(rowData.prix_achat_valeur);
        $('#prix_vente_modif').val(rowData.prix_vente_valeur);
        $('#unite_mesure_modif').val(rowData.unite_mesure || 'piece');
        $('#stock_actuel_modif').val(rowData.stock_actuel);
        $('#stock_minimum_modif').val(rowData.stock_minimum);
        $('#statut_modif').val(rowData.statut);
        $('#photo_actuelle').val(rowData.photo || '');
        
        // CORRECTION: Chemin absolu pour la photo
        if (rowData.photo) {
            $('#photo_preview').html('<img src="/garagee/uploads/produits/' + rowData.photo + '" style="max-width: 100px; max-height: 100px;">');
        } else {
            $('#photo_preview').html('');
        }
        
        $('#modifier_produit').modal('show');
    });

    // 5. Clic sur « Détails »
    $('#dataTable tbody').on('click', '.details-btn', function () {
        var rowData = table.row($(this).closest('tr')).data();
        var id = rowData.id_produit;
        
        $.ajax({
            url: '../api/modules/details_produit.php',
            type: 'GET',
            data: { id_produit: id },
            dataType: 'html',
            success: function(html) {
                $('#details_contenu').html(html);
                $('#details_produit').modal('show');
            },
            error: function() {
                $('#details_contenu').html('<div class="alert alert-danger">Erreur lors du chargement des détails</div>');
                $('#details_produit').modal('show');
            }
        });
    });

    // 6. Clic sur « Code-barres »
    $('#dataTable tbody').on('click', '.codebarres-btn', function () {
        var rowData = table.row($(this).closest('tr')).data();
        var code = rowData.code_produit;
        var nom = rowData.nom;
        
        // Générer le code-barres en HTML/CSS
        var html = '<div style="text-align: center;">';
        html += '<p><strong>' + nom + '</strong></p>';
        html += '<div style="font-family: monospace; font-size: 24px; letter-spacing: 2px;">';
        html += '*.' + code + '.*';
        html += '</div>';
        html += '<p style="margin-top: 10px;">' + code + '</p>';
        html += '<p><small>Prix: ' + rowData.prix_vente_formate + '</small></p>';
        html += '</div>';
        
        $('#codebarres_contenu').html(html);
        $('#modal_codebarres').modal('show');
    });
    
    $('#imprimer_codebarres').on('click', function() {
        var printContents = $('#codebarres_contenu').html();
        var originalContents = document.body.innerHTML;
        document.body.innerHTML = printContents;
        window.print();
        document.body.innerHTML = originalContents;
        location.reload();
    });

    // 7. Clic sur « Supprimer »
    $('#dataTable tbody').on('click', '.delete-btn', function () {
        var rowData = table.row($(this).closest('tr')).data();
        var id = rowData.id_produit;
        var nom = rowData.nom;

        Swal.fire({
            title: 'Êtes-vous sûr ?',
            text: "Vous allez supprimer le produit : " + nom + ". Cette action est irréversible.",
            icon: 'warning',
            showCancelButton: true,
            confirmButtonColor: '#DC2626',
            cancelButtonColor: '#F59E0B',
            confirmButtonText: "Oui, supprimer",
            cancelButtonText: "Annuler"
        }).then((result) => {
            if (result.isConfirmed) {
                window.location.href = '../api/modules/supprimer_produit.php?id_produit=' + id;
            }
        });
    });
});