<?php
$page_title = "关键词管理";
$page_subtitle = "空格分隔多词组 · 不区分顺序 · 同时作用于已开启的站点";
?>
<div class="card">
  <div class="card-head keyword-toolbar">
    <div class="keyword-add">
      <input class="input" id="new-text" placeholder="+ 添加新关键词，多词用空格分隔…" aria-label="新关键词">
      <button class="btn btn-primary" onclick="doAdd()">添加</button>
    </div>
    <div class="keyword-search">
      <input class="input" id="q" placeholder="🔍 搜索关键词…" oninput="load(1)" aria-label="搜索关键词">
      <span class="tag tag-gray">已排序 A → Z</span>
    </div>
  </div>
  <div class="keyword-cloud" id="keyword-cloud"><div class="keyword-empty">加载中…</div></div>
  <div class="table-foot"><span id="page-info"></span><div id="pager"></div></div>
</div>

<dialog class="modal" id="delete-modal">
  <div class="card-head"><div class="card-title">确认删除</div><button type="button" class="icon-btn" onclick="this.closest(\"dialog\").close()">✕</button></div>
  <div class="card-body"><p class="muted">确定要删除这条关键词规则吗？删除后将立即同步到监控脚本。</p></div>
  <div class="card-foot keyword-modal-actions"><button type="button" class="btn btn-ghost" onclick="this.closest(\"dialog\").close()">取消</button><button type="button" class="btn btn-danger" onclick="del()">删除</button></div>
</dialog>

<script>
let curPage=1;
let pendingDeleteId=0;
async function load(p=1){
  curPage=p;
  const q=document.getElementById("q").value.trim();
  try{
    const r=await App.api({action:"list_keywords", q, page:p, size:50});
    const cloud=document.getElementById("keyword-cloud");
    document.getElementById("page-info").textContent = r.total ? "第 "+r.page+" 页 / 共 "+Math.ceil(r.total/r.size)+" 页" : "暂无关键词";
    if(!r.rows.length){ cloud.innerHTML="<div class=\"keyword-empty\">暂无匹配关键词</div>"; return; }
    cloud.innerHTML=r.rows.map(row=>{
      const text=App.esc(row.text);
      return "<span class=\"keyword-chip\"><span>"+text+"</span><button type=\"button\" class=\"keyword-remove\" aria-label=\"删除 "+text+"\" onclick=\"openDelete("+row.id+")\">×</button></span>";
    }).join("");
  }catch(e){ document.getElementById("keyword-cloud").innerHTML="<div class=\"keyword-empty\">加载失败："+App.esc(e.message)+"</div>"; }
}
async function doAdd(){
  const input=document.getElementById("new-text");
  const text=input.value.trim();
  if(!text) return;
  try{ await App.api({action:"add_keyword", text}); App.toast("已添加","success"); input.value=""; load(1); }
  catch(err){ App.toast(err.message,"error"); }
}
function openDelete(id){ pendingDeleteId=id; document.getElementById("delete-modal").showModal(); }
async function del(){
  const id=pendingDeleteId;
  document.getElementById("delete-modal").close();
  try{ await App.api({action:"delete_keyword", id}); App.toast("已删除","success"); load(curPage); }
  catch(err){ App.toast(err.message,"error"); }
}
document.getElementById("new-text").addEventListener("keydown", e=>{ if(e.key==="Enter") doAdd(); });
window.addEventListener("load",()=>load(1));
</script>
