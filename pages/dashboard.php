<?php
\$page_title = "仪表盘";
\$page_subtitle = "nodeseek / hostloc 监控总览 · 站点开关";
\$stats = ["total"=>0,"hostloc"=>"1","nodeseek"=>"1"];
try {
    \$total = (int)db()->query("SELECT COUNT(*) FROM keywords")->fetchColumn();
    \$stats["total"] = \$total;
    \$stmt = db()->prepare("SELECT value FROM settings WHERE key=?");
    \$stmt->execute(["hostloc_enabled"]);
    \$h = \$stmt->fetchColumn();
    \$stmt->execute(["nodeseek_enabled"]);
    \$n = \$stmt->fetchColumn();
    if (\$h!==false) \$stats["hostloc"]=\$h; else \$stats["hostloc"]="1";
    if (\$n!==false) \$stats["nodeseek"]=\$n; else \$stats["nodeseek"]="1";
} catch(Throwable \$e){}
?>
<div class="stat-grid">
  <div class="stat-card">
    <div class="stat-head"><span class="stat-icon">🔍</span><span class="stat-label">关键词规则</span></div>
    <div class="stat-value"><?= number_format(\$stats["total"]) ?></div>
    <div class="stat-foot">共 <?= \$stats["total"] ?> 条 · <a href="?p=keywords" style="color:var(--accent)">去管理 →</a></div>
  </div>
  <div class="stat-card">
    <div class="stat-head"><span class="stat-icon">🌐</span><span class="stat-label">Nodeseek</span></div>
    <div class="stat-value" style="font-size:var(--t-lg)"><?= \$stats["nodeseek"]==="1" ? "✅ 运行中" : "⏸ 已暂停" ?></div>
    <div class="stat-foot">每 2 分钟 · nodeseek.com/categories/trade</div>
  </div>
  <div class="stat-card">
    <div class="stat-head"><span class="stat-icon">💬</span><span class="stat-label">Hostloc</span></div>
    <div class="stat-value" style="font-size:var(--t-lg)"><?= \$stats["hostloc"]==="1" ? "✅ 运行中" : "⏸ 已暂停" ?></div>
    <div class="stat-foot">每 2 分钟 · hostloc.com</div>
  </div>
  <div class="stat-card stat-accent">
    <div class="stat-head"><span class="stat-icon">⏱</span><span class="stat-label">Cron</span></div>
    <div class="stat-value" style="font-size:var(--t-sm)">*/2 * * * *</div>
    <div class="stat-foot">/home/www/ccc/hostloc 双脚本</div>
  </div>
</div>

<div class="card" style="margin-top:var(--s-4)">
  <div class="card-head">
    <div><div class="card-title">站点开关</div><div class="card-sub">关闭后对应爬虫将跳过执行，不再推送 Telegram</div></div>
  </div>
  <div class="card-body" style="display:flex;gap:var(--s-6);flex-wrap:wrap">
    <label style="display:flex;align-items:center;gap:12px;cursor:pointer">
      <span style="min-width:90px;font-weight:500">Nodeseek</span>
      <label class="switch"><input type="checkbox" id="sw-nodeseek" <?= \$stats["nodeseek"]==="1"?"checked":"" ?>><span class="switch-slider"></span></label>
      <span class="tag" id="tag-nodeseek" style="margin-left:4px"><?= \$stats["nodeseek"]==="1" ? "开启" : "关闭" ?></span>
    </label>
    <label style="display:flex;align-items:center;gap:12px;cursor:pointer">
      <span style="min-width:90px;font-weight:500">Hostloc</span>
      <label class="switch"><input type="checkbox" id="sw-hostloc" <?= \$stats["hostloc"]==="1"?"checked":"" ?>><span class="switch-slider"></span></label>
      <span class="tag" id="tag-hostloc" style="margin-left:4px"><?= \$stats["hostloc"]==="1" ? "开启" : "关闭" ?></span>
    </label>
  </div>
</div>

<div class="card" style="margin-top:var(--s-4)">
  <div class="card-head"><div class="card-title">最近运行日志</div><a class="btn btn-ghost btn-sm" href="?p=keywords">管理关键词</a></div>
  <div class="card-body">
    <div id="cron-log" style="font-family:monospace;font-size:12px;background:var(--bg);padding:12px;border-radius:var(--r-sm);max-height:240px;overflow:auto;white-space:pre-wrap">加载中…</div>
  </div>
</div>

<script>
async function loadSettings(){
  try{
    const r = await App.api({action:"get_settings"});
    document.getElementById("sw-nodeseek").checked = !!r.nodeseek_enabled;
    document.getElementById("sw-hostloc").checked = !!r.hostloc_enabled;
    document.getElementById("tag-nodeseek").textContent = r.nodeseek_enabled ? "开启" : "关闭";
    document.getElementById("tag-hostloc").textContent = r.hostloc_enabled ? "开启" : "关闭";
  }catch(e){}
}
async function toggle(site, on){
  try{
    const payload = {action:"update_settings"};
    payload[site+"_enabled"] = on ? 1 : 0;
    await App.api(payload);
    App.toast(site + (on ? " 已开启" : " 已暂停"), on ? "success" : "info");
    document.getElementById("tag-"+site).textContent = on ? "开启" : "关闭";
  }catch(e){ App.toast(e.message||"失败","error"); }
}
document.getElementById("sw-nodeseek").addEventListener("change", e=>toggle("nodeseek", e.target.checked));
document.getElementById("sw-hostloc").addEventListener("change", e=>toggle("hostloc", e.target.checked));
loadSettings();
</script>
