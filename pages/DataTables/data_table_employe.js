// pages/DataTables/data_table_employe.js

$(document).ready(function () {
    // 1. Initialisation de DataTable
    var table = $('#dataTable').DataTable({
      processing: true,
      serverSide: true,
      ajax: {
        url: '../api/modules/employe_data.php',
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
        { data: 'id_employe', className: 'text-center' },   // N°
        { data: 'nom', className: 'text-center' },          // Nom
        { data: 'prenom', className: 'text-center' },       // Prénom
        { data: 'poste', className: 'text-center' },        // Poste
        { data: 'email', className: 'text-center' },        // Email
        { data: 'telephone', className: 'text-center' },    // Téléphone
        { data: 'date_embauche', className: 'text-center' },// Date d’embauche
        { data: 'salaire_base', className: 'text-center' }, // Salaire
        { data: 'statut', className: 'text-center' },       // Statut
        {
          data: null,
          orderable: false,
          className: 'text-center',
          render: function (_data, _type, row) {
            return (
              '<button type="button" ' +
              'class="btn btn-warning edit-btn" ' +
              'data-toggle="modal" ' +
              'data-target="#modifier_employe" ' +
              'data-id="' + row.id_employe + '">' +
              '<i class="fa fa-pencil"></i>' +
              '</button>'
            );
          }
        },
        {
          data: null,
          orderable: false,
          className: 'text-center',
          render: function (_data, _type, row) {
            return (
              '<button type="button" ' +
              'class="btn btn-danger delete-btn" ' +
              'data-id="' + row.id_employe + '">' +
              '<i class="fa fa-trash"></i>' +
              '</button>'
            );
          }
        }
      ],
      order: [[0, 'desc']],
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
    $('#dataTable tbody').on('click', '.edit-btn', function () {
      var row = table.row($(this).closest('tr')).data();
  
      $('#id_employe_modif').val(row.id_employe);
      $('#emp_nom_modif').val(unEscape(row.nom));
      $('#emp_prenom_modif').val(unEscape(row.prenom));
      $('#emp_poste_modif').val(unEscape(row.poste));
      $('#emp_email_modif').val(unEscape(row.email));
      $('#emp_tel_modif').val(unEscape(row.telephone));
      $('#emp_date_embauche_modif').val(row.date_embauche);
      $('#emp_salaire_modif').val(row.salaire_base);
      $('#emp_statut_modif').val(row.statut);
  
      // Le modal s’ouvre via data-toggle + data-target
    });
  
    // 4. Clic sur « Supprimer » : SweetAlert2
    $('#dataTable tbody').on('click', '.delete-btn', function () {
      var id = table.row($(this).closest('tr')).data().id_employe;
  
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
          window.location.href = '../api/modules/supprimer_employe.php?id_employe=' + encodeURIComponent(id);
        }
      });
    });
  });
  