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
// Function to add Tables to Amazon DynamoDB Database
//------------------------------------------------------------------

function create_table($t_name){
    
    $t_name = table_case($t_name);
    
    list($tables) = list_tables();
    if (in_array($t_name, $tables)) {
        echo "<pre>";
        echo "Error: $t_name Table ==> Already Exists";
        echo "</pre>";
    }
    else {
    
	$GLOBALS['client']->createTable(array(
		'TableName' => $t_name,
		'AttributeDefinitions' => array(
				array(
						'AttributeName' => 'Link_ID',
						'AttributeType' => 'S'
				),
				array(
						'AttributeName' => 'Title',
						'AttributeType' => 'S'
				)
		),
		'KeySchema' => array(
				array(
						'AttributeName' => 'Link_ID',
						'KeyType'       => 'HASH'
				),
				array(
						'AttributeName' => 'Title',
						'KeyType'       => 'RANGE'
				)
		),
		'ProvisionedThroughput' => array(
				'ReadCapacityUnits'  => 10,
				'WriteCapacityUnits' => 20
		)
    ));

    $GLOBALS['client']->waitUntil('TableExists', array(
        'TableName' => $t_name
    ));
    echo "<pre>";
    echo "Table $t_name is added successfully" ."\xA";
    }
}
//------------------------------------------------------------------
// Function to List Tables of Amazon DynamoDB Database
//------------------------------------------------------------------

function list_tables(){
    $result = $GLOBALS['client']->listTables();
    $tables = array();
    $i = 0;
    // TableNames contains an array of table names
    foreach ($result['TableNames'] as $tableName) {
        $tables[$i]=$tableName;
        $i=$i+1;
    }
    return  array($tables);
}

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

?>
