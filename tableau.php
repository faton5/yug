<?php
session_start();
require "fonctions.php";
// Tableau de bord : accessible uniquement aux utilisateurs connectés
requireLogin();
?>

<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Tableau de bord - Yoann</title>
</head>
<body>
    <header class="topbar">
        <div class="brand">
            <span class="orb"></span>
            <span>Yoann</span>
        </div>
        <nav class="nav-links">
            <a href="index.html">Accueil</a>
            <a href="logout.php">Déconnexion</a>
        </nav>
    </header>

    <main class="page">
        <div class="grid-two">
            <section class="hero">
                <div class="eyebrow">Bienvenue</div>
                <h2>Salut <?php echo htmlspecialchars($_SESSION['user_nom']); ?> ?</h2>
                <p>Rôle : <?php echo htmlspecialchars($_SESSION['user_role_name'] ?? 'user'); ?></p>
                <p>Moi c'est Yoann</p>
                <div class="actions">
                    <a class="btn" href="logout.php">Se déconnecter</a>
                    <a class="btn" href="delete.php">Supprimer le compte</a>
                    <?php if (isAdmin()): ?>
                        <a class="btn" href="admin_users.php">Admin utilisateurs</a>
                    <?php endif; ?>
                    <a class="btn secondary" href="index.html">Retour accueil</a>
                </div>
            </section>

            <section class="card">
                <h3>Etat de bord</h3>
                <div class="table">
                    <div><span>Profil</span> - <span class="badge">Actif</span></div>
                    <div class="divider"></div>
                    <div><span>Mode</span> - <small>Eleve</small></div>
                    <div><span>Progression</span> - <small>Php</small></div>
                    <div><span>Action rapide</span> - <small><a class="inline-link" href="logout.php">Déconnexion</a></small></div>
                </div>
            </section>
        </div>
    </main>
</body>
</html>
