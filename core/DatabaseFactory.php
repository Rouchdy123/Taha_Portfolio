<?php
require_once __DIR__ . '/DatabaseInterface.php';
require_once __DIR__ . '/MySQLDatabase.php';
require_once __DIR__ . '/SupabaseDatabase.php';

/**
 * Factory pour créer l'instance de base de données appropriée
 * Permet de basculer entre MySQL et Supabase via configuration
 */
class DatabaseFactory
{
    private static ?DatabaseInterface $instance = null;

    private static function resolveConfigValue(array $envNames, array $serverNames, array $envVarNames, mixed $default = null): mixed
    {
        foreach ($envNames as $name) {
            $value = getenv($name);
            if ($value !== false && $value !== '') {
                return $value;
            }
        }

        foreach ($serverNames as $name) {
            $value = $_SERVER[$name] ?? null;
            if ($value !== null && $value !== '') {
                return $value;
            }
        }

        foreach ($envVarNames as $name) {
            $value = $_ENV[$name] ?? null;
            if ($value !== null && $value !== '') {
                return $value;
            }
        }

        return $default;
    }

    /**
     * Retourne l'instance de base de données configurée
     * Utilise le fichier config.php pour déterminer le type
     */
    public static function getInstance(): DatabaseInterface
    {
        if (self::$instance === null) {
            $config = require __DIR__ . '/../config.php';
            
            // Détection du type de DB via config ou variable d'environnement (avec fallback $_SERVER et $_ENV)
            $dbType = getenv('DB_TYPE') ?: ($_SERVER['DB_TYPE'] ?? ($_ENV['DB_TYPE'] ?? ($config['db_type'] ?? 'mysql')));
            
            switch ($dbType) {
                case 'supabase':
                    // URL
                    $url = getenv('SUPABASE_URL') ?: (getenv('URL_SUPABASE') ?: ($_SERVER['SUPABASE_URL'] ?? ($_SERVER['URL_SUPABASE'] ?? ($_ENV['SUPABASE_URL'] ?? ($_ENV['URL_SUPABASE'] ?? ($config['supabase_url'] ?? ''))))));
                    
                    $key = self::resolveConfigValue(
                        ['SUPABASE_KEY', 'SUPABASE_ANON_KEY', 'SUPABASE_PUBLISHABLE_KEY', 'NEXT_PUBLIC_SUPABASE_ANON_KEY', 'SUPABASE_API_KEY', 'CLÉ_PUBLISHABLE_SUPABASE', 'CLE_PUBLISHABLE_SUPABASE'],
                        ['SUPABASE_KEY', 'SUPABASE_ANON_KEY', 'SUPABASE_PUBLISHABLE_KEY', 'NEXT_PUBLIC_SUPABASE_ANON_KEY', 'SUPABASE_API_KEY', 'CLÉ_PUBLISHABLE_SUPABASE', 'CLE_PUBLISHABLE_SUPABASE'],
                        ['SUPABASE_KEY', 'SUPABASE_ANON_KEY', 'SUPABASE_PUBLISHABLE_KEY', 'NEXT_PUBLIC_SUPABASE_ANON_KEY', 'SUPABASE_API_KEY', 'CLÉ_PUBLISHABLE_SUPABASE', 'CLE_PUBLISHABLE_SUPABASE'],
                        $config['supabase_key'] ?? ''
                    );
                    
                    $authToken = self::resolveConfigValue(
                        ['SUPABASE_AUTH_TOKEN', 'SUPABASE_SERVICE_ROLE_KEY', 'SUPABASE_SERVICE_ROLE', 'CLÉ_SECRET_SUPABASE', 'CLE_SECRET_SUPABASE'],
                        ['SUPABASE_AUTH_TOKEN', 'SUPABASE_SERVICE_ROLE_KEY', 'SUPABASE_SERVICE_ROLE', 'CLÉ_SECRET_SUPABASE', 'CLE_SECRET_SUPABASE'],
                        ['SUPABASE_AUTH_TOKEN', 'SUPABASE_SERVICE_ROLE_KEY', 'SUPABASE_SERVICE_ROLE', 'CLÉ_SECRET_SUPABASE', 'CLE_SECRET_SUPABASE'],
                        $config['supabase_auth_token'] ?? null
                    );
                    
                    if (empty($url) || empty($key)) {
                        $isVercel = !empty(getenv('VERCEL')) || !empty($_SERVER['VERCEL']) || !empty($_ENV['VERCEL']) || isset($_SERVER['NOW_REGION']);
                        if ($isVercel) {
                            $missing = [];
                            if (empty($url)) $missing[] = 'SUPABASE_URL';
                            if (empty($key)) $missing[] = 'SUPABASE_KEY';
                            throw new RuntimeException("Erreur de configuration critique sur Vercel : Variables d'environnement manquantes dans le tableau de bord Vercel : " . implode(', ', $missing) . ". Veuillez vous assurer d'avoir correctement ajoute ces variables dans Vercel.");
                        } else {
                            $dbType = 'mysql';
                        }
                    }
                    break;
            }

            if ($dbType === 'supabase') {
                self::$instance = new SupabaseDatabase($url, $key, $authToken);
            } else {
                self::$instance = new MySQLDatabase(
                    $config['db_host'],
                    $config['db_name'],
                    $config['db_user'],
                    $config['db_pass']
                );
            }
        }
        
        return self::$instance;
    }

    /**
     * Réinitialise l'instance (utile pour les tests)
     */
    public static function reset(): void
    {
        self::$instance = null;
    }
}
