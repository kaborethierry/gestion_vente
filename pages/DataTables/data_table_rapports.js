// pages/DataTables/data_table_rapports.js
// DANFANIMENT POS - Gestion des rapports PDF

async function genererRapport(type) {
    let dateDebut = '';
    let dateFin = '';
    let titre = '';
    let sousTitre = '';
    
    const aujourdhui = new Date();
    
    switch(type) {
        case 'journalier':
            dateDebut = formatDate(aujourdhui);
            dateFin = formatDate(aujourdhui);
            titre = 'RAPPORT JOURNALIER';
            sousTitre = `du ${formatDateLong(dateDebut)}`;
            break;
        case 'hebdomadaire':
            const debutSemaine = new Date(aujourdhui);
            const jour = aujourdhui.getDay();
            const diff = jour === 0 ? 6 : jour - 1;
            debutSemaine.setDate(aujourdhui.getDate() - diff);
            dateDebut = formatDate(debutSemaine);
            dateFin = formatDate(aujourdhui);
            titre = 'RAPPORT HEBDOMADAIRE';
            sousTitre = `du ${formatDateLong(dateDebut)} au ${formatDateLong(dateFin)}`;
            break;
        case 'mensuel':
            dateDebut = formatDate(new Date(aujourdhui.getFullYear(), aujourdhui.getMonth(), 1));
            dateFin = formatDate(aujourdhui);
            titre = 'RAPPORT MENSUEL';
            sousTitre = `du ${formatDateLong(dateDebut)} au ${formatDateLong(dateFin)}`;
            break;
        case 'annuel':
            const annee = aujourdhui.getFullYear();
            dateDebut = `${annee}-01-01`;
            dateFin = `${annee}-12-31`;
            titre = `RAPPORT ANNUEL ${annee}`;
            sousTitre = `du 1er janvier au 31 décembre ${annee}`;
            break;
        case 'personnalise':
            dateDebut = document.getElementById('date_debut').value;
            dateFin = document.getElementById('date_fin').value;
            if (!dateDebut || !dateFin) {
                Swal.fire('Erreur', 'Veuillez sélectionner une période', 'error');
                return;
            }
            titre = 'RAPPORT PERSONNALISE';
            sousTitre = `du ${formatDateLong(dateDebut)} au ${formatDateLong(dateFin)}`;
            break;
        case 'clients':
            titre = "RAPPORT CLIENTS";
            sousTitre = "Analyse détaillée de la clientèle";
            break;
        case 'prestataires':
            titre = 'RAPPORT PRESTATAIRES';
            sousTitre = "Statistiques et paiements des prestataires";
            break;
        default:
            titre = 'RAPPORT';
            sousTitre = '';
    }
    
    Swal.fire({
        title: 'Génération en cours...',
        text: 'Veuillez patienter',
        allowOutsideClick: false,
        didOpen: () => { Swal.showLoading(); }
    });
    
    try {
        let url = `../api/modules/rapport_data.php?type=${type}`;
        if (type === 'personnalise') {
            url += `&date_debut=${dateDebut}&date_fin=${dateFin}`;
        } else if (type === 'annuel') {
            url += `&annee=${new Date().getFullYear()}`;
        } else if (type !== 'clients' && type !== 'prestataires') {
            url += `&date_debut=${dateDebut}&date_fin=${dateFin}`;
        }
        
        const response = await fetch(url);
        const data = await response.json();
        
        if (data.error) {
            Swal.fire('Erreur', data.error, 'error');
            return;
        }
        
        Swal.close();
        genererPDF(data, titre, sousTitre, type);
        
    } catch (error) {
        Swal.fire('Erreur', 'Erreur lors de la génération du rapport: ' + error.message, 'error');
        console.error(error);
    }
}

function formatDate(date) {
    const year = date.getFullYear();
    const month = String(date.getMonth() + 1).padStart(2, '0');
    const day = String(date.getDate()).padStart(2, '0');
    return `${year}-${month}-${day}`;
}

function formatDateLong(dateStr) {
    if (!dateStr) return 'Non défini';
    const d = new Date(dateStr);
    return d.toLocaleDateString('fr-FR', { day: '2-digit', month: 'long', year: 'numeric' });
}

