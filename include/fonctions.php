<?php

function listeDeroulanteMoisAnnee($connexion) {
    //Recherche et récupềre les différents dates (sans doublons) des toutes les fiches de frais
    $resultatRecherche = $connexion->query('SELECT DISTINCT mois FROM fichefrais ORDER BY mois DESC');
                
    // Pour chaque date des dates récupérées
    while($Mois = $resultatRecherche->fetch()) {
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
    $resultatRecherche->closeCursor();
}

function phraseEtatFiche($connexion, $mois) {
    // Création d'une variable de session contenant le mois pour le cas où l'on veut créer le PDF
    $_SESSION['mois']=$mois;
    //echo $_POST['mois'];
    $tabMois = array("Janvier", "Février", "Mars", "Avril", "Mai", "Juin", "Juillet", "Août", "Septembre", "Octobre", "Novembre", "Décembre");
    $leMois = substr($_POST['mois'], 4,2);
    $leMois = $tabMois[intval($leMois)-1];
    
    $lAnnee = substr($_POST['mois'], 0,4);
    
    // Récupération de l'état de la fiche (création, remboursé ...) et de sa date de der,ière modifiaction
    $resultatEtat = $connexion->query('SELECT libelle, dateModif
                                    FROM etat E
                                    INNER JOIN fichefrais FF ON E.id = FF.idEtat
                                    WHERE FF.idVisiteur = "'.$_SESSION['id'].'"
                                    AND FF.mois = "'.$_POST['mois'].'"');

    while($typeEtat = $resultatEtat->fetch()) {
        $etat = $typeEtat['libelle'];
        $date = $typeEtat['dateModif'];
    }
    $resultatEtat->closeCursor();
    
    echo "Fiche de frais du mois de ".$leMois." ".$lAnnee." : ".$etat. " depuis le ".$date;
}

function montantFiche($connexion) {
    $resultat = $connexion->query('SELECT montantValide 
                                 FROM fichefrais 
                                 WHERE idVisiteur="'.$_SESSION['id'].'" 
                                 AND mois = "'.$_POST['mois'].'"');

    if ($ligne = $resultat->fetch()) {
        $montant = $ligne['montantValide'];
        echo '<br/><br/>';
        echo 'Montant validé : '.$montant.'';
    }
}

function ligneTableauForfait($connexion) {
    $resultat2 = $connexion->query('SELECT quantite
                                  FROM lignefraisforfait 
                                  WHERE idVisiteur="'.$_SESSION['id'].'" 
                                  AND mois = "'.$_POST['mois'].'"');


    while($ligne = $resultat2->fetch()) {
        $idfrais = $ligne['quantite'];     
        echo  "<td width='25%' align='center'>".$idfrais."</td>";         
     }
     
     $resultat2->closeCursor();
}

function ligneTableauHorsForfait($connexion) {
    $resultat3 = $connexion->query('SELECT DATE, montant, libelle 
                                  FROM lignefraishorsforfait 
                                  WHERE mois="'.$_POST['mois'].'" 
                                  AND idVisiteur="'.$_SESSION['id'].'" order by mois desc');
    
    while($ligne=$resultat3->fetch()) {
        $date = $ligne['DATE'];
        $montant = $ligne['montant'];
        $libelle = $ligne['libelle'];

         echo "
         <tr>
             <td width='20%' align='center'>$date</td>             
             <td width='60%' align='center'>$libelle</td>		 
             <td width='20%' align='center'>$montant</td>
         </tr>";
    }
    
    $resultat3->closeCursor();
}

?>