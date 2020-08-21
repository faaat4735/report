<?php

//topon api https://docs.toponad.com/#/zh-cn/android/open-api/report_api_cn
namespace Core;


class Topon
{

    protected $url = 'https://openapi.toponad.com/v3/ltvreport';
    protected $body = "{\"startdate\":20200808,\"enddate\":20200810,\"limit\":120,\"group_by\":[\"date\", \"channel\"],\"metric\":[\"dau\", \"arpu\", \"request\", \"fillrate\", \"impression\", \"click\", \"ctr\", \"ecpm\", \"revenue\", \"request_api\", \"fillrate_api\", \"impression_api\"],\"start\":0,\"app_id\":\"a5efc30a3c896f\"}";
    protected $publisherKey = "01821c8e7fbe79c9ddc6298fa3c55312b8ffa367";
    protected $httpMethod = "POST";
    protected $contentType = "application/json";
    protected $publisherTimestamp = 0;//intval(microtime(true) * 1000);

//error_reporting(0);
//$demoUrl = "https://openapi.toponad.com/v1/fullreport";
//$body = "{}";
//$publisherKey = "Your publisherKey";
//$httpMethod = "POST";
//$contentType = "application/json";
//$publisherTimestamp = intval(microtime(true) * 1000);

    public function __construct()
    {
        $this->publisherTimestamp = intval(microtime(true) * 1000);
//        var_dump($this->httpPostJson($this->url, $this->body, $lastHeader));
    }

    public function getReport ($date) {
//        $this->body = "{\"startdate\":" . $date .",\"enddate\":" . $date . ",\"group_by\":[\"channel\"]" . "}";
        $data = array('start_date' => (int) $date, 'end_date' => (int) $date, 'group_by' => array('channel'), 'metric' => array('all'));
//        $this->body = str_replace('"', '\\"', json_encode($data));
        $this->body = json_encode($data);
//        var_dump("{\"startdate\":" . $date .",\"enddate\":" . $date . ",\"group_by\":[\"channel\"]" . "}");
        $headerArrs = ['X-Up-Timestamp' => $this->publisherTimestamp, 'X-Up-Key' => $this->publisherKey];

        $contentMd5 = strtoupper(md5($this->body));
//        var_dump($contentMd5);

        $t = parse_url($this->url);
        $resource = $t["path"];

        $publisherSignature = $this->signature($this->httpMethod, $contentMd5, $this->contentType, $this->headerJoin($headerArrs), $resource);

        $headerArrs['Content-Type'] = $this->contentType;
        $headerArrs['X-Up-Signature'] = $publisherSignature;

        $lastHeader = [];
        foreach ($headerArrs as $k => $v)
        {
            $lastHeader[] = $k . ":" . $v;
        }
        $res = $this->httpPostJson($this->url, $this->body, $lastHeader);
//        var_dump($res);
        return $res->records;
    }

    protected function httpPostJson($url, $jsonStr, $header = array())
    {
//        var_dump($header);
        $ch = curl_init();
        curl_setopt($ch, CURLOPT_POST, 1);
        curl_setopt($ch, CURLOPT_URL, $url);
        curl_setopt($ch, CURLOPT_POSTFIELDS, $jsonStr);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
        curl_setopt($ch, CURLOPT_HTTPHEADER, $header);
        $response = curl_exec($ch);
        $value_array = json_decode($response);
//        $value_array = json_decode(json_encode(simplexml_load_string($response, 'SimpleXMLElement', LIBXML_NOCDATA)), true);
        curl_close($ch);
        return $value_array;
    }

    protected function headerJoin($headers = [])
    {
        $headerKeys = [
            "X-Up-Timestamp",
            "X-Up-Key"
        ];
        sort($headerKeys, SORT_STRING);
        $ret = [];
        foreach ($headerKeys as $v) {
            if ($headers[$v]) {
                $ret[] = $v . ":" . strval($headers[$v]);
            }
        }
        return implode($ret, "\n");
    }

    protected function signature($httpMethod, $contentMD5, $contentType, $headerString, $resource)
    {
        $stringSection = [
            $httpMethod,
            $contentMD5,
            $contentType,
            $headerString,
            $resource
        ];
        $stringSection = implode($stringSection, "\n");
        return strtoupper(md5($stringSection));
    }
}
