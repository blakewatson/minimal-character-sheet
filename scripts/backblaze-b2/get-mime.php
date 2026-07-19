<?php
/**
*	Detect MIME type using file extension or file inspection, stolen from F3
*	@return string
*	@param $file string File tmp_name
*	@param $inspect bool
**/
function get_mime($file, $inspect=FALSE) {
    if ($inspect) {
        if (is_file($file) && is_readable($file)) {
            // physical files
            if (extension_loaded('fileinfo'))
                $mime=mime_content_type($file);
            elseif (preg_match('/Darwin/i',PHP_OS))
                $mime=trim(exec('file -bI '.escapeshellarg($file)));
            elseif (!preg_match('/^win/i',PHP_OS))
                $mime=trim(exec('file -bi '.escapeshellarg($file)));
            if (isset($mime) && !empty($mime)){
                // cut charset information if any
                $exp=explode(';',$mime,2);
                $mime=$exp[0];
            }
        }
        else {
            // remote and stream files
            if (ini_get('allow_url_fopen') && ($fhandle=fopen($file,'rb'))) {
                // only get head bytes instead of whole file
                $bytes=fread($fhandle,20);
                fclose($fhandle);
            }
            elseif (($response=$this->request($file,['method' => 'HEAD']))
                && preg_grep('/HTTP\/[\d.]{1,3} 200/',$response['headers'])
                && ($type = preg_grep('/^Content-Type:/i',$response['headers']))) {
                // get mime type directly from response header
                return preg_replace('/^Content-Type:\s*/i','',array_pop($type));
            }
            else // load whole file
                $bytes=file_get_contents($file);
            if (extension_loaded('fileinfo')) {
                // get mime from fileinfo
                $finfo=finfo_open(FILEINFO_MIME_TYPE);
                $mime=finfo_buffer($finfo,$bytes);
            }
            elseif ($bytes) {
                // magic number header fallback
                $map=[
                    '\x64\x6E\x73\x2E'=>'audio/basic',
                    '\x52\x49\x46\x46.{4}\x41\x56\x49\x20\x4C\x49\x53\x54'=>'video/avi',
                    '\x42\x4d'=>'image/bmp',
                    '\x42\x5A\x68'=>'application/x-bzip2',
                    '\x07\x64\x74\x32\x64\x64\x74\x64'=>'application/xml-dtd',
                    '\xD0\xCF\x11\xE0\xA1\xB1\x1A\xE1'=>'application/msword',
                    '\x50\x4B\x03\x04\x14\x00\x06\x00'=>'application/msword',
                    '\x0D\x44\x4F\x43'=>'application/msword',
                    'GIF\d+a'=>'image/gif',
                    '\x1F\x8B'=>'application/x-gzip',
                    '\xff\xd8\xff'=>'image/jpeg',
                    '\x49\x46\x00'=>'image/jpeg',
                    '\xFF\xFB'=>'audio/mpeg',
                    '\x49\x44\x33'=>'audio/mpeg',
                    '\x00\x00\x01\xBA'=>'video/mpeg',
                    '\x4F\x67\x67\x53\x00\x02\x00\x00\x00\x00\x00\x00\x00\x00'=>'audio/vorbis',
                    '\x25\x50\x44\x46'=>'application/pdf',
                    '\x89PNG\x0d\x0a'=>'image/png',
                    '.{4}\x6D\x6F\x6F\x76\x'=>'video/quicktime',
                    '\x53\x49\x54\x21\x00'=>'application/x-stuffit',
                    '\x43\x57\x53'=>'application/x-shockwave-flash',
                    '\x1F\x8B\x08'=>'application/x-tar',
                    '\x49\x20\x49'=>'image/tiff',
                    '\x52\x49\x46\x46.{4}\x57\x41\x56\x45\x66\x6D\x74\x20'=>'audio/wav',
                    '\xFD\xFF\xFF\xFF\x20\x00\x00\x00'=>'application/vnd.ms-excel',
                    '\x50\x4B\x03\x04'=>'application/x-zip-compressed',
                    '[ -~]+$'=>'text/plain',
                ];
                foreach ($map as $key=>$val)
                    if (preg_match('/^'.$key.'/',substr($bytes,0,128)))
                        return $val;
            }
        }
        if (isset($mime) && !empty($mime))
            return $mime;
        // Fallback to file extension-based check if no mime was found yet
    }
    if (preg_match('/\w+$/',$file,$ext)) {
        $map=[
            'au'=>'audio/basic',
            'avi'=>'video/avi',
            'bmp'=>'image/bmp',
            'bz2'=>'application/x-bzip2',
            'css'=>'text/css',
            'dtd'=>'application/xml-dtd',
            'doc'=>'application/msword',
            'gif'=>'image/gif',
            'gz'=>'application/x-gzip',
            'hqx'=>'application/mac-binhex40',
            'html?'=>'text/html',
            'jar'=>'application/java-archive',
            'jpe?g|jfif?'=>'image/jpeg',
            'js'=>'application/x-javascript',
            'midi'=>'audio/x-midi',
            'mp3'=>'audio/mpeg',
            'mpe?g'=>'video/mpeg',
            'ogg'=>'audio/vorbis',
            'pdf'=>'application/pdf',
            'png'=>'image/png',
            'ppt'=>'application/vnd.ms-powerpoint',
            'ps'=>'application/postscript',
            'qt'=>'video/quicktime',
            'ram?'=>'audio/x-pn-realaudio',
            'rdf'=>'application/rdf',
            'rtf'=>'application/rtf',
            'sgml?'=>'text/sgml',
            'sit'=>'application/x-stuffit',
            'svg'=>'image/svg+xml',
            'swf'=>'application/x-shockwave-flash',
            'tgz'=>'application/x-tar',
            'tiff'=>'image/tiff',
            'txt'=>'text/plain',
            'wav'=>'audio/wav',
            'xls'=>'application/vnd.ms-excel',
            'xml'=>'application/xml',
            'zip'=>'application/x-zip-compressed'
        ];
        foreach ($map as $key=>$val)
            if (preg_match('/'.$key.'/',strtolower($ext[0])))
                return $val;
    }
    return 'application/octet-stream';
}