<?php

require_once __DIR__ . '/../init.inc.php';
// 获取topon变现数据

$startDate = isset($argv[1]) ? strtotime($argv[1]) : strtotime('-2 day');
$endDate =  max(strtotime('-30 day'), strtotime('20200815'));

var_dump(date('Y-m-d',$startDate));

// 获取巨量引擎推广数据
$topon = new \Core\Topon();
$i = 1;
while (true) {
    $reportDate = date('Y-m-d',$startDate);

    $sql = 'SELECT ad_id, convert_cost, `convert`, advertiser_id, campaign_id FROM r_ocean WHERE report_date = ?';
    $reportList = $locator->db->getAll($sql, $reportDate);
    $roi = 'roi_' . $i;
    $ltv = 'ltv_day_' . $i;
    foreach ($reportList as $reportInfo) {
        $sql = 'SELECT new_user, ' . $ltv . ' FROM r_topon WHERE report_date = ? AND ad_id = ?';
        $toponInfo = $locator->db->getRow($sql, $reportDate, $reportInfo['ad_id']);
        if ($toponInfo) {
            $roiVal = $reportInfo['convert_cost'] ? ($toponInfo[$ltv] / $reportInfo['convert_cost']) : 0;
            $sql = 'SELECT COUNT(ad_id) FROM r_report WHERE report_date = ? AND ad_id = ?';
            if ($locator->db->getOne($sql, $reportDate, $reportInfo['ad_id'])) {
                $sql = 'UPDATE r_report SET ' . $roi . '  = ?, new_user_topon = ?, new_user_ocean = ? WHERE report_date = ? AND ad_id = ?';
                $locator->db->exec($sql, $roiVal, $toponInfo['new_user'], $reportInfo['convert'], $reportDate, $reportInfo['ad_id']);
            } else {
                $sql = 'INSERT INTO r_report SET report_date = ?, advertiser_id = ?, campaign_id = ?, ad_id = ?, ' . $roi . ' = ?, new_user_topon = ?, new_user_ocean = ?, new_user_gap = ? ';
                $locator->db->exec($sql, $reportDate, $reportInfo['advertiser_id'], $reportInfo['campaign_id'], $reportInfo['ad_id'], $roiVal, $toponInfo['new_user'], $reportInfo['convert'],  $reportInfo['convert'] ? ROUND(1 - $toponInfo['new_user'] / $reportInfo['convert'], 4) : 0);
            }
        }
    }
    $startDate = strtotime('-1 day', $startDate);
    if ($startDate <= $endDate) {
        break;
    }
    $i++;
}
echo 'done';
exit;


// 聚合 推广和变现数据
