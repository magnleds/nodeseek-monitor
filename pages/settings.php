<?php $page_title = '设置'; $page_subtitle = '本项目自身的偏好配置'; ?>

<div class="card" style="max-width:720px">
  <div class="card-head"><div class="card-title">界面偏好</div></div>
  <div class="card-body">
    <div class="grid-2">
      <label class="field">
        <span class="field-label">主题</span>
        <select class="select"><option>跟随系统</option><option>浅色</option><option>深色</option></select>
      </label>
      <label class="field">
        <span class="field-label">每页条数</span>
        <input class="input" type="number" value="20">
      </label>
    </div>
  </div>
  <div class="card-foot">
    <button class="btn btn-ghost">取消</button>
    <button class="btn btn-primary" onclick="App.toast('已保存','success')">保存</button>
  </div>
</div>

<div class="card" style="max-width:720px">
  <div class="card-head"><div class="card-title">危险操作</div></div>
  <div class="card-body">
    <p class="muted">以下操作不可恢复，请谨慎。</p>
  </div>
  <div class="card-foot">
    <button class="btn btn-danger" onclick="App.confirm('确定清空所有数据？', ()=>App.toast('已清空','info'))">清空所有数据</button>
  </div>
</div>
