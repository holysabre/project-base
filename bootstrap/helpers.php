<?php

function json_response($code = 200, $msg = 'success', $data = [], $total = 0, $http_code = 200, $params = [])
{
    $resp['data'] = $data;
    $resp['total'] = intval($total);
    if (!empty($params)) {
        $resp = array_merge($resp, $params);
    }
    $response = [
        'code' => intval($code),
        'msg' => empty($msg) ? 'success' : $msg,
    ];
    $response = array_merge($response, $resp);
    if ($code == 500) {
        $http_code = $code;
    }
    return response()->json($response)->setStatusCode($http_code);
}


function getFilesFromDir($path, $files = [])
{

    if (is_dir($path)) {

        $dir =  scandir($path);
        foreach ($dir as $value) {
            $sub_path = $path . '/' . $value;
            if ($value == '.' || $value == '..') {
                continue;
            } else if (is_dir($sub_path)) {
                getFilesFromDir($sub_path, $files);
            } else {
                $files[] = [$value => $path . '/' . $value];
            }
        }
    }
    return $files;
}

function getFilenameByPath($path)
{
    $parts = explode('/', $path);
    list($filename, $ext) = explode('.', last($parts));
    return $filename . (empty($ext) ? '' : '.' . $ext);
}
