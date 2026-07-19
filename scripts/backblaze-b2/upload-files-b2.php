<?php
require_once __DIR__ . '/file-upload-errors.php';
require_once __DIR__ . '/get-mime.php';
require_once __DIR__ . '/b2-functions.php';

function upload_file_b2(string $file_path, string $file_name, array $mime_types = []) {
    $type = get_mime($file_path, true);

    if (!empty($mime_types) && !in_array($type, $mime_types, true)) {
        return [
            'success' => false,
            'error' => "File type for $file_name not recognized.",
            'mime_type' => $type,
        ];
    }

    return [
        'success' => true,
        'b2_file' => b2_upload_file($file_path, $file_name, $type),
        'mime_type' => $type,
    ];
}

function upload_files_b2(string $files_key, array $mime_types) {
    if (empty($_FILES[$files_key]) || empty($_FILES[$files_key]["error"])) {
        return false;
    }

    $upload_failures = [];
    $b2_files = [];

    foreach ($_FILES[$files_key]["error"] as $key => $error) {
        if ($error !== UPLOAD_ERR_OK) {
            $upload_failures[$key] = FILE_UPLOAD_ERRORS[$error] ?? 'unknown error';
            continue;
        }
        
        $basename = basename($_FILES[$files_key]["name"][$key]);
        $tmp_name = $_FILES[$files_key]["tmp_name"][$key];

        // basename() may prevent filesystem traversal attacks;
        // further validation/sanitation of the filename may be appropriate
        $name = bin2hex(random_bytes(3)) . '_' . $basename;
        $upload = upload_file_b2($tmp_name, $name, $mime_types);
        
        if ($upload['success']) {
            $b2_files[] = $upload['b2_file'];
        } else {
            $upload_failures[$key] = $upload['error'] ?? 'unknown error';
        }
    }

    return [
        'b2_files' => $b2_files,
        'upload_failures' => $upload_failures,
    ];
}
