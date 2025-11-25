<?php

// Role constants mapped to table roles (1 = admin, 2 = user)
if (!defined('ROLE_ADMIN')) {
    define('ROLE_ADMIN', 1); // 1 correspond au role admin dans la table roles
}
if (!defined('ROLE_USER')) {
    define('ROLE_USER', 2); // 2 correspond au role user dans la table roles
}

// Ouvre une connexion PDO vers la BDD (UTF-8, exceptions, pas d'emulation)
function getDB() {
    $host = "localhost";
    $dbname = "mysql";
    $username = "root";
    $password = "";

    try {
        return new PDO(
            "mysql:host=$host;port=3306;dbname=$dbname;charset=utf8",
            $username,
            $password,
            [
                PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
                PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
                PDO::ATTR_EMULATE_PREPARES => false
            ]
        );
    } catch (PDOException $e) {
        die("Erreur de connexion BDD : " . $e->getMessage());
    }
}

// Vérifie le format email (regex simple couvrant les cas usuels)
function validateEmail($email) {
    return (bool) preg_match('/^[A-Z0-9._%+-]+@[A-Z0-9.-]+\.[A-Z]{2,}$/i', $email);
}

// Valide le mot de passe (8-64 chars, min/maj, chiffre, spécial)
function validatePassword($password) {
    return (bool) preg_match('/^(?=.*[a-z])(?=.*[A-Z])(?=.*\d)(?=.*[^\w\s]).{8,64}$/', $password);
}

// Vérifie si un email existe déjà
function emailExiste($pdo, $email) {
    // Requete simple pour savoir si l'email est deja present
    $stmt = $pdo->prepare("SELECT id FROM users WHERE email = ?");
    $stmt->execute([$email]);
    return $stmt->rowCount() > 0;
}

// Crée un utilisateur avec role (user par défaut)
function creerUtilisateur($pdo, $nom, $email, $passwordHash, $adresse, $roleId = ROLE_USER) {
    // role_id reference la table roles (1=admin, 2=user)
    $stmt = $pdo->prepare("INSERT INTO users (nom, email, password, adresse, role_id) VALUES (?, ?, ?, ?, ?)");
    return $stmt->execute([$nom, $email, $passwordHash, $adresse, $roleId]);
}

// Récupère un utilisateur via son email (avec nom de rôle)
function getUserByEmail($pdo, $email) {
    // LEFT JOIN pour recuperer le nom du role; l'utilisateur revient meme si role manquant
    $stmt = $pdo->prepare("SELECT u.*, r.role_name FROM users u LEFT JOIN roles r ON u.role_id = r.id WHERE u.email = ?");
    $stmt->execute([$email]);
    return $stmt->fetch();
}

// Récupère un utilisateur via son id (avec nom de rôle)
function getUserById($pdo, $id) {
    // Equivalent de getUserByEmail mais sur l'id
    $stmt = $pdo->prepare("SELECT u.*, r.role_name FROM users u LEFT JOIN roles r ON u.role_id = r.id WHERE u.id = ?");
    $stmt->execute([$id]);
    return $stmt->fetch();
}

// Liste tous les utilisateurs avec leurs rôles
function getAllUsers($pdo) {
    // Classement du plus recent (id desc) pour faciliter l'affichage admin
    $stmt = $pdo->query("SELECT u.*, r.role_name FROM users u LEFT JOIN roles r ON u.role_id = r.id ORDER BY u.id DESC");
    return $stmt->fetchAll();
}

// Met à jour un utilisateur; mot de passe optionnel si null
function updateUser($pdo, $id, $nom, $email, $adresse, $roleId, $passwordHash = null) {
    // Si pas de nouveau mot de passe, on ne touche pas au champ password
    if ($passwordHash === null) {
        $stmt = $pdo->prepare("UPDATE users SET nom = ?, email = ?, adresse = ?, role_id = ? WHERE id = ?");
        return $stmt->execute([$nom, $email, $adresse, $roleId, $id]);
    }
    $stmt = $pdo->prepare("UPDATE users SET nom = ?, email = ?, adresse = ?, role_id = ?, password = ? WHERE id = ?");
    return $stmt->execute([$nom, $email, $adresse, $roleId, $passwordHash, $id]);
}

// Supprime un utilisateur (a utiliser coté admin)
function deleteUserById($pdo, $id) {
    // Suppression brute; a proteger par un controle d'acces (admin)
    $stmt = $pdo->prepare("DELETE FROM users WHERE id = ?");
    $stmt->execute([$id]);
}

// Check session login
function isLogged() {
    return isset($_SESSION['user_id']);
}

// Check if current user is admin
function isAdmin() {
    return isset($_SESSION['user_role_id']) && (int) $_SESSION['user_role_id'] === ROLE_ADMIN;
}

// Redirige vers login si non connecté
function requireLogin() {
    if (!isLogged()) {
        header("Location: login.php");
        exit;
    }
}

// Redirige vers tableau si non admin
function requireAdmin() {
    if (!isAdmin()) {
        header("Location: tableau.php");
        exit;
    }
}

// Supprime le compte courant (utilisé par delete.php)
function deleteAccount($pdo, $id) {
    deleteUserById($pdo, $id);
}

?>
