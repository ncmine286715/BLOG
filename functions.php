<?php
// functions.php - Funções de segurança e utilidades
require_once __DIR__ . '/db_connect.php';

// Gerar token CSRF
function generateCSRFToken() {
    if (empty($_SESSION['csrf_token'])) {
        $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
    }
    return $_SESSION['csrf_token'];
}

// Verificar token CSRF
function verifyCSRFToken($token) {
    if (empty($_SESSION['csrf_token']) || $token !== $_SESSION['csrf_token']) {
        return false;
    }
    return true;
}

// Sanitizar input
function sanitize($input) {
    return htmlspecialchars(trim($input), ENT_QUOTES, 'UTF-8');
}

// Validar email
function validateEmail($email) {
    return filter_var($email, FILTER_VALIDATE_EMAIL) && 
           preg_match('/^[a-zA-Z0-9._%+-]+@[a-zA-Z0-9.-]+\.[a-zA-Z]{2,}$/', $email);
}

// Verificar rate limiting de login
function checkLoginAttempts($email, $ip) {
    $db = db();
    
    // Limpar tentativas antigas
    $stmt = $db->prepare("DELETE FROM login_attempts WHERE tentativa < DATE_SUB(NOW(), INTERVAL ? MINUTE)");
    $stmt->execute([LOGIN_TIMEOUT]);
    
    // Contar tentativas recentes
    $stmt = $db->prepare("
        SELECT COUNT(*) as attempts 
        FROM login_attempts 
        WHERE (email = ? OR ip = ?) 
        AND sucesso = 0 
        AND tentativa > DATE_SUB(NOW(), INTERVAL ? MINUTE)
    ");
    $stmt->execute([$email, $ip, LOGIN_TIMEOUT]);
    $result = $stmt->fetch();
    
    return $result['attempts'] < MAX_LOGIN_ATTEMPTS;
}

// Registrar tentativa de login
function logLoginAttempt($email, $ip, $sucesso) {
    $db = db();
    $stmt = $db->prepare("INSERT INTO login_attempts (email, ip, sucesso) VALUES (?, ?, ?)");
    $stmt->execute([$email, $ip, $sucesso]);
}

// Registrar log de segurança
function securityLog($usuario_id, $acao, $detalhes = null) {
    $db = db();
    $ip = $_SERVER['REMOTE_ADDR'] ?? '0.0.0.0';
    $user_agent = $_SERVER['HTTP_USER_AGENT'] ?? 'Desconhecido';
    
    $stmt = $db->prepare("
        INSERT INTO security_logs (usuario_id, acao, ip, user_agent, detalhes) 
        VALUES (?, ?, ?, ?, ?)
    ");
    $stmt->execute([$usuario_id, $acao, $ip, $user_agent, $detalhes]);
}

// Verificar se usuário está logado
function isLoggedIn() {
    return isset($_SESSION['user_id']) && isset($_SESSION['login_time']);
}

// Redirecionar se não estiver logado
function requireLogin() {
    if (!isLoggedIn()) {
        $_SESSION['redirect_after'] = $_SERVER['REQUEST_URI'];
        header('Location: ' . SITE_URL . '/pages/login.php');
        exit();
    }
}

// Obter usuário atual
function getCurrentUser() {
    if (!isLoggedIn()) return null;
    
    $db = db();
    $stmt = $db->prepare("SELECT id, nome, email, nivel, status FROM usuarios WHERE id = ?");
    $stmt->execute([$_SESSION['user_id']]);
    return $stmt->fetch();
}

// Logout seguro
function logout() {
    // Remover sessão ativa do banco
    if (isset($_SESSION['session_id'])) {
        $db = db();
        $stmt = $db->prepare("DELETE FROM sessoes_ativas WHERE session_id = ?");
        $stmt->execute([session_id()]);
    }
    
    // Destruir sessão
    $_SESSION = [];
    
    if (ini_get("session.use_cookies")) {
        $params = session_get_cookie_params();
        setcookie(session_name(), '', time() - 42000,
            $params["path"], $params["domain"],
            $params["secure"], $params["httponly"]
        );
    }
    
    session_destroy();
}

// Gerar senha segura
function generateSecurePassword($length = 12) {
    return bin2hex(random_bytes($length));
}

// Enviar email de confirmação
function sendConfirmationEmail($email, $nome, $token) {
    $assunto = 'Confirme seu cadastro - ' . SITE_NAME;
    $link = SITE_URL . '/pages/confirmar.php?token=' . urlencode($token);
    
    $mensagem = "
    <html>
    <body style='font-family: Arial, sans-serif;'>
        <h2>Olá $nome!</h2>
        <p>Obrigado por se cadastrar no " . SITE_NAME . ".</p>
        <p>Para ativar sua conta, clique no link abaixo:</p>
        <p><a href='$link' style='background: #33ff33; color: #000; padding: 10px 20px; text-decoration: none;'>Confirmar Email</a></p>
        <p>Se você não criou esta conta, ignore este email.</p>
        <p>Atenciosamente,<br>Equipe " . SITE_NAME . "</p>
    </body>
    </html>
    ";
    
    $headers = "MIME-Version: 1.0\r\n";
    $headers .= "Content-type: text/html; charset=utf-8\r\n";
    $headers .= "From: noreply@" . $_SERVER['HTTP_HOST'] . "\r\n";
    
    return mail($email, $assunto, $mensagem, $headers);
}

// Função para gerar links de download seguros
function getDownloadLink($addon_id) {
    if (!isLoggedIn()) return null;
    
    $db = db();
    
    // Verificar se usuário tem acesso (comprou ou é grátis)
    $stmt = $db->prepare("
        SELECT a.*, 
               CASE 
                   WHEN a.tipo = 'gratis' THEN TRUE
                   WHEN c.id IS NOT NULL AND c.status_pagamento = 'aprovado' THEN TRUE
                   ELSE FALSE
               END as pode_baixar
        FROM addons a
        LEFT JOIN compras c ON c.addon_id = a.id AND c.usuario_id = ? AND c.status_pagamento = 'aprovado'
        WHERE a.id = ? AND a.ativo = 1
    ");
    $stmt->execute([$_SESSION['user_id'], $addon_id]);
    $addon = $stmt->fetch();
    
    if (!$addon || !$addon['pode_baixar']) {
        return null;
    }
    
    // Incrementar contador de downloads
    $stmt = $db->prepare("UPDATE addons SET downloads = downloads + 1 WHERE id = ?");
    $stmt->execute([$addon_id]);
    
    // Retornar link apropriado
    return $addon['tipo'] == 'gratis' ? $addon['link_terabox'] : $addon['link_premium'];
}
?>
