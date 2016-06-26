<?php
//第二个功能:
//1 用户发送位置信息,将用户的位置信息保存到数据库
//2 用户发送关键字,调用百度地图api去查询该区域内的符合该关键字的结果
define('AK','6d3db534af92f194e9d2e5242101f80b');
//>>1 获取用户发送的位置信息
$postXml = file_get_contents('php://input');//访问请求的原始数据的只读流(获取微信服务器POST过来的xml数据)
file_put_contents('2.xml',$postXml);//保存微信发送的xml数据,方便调试
$simpleXml = simplexml_load_string($postXml);//使用simplexml加载xml数据
//解析xml数据
$ToUserName = (string)$simpleXml->ToUserName;
$FromUserName = (string)$simpleXml->FromUserName;
$MsgType = (string)$simpleXml->MsgType;
//链接数据库
mysql_connect('localhost','itsource','itsource');
mysql_select_db('itsource');
mysql_query("set names utf8");

// 用户发送位置信息
if($MsgType == 'location'){
	$Location_X = (string)$simpleXml->Location_X;
	$Location_Y = (string)$simpleXml->Location_Y;
	$Scale = (string)$simpleXml->Scale;
	$Label = (string)$simpleXml->Label;

//>>2 将用户的位置信息保存到数据库

	$sql = "INSERT INTO `wx_location` VALUES (NULL,'{$FromUserName}',{$Location_X},{$Location_Y},'{$Label}')";
	$re = mysql_query($sql);

	ob_start();
	//回复一个文本信息:位置信息已保存,请发送关键字查询
	$Content = '位置信息已保存,请发送关键字查询';
	require 'text.xml';
	$xml = ob_get_contents();
	file_put_contents('location.xml',$xml);
}elseif($MsgType == 'text'){
	//用户发送文本信息
	$Content = (string)$simpleXml->Content;//获取用户发送关键字
	//判断数据表里面是否有该用户的位置信息
	$sql = "SELECT * FROM `wx_location` WHERE `open_id`='{$FromUserName}' LIMIT 1";
	$re = mysql_query($sql);
	$rows = mysql_fetch_assoc($re);
	if($rows){
		//如果有用户的位置信息
		$row = $rows;//获取用户的位置信息


		//调用百度地图api
		$url = "http://api.map.baidu.com/place/v2/search?query={$Content}&page_size=10&page_num=0&scope=1&location={$row['x']},{$row['y']}&radius=2000&output=xml&ak=".AK;
//		file_put_contents('url.xml',$url);
		//解析百度地图api返回的xml数据
		$baiduApi = simplexml_load_file($url);
		$news = array();
		//循环获取百度地图api返回的结果
		foreach($baiduApi->results->result as $result)
		{
			//将搜索结果保存到数组中
			$news[] = array(
				'name'=>(string)$result->name,
				'address'=>(string)$result->address,
				'uid'=>(string)$result->uid,
				'x'=>$result->location->lng,
				'y'=>$result->location->lat
			);
		}
		ob_start();
		require 'news.xml';
		$content = ob_get_contents();
		file_put_contents('api.xml',$content);
//http://api.map.baidu.com/place/v2/search?query=%E9%85%92%E5%BA%97&page_size=10&page_num=0&scope=1&location=39.915,116.404&radius=2000&output=xml&ak=6d3db534af92f194e9d2e5242101f80b
	}else{
		//如果没有:提示 请先发送位置信息
		$Content = '请先发送位置信息';
		require 'text.xml';
	}

}





//$result = mysql_fetch_assoc($re);




