<?php
$page_title = "仪表盘";
$page_subtitle = "nodeseek / hostloc 监控总览 · 站点开关";
$stats = ["total"=>0,"hostloc"=>"1","nodeseek"=>"1"];
try {
    $total = (int)db()->query("SELECT COUNT(*) FROM keywords")->fetchColumn();
    $stats["total"] = $total;
    $stmt = db()->prepare("SELECT value FROM settings WHERE key=?");
    $stmt->execute(["hostloc_enabled"]);
    $h = $stmt->fetchColumn();
    $stmt->execute(["nodeseek_enabled"]);
    $n = $stmt->fetchColumn();
    if ($h!==false) $stats["hostloc"]=$h; else $stats["hostloc"]="1";
    if ($n!==false) $stats["nodeseek"]=$n; else $stats["nodeseek"]="1";
} catch(Throwable $e){}
?>
<div class="stat-grid">
  <div class="stat-card">
    <div class="stat-head"><span class="stat-icon">🔍</span><span class="stat-label">关键词规则</span></div>
    <div class="stat-value"><?= number_format($stats["total"]) ?></div>
    <div class="stat-foot">共 <?= $stats["total"] ?> 条 · <a href="?p=keywords" style="color:var(--accent)">去管理 →</a></div>
  </div>
  <div class="stat-card">
    <div class="stat-head"><span class="stat-icon">🌐</span><span class="stat-label">Nodeseek</span></div>
    <div class="stat-value" style="font-size:var(--t-lg)"><?= $stats["nodeseek"]==="1" ? "✅ 运行中" : "⏸ 已暂停" ?></div>
    <div class="stat-foot">每 2 分钟 · nodeseek.com/categories/trade</div>
  </div>
  <div class="stat-card">
    <div class="stat-head"><span class="stat-icon">💬</span><span class="stat-label">Hostloc</span></div>
    <div class="stat-value" style="font-size:var(--t-lg)"><?= $stats["hostloc"]==="1" ? "✅ 运行中" : "⏸ 已暂停" ?></div>
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
      <label class="switch"><input type="checkbox" id="sw-nodeseek" <?= $stats["nodeseek"]==="1"?"checked":"" ?>><span class="switch-slider"></span></label>
      <span class="tag" id="tag-nodeseek" style="margin-left:4px"><?= $stats["nodeseek"]==="1" ? "开启" : "关闭" ?></span>
    </label>
    <label style="display:flex;align-items:center;gap:12px;cursor:pointer">
      <span style="min-width:90px;font-weight:500">Hostloc</span>
      <label class="switch"><input type="checkbox" id="sw-hostloc" <?= $stats["hostloc"]==="1"?"checked":"" ?>><span class="switch-slider"></span></label>
      <span class="tag" id="tag-hostloc" style="margin-left:4px"><?= $stats["hostloc"]==="1" ? "开启" : "关闭" ?></span>
    </label>
  </div>
</div>

<div class="card" style="margin-top:var(--s-4)">
  <div class="card-head">
    <div><div class="card-title">最近运行</div><div class="card-sub">最新 10 次 · 运行时间 + 通知状态，点击展开详情</div></div>
    <button class="btn btn-ghost btn-sm" onclick="loadRuns()">刷新</button>
  </div>
  <div class="card-body">
    <div id="runs-list" style="font-size:13px">加载中…</div>
  </div>
</div>

<script>
async function loadRuns(){
  const box=document.getElementById("runs-list");
  box.textContent="加载中…";
  try{
    const r=await App.api({action:"runtime_runs",limit:10});
    if(!r.runs.length){box.textContent="暂无运行记录";return;}
    box.innerHTML=r.runs.map((run,i)=>{
      const dot=run.ok
        ? '<span class="dot" style="background:#10B981"></span>'
        : '<span class="dot" style="background:#EF4444"></span>';
      const badge=!run.ok
        ? '<span class="tag tag-red">异常</span>'
        : run.notified>0
          ? '<span class="tag tag-orange">🔔 '+run.notified+' 条通知</span>'
          : '<span class="tag tag-gray">无通知</span>';
      const titles=(run.titles||[]).map(t=>'<div style="color:var(--text-2);font-size:12px">🔔 '+App.esc(t)+'</div>').join("");
      return '<div style="border-bottom:1px solid var(--bg-tint);padding:8px 0;cursor:pointer" onclick="toggleRun('+i+')">'
        + '<div style="display:flex;align-items:center;gap:8px">'+dot
        + '<span style="font-family:monospace">'+App.esc(run.time)+'</span>'+badge
        + '<span style="margin-left:auto;color:var(--text-2);font-size:12px">详情 ▾</span></div>'
        + titles
        + '<pre id="run-detail-'+i+'" style="display:none;font-family:monospace;font-size:12px;background:var(--bg);padding:12px;border-radius:var(--r-sm);max-height:240px;overflow:auto;white-space:pre-wrap;margin-top:8px">'+App.esc(run.detail)+'</pre>'
        + '</div>';
    }).join("");
  }catch(e){box.textContent="加载失败："+(e.message||"请求失败");}
}
function toggleRun(i){
  const el=document.getElementById("run-detail-"+i);
  if(el) el.style.display=el.style.display==="none"?"block":"none";
}
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
window.addEventListener("load",()=>{loadSettings();loadRuns();});
loadRuns();
</script>
