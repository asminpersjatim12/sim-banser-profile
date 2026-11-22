<?php

declare(strict_types=1);

$baseDirectory = __DIR__;
$storageDirectory = $baseDirectory . DIRECTORY_SEPARATOR . 'storage';
$databasePath = $storageDirectory . DIRECTORY_SEPARATOR . 'banser.sqlite';

if (!is_dir($storageDirectory)) {
    mkdir($storageDirectory, 0700, true);
}

$pdo = new PDO('sqlite:' . $databasePath);
$pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
$pdo->setAttribute(PDO::ATTR_DEFAULT_FETCH_MODE, PDO::FETCH_ASSOC);

$pdo->exec(
    'CREATE TABLE IF NOT EXISTS users (
        id INTEGER PRIMARY KEY AUTOINCREMENT,
        username TEXT NOT NULL UNIQUE,
        password_hash TEXT NOT NULL,
        created_at TEXT NOT NULL
    )'
);
