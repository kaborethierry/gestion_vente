// Fichier : pages/DataTables/data_table_vehicule.js

$(document).ready(function () {
    // 0) Ciblage de la table
    const $tableEl = $('#dataTableVehicule, #dataTableVehicles').first();
    if ($tableEl.length === 0) {
      console.error('Table cible introuvable (#dataTableVehicule ou #dataTableVehicles).');
      return;
    }
  
    // 1) Initialisation DataTable (serveur)
    const table = $tableEl.DataTable({
      processing: true,
      serverSide: true,
      ajax: {
        url: '../api/modules/vehicule_data.php',
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
        { data: 'id_vehicule', className: 'text-center' },          // N°
        { data: 'client_label', className: 'text-center' },         // Client (raison sociale ou Nom Prénom)
        { data: 'immatriculation', className: 'text-center' },      // Immatriculation
        { data: 'marque', className: 'text-center' },               // Marque
        { data: 'modele', className: 'text-center' },               // Modèle
        { data: 'type_moteur', className: 'text-center' },          // Type moteur
        { data: 'annee', className: 'text-center' },                // Année
        { data: 'kilometrage', className: 'text-center' },          // Kilométrage
        { data: 'couleur', className: 'text-center' },              // Couleur
        { data: 'transmission', className: 'text-center' },         // Transmission
        { data: 'statut_vehicule', className: 'text-center' },      // Statut
        {
          data: null,
          orderable: false,
          className: 'text-center',
          render: function (_data, _type, row) {
            return (
              '<button type="button" ' +
              'class="btn btn-info details-btn" ' +
              'data-toggle="modal" ' +
              'data-target="#details_vehicule" ' +
              'data-id="' + row.id_vehicule + '">' +
              '<i class="fa fa-eye"></i>' +
              '</button>'
            );
          }
        }, // Détails
        {
          data: null,
          orderable: false,
          className: 'text-center',
          render: function (_data, _type, row) {
            return (
              '<button type="button" ' +
              'class="btn btn-warning edit-btn" ' +
              'data-toggle="modal" ' +
              'data-target="#modifier_vehicule" ' +
              'data-id="' + row.id_vehicule + '">' +
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
              'data-id="' + row.id_vehicule + '">' +
              '<i class="fa fa-trash"></i>' +
              '</button>'
            );
          }
        } // Supprimer
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
  
    // 2) Helpers d'affichage/formatage
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
  
    function safeText(v, placeholder = '—') {
      if (v === null || v === undefined) return placeholder;
      const s = String(v).trim();
      return s === '' ? placeholder : s;
    }
  
    function toDisplayDate(yyyy_mm_dd) {
      if (!yyyy_mm_dd) return '—';
      const m = /^(\d{4})-(\d{2})-(\d{2})$/.exec(yyyy_mm_dd);
      return m ? `${m[3]}/${m[2]}/${m[1]}` : safeText(yyyy_mm_dd);
      // fallback: renvoie tel quel si déjà formaté autrement
    }
  
    function toDisplayDateTime(yyyy_mm_dd_hh_mm_ss_or_hh_mm) {
      if (!yyyy_mm_dd_hh_mm_ss_or_hh_mm) return '—';
      const s = String(yyyy_mm_dd_hh_mm_ss_or_hh_mm).trim();
      const parts = s.split(' ');
      if (parts.length < 2) return safeText(s);
      const date = toDisplayDate(parts[0]);
      const time = parts[1].slice(0,5); // HH:mm
      return `${date} ${time}`;
    }
  
    function toInputDate(yyyy_mm_dd) {
      // pour <input type="date">
      if (!yyyy_mm_dd) return '';
      return /^\d{4}-\d{2}-\d{2}$/.test(yyyy_mm_dd) ? yyyy_mm_dd : '';
    }
  
    function toInputDateTimeLocal(yyyy_mm_dd_hh_mm) {
      // pour <input type="datetime-local"> => YYYY-MM-DDTHH:mm
      if (!yyyy_mm_dd_hh_mm) return '';
      const s = String(yyyy_mm_dd_hh_mm).trim();
      // accepte 'YYYY-MM-DD HH:mm' ou 'YYYY-MM-DD HH:mm:ss'
      const m = /^(\d{4}-\d{2}-\d{2})\s+(\d{2}:\d{2})(:\d{2})?$/.exec(s);
      return m ? `${m[1]}T${m[2]}` : '';
    }
  
    function getRowDataFromButton(btn) {
      let $tr = $(btn).closest('tr');
      if ($tr.hasClass('child')) {
        // Ligne responsive: remonter au parent
        $tr = $tr.prev();
      }
      return table.row($tr).data();
    }
  
    // 3) Clic « Détails » : remplissage du modal #details_vehicule
    $tableEl.on('click', '.details-btn', function () {
      const rowData = getRowDataFromButton(this);
      if (!rowData) return;
  
      // Propriétaire
      $('#det_id_vehicule').text(safeText(rowData.id_vehicule));
      $('#det_id_client').text(safeText(rowData.id_client));
      $('#det_client_label').text(safeText(unEscape(rowData.client_label)));
  
      // Identification
      $('#det_immatriculation').text(safeText(unEscape(rowData.immatriculation)));
      $('#det_vin').text(safeText(unEscape(rowData.vin)));
      $('#det_date_ajout').text(toDisplayDateTime(rowData.date_ajout));
      $('#det_date_immatriculation').text(toDisplayDate(rowData.date_immatriculation));
  
      // Caractéristiques (col gauche)
      $('#det_marque').text(safeText(unEscape(rowData.marque)));
      $('#det_modele').text(safeText(unEscape(rowData.modele)));
      $('#det_categorie').text(safeText(unEscape(rowData.categorie)));
      $('#det_annee').text(safeText(rowData.annee));
      $('#det_couleur').text(safeText(unEscape(rowData.couleur)));
      $('#det_couleur_interieur').text(safeText(unEscape(rowData.couleur_interieur)));
      $('#det_nbr_portes').text(safeText(rowData.nbr_portes));
      $('#det_transmission').text(safeText(unEscape(rowData.transmission)));
      $('#det_statut_vehicule').text(safeText(unEscape(rowData.statut_vehicule)));
  
      // Caractéristiques (col droite)
      $('#det_type_moteur').text(safeText(unEscape(rowData.type_moteur)));
      $('#det_capacite_moteur').text(safeText(rowData.capacite_moteur));
      $('#det_puissance_cv').text(safeText(rowData.puissance_cv));
      $('#det_kilometrage').text(safeText(rowData.kilometrage));
      $('#det_conso_urbaine').text(safeText(rowData.conso_urbaine));
      $('#det_conso_extra_urbaine').text(safeText(rowData.conso_extra_urbaine));
      $('#det_emission_co2').text(safeText(rowData.emission_co2));
  
      // Entretiens & Garantie
      $('#det_date_derniere_entretien').text(toDisplayDateTime(rowData.date_derniere_entretien));
      $('#det_kilometrage_derniere_entretien').text(safeText(rowData.kilometrage_derniere_entretien));
      $('#det_date_prochain_entretien').text(toDisplayDateTime(rowData.date_prochain_entretien));
      $('#det_garantie_fin').text(toDisplayDate(rowData.garantie_fin));
  
      // Assurance
      $('#det_type_assurance').text(safeText(unEscape(rowData.type_assurance)));
      $('#det_numero_assurance').text(safeText(unEscape(rowData.numero_assurance)));
      $('#det_date_expiration_assurance').text(toDisplayDate(rowData.date_expiration_assurance));
    });
  
    // 4) Clic « Modifier » : pré-remplissage du modal #modifier_vehicule
    $tableEl.on('click', '.edit-btn', function () {
      const rowData = getRowDataFromButton(this);
      if (!rowData) return;
  
      // Identifiant
      $('#id_vehicule_modif').val(rowData.id_vehicule ?? '');
  
      // Client (select)
      const $clientSelect = $('#id_client_vehicule_modif');
      if ($clientSelect.length) {
        const idCli = rowData.id_client ?? '';
        if (idCli !== '') {
          if ($clientSelect.find(`option[value="${idCli}"]`).length === 0) {
            const label = `${idCli} — ${rowData.client_label ? rowData.client_label : ''}`.trim();
            $clientSelect.append(new Option(label, idCli, true, true));
          } else {
            $clientSelect.val(idCli);
          }
        } else {
          $clientSelect.val('');
        }
      }
  
      // Champs texte / num
      $('#immatriculation_modif').val(unEscape(rowData.immatriculation ?? ''));
      $('#marque_modif').val(unEscape(rowData.marque ?? ''));
      $('#modele_modif').val(unEscape(rowData.modele ?? ''));
      $('#categorie_modif').val(unEscape(rowData.categorie ?? ''));
      $('#type_moteur_modif').val(unEscape(rowData.type_moteur ?? ''));
      $('#capacite_moteur_modif').val(rowData.capacite_moteur ?? '');
      $('#puissance_cv_modif').val(rowData.puissance_cv ?? '');
      $('#annee_modif').val(rowData.annee ?? '');
      $('#kilometrage_modif').val(rowData.kilometrage ?? '');
      $('#couleur_modif').val(unEscape(rowData.couleur ?? ''));
      $('#vin_modif').val(unEscape(rowData.vin ?? ''));
      $('#nbr_portes_modif').val(rowData.nbr_portes ?? '');
      $('#conso_urbaine_modif').val(rowData.conso_urbaine ?? '');
      $('#conso_extra_urbaine_modif').val(rowData.conso_extra_urbaine ?? '');
      $('#emission_co2_modif').val(rowData.emission_co2 ?? '');
      $('#couleur_interieur_modif').val(unEscape(rowData.couleur_interieur ?? ''));
  
      // Selects avec vérification des options
      (function setSelectValue($sel, value, fallback) {
        if (!$sel.length) return;
        const val = value ?? fallback ?? '';
        if (val === '') { $sel.val(''); return; }
        if ($sel.find(`option[value="${val}"]`).length === 0) {
          $sel.append(new Option(val, val, true, true));
        } else {
          $sel.val(val);
        }
      })($('#transmission_modif'), rowData.transmission, 'Manuelle');
  
      (function setSelectValue($sel, value, fallback) {
        if (!$sel.length) return;
        const val = value ?? fallback ?? '';
        if (val === '') { $sel.val(''); return; }
        if ($sel.find(`option[value="${val}"]`).length === 0) {
          $sel.append(new Option(val, val, true, true));
        } else {
          $sel.val(val);
        }
      })($('#statut_vehicule_modif'), rowData.statut_vehicule, 'En service');
  
      // Dates
      $('#date_immatriculation_modif').val(toInputDate(rowData.date_immatriculation));
      $('#date_derniere_entretien_modif').val(toInputDateTimeLocal(rowData.date_derniere_entretien));
      $('#kilometrage_derniere_entretien_modif').val(rowData.kilometrage_derniere_entretien ?? '');
      $('#date_prochain_entretien_modif').val(toInputDateTimeLocal(rowData.date_prochain_entretien));
      $('#garantie_fin_modif').val(toInputDate(rowData.garantie_fin));
      $('#date_expiration_assurance_modif').val(toInputDate(rowData.date_expiration_assurance));
  
      // Assurance
      $('#type_assurance_modif').val(unEscape(rowData.type_assurance ?? ''));
      $('#numero_assurance_modif').val(unEscape(rowData.numero_assurance ?? ''));
    });
  
    // 5) Clic « Supprimer »
    $tableEl.on('click', '.delete-btn', function () {
      const rowData = getRowDataFromButton(this);
      if (!rowData) return;
  
      const id = rowData.id_vehicule;
  
      if (typeof Swal === 'undefined') {
        if (confirm('Confirmer la suppression du véhicule ?')) {
          window.location.href = '../api/modules/supprimer_vehicule.php?id_vehicule=' + encodeURIComponent(id);
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
          window.location.href = '../api/modules/supprimer_vehicule.php?id_vehicule=' + encodeURIComponent(id);
        }
      });
    });
  });
  