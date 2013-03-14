<?php

require_once 'src.php';
Import::php("OpenM-DAO.DB.OpenM_DBFactory");

$dbFactory = new OpenM_DBFactory();

$db = $dbFactory->createFromProperties("config.properties");
echo $db->connect();
echo "<br/>";
var_dump($db);

//for($i=0;$i<1000;$i++)
//    $db->request("insert into test (text, nb) values ('plop$i',$i)");

$r = $db->request_ArrayList("select * from test");
$e = $r->enum();
while($e->hasNext())
    echo "- ".$e->next()->get("text")."  ".$e->current()->get("nb") ."<br>";
?>
