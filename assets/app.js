/* ============================================================
   PHP+SQLite 模板 — 通用 JS
   用法（任何页面都可以直接用）：
     App.api({action:'xxx', ...}).then(d => ...)
     App.toast('保存成功')
     App.toast('失败', 'error')
     App.confirm('确定删除？', () => { ... })
   ============================================================ */
const App = {
  /** 调 api.php，统一处理 ok/error */
  async api(params) {
    if (params instanceof FormData) params = Object.fromEntries(params);
    const url = 'api.php?' + new URLSearchParams(params);
    const r = await fetch(url, { credentials: 'same-origin' });
    if (r.status === 401) { location.href = 'login.php'; throw new Error('未登录'); }
    const j = await r.json();
    if (!j.ok) {
      this.toast(j.error || '请求失败', 'error');
      throw new Error(j.error);
    }
    return j.data;
  },

  /** 顶部右侧 toast */
  toast(msg, type = 'success', duration = 2500) {
    const host = document.getElementById('toast-host');
    if (!host) { alert(msg); return; }
    const el = document.createElement('div');
    el.className = 'toast toast-' + type;
    el.textContent = msg;
    host.appendChild(el);
    setTimeout(() => {
      el.style.animation = 'slideOut .2s ease forwards';
      setTimeout(() => el.remove(), 200);
    }, duration);
  },

  /** 二次确认 */
  confirm(msg, onYes) {
    if (window.confirm(msg)) onYes();
  },

  /** HTML 转义 */
  esc(s) {
    return String(s ?? '').replace(/[&<>"']/g, c => ({
      '&':'&amp;', '<':'&lt;', '>':'&gt;', '"':'&quot;', "'":'&#39;'
    })[c]);
  },

  /** 时间戳 → 友好显示 */
  ts(t) {
    return new Date(t * 1000).toLocaleString('zh-CN', { hour12: false });
  },

  /** 数字千分位 */
  num(n) {
    return Number(n).toLocaleString('en-US');
  },
};

// Esc 关闭所有 dialog
document.addEventListener('keydown', e => {
  if (e.key === 'Escape') document.querySelectorAll('dialog[open]').forEach(d => d.close());
});

// 点击 dialog 背景关闭
document.addEventListener('click', e => {
  if (e.target.tagName === 'DIALOG' && e.target.open) {
    const r = e.target.getBoundingClientRect();
    if (e.clientX < r.left || e.clientX > r.right || e.clientY < r.top || e.clientY > r.bottom) {
      e.target.close();
    }
  }
});
