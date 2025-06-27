<?php
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

require_once '../includes/functions.php';
require_once '../config/database.php';


// Si déjà connecté, rediriger
if (isLoggedIn()) {
    header('Location: dashboard.php');
    exit();
}

$error = '';
$success = '';

// Récupérer les filières pour le formulaire
try {
    $pdo = getDB();
    $stmt = $pdo->query("SELECT id, nom FROM filieres WHERE actif = TRUE ORDER BY nom");
    $filieres = $stmt->fetchAll();
} catch (Exception $e) {
    $error = 'Erreur lors de la récupération des filières.';
    $filieres = [];
}

// Traitement du formulaire
if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $nom = cleanInput($_POST['nom']);
    $prenom = cleanInput($_POST['prenom']);
    $email = cleanInput($_POST['email']);
    $password = $_POST['password'];
    $confirm_password = $_POST['confirm_password'];
    $filiere_id = (int)$_POST['filiere_id'];
    $niveau = cleanInput($_POST['niveau']);
   
    
    // Validation
    if (empty($nom) || empty($prenom) || empty($email) || empty($password)) {
        $error = 'Veuillez remplir tous les champs obligatoires.';
    } elseif ($password !== $confirm_password) {
        $error = 'Les mots de passe ne correspondent pas.';
    } elseif (strlen($password) < 6) {
        $error = 'Le mot de passe doit contenir au moins 6 caractères.';
    } else {
        try {
            // Vérifier si l'email existe déjà
            $stmt = $pdo->prepare("SELECT id FROM utilisateurs WHERE email = ?");
            $stmt->execute([$email]);
            
            if ($stmt->fetch()) {
                $error = 'Cette adresse email est déjà utilisée.';
            } else {
                // Créer le compte
                $password_hash = hashPassword($password);
                
                $stmt = $pdo->prepare("
                    INSERT INTO utilisateurs (nom, prenom, email, mot_de_passe_hash, role, filiere_id, niveau) 
                    VALUES (?, ?, ?, ?, 'etudiant', ?, ?)
                ");
                
                $stmt->execute([$nom, $prenom, $email, $password_hash, $filiere_id, $niveau]);
                
                $success = 'Votre compte a été créé avec succès ! Vous pouvez maintenant vous connecter.';
            }
        } catch (Exception $e) {
            $error = 'Erreur lors de la création du compte : ' . $e->getMessage();
        }
    }
}
?>

<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Inscription - Gestion Projets ESI</title>
    <link rel="stylesheet" href="../assets/css/style.css">
</head>
<body>
    <div class="login-container" style="max-width: 500px; margin-top: 50px;">
        <div class="login-header">
            <h2>Inscription Étudiant</h2>
            <p>École Supérieure d'Informatique</p>
        </div>
        
        <?php if ($error): ?>
            <div class="alert alert-error"><?php echo $error; ?></div>
        <?php endif; ?>
        
        <?php if ($success): ?>
            <div class="alert alert-success"><?php echo $success; ?></div>
            <div style="text-align: center; margin-top: 20px;">
                <a href="../index.php" class="btn">Se connecter</a>
            </div>
        <?php else: ?>
        
        <form method="POST" action="">
            <div class="form-group">
                <label for="nom">Nom * :</label>
                <input type="text" id="nom" name="nom" required 
                       value="<?php echo isset($_POST['nom']) ? htmlspecialchars($_POST['nom']) : ''; ?>">
            </div>
            
            <div class="form-group">
                <label for="prenom">Prénom * :</label>
                <input type="text" id="prenom" name="prenom" required 
                       value="<?php echo isset($_POST['prenom']) ? htmlspecialchars($_POST['prenom']) : ''; ?>">
            </div>
            
            <div class="form-group">
                <label for="email">Email * :</label>
                <input type="email" id="email" name="email" required 
                       value="<?php echo isset($_POST['email']) ? htmlspecialchars($_POST['email']) : ''; ?>">
            </div>
            
            <div class="form-group">
                <label for="filiere_id">Filière * :</label>
                <select id="filiere_id" name="filiere_id" required>
                    <option value="">Choisir une filière</option>
                    <?php foreach ($filieres as $filiere): ?>
                        <option value="<?php echo $filiere['id']; ?>" 
                                <?php echo (isset($_POST['filiere_id']) && $_POST['filiere_id'] == $filiere['id']) ? 'selected' : ''; ?>>
                            <?php echo htmlspecialchars($filiere['nom']); ?>
                        </option>
                    <?php endforeach; ?>
                </select>
            </div>
            
            <div class="form-group">
                <label for="niveau">Niveau :</label>
                <select id="niveau" name="niveau">
                    <option value="">Choisir un niveau</option>
                    <option value="L1-TC1" <?php echo (isset($_POST['niveau']) && $_POST['niveau'] == 'L1-TC1') ? 'selected' : ''; ?>>L1-TC1</option>
                    <option value="L2-TC2" <?php echo (isset($_POST['niveau']) && $_POST['niveau'] == 'L2-TC2') ? 'selected' : ''; ?>>L2-TC2</option>
                    <option value="L3-IRS" <?php echo (isset($_POST['niveau']) && $_POST['niveau'] == 'L3-IRS') ? 'selected' : ''; ?>>L3-IRS</option>
                    <option value="L3-ISI" <?php echo (isset($_POST['niveau']) && $_POST['niveau'] == 'M1-ISI') ? 'selected' : ''; ?>>M1-ISI</option>
                   
                </select>
            </div>
            
            
            <div class="form-group">
                <label for="password">Mot de passe * :</label>
                <input type="password" id="password" name="password" required>
                <small>Minimum 6 caractères</small>
            </div>
            
            <div class="form-group">
                <label for="confirm_password">Confirmer le mot de passe * :</label>
                <input type="password" id="confirm_password" name="confirm_password" required>
            </div>
            
            <div class="form-group">
                <button type="submit" class="btn btn-success" style="width: 100%;">Créer mon compte</button>
            </div>
        </form>
        
        <?php endif; ?>
        
        <div style="text-align: center; margin-top: 20px;">
            <p><a href="../index.php" style="color: #3498db; text-decoration: none;">← Retour à la connexion</a></p>
        </div>
    </div>
</body>
</html>
