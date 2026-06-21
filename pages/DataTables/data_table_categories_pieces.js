// pages/DataTables/data_table_categories_pieces.js

$(document).ready(function () {
    // 1. Initialisation de DataTable
    var table = $('#dataTable').DataTable({
      processing: true,
      serverSide: true,
      ajax: {
        url: '../api/modules/categories_pieces_data.php',
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
        {
          data: 'id_categorie',
          className: 'text-center',
          render: function (_data, _type, _row, meta) {
            // Numérotation 1..n
            return meta.settings._iDisplayStart + meta.row + 1;
          }
        }, // N°
        { data: 'libelle', className: 'text-center' }, // Libellé
        {
          data: 'description',
          className: 'text-center',
          render: function (data) {
            if (!data) return '';
            return data.length > 120 ? data.substring(0, 117) + '…' : data;
          }
        }, // Description
        {
          data: null,
          orderable: false,
          className: 'text-center',
          render: function (_data, _type, row) {
            return (
              '<button type="button" ' +
              'class="btn btn-warning edit-btn" ' +
              'data-toggle="modal" ' +
              'data-target="#modifier_categorie_piece" ' +
              'data-id="' + row.id_categorie + '">' +
              '<i class="fa fa-pencil"></i>' +
              '</button>'
            );
          }
        }, // Modifier
        {
          data: null,
          orderable: false,
          className: 'text-center',
          render: function (_data, _type, row) {
            return (
              '<button type="button" ' +
              'class="btn btn-danger delete-btn" ' +
              'data-id="' + row.id_categorie + '">' +
              '<i class="fa fa-trash"></i>' +
              '</button>'
            );
          }
        } // Supprimer
      ],
      order: [[1, 'asc']], // Tri par libellé
      pageLength: 5,
      lengthMenu: [
        [5, 10, 25, 50, -1],
        [5, 10, 25, 50, 'Tout']
      ],
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
  
    // 2. Déséchappement HTML (si vos données contiennent des entités)
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
    // NB: On récupère les valeurs directement de la ligne (comme Employés).
    $('#dataTable tbody').on('click', '.edit-btn', function () {
      var row = table.row($(this).closest('tr')).data();
  
      $('#id_categorie_edit').val(row.id_categorie);
      $('#libelle_edit').val(unEscape(row.libelle));
      $('#description_edit').val(unEscape(row.description));
      // Le modal s’ouvre via data-toggle + data-target
    });
  
    // 4. Clic sur « Supprimer » : SweetAlert2 + redirection (même logique que Employés)
    $('#dataTable tbody').on('click', '.delete-btn', function () {
      var id = table.row($(this).closest('tr')).data().id_categorie;
  
      Swal.fire({
        title: 'Êtes-vous sûr ?',
        text: "Cette action est irréversible.",
        icon: 'warning',
        showCancelButton: true,
        confirmButtonColor: '#d33',
        cancelButtonColor: '#3085d6',
        confirmButtonText: "Oui, supprimer",
        cancelButtonText: "Annuler"
      }).then((result) => {
        if (result.isConfirmed) {
          // Suppression logique via API modules
          window.location.href = '../api/modules/supprimer_categorie_piece.php?id_categorie=' + encodeURIComponent(id);
        }
      });
    });
  });
  