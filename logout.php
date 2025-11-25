<?php
session_start();
// Destruction de la session courante puis redirection vers la connexion
session_destroy();
header("Location: login.php");
exit;
