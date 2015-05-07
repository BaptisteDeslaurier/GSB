<?php

// Fonctions de la page affiche_frais.php

/**
 * Fontion qui recupère les différents dates (sans doublons)
 * @param varchar $connexion
 */
function listeDeroulanteMoisAnnee($connexion) {
    //Recherche et récupềre les différents dates (sans doublons) des toutes les fiches de frais
    $listeMois = $connexion->query('SELECT DISTINCT mois FROM fichefrais ORDER BY mois DESC');
                
    // Pour chaque date des dates récupérées
    while($Mois = $listeMois->fetch()) {
        // Création d'un tableau des mois
        $tabMois = array("Janvier", "Février", "Mars", "Avril", "Mai", "Juin", "Juillet", "Août", "Septembre", "Octobre", "Novembre", "Décembre");
        // Segmentation de la chaine date pour en extraire le mois 
        $leMois = substr($Mois['mois'], 4,2);
        // Recherche du mois qui correspond au chiffre extrait dans le tableau précédemment crée
        $leMois = $tabMois[intval($leMois)-1];
        // Segmentation de la chaine date pour en extraire l'année
        $lAnnee = substr($Mois['mois'], 0,4);
        ?>
        <option value="<?php echo $Mois['mois'] ?>"><?php echo $leMois. " " .$lAnnee . "\n"; ?></option>
        <?php
    }
    // Ferme le curseur, permettant à la requête d'être de nouveau exécutée
    $listeMois->closeCursor();
}

/**
 * Fontion qui va afficher la phrase montrant l'état de la fiche
 * @param varchar $connexion
 * @param varchar $mois
 * @param integer $id
 */
