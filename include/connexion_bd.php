<?php

$Hostname = "172.16.40.36";
$NameBDD = "gsb-prod";
$User = "webmaster";
$Password = "webmaster";

try
{
    $connexion = new PDO("mysql:host=$Hostname;dbname=$NameBDD", $User, $Password);
}
catch(Exception $e)
{
    die('Erreur : '.$e->getMessage());
    
}

?>