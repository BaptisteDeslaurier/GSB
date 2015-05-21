<?php

$Hostname = "localhost";
$NameBDD = "gsb";
$User = "gsb_user";
$Password = "P@ssword";

try
{
    $connexion = new PDO("mysql:host=$Hostname;dbname=$NameBDD", $User, $Password);
}
catch(Exception $e)
{
    die('Erreur : '.$e->getMessage());
    
}

?>