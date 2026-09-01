<?php
/**
 * API 入口
 * 路由：?action=login / logout / list_items / create_item / ...
 * 所有需要登录的 action 第一行加 require_auth()
 * 登录密码用 password_verify 校验（config 的 users.password 存 hash）
 */
define('APP_ENTRY', true);
require __DIR__ . '/db.php';

$action = $_GET['action'] ?? '';

function require_auth() {
    if (!token_check()) fail('未登录', 401);
}

// ====== 登录 / 登出 ======
if ($action === 'login') {
    $u = input('username');
    $p = input('password');
    global $CONFIG;
    $ok = false;
    foreach ($CONFIG['users'] as $row) {
        if (hash_equals($row['username'], $u) && hash_equals($row['password'], $p)) {
            $ok = true; break;
        }
    }
    if (!$ok) fail('账号或密码错误', 401);
    token_create($u);
    ok(['username' => $u]);
}

if ($action === 'logout') {
    token_destroy();
    ok();
}

if ($action === 'me') {
    require_auth();
    ok(['username' => current_username()]);
}

// ====== 示例 CRUD：items ======
if ($action === 'list_items') {
    require_auth();
    $q     = trim((string)input('q', ''));
    $cat   = (string)input('category', '');
    $st    = (string)input('status', '');
    $page  = max(1, (int)input('page', 1));
    $size  = min(100, max(5, (int)input('size', 20)));
    $sort  = preg_replace('/[^a-z_]/', '', (string)input('sort', 'created_at'));
    $dir   = strtolower((string)input('dir', 'desc')) === 'asc' ? 'ASC' : 'DESC';

    $where = []; $args = [];
    if ($q !== '')  { $where[] = 'name LIKE ?'; $args[] = "%$q%"; }
    if ($cat !== '') { $where[] = 'category = ?'; $args[] = $cat; }
    if ($st !== '')  { $where[] = 'status = ?';   $args[] = $st; }
    $whereSql = $where ? ('WHERE ' . implode(' AND ', $where)) : '';

    $total = (int)db()->prepare("SELECT COUNT(*) FROM items $whereSql")
        ->execute($args) ?: 0;
    $stmt = db()->prepare("SELECT COUNT(*) FROM items $whereSql");
    $stmt->execute($args);
    $total = (int)$stmt->fetchColumn();

    $offset = ($page - 1) * $size;
    $stmt = db()->prepare("SELECT * FROM items $whereSql ORDER BY $sort $dir LIMIT $size OFFSET $offset");
    $stmt->execute($args);
    $rows = $stmt->fetchAll(PDO::FETCH_ASSOC);

    ok(['rows' => $rows, 'total' => $total, 'page' => $page, 'size' => $size]);
}

if ($action === 'create_item') {
    require_auth();
    $name = trim((string)input('name', ''));
    if ($name === '') fail('名称必填');
    $cat = (string)input('category', '');
    $amt = (float)input('amount', 0);
    $st  = (string)input('status', 'active');
    db()->prepare('INSERT INTO items (name, category, status, amount, created_at) VALUES (?,?,?,?,?)')
        ->execute([$name, $cat, $st, $amt, time()]);
    ok(['id' => db()->lastInsertId()]);
}

if ($action === 'delete_item') {
    require_auth();
    $id = (int)input('id', 0);
    db()->prepare('DELETE FROM items WHERE id=?')->execute([$id]);
    ok();
}

if ($action === 'stats') {
    require_auth();
    $total = (int)db()->query('SELECT COUNT(*) FROM items')->fetchColumn();
    $active = (int)db()->query("SELECT COUNT(*) FROM items WHERE status='active'")->fetchColumn();
    $sum = (float)db()->query('SELECT COALESCE(SUM(amount),0) FROM items')->fetchColumn();
    ok(['total' => $total, 'active' => $active, 'sum' => $sum]);
}


