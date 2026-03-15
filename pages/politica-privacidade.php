<?php
// pages/politica-privacidade.php
require_once '../includes/functions.php';
?>
<!DOCTYPE html>
<html lang="pt-BR">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Política de Privacidade - MineAddonsNews</title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0-beta3/css/all.min.css">
    <link rel="stylesheet" href="../style.css">
    <style>
        .privacy-content {
            max-width: 800px;
            margin: 120px auto 60px;
            padding: 40px;
            background: #111;
            border: 1px solid #33ff33;
            border-radius: 20px;
            color: #e0e0e0;
        }
        
        .privacy-content h1 {
            color: #33ff33;
            font-size: 2.5rem;
            margin-bottom: 30px;
        }
        
        .privacy-content h2 {
            color: #fff;
            margin: 30px 0 15px;
        }
        
        .privacy-content p {
            margin-bottom: 15px;
            line-height: 1.8;
        }
        
        .privacy-content ul {
            margin-bottom: 20px;
            padding-left: 20px;
        }
        
        .privacy-content li {
            margin-bottom: 10px;
        }
        
        .last-updated {
            color: #999;
            font-style: italic;
            margin-bottom: 30px;
        }
    </style>
</head>
<body>
    <?php include '../includes/header.php'; ?>
    
    <div class="privacy-content">
        <h1>Política de Privacidade</h1>
        <p class="last-updated">Última atualização: 15 de março de 2025</p>
        
        <h2>1. Informações que Coletamos</h2>
        <p>Coletamos as seguintes informações quando você utiliza nosso site:</p>
        <ul>
            <li><strong>Informações de cadastro:</strong> nome, e-mail (obrigatórios para criar uma conta)</li>
            <li><strong>Informações de uso:</strong> addons baixados, páginas visitadas</li>
            <li><strong>Informações técnicas:</strong> endereço IP, tipo de navegador, sistema operacional</li>
        </ul>
        
        <h2>2. Como Usamos suas Informações</h2>
        <p>Utilizamos suas informações para:</p>
        <ul>
            <li>Fornecer acesso aos addons comprados</li>
            <li>Processar pagamentos via Mercado Pago</li>
            <li>Enviar notificações sobre novos addons (com seu consentimento)</li>
            <li>Melhorar nossa plataforma e experiência do usuário</li>
            <li>Prevenir fraudes e garantir a segurança do site</li>
        </ul>
        
        <h2>3. Proteção de Dados</h2>
        <p>Implementamos medidas de segurança rigorosas:</p>
        <ul>
            <li>Todas as senhas são armazenadas com hash (bcrypt)</li>
            <li>Conexão HTTPS obrigatória</li>
            <li>Proteção contra SQL Injection e XSS</li>
            <li>Monitoramento de atividades suspeitas</li>
            <li>Nunca armazenamos dados de cartão de crédito (processados pelo Mercado Pago)</li>
        </ul>
        
        <h2>4. Compartilhamento de Dados</h2>
        <p>Não vendemos seus dados pessoais. Compartilhamos apenas quando necessário:</p>
        <ul>
            <li><strong>Mercado Pago:</strong> para processar pagamentos</li>
            <li><strong>Autoridades judiciais:</strong> quando exigido por lei</li>
        </ul>
        
        <h2>5. Seus Direitos</h2>
        <p>Você tem direito a:</p>
        <ul>
            <li>Acessar seus dados pessoais</li>
            <li>Corrigir dados incorretos</li>
            <li>Solicitar exclusão da conta</li>
            <li>Revogar consentimento para marketing</li>
        </ul>
        
        <h2>6. Cookies</h2>
        <p>Utilizamos cookies essenciais para:</p>
        <ul>
            <li>Manter você logado</li>
            <li>Lembrar preferências</li>
            <li>Analisar tráfego (Google Analytics - opcional)</li>
        </ul>
        
        <h2>7. Contato</h2>
        <p>Para questões sobre privacidade: privacidade@mineaddonsnews.online</p>
        
        <h2>8. Menores de Idade</h2>
        <p>Nosso site não é direcionado a menores de 13 anos. Se você é menor, não se cadastre sem autorização dos pais.</p>
        
        <h2>9. Alterações nesta Política</h2>
        <p>Podemos atualizar esta política ocasionalmente. Recomendamos revisar periodicamente.</p>
    </div>
    
    <?php include '../includes/footer.php'; ?>
</body>
</html>
