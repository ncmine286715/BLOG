<?php
// pages/logout.php - Logout seguro
require_once '../includes/functions.php';

securityLog($_SESSION['user_id'] ?? null, 'LOGOUT', 'Usuário fez logout');

// Fazer logout
logout();

// Redirecionar para home
header('Location: ../index.php');
exit();
?>
