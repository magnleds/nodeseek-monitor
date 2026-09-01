<?php
\$page_title = "关键词管理";
\$page_subtitle = "空格分隔多词组 · 不区分顺序 · 同时作用于已开启的站点";
?>
<div class="card">
  <div class="card-head" style="flex-wrap:wrap;gap:12px">
    <div><div class="card-title">规则列表</div><div class="card-sub">关键词不分顺序，一行一条，命中即推送 Telegram</div></div>
    <button class="btn btn-primary" onclick="openAdd()">+ 添加</button>
  </div>
  <div class="toolbar">
    <div class="toolbar-left" style="flex:1">
      <input class="input" id="q" placeholder="搜索关键词…" oninput="load(1)" style="max-width:320px">
    </div>
    <div class="toolbar-right"><span class="tag" id="total-tag">0 条</span></div>
  </div>
  <div class="card-body" style="padding:0">
    <table class="data-table">
      <thead><tr><th>关键词</th><th>创建时间</th><th class="col-action">操作</th></tr></thead>
      <tbody id="tbody"><tr><td colspan=3 style="text-align:center;padding:24px">加载中…</td></tr></tbody>
    </table>
  </div>
  <div class="table-foot"><span id="page-info"></span><div id="pager"></div></div>
</div>

<dialog class="modal" id="add-modal">
  <form method="dialog" onsubmit="doAdd(event)">
    <div class="card-head"><div class="card-title">添加关键词</div><button type="button" class="icon-btn" onclick="this.closest('dialog').close()">✕</button></div>
    <div class="card-body">
      <div class="field"><label class="field-label">关键词（空格分隔多词）</label><input class="input" id="new-text" placeholder="如：bage 盐城  或  斯巴达 2.5" required></div>
      <div class="alert alert-info" style="margin-top:12px">一行中的所有词必须同时出现在标题中才会命中</div>
    </div>
    <div class="card-foot" style="justify-content:flex-end;gap:8px;display:flex"><button type="button" class="btn btn-ghost" onclick="this.closest('dialog').close()">取消</button><button class="btn btn-primary" type="submit">添加</button></div>
  </form>
</dialog>

<script>
let curPage=1;
async function load(p=1){
  curPage=p;
  const q=document.getElementById("q").value.trim();
  const r=await App.api({action:"list_keywords", q, page:p, size:20});
  document.getElementById("total-tag").textContent = r.total+" 条";
  document.getElementById("page-info").textContent = "第 "+r.page+" 页 / 共 "+Math.ceil(r.total/r.size)+" 页";
  const tb=document.getElementById("tbody");
  if(!r.rows.length){ tb.innerHTML="<tr><td colspan=3 style='text-align:center;padding:32px;color:var(--text-muted)'>暂无数据</td></tr>"; return; }
  tb.innerHTML = r.rows.map(row=>{
    const parts=row.text.split(" ").filter(Boolean).map(p=>"<span class=\'tag tag-blue\'>"+esc(p)+"</span>").join("");
    const d=new Date(row.created_at*1000).toLocaleString();
    return "<tr><td><div style='display:flex;gap:6px;flex-wrap:wrap'>"+parts+"</div></td><td style='color:var(--text-muted);font-size:13px'>"+d+"</td><td><button class=\'btn btn-ghost btn-sm\' onclick=\'del("+row.id+")\'>删除</button></td></tr>";
  }).join("");
}
function openAdd(){ document.getElementById("new-text").value=""; document.getElementById("add-modal").showModal(); }
async function doAdd(e){
  e.preventDefault();
  const text=document.getElementById("new-text").value.trim();
  if(!text) return;
  try{ await App.api({action:"add_keyword", text}); App.toast("已添加","success"); e.target.closest("dialog").close(); load(1);}catch(err){ App.toast(err.message,"error"); }
}
async function del(id){
  if(!confirm("确定删除？")) return;
  try{ await App.api({action:"delete_keyword", id}); App.toast("已删除","success"); load(curPage);}catch(err){ App.toast(err.message,"error"); }
}
function esc(s){ return s.replace(/&/g,"&amp;").replace(/</g,"&lt;").replace(/>/g,"&gt;"); }
load(1);
</script>
