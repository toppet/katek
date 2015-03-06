<?php

class DB{
    protected static $connection;
    
    public function connect(){
        if(!isset(self::$connection)){
            self::$connection = new mysqli('localhost','root','','gyartas');
        }
        
        if(self::$connection === false){
            return false;
        }
        return self::$connection;
    }
    public function query($query){
        $connection = $this -> connect();
        $result = $connection -> query($query);
        return $result;
    }
    
    public function select($query){
        $rows = array();
        $result = $this->query($query);
        if($result === false){
            return false;
        }
        while ($row = $result ->fetch_row()){
            $rows[] = $row;
        }
        return $rows;
    }
    
    public function error(){
        $connection = $this ->connect();
        return $connection ->error;
    }
    
    public function qoute($value){
        $connection = $this ->connect();
        return "'".$connection -> real_escape_string($value)."'";
    }
}

$db = new DB;

$rows = $db -> select("Select * FROM gyartas.termekek");
//var_dump($rows);
$product_name = $rows[0][3];

echo "<table style='border:2px solid #000;'>";
echo "<tr><th>pid</th><th>smt</th><th>product_name</th></tr>";
for($i=0;$i<count($rows);$i++){
    echo "<tr>";
    //echo count($rows[$i]);
    for($j=0;$j<count($rows[$i]);$j++){
        echo "<td>".$rows[$i][$j]."</td>";
    }
    echo "</tr>";
}
echo "</table>";
//echo count($rows);
echo "<p>Product name:".$product_name ." </p>";
?>

<html>
<head>
    
<style type='text/css'>
    table{
        border-collapse:collapse;
        text-align:center;
    }
    table,td,th{
        border:2px solid #000;
        padding:5px;
    }
</style>
</head>
    <body>
    
    </body>

</html>