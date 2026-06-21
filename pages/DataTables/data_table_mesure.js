// pages/DataTables/data_table_mesure.js
// DANFANIMENT POS - Gestion des mesures clients

function initMesuresTable(clientId) {
    $('#dataTableMesures').DataTable({
        processing: true,
        serverSide: true,
        destroy: true,
        ajax: {
            url: '../api/modules/mesure_data.php',
            type: 'GET',
            data: { id_client: clientId }
        },
        columns: [
            { data: 'version' },
            { data: 'date_mesure' },
            { data: 'poitrine' },
            { data: 'tour_taille' },
            { data: 'bassin' },
            { data: 'long_robe' },
            {
                data: null,
                render: function(row) {
                    return '<button class="btn btn-info btn-sm view-mesure-btn" data-id="' + row.id_mesure + '" data-client-id="' + clientId + '"><i class="fas fa-eye"></i> Voir</button>';
                }
            },
            {
                data: null,
                render: function(row) {
                    return '<button class="btn btn-secondary btn-sm print-mesure-btn" data-id="' + row.id_mesure + '" data-client-id="' + clientId + '"><i class="fas fa-print"></i> Imprimer</button>';
                }
            },
            {
                data: null,
                render: function(row) {
                    return '<button class="btn btn-warning btn-sm edit-mesure-btn" data-id="' + row.id_mesure + '"><i class="fas fa-edit"></i> Modifier</button>';
                }
            },
            {
                data: null,
                render: function(row) {
                    return '<button class="btn btn-danger btn-sm delete-mesure-btn" data-id="' + row.id_mesure + '"><i class="fas fa-trash"></i> Supprimer</button>';
                }
            }
        ],
        order: [[0, 'desc']],
        language: {
            sZeroRecords: "Aucune mesure pour ce client",
            sProcessing: "Chargement...",
            sSearch: "Rechercher:",
            sLengthMenu: "Afficher _MENU_ éléments",
            sInfo: "Affichage _START_ à _END_ sur _TOTAL_ éléments",
            sInfoEmpty: "Affichage 0 à 0 sur 0 éléments",
            sInfoFiltered: "(filtré de _MAX_ éléments au total)",
            oPaginate: { sFirst: "Premier", sPrevious: "Précédent", sNext: "Suivant", sLast: "Dernier" }
        }
    });
}

// Fonction pour imprimer les mesures en PDF
function imprimerMesuresPDF(idMesure, clientId) {
    Swal.fire({
        title: 'Génération du PDF...',
        text: 'Veuillez patienter',
        allowOutsideClick: false,
        didOpen: () => { Swal.showLoading(); }
    });

    $.ajax({
        url: '../api/modules/get_client_info.php',
        type: 'GET',
        data: { id: clientId },
        dataType: 'json',
        success: function(clientData) {
            var clientNom = clientData.success ? clientData.client.nom + ' ' + clientData.client.prenom : 'Client';
            var clientTel = clientData.success ? clientData.client.telephone : '-';
            
            $.ajax({
                url: '../api/modules/get_mesure.php',
                type: 'GET',
                data: { id: idMesure },
                dataType: 'json',
                success: function(data) {
                    Swal.close();
                    if (data.success) {
                        genererPDFMesures(data.mesure, clientNom, clientTel);
                    } else {
                        Swal.fire('Erreur', data.error || 'Impossible de charger les mesures', 'error');
                    }
                },
                error: function() {
                    Swal.close();
                    Swal.fire('Erreur', 'Erreur lors du chargement des mesures', 'error');
                }
            });
        },
        error: function() {
            $.ajax({
                url: '../api/modules/get_mesure.php',
                type: 'GET',
                data: { id: idMesure },
                dataType: 'json',
                success: function(data) {
                    Swal.close();
                    if (data.success) {
                        genererPDFMesures(data.mesure, 'Client', '-');
                    } else {
                        Swal.fire('Erreur', data.error || 'Impossible de charger les mesures', 'error');
                    }
                },
                error: function() {
                    Swal.close();
                    Swal.fire('Erreur', 'Erreur lors du chargement des mesures', 'error');
                }
            });
        }
    });
}