// Fonction de formatage des nombres CORRIGÉE
function formatNumber(value) {
    if (value === undefined || value === null) return '0';
    const num = parseFloat(value);
    if (isNaN(num)) return '0';
    // Format: espaces comme séparateurs de milliers
    return num.toLocaleString('fr-FR').replace(/\s/g, ' ').replace(/,/g, ' ');
}

// Fonction pour formater les montants avec le symbole FCFA
function formatMontant(value) {
    return formatNumber(value) + ' FCFA';
}

function genererPDF(data, titre, sousTitre, type) {
    if (typeof window.jspdf === 'undefined') {
        Swal.fire('Erreur', 'Librairie PDF non chargée', 'error');
        return;
    }
    
    const { jsPDF } = window.jspdf;
    const doc = new jsPDF('p', 'mm', 'a4');
    let y = 25;
    
    // ========== EN-TÊTE ==========
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
    
    // ========== TITRE ==========
    doc.setFontSize(18);
    doc.setTextColor(0, 0, 0);
    doc.setFont('helvetica', 'bold');
    doc.text(titre, 105, y, { align: 'center' });
    y += 10;
    
    if (sousTitre) {
        doc.setFontSize(11);
        doc.setFont('helvetica', 'normal');
        doc.setTextColor(80, 80, 80);
        doc.text(sousTitre, 105, y, { align: 'center' });
        y += 8;
    }
    
    doc.setFontSize(9);
    doc.setTextColor(120, 120, 120);
    doc.text(`Généré le ${new Date().toLocaleString('fr-FR')} par ${document.querySelector('.navbar-brand')?.innerText || 'Administrateur'}`, 105, y, { align: 'center' });
    y += 8;
    
    doc.setDrawColor(200, 200, 200);
    doc.line(20, y, 190, y);
    y += 10;
    
    // ========== CONTENU SELON LE TYPE ==========
    switch(type) {
        case 'journalier':
            y = contenuJournalier(doc, data, y);
            break;
        case 'hebdomadaire':
            y = contenuHebdomadaire(doc, data, y);
            break;
        case 'mensuel':
            y = contenuMensuel(doc, data, y);
            break;
        case 'annuel':
            y = contenuAnnuel(doc, data, y);
            break;
        case 'clients':
            y = contenuClients(doc, data, y);
            break;
        case 'prestataires':
            y = contenuPrestataires(doc, data, y);
            break;
        case 'personnalise':
            y = contenuPersonnalise(doc, data, y);
            break;
    }
    
    // ========== PIED DE PAGE ==========
    const pageCount = doc.internal.getNumberOfPages();
    for (let i = 1; i <= pageCount; i++) {
        doc.setPage(i);
        doc.setFontSize(8);
        doc.setTextColor(150, 150, 150);
        doc.text(`Page ${i} sur ${pageCount}`, 105, 285, { align: 'center' });
        doc.text('Signature de l\'administrateur: ___________________', 20, 285);
        doc.text('Cachet de l\'établissement', 150, 285);
    }
    
    const nomFichier = `${titre.replace(/\s/g, '_')}_${new Date().toISOString().slice(0, 19)}.pdf`;
    doc.save(nomFichier);
}

