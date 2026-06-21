$(document).ready(function () {
  // 1. Initialisation de DataTable
  var table = $('#dataTable').DataTable({
    processing: true,
    serverSide: true,
    ajax: {
      url: '../api/modules/utilisateur_data.php',
      type: 'GET',
      dataType: 'json',
      error: function (xhr, status, error) {
        console.error('DataTables AJAX Error:', status, error);
        console.log('Response text:', xhr.responseText);
      }
    },
    dom: 'Blfrtip',
    buttons: [
      { extend: 'pdfHtml5', orientation: 'landscape', pageSize: 'LEGAL' },
      'copyHtml5',
      'excelHtml5',
      'csvHtml5'
    ],
    columns: [
      { data: 'id_utilisateur' },
      { data: 'nom_complet' },
      { data: 'nom_utilisateur' },
      { data: 'email' },
      { data: 'telephone' },
      { data: 'role' },
      { data: 'dernier_acces' },
      { data: 'statut' },
      {
        data: null,
        orderable: false,
        render: function (_data, _type, row) {
          return '<button type="button" ' +
                    'class="btn btn-warning edit-btn" ' +
                    'data-toggle="modal" ' +
                    'data-target="#modifier_utilisateur" ' +
                    'data-id="' + row.id_utilisateur + '">' +
                    '<i class="fa fa-pencil"></i>' +
                  '</button>';
        }
      },
      {
        data: null,
        orderable: false,
        render: function (_data, _type, row) {
          return '<button type="button" ' +
                    'class="btn btn-danger delete-btn" ' +
                    'data-id="' + row.id_utilisateur + '">' +
                    '<i class="fa fa-trash"></i>' +
                  '</button>';
        }
      }
    ],
    order: [[0, 'desc']],
    pageLength: 10,
    lengthMenu: [
      [10, 25, 50, 100, -1],
      [10, 25, 50, 100, 'Tout']
    ],
    language: {
      sProcessing:     "Traitement en cours...",
      sSearch:         "Rechercher :",
      sLengthMenu:     "Afficher _MENU_ éléments",
      sInfo:           "Affichage _START_ à _END_ sur _TOTAL_ éléments",
      sInfoEmpty:      "Affichage 0 à 0 sur 0 éléments",
      sInfoFiltered:   "(filtré de _MAX_ éléments au total)",
      sLoadingRecords: "Chargement en cours...",
      sZeroRecords:    "Aucun élément à afficher",
      sEmptyTable:     "Aucune donnée disponible dans le tableau",
      oPaginate: {
        sFirst:    "Premier",
        sPrevious: "Précédent",
        sNext:     "Suivant",
        sLast:     "Dernier"
      },
      oAria: {
        sSortAscending:  ": activer pour trier la colonne par ordre croissant",
        sSortDescending: ": activer pour trier la colonne par ordre décroissant"
      }
    }
  });

  // 2. Déséchappement HTML
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

  // 3. Clic sur « Modifier » : préremplissage + ouverture
  $('#dataTable tbody').on('click', '.edit-btn', function () {
    // Récupérer les données de la ligne
    var rowData = table.row($(this).closest('tr')).data();
    
    console.log('Données de la ligne:', rowData); // Pour debug
    
    // Remplir le modal avec les données
    $('#id_utilisateur_modif').val(rowData.id_utilisateur);
    $('#nom_complet_modif').val(unEscape(rowData.nom_complet));
    $('#nom_utilisateur_modif').val(unEscape(rowData.nom_utilisateur));
    $('#email_modif').val(unEscape(rowData.email || ''));
    $('#telephone_modif').val(unEscape(rowData.telephone || ''));
    $('#role_modif').val(rowData.role);
    
    // CORRECTION : Utiliser 'status' qui est maintenant disponible
    var statusValue = rowData.status || (rowData.statut === 'Actif' ? 'actif' : 'inactif');
    $('#status_modif').val(statusValue);
    
    // Vider le champ mot de passe
    $('#mot_de_passe_modif').val('');
  });

  // 4. Clic sur « Supprimer » : SweetAlert2
  $('#dataTable tbody').on('click', '.delete-btn', function () {
    var rowData = table.row($(this).closest('tr')).data();
    var id = rowData.id_utilisateur;

    Swal.fire({
      title: 'Êtes-vous sûr ?',
      text: "Cette action est irréversible.",
      icon: 'warning',
      showCancelButton: true,
      confirmButtonColor: '#DC2626',
      cancelButtonColor: '#F59E0B',
      confirmButtonText: "Oui, supprimer",
      cancelButtonText: "Annuler"
    }).then((result) => {
      if (result.isConfirmed) {
        window.location.href = '../api/modules/supprimer_utilisateur.php?id_utilisateur=' + id;
      }
    });
  });
});