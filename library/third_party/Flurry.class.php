<?php

/**
 * 参数说明：<br>
 * STARTDATE 格式：YYYY-MM-DD
 * ENDDATE 格式：YYYY-MM-DD
 * VERSIONNAME 可选参数，由sdk版本确定
 * COUNTRY 可选参数，默认为'all'，详细请参考 http://support.flurry.com/index.php?title=Countries
 * GROUPBY 可选参数，默认为天（DAYS，类型为ActiveUsersByWeek和ActiveUsersByMonth除外），参数列表：DAYS, WEEKS, or MONTHS
 * 
 * 返回值示例：
 *   SimpleXMLElement Object
 *   (
 *        [@attributes] => Array
 *            (
 *                 [endDate] => 2014-10-10
 *                 [metric] => ActiveUsersByDay
 *                 [startDate] => 2014-10-01
 *                 [generatedDate] => 10/10/14 2:40 AM
 *                 [version] => 1.0
 *             )
 *         [day] => Array
 *             (
 *                 [0] => SimpleXMLElement Object
 *                     (
 *                         [@attributes] => Array
 *                             (
 *                                 [date] => 2014-10-01
 *                                 [value] => 19
 *                             )
 *                     )
 *              )
 *   )
 *
 * @link       http://blog.protector.com.br
 * @author     Renan Protector
 * @modified   etong <zhoufeng@wondershare.cn>
 * @version    $ids Flurry.class.ph 2014/10/10 16:51 v1$
 **/
class Flurry {
    
    /**
     * @var string Flurry的accessToken
     */
	public $apiAccess = '';
    
    /**
     * @var string Flurry对应应用的key
     */
	public $appKey = '';
    
    /**
     * @var array 允许获取的数据类型
     */
    public $allowType = array(
        "ActiveUsers", //独立用户数
        "ActiveUsersByWeek", //每周独立用户 
        "ActiveUsersByMonth", //每月独立用户
        "NewUsers", //每日新用户（独立用户）
        "MedianSessionLength", //每日中间回话时长 
        "AvgSessionLength", // 每日平均回话时长
        "Sessions", //每日用户访问总次数
        "RetainedUsers", //每天的活跃总用户数。 
        "PageViews", //每天应用的活跃用户总用户数。 
        "AvgPageViewsPerSession" //用户的平均页面访问量
    );

    public function __construct($api, $app){
		$this->apiAccess = $api;
		$this->appKey = $app;
    }
    
    /**
     * 获取详细数据
     * 
     * @param string $metricName 类型名称
     * @param string $startDate 起始日期
     * @param string $endDate 结束日期
     * @param string $country 国家或地区，默认为空
     * @param string $versionName 版本名称，默认为空
     * @return Object Xml
     */
	public function getMetric($metricName, $startDate, $endDate, $country=FALSE, $versionName=FALSE){
        // 对于异常请求，直接返回空数据
        if(FALSE == in_array($metricName, $this->allowType)){
            return '';
        }
        $url = "http://api.flurry.com/appMetrics/";
		$URLRequest = $url.$metricName.'?apiAccessCode='.$this->apiAccess.'&apiKey='.$this->appKey;
        $URLRequest .= '&startDate='.$startDate.'&endDate='.$endDate;
        if ($country){
            $URLRequest .= "&country=$country";
        }
			
        if ($versionName){
            $URLRequest .= "&versionName=$versionName";
        }

		$data = array();
		$config = array(
			'http' => array(
				'header' => 'Accept: application/xml',
				'method' => 'GET',
				'ignore_errors' => true
			)
		);
        
		$stream = stream_context_create($config);
		$xml = file_get_contents($URLRequest, false, $stream);
		$metricValues = new SimpleXMLElement($xml);
		return $metricValues;
	}

    /**
     * 获取全部数据,<br>
     * 特别注意，必须要强制间隔时间为1s，因为flurry每个app限制1s为一次请求
     * 
     * @param string $startDate 起始日期
     * @param string $endDate 结束日期
     * @param string $country 国家或地区，默认为空
     * @param string $versionName 版本名称，默认为空
     * @return array
     */
	public function getAllMetrics($startDate, $endDate, $country=FALSE, $versionName=FALSE){
		$metrics = array();
        foreach($this->allowType as $type){
            // print_r($type);exit;
            $metrics[$type] = $this->getMetric($type, $startDate, $endDate, $country, $versionName);
            sleep(1);
        }
		return $metrics;
	}
    
    /**
     * 依据类型名称获取数据,<br>
     * 特别注意，必须要强制间隔时间为1s，因为flurry每个app限制1s为一次请求
     * 
     * @param $typeArray 类型数组
     * @param $startDate 起始日期
     * @param $endDate 结束日期
     * @param $country 国家或地区，默认为空
     * @param $versionName 版本名称，默认为空
     * @return array
     */
	public function getMetricsByType(array $typeArray, $startDate, $endDate, $country=FALSE, $versionName=FALSE){
		$metrics = array();
        if(is_array($typeArray) && FALSE == empty($typeArray)){
            foreach($typeArray as $type){
                $metrics[$type] = $this->getMetric($type, $startDate, $endDate, $country, $versionName);
                sleep(1);
            }
        }
		return $metrics;
	}
}
?>