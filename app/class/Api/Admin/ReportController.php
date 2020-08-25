<?php

namespace Api\Admin;

use Core\Controller;

Class ReportController extends Controller {

    // app列表
    public function roiAction() {
        $whereArr = array();
        $dataArr = array();
        if (isset($_POST['advertiser_id']) && $_POST['advertiser_id']) {
            $whereArr[] = 'advertiser_id = :advertiser_id';
            $dataArr['advertiser_id'] = $_POST['advertiser_id'];
        }
        if (isset($_POST['ad_id']) && $_POST['ad_id']) {
            $whereArr[] = 'ad_id = :ad_id';
            $dataArr['ad_id'] = $_POST['ad_id'];
        }
        if (isset($_POST['dateRange']) && $_POST['dateRange']) {
            $whereArr[] = 'report_date >= :date_start';
            $whereArr[] = 'report_date <= :date_end';
            $dataArr['date_start'] = $_POST['dateRange'][0];
            $dataArr['date_end'] = $_POST['dateRange'][1];
        }
        if (isset($_POST['ad_roi_val']) && $_POST['ad_roi_val'] && isset($_POST['ad_roi']) && $_POST['ad_roi']) {
            $whereArr[] = $_POST['ad_roi'] . ' >= :ad_roi';
            $dataArr['ad_roi'] = $_POST['ad_roi_val'];
        }
        if (!$whereArr) {
            $whereArr[] = 'report_date > ' . date('Y-m-d', strtotime('-7 day'));
        }
//var_dump($whereArr);
        $where = 'WHERE ' . implode(' AND ', $whereArr);

        $sql = "SELECT COUNT(*) FROM r_report " . $where;
        $totalCount = $this->locator->db->getOne($sql, $dataArr);

        $list = array();
        if ($totalCount) {
            $sql = "SELECT *, ROUND(new_user_gap * 100, 2) rate FROM r_report $where ORDER BY report_date DESC, new_user_ocean DESC LIMIT " . $this->page;
            $list = $this->locator->db->getAll($sql, $dataArr);
        }
        return array(
            'totalCount' => (int) $totalCount,
            'list' => $list
        );
    }
}