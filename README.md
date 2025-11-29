# PHP + html + css + js  (login/register + rôles)

Application PHP simple (sans framework) : inscription/connexion avec regex de validation, rôles admin/user, gestion des utilisateurs par l’admin, suppression de compte et tableau de bord.


# Db
   - Tables : `users` (avec `role_id`), `roles` (1 = admin, 2 = user).
2. Configure l’accès BDD dans `fonctions.php` (host/db/user/pass, port 3306 par défaut).
3. Place les fichiers dans le webroot et lance ton serveur (http://localhost/...).

## Usage
- Inscription (`register.php`) : crée un compte avec rôle par défaut `user`.
- Connexion (`login.php`) : met en session l’utilisateur + son rôle.
- Tableau de bord (`tableau.php`) : visible si connecté, affiche le rôle, lien admin si admin.
- Zone admin (`admin_users.php`) : CRUD utilisateurs (créer/modifier/supprimer, choisir le rôle). Protégé par `requireAdmin()`.
- Test rôle (`admin_test.php`) : page de diagnostic qui indique si le compte courant est admin ou non.
- Suppression de compte (`delete.php`) : supprime le compte connecté, puis redirige vers l’accueil.
- Déconnexion (`logout.php`) : détruit la session et retourne sur la connexion.

## Sécurité/Validation
- Emails validés par regex (`validateEmail`).
- Mots de passe : 8-64 caractères, au moins une minuscule, une majuscule, un chiffre et un spécial (`validatePassword`).
- Hashage des mots de passe avec `password_hash` / vérification par `password_verify`.
- Contrôles d’accès :
  - `requireLogin()` protège les pages nécessitant une session.
  - `requireAdmin()` protège les pages admin.
- Protection basique contre l’auto-suppression admin dans `admin_users.php` (un admin ne peut pas se supprimer lui-même via ce formulaire).

## Gestion des rôles
- Constantes : `ROLE_ADMIN = 1`, `ROLE_USER = 2`.
- Pour promouvoir un compte en admin :
  ```sql
  UPDATE users SET role_id = 1 WHERE email = 'ton.email@example.com';
  ```
- Pour revenir en user : `role_id = 2`.

## Structure des fichiers principaux (hors CSS)
- `fonctions.php` : connexion PDO, validations, helpers CRUD user/role, contrôles d’accès.
- `register.php` : inscription (rôle user par défaut).
- `login.php` : connexion, mise en session du rôle.
- `tableau.php` : tableau de bord connecté, lien admin si rôle admin.
- `admin_users.php` : CRUD complet des utilisateurs, réservé admin.
- `admin_test.php` : vérifie visuellement le rôle courant.
- `delete.php` : suppression du compte courant.
- `logout.php` : déconnexion.
- `cour_login.sql` : structure et données d’exemple (rôles + utilisateurs).

## Notes
- Adapter le nom de base (`cour_login` ou autre) et les identifiants BDD dans `fonctions.php`.
- Le front dépend de `style.css` (non documenté ici) pour l’apparence.
