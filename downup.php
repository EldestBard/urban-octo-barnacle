<?php  
if ($_GET['data'] && ($_GET['token'] == "arduino")) {//token必须和Arduino端的相同
        $con = mysql_connect(SAE_MYSQL_HOST_M.':'.SAE_MYSQL_PORT,SAE_MYSQL_USER,SAE_MYSQL_PASS); 
        $data = $_GET['data'];
        //$data1 = $_GET['data1'];
        mysql_select_db("app_wxxcc", $con); //数据库名
        //从数据库中读取SWITCH的状态值
        $result = mysql_query("SELECT * FROM switch");
        $result1 = mysql_query("SELECT * FROM switch1");
        while($arr = mysql_fetch_array($result)){
                if ($arr['ID'] == 1) {
                        $state = $arr['state'];
                }
        }
        while($arr = mysql_fetch_array($result1)){
                if ($arr['ID'] == 1) {
                        $state1 = $arr['state'];
                }
        }
        $dati = date("h:i:sa");//获取时间
        $sql ="UPDATE sensor SET timestamp='$dati',data = '$data'
        WHERE ID = '1'";//更新相应的传感器的值
        /*$sql ="UPDATE sensor1 SET timestamp='$dati',data = '$data1'
        WHERE ID = '1'";*/
        if(!mysql_query($sql,$con)){
            die('Error: ' . mysql_error());//如果出错，显示错误
        }
        mysql_close($con);
        echo "{".$state."}";//返回状态值，加“{”是为了帮助Arduino确定数据的位置
}else{
        echo "Permission Denied";//请求中没有type或data或token错误时，显示Permission Denied
} 
?>