// ==================== CONTENU JOURNALIER ====================
function contenuJournalier(doc, data, y) {
    // 1. Ventes du jour
    doc.setFontSize(14);
    doc.setTextColor(220, 38, 38);
    doc.setFont('helvetica', 'bold');
    doc.text('1. VENTES DU JOUR', 20, y);
    y += 8;
    
    doc.autoTable({ 
        startY: y, 
        head: [['N° Vente', 'Client', 'Montant', 'Mode', 'Caissier']], 
        body: data.ventes.map(v => [v.numero_vente, v.client || '-', formatMontant(v.total_ttc), v.mode_paiement, v.caissier || '-']),
        theme: 'striped', 
        headStyles: { fillColor: [220, 38, 38], textColor: 255 }, 
        margin: { left: 20, right: 20 } 
    });
    y = doc.lastAutoTable.finalY + 10;
    
    // 2. Résumé des ventes
    doc.setFontSize(12);
    doc.setFont('helvetica', 'bold');
    doc.text(`Total ventes: ${formatMontant(data.total_ventes)} (${data.nb_ventes} ventes)`, 20, y);
    y += 6;
    doc.text(`Montant brut: ${formatMontant(data.total_ventes_brut)}`, 20, y);
    y += 6;
    doc.text(`Remises accordées: ${formatMontant(data.total_remises)}`, 20, y);
    y += 10;
    
    // 3. Ventes par mode de paiement
    if (data.ventes_par_mode && data.ventes_par_mode.length > 0) {
        doc.setFontSize(12);
        doc.setTextColor(220, 38, 38);
        doc.text('2. VENTES PAR MODE DE PAIEMENT', 20, y);
        y += 8;
        
        doc.autoTable({ 
            startY: y, 
            head: [['Mode', 'Nombre', 'Montant']], 
            body: data.ventes_par_mode.map(m => [m.mode_paiement, m.nb_ventes, formatMontant(m.montant_total)]),
            theme: 'striped', 
            headStyles: { fillColor: [220, 38, 38], textColor: 255 }, 
            margin: { left: 20, right: 20 } 
        });
        y = doc.lastAutoTable.finalY + 10;
    }
    
    // 4. Confections livrées
    if (data.confections_livrees && data.confections_livrees.length > 0) {
        doc.setFontSize(12);
        doc.setTextColor(220, 38, 38);
        doc.text('3. CONFECTIONS LIVREES', 20, y);
        y += 8;
        
        doc.autoTable({ 
            startY: y, 
            head: [['N° Commande', 'Client', 'Type', 'Montant']], 
            body: data.confections_livrees.map(c => [c.numero_commande, c.client, c.type_tenue, formatMontant(c.montant_total)]),
            theme: 'striped', 
            headStyles: { fillColor: [220, 38, 38], textColor: 255 }, 
            margin: { left: 20, right: 20 } 
        });
        y = doc.lastAutoTable.finalY + 10;
    }
    
    // 5. Dépenses du jour
    if (data.depenses && data.depenses.length > 0) {
        doc.setFontSize(12);
        doc.setTextColor(220, 38, 38);
        doc.text('4. DEPENSES DU JOUR', 20, y);
        y += 8;
        
        doc.autoTable({ 
            startY: y, 
            head: [['Libellé', 'Catégorie', 'Montant', 'Bénéficiaire']], 
            body: data.depenses.map(d => [d.libelle, d.categorie, formatMontant(d.montant), d.beneficiaire]),
            theme: 'striped', 
            headStyles: { fillColor: [220, 38, 38], textColor: 255 }, 
            margin: { left: 20, right: 20 } 
        });
        y = doc.lastAutoTable.finalY + 10;
    }
    
    // 6. Total des dépenses
    doc.setFontSize(12);
    doc.setFont('helvetica', 'bold');
    doc.text(`Total dépenses: ${formatMontant(data.total_depenses)}`, 20, y);
    y += 8;
    doc.setTextColor(0, 150, 0);
    doc.text(`BENEFICE DU JOUR: ${formatMontant(data.benefice)}`, 20, y);
    y += 15;
    
    return y;
}

