<?php

// Role constants mapped to table roles (1 = admin, 2 = user)
if (!defined('ROLE_ADMIN')) {
    define('ROLE_ADMIN', 1);
}
if (!defined('ROLE_USER')) {
    define('ROLE_USER', 2);
}

// PDO connection to database (exceptions enabled, UTF-8, no emulation)
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

// Email validation via regex (simple but effective for common cases)
function validateEmail($email) {
    return (bool) preg_match('/^[A-Z0-9._%+-]+@[A-Z0-9.-]+\.[A-Z]{2,}$/i', $email);
}

// Password validation: 8-64 chars, lowercase, uppercase, digit, special char
function validatePassword($password) {
    return (bool) preg_match('/^(?=.*[a-z])(?=.*[A-Z])(?=.*\d)(?=.*[^\w\s]).{8,64}$/', $password);
}

// Check if an email already exists
function emailExiste($pdo, $email) {
    $stmt = $pdo->prepare("SELECT id FROM users WHERE email = ?");
    $stmt->execute([$email]);
    return $stmt->rowCount() > 0;
}

// Insert a user with a role (default user)
function creerUtilisateur($pdo, $nom, $email, $passwordHash, $adresse, $roleId = ROLE_USER) {
    $stmt = $pdo->prepare("INSERT INTO users (nom, email, password, adresse, role_id) VALUES (?, ?, ?, ?, ?)");
    return $stmt->execute([$nom, $email, $passwordHash, $adresse, $roleId]);
}

// Fetch a user by email (with role join)
function getUserByEmail($pdo, $email) {
    $stmt = $pdo->prepare("SELECT u.*, r.role_name FROM users u LEFT JOIN roles r ON u.role_id = r.id WHERE u.email = ?");
    $stmt->execute([$email]);
    return $stmt->fetch();
}

// Fetch a user by id (with role join)
function getUserById($pdo, $id) {
    $stmt = $pdo->prepare("SELECT u.*, r.role_name FROM users u LEFT JOIN roles r ON u.role_id = r.id WHERE u.id = ?");
    $stmt->execute([$id]);
    return $stmt->fetch();
}

// List all users with their roles
function getAllUsers($pdo) {
    $stmt = $pdo->query("SELECT u.*, r.role_name FROM users u LEFT JOIN roles r ON u.role_id = r.id ORDER BY u.id DESC");
    return $stmt->fetchAll();
}

// Update a user; passwordHash optional (if null, keep existing password)
function updateUser($pdo, $id, $nom, $email, $adresse, $roleId, $passwordHash = null) {
    if ($passwordHash === null) {
        $stmt = $pdo->prepare("UPDATE users SET nom = ?, email = ?, adresse = ?, role_id = ? WHERE id = ?");
        return $stmt->execute([$nom, $email, $adresse, $roleId, $id]);
    }
    $stmt = $pdo->prepare("UPDATE users SET nom = ?, email = ?, adresse = ?, role_id = ?, password = ? WHERE id = ?");
    return $stmt->execute([$nom, $email, $adresse, $roleId, $passwordHash, $id]);
}

// Delete a user by id
function deleteUserById($pdo, $id) {
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

// Require login
function requireLogin() {
    if (!isLogged()) {
        header("Location: login.php");
        exit;
    }
}

// Require admin
function requireAdmin() {
    if (!isAdmin()) {
        header("Location: tableau.php");
        exit;
    }
}

// Delete account (used by self-delete route)
function deleteAccount($pdo, $id) {
    deleteUserById($pdo, $id);
}

?>
