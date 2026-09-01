<?php
/**
 * 数据库 + 认证
 * 任何页面 require 这一个文件就够了
 */

if (!defined('APP_ENTRY')) {
    http_response_code(403);
    exit('Forbidden');
}

$CONFIG = require __DIR__ . '/config.php';
if (!$CONFIG) {
    die('Missing config.php — copy config.example.php to config.php and fill in.');
}

// ====== PDO 单例 ======
function db(): PDO {
    global $CONFIG;
    static $pdo = null;
    if ($pdo) return $pdo;
    $path = $CONFIG['db_path'];
    $dir  = dirname($path);
    if (!is_dir($dir)) mkdir($dir, 0755, true);
    $pdo = new PDO('sqlite:' . $path);
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
    $pdo->exec('PRAGMA journal_mode=WAL;');
    $pdo->exec('PRAGMA foreign_keys=ON;');
    init_schema($pdo);
    return $pdo;
}

function init_schema(PDO $pdo): void {
    $pdo->exec("
        CREATE TABLE IF NOT EXISTS auth_tokens (
            id INTEGER PRIMARY KEY AUTOINCREMENT,
            token TEXT UNIQUE NOT NULL,
            username TEXT NOT NULL,
            expires_at INTEGER NOT NULL,
            created_at INTEGER NOT NULL
        );
        CREATE INDEX IF NOT EXISTS idx_token ON auth_tokens(token);

        -- 示例表：items（列表/筛选/搜索的 demo）
        CREATE TABLE IF NOT EXISTS items (
            id INTEGER PRIMARY KEY AUTOINCREMENT,
            name TEXT NOT NULL,
            category TEXT,
            status TEXT DEFAULT 'active',
            amount REAL DEFAULT 0,
            created_at INTEGER NOT NULL
        );

        CREATE TABLE IF NOT EXISTS keywords (
            id INTEGER PRIMARY KEY AUTOINCREMENT,
            text TEXT NOT NULL UNIQUE,
            created_at INTEGER NOT NULL
        );

        CREATE TABLE IF NOT EXISTS settings (
            key TEXT PRIMARY KEY,
            value TEXT NOT NULL
        );
    ");
}

// ====== Token 持久化登录 ======
function TOKEN_COOKIE(): string {
    global $CONFIG;
    return 'APP_AUTH_' . substr(md5($CONFIG['db_path']), 0, 8);
}

function token_check(): bool {
    $c = TOKEN_COOKIE();
    if (empty($_COOKIE[$c])) return false;
    $token = $_COOKIE[$c];
    $row = db()->prepare('SELECT id, expires_at FROM auth_tokens WHERE token=?');
    $row->execute([$token]);
    $r = $row->fetch(PDO::FETCH_ASSOC);
    if (!$r) return false;
    $now = time();
    $exp = (int)$r['expires_at'];
    if ($exp !== 0 && $exp < $now) {
        db()->prepare('DELETE FROM auth_tokens WHERE id=?')->execute([$r['id']]);
        setcookie(TOKEN_COOKIE(), '', time() - 3600, '/', '', false, true);
        return false;
    }
    return true;
}

function token_create(string $username): void {
    global $CONFIG;
    // 清理过期
    db()->prepare('DELETE FROM auth_tokens WHERE expires_at > 0 AND expires_at < ?')
        ->execute([time()]);
    $token = bin2hex(random_bytes(32));
    $now   = time();
    $life  = (int)$CONFIG['session_lifetime'];
    $exp   = $life > 0 ? $now + $life : 0;
    db()->prepare('INSERT INTO auth_tokens (token, username, expires_at, created_at) VALUES (?,?,?,?)')
        ->execute([$token, $username, $exp, $now]);
    setcookie(
        TOKEN_COOKIE(),
        $token,
        [
            'expires'  => $exp > 0 ? $exp : 0,
            'path'     => '/',
            'secure'   => false, // 走反代时由反代处理 https
            'httponly' => true,
            'samesite' => 'Lax',
        ]
    );
}

function token_destroy(): void {
    $c = TOKEN_COOKIE();
    if (!empty($_COOKIE[$c])) {
        db()->prepare('DELETE FROM auth_tokens WHERE token=?')->execute([$_COOKIE[$c]]);
        setcookie($c, '', time() - 3600, '/', '', false, true);
    }
}

function current_username(): string {
    $c = TOKEN_COOKIE();
    if (empty($_COOKIE[$c])) return '';
    $row = db()->prepare('SELECT username FROM auth_tokens WHERE token=?');
    $row->execute([$_COOKIE[$c]]);
    return $row->fetchColumn() ?: '';
}

// ====== 通用响应 ======
function json_out($data, int $code = 200): void {
    http_response_code($code);
    header('Content-Type: application/json; charset=utf-8');
    echo json_encode($data, JSON_UNESCAPED_UNICODE);
    exit;
}

function ok($data = null): void { json_out(['ok' => true, 'data' => $data]); }
function fail(string $msg, int $code = 400): void { json_out(['ok' => false, 'error' => $msg], $code); }

function input(string $key, $default = null) {
    $body = json_decode(file_get_contents('php://input'), true) ?: [];
    return $_REQUEST[$key] ?? $body[$key] ?? $default;
}
