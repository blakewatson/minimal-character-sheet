<?php

if (!defined('ROOT_DIR')) {
    define('ROOT_DIR', dirname(__DIR__, 2));
}

$b2_cache = [];

function get_b2_cache() {
    global $b2_cache;

    if (!count($b2_cache)) {
        // try to read in a file
        if (file_exists(ROOT_DIR. '/b2-cache.json')) {
            $cache = json_decode(file_get_contents(ROOT_DIR. '/b2-cache.json'), true);
            $b2_cache = is_array($cache) ? $cache : [];
        } else {
            $b2_cache = [];
        }
    }

    return $b2_cache;
}

function get_b2_cache_item($key) {
    $b2_cache = get_b2_cache();

    if (!array_key_exists($key, $b2_cache)) {
        return null;
    }

    return $b2_cache[$key];
}

function set_b2_cache_item($key, $data) {
    global $b2_cache;
    $cache = get_b2_cache();

    $cache[$key] = $data;
    $b2_cache = $cache;

    file_put_contents(ROOT_DIR. '/b2-cache.json', json_encode($b2_cache), LOCK_EX);
}

function http_is_any_error($response) {
    if ($response === false) {
        return true;
    }
    return isset($response['status']) && $response['status'] >= 400;
}

function http_is_auth_error($response) {
    $status = $response['status'] ?? null;
    if ($status === null) {
        return false;
    }
    return $status >= 400 && $status < 500;
}

function http_request($headers, $url, $post_data = null) {
    // Initialize a cURL session
    $curl = curl_init($url);
    
    // Set cURL options
    curl_setopt($curl, CURLOPT_HTTPHEADER, $headers);      // Set custom headers
    curl_setopt($curl, CURLOPT_RETURNTRANSFER, true);      // Return transfer as a string
    
    if ($post_data !== null) {
        curl_setopt($curl, CURLOPT_POST, true);
        curl_setopt($curl, CURLOPT_POSTFIELDS, $post_data);
    }

    // Execute the cURL session
    $response = curl_exec($curl);
    $http_code = curl_getinfo($curl, CURLINFO_HTTP_CODE);
    
    // Check for errors
    if (curl_errno($curl)) {
        error_log('Error in cURL: ' . curl_error($curl));
        curl_close($curl);
        return false;
    }
    
    // Close the cURL session
    curl_close($curl);

    // Process the response
    $data = json_decode($response, true);

    if (!is_array($data)) {
        $data = [];
    }

    if (!isset($data['status']) && $http_code >= 400) {
        $data['status'] = $http_code;
    }

    return $data;
}

function b2_authorize_account() {
    $url = 'https://api.backblazeb2.com/b2api/v4/b2_authorize_account';
    $headers = [
        'Authorization: Basic '. base64_encode($_ENV['BACKBLAZE_KEY_ID']. ':'. $_ENV['BACKBLAZE_KEY']),
        'Content-Type: application/json'
    ];

    $data = http_request($headers, $url);

    if (
        !is_array($data) ||
        empty($data['authorizationToken']) ||
        empty($data['apiInfo']['storageApi']['apiUrl'])
    ) {
        error_log('No authorizationToken in response');
        return false;
    }

    set_b2_cache_item('authorizationToken', $data['authorizationToken']);
    set_b2_cache_item('apiUrl', $data['apiInfo']['storageApi']['apiUrl']);

    return [
        'authorizationToken' => $data['authorizationToken'],
        'apiUrl' => $data['apiInfo']['storageApi']['apiUrl']
    ];
}

function b2_get_authorization_creds($skip_cache = false) {
    $token = get_b2_cache_item('authorizationToken');
    $api_url = get_b2_cache_item('apiUrl');

    if (!$token || !$api_url || $skip_cache) {
        $data = b2_authorize_account();
        if (!$data) {
            throw new Exception('Could not authorize account.');
        }
        $token = $data['authorizationToken'];
        $api_url = $data['apiUrl'];
    }

    return [
        'authorizationToken' => $token,
        'apiUrl' => $api_url
    ];    
}

