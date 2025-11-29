<?php
session_start();
require "fonctions.php";

// Page d'inscription : crée un compte role user par défaut après validations
$pdo = getDB();
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // Récupération et nettoyage des champs
    $nom = trim($_POST['nom']);
    $email = trim($_POST['email']);
    $adresse = trim($_POST['adresse']);
    $password = trim($_POST['password']);
    $passwordConfirm = trim($_POST['password_confirm']);

    if ($nom === "" || $email === "" || $adresse === "" || $password === "" || $passwordConfirm === "") {
        die("Tous les champs sont obligatoires.");
    }

    if (!validateEmail($email)) {
        die("Email invalide.");
    }

    if (!validatePassword($password)) {
        die("Mot de passe invalide (8-64 car., minuscule, majuscule, chiffre, caractere special).");
    }

    if ($password !== $passwordConfirm) {
        die("Les mots de passe ne correspondent pas.");
    }

    if (emailExiste($pdo, $email)) {
        die("Cet email existe déjà.");
    }

    $passwordHash = password_hash($password, PASSWORD_DEFAULT);
    $roleId = ROLE_USER; // role par defaut "user" (voir table roles)

    if (creerUtilisateur($pdo, $nom, $email, $passwordHash, $adresse, $roleId)) {
        echo "Inscription réussie. <a href='login.php'>Se connecter</a>";
    } else {
        echo "Erreur lors de l'inscription.";
    }
}
?>


<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Inscription - Yoann</title>
    <link rel="stylesheet" href="style.css">
    <script src="asset/js/api.js" defer></script>
</head>
<body>
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
                <h3>Créer un compte</h3>
                <form method="POST">
                    <div class="field">
                        <label for="nom">Nom</label>
                        <input id="nom" type="text" name="nom" required>
                    </div>
                    <div class="field">
                        <label for="email">Email</label>
                        <input id="email" type="email" name="email" required>
                    </div>
                    <div class="field">
                        <label for="addressInput">Adresse physique</label>
                        <input id="addressInput" type="text" name="adresse" placeholder="N° et rue, ville" required>
                        <ul id="suggestions"></ul>
                    </div>
                    <div class="field">
                        <label for="password">Mot de passe</label>
                        <input id="password" type="password" name="password" required>
                    </div>
                    <div class="field">
                        <label for="password_confirm">Confirmer le mot de passe</label>
                        <input id="password_confirm" type="password" name="password_confirm" required>
                    </div>
                    <div class="actions">
                        <button class="btn" type="submit">S'inscrire</button>
                        <a class="inline-link" href="login.php">Déjà membre ?</a>
                    </div>
                </form>
            </section>
        </div>
    </main>
</body>
</html>
