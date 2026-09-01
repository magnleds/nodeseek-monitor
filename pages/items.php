<?php $page_title = '数据列表'; $page_subtitle = '展示筛选、搜索、排序、分页、批量操作的样板'; ?>

<div class="toolbar">
  <div class="toolbar-left">
    <div class="filter-pills">
      <button class="pill active" data-cat="">全部 <span class="pill-count"><?= $stats['total'] ?? 0 ?></span></button>
      <button class="pill" data-cat="A">类目 A <span class="pill-count">0</span></button>
      <button class="pill" data-cat="B">类目 B <span class="pill-count">0</span></button>
      <button class="pill" data-cat="C">类目 C <span class="pill-count">0</span></button>
    </div>
  </div>
  <div class="toolbar-right">
    <button class="btn btn-ghost" onclick="App.toast('已触发批量导入', 'info')"><span>↑</span> 批量导入</button>
    <button class="btn btn-primary" onclick="openItemModal()"><span>＋</span> 新建</button>
  </div>
</div>

<div class="toolbar toolbar-second">
  <input class="input" id="q" placeholder="搜索名称…">
  <select class="select" id="filter-status">
    <option value="">所有状态</option>
    <option value="active">活跃</option>
    <option value="inactive">停用</option>
  </select>
  <button class="btn btn-ghost" onclick="reloadItems()">刷新</button>
</div>

<div class="card flush">
  <div class="table-wrap">
    <table class="data-table">
      <thead>
        <tr>
          <th class="col-check"><input type="checkbox" id="check-all"></th>
          <th class="sortable" data-sort="id">#</th>
          <th class="sortable" data-sort="name">名称</th>
          <th class="sortable" data-sort="category">类目</th>
          <th class="sortable" data-sort="status">状态</th>
          <th class="sortable num" data-sort="amount">金额</th>
          <th class="sortable" data-sort="created_at">创建时间</th>
          <th class="col-action">操作</th>
        </tr>
      </thead>
      <tbody id="rows"></tbody>
    </table>
  </div>
  <div class="table-foot">
    <div class="foot-left"><span id="sel-count">0</span> 条已选 / 共 <span id="total-count">0</span> 条</div>
    <div class="foot-right">
      <label class="muted">每页
        <select class="select select-sm" id="size">
          <option>10</option><option selected>20</option><option>50</option>
        </select>
      </label>
      <button class="icon-btn" onclick="gotoPage(1)" aria-label="首页">⇤</button>
      <button class="icon-btn" onclick="gotoPage(state.page-1)" aria-label="上一页">‹</button>
      <span class="muted">第 <span id="cur-page">1</span> 页</span>
      <button class="icon-btn" onclick="gotoPage(state.page+1)" aria-label="下一页">›</button>
    </div>
  </div>
</div>

<!-- 新建/编辑弹窗 -->
<dialog id="item-modal" class="modal">
  <form id="item-form" method="dialog" onsubmit="return submitItem(event)">
    <header class="modal-head">
      <div>
        <h2 class="modal-title" id="modal-title">新建</h2>
        <p class="modal-sub" id="modal-sub">添加一条新记录</p>
      </div>
      <button type="button" class="icon-btn" onclick="document.getElementById('item-modal').close()">✕</button>
    </header>
    <div class="modal-body">
      <div class="grid-2">
        <label class="field">
          <span class="field-label">名称 <em>*</em></span>
          <input class="input" name="name" required>
        </label>
        <label class="field">
          <span class="field-label">类目</span>
          <select class="select" name="category">
            <option value="A">类目 A</option>
            <option value="B">类目 B</option>
            <option value="C">类目 C</option>
          </select>
        </label>
        <label class="field">
          <span class="field-label">状态</span>
          <select class="select" name="status">
            <option value="active">活跃</option>
            <option value="inactive">停用</option>
          </select>
        </label>
        <label class="field">
          <span class="field-label">金额</span>
          <input class="input" name="amount" type="number" step="0.01" value="0">
        </label>
      </div>
    </div>
    <footer class="modal-foot">
      <button type="button" class="btn btn-ghost" onclick="document.getElementById('item-modal').close()">取消</button>
      <button type="submit" class="btn btn-primary">保存</button>
    </footer>
  </form>
