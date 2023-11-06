<?php

namespace App\Handlers;

use Illuminate\Support\Facades\Cache;
use Qiniu\Auth as QiniuAuth;
use Qiniu\Storage\UploadManager;
use Qiniu\Storage\BucketManager;

class QiniuHandler
{
    private $auth;
    private $bucket;

    public function __construct()
    {
        $this->auth = $this->getAuth();
        $this->bucket = env('QINIU_BUCKET');
    }

    public function uploadToQiniu($filename, $filepath)
    {
        $token_package = $this->qiniuToken();
        $token = $token_package['token'];
        $uploadMgr = new UploadManager();
        $resp = $uploadMgr->putFile($token, $filename, $filepath);

        return $resp;
    }

    public function qiniuToken($ttl = 86400)
    {
        $qiniu_token = Cache::remember('qiniu_token', $ttl, function () use ($ttl) {
            $accessKey = env('QINIU_ACCESS_KEY');
            $secretKey =  env('QINIU_SECRET_KEY');
            $bucket = env('QINIU_BUCKET');
            $auth = new QiniuAuth($accessKey, $secretKey);
            $token = $auth->uploadToken($bucket, null, $ttl);
            $expired_at = now()->addSeconds($ttl)->getTimestamp();
            return [
                'token' => $token,
                'expired_at' => $expired_at,
                'bucket' => $bucket,
            ];
        });

        return $qiniu_token;
    }

    public function getMetadataFromQiniu($key)
    {
        $bucketManager = new BucketManager($this->auth);
        $data = $bucketManager->stat($this->bucket, $key);

        return $data;
    }

    private function getAuth()
    {
        $accessKey = env('QINIU_ACCESS_KEY');
        $secretKey =  env('QINIU_SECRET_KEY');
        $auth = new QiniuAuth($accessKey, $secretKey);

        return $auth;
    }
}
