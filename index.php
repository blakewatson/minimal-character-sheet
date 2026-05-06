<?php

// load packages
require_once('./vendor/autoload.php');

// bring in Fat Free
$f3 = \Base::instance();

// load phpdotenv
$dotenv = Dotenv\Dotenv::createImmutable(__DIR__);
$dotenv->load();


if ( ( $_ENV['ENV'] ?? '' ) === 'MAINTENANCE') {
    error_log($_ENV['ENV']);
    echo \Template::instance()->render( 'templates/maintenance.html' );
    return;
}

// require POSTMARK_SECRET
$dotenv->required( 'POSTMARK_SECRET' );

// set default debug level
$debug_level = $_ENV['DEBUG'] ?? '';
$debug_level = $debug_level ? $debug_level : 0;

$f3->set( 'DEBUG', $debug_level );

$f3->set( 'AUTOLOAD', 'classes/models/; classes/controllers/' );
$f3->set( 'DB', new \DB\SQL( 'sqlite:data/db.sqlite3' ) );
$f3->set( 'CACHE', 'folder=tmp/cache/' );

// Allow only admins to access the site?
$f3->set( 'admin_only', ($_ENV['ADMIN_ONLY'] ?? null) === '1' );

// allow signups only if ALLOW_SIGNUPS and not ADMIN_ONLY
$f3->set( 'allow_signups', ($_ENV['ALLOW_SIGNUPS'] ?? null) === '1' && !$f3->get( 'admin_only' ));

// set the font awesome kit URL from env for icon loading
$f3->set( 'font_awesome_kit_url', $_ENV['FONT_AWESOME_KIT_URL'] ?? '' );

// homepage
$f3->route( 'GET /', function( $f3 ) {
    $f3->set( 'lightbox', true );
    echo \Template::instance()->render( 'templates/home.html' );
} );

// dashboard
$f3->route( 'GET /dashboard', 'Dashboard->sheet_list' );
$f3->route( 'GET|POST /add-sheet', 'Dashboard->add_sheet' );
$f3->route( 'POST /make-public/@sheet_slug', 'Dashboard->make_sheet_public' );
$f3->route( 'POST /import-sheet', 'Dashboard->import_sheet' );

// sheet view
$f3->route( 'GET /sheet/@sheet_slug', 'Dashboard->sheet_single' );
$f3->route( 'POST /sheet/@sheet_slug', 'Dashboard->save_sheet' );
$f3->route( 'DELETE /sheet/@sheet_slug', 'Dashboard->delete_sheet' );
// read-only public sheet refresh. This endpoint receives a request for a sheet
// and provides an updated date if it exists. The endpoint returns sheet data
// only if the sheet has been updated.
$f3->route( 'GET /sheet-data/@sheet_slug', 'Dashboard->get_sheet_data' );
$f3->route( 'GET /print/@sheet_slug', 'Dashboard->print_sheet' );

// random.org proxy (keeps API key server-side)
$f3->route( 'POST /api/random', function( $f3 ) {
    header( 'Content-Type: application/json' );

    // initialize DB-backed session so we can check auth
    new \DB\SQL\Session( $f3->get( 'DB' ), 'sessions', true, function( $sess ) { return true; } );

    // auth check
    if ( ! $f3->get( 'SESSION.email' ) ) {
        $f3->status( 401 );
        echo json_encode([ 'success' => false, 'reason' => 'unauthorized' ]);
        return;
    }

    // rate limit: 10 requests per 60 seconds per IP
    $cache = \Cache::instance();
    $ip = $f3->get( 'IP' );
    $cacheKey = 'ratelimit_random_' . md5( $ip );
    $limit = 10;
    $window = 60;

    if ( $cache->exists( $cacheKey, $count ) ) {
        if ( $count >= $limit ) {
            $f3->status( 429 );
            echo json_encode([ 'success' => false, 'reason' => 'rate_limited' ]);
            return;
        }
        $cache->set( $cacheKey, $count + 1, $window );
    } else {
        $cache->set( $cacheKey, 1, $window );
    }

    // check API key is configured
    $apiKey = $_ENV['RANDOM_ORG_API_KEY'] ?? '';
    if ( ! $apiKey ) {
        $f3->status( 503 );
        echo json_encode([ 'success' => false, 'reason' => 'not_configured' ]);
        return;
    }

    // parse and validate request body
    $body = json_decode( file_get_contents( 'php://input' ), true );
    if ( ! $body ) {
        $f3->status( 400 );
        echo json_encode([ 'success' => false, 'reason' => 'invalid_json' ]);
        return;
    }

    $n = $body['n'] ?? null;
    $min = $body['min'] ?? null;
    $max = $body['max'] ?? null;
    $allowedMax = [ 4, 6, 8, 10, 12, 20, 100 ];

    if ( ! is_int( $n ) || $n < 1 || $n > 100 ) {
        $f3->status( 400 );
        echo json_encode([ 'success' => false, 'reason' => 'invalid_n' ]);
        return;
    }

    if ( ! is_int( $min ) || $min !== 1 ) {
        $f3->status( 400 );
        echo json_encode([ 'success' => false, 'reason' => 'invalid_min' ]);
        return;
    }

    if ( ! is_int( $max ) || ! in_array( $max, $allowedMax, true ) ) {
        $f3->status( 400 );
        echo json_encode([ 'success' => false, 'reason' => 'invalid_max' ]);
        return;
    }

    // proxy to RANDOM.ORG v4 API
    $payload = json_encode([
        'jsonrpc' => '2.0',
        'method' => 'generateIntegers',
        'params' => [
            'apiKey' => $apiKey,
            'n' => $n,
            'min' => $min,
            'max' => $max,
            'replacement' => true,
            'base' => 10,
        ],
        'id' => 1,
    ]);

    $ch = curl_init( 'https://api.random.org/json-rpc/4/invoke' );
    curl_setopt_array( $ch, [
        CURLOPT_POST => true,
        CURLOPT_POSTFIELDS => $payload,
        CURLOPT_HTTPHEADER => [ 'Content-Type: application/json' ],
        CURLOPT_RETURNTRANSFER => true,
        CURLOPT_TIMEOUT => 10,
    ]);
    $response = curl_exec( $ch );
    $httpCode = curl_getinfo( $ch, CURLINFO_HTTP_CODE );
    curl_close( $ch );

    if ( $httpCode !== 200 || ! $response ) {
        $f3->status( 502 );
        echo json_encode([ 'success' => false, 'reason' => 'upstream_error' ]);
        return;
    }

    $result = json_decode( $response, true );
    if ( ! isset( $result['result']['random']['data'] ) ) {
        $f3->status( 502 );
        echo json_encode([ 'success' => false, 'reason' => 'unexpected_response' ]);
        return;
    }

    echo json_encode([
        'success' => true,
        'data' => $result['result']['random']['data'],
    ]);
});

