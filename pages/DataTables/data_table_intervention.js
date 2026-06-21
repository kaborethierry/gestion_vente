// Fichier : pages/DataTables/data_table_intervention.js

$(document).ready(function () {
    const $tableEl = $('#dataTable, #dataTableInterventions').first();
    if ($tableEl.length === 0) {
      console.error('Table cible introuvable (#dataTable ou #dataTableInterventions).');
      return;
    }
  
    const table = $tableEl.DataTable({
      processing: true,
      serverSide: true,
      ajax: {
        url: '../api/modules/intervention_data.php',
        type: 'GET',
        dataType: 'json',
        dataSrc: 'data',
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
        { data: 'id_intervention', className: 'text-center' },
        { data: 'vehicule_label', className: 'text-center' },
        { data: 'employe_label', className: 'text-center' },
        { data: 'type_intervention', className: 'text-center' },
        { data: 'date_debut', className: 'text-center' },
        { data: 'date_fin', className: 'text-center' },
        { data: 'kilometrage', className: 'text-center' },
        { data: 'statut', className: 'text-center' },
        { data: 'priorite', className: 'text-center' },
        { data: 'temps_estime', className: 'text-center' },
        { data: 'temps_reel', className: 'text-center' },
        { data: 'main_oeuvre_ht', className: 'text-center' },
        {
          data: null,
          orderable: false,
          className: 'text-center',
          render: function (_data, _type, row) {
            return (
              '<button type="button" ' +
              'class="btn btn-info details-btn" ' +
              'data-toggle="modal" ' +
              'data-target="#details_intervention" ' +
              'data-id="' + row.id_intervention + '">' +
              '<i class="fa fa-eye"></i>' +
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
              'class="btn btn-warning edit-btn" ' +
              'data-toggle="modal" ' +
              'data-target="#modifier_intervention" ' +
              'data-id="' + row.id_intervention + '">' +
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
              'data-id="' + row.id_intervention + '">' +
              '<i class="fa fa-trash"></i>' +
              '</button>'
            );
          }
        }
      ],
      // Nettoyage visuel des null/undefined sur les colonnes sensibles
      columnDefs: [
        {
          targets: [4, 5], // date_debut, date_fin
          render: function (data) { return data || ''; }
        },
        {
          targets: [6, 9, 10, 11], // kilometrage, temps_estime, temps_reel, main_oeuvre_ht
          render: function (data) { return (data ?? '') === null ? '' : (data ?? ''); }
        },
        {
          targets: [1, 2, 3, 7, 8], // libellés texte
          render: function (data) { return data || ''; }
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
  
    function unEscape(html) {
      if (html === null || html === undefined) return '';
      return String(html)
        .replace(/&lt;/g, '<')
        .replace(/&gt;/g, '>')
        .replace(/&quot;/g, '"')
        .replace(/&#039;/g, "'")
        .replace(/&amp;/g, '&')
        .replace(/<br\s*\/?>/gi, '\n');
    }
  
    // Normalise "YYYY-MM-DD HH:mm:ss" -> "YYYY-MM-DDTHH:mm" (input[type="datetime-local"])
    function toDatetimeLocal(value) {
      if (!value) return '';
      if (value.startsWith('0000-00-00')) return '';
      const m = /^(\d{4}-\d{2}-\d{2})\s+(\d{2}:\d{2})/.exec(value);
      return m ? `${m[1]}T${m[2]}` : '';
    }
  
    function getRowDataFromBtn(btn) {
      let tr = $(btn).closest('tr');
      // Gestion cas responsive: si clic depuis une ligne enfant
      if (tr.hasClass('child')) {
        tr = tr.prev(); // ligne parent correspondante
      }
      const row = table.row(tr);
      return row.data();
    }
  
    $tableEl.on('click', '.edit-btn', function () {
      const rowData = getRowDataFromBtn(this);
      if (!rowData) return;
  
      $('#id_intervention_modif').val(rowData.id_intervention);
      $('#vehicule_modif').val(rowData.id_vehicule);
      $('#employe_modif').val(rowData.id_employe);
      $('#type_modif').val(rowData.type_intervention);
      $('#kilometrage_modif').val(rowData.kilometrage ?? '');
      $('#date_debut_modif').val(toDatetimeLocal(rowData.date_debut));
      $('#date_fin_modif').val(toDatetimeLocal(rowData.date_fin));
      $('#description_modif').val(unEscape(rowData.description));
      $('#statut_modif').val(rowData.statut);
      $('#priorite_modif').val(rowData.priorite);
      $('#temps_estime_modif').val(rowData.temps_estime ?? '');
      $('#temps_reel_modif').val(rowData.temps_reel ?? '');
      $('#main_oeuvre_modif').val(rowData.main_oeuvre_ht ?? '');
      if ($('#remarques_modif').length) {
        $('#remarques_modif').val(unEscape(rowData.remarques));
      }
    });
  
    $tableEl.on('click', '.details-btn', function () {
      const rowData = getRowDataFromBtn(this);
      if (!rowData) return;
  
      $('#det_id_intervention').text(rowData.id_intervention);
      $('#det_vehicule_label').text(unEscape(rowData.vehicule_label));
      $('#det_employe_label').text(unEscape(rowData.employe_label));
      $('#det_type_intervention').text(unEscape(rowData.type_intervention));
      $('#det_statut').text(rowData.statut || '');
      $('#det_priorite').text(rowData.priorite || '');
      $('#det_date_debut').text(rowData.date_debut || '');
      $('#det_date_fin').text(rowData.date_fin || '');
      $('#det_kilometrage').text(rowData.kilometrage ?? '');
      $('#det_temps_estime').text(rowData.temps_estime ?? '');
      $('#det_temps_reel').text(rowData.temps_reel ?? '');
      $('#det_main_oeuvre_ht').text(rowData.main_oeuvre_ht ?? '');
      $('#det_description').html(unEscape(rowData.description));
      $('#det_remarques').html(unEscape(rowData.remarques));
    });
  
    $tableEl.on('click', '.delete-btn', function () {
      const rowData = getRowDataFromBtn(this);
      if (!rowData) return;
  
      const id = rowData.id_intervention;
  
      if (typeof Swal === 'undefined') {
        if (confirm('Confirmer la suppression ?')) {
          window.location.href = '../api/modules/supprimer_intervention.php?id_intervention=' + encodeURIComponent(id);
        }
        return;
      }
  
      Swal.fire({
        title: 'Êtes-vous sûr ?',
        text: 'Cette action est irréversible.',
        icon: 'warning',
        showCancelButton: true,
        confirmButtonColor: '#d33',
        cancelButtonColor: '#3085d6',
        confirmButtonText: 'Oui, supprimer',
        cancelButtonText: 'Annuler'
      }).then((result) => {
        if (result.isConfirmed) {
          window.location.href = '../api/modules/supprimer_intervention.php?id_intervention=' + encodeURIComponent(id);
        }
      });
    });
  });
  