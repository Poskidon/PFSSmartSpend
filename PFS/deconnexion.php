<?php
session_start();

// Détruire complètement la session
session_destroy();

// Rediriger vers une autre page après la déconnexion
header("Location: index.php");
exit();
?>