// ==================== CONTENU HEBDOMADAIRE ====================
function contenuHebdomadaire(doc, data, y) {
    // 1. Résumé de la semaine
    doc.setFontSize(14);
    doc.setTextColor(220, 38, 38);
    doc.setFont('helvetica', 'bold');
    doc.text('RESUME DE LA SEMAINE', 20, y);
    y += 8;
    
    doc.autoTable({ 
        startY: y, 
        body: [
            ['Chiffre d\'affaires total', formatMontant(data.total_ca)],
            ['Montant brut', formatMontant(data.total_ca_brut)],
            ['Remises accordées', formatMontant(data.total_remises)],
            ['Nombre de ventes', data.nb_ventes],
            ['Total des dépenses', formatMontant(data.total_depenses)],
            ['BENEFICE', formatMontant(data.benefice)]
        ], 
        theme: 'plain', 
        margin: { left: 20, right: 20 } 
    });
    y = doc.lastAutoTable.finalY + 15;
    
    // 2. Ventes par jour
    if (data.ventes_par_jour && data.ventes_par_jour.length > 0) {
        doc.setFontSize(12);
        doc.setTextColor(220, 38, 38);
        doc.text('VENTES PAR JOUR', 20, y);
        y += 8;
        
        doc.autoTable({ 
            startY: y, 
            head: [['Jour', 'Nombre', 'Montant', 'Remises']], 
            body: data.ventes_par_jour.map(j => [new Date(j.jour).toLocaleDateString('fr-FR'), j.nb_ventes, formatMontant(j.total_ventes), formatMontant(j.total_remises)]),
            theme: 'striped', 
            headStyles: { fillColor: [220, 38, 38], textColor: 255 }, 
            margin: { left: 20, right: 20 } 
        });
        y = doc.lastAutoTable.finalY + 15;
    }
    
    // 3. Paiements prestataires
    if (data.paiements_prestataires && data.paiements_prestataires.length > 0) {
        doc.setFontSize(12);
        doc.setTextColor(220, 38, 38);
        doc.text('PAIEMENTS PRESTATAIRES', 20, y);
        y += 8;
        
        doc.autoTable({ 
            startY: y, 
            head: [['Prestataire', 'Montant', 'Date', 'Type']], 
            body: data.paiements_prestataires.map(p => [p.libelle || `${p.prestataire_prenom || ''} ${p.prestataire_nom || ''}`, formatMontant(p.montant), new Date(p.date_depense).toLocaleDateString('fr-FR'), p.type_prestataire || '-']),
            theme: 'striped', 
            headStyles: { fillColor: [220, 38, 38], textColor: 255 }, 
            margin: { left: 20, right: 20 } 
        });
        y = doc.lastAutoTable.finalY + 10;
        
        doc.setFontSize(10);
        doc.setFont('helvetica', 'bold');
        doc.text(`Total paiements prestataires: ${formatMontant(data.total_paiements_prestataires)}`, 20, y);
        y += 10;
    }
    
    // 4. Confections
    if (data.confections_cours && data.confections_cours.length > 0) {
        doc.setFontSize(12);
        doc.setTextColor(220, 38, 38);
        doc.text('CONFECTIONS EN COURS', 20, y);
        y += 8;
        
        doc.autoTable({ 
            startY: y, 
            head: [['N° Commande', 'Client', 'Type', 'Montant', 'Livraison']], 
            body: data.confections_cours.map(c => [c.numero_commande, c.client, c.type_tenue, formatMontant(c.montant_total), new Date(c.date_livraison_prevue).toLocaleDateString('fr-FR')]),
            theme: 'striped', 
            headStyles: { fillColor: [220, 38, 38], textColor: 255 }, 
            margin: { left: 20, right: 20 } 
        });
        y = doc.lastAutoTable.finalY + 15;
    }
    
    return y;
}

// ==================== CONTENU MENSUEL ====================
function contenuMensuel(doc, data, y) {
    // 1. Bilan mensuel
    doc.setFontSize(14);
    doc.setTextColor(220, 38, 38);
    doc.setFont('helvetica', 'bold');
    doc.text('BILAN MENSUEL', 20, y);
    y += 8;
    
    doc.autoTable({ 
        startY: y, 
        body: [
            ['Chiffre d\'affaires', formatMontant(data.total_ca)],
            ['Montant brut', formatMontant(data.total_ca_brut)],
            ['Remises', formatMontant(data.total_remises)],
            ['Nombre de ventes', data.nb_ventes],
            ['Dépenses totales', formatMontant(data.total_depenses)],
            ['Clients actifs', data.nb_clients_actifs],
            ['Dépenses clients', formatMontant(data.total_depenses_clients)],
            ['Confections terminées', data.nb_confections_terminees],
            ['BENEFICE', formatMontant(data.benefice)]
        ], 
        theme: 'plain', 
        margin: { left: 20, right: 20 } 
    });
    y = doc.lastAutoTable.finalY + 15;
    
    // 2. Ventes par mode
    if (data.ventes_par_mode && data.ventes_par_mode.length > 0) {
        doc.setFontSize(12);
        doc.setTextColor(220, 38, 38);
        doc.text('VENTES PAR MODE DE PAIEMENT', 20, y);
        y += 8;
        
        doc.autoTable({ 
            startY: y, 
            head: [['Mode', 'Nombre', 'Montant']], 
            body: data.ventes_par_mode.map(m => [m.mode_paiement, m.nb_ventes, formatMontant(m.montant_total)]),
            theme: 'striped', 
            headStyles: { fillColor: [220, 38, 38], textColor: 255 }, 
            margin: { left: 20, right: 20 } 
        });
        y = doc.lastAutoTable.finalY + 15;
    }
    
    // 3. Dépenses par catégorie
    if (data.depenses_par_categorie && data.depenses_par_categorie.length > 0) {
        doc.setFontSize(12);
        doc.setTextColor(220, 38, 38);
        doc.text('DEPENSES PAR CATEGORIE', 20, y);
        y += 8;
        
        doc.autoTable({ 
            startY: y, 
            head: [['Catégorie', 'Nombre', 'Montant']], 
            body: data.depenses_par_categorie.map(d => [d.categorie, d.nb_depenses, formatMontant(d.total)]),
            theme: 'striped', 
            headStyles: { fillColor: [220, 38, 38], textColor: 255 }, 
            margin: { left: 20, right: 20 } 
        });
        y = doc.lastAutoTable.finalY + 15;
    }
    
    // 4. Top clients
    if (data.top_clients && data.top_clients.length > 0) {
        doc.setFontSize(12);
        doc.setTextColor(220, 38, 38);
        doc.text('TOP 10 CLIENTS DU MOIS', 20, y);
        y += 8;
        
        doc.autoTable({ 
            startY: y, 
            head: [['Client', 'Téléphone', 'Achats', 'Dépenses']], 
            body: data.top_clients.map(c => [c.client, c.telephone || '-', c.nb_achats, formatMontant(c.total_depense)]),
            theme: 'striped', 
            headStyles: { fillColor: [220, 38, 38], textColor: 255 }, 
            margin: { left: 20, right: 20 } 
        });
        y = doc.lastAutoTable.finalY + 15;
    }
    
    return y;
}

