// ============================================================
// automata_matrix.js - VERSÃO COM OPENROUTER
// ============================================================

const axios = require('axios');
const { chamarOpenRouter } = require('./ia/openrouter');

const HF_TOKEN = process.env.HF_TOKEN;
const GH_TOKEN = process.env.GH_TOKEN;
const REPO = "ncmine286715/BLOG";

// ============================================================
// PERSONALIDADE DO NICK
// ============================================================
const PERSONALIDADE = {
    nome: "Nick",
    setup: "Ryzen 7 5800X, RTX 5060 Ti, 64GB RAM, Corsair 4000D, Samsung 990 Pro",
    historia: "Em 2018, uma fonte genérica de 500W explodiu e levou meu primeiro servidor junto. Perdi 6 meses de trabalho.",
    email: "ncmine75@gmail.com"
};

// ============================================================
// FUNÇÃO PARA PUBLICAR NO GITHUB
// ============================================================
async function publicarNoGithub(arquivo, conteudoBase64, mensagem) {
    try {
        const url = `https://api.github.com/repos/${REPO}/contents/${arquivo}`;
        
        let sha = null;
        try {
            const res = await axios.get(url, {
                headers: { Authorization: `token ${GH_TOKEN}` }
            });
            sha = res.data.sha;
        } catch (e) {}
        
        await axios.put(url, {
            message: mensagem,
            content: conteudoBase64,
            sha: sha
        }, {
            headers: { Authorization: `token ${GH_TOKEN}` }
        });
        
        console.log(`✅ Publicado: ${arquivo}`);
        return true;
        
    } catch (error) {
        console.error(`❌ Erro ao publicar:`, error.message);
        return false;
    }
}

// ============================================================
// FUNÇÃO PRINCIPAL
// ============================================================
async function main() {
    console.log("\n🚀 INICIANDO AUTOMATA NICK COM OPENROUTER\n");
    
    const temas = [
        "Placas de vídeo RTX 5090 vs RX 7900 XTX",
        "Processadores Ryzen 9 9950X3D vs Intel Core Ultra 9 285K",
        "Memória RAM DDR5 vs CAMM2 para servidores 24/7",
        "SSDs NVMe Gen5 vs Gen4",
        "Fontes Titanium vs Platinum"
    ];
    
    const tema = temas[Math.floor(Math.random() * temas.length)];
    console.log(`🎯 Tema: ${tema}\n`);
    
    // Gera introdução
    const promptIntro = `Você é o Nick, analista de hardware. Escreva uma introdução sobre "${tema}".
    Use primeira pessoa. Mencione que em 2018 uma fonte explodiu e destruiu seu servidor.
    Mínimo 300 palavras.`;
    
    const introducao = await chamarOpenRouter(promptIntro);
    
    // Gera seções
    const secoes = [];
    const titulos = ["Especificações", "Testes Práticos", "Custo-Benefício"];
    
    for (const titulo of titulos) {
        const prompt = `Escreva a seção "${titulo}" sobre "${tema}". Inclua uma tabela HTML.`;
        const secao = await chamarOpenRouter(prompt);
        secoes.push({ titulo, conteudo: secao });
    }
    
    // Gera conclusão
    const promptConclusao = `Escreva uma conclusão sobre "${tema}". Dê sua recomendação pessoal.`;
    const conclusao = await chamarOpenRouter(promptConclusao);
    
    // Monta HTML
    const data = new Date().toLocaleDateString('pt-BR');
    const slug = tema.toLowerCase().replace(/[^a-z0-9]/g, '-') + '.html';
    
    const html = `<!DOCTYPE html>
<html>
<head>
    <title>Nick Analista: ${tema}</title>
    <meta charset="UTF-8">
    <style>
        body { font-family: Arial, sans-serif; max-width: 900px; margin: 0 auto; padding: 20px; background: #0d1117; color: #f0f6fc; }
        h1 { color: #3fb950; }
        .author { background: #21262d; padding: 20px; border-radius: 10px; margin: 20px 0; }
    </style>
</head>
<body>
    <h1>${tema}</h1>
    <div class="author">
        <strong>Por Nick</strong> · ${data}<br>
        ${PERSONALIDADE.setup}
    </div>
    
    <h2>Introdução</h2>
    <p>${introducao.replace(/\n/g, '<br>')}</p>
    
    ${secoes.map(s => `
        <h2>${s.titulo}</h2>
        <p>${s.conteudo.replace(/\n/g, '<br>')}</p>
    `).join('')}
    
    <h2>Conclusão</h2>
    <p>${conclusao.replace(/\n/g, '<br>')}</p>
    
    <hr>
    <p>📧 ${PERSONALIDADE.email}</p>
</body>
</html>`;
    
    await publicarNoGithub(slug, Buffer.from(html).toString('base64'), `🤖 Artigo: ${tema}`);
    console.log(`\n✅ Artigo publicado: ${slug}`);
}

main();
