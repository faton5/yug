<?php
session_start();
require "fonctions.php";

// Page de diagnostic : accessible aux connectés, indique si le rôle est admin ou non
requireLogin();
$isAdmin = isAdmin(); // True si role_id = ROLE_ADMIN
?>
<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Test role</title>
</head>
<body>
    <header class="topbar">
        <div class="brand"><span class="orb"></span><span>Zone admin (test)</span></div>
        <nav class="nav-links">
            <a href="index.html">Accueil</a>
            <a href="tableau.php">Tableau</a>
            <a href="logout.php">Déconnexion</a>
        </nav>
    </header>

    <main class="page">
        <section class="card">
            <h3>Accès admin</h3>
            <?php if ($isAdmin): ?>
                <p>Tu es admin, accès autorisé.</p>
            <?php else: ?>
                <p>Accès refusé : rôle user. Passe <code>role_id</code> à 1 dans <code>users</code> pour tester l'accès admin.</p>
            <?php endif; ?>
            <p>Rôle courant : <?php echo htmlspecialchars($_SESSION['user_role_name'] ?? 'user'); ?> (id = <?php echo htmlspecialchars($_SESSION['user_role_id'] ?? ''); ?>)</p>
        </section>
    </main>
</body>
</html>
