<?php

    define("BDMYSQL",'crmgrupoexpansionv6'); //BDMYSQL CRM
    define("SERVIDORMYSQL",'3.18.61.114'); //SERVIDORMYSQL CRM
    define("PUERTOMYSQLVAR",'3306'); //PUERTOMYSQL CRM
    define("USUARIOMYSQL",'root'); //USUARIOMYSQL CRM
    define("CLAVEMYSQL",'P@r@n01d'); //CLAVEMYSQL CRM

    $con = @mysqli_connect(SERVIDORMYSQL, USUARIOMYSQL, CLAVEMYSQL, BDMYSQL);
    $con->set_charset("utf8");
    if (!$con) {
        die("imposible conectarse: ");
    }
    if (@mysqli_connect_errno()) {
        die("Connect failed: " . mysqli_connect_errno() . " : " . mysqli_connect_error());
    }
    
?>