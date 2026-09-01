<!DOCTYPE html>
<html lang="zh-CN">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>关键词管理</title>
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link href="https://fonts.googleapis.com/css2?family=Noto+Sans+SC:wght@300;400;500;600&family=JetBrains+Mono:wght@400;500&display=swap" rel="stylesheet">
    <style>
        *, *::before, *::after { box-sizing: border-box; margin: 0; padding: 0; }
        :root {
            --bg: #f4f6fb;
            --surface: #ffffff;
            --surface2: #f0f2f8;
            --border: #dde1ef;
            --border-focus: #7b97f5;
            --accent: #4a6ef5;
            --accent-light: rgba(74,110,245,0.09);
            --accent-hover: #3558e0;
            --danger: #e5383b;
            --danger-light: rgba(229,56,59,0.08);
            --danger-border: rgba(229,56,59,0.3);
            --text: #1e2235;
            --text-muted: #8891b0;
            --text-sub: #5a6380;
            --green: #2da44e;
            --shadow-sm: 0 1px 3px rgba(30,34,53,0.07), 0 1px 2px rgba(30,34,53,0.04);
            --shadow-md: 0 4px 16px rgba(30,34,53,0.1), 0 2px 6px rgba(30,34,53,0.06);
            --shadow-lg: 0 16px 48px rgba(30,34,53,0.14);
            --radius: 12px;
            --radius-sm: 8px;
            --radius-xs: 5px;
        }
        body {
            background: var(--bg);
            color: var(--text);
            font-family: 'Noto Sans SC', sans-serif;
            min-height: 100vh;
            padding: 44px 20px 60px;
        }
        .page { max-width: 980px; margin: 0 auto; }

        /* Header */
        .header { display: flex; align-items: flex-start; justify-content: space-between; margin-bottom: 28px; gap: 16px; flex-wrap: wrap; }
        .header-left h1 { font-size: 1.5rem; font-weight: 600; letter-spacing: -0.02em; color: var(--text); display: flex; align-items: center; gap: 10px; }
        .live-dot { width: 8px; height: 8px; background: var(--accent); border-radius: 50%; box-shadow: 0 0 0 0 rgba(74,110,245,0.4); animation: ripple 2s ease-in-out infinite; display:inline-block; }
        @keyframes ripple { 0%{box-shadow:0 0 0 0 rgba(74,110,245,0.4)} 70%{box-shadow:0 0 0 7px rgba(74,110,245,0)} 100%{box-shadow:0 0 0 0 rgba(74,110,245,0)} }
        .subtitle { margin-top: 6px; font-size: 0.82rem; color: var(--text-muted); line-height: 1.65; }
        .subtitle code { font-family: 'JetBrains Mono', monospace; font-size: 0.76rem; background: var(--accent-light); color: var(--accent); padding: 1px 6px; border-radius: 4px; }
        .stats-badge { background: var(--surface); border: 1px solid var(--border); border-radius: var(--radius); padding: 12px 22px; text-align: center; box-shadow: var(--shadow-sm); min-width: 88px; }
        .stats-badge .count { font-size: 2rem; font-weight: 600; color: var(--accent); font-family: 'JetBrains Mono', monospace; line-height: 1; }
        .stats-badge .lbl { font-size: 0.7rem; color: var(--text-muted); margin-top: 4px; letter-spacing: 0.04em; }

        /* Add panel */
        .add-panel { background: var(--surface); border: 1px solid var(--border); border-radius: var(--radius); padding: 16px 18px; margin-bottom: 16px; display: flex; gap: 10px; align-items: center; box-shadow: var(--shadow-sm); }
        .add-panel .input-wrap { flex: 1; position: relative; }
        .add-panel .input-wrap::before { content: '+'; position: absolute; left: 13px; top: 50%; transform: translateY(-50%); color: var(--text-muted); font-size: 1.15rem; pointer-events: none; }
        .add-panel input { width: 100%; background: var(--surface2); border: 1.5px solid var(--border); color: var(--text); padding: 9px 13px 9px 32px; border-radius: var(--radius-sm); font-size: 0.88rem; font-family: 'JetBrains Mono', monospace; outline: none; transition: border-color 0.18s, box-shadow 0.18s; }
        .add-panel input:focus { border-color: var(--border-focus); box-shadow: 0 0 0 3px rgba(74,110,245,0.12); background: #fff; }
        .add-panel input::placeholder { font-family: 'Noto Sans SC', sans-serif; font-size: 0.83rem; color: var(--text-muted); }
        .btn-add { background: var(--accent); color: #fff; border: none; padding: 9px 22px; border-radius: var(--radius-sm); font-size: 0.87rem; font-weight: 500; cursor: pointer; font-family: 'Noto Sans SC', sans-serif; transition: background 0.18s, transform 0.1s, box-shadow 0.18s; box-shadow: 0 2px 8px rgba(74,110,245,0.3); white-space: nowrap; }
        .btn-add:hover { background: var(--accent-hover); box-shadow: 0 4px 14px rgba(74,110,245,0.35); }
        .btn-add:active { transform: scale(0.97); }

        /* Toolbar */
        .toolbar { display: flex; align-items: center; gap: 12px; margin-bottom: 14px; }
        .search-wrap { flex: 1; position: relative; }
        .search-wrap svg { position: absolute; left: 11px; top: 50%; transform: translateY(-50%); color: var(--text-muted); pointer-events: none; }
        .search-wrap input { width: 100%; background: var(--surface); border: 1.5px solid var(--border); color: var(--text); padding: 8px 13px 8px 34px; border-radius: var(--radius-sm); font-size: 0.84rem; outline: none; font-family: 'Noto Sans SC', sans-serif; transition: border-color 0.18s, box-shadow 0.18s; box-shadow: var(--shadow-sm); }
        .search-wrap input:focus { border-color: var(--border-focus); box-shadow: 0 0 0 3px rgba(74,110,245,0.1); }
        .search-wrap input::placeholder { color: var(--text-muted); }
        .section-label { font-size: 0.7rem; color: var(--text-muted); text-transform: uppercase; letter-spacing: 0.1em; font-weight: 500; align-self: center; white-space: nowrap; }

        /* Keywords grid */
        .keywords-grid { display: grid; grid-template-columns: repeat(auto-fill, minmax(210px, 1fr)); gap: 8px; }
        .keyword-card { background: var(--surface); border: 1.5px solid var(--border); border-radius: var(--radius-sm); padding: 10px 12px; display: flex; align-items: center; justify-content: space-between; gap: 8px; box-shadow: var(--shadow-sm); transition: border-color 0.15s, box-shadow 0.15s, transform 0.1s; animation: fadeUp 0.22s ease both; }
        @keyframes fadeUp { from{opacity:0;transform:translateY(5px)} to{opacity:1;transform:translateY(0)} }
        .keyword-card:hover { border-color: #c5cce8; box-shadow: var(--shadow-md); transform: translateY(-1px); }
        .kw-parts { display: flex; flex-wrap: wrap; gap: 4px; flex: 1; overflow: hidden; }
        .kw-tag { background: var(--accent-light); color: var(--accent); padding: 2px 8px; border-radius: var(--radius-xs); font-size: 0.74rem; font-family: 'JetBrains Mono', monospace; white-space: nowrap; font-weight: 500; }
        .btn-del { flex-shrink: 0; background: transparent; border: 1.5px solid transparent; color: #c0c7de; width: 26px; height: 26px; border-radius: var(--radius-xs); cursor: pointer; display: flex; align-items: center; justify-content: center; transition: background 0.15s, color 0.15s, border-color 0.15s; }
        .btn-del:hover { background: var(--danger-light); color: var(--danger); border-color: var(--danger-border); }
        .btn-del svg { pointer-events: none; }
        .empty { grid-column: 1/-1; text-align: center; padding: 64px 20px; color: var(--text-muted); }
        .empty .empty-icon { font-size: 2.2rem; margin-bottom: 12px; opacity: 0.35; }
        .empty p { font-size: 0.87rem; }

        /* Popup */
        .popup-overlay { position: fixed; inset: 0; background: rgba(20,24,48,0.32); backdrop-filter: blur(3px); z-index: 100; display: flex; align-items: center; justify-content: center; opacity: 0; pointer-events: none; transition: opacity 0.2s; }
        .popup-overlay.visible { opacity: 1; pointer-events: all; }
        .popup { background: var(--surface); border: 1.5px solid var(--border); border-radius: var(--radius); padding: 28px 28px 22px; max-width: 360px; width: 90%; box-shadow: var(--shadow-lg); transform: scale(0.94) translateY(8px); transition: transform 0.22s cubic-bezier(.34,1.36,.64,1), opacity 0.18s; opacity: 0; }
        .popup-overlay.visible .popup { transform: scale(1) translateY(0); opacity: 1; }
        .popup-icon { width: 42px; height: 42px; background: var(--danger-light); border-radius: 50%; display: flex; align-items: center; justify-content: center; margin-bottom: 14px; color: var(--danger); }
        .popup h3 { font-size: 1rem; font-weight: 600; color: var(--text); margin-bottom: 8px; }
        .popup p { font-size: 0.84rem; color: var(--text-sub); line-height: 1.6; }
        .popup .kw-preview { display: inline-flex; flex-wrap: wrap; gap: 4px; margin: 10px 0 22px; }
        .popup .kw-preview .kw-tag { background: var(--danger-light); color: var(--danger); }
        .popup-actions { display: flex; gap: 10px; justify-content: flex-end; }
        .btn-cancel { background: var(--surface2); color: var(--text-sub); border: 1.5px solid var(--border); padding: 8px 18px; border-radius: var(--radius-sm); font-size: 0.86rem; cursor: pointer; font-family: 'Noto Sans SC', sans-serif; transition: background 0.15s; }
        .btn-cancel:hover { background: #e6e9f4; }
        .btn-confirm-del { background: var(--danger); color: #fff; border: none; padding: 8px 20px; border-radius: var(--radius-sm); font-size: 0.86rem; font-weight: 500; cursor: pointer; font-family: 'Noto Sans SC', sans-serif; transition: background 0.15s, box-shadow 0.15s; box-shadow: 0 2px 8px rgba(229,56,59,0.25); }
        .btn-confirm-del:hover { background: #c92a2d; box-shadow: 0 4px 14px rgba(229,56,59,0.35); }

        /* Toast */
        .toast { position: fixed; bottom: 28px; right: 28px; background: var(--surface); border: 1.5px solid var(--border); border-radius: var(--radius-sm); padding: 10px 18px; font-size: 0.83rem; color: var(--text); z-index: 999; display: flex; align-items: center; gap: 8px; box-shadow: var(--shadow-md); animation: toastIn 0.22s ease; transition: opacity 0.28s; }
        @keyframes toastIn { from{transform:translateY(8px);opacity:0} to{transform:translateY(0);opacity:1} }
        .toast.success { border-color: rgba(45,164,78,0.35); }
        .toast.success .t-icon { color: var(--green); }
        .toast.danger { border-color: rgba(229,56,59,0.3); }
        .toast.danger .t-icon { color: var(--danger); }

        @media (max-width: 540px) {
            .keywords-grid { grid-template-columns: 1fr 1fr; }
            .header { flex-direction: column; }
            .popup { padding: 22px 18px 18px; }
        }
    </style>
</head>
<body>
<div class="page">

    <div class="header">
        <div class="header-left">
            <h1><span class="live-dot"></span>关键词管理</h1>
            <p class="subtitle">
                空格分隔多个词组，关键词不分顺序 &nbsp;·&nbsp;
                同时监听 <code>hostloc</code> 和 <code>nodeseek</code>
            </p>
        </div>
        <div class="stats-badge">
            <div class="count">
                <?php
                $file = 'nodeseek.txt';
                $lines = [];
                if (file_exists($file)) {
                    $lines = array_filter(array_map('trim', file($file, FILE_IGNORE_NEW_LINES)), 'strlen');
                    usort($lines, function($a, $b) { return strnatcasecmp($a, $b); });
                    $lines = array_values($lines);
                }
                echo count($lines);
                ?>
            </div>
            <div class="lbl">条规则</div>
        </div>
    </div>

    <div class="add-panel">
        <div class="input-wrap">
            <input type="text" id="new-text" placeholder="添加新关键词，多词用空格分隔…" autocomplete="off">
        </div>
        <button class="btn-add" onclick="addText()">添加</button>
    </div>

    <div class="toolbar">
        <div class="search-wrap">
            <svg width="15" height="15" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><circle cx="11" cy="11" r="8"/><line x1="21" y1="21" x2="16.65" y2="16.65"/></svg>
            <input type="text" id="search-input" placeholder="搜索关键词…" oninput="filterKeywords()" autocomplete="off">
        </div>
        <span class="section-label">已排序 A → Z</span>
    </div>

    <div class="keywords-grid" id="text-list">
        <?php if (empty($lines)): ?>
            <div class="empty">
                <div class="empty-icon">🔖</div>
                <p>暂无关键词，添加第一条规则吧</p>
            </div>
        <?php else:
            foreach ($lines as $i => $line):
                $parts = array_values(array_filter(explode(' ', $line), 'strlen'));
                $safeText = htmlspecialchars($line, ENT_QUOTES);
                $kwLower  = strtolower(htmlspecialchars($line));
                $delay    = min($i * 0.025, 0.4);
                echo '<div class="keyword-card" data-kw="'.$kwLower.'" style="animation-delay:'.$delay.'s">';
                echo '<div class="kw-parts">';
                foreach ($parts as $part) {
                    echo '<span class="kw-tag">'.htmlspecialchars($part).'</span>';
                }
                echo '</div>';
                echo '<button class="btn-del" onclick="confirmDelete(\''.addslashes($safeText).'\')" title="删除">';
                echo '<svg width="13" height="13" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.2" stroke-linecap="round"><line x1="18" y1="6" x2="6" y2="18"/><line x1="6" y1="6" x2="18" y2="18"/></svg>';
                echo '</button>';
                echo '</div>';
            endforeach;
        endif; ?>
    </div>
</div>

<!-- Delete confirmation popup -->
<div class="popup-overlay" id="popup-overlay" onclick="overlayClick(event)">
    <div class="popup">
        <div class="popup-icon">
            <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                <polyline points="3 6 5 6 21 6"/><path d="M19 6l-1 14a2 2 0 01-2 2H8a2 2 0 01-2-2L5 6"/>
                <path d="M10 11v6M14 11v6"/><path d="M9 6V4h6v2"/>
            </svg>
        </div>
        <h3>删除确认</h3>
        <p>确定要删除以下关键词规则吗？此操作不可撤销。</p>
        <div class="kw-preview" id="popup-kw-preview"></div>
        <div class="popup-actions">
            <button class="btn-cancel" onclick="closePopup()">取消</button>
            <button class="btn-confirm-del" id="popup-confirm-btn">删除</button>
        </div>
    </div>
</div>

<script>
function confirmDelete(text) {
    var parts = text.split(' ').filter(function(p){ return p.trim() !== ''; });
    document.getElementById('popup-kw-preview').innerHTML = parts.map(function(p){
        return '<span class="kw-tag">' + escHtml(p) + '</span>';
    }).join('');
    document.getElementById('popup-confirm-btn').onclick = function(){ executeDelete(text); };
    document.getElementById('popup-overlay').classList.add('visible');
}
function overlayClick(e) {
    if (e.target === document.getElementById('popup-overlay')) closePopup();
}
function closePopup() {
    document.getElementById('popup-overlay').classList.remove('visible');
}
function executeDelete(text) {
    closePopup();
    var xhr = new XMLHttpRequest();
    xhr.open("POST", "delete.php", true);
    xhr.setRequestHeader("Content-Type", "application/x-www-form-urlencoded");
    xhr.onreadystatechange = function() {
        if (xhr.readyState === 4 && xhr.status === 200) {
            showToast('已删除规则', 'danger');
            setTimeout(function(){ location.reload(); }, 680);
        }
    };
    xhr.send("text=" + encodeURIComponent(text));
}
function addText() {
    var newText = document.getElementById("new-text").value.trim();
    if (newText === "") { showToast('请输入关键词', 'danger'); return; }
    var xhr = new XMLHttpRequest();
    xhr.open("POST", "add.php", true);
    xhr.setRequestHeader("Content-Type", "application/x-www-form-urlencoded");
    xhr.onreadystatechange = function() {
        if (xhr.readyState === 4 && xhr.status === 200) {
            showToast('添加成功');
            setTimeout(function(){ location.reload(); }, 680);
        }
    };
    xhr.send("text=" + encodeURIComponent(newText));
}
function filterKeywords() {
    var q = document.getElementById('search-input').value.toLowerCase();
    document.querySelectorAll('.keyword-card').forEach(function(card) {
        card.style.display = card.dataset.kw.includes(q) ? '' : 'none';
    });
}
function showToast(msg, type) {
    type = type || 'success';
    var icon = type === 'success'
        ? '<svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round"><polyline points="20 6 9 17 4 12"/></svg>'
        : '<svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round"><line x1="18" y1="6" x2="6" y2="18"/><line x1="6" y1="6" x2="18" y2="18"/></svg>';
    var t = document.createElement('div');
    t.className = 'toast ' + type;
    t.innerHTML = '<span class="t-icon">' + icon + '</span>' + msg;
    document.body.appendChild(t);
    setTimeout(function(){ t.style.opacity='0'; setTimeout(function(){ t.remove(); },300); }, 2300);
}
function escHtml(s) {
    return s.replace(/&/g,'&amp;').replace(/</g,'&lt;').replace(/>/g,'&gt;').replace(/"/g,'&quot;');
}
document.addEventListener('keydown', function(e){
    if (e.key === 'Escape') closePopup();
});
document.getElementById('new-text').addEventListener('keydown', function(e){
    if (e.key === 'Enter') addText();
});
</script>
</body>
</html>
