# Deploy do Finly no Hostinger — Handoff

Objetivo: corrigir a **tela branca em `/app`** e aplicar o **painel admin seguro**.
Hospedagem: Hostinger (hPanel → File Manager). Domínio: `getfinly.com.br`.

> ⚠️ Tudo deve ser feito **dentro do document root do site getfinly.com.br** — a pasta
> que contém `index.html` (landing), `app/` e `api/`. Abra o File Manager via
> **Websites → getfinly.com.br** para cair na pasta certa.

## Arquivos (download direto do GitHub)

Repo: `joaoomartinssilva2007-lab/finly` (branch `main`).

| Arquivo | URL raw |
|---|---|
| Pacote do app corrigido | https://github.com/joaoomartinssilva2007-lab/finly/raw/main/app-corrigido.zip |
| admin.php (corrigido) | https://raw.githubusercontent.com/joaoomartinssilva2007-lab/finly/main/api/admin.php |
| codes.json | https://raw.githubusercontent.com/joaoomartinssilva2007-lab/finly/main/api/data/codes.json |
| validate.php (inalterado) | https://raw.githubusercontent.com/joaoomartinssilva2007-lab/finly/main/api/validate.php |

## Tarefa 1 — Corrigir tela branca do /app

Causa: uploads repetidos empilharam builds e aninharam pastas (`app/assets/assets`).
O `index.html` referencia `/app/assets/index-r_foOKkf.js` + `index-BqTO-DPR.css`, que
não estavam em `app/assets/` → o `.htaccess` devolvia HTML no lugar do JS → tela branca.

Passos:
1. No document root, **APAGUE a pasta `app/` inteira** (está bagunçada).
2. Faça upload de **`app-corrigido.zip`** no document root.
3. **Extraia** o zip (cria a `app/` limpa: `index.html`, `assets/index-r_foOKkf.js`,
   `assets/index-BqTO-DPR.css`, `favicon.svg`, `icons.svg`).
4. Apague o `app-corrigido.zip` do servidor depois de extrair.

Resultado esperado: a `/app` no servidor tem SÓ um build, plano, sem aninhamento.

## Tarefa 2 — Aplicar admin seguro

1. Substitua **`api/admin.php`** pelo arquivo do GitHub (corrige bypass de senha; lê o
   segredo de `admin_secret.php`).
1b. **Suba `api/admin_secret.php`** (o segredo da senha — NÃO está no GitHub por segurança;
   é fornecido à parte). Sem ele, o admin retorna erro 500 ("Admin não configurado").
   Como é `.php`, o servidor executa e não expõe o conteúdo via HTTP.
2. `api/data/codes.json` no repo é só um template vazio `{}`. No servidor, deixe o
   `codes.json` existente (ou crie `{}` se não existir). ⚠️ NÃO sobrescreva o `codes.json`
   do servidor com o do repo se já houver códigos gerados — isso apagaria as licenças.
3. `api/validate.php` não mudou (pode pular).

### Gerar o acesso do dono (pós-deploy)
Não há código pré-definido no repositório (por segurança). Depois de subir o admin:
1. Acesse `getfinly.com.br/api/admin.php` e entre.
2. Selecione plano **Vitalício** e clique **Gerar** → copie o código gerado.
3. Use esse código no app pra destravar (ele trava no 1º dispositivo).

## Verificação (após subir)

1. `https://getfinly.com.br/app/` → abre o app (não fica em branco). Ctrl+F5 p/ limpar cache.
2. No app, use o código vitalício gerado no admin → destrava.
3. `https://getfinly.com.br/api/admin.php` → senha errada recusa; senha correta entra.
4. (Opcional) DevTools → Network: `index-r_foOKkf.js` deve voltar com
   `Content-Type: application/javascript` (e não `text/html`).