// ==================== CONTENU ANNUEL ====================
function contenuAnnuel(doc, data, y) {
    // 1. Bilan annuel
    doc.setFontSize(14);
    doc.setTextColor(220, 38, 38);
    doc.setFont('helvetica', 'bold');
    doc.text('BILAN ANNUEL', 20, y);
    y += 8;
    
    doc.autoTable({ 
        startY: y, 
        body: [
            ['Chiffre d\'affaires annuel', formatMontant(data.total_ca)],
            ['Montant brut', formatMontant(data.total_ca_brut)],
            ['Remises totales', formatMontant(data.total_remises)],
            ['Nombre total de ventes', data.nb_ventes],
            ['Dépenses annuelles', formatMontant(data.total_depenses)],
            ['Nouveaux clients', data.nb_nouveaux_clients],
            ['Total clients', data.total_clients],
            ['BENEFICE ANNUEL', formatMontant(data.benefice)]
        ], 
        theme: 'plain', 
        margin: { left: 20, right: 20 } 
    });
    y = doc.lastAutoTable.finalY + 15;
    
    // 2. CA par mois
    if (data.ca_par_mois && data.ca_par_mois.length > 0) {
        doc.setFontSize(12);
        doc.setTextColor(220, 38, 38);
        doc.text('CHIFFRE D\'AFFAIRES PAR MOIS', 20, y);
        y += 8;
        
        const moisLabels = ['Janvier', 'Février', 'Mars', 'Avril', 'Mai', 'Juin', 'Juillet', 'Août', 'Septembre', 'Octobre', 'Novembre', 'Décembre'];
        const caParMois = data.ca_par_mois.map(m => [moisLabels[parseInt(m.mois)-1], m.nb_ventes, formatMontant(m.total_ventes), formatMontant(m.total_remises)]);
        
        doc.autoTable({ 
            startY: y, 
            head: [['Mois', 'Ventes', 'CA', 'Remises']], 
            body: caParMois,
            theme: 'striped', 
            headStyles: { fillColor: [220, 38, 38], textColor: 255 }, 
            margin: { left: 20, right: 20 } 
        });
        y = doc.lastAutoTable.finalY + 15;
    }
    
    // 3. Ventes par mode de paiement
    if (data.ventes_par_mode && data.ventes_par_mode.length > 0) {
        doc.setFontSize(12);
        doc.setTextColor(220, 38, 38);
        doc.text('VENTES PAR MODE DE PAIEMENT', 20, y);
        y += 8;
        
        doc.autoTable({ 
            startY: y, 
            head: [['Mode', 'Nombre', 'Montant']], 
            body: data.ventes_par_mode.map(m => [m.mode_paiement, m.nb_ventes, formatMontant(m.montant_total)]),
            theme: 'striped', 
            headStyles: { fillColor: [220, 38, 38], textColor: 255 }, 
            margin: { left: 20, right: 20 } 
        });
        y = doc.lastAutoTable.finalY + 15;
    }
    
    return y;
}

