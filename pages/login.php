<?php
// pages/login.php - Login de usuários
require_once '../includes/functions.php';

$error = '';

// Se já estiver logado, redirecionar
if (isLoggedIn()) {
    header('Location: dashboard.php');
    exit();
}

// Processar login
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // Verificar CSRF
    if (!isset($_POST['csrf_token']) || !verifyCSRFToken($_POST['csrf_token'])) {
        $error = 'Erro de validação. Tente novamente.';
        securityLog(null, 'CSRF_INVALID', 'Tentativa de login com CSRF inválido');
    } else {
        $email = sanitize($_POST['email'] ?? '');
        $senha = $_POST['senha'] ?? '';
        $ip = $_SERVER['REMOTE_ADDR'] ?? '0.0.0.0';
        
        // Verificar rate limiting
        if (!checkLoginAttempts($email, $ip)) {
            $error = 'Muitas tentativas. Tente novamente mais tarde.';
            securityLog(null, 'RATE_LIMIT', "Rate limit excedido para $email / $ip");
        } elseif (empty($email) || empty($senha)) {
            $error = 'Preencha todos os campos.';
        } else {
            $db = db();
            
            // Buscar usuário
            $stmt = $db->prepare("
                SELECT id, nome, email, senha_hash, status, nivel, email_confirmado 
                FROM usuarios 
                WHERE email = ?
            ");
            $stmt->execute([$email]);
            $user = $stmt->fetch();
            
            if ($user && password_verify($senha, $user['senha_hash'])) {
                // Verificar se email foi confirmado
                if (!$user['email_confirmado']) {
                    $error = 'Por favor, confirme seu email antes de fazer login.';
                    logLoginAttempt($email, $ip, false);
                } elseif ($user['status'] === 'bloqueado') {
                    $error = 'Sua conta está bloqueada. Contate o suporte.';
                    securityLog($user['id'], 'BLOCKED_LOGIN', 'Tentativa de login em conta bloqueada');
                    logLoginAttempt($email, $ip, false);
                } else {
                    // Login bem-sucedido
                    
                    // Regenerar ID da sessão (proteção contra fixation)
                    session_regenerate_id(true);
                    
                    // Criar sessão
                    $_SESSION['user_id'] = $user['id'];
                    $_SESSION['user_nome'] = $user['nome'];
                    $_SESSION['user_nivel'] = $user['nivel'];
                    $_SESSION['login_time'] = time();
                    $_SESSION['session_id'] = session_id();
                    
                    // Atualizar último login
                    $stmt = $db->prepare("
                        UPDATE usuarios 
                        SET ultimo_login = NOW(), ultimo_ip = ? 
                        WHERE id = ?
                    ");
                    $stmt->execute([$ip, $user['id']]);
                    
                    // Registrar sessão ativa
                    $stmt = $db->prepare("
                        INSERT INTO sessoes_ativas (usuario_id, session_id, ip, user_agent, expira) 
                        VALUES (?, ?, ?, ?, DATE_ADD(NOW(), INTERVAL 1 DAY))
                    ");
                    $stmt->execute([
                        $user['id'], 
                        session_id(), 
                        $ip, 
                        $_SERVER['HTTP_USER_AGENT'] ?? 'Desconhecido'
                    ]);
                    
                    // Registrar log
                    logLoginAttempt($email, $ip, true);
                    securityLog($user['id'], 'LOGIN_SUCCESS', 'Login realizado com sucesso');
                    
                    // Redirecionar (para página anterior ou dashboard)
                    $redirect = $_SESSION['redirect_after'] ?? 'dashboard.php';
                    unset($_SESSION['redirect_after']);
                    
                    header("Location: $redirect");
                    exit();
                }
            } else {
                // Login falhou
                $error = 'Email ou senha inválidos.';
                logLoginAttempt($email, $ip, false);
                securityLog(null, 'LOGIN_FAIL', "Tentativa de login falhou para $email");
            }
        }
    }
}

$csrf_token = generateCSRFToken();
?>
<!DOCTYPE html>
<html lang="pt-BR">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Login - MineAddonsNews</title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0-beta3/css/all.min.css">
    <link rel="stylesheet" href="../style.css">
    <style>
        /* Mesmo estilo do register */
        .auth-container {
            min-height: 100vh;
            display: flex;
            align-items: center;
            justify-content: center;
            padding: 100px 20px 50px;
            background: linear-gradient(135deg, #0a0a0a 0%, #1a1a1a 100%);
        }
        
        .auth-box {
            background: #111;
            border: 1px solid #33ff33;
            border-radius: 20px;
            padding: 40px;
            width: 100%;
            max-width: 450px;
            box-shadow: 0 0 30px rgba(51, 255, 51, 0.3);
        }
        
        .auth-box h1 {
            color: #33ff33;
            text-align: center;
            margin-bottom: 30px;
            font-size: 2rem;
        }
        
        .form-group {
            margin-bottom: 20px;
        }
        
        .form-group label {
            display: block;
            margin-bottom: 5px;
            color: #fff;
        }
        
        .form-group input {
            width: 100%;
            padding: 12px;
            background: #222;
            border: 1px solid #333;
            border-radius: 8px;
            color: #fff;
            font-size: 16px;
        }
        
        .form-group input:focus {
            outline: none;
            border-color: #33ff33;
            box-shadow: 0 0 10px rgba(51, 255, 51, 0.3);
        }
        
        .btn-auth {
            width: 100%;
            padding: 14px;
            background: #33ff33;
            color: #000;
            border: none;
            border-radius: 8px;
            font-size: 18px;
            font-weight: bold;
            cursor: pointer;
            transition: all 0.3s;
        }
        
        .btn-auth:hover {
            background: #2be02b;
            box-shadow: 0 0 20px #33ff33;
        }
        
        .alert {
            padding: 12px;
            border-radius: 8px;
            margin-bottom: 20px;
        }
        
        .alert-error {
            background: rgba(255, 0, 0, 0.1);
            border: 1px solid #ff3333;
            color: #ff9999;
        }
        
        .auth-footer {
            text-align: center;
            margin-top: 20px;
            color: #999;
        }
        
        .auth-footer a {
            color: #33ff33;
            text-decoration: none;
        }
        
        .auth-footer a:hover {
            text-decoration: underline;
        }
        
        .remember-me {
            display: flex;
            align-items: center;
            gap: 8px;
            color: #999;
        }
    </style>
</head>
<body>
    <?php include '../includes/header.php'; ?>
    
    <div class="auth-container">
        <div class="auth-box">
            <h1>Entrar</h1>
            
            <?php if ($error): ?>
                <div class="alert alert-error"><?= $error ?></div>
            <?php endif; ?>
            
            <form method="POST" action="">
                <input type="hidden" name="csrf_token" value="<?= $csrf_token ?>">
                
                <div class="form-group">
                    <label for="email">E-mail</label>
                    <input type="email" id="email" name="email" required
                           value="<?= isset($_POST['email']) ? sanitize($_POST['email']) : '' ?>">
                </div>
                
                <div class="form-group">
                    <label for="senha">Senha</label>
                    <input type="password" id="senha" name="senha" required>
                </div>
                
                <div class="form-group">
                    <label class="remember-me">
                        <input type="checkbox" name="lembrar">
                        <span>Manter conectado</span>
                    </label>
                </div>
                
                <button type="submit" class="btn-auth">Entrar</button>
            </form>
            
            <div class="auth-footer">
                <p><a href="recuperar-senha.php">Esqueceu a senha?</a></p>
                <p>Não tem uma conta? <a href="register.php">Cadastre-se</a></p>
            </div>
        </div>
    </div>
    
    <?php include '../includes/footer.php'; ?>
</body>
</html>
