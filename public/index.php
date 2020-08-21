<?php

use Core\AutoLoad;
use Core\LoadUrl;

require_once './init.inc.php';
$params = $locator->params;
$loadUrl = new LoadUrl($locator);

$result = $loadUrl->dispatch($params['_controller'], $params['_action']);
//var_dump($result);

$return = array();
switch ($params['_type']) {
    case 'admin':
        if (isset($result['status'])) {
            $return = $result;
        } else {
            $return = array('status' => 'ok', 'data' => $result, 'msg' => '');
        }
        break;
    case 'api':
        if (is_array($result)) {
            $return = array('code' => 200, 'data' => $result, 'msg' => '');
        } else {
            //opt 返回错误码msg列表
            //2** 通用错误 3** home 4** bouns 5** my 6** system
            //201 token 错误  202 访问错误  203 认证失败
            $array = array(201 => '无效token', 202 => '访问失败，请稍后再试', 203 => '访问失败，请稍后再试', 204 => '抱歉您的账户已被冻结', 301 => '位置已满', 302 => '金币不足购买', 303 => '领取失败', 304 => '达到每日上限', 305 => '当前狗狗不能放入背包', 401 => '您已填写过邀请码', 402 => '邀请码无效，请重新输入', 403 => '验证码无效，请填写比您先注册的用户的邀请码', 501 => '抱歉，您的余额暂未达到提现门槛', 502 => '新用户提现专享', 503 => '认证失败', '601' => '登陆失败');
            $return = array('code' => $result, 'data' => (object) array(), 'msg' => $array[$result] ?? '');
        }
        if (DEBUG_MODE) {
            //add api log
            $logFile = LOG_DIR . 'access/' . date('Ymd') . '/';
            if (!is_dir($logFile)) {
                mkdir($logFile, 0755, true);
            }
            file_put_contents($logFile . 'access_' . date('H') . '.log', date('Y-m-d H:i:s') . '|' . ($_SERVER['HTTP_ACCESSTOKEN'] ?? ' ') .  '|' . json_encode($params) . '|' . json_encode($return, JSON_UNESCAPED_UNICODE) . PHP_EOL, FILE_APPEND);
        }
        break;
}
echo json_encode($return);