<?php

/*session_start();*/
/*  for debug use only */
define('DEBUG', 'debug'); //comment the line before the commercial use!!

function database_connection(){
    $dbcon = include('dbconfig.php');
    $con = mysqli_connect($dbcon['host'], $dbcon['username'], $dbcon['password'], $dbcon['dbname'], $dbcon['port']);
    if (mysqli_connect_errno($con)){
        if(defined('DEBUG') || ($_SESSION['admin'] == true))
            $_SESSION['last_error'] =  "ERROR 500: Failed to connect to MySQL: ".mysqli_connect_error($con);
        else  $_SESSION['last_error'] = "ERROR 500: Something went wrong,please retry later or send us a message";
        header("Location: error.php");
        exit;
    }
    else return $con;
}
//

function get_information($table, $column, $columnKey, $key, $entireRow=false){
	$con = database_connection();
	$condition = generate_condition($columnKey,$key);
	$query = "SELECT ".$column." FROM ".$table." WHERE ".$condition.";";
	$res = mysqli_query($con,$query);
	if(!$res){
		if(defined('DEBUG') || ($_SESSION['admin'] == true))
			$_SESSION['last_error'] = "ERROR 501: Failed to execute the query: ".$query.PHP_EOL;
		else $_SESSION['last_error'] = "ERROR 501: Something went wrong,please retry later or send us a message";
		header("Location: error.php");
		exit;
	}
	$row = mysqli_fetch_assoc($res);
	// if entireRow is set, ignores column and return entire row:
	return  $entireRow? $row : $row[$column];
}


function set_information($table, $columnKey, $key, $columnToBeSet, $newValue, $numeric=false){
	$con = database_connection();
    $condition = generate_condition($columnKey,$key);
    $toupdate = $numeric? $newValue : "\"".$newValue."\"";
    $query = "UPDATE ".$table." SET ".$columnToBeSet." = ".$toupdate." WHERE ".$condition.";";
	$res = mysqli_query($con,$query);
	if(!$res){
		if(defined('DEBUG') || ($_SESSION['admin'] == true))
			$_SESSION['last_error'] = "ERROR 501: Failed to execute the query: ".$query.PHP_EOL;
		else $_SESSION['last_error'] = "ERROR 501: Something went wrong,please retry later or send us a message";
		header("Location: error.php");
		exit;
	}
}


function row_insertion($table, $toBeInsert){
	$con = database_connection();
	$query ="INSERT INTO ".$table." VALUES (";
	foreach ($toBeInsert as $value) {
	    if($value instanceof integer)
	        $query .= $value.",";
		else $query .= "\"".$value."\",";
	}
	$query = rtrim($query,',');
	$query .= ");";
	$res = mysqli_query($con,$query);
	if(!$res){
		if(defined('DEBUG') || ($_SESSION['admin'] == true))
			$_SESSION['last_error'] = "ERROR 501: Failed to execute the query: ".$query.PHP_EOL;
		else $_SESSION['last_error'] = "ERROR 501: Something went wrong,please retry later or send us a message";
		header("Location: error.php");
		exit;
	}
}

function row_deletion($table,$columnKey,$toBeDeleted){
    $con = database_connection();
    $condition = generate_condition($columnKey,$toBeDeleted);
    $query = "DELETE FROM ".$table." WHERE ".$condition.";";
    $res = mysqli_query($con,$query);
    if(!res){
        if(defined('DEBUG') || ($_SESSION['admin'] == true))
            $_SESSION['last_error'] = "ERROR 501: Failed to execute the query: ".$query.PHP_EOL;
        else $_SESSION['last_error'] = "ERROR 501: Something went wrong,please retry later or send us a message";
        header("Location: error.php");
        exit;
    }
}



function get_information_listed($table, $column, $columnKey, $key, $like=false){
    $con = database_connection();
    $cond = $like? " like \"%".$key."%\";" : " = \"".$key."\";";
    $query = "SELECT ".$column." FROM ".$table." WHERE ".$columnKey.$cond;
    $res = mysqli_query($con,$query);
    if(!$res){
        if(defined('DEBUG') || ($_SESSION['admin'] == true))
            $_SESSION['last_error'] = "ERROR 501: Failed to execute the query: ".$query.PHP_EOL;
        else $_SESSION['last_error'] = "ERROR 501: Something went wrong,please retry later or send us a message";
        header("Location: error.php");
        exit;
    }
    $array = array();
    $index = 0;
    while( $row = mysqli_fetch_assoc($res)){
       $array[$index] = $row[$column];
       $index++;
    }
    return  $array;
}



function search_items($resultColumn,$table,$columnMatch,$search,$orderby,$direction,$min_price,$max_price){
    $con = database_connection();
    $query = "SELECT ".$resultColumn." FROM ".$table." WHERE MATCH(";
    foreach ($columnMatch as $column)
        $query .= $column.",";
    $query = substr($query,0,-1);
    $query .= ") AGAINST('+".$search."' IN NATURAL LANGUAGE MODE) AND price >= ".$min_price." AND price <= ".$max_price;

    if($orderby === false)
        $query .=";";
    else{
        $query .= " ORDER BY ".$orderby." ".$direction.";";
    }
    $res = mysqli_query($con,$query);
    if(!$res){
        if(defined('DEBUG') || ($_SESSION['admin'] == true))
            $_SESSION['last_error'] = "ERROR 501: Failed to execute the query: ".$query.PHP_EOL;
        else $_SESSION['last_error'] = "ERROR 501: Something went wrong,please retry later or send us a message";
        header("Location: error.php");
        exit;
    }
    $array = array();
    $index = 0;
    while($row = mysqli_fetch_assoc($res)){
        $array[$index] = $row[$resultColumn];
        $index++;
    }
    return  $array;

}

function generate_condition($column , $key){
    $condition = "";
    $index = 0;
    if(is_array($column) && is_array($key)){
        foreach($column as $singleColumn) {
            $condition .= $singleColumn . " = \"" . $key[$index] . "\" AND ";
            $index++;
        }
        $condition = substr($condition,0,-4);
        //$condition = rtrim($condition,"AND");
    }
    else $condition = $column." = \"".$key."\"";
return $condition;
}


?>