<?php

// Affiche la version de PHP
echo "Version PHP : " . PHP_VERSION . PHP_EOL . PHP_EOL;

// Extensions à tester
$extensions = [
    'mysqli','pdo_mysql','curl','mbstring','openssl','sodium','intl','fileinfo','sqlite3','pdo_sqlite'
];

$erreur = false;

foreach ($extensions as $ext) {
    if (extension_loaded($ext)) {
        echo "[OK] $ext chargé" . PHP_EOL;
    } else {
        echo "[ERREUR] $ext manquant" . PHP_EOL;
        $erreur = true;
    }
}
