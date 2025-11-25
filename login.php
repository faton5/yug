<?php
session_start();
require "fonctions.php";

$pdo = getDB();

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $email = trim($_POST['email']);
    $password = trim($_POST['password']);

    if ($email === "" || $password === "") {
        die("Veuillez remplir tous les champs.");
    }

    $user = getUserByEmail($pdo, $email);

    if (!$user) {
        die("Email ou mot de passe incorrect.");
    }

    if (!password_verify($password, $user['password'])) {
        die("Email ou mot de passe incorrect.");
    }

    $_SESSION['user_id'] = $user['id'];
    $_SESSION['user_nom'] = $user['nom'];

    header("Location: tableau.php");
    exit;
}
?>

<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Connexion - Yoann</title>
    <link rel="stylesheet" href="style.css">
</head>
<body>
    <div class="nebula-dot" style="left: 6%; top: 22%;"></div>
    <div class="nebula-dot secondary" style="right: 12%; bottom: 16%;"></div>

    <header class="topbar">
        <div class="brand">
            <span class="orb"></span>
            <span>Yoann</span>
        </div>
        <nav class="nav-links">
            <a href="index.html">Accueil</a>
            <a href="register.php">Inscription</a>
            <a href="login.php">Connexion</a>
        </nav>
    </header>

    <main class="page">
        <div class="grid-two">

            <section class="card">
                <h3>Accéder à ton compte</h3>
                <form method="POST">
                    <div class="field">
                        <label for="email">Email</label>
                        <input id="email" type="email" name="email" required>
                    </div>
                    <div class="field">
                        <label for="password">Mot de passe</label>
                        <input id="password" type="password" name="password" required>
                    </div>
                    <div class="actions">
                        <button class="btn" type="submit">Se connecter</button>
                        <a class="inline-link" href="register.php">Créer un compte</a>
                    </div>
                </form>
            </section>
        </div>
    </main>
</body>
</html>
