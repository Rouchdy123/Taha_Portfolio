<?php
header('Content-Type: application/json');

$debug = [
    'php_version' => PHP_VERSION,
    'db_type_env' => getenv('DB_TYPE'),
    'db_type_server' => $_SERVER['DB_TYPE'] ?? null,
    'db_type_env_array' => $_ENV['DB_TYPE'] ?? null,
    
    'supabase_url_set' => !empty(getenv('SUPABASE_URL')) || !empty($_SERVER['SUPABASE_URL']) || !empty($_ENV['SUPABASE_URL']),
    'supabase_key_set' => !empty(getenv('SUPABASE_KEY')) || !empty($_SERVER['SUPABASE_KEY']) || !empty($_ENV['SUPABASE_KEY']),
    
    'url_preview' => substr(getenv('SUPABASE_URL') ?: ($_SERVER['SUPABASE_URL'] ?? ($_ENV['SUPABASE_URL'] ?? '')), 0, 15) . '...',
    'key_len' => strlen(getenv('SUPABASE_KEY') ?: ($_SERVER['SUPABASE_KEY'] ?? ($_ENV['SUPABASE_KEY'] ?? ''))),
    
    'all_server_keys' => array_keys($_SERVER),
    'all_env_keys' => array_keys($_ENV)
];

echo json_encode($debug, JSON_PRETTY_PRINT);