// login/logout
$f3->route( 'GET /login', 'Authentication->login_form' );
$f3->route( 'POST /login', 'Authentication->login' );
$f3->route( 'GET /logout', 'Authentication->logout' );

// registration
if ($f3->get('allow_signups')) {
    $f3->route( 'GET /register/confirm/@email/@clear_token', 'Authentication->confirm' );
    $f3->route( 'GET /register/confirm/send', 'Authentication->resend_confirmation_form' );
    $f3->route( 'POST /register/confirm/send', 'Authentication->resend_confirmation' );
    $f3->route( 'GET /register', 'Authentication->registration_form' );
    $f3->route( 'POST /register', 'Authentication->register' );
}

// password reset
$f3->route( 'GET /request-password-reset', 'Authentication->request_password_reset_form' );
$f3->route( 'POST /request-password-reset', 'Authentication->request_password_reset' );
$f3->route( 'GET /password-reset/@email/@clear_token', 'Authentication->password_reset_form' );
$f3->route( 'POST /password-reset', 'Authentication->password_reset' );

// admin dashboard
$f3->route( 'GET /admin', 'Admin->admin_dashboard' );
$f3->route( 'GET /admin/users', 'Admin->admin_users' );
$f3->route( 'GET /admin/stats', 'Admin->stats' );
$f3->route( 'GET|POST /admin/restore-sheet', 'Admin->restore_sheet' );

// set up custom error page
$f3->set( 'ONERROR', function( $f3, $params ) {
    echo \Template::instance()->render( 'templates/error.html' );
} );

/**
 * Helper functions
 * Move these to a separate helpers.php file if the list grows.
 */

/**
 * Returns a versioned asset path using Vite's manifest file.
 * Used in templates for cache busting: {{ vite('js/app.js') }}
 */
function vite($entry) {
    static $manifest;
    if (!$manifest) {
        $manifestPath = __DIR__ . '/dist/.vite/manifest.json';
        if (file_exists($manifestPath)) {
            $manifest = json_decode(file_get_contents($manifestPath), true);
        } else {
            $manifest = [];
        }
    }
    if (isset($manifest[$entry])) {
        return '/dist/' . $manifest[$entry]['file'];
    }
    // Fallback: return the path as-is (for non-manifest assets)
    return '/dist/' . $entry;
}

/**
 * Returns companion CSS paths for a JS entry from the Vite manifest.
 */
function viteCssFromJs($entry) {
    static $manifest;
    if (!$manifest) {
        $manifestPath = __DIR__ . '/dist/.vite/manifest.json';
        if (file_exists($manifestPath)) {
            $manifest = json_decode(file_get_contents($manifestPath), true);
        } else {
            $manifest = [];
        }
    }
    if (isset($manifest[$entry]['css'])) {
        return array_map(fn($path) => '/dist/' . $path, $manifest[$entry]['css']);
    }
    return [];
}

$f3->run();