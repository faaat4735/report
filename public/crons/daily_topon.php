<?php

require_once __DIR__ . '/../init.inc.php';
// 获取topon变现数据

$startDate = strtotime('-2 day');
$endDate =  max(strtotime('-30 day'), strtotime('20200815'));


// 获取巨量引擎推广数据
$topon = new \Core\Topon();
$i = 1;
while (true) {
    $toponList = $topon->getReport(date('Ymd',$startDate));
    $ltv = 'ltv_day_' . $i;
    foreach ($toponList as $toponInfo) {
        if (isset($toponInfo->channel)) {
            $channle = $toponInfo->channel;
            $info = explode('_', $channle);
            $campaignId = ('qh' == $info[0]) ? ($info[2] ?? '') : ($info[1] ?? '');
            if ($campaignId) {
//                var_dump($toponInfo);
//                echo $ltv . ':' . $toponInfo->$ltv . PHP_EOL;
                $sql = 'REPLACE INTO r_topon SET report_date = ?, ad_id = ?, ' . $ltv . ' = ?, new_user = ?';
                $locator->db->exec($sql, date('Y-m-d',$startDate), $campaignId, $toponInfo->$ltv, $toponInfo->new_user);
                // 保存topon变现数据
//                var_dump($toponInfo);
            }
        }
    }
    $startDate = strtotime('-1 day', $startDate);
//    echo $startDate;
//    echo $endDate;
//    exit;
    if ($startDate <= $endDate) {
        break;
    }
    $i++;
}
exit;


// 聚合 推广和变现数据
