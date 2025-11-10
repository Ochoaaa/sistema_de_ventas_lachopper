<?php
session_start();
session_unset(); // Borra todas las variables de sesión
session_destroy(); // Destruye la sesión
setcookie(session_name(), '', time() - 3600, '/'); // Borra la cookie del navegador
header("Location: ../index.php"); // Redirige al login
exit;
