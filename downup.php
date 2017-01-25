<?php
//定义数据库信息
define ( "MYSQL_HOST", "mysql.example.com" );//数据库地址
define ( "MYSQL_PORT", "3306" );//数据库端口
define ( "MYSQL_USER", "user" );//数据库账号
define ( "MYSQL_PASS", "passwords" );//数据库密码
if ($_GET['data'] && ($_GET['token'] == "arduino")) {//此token必须和Arduino代码中的相同
        $con = mysql_connect(MYSQL_HOST.':'.MYSQL_PORT,MYSQL_USER,MYSQL_PASS); 
        $data = $_GET['data'];
        mysql_select_db("database_name", $con); //修改数据库名
        //从数据库中读取SWITCH的状态值
        $result = mysql_query("SELECT * FROM switch");
        while($arr = mysql_fetch_array($result)){
                if ($arr['ID'] == 1) {
                        $state = $arr['state'];
                }
        }
        $dati = date("h:i:sa");//获取时间
        $sql ="UPDATE sensor SET timestamp='$dati',data = '$data'
        WHERE ID = '1'";//更新相应的传感器的值
        if(!mysql_query($sql,$con)){
            die('Error: ' . mysql_error());//如果出错，显示错误
        }
        mysql_close($con);
        echo "{".$state."}";//返回状态值，加“{”是为了帮助Arduino确定数据的位置
}else{
        echo "Permission Denied";//请求中没有type或data或token错误时，显示Permission Denied
} 
?>
