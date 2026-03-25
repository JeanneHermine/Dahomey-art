<?php
session_start();
require_once './config.php';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $nom = trim($_POST['nom']);
    $prenom = trim($_POST['prenom']);
    $email = trim($_POST['email'] ?? '');
    $telephone = trim($_POST['telephone'] ?? '');
    $adresse = trim($_POST['adresse']);
    $date_inscription = date('Y-m-d');
    $mot = $_POST['mot_de_passe'];
    $confirm = $_POST['confirmer_mot_de_passe'];

    // ⚠️ Vérifier au moins un contact (email ou téléphone)
    if (empty($email) && empty($telephone)) {
        $_SESSION['message_client'] = "Veuillez fournir au moins une adresse e-mail ou un numéro de téléphone.";
        $_SESSION['type_client'] = "error";
        header("Location: ../front/templates/inscription_client.php");
        exit();
    }

    // ✅ Valider email uniquement s’il est rempli
    if (!empty($email)) {
        $email = filter_var($email, FILTER_SANITIZE_EMAIL);
        if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
            $_SESSION['message_client'] = "Adresse e-mail invalide.";
            $_SESSION['type_client'] = "error";
            header("Location: ../front/templates/inscription_client.php");
            exit();
        }
    } else {
        $email = null;
    }

    // 🔒 Vérifier mot de passe
    if ($mot !== $confirm) {
        $_SESSION['message_client'] = "Les mots de passe ne correspondent pas.";
        $_SESSION['type_client'] = "error";
        header("Location: ../front/templates/inscription_client.php");
        exit();
    }

    $mot_de_passe = password_hash($mot, PASSWORD_DEFAULT);

    try {
        // 🔍 Vérifier s'il existe un client avec le même email + téléphone
        $req = $pdo->prepare("SELECT * FROM clients WHERE email = ? AND telephone = ?");
        $req->execute([$email, $telephone]);
        if ($req->rowCount() > 0) {
            $_SESSION['message_client'] = "Un client avec cet email et ce numéro existe déjà.";
            $_SESSION['type_client'] = "error";
            header("Location: ../front/templates/inscription_client.php");
            exit();
        }

        // ✅ Insertion
        $stmt = $pdo->prepare("INSERT INTO clients (nom, prenom, email, mot_de_passe, adresse, telephone, date_inscription)
                               VALUES (?, ?, ?, ?, ?, ?, ?)");
        $stmt->execute([$nom, $prenom, $email, $mot_de_passe, $adresse, $telephone ?: null, $date_inscription]);

        $_SESSION['message_client'] = "Inscription réussie !";
        $_SESSION['type_client'] = "success";
        header("Location: ../front/templates/boutique.php");
        exit();

    } catch (PDOException $e) {
        $_SESSION['message_client'] = "Erreur : " . $e->getMessage();
        $_SESSION['type_client'] = "error";
        header("Location: ../front/templates/inscription_client.php");
        exit();
    }
}
