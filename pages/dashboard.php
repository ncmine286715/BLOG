<?php
// pages/dashboard.php - Painel do usuário
require_once '../includes/functions.php';
requireLogin();

$currentUser = getCurrentUser();
$db = db();

// Buscar addons comprados
$stmt = $db->prepare("
    SELECT a.*, c.data_compra 
    FROM compras c
    JOIN addons a ON a.id = c.addon_id
    WHERE c.usuario_id = ? AND c.status_pagamento = 'aprovado'
    ORDER BY c.data_compra DESC
");
$stmt->execute([$currentUser['id']]);
$compras = $stmt->fetchAll();

// Buscar downloads gratuitos recentes
$stmt = $db->prepare("
    SELECT * FROM addons 
    WHERE tipo = 'gratis' 
    ORDER BY downloads DESC 
    LIMIT 5
");
$gratuitos = $stmt->fetchAll();
?>
<!DOCTYPE html>
<html lang="pt-BR">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Meu Painel - MineAddonsNews</title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0-beta3/css/all.min.css">
    <link rel="stylesheet" href="../style.css">
    <style>
        .dashboard {
            padding: 100px 20px 50px;
            max-width: 1200px;
            margin: 0 auto;
        }
        
        .welcome-card {
            background: #111;
            border: 1px solid #33ff33;
            border-radius: 20px;
            padding: 30px;
            margin-bottom: 30px;
            display: flex;
            justify-content: space-between;
            align-items: center;
        }
        
        .welcome-card h1 {
            color: #33ff33;
            margin-bottom: 10px;
        }
        
        .stats-grid {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(200px, 1fr));
            gap: 20px;
            margin-bottom: 40px;
        }
        
        .stat-box {
            background: #111;
            border: 1px solid #333;
            border-radius: 10px;
            padding: 20px;
            text-align: center;
        }
        
        .stat-number {
            font-size: 2rem;
            color: #33ff33;
            font-weight: bold;
        }
        
        .stat-label {
            color: #999;
        }
        
        .section-title {
            color: #33ff33;
            margin: 30px 0 20px;
            font-size: 1.5rem;
        }
        
        .items-grid {
            display: grid;
            grid-template-columns: repeat(auto-fill, minmax(300px, 1fr));
            gap: 20px;
        }
        
        .item-card {
            background: #111;
            border: 1px solid #333;
            border-radius: 10px;
            padding: 20px;
            transition: all 0.3s;
        }
        
        .item-card:hover {
            border-color: #33ff33;
            box-shadow: 0 0 20px rgba(51, 255, 51, 0.3);
        }
        
        .item-card h3 {
            color: #fff;
            margin-bottom: 10px;
        }
        
        .item-meta {
            color: #999;
            font-size: 0.9rem;
            margin-bottom: 15px;
        }
        
        .btn-download {
            display: inline-block;
            background: #33ff33;
            color: #000;
            padding: 10px 20px;
            border-radius: 5px;
            text-decoration: none;
            font-weight: bold;
            transition: all 0.3s;
        }
        
        .btn-download:hover {
            background: #2be02b;
            box-shadow: 0 0 15px #33ff33;
        }
        
        .btn-outline {
            display: inline-block;
            border: 1px solid #33ff33;
            color: #33ff33;
            padding: 8px 16px;
            border-radius: 5px;
            text-decoration: none;
            transition: all 0.3s;
        }
        
        .btn-outline:hover {
            background: #33ff33;
            color: #000;
        }
    </style>
</head>
<body>
    <?php include '../includes/header.php'; ?>
    
    <div class="dashboard">
        <div class="welcome-card">
            <div>
                <h1>Olá, <?= sanitize($currentUser['nome']) ?>! 👋</h1>
                <p>Bem-vindo ao seu painel de downloads</p>
            </div>
            <a href="perfil.php" class="btn-outline">Editar Perfil</a>
        </div>
        
        <div class="stats-grid">
            <div class="stat-box">
                <div class="stat-number"><?= count($compras) ?></div>
                <div class="stat-label">Addons Comprados</div>
            </div>
            <div class="stat-box">
                <div class="stat-number">0</div>
                <div class="stat-label">Downloads Hoje</div>
            </div>
            <div class="stat-box">
                <div class="stat-number">R$ 0,00</div>
                <div class="stat-label">Total Gasto</div>
            </div>
        </div>
        
        <?php if (count($compras) > 0): ?>
            <h2 class="section-title">📦 Seus Addons</h2>
            <div class="items-grid">
                <?php foreach ($compras as $addon): ?>
                <div class="item-card">
                    <h3><?= sanitize($addon['nome']) ?></h3>
                    <div class="item-meta">
                        Comprado em: <?= date('d/m/Y', strtotime($addon['data_compra'])) ?>
                    </div>
                    <a href="download.php?id=<?= $addon['id'] ?>" class="btn-download">
                        <i class="fas fa-download"></i> Baixar
                    </a>
                </div>
                <?php endforeach; ?>
            </div>
        <?php endif; ?>
        
        <h2 class="section-title">🔥 Addons Gratuitos Populares</h2>
        <div class="items-grid">
            <?php foreach ($gratuitos as $addon): ?>
            <div class="item-card">
                <h3><?= sanitize($addon['nome']) ?></h3>
                <div class="item-meta">
                    <?= $addon['downloads'] ?> downloads
                </div>
                <a href="download.php?id=<?= $addon['id'] ?>" class="btn-download">
                    <i class="fas fa-download"></i> Baixar Grátis
                </a>
            </div>
            <?php endforeach; ?>
        </div>
    </div>
    
    <?php include '../includes/footer.php'; ?>
</body>
</html>
