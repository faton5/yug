<?php
session_start();

require "fonctions.php";
$pdo = getDB();

deleteAccount($pdo, $_SESSION['user_id']);

header("Location: index.html");
exit;