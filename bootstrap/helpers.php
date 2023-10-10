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