function b2_get_upload_url() {
    $auth = b2_get_authorization_creds();

    if (!$auth) {
        throw new Exception('Could not authorize account while getting upload URL.');
    }

    $url = $auth['apiUrl']. '/b2api/v4/b2_get_upload_url?bucketId='. $_ENV['BACKBLAZE_BUCKET_ID'];

    $headers = [
        'Authorization: '. $auth['authorizationToken'],
        'Content-Type: application/json'
    ];
    
    $data = http_request($headers, $url);

    if (http_is_auth_error($data)) {
        $auth = b2_get_authorization_creds(true); // skip cache

        $url = $auth['apiUrl']. '/b2api/v4/b2_get_upload_url?bucketId='. $_ENV['BACKBLAZE_BUCKET_ID'];
    
        $headers = [
            'Authorization: '. $auth['authorizationToken'],
            'Content-Type: application/json'
        ];
    
        $data = http_request($headers, $url);

        if (http_is_any_error($data)) {
            throw new Exception('Could not get upload URL.');
        }
    }

    if (http_is_any_error($data) || empty($data['authorizationToken']) || empty($data['uploadUrl'])) {
        throw new Exception('Could not get upload URL.');
    }

    set_b2_cache_item('uploadToken', $data['authorizationToken']);
    set_b2_cache_item('uploadUrl', $data['uploadUrl']);

    return [
        'uploadToken' => $data['authorizationToken'],
        'uploadUrl' => $data['uploadUrl']
    ];
}

function b2_get_upload_creds($skip_cache = false) {
    $uploadUrl = get_b2_cache_item('uploadUrl');
    $uploadToken = get_b2_cache_item('uploadToken');

    if (!$uploadUrl || !$uploadToken || $skip_cache) {
        $data = b2_get_upload_url();
        if (!$data) {
            throw new Exception('Could not get upload URL.');
        }
        $uploadUrl = $data['uploadUrl'];
        $uploadToken = $data['uploadToken'];
    }

    return [
        'uploadUrl' => $uploadUrl,
        'uploadToken' => $uploadToken
    ];
}

function b2_encode_file_name($file_name) {
    return implode('/', array_map('rawurlencode', explode('/', $file_name)));
}

function b2_upload_file($file_path, $file_name, $file_type = 'b2/x-auto') {
    if (!is_file($file_path) || !is_readable($file_path)) {
        throw new Exception("File does not exist or is not readable: $file_path");
    }

    $creds = b2_get_upload_creds();

    if (!$creds) {
        throw new Exception('Could not get upload credentials while uploading file.');
    }

    $url = $creds['uploadUrl'];
    $token = $creds['uploadToken'];

    // Calculate the file size
    $file_size = filesize($file_path);

    // Compute the SHA1 hash of the file
    $fileSha1 = sha1_file($file_path);

    $headers = [
        'Authorization: '. $token,
        'Content-Type: '. $file_type,
        'Content-Length: '. $file_size,
        'X-Bz-File-Name: '. b2_encode_file_name($file_name),
        'X-Bz-Content-Sha1: '. $fileSha1,
    ];
    
    $post_data = file_get_contents($file_path);

    $resp = http_request($headers, $url, $post_data);

    $err_count = 0;

    // Backblaze recommends trying five times before giving up
    while (http_is_any_error($resp) && $err_count < 5) {
        $err_count++;
        $creds = b2_get_upload_creds(true); // skip cache
        $url = $creds['uploadUrl'];
        $headers[0] = 'Authorization: '. $creds['uploadToken'];
        $resp = http_request($headers, $url, $post_data);
    }

    if (http_is_any_error($resp)) {
        throw new Exception('Could not upload file.');
    }

    if (empty($resp['fileId']) || empty($resp['fileName'])) {
        throw new Exception('Could not upload file: unexpected Backblaze B2 response.');
    }

    return [
        'fileId' => $resp['fileId'],
        'fileName' => $resp['fileName']
    ];
}

function b2_delete_file_version($file_id, $file_name) {
    $auth = b2_get_authorization_creds();

    if (!$auth) {
        throw new Exception('Could not authorize account while deleting b2 file version.');
    }

    $url = $auth['apiUrl'] . '/b2api/v4/b2_delete_file_version?';
    $url .= sprintf('fileId=%s&fileName=%s', rawurlencode($file_id), rawurlencode($file_name));

    $headers = [
        'Authorization: '. $auth['authorizationToken'],
        'Content-Type: application/json'
    ];

    $data = http_request($headers, $url);
    error_log( print_r( $data, true ) );

    if (http_is_auth_error($data)) {
        error_log('Retrying b2 file deletion.');
        $auth = b2_get_authorization_creds(true); // skip cache

        $headers = [
            'Authorization: '. $auth['authorizationToken'],
            'Content-Type: application/json'
        ];

        $data = http_request($headers, $url);

        if (http_is_auth_error($data)) {
            error_log( print_r( $data, true ) );
            throw new Exception('Could not delete b2 file version.');
        }
    }

    return true;
}
