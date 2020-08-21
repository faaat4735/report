<?php

// 巨量引擎 api https://ad.oceanengine.com/openapi/doc/index.html?id=1670090701127683
// 获取auth_code https://ad.oceanengine.com/openapi/audit/oauth.html?app_id=1674513528612907&state=2&redirect_uri=https://www.stepcounter.cn:4420/api/ocean
namespace Core;

class Ocean
{

    protected $url = '';
    protected $data = array();
    protected $headers = array();
    protected $contentType = "application/json";
    protected $appId = 1674513528612907;
    protected $advertiserId = 1670284495907843;
//    protected $advertiserId = 1658126460548109;
    protected $secret = '7b96269435050d98d7f4f7fc03f6d547ce5c8e5e';
    protected $accessToken = '';

    public function __construct()
    {
        $this->headers = array('Content-Type' => 'application/json');
        $this->accessToken = $this->getToken();
//        var_dump($this->httpPostJson($this->url, $this->body, $lastHeader));
    }
// access_token f01f3929c9d66c35f00578b0cf11ea8eaf24f263
// refresh_token 3ade2342ca4b750bf477d8a0a09dd3dc28626ffb
    public function getToken ()
    {
//        $this->url = 'https://ad.oceanengine.com/open_api/oauth2/access_token/';
//        $this->data = array('app_id' => $this->appId, 'secret' => $this->secret, 'grant_type' => 'auth_code', 'auth_code' => 'f84bbba970ccc2fb8dcda4598692bb19e232a506');
//        $return = $this->execCurl();
//        var_dump($return);
//        return $return;
        $tokenFile = LOG_DIR . 'ocean_access_token.txt';
        $data = json_decode(file_get_contents($tokenFile));
        if ($data->expires_in < time()) {
            return $this->refreshToken($data->refresh_token);
        }
        return $data->access_token;

    }

    public function refreshToken ($refreshToken)
    {
        $tokenFile = LOG_DIR . 'ocean_access_token.txt';
        $this->url = 'https://ad.oceanengine.com/open_api/oauth2/refresh_token/';
        $this->data = array('app_id' => $this->appId, 'secret' => $this->secret, 'grant_type' => 'refresh_token', 'refresh_token' => $refreshToken);
        $return = $this->execCurl();
        $return->expires_in += time() - 100;
        $return->refresh_token_expires_in += time() - 100;
        file_put_contents($tokenFile, json_encode($return));//FILE_APPEND
        return $return->access_token;
    }

    public function getAdvertiserList () {
        $this->data = array('advertiser_id' => $this->advertiserId);
        $this->url = 'https://ad.oceanengine.com/open_api/2/majordomo/advertiser/select/?' . http_build_query($this->data);
        $this->headers = array('Access-Token:' . $this->accessToken);
        $return = $this->execCurl(true);
        return $return->list;
    }

    public function getAdList ($advertiserId) {
        $this->data = array('advertiser_id' => $advertiserId);
        $this->url = 'https://ad.oceanengine.com/open_api/2/ad/get/?' . http_build_query($this->data);
        $this->headers = array('Access-Token:' . $this->accessToken);
        $return = $this->execCurl(true);
        return $return;
    }

    public function getReport ($advertiserId, $date)
    {
        $this->data = array('advertiser_id' => $advertiserId, 'start_date' => $date, 'end_date' => $date, "group_by" => '["STAT_GROUP_BY_FIELD_ID"]');
//        https://ad.oceanengine.com/open_api/2/report/integrated/get/
        $this->url = 'https://ad.oceanengine.com/open_api/2/report/ad/get/?' . http_build_query($this->data);
        $this->headers = array('Access-Token:' . $this->accessToken);
        $return = $this->execCurl(true);
        return $return->list;
    }

    protected function execCurl ($isGet = FALSE)
    {
        $ch = curl_init();
        curl_setopt($ch, CURLOPT_URL, $this->url);
        //  curl_setopt($ch, CURLOPT_VERBOSE ,1);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER ,1);
        curl_setopt($ch, CURLOPT_HTTPGET ,true);
        curl_setopt($ch, CURLOPT_TIMEOUT, 10);
        if (!$isGet) {
            curl_setopt($ch, CURLOPT_POSTFIELDS, http_build_query($this->data));
        }
        curl_setopt($ch, CURLOPT_HTTPHEADER, $this->headers);
        $response = curl_exec($ch);
        curl_close($ch);
        $res = json_decode($response);
//        var_dump($res);
        if ($res->code != 0) {
            die($res->code . ":" . $res->message);
        }
        return $res->data;
    }
}
