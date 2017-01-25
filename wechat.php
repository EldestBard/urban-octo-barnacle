<?php
 
//错误日志
function echo_server_log($log){
        file_put_contents("log.txt", $log, FILE_APPEND);
}
 
//在微信上设置的TOKEN必须与下方一致
define ( "TOKEN", "weixin" );
 
//验证微信公众平台签名
function checkSignature() {
        $signature = $_GET ['signature'];
        $nonce = $_GET ['nonce'];
        $timestamp = $_GET ['timestamp'];
        $tmpArr = array ($nonce, $timestamp, TOKEN );
        sort ( $tmpArr );
         
        $tmpStr = implode ( $tmpArr );
        $tmpStr = sha1 ( $tmpStr );
        if ($tmpStr == $signature) {
                return true;
        }else{
                return false;
        }
}
if(false == checkSignature()) {
        exit(0);
}
 
//接入时验证接口
header('content-type:text');
$echostr = $_GET ['echostr'];
if($echostr) {
        echo $echostr;
        exit(0);
}
 
//获取POST数据
function getPostData() {
        $data = $GLOBALS['HTTP_RAW_POST_DATA'];
        return        $data;
}
$PostData = getPostData();
 
//验错
if(!$PostData){
        echo_server_log("wrong input! PostData is NULL");
        echo "wrong input!";
        exit(0);
}
 
//装入XML
$xmlObj = simplexml_load_string($PostData, 'SimpleXMLElement', LIBXML_NOCDATA);
 
//验错
if(!$xmlObj) {
        echo_server_log("wrong input! xmlObj is NULL\n");
        echo "wrong input!";
        exit(0);
}
 
//准备XML
$fromUserName = $xmlObj->FromUserName;
$toUserName = $xmlObj->ToUserName;
$msgType = $xmlObj->MsgType;
 
 
if($msgType == 'voice') {//判断是否为语音
        $content = $xmlObj->Recognition;
}elseif($msgType == 'text'){
        $content = $xmlObj->Content;
}else{
        $retMsg = '只支持文本和语音消息';
}
 
if (strstr($content, "雾霾")) {
        $con = mysql_connect(SAE_MYSQL_HOST_M.':'.SAE_MYSQL_PORT,SAE_MYSQL_USER,SAE_MYSQL_PASS); 
        mysql_select_db("app_wxxcc", $con);//修改数据库名
 
        $result = mysql_query("SELECT * FROM sensor");
        while($arr = mysql_fetch_array($result)){
          if ($arr['ID'] == 1) {
                  $tempr = $arr['data'];
          }
        }
        mysql_close($con);
 
    $retMsg = "当前雾霾传感器读数为".$tempr;
}else if (strstr($content, "开灯")) {
        $con = mysql_connect(SAE_MYSQL_HOST_M.':'.SAE_MYSQL_PORT,SAE_MYSQL_USER,SAE_MYSQL_PASS); 
 
 
        $dati = date("h:i:sa");
        mysql_select_db("app_wxxcc", $con);//修改数据库名
 
        $sql ="UPDATE switch SET timestamp='$dati',state = '1'
        WHERE ID = '1'";//修改开关状态值
 
        if(!mysql_query($sql,$con)){
            die('Error: ' . mysql_error());
        }else{
                mysql_close($con);
                $retMsg = "好的主人";
        }
}else if (strstr($content, "关灯")) {
        $con = mysql_connect(SAE_MYSQL_HOST_M.':'.SAE_MYSQL_PORT,SAE_MYSQL_USER,SAE_MYSQL_PASS); 
 
 
        $dati = date("h:i:sa");
        mysql_select_db("app_wxxcc", $con);//修改数据库名
 
        $sql ="UPDATE switch SET timestamp='$dati',state = '0'
        WHERE ID = '1'";//修改开关状态值
 
        if(!mysql_query($sql,$con)){
            die('Error: ' . mysql_error());
        }else{
                mysql_close($con);
                $retMsg = "好的主人";
        }        
}else if (strstr($content, "打开1")) {
        $con = mysql_connect(SAE_MYSQL_HOST_M.':'.SAE_MYSQL_PORT,SAE_MYSQL_USER,SAE_MYSQL_PASS); 
 
 
        $dati = date("h:i:sa");
        mysql_select_db("app_wxxcc", $con);//修改数据库名
 
        $sql ="UPDATE switch1 SET timestamp='$dati',state = '1'
        WHERE ID = '1'";//修改开关状态值
 
        if(!mysql_query($sql,$con)){
            die('Error: ' . mysql_error());
        }else{
                mysql_close($con);
                $retMsg = "好的主人";
		}
}else if (strstr($content, "关闭1")) {
        $con = mysql_connect(SAE_MYSQL_HOST_M.':'.SAE_MYSQL_PORT,SAE_MYSQL_USER,SAE_MYSQL_PASS); 
 
 
        $dati = date("h:i:sa");
        mysql_select_db("app_wxxcc", $con);//修改数据库名
 
        $sql ="UPDATE switch1 SET timestamp='$dati',state = '0'
        WHERE ID = '1'";//修改开关状态值
 
        if(!mysql_query($sql,$con)){
            die('Error: ' . mysql_error());
        }else{
                mysql_close($con);
                $retMsg = "好的主人";
        }
}else{
        $retMsg = "输入“雾霾”获取当前雾霾传感器读数，“开灯”、“关灯”可改变继电器状态。";
}
 
//装备XML
$retTmp = "<xml>
                <ToUserName><![CDATA[%s]]></ToUserName>
                <FromUserName><![CDATA[%s]]></FromUserName>
                <CreateTime>%s</CreateTime>
                <MsgType><![CDATA[text]]></MsgType>
                <Content><![CDATA[%s]]></Content>
                <FuncFlag>0</FuncFlag>
                </xml>";
$resultStr = sprintf($retTmp, $fromUserName, $toUserName, time(), $retMsg);
 
//反馈到微信服务器
echo $resultStr;
?>
