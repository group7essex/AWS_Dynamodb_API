<?php

//header("refresh: 3;");

require ("aws/vendor/autoload.php");

use Aws\DynamoDb\DynamoDbClient;
$client = DynamoDbClient::factory(array(
		'key'    => 'your.key',
		'secret' => 'your.secret.code',
		'region' => 'your.region'
));

//-------------------------------------------------------------------------
// Funtion to scan Amazon DynamoDB Database
//-------------------------------------------------------------------------

function scan_db($client, $t_nam, $new_ar){
  //  $per_arr = scan_pnews($client, $t_nam);
    $iterator = $client->getScanIterator(array(
        'TableName' => $t_nam
    ), array(
        'limit' => 200
    ));

    foreach ($iterator as $item) {
        if ($item['Link_ID']['S']== $new_ar){
            $tts = $item['Title']['S'];
            echo "<div style ='font:19px/18px Arial,tahoma,sans-serif;color:darkblue'> $tts</div>";
            echo "<br>";
            echo "<br>";
            echo $item['Date']['S'];
            echo "<br>";
            echo "<a href=".$item['News_URL']['S'].">Source: The Rabbit Newspaper</a>";
            echo "<br>";
            echo "<br>";
            echo "<a href=".$item['News_URL']['S']."><img src=".$item['Image_URL']['S']." width=".'"938" '."  height=".'"535"'."/></a>";
            echo "<br>";
            echo "<br>";
            echo "<p align="."justify".">";
            echo "<div style ='font:17px/18px Arial,tahoma,sans-serif;color:'>".$item['News']['S']."</div>";
            echo "</p>";
            echo "<br>";
            echo "<a href=".$item['News_URL']['S'].">Read More ...</a>";
            echo "<br>";
            echo "</br>";
            echo "<hr />";
            echo "<br>";
            break;
        }
    }

}

//-------------------------------------------------------------------------
// Funtion to scan Amazon DynamoDB PersonalNews Table
//-------------------------------------------------------------------------

function scan_pnews($client, $t_nam, $per_ar){
    $iterator = $client->getScanIterator(array(
        'TableName' => 'PERSONALNEWS'
    ), array(
        'limit' => 200
    ));

    foreach ($iterator as $item) {
        if (($item['Link_ID']['S']== $per_ar) && ($item['Type']['S'] == $t_nam)){
            $tts = $item['Title']['S'];
            echo "<div style ='font:19px/18px Arial,tahoma,sans-serif;color:darkblue'> $tts</div>";
            echo "<br>";
            echo "<br>";
            echo $item['Date']['S'];
            echo "<br>";
            echo "<a href=".$item['News_URL']['S'].">Source: User Entry</a>";
            echo "<br>";
            echo "<br>";
            echo "<a href=".$item['News_URL']['S']."><img src=".$item['Image_URL']['S']." width=".'"938" '."  height=".'"535"'."/></a>";
            echo "<br>";
            echo "<br>";
            echo "<p align="."justify".">";
            echo "<div style ='font:17px/18px Arial,tahoma,sans-serif;color:'>".$item['News']['S']."</div>";
            echo "</p>";
            echo "<br>";
            echo "<a href=".$item['News_URL']['S'].">Read More ...</a>";
            echo "</br>";
                    echo "<br>";
            echo "<hr />";
            echo "<br>";
            break;
        }
    }
    
}

//-------------------------------------------------------------------------
// Funtion to get Date arrays of PersonalNews and Rabbit Equiv. Tables 
//-------------------------------------------------------------------------

function scan_dbs($client, $t_nam){
    $iterator1 = $client->getScanIterator(array(
        'TableName' => 'PERSONALNEWS'
    ), array(
        'limit' => 200
    ));
    $new_ar = array();
    $per_ar = array();
    $lin_ar = array();
    $lid_ar = array();

    $count = 0;
    
    foreach ($iterator1 as $item) {
        if ($item['Type']['S'] == $t_nam) {      
            $per_ar[$count] = $item['Date']['S'];
            $lin_ar[$count] = $item['Link_ID']['S'];
            $count++;
        }
    }
    array_multisort($per_ar, SORT_DESC, $lin_ar);
        
    $iterator2 = $client->getScanIterator(array(
        'TableName' => $t_nam
    ), array(
        'limit' => 200
    ));
    
    $count = 0;
    foreach ($iterator2 as $item) {
        $new_ar[$count] = $item['Date']['S'];
        $lid_ar[$count] = $item['Link_ID']['S'];
        $count++;
    }
    

    array_multisort($new_ar, SORT_DESC, $lid_ar);

    
    $c1 = count($per_ar);
    $c2 = count($new_ar);
    if ($c1 < $c2 || $c1 == $c2){
        for ($i = 0; $i < $c1; $i++){ 
            if ($per_ar[$i] > $new_ar[$i]){
                scan_pnews($client, $t_nam, $lin_ar[$i]);
            }
            elseif ($per_ar[$i] < $new_ar[$i]){
                scan_db($client, $t_nam, $lid_ar[$i]);
                unset($lid_ar[$i]);
                $lid_ar = array_values($lid_ar);
            }
            else{
                scan_pnews($client, $t_nam, $lin_ar[$i]);
            }
        }

        for ($i = 0; $i < count($lid_ar); $i++){
            scan_db($client, $t_nam, $lid_ar[$i]);
        }
    }
    else{
        for ($i = 0; $i < $c2; $i++){
            if ($new_ar[$i] > $per_ar[$i]){
                scan_db($client, $t_nam, $lid_ar[$i]);
            }
            elseif ($new_ar[$i] < $per_ar[$i]){
                scan_pnews($client, $t_nam, $lin_ar[$i]);
                unset($per_ar[$i]);
                $per_ar = array_values($per_ar);
                unset($lin_ar[$i]);
                $lin_ar = array_values($lin_ar);
            }
            else{
                scan_pnews($client, $t_nam, $lin_ar[$i]);
                unset($per_ar[$i]);
                $per_ar = array_values($per_ar);
                unset($lin_ar[$i]);
                $lin_ar = array_values($lin_ar);

            }
        }
        for ($i = 0; $i < count($per_ar); $i++){
            scan_pnews($client, $t_nam, $lin_ar[$i]);
        }
    }
                 
}
    
//-------------------------------------------------------------------------
// Funtion to convert title name to upper case
//-------------------------------------------------------------------------
function table_case($tn){
    if (strlen($tn) < 3){
       $tn = "on_$tn";
    }
    
    $tn = strtoupper ($tn);
    
    return $tn;
}
//-------------------------------------------------------------------------
// Funtion to List Tables of Amazon DynamoDB Database
//-------------------------------------------------------------------------

function list_tables($client){
    $result = $client->listTables();
    $tables = array();
    $i = 0;
    // TableNames contains an array of table names
    foreach ($result['TableNames'] as $tableName) {
        $tables[$i]=$tableName;
        $i=$i+1;
    }
    return  array($tables);
}
//-------------------------------------------------------------------------
// Main Program
//-------------------------------------------------------------------------
//$sectionType = array("news", "comment", "music", "tv", "science", "arts", "film", "sport", "lifestyle", "books");


$tn1 = table_case($section);
$tn2 = ucfirst ($section);
    
echo "<br>";
echo "<h1> Essex $tn2 Page</h1>";
echo "<h3> This site is designed for University of Essex - CE903 course - Group 7</h4>";
echo "<br>";
echo "<hr />";
echo "<br>";
echo "<br>";

//$section = 'NEWS';
scan_dbs($client, $tn1);
//scan_pnews($client, $section);
    

?>