// Génération du PDF des mesures
function genererPDFMesures(mesure, clientNom, clientTel) {
    if (typeof window.jspdf === 'undefined') {
        Swal.fire('Erreur', 'Librairie PDF non chargée', 'error');
        return;
    }

    const { jsPDF } = window.jspdf;
    const doc = new jsPDF('p', 'mm', 'a4');
    let y = 20;

    // En-tête
    doc.setFontSize(22);
    doc.setTextColor(220, 38, 38);
    doc.setFont('helvetica', 'bold');
    doc.text('DANFANIMENT', 105, y, { align: 'center' });
    y += 10;

    doc.setFontSize(12);
    doc.setTextColor(100, 100, 100);
    doc.setFont('helvetica', 'normal');
    doc.text('Boutique de tissus et confection', 105, y, { align: 'center' });
    y += 8;

    doc.setFontSize(10);
    doc.setTextColor(80, 80, 80);
    doc.text('Tél: +226 74 50 41 41 | Email: danfaniment23@gmail.com', 105, y, { align: 'center' });
    y += 8;

    doc.setDrawColor(220, 38, 38);
    doc.line(20, y, 190, y);
    y += 10;

    // Titre
    doc.setFontSize(16);
    doc.setTextColor(0, 0, 0);
    doc.setFont('helvetica', 'bold');
    doc.text('FICHE DE MESURES', 105, y, { align: 'center' });
    y += 10;

    // Informations client
    doc.setFontSize(11);
    doc.setFont('helvetica', 'bold');
    doc.setTextColor(220, 38, 38);
    doc.text('Informations client', 20, y);
    y += 8;

    doc.setFontSize(10);
    doc.setFont('helvetica', 'normal');
    doc.setTextColor(0, 0, 0);
    doc.text(`Client: ${clientNom}`, 20, y);
    doc.text(`Téléphone: ${clientTel}`, 105, y);
    y += 8;
    doc.text(`Version: ${mesure.version || '1'}`, 20, y);
    var dateMesure = mesure.date_mesure ? mesure.date_mesure : new Date().toLocaleDateString('fr-FR');
    doc.text(`Date: ${dateMesure}`, 105, y);
    y += 12;

    doc.setDrawColor(200, 200, 200);
    doc.line(20, y, 190, y);
    y += 10;

    function addSection(title, dataLines) {
        if (y > 270) {
            doc.addPage();
            y = 20;
        }
        doc.setFontSize(12);
        doc.setFont('helvetica', 'bold');
        doc.setTextColor(220, 38, 38);
        doc.text(title, 20, y);
        y += 8;
        
        doc.setFontSize(10);
        doc.setFont('helvetica', 'normal');
        doc.setTextColor(0, 0, 0);
        for (var i = 0; i < dataLines.length; i++) {
            doc.text(dataLines[i].label + ': ' + dataLines[i].value, dataLines[i].x, y);
            if (i % 2 === 1) y += 6;
        }
        y += 4;
    }

    // Section 1: Dos, Épaules, Col
    addSection('1. Dos, Épaules & Col', [
        { label: 'Dos', value: (mesure.dos || '-') + ' cm', x: 20 },
        { label: 'Épaule', value: (mesure.epaule || '-') + ' cm', x: 105 },
        { label: 'Col', value: (mesure.col || '-') + ' cm', x: 20 }
    ]);

    // Section 2: Poitrine et Taille
    addSection('2. Poitrine & Taille', [
        { label: 'Poitrine', value: (mesure.poitrine || '-') + ' cm', x: 20 },
        { label: 'Tour de taille', value: (mesure.tour_taille || '-') + ' cm', x: 105 },
        { label: 'Longueur taille', value: (mesure.long_taille || '-') + ' cm', x: 20 }
    ]);

    // Section 3: Manches
    addSection('3. Manches & Poignet', [
        { label: 'Longueur manche', value: (mesure.long_manche || '-') + ' cm', x: 20 },
        { label: 'Tour manche', value: (mesure.tour_manche || '-') + ' cm', x: 105 },
        { label: 'Poignet', value: (mesure.poignet || '-') + ' cm', x: 20 }
    ]);

    // Section 4: Longueurs vêtements
    addSection('4. Longueurs vêtements (Haut/Robes)', [
        { label: 'Long. camisole', value: (mesure.long_camisole || '-') + ' cm', x: 20 },
        { label: 'Long. robe', value: (mesure.long_robe || '-') + ' cm', x: 105 },
        { label: 'Frappe/Fente', value: (mesure.frappe || '-') + ' cm', x: 20 },
        { label: 'Long. chemise', value: (mesure.long_chemise || '-') + ' cm', x: 105 }
    ]);

    // Section 5: Bas
    addSection('5. Bas (Pantalons & Jupes)', [
        { label: 'Ceinture', value: (mesure.ceinture || '-') + ' cm', x: 20 },
        { label: 'Bassin', value: (mesure.bassin || '-') + ' cm', x: 105 },
        { label: 'Cuisse', value: (mesure.cuisse || '-') + ' cm', x: 20 },
        { label: 'Genoux', value: (mesure.genoux || '-') + ' cm', x: 105 },
        { label: 'Long. jupe', value: (mesure.long_jupe || '-') + ' cm', x: 20 },
        { label: 'Long. pantalon', value: (mesure.long_pantalon || '-') + ' cm', x: 105 },
        { label: 'Bas pantalon', value: (mesure.bas || '-') + ' cm', x: 20 }
    ]);

    // Section 6: Général
    addSection('6. Mensurations générales', [
        { label: 'Hauteur totale', value: (mesure.hauteur_totale || '-') + ' cm', x: 20 },
        { label: 'Poids', value: (mesure.poids || '-') + ' kg', x: 105 },
        { label: 'Pointure', value: mesure.pointure_chaussure || '-', x: 20 },
        { label: 'Taille ceinture', value: mesure.taille_ceinture || '-', x: 105 }
    ]);

    // Pinces
    if (mesure.pinces) {
        if (y > 260) {
            doc.addPage();
            y = 20;
        }
        doc.setFontSize(12);
        doc.setFont('helvetica', 'bold');
        doc.setTextColor(220, 38, 38);
        doc.text('7. Pinces', 20, y);
        y += 8;
        doc.setFontSize(10);
        doc.setFont('helvetica', 'normal');
        doc.setTextColor(0, 0, 0);
        var pincesLines = doc.splitTextToSize(mesure.pinces, 170);
        doc.text(pincesLines, 20, y);
        y += (pincesLines.length * 6) + 10;
    }

    // Notes
    if (mesure.notes) {
        if (y > 260) {
            doc.addPage();
            y = 20;
        }
        doc.setFontSize(12);
        doc.setFont('helvetica', 'bold');
        doc.setTextColor(220, 38, 38);
        doc.text('8. Notes particulières', 20, y);
        y += 8;
        
        doc.setFontSize(10);
        doc.setFont('helvetica', 'normal');
        doc.setTextColor(0, 0, 0);
        var notesLines = doc.splitTextToSize(mesure.notes, 170);
        doc.text(notesLines, 20, y);
        y += (notesLines.length * 6) + 10;
    }

    // Pied de page
    const pageCount = doc.internal.getNumberOfPages();
    for (let i = 1; i <= pageCount; i++) {
        doc.setPage(i);
        doc.setFontSize(8);
        doc.setTextColor(150, 150, 150);
        doc.text(`Page ${i} sur ${pageCount}`, 105, 285, { align: 'center' });
        doc.text('Signature du client: ___________________', 20, 285);
        doc.text('Cachet de l\'établissement', 150, 285);
    }

    var nomFichier = `Fiche_mesures_${clientNom.replace(/\s/g, '_')}_${new Date().toISOString().slice(0, 19)}.pdf`;
    doc.save(nomFichier);
}

