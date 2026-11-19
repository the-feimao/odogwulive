<?php

/**
 * Rebuild the isolated MySQL database used by the feature test suite.
 *
 * This script only ever touches the dedicated `odogwu_test` database;
 * the development/production database configured in .env is never modified.
 *
 * Usage: php tests/seed-test-db.php
 */

$root = dirname(__DIR__);
$database = 'odogwu_test';

$env = [];
foreach (file($root.'/.env') ?: [] as $line) {
    $line = trim($line);
    if ($line === '' || strpos($line, '#') === 0 || strpos($line, '=') === false) {
        continue;
    }
    [$key, $value] = explode('=', $line, 2);
    $env[trim($key)] = trim($value, " \t\n\r\0\x0B\"'");
}

$host = $env['DB_HOST'] ?? '127.0.0.1';
$port = $env['DB_PORT'] ?? '3306';
$username = $env['DB_USERNAME'] ?? 'root';
$password = $env['DB_PASSWORD'] ?? '';

try {
    $pdo = new PDO(
        sprintf('mysql:host=%s;port=%s;charset=utf8mb4', $host, $port),
        $username,
        $password,
        [PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION]
    );
    $pdo->exec('DROP DATABASE IF EXISTS `'.$database.'`');
    $pdo->exec('CREATE DATABASE `'.$database.'` CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci');

    $dump = file_get_contents($root.'/installer/eventright.sql');
    if ($dump === false) {
        throw new RuntimeException('Unable to read installer/eventright.sql');
    }
    $pdo->exec('USE `'.$database.'`');
    $pdo->exec($dump);

    $count = $pdo->query('SELECT COUNT(*) FROM information_schema.tables WHERE table_schema = '.$pdo->quote($database))
        ->fetchColumn();
    echo sprintf("Test database `%s` rebuilt with %d tables.\n", $database, $count);
} catch (Throwable $e) {
    fwrite(STDERR, 'Failed to rebuild test database: '.$e->getMessage().PHP_EOL);
    exit(1);
}
