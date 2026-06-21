<!-- Modal Ajouter un projet -->
<div class="modal fade" id="ajouter_projet" tabindex="-1" role="dialog" aria-labelledby="largeModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-lg" role="document">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="largeModalLabel">Ajout d'un nouveau projet</h5>
                <button type="button" class="close" data-dismiss="modal" aria-label="Fermer">
                    <span aria-hidden="true">&times;</span>
                </button>
            </div>
            <form action="./../api/modules/ajouter_projet.php" method="POST">
                <div class="modal-body">
                    <div class="form-group">
                        <div class="row">
                            <div class="col-md-4">
                                <label for="code_projet">Code projet *</label>
                                <input id="code_projet" class="form-control" type="text" name="code_projet" placeholder="Entrez le code projet" required>
                            </div>
                            <div class="col-md-4">
                                <label for="nom_projet">Nom du projet *</label>
                                <input id="nom_projet" class="form-control" type="text" name="nom_projet" placeholder="Entrez le nom du projet" required>
                            </div>
                            <div class="col-md-4">
                                <label for="budget">Budget</label>
                                <input id="budget" class="form-control" type="number" step="0.01" name="budget" placeholder="Entrez le budget">
                            </div>
                        </div>
                    </div>
                    <div class="form-group">
                        <div class="row">
                            <div class="col-md-6">
                                <label for="date_debut">Date de début *</label>
                                <input id="date_debut" class="form-control" type="date" name="date_debut" required>
                            </div>
                            <div class="col-md-6">
                                <label for="date_fin">Date de fin</label>
                                <input id="date_fin" class="form-control" type="date" name="date_fin">
                            </div>
                        </div>
                    </div>
                    <div class="form-group">
                        <label for="description">Description</label>
                        <textarea id="description" class="form-control" name="description" rows="3" placeholder="Entrez la description du projet"></textarea>
                    </div>
                    <div class="form-group">
                        <div class="row">
                            <div class="col-md-6">
                                <label for="responsable">Responsable</label>
                                <input id="responsable" class="form-control" type="text" name="responsable" placeholder="Nom du responsable">
                            </div>
                            <div class="col-md-6">
                                <label for="statut">Statut *</label>
                                <select id="statut" name="statut" class="form-control" required>
                                    <option value="Planifié">Planifié</option>
                                    <option value="En cours">En cours</option>
                                    <option value="Terminé">Terminé</option>
                                    <option value="Annulé">Annulé</option>
                                </select>
                            </div>
                        </div>
                    </div>
                    <h7><span style="text-decoration: underline;">NB</span>: Les champs avec * sont obligatoires.</h7>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-secondary" data-dismiss="modal">Annuler</button>
                        <button type="submit" class="btn btn-primary" style="color:#ffffff;">Ajouter</button>
                    </div>
                </div>
            </form>
        </div>
    </div>
</div>

<!-- Modal Modifier un projet -->
<div class="modal fade" id="modifier_projet" tabindex="-1" role="dialog" aria-labelledby="largeModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-lg" role="document">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="largeModalLabel">Modification d'un projet</h5>
                <button type="button" class="close" data-dismiss="modal" aria-label="Fermer">
                    <span aria-hidden="true">&times;</span>
                </button>
            </div>
            <form action="./../api/modules/modifier_projet.php" method="POST">
                <div class="modal-body">
                    <!-- Champ caché pour l'ID du projet -->
                    <input type="hidden" id="id_projet" name="id_projet" value="">
                    <div class="form-group">
                        <div class="row">
                            <div class="col-md-4">
                                <label for="code_projet_modif">Code projet *</label>
                                <input id="code_projet_modif" class="form-control" type="text" name="code_projet" placeholder="Entrez le code projet" required>
                            </div>
                            <div class="col-md-4">
                                <label for="nom_projet_modif">Nom du projet *</label>
                                <input id="nom_projet_modif" class="form-control" type="text" name="nom_projet" placeholder="Entrez le nom du projet" required>
                            </div>
                            <div class="col-md-4">
                                <label for="budget_modif">Budget</label>
                                <input id="budget_modif" class="form-control" type="number" step="0.01" name="budget" placeholder="Entrez le budget">
                            </div>
                        </div>
                    </div>
                    <div class="form-group">
                        <div class="row">
                            <div class="col-md-6">
                                <label for="date_debut_modif">Date de début *</label>
                                <input id="date_debut_modif" class="form-control" type="date" name="date_debut" required>
                            </div>
                            <div class="col-md-6">
                                <label for="date_fin_modif">Date de fin</label>
                                <input id="date_fin_modif" class="form-control" type="date" name="date_fin">
                            </div>
                        </div>
                    </div>
                    <div class="form-group">
                        <label for="description_modif">Description</label>
                        <textarea id="description_modif" class="form-control" name="description" rows="3" placeholder="Entrez la description du projet"></textarea>
                    </div>
                    <div class="form-group">
                        <div class="row">
                            <div class="col-md-6">
                                <label for="responsable_modif">Responsable</label>
                                <input id="responsable_modif" class="form-control" type="text" name="responsable" placeholder="Nom du responsable">
                            </div>
                            <div class="col-md-6">
                                <label for="statut_modif">Statut *</label>
                                <select id="statut_modif" name="statut" class="form-control" required>
                                    <option value="Planifié">Planifié</option>
                                    <option value="En cours">En cours</option>
                                    <option value="Terminé">Terminé</option>
                                    <option value="Annulé">Annulé</option>
                                </select>
                            </div>
                        </div>
                    </div>
                    <h7><span style="text-decoration: underline;">NB</span>: Les champs avec * sont obligatoires.</h7>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-secondary" data-dismiss="modal">Annuler</button>
                        <button type="submit" class="btn btn-primary" style="color:#ffffff;">Modifier</button>
                    </div>
                </div>
            </form>
        </div>
    </div>
</div>