// Voir détail - MODAL avec design professionnel
$(document).on('click', '.view-mesure-btn', function() {
    var id = $(this).data('id');
    var clientId = $(this).data('client-id');
    
    $.ajax({
        url: '../api/modules/get_client_info.php',
        type: 'GET',
        data: { id: clientId },
        dataType: 'json',
        success: function(clientData) {
            var clientNom = clientData.success ? clientData.client.nom + ' ' + clientData.client.prenom : 'Client';
            var clientTel = clientData.success ? clientData.client.telephone : '-';
            
            $.ajax({
                url: '../api/modules/get_mesure.php',
                type: 'GET',
                data: { id: id },
                dataType: 'json',
                success: function(data) {
                    if (data.success) {
                        var m = data.mesure;
                        var html = `
                        <div style="font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif; max-height: 550px; overflow-y: auto; padding: 0;">
                            <div style="background: linear-gradient(135deg, #DC2626, #F59E0B); color: white; padding: 20px; text-align: center; margin-bottom: 20px;">
                                <h2 style="margin: 0;"><i class="fas fa-user"></i> ${clientNom}</h2>
                                <p style="margin: 5px 0 0;"><i class="fas fa-phone"></i> ${clientTel}</p>
                                <p style="margin: 5px 0 0;"><i class="fas fa-ruler-combined"></i> Mesures corporelles - Version ${m.version}</p>
                                <p style="margin: 5px 0 0; font-size: 12px;"><i class="fas fa-calendar-alt"></i> Prise de mesures : ${m.date_mesure || '-'}</p>
                            </div>
                            
                            <div style="display: flex; flex-wrap: wrap; gap: 15px; padding: 0 10px;">
                                <div style="flex: 1; min-width: 280px;">
                                    <div class="mesure-section">
                                        <h6><i class="fas fa-user"></i> Dos, Épaules & Col</h6>
                                        <p><strong>Dos :</strong> <span class="mesure-badge">${m.dos || '-'} cm</span></p>
                                        <p><strong>Épaule :</strong> ${m.epaule || '-'} cm</p>
                                        <p><strong>Col :</strong> ${m.col || '-'} cm</p>
                                    </div>
                                    <div class="mesure-section">
                                        <h6><i class="fas fa-female"></i> Poitrine & Taille</h6>
                                        <p><strong>Poitrine :</strong> <span class="mesure-badge">${m.poitrine || '-'} cm</span></p>
                                        <p><strong>Tour de taille :</strong> <span class="mesure-badge">${m.tour_taille || '-'} cm</span></p>
                                        <p><strong>Longueur taille :</strong> ${m.long_taille || '-'} cm</p>
                                    </div>
                                    <div class="mesure-section">
                                        <h6><i class="fas fa-hand-peace"></i> Manches & Poignet</h6>
                                        <p><strong>Longueur manche :</strong> ${m.long_manche || '-'} cm</p>
                                        <p><strong>Tour manche :</strong> ${m.tour_manche || '-'} cm</p>
                                        <p><strong>Poignet :</strong> ${m.poignet || '-'} cm</p>
                                    </div>
                                </div>
                                
                                <div style="flex: 1; min-width: 280px;">
                                    <div class="mesure-section">
                                        <h6><i class="fas fa-vest"></i> Longueurs vêtements</h6>
                                        <p><strong>Long. camisole :</strong> ${m.long_camisole || '-'} cm</p>
                                        <p><strong>Long. robe :</strong> <span class="mesure-badge">${m.long_robe || '-'} cm</span></p>
                                        <p><strong>Frappe/Fente :</strong> ${m.frappe || '-'} cm</p>
                                        <p><strong>Long. chemise :</strong> ${m.long_chemise || '-'} cm</p>
                                    </div>
                                    <div class="mesure-section">
                                        <h6><i class="fas fa-tshirt"></i> Pinces</h6>
                                        <p><strong>Description :</strong> ${m.pinces || '-'}</p>
                                    </div>
                                </div>
                                
                                <div style="flex: 1; min-width: 280px;">
                                    <div class="mesure-section">
                                        <h6><i class="fas fa-walking"></i> Bas (Pantalons & Jupes)</h6>
                                        <p><strong>Ceinture :</strong> ${m.ceinture || '-'} cm</p>
                                        <p><strong>Bassin :</strong> <span class="mesure-badge">${m.bassin || '-'} cm</span></p>
                                        <p><strong>Cuisse :</strong> ${m.cuisse || '-'} cm</p>
                                        <p><strong>Genoux :</strong> ${m.genoux || '-'} cm</p>
                                        <p><strong>Long. jupe :</strong> ${m.long_jupe || '-'} cm</p>
                                        <p><strong>Long. pantalon :</strong> ${m.long_pantalon || '-'} cm</p>
                                        <p><strong>Bas pantalon :</strong> ${m.bas || '-'} cm</p>
                                    </div>
                                    <div class="mesure-section">
                                        <h6><i class="fas fa-chart-simple"></i> Mensurations générales</h6>
                                        <p><strong>Hauteur totale :</strong> ${m.hauteur_totale || '-'} cm</p>
                                        <p><strong>Poids :</strong> ${m.poids || '-'} kg</p>
                                        <p><strong>Pointure :</strong> ${m.pointure_chaussure || '-'}</p>
                                        <p><strong>Taille ceinture :</strong> ${m.taille_ceinture || '-'}</p>
                                    </div>
                                </div>
                            </div>
                            ${m.notes ? `
                            <div style="margin: 15px; padding: 12px; background: #fef3c7; border-radius: 10px; border-left: 4px solid #F59E0B;">
                                <h6 style="margin: 0 0 5px; color: #D97706;"><i class="fas fa-sticky-note"></i> Notes particulières</h6>
                                <p style="margin: 0; font-style: italic;">${m.notes}</p>
                            </div>
                            ` : ''}
                        </div>`;
                        
                        Swal.fire({
                            title: 'Fiche de mesures',
                            html: html,
                            width: '1000px',
                            showConfirmButton: true,
                            confirmButtonText: '<i class="fas fa-check"></i> Fermer',
                            confirmButtonColor: '#DC2626',
                            showCloseButton: true,
                            customClass: {
                                popup: 'mesure-detail-modal'
                            }
                        });
                    } else {
                        Swal.fire('Erreur', data.error || 'Impossible de charger les détails', 'error');
                    }
                },
                error: function() {
                    Swal.fire('Erreur', 'Erreur lors du chargement des détails', 'error');
                }
            });
        },
        error: function() {
            Swal.fire('Erreur', 'Erreur lors du chargement des informations client', 'error');
        }
    });
});

