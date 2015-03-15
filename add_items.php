<?php

//------------------------------------------------------------------
// Credential properties to Amazon Web Services
//------------------------------------------------------------------
    require ("aws/vendor/autoload.php");
    use Aws\DynamoDb\DynamoDbClient;
    $client = DynamoDbClient::factory(array(
            'key'    => 'your.key',
            'secret' => 'your.secret.code',
            'region' => 'your.region'
    ));

//------------------------------------------------------------------
// Start of Amazon DynamoDB API Functions
//------------------------------------------------------------------
// Function to convert title name to upper case
//------------------------------------------------------------------
function table_case($tn){
    if (strlen($tn) < 3){
       $tn = "on_$tn";
    }
    
    $tn = strtoupper ($tn);
    
    return $tn;
}

//------------------------------------------------------------------
// Function to add items to Amazon DynamoDB Database
//------------------------------------------------------------------
function add_items($title, $ne_url, $txt, $im_src, $im_url,  $dat, $tn){
    date_default_timezone_set("Europe/London");
    $t=time();
    $ts = date("Y.m.d.H.i.s",$t);
    
    $tn = table_case($tn);
    
    $check = scan_db($tn, $title, $ne_url);
    
    if ($txt == '') {
        $txt = $title;
    }
    
    if ($check == 'True'){
        echo 'Error: Item is already  added';
    } else {
        if ($title =='' || $ne_url == ''|| $dat == ''|| $im_url == ''){
            echo "<pre>";
            echo 'Error: Missing Items';
            echo "</pre>";
        } else{
        echo "<pre>";
        echo "Adding Items to $tn";
        echo "</pre>";
        $result = $GLOBALS['client']-> putItem(array(
                'TableName' => $tn,
                'Item' => array(
                        'Link_ID'   => array('S' => $ts),
                        'Title'     => array('S' => $title),
                        'News_URL'  => array('S' => $ne_url),
                        'News'      => array('S' => $txt),
                        'Date'      => array('S' => $dat),
                        'Image_URL' => array('S' => $im_url),
                        'Image_SRC' => array('S' => $im_src)
                    
                )
        ));
    
        }
    }
    
    

}
//------------------------------------------------------------------
// Function to scan Amazon DynamoDB Database
//------------------------------------------------------------------

function scan_db($t_nam, $tt, $url){
    $check = '';
    $iterator = $GLOBALS['client']->getIterator('Scan', array(
    'TableName' => $t_nam,
    'ScanFilter' => array(
        'Title' => array(
            'AttributeValueList' => array(
                array('S' => $tt)
            ),
            'ComparisonOperator' => 'CONTAINS'
        ),
            )
        ));

// Each item will contain the attributes we added
    foreach ($iterator as $item) {
        // Grab the time number value
        echo "<pre>";
        echo $item['Title']['S'];
        echo "<pre>";
        if ($tt == $item['Title']['S'] || $url == $item['News_URL']['S']){
            echo "<pre>";
            echo $item['Title']['S']. " is already exist";
            echo "<pre>";
            $check = 'True';
        }
        else{
            echo "<pre>";
            echo $item['Title']['S']. " is a new item";
            echo "<pre>";
            $check = 'False';
        }
    }
    return $check;
}

//------------------------------------------------------------------
// Main Program
//------------------------------------------------------------------

?>
