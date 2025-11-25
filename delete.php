<?php
session_start();

require "fonctions.php";
$pdo = getDB();
requireLogin(); // bloque la suppression si l'utilisateur n'est pas connecté

// Supprime le compte lié à la session courante puis retourne à l'accueil
deleteAccount($pdo, $_SESSION['user_id']);

header("Location: index.html");
exit;
