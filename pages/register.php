<?php
// pages/register.php - Cadastro de usuários
require_once '../includes/functions.php';

$error = '';
$success = '';

// Se já estiver logado, redirecionar
if (isLoggedIn()) {
    header('Location: dashboard.php');
    exit();
}

// Processar formulário
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // Verificar CSRF
    if (!isset($_POST['csrf_token']) || !verifyCSRFToken($_POST['csrf_token'])) {
        $error = 'Erro de validação. Tente novamente.';
        securityLog(null, 'CSRF_INVALID', 'Tentativa de registro com CSRF inválido');
    } else {
        $nome = sanitize($_POST['nome'] ?? '');
        $email = sanitize($_POST['email'] ?? '');
        $senha = $_POST['senha'] ?? '';
        $confirmar_senha = $_POST['confirmar_senha'] ?? '';
        
        // Validações
        if (empty($nome) || empty($email) || empty($senha)) {
            $error = 'Todos os campos são obrigatórios.';
        } elseif (!validateEmail($email)) {
            $error = 'Email inválido.';
        } elseif (strlen($senha) < 8) {
            $error = 'A senha deve ter no mínimo 8 caracteres.';
        } elseif ($senha !== $confirmar_senha) {
            $error = 'As senhas não conferem.';
        } else {
            $db = db();
            
            // Verificar se email já existe
            $stmt = $db->prepare("SELECT id FROM usuarios WHERE email = ?");
            $stmt->execute([$email]);
            
            if ($stmt->fetch()) {
                $error = 'Este email já está cadastrado.';
                securityLog(null, 'DUPLICATE_EMAIL', "Tentativa de registro com email existente: $email");
            } else {
                // Criar usuário
                $senha_hash = password_hash($senha, PASSWORD_DEFAULT);
                $token_confirmacao = bin2hex(random_bytes(32));
                
                $stmt = $db->prepare("
                    INSERT INTO usuarios (nome, email, senha_hash, token_confirmacao, ultimo_ip) 
                    VALUES (?, ?, ?, ?, ?)
                ");
                
                $ip = $_SERVER['REMOTE_ADDR'] ?? '0.0.0.0';
                
                if ($stmt->execute([$nome, $email, $senha_hash, $token_confirmacao, $ip])) {
                    $usuario_id = $db->lastInsertId();
                    
                    // Enviar email de confirmação
                    sendConfirmationEmail($email, $nome, $token_confirmacao);
                    
                    securityLog($usuario_id, 'REGISTER_SUCCESS', "Novo usuário registrado: $email");
                    $success = 'Cadastro realizado! Verifique seu email para ativar a conta.';
                    
                    // Redirecionar após 3 segundos
                    header("refresh:3;url=login.php");
                } else {
                    $error = 'Erro ao cadastrar. Tente novamente.';
                    securityLog(null, 'REGISTER_ERROR', "Erro no banco ao registrar $email");
                }
            }
        }
    }
}

// Gerar token CSRF
$csrf_token = generateCSRFToken();
?>
<!DOCTYPE html>
<html lang="pt-BR">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Cadastro - MineAddonsNews</title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0-beta3/css/all.min.css">
    <link rel="stylesheet" href="../style.css">
    <style>
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
        
        .alert-success {
            background: rgba(51, 255, 51, 0.1);
            border: 1px solid #33ff33;
            color: #33ff33;
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
        
        .password-requirements {
            font-size: 12px;
            color: #999;
            margin-top: 5px;
        }
    </style>
</head>
<body>
    <?php include '../includes/header.php'; ?>
    
    <div class="auth-container">
        <div class="auth-box">
            <h1>Criar Conta</h1>
            
            <?php if ($error): ?>
                <div class="alert alert-error"><?= $error ?></div>
            <?php endif; ?>
            
            <?php if ($success): ?>
                <div class="alert alert-success"><?= $success ?></div>
            <?php endif; ?>
            
            <form method="POST" action="">
                <input type="hidden" name="csrf_token" value="<?= $csrf_token ?>">
                
                <div class="form-group">
                    <label for="nome">Nome</label>
                    <input type="text" id="nome" name="nome" required 
                           value="<?= isset($_POST['nome']) ? sanitize($_POST['nome']) : '' ?>">
                </div>
                
                <div class="form-group">
                    <label for="email">E-mail</label>
                    <input type="email" id="email" name="email" required
                           value="<?= isset($_POST['email']) ? sanitize($_POST['email']) : '' ?>">
                </div>
                
                <div class="form-group">
                    <label for="senha">Senha</label>
                    <input type="password" id="senha" name="senha" required minlength="8">
                    <div class="password-requirements">
                        Mínimo 8 caracteres
                    </div>
                </div>
                
                <div class="form-group">
                    <label for="confirmar_senha">Confirmar Senha</label>
                    <input type="password" id="confirmar_senha" name="confirmar_senha" required minlength="8">
                </div>
                
                <div class="form-group">
                    <label>
                        <input type="checkbox" name="termos" required>
                        <span style="color: #999; font-size: 14px;">
                            Li e aceito os <a href="politica-privacidade.php" target="_blank">termos de uso</a> e política de privacidade
                        </span>
                    </label>
                </div>
                
                <button type="submit" class="btn-auth">Criar Conta</button>
            </form>
            
            <div class="auth-footer">
                <p>Já tem uma conta? <a href="login.php">Faça login</a></p>
            </div>
        </div>
    </div>
    
    <?php include '../includes/footer.php'; ?>
</body>
</html>