function phraseEtatFiche($connexion, $mois, $id) {
    // Création d'une variable de session contenant le mois pour le cas où l'on veut créer le PDF
    $_SESSION['mois']=$mois;
    //echo $_POST['mois'];
    // Création d'un tableau des mois
    $tabMois = array("Janvier", "Février", "Mars", "Avril", "Mai", "Juin", "Juillet", "Août", "Septembre", "Octobre", "Novembre", "Décembre");
    // Segmentation de la chaine date pour en extraire le mois 
    $leMois = substr($_POST['mois'], 4,2);
    // Recherche du mois qui correspond au chiffre extrait dans le tableau précédemment crée
    $leMois = $tabMois[intval($leMois)-1];
    // Segmentation de la chaine date pour en extraire l'année
    $lAnnee = substr($_POST['mois'], 0,4);
    
    // Récupération de l'état de la fiche (création, remboursé ...) et de sa date de dernière modifiaction
    $resultatEtatFiche = $connexion->query('SELECT libelle, dateModif
                                    FROM etat E
                                    INNER JOIN fichefrais FF ON E.id = FF.idEtat
                                    WHERE FF.idVisiteur = "'.$id.'"
                                    AND FF.mois = "'.$_POST['mois'].'"');

    // Pour chaque Etat récupérer (normalement un seul)
    while($typeEtat = $resultatEtatFiche->fetch()) {
        $etat = $typeEtat['libelle'];
        $date = $typeEtat['dateModif'];
    }
    $resultatEtatFiche->closeCursor();
    
    // Affichage de la phrase pour létat de la fiche
    echo "Fiche de frais du mois de ".$leMois." ".$lAnnee." : ".utf8_encode($etat). " depuis le ".$date;
}

/**
 * Fontion qui va afficher le montant de la fiche de frais
 * @param varchar $connexion
 * @param integer $id
 */
function montantFiche($connexion, $id) {
    // Récupération du montant total de la fiche de frais sélectionnée
    $resultatMontantFiche = $connexion->query('SELECT montantValide 
                                 FROM fichefrais 
                                 WHERE idVisiteur="'.$id.'" 
                                 AND mois = "'.$_POST['mois'].'"');

    // A REVOIR ou FAIRE -> enlever la phrase quand montant = 0.00
    // + Eviter de passer sur des page sans avoir de log
    // + Enlever le tableau si aucun élément est présent
    if ($ligne = $resultatMontantFiche->fetch()) {
        $montant = $ligne['montantValide'];
        echo '<br/><br/>';
        echo 'Montant validé : '.$montant.' €';
    }
    $resultatMontantFiche->closeCursor();
}

/**
 * Fontion qui va afficher les différents éléments forfait de la fiche sélectionnée
 * @param varchar $connexion
 * @param integer $id
 */
function ligneTableauForfait($connexion, $id) {
    // Récupération des éléments forfaits
    $resultatElementForfait = $connexion->query('SELECT quantite
                                  FROM lignefraisforfait 
                                  WHERE idVisiteur="'.$id.'" 
                                  AND mois = "'.$_POST['mois'].'"');

    // Pour chaque élément forfait
    while($unElementForfait = $resultatElementForfait->fetch()) {
        $idfrais = $unElementForfait['quantite'];     
        echo  "<td width='25%' align='center'>".$idfrais."</td>";         
    }
    $resultatElementForfait->closeCursor();
}

/**
 * Fontion qui va afficher les différents éléments hors forfait de la fiche sélectionnée
 * @param varchar $connexion
 * @param integer $id
 */
function ligneTableauHorsForfait($connexion, $id) {
    // Récupération des éléments hors forfaits
    $resultatElementHorsForfait = $connexion->query('SELECT DATE, montant, libelle 
                                  FROM lignefraishorsforfait 
                                  WHERE mois="'.$_POST['mois'].'" 
                                  AND idVisiteur="'.$id.'" order by mois desc');
    
    // Pour chaque élément hors forfait
    while($unElementHorsForfait = $resultatElementHorsForfait->fetch()) {
        $date = $unElementHorsForfait['DATE'];
        $montant = $unElementHorsForfait['montant'];
        $libelle = $unElementHorsForfait['libelle'];

         echo "
         <tr>
             <td width='20%' align='center'>".$date."</td>             
             <td width='60%' align='center'>".utf8_encode($libelle)."</td>		 
             <td width='20%' align='center'>".$montant."</td>
         </tr>";
    }
    $resultatElementHorsForfait->closeCursor();
}

// Fonction de la page maj_frais_forfait.php

/**
 * Fontion qui va mettre à jour les éléments forfaits
 * @param varchar $connexion
 */
function updateFraisForfait($connexion) {
    // Mise a jour du forfait étape
    $connexion->exec ('update lignefraisforfait set quantite = quantite + '.$_POST['etape'].' where idFraisForfait = "ETP" and idVisiteur = "'.$_SESSION['id'].'" and mois = "'.$_SESSION['annee_mois']. '"');
    // Mise a jour des frais kilométrique
    $connexion->exec ('update lignefraisforfait set quantite = quantite + '.$_POST['km'].' where idFraisForfait = "KM" and idVisiteur = "'.$_SESSION['id'].'" and mois = "'.$_SESSION['annee_mois']. '"');
    // Mise a jour des nuitées hôtel
    $connexion->exec ('update lignefraisforfait set quantite = quantite + '.$_POST['nuit'].' where idFraisForfait = "NUI" and idVisiteur = "'.$_SESSION['id'].'" and mois = "'.$_SESSION['annee_mois']. '"');
    // Mise a jour des repas restaurant
    $connexion->exec ('update lignefraisforfait set quantite = quantite + '.$_POST['repas'].' where idFraisForfait = "REP" and idVisiteur = "'.$_SESSION['id'].'" and mois = "'.$_SESSION['annee_mois']. '"');

    // Retour vers la page saisie_frais.php
    header('location: saisie_frais.php');
}

// Fonction de la page maj_frais_hors_forfait.php

/**
 * Fontion qui va mettre à jour les éléments hors forfait
 * @param varchar $connexion
 */
function updateFraisHorsForfait($connexion) {
    // Récupération du dernier éléement hors forfait
    $idMaxHF = $connexion->query('select MAX(id) as idMax from lignefraishorsforfait');
    $id = $idMaxHF->fetch();
    $idMax = ($id['idMax']+1);

    // §Insertion du nouveau élément hors forfait pour cette fiche frais
    $connexion->exec ('insert into lignefraishorsforfait values('.$idMax.', "'.$_SESSION['id'].'", "'.$_SESSION['annee_mois'].'", "'.$_POST['libelle'].'", "' .$_POST['date'].'", ' .$_POST['montant']. ')');

    header('location: saisie_frais.php');
}

// Fonction de la page saisie_frais.php

/**
 * Fonction de création de la fiche de frais
 * @param varchar $connexion
 * @param integer $id
 */
function creationFicheFrais($connexion, $id){
    // Initialisation du jour et du mois
    $jour = date("d");
    $annee = date('Y');
    if ($jour < 10)// test pour savoir sur quel mois on est.
    {
        if (date('m') == 1)// test du mois d'une année inférieur 
        {
            $mois = 12;//on renvoit vers le mois d'après (décembre 12)
            $annee = $annee - 1;//on renvoit vers l'année précédente
        }
        else
        {
            $mois = date('m') - 1;
        }
    }
     else 
    {
         $mois = date('m');
    }
    // On vérifie si une fiche de frais existe pour ce mois là
    $_SESSION['annee_mois'] = $annee.$mois;
    $sql = "select * from fichefrais where idVisiteur = '" .$id."' and mois='".$_SESSION['annee_mois']."'";
    $resultatFicheDuMois = $connexion->query($sql);
    if (!$ligne = $resultatFicheDuMois->fetch())
    {
    //pas de fiche de frais pour ce mois là on la crée avec les lignes frais forfait correpondante
        $connexion->exec("insert into fichefrais values ('".$id."', '".$_SESSION['annee_mois']."', 0, 0, NULL, 'CR')");
        $connexion->exec("insert into lignefraisforfait select '".$id."', '".$_SESSION['annee_mois']."', id, 0 from fraisforfait");
    }
    $resultatFicheDuMois->closeCursor();
}

/**
 * Fontion qui recupère la quantite de forfais étape, frais kilometrique, nuitée, et repas restaurant
 * @param varchar $connexion
 * @param integer $id
 * $resultatForfaitEtape int(11) ETP
 * $forfaitEtape = $resultatForfaitEtape
 * $resultatFraisKm int(11) KM
 * $fraisKm = $resultatFraisKm
 * $resultatNuitee int(11) NUI
 * $nuitee = $resultatNuitee
 * $resultatRepas int(11) REP
 * $repas = $resultatRepas
 */
function recuperationElementsForfaitises($connexion, $id){
    // Recupération des forfaits étapes
    $resultatForfaitEtape = $connexion->query('SELECT quantite FROM lignefraisforfait WHERE idVisiteur = "' .$id. '" AND idFraisForfait = "ETP" AND mois = "' .$_SESSION['annee_mois']. '"');
    // Recupère le resultat optenue par la requéte
    $forfaitEtape = $resultatForfaitEtape->fetch();
    // Recupération des frais KM
    $resultatFraisKm = $connexion->query('SELECT quantite FROM lignefraisforfait WHERE idVisiteur = "' .$id. '" AND idFraisForfait = "KM" AND mois = "' .$_SESSION['annee_mois']. '"');
    $fraisKm = $resultatFraisKm->fetch();
    // Recupération des frais nuitee
    $resultatNuitee = $connexion->query('SELECT quantite FROM lignefraisforfait WHERE idVisiteur = "' .$id. '" AND idFraisForfait = "NUI" AND mois = "' .$_SESSION['annee_mois']. '"');
    $nuitee = $resultatNuitee->fetch();
    // Recupération des frais repas
    $resultatRepas = $connexion->query('SELECT quantite FROM lignefraisforfait WHERE idVisiteur = "' .$id. '" AND idFraisForfait = "REP" AND mois = "' .$_SESSION['annee_mois']. '"');
    $repas = $resultatRepas->fetch();
    ?>
    <h3>Tableau recapitulatif des éléments forfaitisé</h3>
        <table>
            <tr>
                <td><?php echo $forfaitEtape['quantite']; ?></td>
                <td><?php echo $fraisKm['quantite']; ?></td>
                <td><?php echo $nuitee['quantite']; ?></td>
                <td><?php echo $repas['quantite']; ?></td>
            </tr>
        </table>
    <?php
    }
    
/**
 * Fontion qui recupère les éléments hors forfaits
 * @param varchar $connexion
 * @param integer $id
 * $resultatHorsForfait -> recupère le libelle, la date et le montant dans la table lignefraishorsforfait quand l'id visiteur = a l'id de l'utilisateur connecté et que le mois = la valeur du mois initialisé dans creationFicheFrais
 */
function recuperationElementsHorsForfait($connexion, $id){
    $resultatHorsForfait = $connexion->query('select libelle, date, montant from lignefraishorsforfait where idVisiteur = "' .$id. '" and mois = ' .$_SESSION['annee_mois']);
    while($horsForfait = $resultatHorsForfait->fetch()) { ?>
        <tr>
            <td><?php echo date("d/m/Y", strtotime($horsForfait['date'])); ?></td>
            <td><?php echo $horsForfait['libelle']; ?></td>
            <td><?php echo $horsForfait['montant']; ?></td>
        </tr>
    <?php }
    $resultatHorsForfait->closeCursor();
}
?>