// ====== nodeseek-monitor: keywords ======
if ($action === "list_keywords") {
    require_auth();
    $q = trim((string)input("q",""));
    $page = max(1, (int)input("page",1));
    $size = min(100, max(5, (int)input("size", 20)));
    $where = ""; $args=[];
    if ($q !== "") { $where="WHERE text LIKE ?"; $args[]="%$q%"; }
    $stmt = db()->prepare("SELECT COUNT(*) FROM keywords $where");
    $stmt->execute($args);
    $total = (int)$stmt->fetchColumn();
    $offset = ($page-1)*$size;
    $stmt = db()->prepare("SELECT * FROM keywords $where ORDER BY id DESC LIMIT $size OFFSET $offset");
    $stmt->execute($args);
    $rows = $stmt->fetchAll(PDO::FETCH_ASSOC);
    ok(["rows"=>$rows,"total"=>$total,"page"=>$page,"size"=>$size]);
}
if ($action === "add_keyword") {
    require_auth();
    $text = trim((string)input("text",""));
    if ($text==="") fail("关键词不能为空");
    $stmt = db()->prepare("SELECT id FROM keywords WHERE text=?");
    $stmt->execute([$text]);
    if ($stmt->fetch()) fail("已存在相同规则");
    db()->prepare("INSERT INTO keywords (text, created_at) VALUES (?,?)")->execute([$text, time()]);
    sync_keywords_to_file();
    ok(["id"=>db()->lastInsertId()]);
}
if ($action === "delete_keyword") {
    require_auth();
    $id = (int)input("id",0);
    $text = (string)input("text","");
    if ($id) {
        db()->prepare("DELETE FROM keywords WHERE id=?")->execute([$id]);
    } elseif ($text!=="") {
        db()->prepare("DELETE FROM keywords WHERE text=?")->execute([$text]);
    } else fail("参数缺失");
    sync_keywords_to_file();
    ok();
}
if ($action === "import_nodeseek_txt") {
    require_auth();
    $file = __DIR__ . "/nodeseek.txt";
    if (!is_file($file)) fail("nodeseek.txt 不存在");
    $lines = file($file, FILE_IGNORE_NEW_LINES|FILE_SKIP_EMPTY_LINES);
    $count=0;
    foreach ($lines as $line) {
        $line=trim($line);
        if ($line==="") continue;
        try { db()->prepare("INSERT OR IGNORE INTO keywords (text, created_at) VALUES (?,?)")->execute([$line, time()]); $count++; } catch(Throwable $e){}
    }
    sync_keywords_to_file();
    ok(["imported"=>$count]);
}
function get_setting(string $k, string $def="1"): string {
    $stmt = db()->prepare("SELECT value FROM settings WHERE key=?");
    $stmt->execute([$k]);
    $v = $stmt->fetchColumn();
    return $v!==false ? (string)$v : $def;
}
function set_setting(string $k, string $v): void {
    db()->prepare("INSERT INTO settings (key,value) VALUES (?,?) ON CONFLICT(key) DO UPDATE SET value=excluded.value")->execute([$k,$v]);
    sync_settings_to_json();
}
function sync_settings_to_json(): void {
    $keys=["hostloc_enabled","nodeseek_enabled"];
    $data=[];
    foreach($keys as $k) $data[$k]=get_setting($k,"1")==="1";
    @file_put_contents(__DIR__."/data/settings.json", json_encode($data, JSON_UNESCAPED_UNICODE|JSON_PRETTY_PRINT));
}
function sync_keywords_to_file(): void {
    $rows = db()->query("SELECT text FROM keywords ORDER BY id ASC")->fetchAll(PDO::FETCH_COLUMN);
    @file_put_contents(__DIR__."/nodeseek.txt", implode(PHP_EOL, $rows));
    sync_settings_to_json();
}
if ($action === "get_settings") {
    require_auth();
    $h = get_setting("hostloc_enabled","1");
    $n = get_setting("nodeseek_enabled","1");
    sync_settings_to_json();
    ok(["hostloc_enabled"=>$h==="1","nodeseek_enabled"=>$n==="1"]);
}
if ($action === "update_settings") {
    require_auth();
    $h = input("hostloc_enabled", null);
    $n = input("nodeseek_enabled", null);
    if ($h!==null) set_setting("hostloc_enabled", $h ? "1":"0");
    if ($n!==null) set_setting("nodeseek_enabled", $n ? "1":"0");
    ok(["hostloc_enabled"=>get_setting("hostloc_enabled","1")==="1","nodeseek_enabled"=>get_setting("nodeseek_enabled","1")==="1"]);
}


fail('Unknown action: ' . $action, 404);
