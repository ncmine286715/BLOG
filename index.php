<?php
// index.php - Página principal com sistema de contas
require_once 'includes/functions.php';

// Obter usuário atual se logado
$currentUser = getCurrentUser();

// Buscar addons do banco
$db = db();
$addons = $db->query("SELECT * FROM addons WHERE ativo = 1 ORDER BY data_criacao DESC LIMIT 8")->fetchAll();
?>
<!DOCTYPE html>
<html lang="pt-BR">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>MineAddonsNews - Addons Minecraft</title>
    <meta name="description" content="Baixe addons de Minecraft rápido e seguro. Addons gratuitos via TeraBox e packs premium com pagamento por Pix/QR Code.">
    
    <!-- Open Graph / SEO (mesmo do template anterior) -->
    <meta property="og:type" content="website">
    <meta property="og:url" content="https://mineaddonsnews.online/">
    <meta property="og:title" content="MineAddonsNews - O melhor site de addons Minecraft">
    <meta property="og:description" content="Baixe addons de Minecraft rápido e seguro.">
    
    <!-- CSS e Fonts -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0-beta3/css/all.min.css">
    <link rel="stylesheet" href="style.css">
    
    <!-- Schema.org (mesmo do anterior) -->
    <script type="application/ld+json">
    {
        "@context": "https://schema.org",
        "@graph": [
            {
                "@type": "Website",
                "name": "MineAddonsNews",
                "url": "https://mineaddonsnews.online",
                "author": { "@type": "Person", "name": "NC Mine" }
            },
            {
                "@type": "Organization",
                "name": "MineAddonsNews",
                "url": "https://mineaddonsnews.online"
            }
        ]
    }
    </script>
</head>
<body>
    <!-- Header com menu dinâmico -->
    <header class="header">
        <div class="header-container">
            <div class="logo">
                <a href="/">
                    <span class="logo-text">Mine<span class="neon">Addons</span>News</span>
                </a>
            </div>
            
            <nav class="navbar">
                <ul class="nav-menu">
                    <li><a href="/" class="active">Home</a></li>
                    <li><a href="#addons">Addons</a></li>
                    <li><a href="#packs">Packs</a></li>
                    <li><a href="#noticias">Notícias</a></li>
                    
                    <?php if ($currentUser): ?>
                        <li><a href="pages/dashboard.php">Olá, <?= sanitize($currentUser['nome']) ?></a></li>
                        <li><a href="pages/logout.php">Sair</a></li>
                    <?php else: ?>
                        <li><a href="pages/login.php">Entrar</a></li>
                        <li><a href="pages/register.php" class="btn-outline">Cadastrar</a></li>
                    <?php endif; ?>
                </ul>
                <div class="hamburger">
                    <span class="bar"></span>
                    <span class="bar"></span>
                    <span class="bar"></span>
                </div>
            </nav>
        </div>
    </header>

    <main>
        <!-- Hero Section (igual ao template) -->
        <section class="hero">
            <div class="hero-content">
                <h1>Baixe Addons de Minecraft <span class="neon-text">Rápido e Seguro</span></h1>
                <p>Addons exclusivos, packs de textura e mods com um clique. Download via TeraBox ou compre com Pix/QR Code.</p>
                <a href="#addons" class="btn-cta">Confira Agora <i class="fas fa-arrow-down"></i></a>
            </div>
        </section>

        <!-- Seção Addons (agora dinâmica do banco) -->
        <section id="addons" class="addons-section">
            <div class="container">
                <h2>🔥 Addons em Destaque</h2>
                <p class="section-sub">Escolha seu addon, baixe grátis ou adquira a versão premium via Pix.</p>

                <div class="cards-grid">
                    <?php foreach ($addons as $addon): ?>
                    <div class="card">
                        <div class="card-image">
                            <img src="<?= sanitize($addon['imagem_url'] ?: 'data:image/svg+xml,%3Csvg...') ?>" 
                                 alt="<?= sanitize($addon['nome']) ?>" loading="lazy">
                        </div>
                        <div class="card-content">
                            <h3><?= sanitize($addon['nome']) ?></h3>
                            <p class="desc"><?= sanitize($addon['descricao']) ?></p>
                            
                            <?php if ($addon['tipo'] == 'gratis'): ?>
                                <!-- Addon Grátis -->
                                <?php if ($currentUser): ?>
                                    <a href="pages/download.php?id=<?= $addon['id'] ?>" class="btn-download">
                                        <i class="fas fa-download"></i> Download Grátis
                                    </a>
                                <?php else: ?>
                                    <a href="pages/login.php" class="btn-download">
                                        <i class="fas fa-lock"></i> Faça login para baixar
                                    </a>
                                <?php endif; ?>
                            
                            <?php else: ?>
                                <!-- Addon Pago -->
                                <div class="card-actions">
                                    <?php if ($currentUser): ?>
                                        <?php
                                        // Verificar se já comprou
                                        $stmt = $db->prepare("
                                            SELECT id FROM compras 
                                            WHERE usuario_id = ? AND addon_id = ? AND status_pagamento = 'aprovado'
                                        ");
                                        $stmt->execute([$currentUser['id'], $addon['id']]);
                                        $jaTem = $stmt->fetch();
                                        ?>
                                        
                                        <?php if ($jaTem): ?>
                                            <a href="pages/download.php?id=<?= $addon['id'] ?>" class="btn-download">
                                                <i class="fas fa-download"></i> Já possui - Baixar
                                            </a>
                                        <?php else: ?>
                                            <a href="pages/checkout.php?id=<?= $addon['id'] ?>" class="btn-buy">
                                                <i class="fas fa-qrcode"></i> Comprar R$<?= $addon['preco'] ?> | Pix
                                            </a>
                                        <?php endif; ?>
                                    
                                    <?php else: ?>
                                        <a href="pages/login.php" class="btn-buy">
                                            <i class="fas fa-lock"></i> Faça login para comprar
                                        </a>
                                    <?php endif; ?>
                                </div>
                            <?php endif; ?>
                        </div>
                    </div>
                    <?php endforeach; ?>
                </div>
            </div>
        </section>

        <!-- Espaço para anúncios -->
        <div class="ads-placeholder">
            <p>🔲 Espaço reservado para publicidade 🔲</p>
        </div>
    </main>

    <!-- Footer -->
    <?php include 'includes/footer.php'; ?>

    <script>
        // Menu mobile
        const hamburger = document.querySelector('.hamburger');
        const navMenu = document.querySelector('.nav-menu');
        
        if (hamburger) {
            hamburger.addEventListener('click', () => {
                hamburger.classList.toggle('active');
                navMenu.classList.toggle('active');
            });
        }
    </script>
</body>
</html>
