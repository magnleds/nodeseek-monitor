<?php
/**
 * 入口：未登录跳 login，已登录按 ?p=xxx 加载页面
 */
define('APP_ENTRY', true);
require __DIR__ . '/db.php';

if (!token_check()) {
    if (!empty($_GET['action']) || (($_GET['p'] ?? '') !== 'login')) {
        header('Location: login.php');
        exit;
    }
}

// 退出
if (($_GET['action'] ?? '') === 'logout') {
    token_destroy();
    header('Location: login.php');
    exit;
}

$page = preg_replace('/[^a-z_]/', '', $_GET['p'] ?? 'dashboard');
$file = __DIR__ . "/pages/$page.php";
if (!is_file($file)) $file = __DIR__ . '/pages/dashboard.php';

$page_title    = ['dashboard'=>'仪表盘', 'keywords'=>'关键词管理', 'settings'=>'设置'][$page] ?? '页面';
$page_subtitle = $page_subtitle ?? '';
$ACTIVE        = $page;

include __DIR__ . '/includes/header.php';
include $file;
include __DIR__ . '/includes/footer.php';
