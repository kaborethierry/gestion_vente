$(document).ready(function () {
    var tab = $('#dataTable').DataTable({
        processing: true,
        serverSide: true,
        ajax: {
            url: '../api/modules/projet_data.php',
            type: 'POST', // Utilisation de la méthode POST
        },
        dom: 'Blfrtip',
        buttons: [
            {
                extend: 'pdfHtml5',
                orientation: 'landscape',
                pageSize: 'LEGAL',
            },
            'copyHtml5',
            'excelHtml5',
            'csvHtml5'
        ],
        // Pour cet exemple, on suppose que le serveur renvoie 10 colonnes :
        // 0: Numéro de ligne, 1: Code projet, 2: Nom projet, 3: Budget, 4: Date début,
        // 5: Date fin, 6: Description, 7: Responsable, 8: Statut, 9: id_projet.
        // Les colonnes d'actions seront ajoutées en indices 10 (Modifier) et 11 (Supprimer)
        columnDefs: [
            { targets: [0,1,2,3,4,5,6,7,8], createdCell: function (td) { $(td).css('text-align', 'center'); } },
            {
                targets: [10],
                data: null,
                defaultContent: '<span title="Modifier le projet"><button data-toggle="modal" id="modifier" data-backdrop="false" class="open-Modifier_Projet btn btn-warning" href="#modifier_projet"><i class="fa fa-pencil"></i></button></span>',
                createdCell: function (td) { $(td).css('text-align', 'center'); },
                orderable: false
            },
            {
                targets: [11],
                data: null,
                defaultContent: '<span title="Supprimer le projet"><button id="supprimer" data-toggle="modal" data-backdrop="false" class="btn btn-danger" type="submit"><i class="fa fa-trash"></i></button></span>',
                createdCell: function (td) { $(td).css('text-align', 'center'); },
                orderable: false
            },
        ],
        order: [0, "desc"],
        deferRender: true,
        pageLength: 5,
        lengthMenu: [ [1, 2, 3, 4, 5, 10, 25, 50, 100, 200, -1], [1, 2, 3, 4, 5, 10, 25, 50, 100, 200, "Tout"] ],
        language: {
            sProcessing:     "Traitement en cours...",
            sSearch:         "Rechercher&nbsp;:",
            sLengthMenu:     "Afficher _MENU_ éléments",
            sInfo:           "Affichage de l'élément _START_ à _END_ sur _TOTAL_ éléments",
            sInfoEmpty:      "Affichage de l'élément 0 à 0 sur 0 éléments",
            sInfoFiltered:   "(filtré de _MAX_ éléments au total)",
            sInfoPostFix:    "",
            sLoadingRecords: "Chargement en cours...",
            sZeroRecords:    "Aucun élément à afficher",
            sEmptyTable:     "Aucune donnée disponible dans le tableau",
            oPaginate: {
                sFirst:      "Premier",
                sPrevious:   "Précédent",
                sNext:       "Suivant",
                sLast:       "Dernier"
            },
            oAria: {
                sSortAscending:  ": activer pour trier la colonne par ordre croissant",
                sSortDescending: ": activer pour trier la colonne par ordre décroissant"
            }
        }
    });

    function unEscape(htmlStr) {
        if (htmlStr) {
            htmlStr = htmlStr.replace(/&lt;/g, "<");
            htmlStr = htmlStr.replace(/&gt;/g, ">");
            htmlStr = htmlStr.replace(/&quot;/g, "\"");
            htmlStr = htmlStr.replace(/&#039;/g, "\'");
            htmlStr = htmlStr.replace(/&amp;/g, "&");
            htmlStr = htmlStr.replace(/<br>/g, "\n");
            return htmlStr;
        }
    };

    // Modifier projet
    $('#dataTable tbody').on('click', '#modifier', function () {
        var data = tab.row($(this).parents('tr')).data();
        // Dans notre cas, d'après la configuration serveur :
        // data[9] correspond à l'identifiant du projet.
        var id = data[9];
        var code_projet = unEscape(data[1]);
        var nom_projet = unEscape(data[2]);
        var budget = unEscape(data[3]);
        var date_debut = unEscape(data[4]);
        var date_fin = unEscape(data[5]);
        var description = unEscape(data[6]);
        var responsable = unEscape(data[7]);
        var statut = unEscape(data[8]);

        $('#id_projet').val(id);
        $('#code_projet_modif').val(code_projet);
        $('#nom_projet_modif').val(nom_projet);
        $('#budget_modif').val(budget);
        $('#date_debut_modif').val(date_debut);
        $('#date_fin_modif').val(date_fin);
        $('#description_modif').val(description);
        $('#responsable_modif').val(responsable);
        $('#statut_modif').val(statut);
    });

    // Supprimer projet
    $('#dataTable tbody').on('click', '#supprimer', function () {
        var data = tab.row($(this).parents('tr')).data();
        var id_projet = data[9];
        Swal.fire({
            title: 'Êtes-vous sûr ?',
            text: "Voulez-vous vraiment supprimer le projet ?",
            icon: 'warning',
            showCancelButton: true,
            confirmButtonColor: '#d33',
            cancelButtonColor: '#6b6c6c',
            confirmButtonText: "Oui, supprimer le projet",
            cancelButtonText: "Annuler"
        }).then((result) => {
            if (result.isConfirmed) {
                window.location.href = './../api/modules/supprimer_projet.php?id_projet=' + id_projet;
            }
        });
    });
});
