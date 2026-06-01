<?php
session_start();
// Senha de admin guardada como SHA-256 + salt (nunca em texto puro no repositório).
$PASS_SALT = 'd679f4fb54851453699885510a6dd43e';
$PASS_HASH = '87ccbe15dcc1a01b236a7b350c40a292e545e6c460b30df160d4221650196953';

// --- Login (parênteses corrigidos + comparação em tempo constante) ---
if (isset($_POST['pw'])) {
    $try = hash('sha256', $PASS_SALT . (string)$_POST['pw']);
    if (hash_equals($PASS_HASH, $try)) { $_SESSION['ok'] = true; }
}
if (!($_SESSION['ok'] ?? false)) { ?>
<!DOCTYPE html><html lang="pt-BR"><head><meta charset="UTF-8"><title>Admin Finly</title>
<style>*{box-sizing:border-box;margin:0;padding:0}body{background:#0A0A12;color:#fff;font-family:Inter,sans-serif;display:flex;align-items:center;justify-content:center;min-height:100vh}form{background:#111120;border:1px solid rgba(255,255,255,.1);border-radius:16px;padding:40px;width:320px}h2{margin-bottom:24px;font-size:1.2rem}input{width:100%;padding:12px;border-radius:8px;border:1px solid rgba(255,255,255,.2);background:#1E1E36;color:#fff;font-size:14px;margin-bottom:16px}button{width:100%;padding:12px;border-radius:8px;background:#6C63FF;color:#fff;border:none;font-size:15px;font-weight:600;cursor:pointer}</style></head>
<body><form method="post"><h2>Admin Finly</h2><input type="password" name="pw" placeholder="Senha admin" autofocus><button>Entrar</button></form></body></html>
<?php exit; }

$f = __DIR__ . '/data/codes.json';
if (!file_exists($f)) { @mkdir(__DIR__.'/data',0755,true); file_put_contents($f,'{}'); }
$codes = json_decode(file_get_contents($f), true) ?: [];

$action = $_POST['action'] ?? '';

// Gerar codigos
$gerados = [];
if ($action === 'gen') {
    $plan = ($_POST['plan'] ?? '') === 'lifetime' ? 'lifetime' : 'monthly';
    $qty  = max(1, min(50, intval($_POST['qty'] ?? 1)));
    for ($i = 0; $i < $qty; $i++) {
        $c = 'FINLY-'.strtoupper(substr(bin2hex(random_bytes(3)),0,6)).'-'.strtoupper(substr(bin2hex(random_bytes(3)),0,6));
        $codes[$c] = ['plan'=>$plan,'created'=>date('Y-m-d H:i:s'),'device_id'=>null,'first_used'=>null];
        $gerados[] = $c;
    }
    file_put_contents($f, json_encode($codes, JSON_PRETTY_PRINT));
}

// Revogar codigo
if ($action === 'del') {
    unset($codes[$_POST['code'] ?? '']);
    file_put_contents($f, json_encode($codes, JSON_PRETTY_PRINT));
}

// Desvincular dispositivo
if ($action === 'unbind') {
    $code = $_POST['code'] ?? '';
    if (isset($codes[$code])) {
        $codes[$code]['device_id']  = null;
        $codes[$code]['first_used'] = null;
        file_put_contents($f, json_encode($codes, JSON_PRETTY_PRINT));
    }
}
?>
<!DOCTYPE html><html lang="pt-BR"><head><meta charset="UTF-8"><title>Admin Finly</title>
<style>
*{box-sizing:border-box;margin:0;padding:0}
body{background:#0A0A12;color:#fff;font-family:Inter,sans-serif;padding:40px}
h1{font-size:1.4rem;margin-bottom:32px;font-weight:800}
.card{background:#111120;border:1px solid rgba(255,255,255,.1);border-radius:16px;padding:28px;margin-bottom:24px}
h3{font-size:.95rem;font-weight:700;margin-bottom:20px;color:#A0A0C0}
select,input[type=number]{padding:10px 14px;border-radius:8px;border:1px solid rgba(255,255,255,.2);background:#1E1E36;color:#fff;font-size:14px;margin-right:8px}
.btn{padding:10px 20px;border-radius:8px;border:none;cursor:pointer;font-size:13px;font-weight:700}
.btn-p{background:#6C63FF;color:#fff}.btn-y{background:rgba(255,181,71,.2);color:#FFB547;border:1px solid rgba(255,181,71,.3)}.btn-r{background:rgba(255,87,87,.15);color:#FF5757;border:1px solid rgba(255,87,87,.3)}
table{width:100%;border-collapse:collapse}
th,td{text-align:left;padding:11px 12px;border-bottom:1px solid rgba(255,255,255,.06);font-size:13px}
th{color:#606080;font-size:11px;font-weight:700;text-transform:uppercase;letter-spacing:1px}
.code{font-family:monospace;background:#1E1E36;padding:3px 8px;border-radius:5px;font-size:12px}
.new-code{background:rgba(0,212,168,.1);border:1px solid rgba(0,212,168,.3);border-radius:8px;padding:10px 16px;margin-bottom:8px;font-family:monospace;font-size:15px;color:#00D4A8;display:flex;align-items:center;justify-content:space-between}
.badge{display:inline-block;padding:2px 8px;border-radius:100px;font-size:11px;font-weight:700}
.badge-m{background:rgba(0,212,168,.15);color:#00D4A8}
.badge-v{background:rgba(255,181,71,.15);color:#FFB547}
.badge-free{background:rgba(108,99,255,.15);color:#6C63FF}
.badge-used{background:rgba(255,87,87,.1);color:#FF5757}
</style></head>
<body>
<h1>⚡ Finly — Painel Admin</h1>

<?php if ($gerados): ?>
<div class="card" style="border-color:rgba(0,212,168,.3);">
  <h3 style="color:#00D4A8;margin-bottom:16px;">✓ <?=count($gerados)?> código(s) gerado(s)</h3>
  <?php foreach ($gerados as $c): ?>
  <div class="new-code">
    <span><?=$c?></span>
    <span style="font-size:11px;color:#606080;">clique para copiar</span>
  </div>
  <?php endforeach; ?>
</div>
<?php endif; ?>

<div class="card">
  <h3>Gerar novos códigos</h3>
  <form method="post" style="display:flex;align-items:center;gap:8px;flex-wrap:wrap;">
    <input type="hidden" name="action" value="gen">
    <select name="plan">
      <option value="monthly">Mensal (R$ 14,90)</option>
      <option value="lifetime">Vitalício (R$ 24,90)</option>
    </select>
    <input type="number" name="qty" value="1" min="1" max="50" style="width:80px;" placeholder="Qtd">
    <button type="submit" class="btn btn-p">Gerar</button>
  </form>
</div>

<div class="card">
  <h3>Códigos ativos — <?=count($codes)?> total</h3>
  <table>
    <tr><th>Código</th><th>Plano</th><th>Dispositivo</th><th>Primeiro uso</th><th>Ações</th></tr>
    <?php foreach ($codes as $code => $d): ?>
    <tr>
      <td><span class="code"><?=htmlspecialchars($code)?></span></td>
      <td><span class="badge badge-<?=$d['plan']==='monthly'?'m':'v'?>"><?=$d['plan']==='monthly'?'Mensal':'Vitalício'?></span></td>
      <td>
        <?php if ($d['device_id']): ?>
          <span class="badge badge-used">Em uso</span>
        <?php else: ?>
          <span class="badge badge-free">Livre</span>
        <?php endif; ?>
      </td>
      <td style="color:#606080;font-size:12px;"><?=$d['first_used']??'—'?></td>
      <td style="display:flex;gap:6px;flex-wrap:wrap;">
        <?php if ($d['device_id']): ?>
        <form method="post" style="display:inline;">
          <input type="hidden" name="action" value="unbind">
          <input type="hidden" name="code" value="<?=htmlspecialchars($code)?>">
          <button class="btn btn-y" onclick="return confirm('Desvincular dispositivo deste código?')">Desvincular</button>
        </form>
        <?php endif; ?>
        <form method="post" style="display:inline;">
          <input type="hidden" name="action" value="del">
          <input type="hidden" name="code" value="<?=htmlspecialchars($code)?>">
          <button class="btn btn-r" onclick="return confirm('Revogar este código permanentemente?')">Revogar</button>
        </form>
      </td>
    </tr>
    <?php endforeach; ?>
    <?php if (!$codes): ?>
    <tr><td colspan="5" style="text-align:center;color:#606080;padding:24px;">Nenhum código ainda</td></tr>
    <?php endif; ?>
  </table>
</div>
</body></html>