// ==================== CONTENU CLIENTS ====================
function contenuClients(doc, data, y) {
    // 1. Statistiques globales
    doc.setFontSize(14);
    doc.setTextColor(220, 38, 38);
    doc.setFont('helvetica', 'bold');
    doc.text('STATISTIQUES CLIENTS', 20, y);
    y += 8;
    
    doc.autoTable({ 
        startY: y, 
        body: [
            ['Total clients', data.total_clients],
            ['Clients fidèles (≥5 visites)', data.clients_fideles],
            ['Total dépenses clients', formatMontant(data.total_depenses_clients)],
            ['Points fidélité totaux', data.total_points_fidelite],
            ['Dépense moyenne par client', formatMontant(data.depense_moyenne)],
            ['Nouveaux clients (semaine)', data.nouveaux_semaine],
            ['Nouveaux clients (mois)', data.nouveaux_mois]
        ], 
        theme: 'plain', 
        margin: { left: 20, right: 20 } 
    });
    y = doc.lastAutoTable.finalY + 15;
    
    // 2. Top clients
    if (data.top_clients && data.top_clients.length > 0) {
        doc.setFontSize(12);
        doc.setTextColor(220, 38, 38);
        doc.text('TOP 10 CLIENTS (DEPENSES)', 20, y);
        y += 8;
        
        doc.autoTable({ 
            startY: y, 
            head: [['Client', 'Téléphone', 'Visites', 'Points', 'Dépenses']], 
            body: data.top_clients.map(c => [`${c.prenom} ${c.nom}`, c.telephone || '-', c.nombre_visites, c.points_fidelite, formatMontant(c.total_depense)]),
            theme: 'striped', 
            headStyles: { fillColor: [220, 38, 38], textColor: 255 }, 
            margin: { left: 20, right: 20 } 
        });
        y = doc.lastAutoTable.finalY + 15;
    }
    
    // 3. Clients inactifs
    if (data.clients_inactifs && data.clients_inactifs.length > 0) {
        doc.setFontSize(12);
        doc.setTextColor(220, 38, 38);
        doc.text('CLIENTS INACTIFS (>3 MOIS)', 20, y);
        y += 8;
        
        doc.autoTable({ 
            startY: y, 
            head: [['Client', 'Téléphone', 'Dernière visite', 'Dépenses totales']], 
            body: data.clients_inactifs.map(c => [`${c.prenom} ${c.nom}`, c.telephone || '-', c.date_derniere_visite ? new Date(c.date_derniere_visite).toLocaleDateString('fr-FR') : 'Jamais', formatMontant(c.total_depense)]),
            theme: 'striped', 
            headStyles: { fillColor: [220, 38, 38], textColor: 255 }, 
            margin: { left: 20, right: 20 } 
        });
        y = doc.lastAutoTable.finalY + 15;
    }
    
    return y;
}

