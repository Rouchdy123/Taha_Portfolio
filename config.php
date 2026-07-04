<?php
// Charger les variables d'environnement depuis le fichier .env local
if (file_exists(__DIR__ . '/.env')) {
    $lines = file(__DIR__ . '/.env', FILE_IGNORE_NEW_LINES | FILE_SKIP_EMPTY_LINES);
    foreach ($lines as $line) {
        $line = trim($line);
        if (empty($line) || strpos($line, '#') === 0) {
            continue;
        }
        $parts = explode('=', $line, 2);
        if (count($parts) === 2) {
            $name = trim($parts[0]);
            $value = trim($parts[1]);
            // Retirer les guillemets si présents
            $value = trim($value, '"\'');
            if (getenv($name) === false) {
                putenv("$name=$value");
                $_ENV[$name] = $value;
                $_SERVER[$name] = $value;
            }
        }
    }
}

$isVercel = !empty(getenv('VERCEL')) || !empty($_SERVER['VERCEL']) || !empty($_ENV['VERCEL']) || isset($_SERVER['NOW_REGION']);
$defaultDbType = $isVercel ? 'supabase' : 'mysql';

return [
    // === TYPE DE BASE DE DONNÉES ===
    // 'mysql' pour l'ancienne base (local)
    // 'supabase' pour la nouvelle base (cloud)
    'db_type' => (getenv('DB_TYPE') ?: ($_SERVER['DB_TYPE'] ?? ($_ENV['DB_TYPE'] ?? null))) ?: 
                 (((getenv('SUPABASE_URL') ?: ($_SERVER['SUPABASE_URL'] ?? ($_ENV['SUPABASE_URL'] ?? null))) || 
                   (getenv('SUPABASE_KEY') ?: ($_SERVER['SUPABASE_KEY'] ?? ($_ENV['SUPABASE_KEY'] ?? null))) || 
                   (getenv('SUPABASE_ANON_KEY') ?: ($_SERVER['SUPABASE_ANON_KEY'] ?? ($_ENV['SUPABASE_ANON_KEY'] ?? null)))) ? 'supabase' : $defaultDbType),
    
    // === CONFIGURATION SUPABASE ===
    'supabase_url' => getenv('SUPABASE_URL') ?: ($_SERVER['SUPABASE_URL'] ?? ($_ENV['SUPABASE_URL'] ?? '')),
    'supabase_key' => getenv('SUPABASE_KEY') ?: ($_SERVER['SUPABASE_KEY'] ?? ($_ENV['SUPABASE_KEY'] ?? (getenv('SUPABASE_ANON_KEY') ?: ($_SERVER['SUPABASE_ANON_KEY'] ?? ($_ENV['SUPABASE_ANON_KEY'] ?? ''))))),
    'supabase_auth_token' => getenv('SUPABASE_AUTH_TOKEN') ?: ($_SERVER['SUPABASE_AUTH_TOKEN'] ?? ($_ENV['SUPABASE_AUTH_TOKEN'] ?? (getenv('SUPABASE_SERVICE_ROLE_KEY') ?: ($_SERVER['SUPABASE_SERVICE_ROLE_KEY'] ?? ($_ENV['SUPABASE_SERVICE_ROLE_KEY'] ?? null))))),
    
    // === CONFIGURATION MYSQL (LEGACY - GARDÉE POUR ROLLBACK) ===
    'db_host' => '127.0.0.1',
    'db_name' => 'portfolio',
    'db_user' => 'root',
    'db_pass' => '',
    
    // === CONFIGURATION COMMUNE ===
    'uploads_dir' => __DIR__ . '/assets/uploads',
    'allowed_image_types' => ['image/jpeg', 'image/png', 'image/webp'],
    'allowed_doc_types' => ['application/pdf'],
];
