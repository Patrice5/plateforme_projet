<?php
// Affichage des erreurs en développement
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);
session_start();
// Définir l'URL de base
define('BASE_URL', 'https://' . $_SERVER['HTTP_HOST'] . '/plateforme_projets/');
// Inclusions
require_once 'includes/functions.php';
require_once 'config/database.php';
// Vérification session
if (isLoggedIn()) {
    $user = getUserById($_SESSION['user_id']);
    if ($user) {
       
        header('Location: pages/dashboard.php');
        exit();
    } else {
        echo "Session active mais utilisateur introuvable, on logout.";
        
        logoutUser();
    }
}
// Traitement du formulaire
$error = '';
if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $email = sanitizeInput($_POST['email']);
    $password = $_POST['password'];
    if (empty($email) || empty($password)) {
        $error = 'Veuillez remplir tous les champs.';
    } else {
        try {
            $pdo = getDB();
            if ($pdo) {
                $stmt = $pdo->prepare("SELECT * FROM utilisateurs WHERE email = ? AND actif = TRUE");
                $stmt->execute([$email]);
                $user = $stmt->fetch();
                if ($user && verifyPassword($password, $user['mot_de_passe_hash'])) {
                    loginUser($user);
                    header('Location: pages/dashboard.php');
                    exit();
                } else {
                    $error = 'Email ou mot de passe incorrect.';
                }
            } else {
                $error = 'Connexion à la base de données impossible.';
            }
        } catch (Exception $e) {
            $error = 'Erreur système : ' . $e->getMessage();
        }
    }
}
?>
<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <title>Connexion - Gestion Projets ESI</title>
    <link rel="stylesheet" href="assets/css/style.css">
    <style>
        .password-container {
            position: relative;
        }
        
        .password-toggle {
            position: absolute;
            right: 10px;
            top: 50%;
            transform: translateY(-50%);
            background: none;
            border: none;
            cursor: pointer;
            color: #666;
            font-size: 14px;
            padding: 0;
        }
        
        .password-toggle:hover {
            color: #3498db;
        }
        
        .form-group input[type="password"],
        .form-group input[type="text"] {
            padding-right: 40px;
        }
    </style>
</head>
<body>
    <div class="login-container">
        <div class="login-header">
            <h2>ESI - Gestion des Projets</h2>
            <p>Université Nazi BONI</p>
        </div>
        <?php if ($error): ?>
            <div class="alert alert-error"><?= htmlspecialchars($error) ?></div>
        <?php endif; ?>
        <form method="POST">
            <div class="form-group">
                <label for="email">Email :</label>
                <input type="email" name="email" id="email" required
                       value="<?= isset($_POST['email']) ? htmlspecialchars($_POST['email']) : '' ?>">
            </div>
            <div class="form-group">
                <label for="password">Mot de passe :</label>
                <div class="password-container">
                    <input type="password" name="password" id="password" required>
                    <button type="button" class="password-toggle" onclick="togglePassword()">
                        ○
                    </button>
                </div>
            </div>
            <div class="form-group">
                <button type="submit" class="btn" style="width:100%;">Se connecter</button>
            </div>
        </form>
        <div style="text-align:center;margin-top:20px;">
           
            <p> Pas encore de compte ?<a href="pages/register.php" style="color:#3498db;">Créer un compte étudiant</a></p>
        </div>
    </div>

    <script>
        function togglePassword() {
            const passwordInput = document.getElementById('password');
            const toggleButton = document.querySelector('.password-toggle');
            
            if (passwordInput.type === 'password') {
                passwordInput.type = 'text';
                toggleButton.innerHTML = '●';
                toggleButton.title = 'Masquer le mot de passe';
            } else {
                passwordInput.type = 'password';
                toggleButton.innerHTML = '○';
                toggleButton.title = 'Afficher le mot de passe';
            }
        }
    </script>
</body>
</html>