// ==================== CONTENU PRESTATAIRES ====================
function contenuPrestataires(doc, data, y) {
    // 1. Statistiques globales
    doc.setFontSize(14);
    doc.setTextColor(220, 38, 38);
    doc.setFont('helvetica', 'bold');
    doc.text('STATISTIQUES PRESTATAIRES', 20, y);
    y += 8;
    
    doc.autoTable({ 
        startY: y, 
        body: [
            ['Total prestataires', data.total_prestataires],
            ['Couturiers', data.nb_couturiers],
            ['Tisseuses', data.nb_tisseuses],
            ['Brodeurs', data.nb_brodeurs],
            ['Perleuses', data.nb_perleuses],
            ['Merceries', data.nb_merceries],
            ['Vendeuses', data.nb_vendeuses],
            ['Total à payer', formatMontant(data.total_a_payer)],
            ['Total payé', formatMontant(data.total_paye)],
            ['Reste à payer', formatMontant(data.total_restant)]
        ], 
        theme: 'plain', 
        margin: { left: 20, right: 20 } 
    });
    y = doc.lastAutoTable.finalY + 15;
    
    // 2. Liste des prestataires
    if (data.prestataires && data.prestataires.length > 0) {
        doc.setFontSize(12);
        doc.setTextColor(220, 38, 38);
        doc.text('LISTE DETAILLEE DES PRESTATAIRES', 20, y);
        y += 8;
        
        doc.autoTable({ 
            startY: y, 
            head: [['Nom', 'Type', 'Téléphone', 'Productions', 'À payer', 'Payé', 'Reste']], 
            body: data.prestataires.map(p => [
                `${p.prenom} ${p.nom}`, 
                p.type_prestataire, 
                p.telephone || '-', 
                p.total_productions, 
                formatMontant(p.total_a_payer), 
                formatMontant(p.total_paye), 
                formatMontant(p.reste_a_payer)
            ]),
            theme: 'striped', 
            headStyles: { fillColor: [220, 38, 38], textColor: 255 }, 
            margin: { left: 20, right: 20 } 
        });
        y = doc.lastAutoTable.finalY + 15;
    }
    
    // 3. Productions en attente
    if (data.productions_attente && data.productions_attente.length > 0) {
        doc.setFontSize(12);
        doc.setTextColor(220, 38, 38);
        doc.text('PRODUCTIONS EN ATTENTE DE PAIEMENT', 20, y);
        y += 8;
        
        doc.autoTable({ 
            startY: y, 
            head: [['Prestataire', 'Productions', 'Montant']], 
            body: data.productions_attente.map(p => [p.prestataire, p.nb_productions, formatMontant(p.total_a_payer)]),
            theme: 'striped', 
            headStyles: { fillColor: [220, 38, 38], textColor: 255 }, 
            margin: { left: 20, right: 20 } 
        });
        y = doc.lastAutoTable.finalY + 10;
        
        doc.setFontSize(10);
        doc.setFont('helvetica', 'bold');
        doc.text(`Total productions en attente: ${formatMontant(data.total_productions_attente)}`, 20, y);
        y += 15;
    }
    
    return y;
}

// ==================== CONTENU PERSONNALISÉ ====================
function contenuPersonnalise(doc, data, y) {
    // 1. Résumé
    doc.setFontSize(14);
    doc.setTextColor(220, 38, 38);
    doc.setFont('helvetica', 'bold');
    doc.text('RESUME DE LA PERIODE', 20, y);
    y += 8;
    
    doc.autoTable({ 
        startY: y, 
        body: [
            ['Nombre de ventes', data.nb_ventes],
            ['Montant total des ventes', formatMontant(data.total_ventes)],
            ['Montant brut', formatMontant(data.total_ventes_brut)],
            ['Remises totales', formatMontant(data.total_remises)],
            ['Dépenses totales', formatMontant(data.total_depenses)],
            ['BENEFICE', formatMontant(data.benefice)]
        ], 
        theme: 'plain', 
        margin: { left: 20, right: 20 } 
    });
    y = doc.lastAutoTable.finalY + 15;
    
    // 2. Liste des ventes
    if (data.ventes && data.ventes.length > 0) {
        doc.setFontSize(12);
        doc.setTextColor(220, 38, 38);
        doc.text('DETAIL DES VENTES', 20, y);
        y += 8;
        
        doc.autoTable({ 
            startY: y, 
            head: [['Date', 'N° Vente', 'Client', 'Montant', 'Mode']], 
            body: data.ventes.map(v => [v.date_vente, v.numero_vente, v.client || '-', formatMontant(v.total_ttc), v.mode_paiement]),
            theme: 'striped', 
            headStyles: { fillColor: [220, 38, 38], textColor: 255 }, 
            margin: { left: 20, right: 20 } 
        });
        y = doc.lastAutoTable.finalY + 15;
    }
    
    // 3. Liste des dépenses
    if (data.depenses && data.depenses.length > 0) {
        doc.setFontSize(12);
        doc.setTextColor(220, 38, 38);
        doc.text('DETAIL DES DEPENSES', 20, y);
        y += 8;
        
        doc.autoTable({ 
            startY: y, 
            head: [['Date', 'Libellé', 'Catégorie', 'Montant']], 
            body: data.depenses.map(d => [d.date_depense, d.libelle, d.categorie, formatMontant(d.montant)]),
            theme: 'striped', 
            headStyles: { fillColor: [220, 38, 38], textColor: 255 }, 
            margin: { left: 20, right: 20 } 
        });
        y = doc.lastAutoTable.finalY + 15;
    }
    
    return y;
}