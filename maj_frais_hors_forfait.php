<?php
    session_start();

    include './include/connexion_bd.php';
    include './include/fonctions.php';

    updateFraisHorsForfait($connexion);
?>