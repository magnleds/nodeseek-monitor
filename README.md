# nodeseek-monitor

hostloc / nodeseek 关键词监听 · 面板 + 开关 + 登录

- 线上：https://apps.998365.xyz/apps/nodeseek-de28967c-effa-11ef-b55d-325096b39f47/
- 仓库：https://github.com/magnleds/nodeseek-monitor
- 管理：manageproject #111

## 功能
- 关键词管理（空格多词无序匹配）→ 写入 nodeseek.txt 供爬虫读取
- 站点开关：仪表盘可单独启停 nodeseek / hostloc，写入 data/settings.json，爬虫启动时检查
- 登录：基于 php-sqlite-template 的 token 持久化（.env 存 APP_ADMIN_USER / APP_ADMIN_PASS hash）

## 目录
- pages/keywords.php — 关键词 CRUD
- pages/dashboard.php — 统计 + 双开关
- api.php — list_keywords / add_keyword / delete_keyword / get_settings / update_settings
- data/app.db — SQLite（keywords / settings / auth_tokens）
- data/settings.json — 供 Python 快速读取的开关缓存
- nodeseek.txt — 明文规则，每行一条，爬虫直接读取
- ../hostloc/check_nodeseek.py / check_hostloc.py — 每 2 分钟 cron，均先读 settings.json 决定是否跳过

## 登录
- 用户名：admin
- 密码：见服务器 /root/nodeseek_admin_raw.txt（仅此一次明文）
- 修改：用 /usr/local/bin/env-edit.sh 改 .env 的 APP_ADMIN_PASS（需 password_hash 值）

## 运维
- Python 开关逻辑在 /home/www/ccc/hostloc/check_*.py 顶部（读 settings.json）
- Cron：宝塔 */2 * * * *  cd /home/www/ccc/hostloc && python3 check_nodeseek.py && python3 check_hostloc.py
