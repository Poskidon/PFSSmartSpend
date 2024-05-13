<?php
$mois =  ["janvier","fevrier","mars","mai","avril","juin","juillet","aout","septembre","ocotbre","novembre","decembre"];
$jourdelasemaine = ["Dimanche","Lundi","Mardi","Mercredi","Jeudi","Vendredi","Samedi"];
$message = "";
include("form.php");
if(isset($valider)){
    if(!preg_match("#^[a-zA-zéèêïÉÈÊÏ \-]+$#",$nom))
        $message="<div class='erreur'>Nom invalide!</div>";
    if(!preg_match("#^[a-zA-zéèêïÉÈÊÏ \-]+$#",$prenom))
        $message.="<div class='erreur'>Prénom invalide!</div>";
    if (!filter_var($email, FILTER_VALIDATE_EMAIL))
        $message.="<div class='erreur'>Email invalide!</div>";
    if($mdp!=$rmdp)
        $message.="<div class='erreur'>Mot de passes non identiques!</div>";
    if($message === ""){
// Connexion à la base de données
$servername = "127.0.0.1";
$username = "root";
$password = "";
$database = "smartspenddb";

$conn = new mysqli($servername, $username, $password, $database);

// Vérifier la connexion
if ($conn->connect_error) {
    die("La connexion à la base de données a échoué : " . $conn->connect_error);
}
// Date d'inscription actuelle
$date_inscription = date("Y-m-d");
$Fullname = $nom." ".$prenom;
$MotDePasse = md5($mdp);
// Insérer les données dans la base de données avec la date d'inscription actuelle
$sql = "INSERT INTO Utilisateurs (Nom, Email, MotDePasse, DateInscription) VALUES ('$Fullname', '$email', '$MotDePasse', '$date_inscription')";
if ($conn->query($sql) === TRUE) {
    $message ="<div class='ok'>Félicitations, vous êtes maintenant inscrit sur notre site! Vous pouvez vous connecter avec vos identifiants.</div>";
} else {
     $message ="<div class='erreur'>Erreur lors du process d'inscription</div>";
}

// Fermer la connexion à la base de données
$conn->close();
  } 
}
?>
<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link rel="stylesheet" href="https://unicons.iconscout.com/release/v4.0.0/css/line.css">
    <link rel="stylesheet" href="CSS/inscription.css">
         
    <title>Login & Registration Form</title> 
</head>
<body>
    
    <div class="container">
            <div class="form signup">
                <span class="title">Registration</span>

                <form name="fo" action="" method="post">
                    <div class="input-field">
                        <input type="text" placeholder="Entrez votre nom" name="nom" required>
                        <i class="uil uil-user"></i>
                    </div>
                    <div class="input-field">
                        <input type="text" placeholder="Entez votre prénom" name="prenom" required>
                        <i class="uil uil-user"></i>
                    </div>
                    <div class="input-field">
                        <input type="text" placeholder="Entrez votre email" name="email" required>
                        <i class="uil uil-envelope icon"></i>
                    </div>
                    <div class="input-field">
                        <input type="password" class="password" placeholder="Créer votre mot de passe" name="mdp" required>
                        <i class="uil uil-lock icon"></i>
                    </div>
                    <div class="input-field">
                        <input type="password" class="password" placeholder="Confirmez votre mot de passe" name="rmdp" required>
                        <i class="uil uil-lock icon"></i>
                    </div>
                    <div class="input-field button">
                        <input type="submit" value="S'inscrire" name="valider">
                    </div>
                </form>
                <?php echo $message;?>

                <div class="login-signup">
                    <span class="text">Already a member?
                        <a href="login.php" class="text login-link">Login Now</a>
                    </span>
                </div>
            </div>
        </div>
    </div>
</body>
</html>