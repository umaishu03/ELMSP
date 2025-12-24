<?php
// One-off script to inspect 'shifts' table columns using .env credentials
$envPath = __DIR__ . '/../.env';
if (!file_exists($envPath)) {
    echo ".env not found\n";
    exit(1);
}
$env = file($envPath, FILE_IGNORE_NEW_LINES | FILE_SKIP_EMPTY_LINES);
$config = [];
foreach ($env as $line) {
    if (strpos(trim($line), '#') === 0) continue;
    if (!strpos($line, '=')) continue;
    [$k, $v] = explode('=', $line, 2);
    $k = trim($k);
    $v = trim($v);
    $v = trim($v, "\"'");
    $config[$k] = $v;
}
$host = $config['DB_HOST'] ?? '127.0.0.1';
$port = $config['DB_PORT'] ?? '3306';
$db = $config['DB_DATABASE'] ?? '';
$user = $config['DB_USERNAME'] ?? '';
$pass = $config['DB_PASSWORD'] ?? '';

$dsn = "mysql:host={$host};port={$port};dbname={$db};charset=utf8mb4";
try {
    $pdo = new PDO($dsn, $user, $pass, [PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION]);
    $stmt = $pdo->query("SHOW COLUMNS FROM shifts");
    $rows = $stmt->fetchAll(PDO::FETCH_ASSOC);
    echo "Columns in shifts table:\n";
    foreach ($rows as $r) {
        echo "- {$r['Field']} ({$r['Type']})" . PHP_EOL;
    }
} catch (PDOException $e) {
    echo "PDO error: " . $e->getMessage() . PHP_EOL;
    exit(1);
}
