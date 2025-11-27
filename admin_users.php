<?php
session_start();
require "fonctions.php";

// Zone admin : nécessite connexion + rôle admin
requireLogin();
requireAdmin();

$pdo = getDB();
$errors = [];
$success = null;

// Détermine l'action demandée
$action = $_POST['action'] ?? null; // create|update|delete

if ($action === 'create') {
    // Création d'un utilisateur (admin ou user) avec validation basique
    $nom = trim($_POST['nom'] ?? '');
    $email = trim($_POST['email'] ?? '');
    $adresse = trim($_POST['adresse'] ?? '');
    $password = trim($_POST['password'] ?? '');
    $roleId = (int)($_POST['role_id'] ?? ROLE_USER);

    if ($nom === '' || $email === '' || $adresse === '' || $password === '') {
        $errors[] = "Tous les champs sont obligatoires.";
    }
    if (!validateEmail($email)) {
        $errors[] = "Email invalide.";
    }
    if (!validatePassword($password)) {
        $errors[] = "Mot de passe invalide.";
    }
    if (emailExiste($pdo, $email)) {
        $errors[] = "Email déjà utilisé.";
    }

    if (!$errors) {
        $passwordHash = password_hash($password, PASSWORD_DEFAULT);
        if (creerUtilisateur($pdo, $nom, $email, $passwordHash, $adresse, $roleId)) {
            $success = "Utilisateur créé.";
        } else {
            $errors[] = "Erreur lors de la création.";
        }
    }
}

if ($action === 'update') {
    // Edition d'un utilisateur; mot de passe optionnel
    $id = (int)($_POST['id'] ?? 0);
    $nom = trim($_POST['nom'] ?? '');
    $email = trim($_POST['email'] ?? '');
    $adresse = trim($_POST['adresse'] ?? '');
    $password = trim($_POST['password'] ?? '');
    $roleId = (int)($_POST['role_id'] ?? ROLE_USER);

    $user = getUserById($pdo, $id);
    if (!$user) {
        $errors[] = "Utilisateur introuvable.";
    }
    if ($nom === '' || $email === '' || $adresse === '') {
        $errors[] = "Nom, email et adresse sont obligatoires.";
    }
    if (!validateEmail($email)) {
        $errors[] = "Email invalide.";
    }
    // Check email uniqueness except current user
    $stmt = $pdo->prepare("SELECT id FROM users WHERE email = ? AND id <> ? LIMIT 1");
    $stmt->execute([$email, $id]);
    if ($stmt->fetch()) {
        $errors[] = "Email déjà utilisé.";
    }

    $passwordHash = null;
    if ($password !== '') {
        if (!validatePassword($password)) {
            $errors[] = "Mot de passe invalide.";
        } else {
            $passwordHash = password_hash($password, PASSWORD_DEFAULT);
        }
    }

    if (!$errors) {
        if (updateUser($pdo, $id, $nom, $email, $adresse, $roleId, $passwordHash)) {
            $success = "Utilisateur mis à jour.";
        } else {
            $errors[] = "Erreur lors de la mise à jour.";
        }
    }
}

if ($action === 'delete') {
    // Suppression protégée (on ne supprime pas soi-même ici)
    $id = (int)($_POST['id'] ?? 0);
    if ($id === (int)($_SESSION['user_id'] ?? 0)) {
        $errors[] = "Impossible de supprimer votre propre compte ici.";
    } else {
        deleteUserById($pdo, $id);
        $success = "Utilisateur supprimé.";
    }
}

$users = getAllUsers($pdo);
?>
<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Admin - Gestion utilisateurs</title>
    <link rel="stylesheet" href="style.css">
</head>
<body>
    <header class="topbar">
        <div class="brand"><span>Admin</span></div>
        <nav>
            <a href="tableau.php">Tableau</a> |
            <a href="logout.php">Déconnexion</a>
        </nav>
    </header>

    <main class="page">
        <section>
            <h3>Créer un utilisateur</h3>
            <?php if ($errors): ?>
                <div class="errors">
                    <?php foreach ($errors as $err): ?>
                        <div><?php echo htmlspecialchars($err); ?></div>
                    <?php endforeach; ?>
                </div>
            <?php endif; ?>
            <?php if ($success): ?>
                <div class="success"><?php echo htmlspecialchars($success); ?></div>
            <?php endif; ?>
            <form method="POST">
                <input type="hidden" name="action" value="create">
                <input type="text" name="nom" placeholder="Nom" required>
                <input type="email" name="email" placeholder="Email" required>
                <input type="text" name="adresse" placeholder="Adresse" required>
                <input type="password" name="password" placeholder="Mot de passe (8+ chars)" required>
                <select name="role_id">
                    <option value="<?php echo ROLE_USER; ?>">User</option>
                    <option value="<?php echo ROLE_ADMIN; ?>">Admin</option>
                </select>
                <button type="submit">Créer</button>
            </form>
        </section>

        <section>
            <h3>Utilisateurs</h3>
            <!-- Liste + formulaires inline pour modifier/supprimer chaque utilisateur -->
            <table>
                <thead>
                    <tr>
                        <th>ID</th><th>Nom</th><th>Email</th><th>Adresse</th><th>Rôle</th><th>Actions</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($users as $u): ?>
                        <tr>
                            <td><?php echo (int)$u['id']; ?></td>
                            <td><?php echo htmlspecialchars($u['nom']); ?></td>
                            <td><?php echo htmlspecialchars($u['email']); ?></td>
                            <td><?php echo htmlspecialchars($u['adresse']); ?></td>
                            <td><?php echo htmlspecialchars($u['role_name'] ?? $u['role_id']); ?></td>
                            <td>
                                <form method="POST">
                                    <input type="hidden" name="action" value="update">
                                    <input type="hidden" name="id" value="<?php echo (int)$u['id']; ?>">
                                    <input type="text" name="nom" value="<?php echo htmlspecialchars($u['nom']); ?>" required>
                                    <input type="email" name="email" value="<?php echo htmlspecialchars($u['email']); ?>" required>
                                    <input type="text" name="adresse" value="<?php echo htmlspecialchars($u['adresse']); ?>" required>
                                    <input type="password" name="password" placeholder="Nouveau mot de passe (optionnel)">
                                    <select name="role_id">
                                        <option value="<?php echo ROLE_USER; ?>" <?php echo ((int)$u['role_id'] === ROLE_USER) ? 'selected' : ''; ?>>User</option>
                                        <option value="<?php echo ROLE_ADMIN; ?>" <?php echo ((int)$u['role_id'] === ROLE_ADMIN) ? 'selected' : ''; ?>>Admin</option>
                                    </select>
                                    <button type="submit">Mettre à jour</button>
                                </form>
                                <form method="POST" onsubmit="return confirm('Supprimer cet utilisateur ?');">
                                    <input type="hidden" name="action" value="delete">
                                    <input type="hidden" name="id" value="<?php echo (int)$u['id']; ?>">
                                    <button type="submit">Supprimer</button>
                                </form>
                            </td>
                        </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        </section>
    </main>
</body>
</html>

