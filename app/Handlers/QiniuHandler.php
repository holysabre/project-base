<?php

namespace App\Handlers;

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
