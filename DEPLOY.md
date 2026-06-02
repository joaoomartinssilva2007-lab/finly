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

1. Substitua **`api/admin.php`** pelo arquivo do GitHub (corrige bypass de senha; senha
   agora é hash SHA-256 + salt).
2. Substitua **`api/data/codes.json`** (no servidor está `{}` vazio).
   ⚠️ NÃO sobrescreva o `codes.json` depois que houver códigos de clientes gerados pelo
   admin no ar — isso apagaria as licenças.
3. `api/validate.php` não mudou (pode pular).

## Verificação (após subir)

1. `https://getfinly.com.br/app/` → abre o app (não fica em branco). Ctrl+F5 p/ limpar cache.
2. No app, código `FINLY-7C25B5-1134F5` → destrava (vitalício do dono).
3. `https://getfinly.com.br/api/admin.php` → senha errada recusa; senha correta entra.
4. (Opcional) DevTools → Network: `index-r_foOKkf.js` deve voltar com
   `Content-Type: application/javascript` (e não `text/html`).
