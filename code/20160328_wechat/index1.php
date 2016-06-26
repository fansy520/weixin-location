<?php
//第一个功能:用户发送什么内容,我们就返回相应内容
//php://input 访问请求的原始数据的只读流(获取微信服务器POST过来的xml数据)
$postXml = file_get_contents('php://input');
//var_dump($postXml);
//保存到xml文件里面
file_put_contents('post.xml',$postXml);
//解析xml
$simpleXml = simplexml_load_string($postXml);

//获取
$ToUserName = (string)$simpleXml->ToUserName;
$FromUserName = (string)$simpleXml->FromUserName;

$MsgType = (string)$simpleXml->MsgType;
$Content = (string)$simpleXml->Content;
ob_start();//打开输出缓冲区

require 'text.xml';
//将返回给微信服务器的xml内容保存到文件
$xml = ob_get_contents();//获取输出缓冲区的内容
file_put_contents('xml.xml',$xml);//将输出给微信服务器的xml保存到文件

