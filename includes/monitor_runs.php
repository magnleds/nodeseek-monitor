<?php
// 最近运行解析：把 data/monitor.log 按 "[时间] monitor start" 切成单次运行，
// 每条只提炼：运行时间 + 是否正常 + 有无通知，详情原文点击才看。
function parse_monitor_runs(string $path, int $limit = 10): array {
    if (!is_file($path)) return [];
    $limit = min(20, max(1, $limit));
    // 只读尾部，避免 10MB+ 大文件吃内存（单次运行约 10-20KB，256KB 足够覆盖最近 20 次）
    $tail = 256 * 1024;
    $size = filesize($path);
    if ($size === false || $size === 0) return [];
    $fh = fopen($path, 'rb');
    if (!$fh) return [];
    $off = $size > $tail ? $size - $tail : 0;
    fseek($fh, $off);
    if ($off > 0) fgets($fh); // 丢掉首个可能不完整的行
    $text = stream_get_contents($fh);
    fclose($fh);
    if (!is_string($text) || $text === '') return [];
    $text = @iconv('UTF-8', 'UTF-8//IGNORE', $text);
    $parts = preg_split('/(?=^\[\d{4}-\d{2}-\d{2} \d{2}:\d{2}:\d{2}\] monitor start)/m', (string)$text);
    $runs = [];
    foreach ((array)$parts as $p) {
        $p = trim((string)$p);
        if ($p === '' || !preg_match('/^\[(\d{4}-\d{2}-\d{2} \d{2}:\d{2}:\d{2})\] monitor start/', $p, $m)) continue;
        $fail = preg_match('/请求异常|获取页面失败|疑似被 Cloudflare|页面结构发生了变化|Telegram 发送失败|Telegram 推送失败|页面获取失败|未加载到任何关键词|正则无法匹配/u', $p) === 1;
        $sent = 0;
        if (preg_match_all('/Telegram 发送成功|Telegram 推送成功/u', $p, $mm)) $sent = count($mm[0]);
        // 通知标题：nodeseek 的"匹配成功（新）" + hostloc 命中去掉已推送跳过的
        $titles = [];
        if (preg_match_all('/匹配成功（新）:\s*(.+)/u', $p, $mm2)) {
            foreach ($mm2[1] as $t) { $t = trim($t); if ($t !== '') $titles[] = $t; }
        }
        if (preg_match_all('/命中关键词.*→\s*标题:\s*(.+)/u', $p, $mm3)) {
            $skipped = [];
            if (preg_match_all('/已推送过，跳过:\s*(.+)/u', $p, $mm4)) {
                foreach ($mm4[1] as $t) $skipped[trim($t)] = true;
            }
            foreach ($mm3[1] as $t) {
                $t = trim($t);
                if ($t !== '' && !isset($skipped[$t]) && !in_array($t, $titles, true)) $titles[] = $t;
            }
        }
        $matched_ns = null; $matched_hl = null;
        if (preg_match('/关键词过滤后匹配数量:\s*(\d+)/u', $p, $m3)) $matched_ns = (int)$m3[1];
        if (preg_match('/关键词匹配结果:\s*(\d+)\s*条/u', $p, $m4)) $matched_hl = (int)$m4[1];
        $runs[] = [
            'time' => $m[1],
            'ok' => !$fail,
            'notified' => $sent,
            'titles' => array_slice($titles, 0, 10),
            'matched_nodeseek' => $matched_ns,
            'matched_hostloc' => $matched_hl,
            'hostloc_paused' => strpos($p, 'hostloc 监控已关闭') !== false,
            'detail' => mb_substr($p, 0, 20000, 'UTF-8'),
        ];
    }
    $runs = array_slice($runs, -$limit);
    return array_reverse($runs); // 最新在前
}