// Imprimer PDF
$(document).on('click', '.print-mesure-btn', function() {
    var id = $(this).data('id');
    var clientId = $(this).data('client-id');
    imprimerMesuresPDF(id, clientId);
});

// Modifier
$(document).on('click', '.edit-mesure-btn', function() {
    var id = $(this).data('id');
    $.ajax({
        url: '../api/modules/get_mesure.php',
        type: 'GET',
        data: { id: id },
        dataType: 'json',
        success: function(data) {
            if (data.success) {
                var m = data.mesure;
                $('#modif_id_mesure').val(m.id_mesure);
                $('#modif_dos').val(m.dos || '');
                $('#modif_epaule').val(m.epaule || '');
                $('#modif_col').val(m.col || '');
                $('#modif_poitrine').val(m.poitrine || '');
                $('#modif_tour_taille').val(m.tour_taille || '');
                $('#modif_long_taille').val(m.long_taille || '');
                $('#modif_pinces').val(m.pinces || '');
                $('#modif_long_manche').val(m.long_manche || '');
                $('#modif_tour_manche').val(m.tour_manche || '');
                $('#modif_poignet').val(m.poignet || '');
                $('#modif_long_camisole').val(m.long_camisole || '');
                $('#modif_long_robe').val(m.long_robe || '');
                $('#modif_frappe').val(m.frappe || '');
                $('#modif_long_chemise').val(m.long_chemise || '');
                $('#modif_ceinture').val(m.ceinture || '');
                $('#modif_bassin').val(m.bassin || '');
                $('#modif_cuisse').val(m.cuisse || '');
                $('#modif_genoux').val(m.genoux || '');
                $('#modif_long_jupe').val(m.long_jupe || '');
                $('#modif_long_pantalon').val(m.long_pantalon || '');
                $('#modif_bas').val(m.bas || '');
                $('#modif_hauteur_totale').val(m.hauteur_totale || '');
                $('#modif_poids').val(m.poids || '');
                $('#modif_pointure_chaussure').val(m.pointure_chaussure || '');
                $('#modif_taille_ceinture').val(m.taille_ceinture || '');
                $('#modif_notes').val(m.notes || '');
                $('#modifier_mesure').modal('show');
            }
        }
    });
});

// Supprimer
$(document).on('click', '.delete-mesure-btn', function() {
    var id = $(this).data('id');
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
            window.location.href = '../api/modules/supprimer_mesure.php?id_mesure=' + id;
        }
    });
});