</dialog>

<script>
const state = { q:'', cat:'', status:'', page:1, size:20, sort:'created_at', dir:'desc' };

function reloadItems() {
  const params = new URLSearchParams({ action:'list_items', ...state });
  App.api(params).then(({rows,total}) => {
    document.getElementById('total-count').textContent = total;
    document.getElementById('cur-page').textContent = state.page;
    document.getElementById('rows').innerHTML = rows.map(rowHtml).join('') || '<tr><td colspan="8" class="empty">暂无数据</td></tr>';
  });
}

function rowHtml(r) {
  const date = new Date(r.created_at*1000).toLocaleString('zh-CN',{hour12:false});
  return `<tr>
    <td class="col-check"><input type="checkbox" class="row-check"></td>
    <td class="num">#${r.id}</td>
    <td><strong>${App.esc(r.name)}</strong></td>
    <td><span class="tag tag-blue">${App.esc(r.category||'-')}</span></td>
    <td>${r.status==='active' ? '<span class="tag tag-green">活跃</span>' : '<span class="tag tag-gray">停用</span>'}</td>
    <td class="num">¥${(+r.amount).toFixed(2)}</td>
    <td class="muted">${date}</td>
    <td class="col-action">
      <button class="icon-btn" title="编辑" onclick="openItemModal(${r.id})">✎</button>
      <button class="icon-btn" title="删除" onclick="delItem(${r.id})">🗑</button>
    </td>
  </tr>`;
}

function openItemModal(id) {
  document.getElementById('item-form').reset();
  document.getElementById('modal-title').textContent = id ? '编辑' : '新建';
  document.getElementById('item-modal').showModal();
}

function submitItem(e) {
  e.preventDefault();
  const f = e.target;
  const data = Object.fromEntries(new FormData(f));
  App.api({action:'create_item', ...data}).then(() => {
    document.getElementById('item-modal').close();
    App.toast('保存成功');
    reloadItems();
  });
}

function delItem(id) {
  App.confirm('确定删除该记录？', () => {
    App.api({action:'delete_item', id}).then(() => { App.toast('已删除'); reloadItems(); });
  });
}

function gotoPage(p) { state.page = p; reloadItems(); }

// 事件绑定
document.getElementById('q').oninput = e => { state.q = e.target.value; state.page=1; reloadItems(); };
document.getElementById('filter-status').onchange = e => { state.status = e.target.value; state.page=1; reloadItems(); };
document.getElementById('size').onchange = e => { state.size = +e.target.value; state.page=1; reloadItems(); };
document.querySelectorAll('.filter-pills .pill').forEach(el => {
  el.onclick = () => {
    document.querySelectorAll('.filter-pills .pill').forEach(x => x.classList.remove('active'));
    el.classList.add('active');
    state.cat = el.dataset.cat; state.page = 1; reloadItems();
  };
});
document.querySelectorAll('th.sortable').forEach(th => {
  th.onclick = () => {
    const s = th.dataset.sort;
    if (state.sort === s) state.dir = state.dir==='asc'?'desc':'asc';
    else { state.sort = s; state.dir = 'asc'; }
    reloadItems();
  };
});

// 首次进入自动塞 5 条 demo 数据（仅当表为空）
App.api({action:'list_items', size:1}).then(({total}) => {
  if (total === 0) {
    ['Alpha 项目','Beta 客户','Gamma 订单','Delta 任务','Epsilon 报告'].forEach((n,i) =>
      App.api({action:'create_item', name:n, category:['A','B','C','A','B'][i], status:i%2?'active':'inactive', amount: (i+1)*99.5})
    ).then(()=>reloadItems());
  } else reloadItems();
});
</script>
