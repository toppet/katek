<?php
/*
    * @author Topos Péter <topospeti@gmail.com>
    * @copyright 2015 Katek Hungary Kft.
    * @license http://www.php.net/license/3_01.txt PHP License 3.01
*/
ini_set('default-charset','UTF-8');
header('Content-Type: text/html; charset=utf-8');

class Person{
    private $name;
    private $age;
    private $birthyear;
    
    public function __construct($_name,$_age){
        $this->name=$_name;
        $this->age=$_age;
        $this->birthyear = date("Y",strtotime('-'.$_age.' year'));
    }
    
    public function happyBirthday(){
        ++$this->age;
        return "<br/>Happy ".$this->age.". Birthday ".$this->name." (született: ". $this->birthyear.")!<br/>";
    }
    
    public function getAge(){
        return $this->age;
    }
    
    public function __toString(){
        return "Név: ".$this->name."<br/> Kor: ".$this->age."<br/>Születési év: ".$this->birthyear."<br/>";
    }
}

$person1 = new Person("Feri",44);
$person2 = new Person("Béla",65);

echo $person1;
echo "----<br/>";
echo $person2;

echo $person2->happyBirthday();
?>