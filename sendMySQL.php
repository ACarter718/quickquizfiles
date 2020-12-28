<?php
$servername = "localhost";
$username = "myusername";
$password = "mypassword";
$database = "mydatabase";

// load the post data
$postdata = file_get_contents("php://input");
$data = json_decode($postdata, true);

$table = $data['table'];
$name = $data['name'];
$email = $data['email'];
$points = $data['points'];
$percentage = $data['percentage'];
$winningPersonality = $data['winningPersonality'];
$frequency = $data['frequency'];
$userAnswers = $data['userAnswers'];

function connect_DB($servername, $username, $password, $database) {
    $conn_obj  = new mysqli($servername, $username, $password, $database);
    $char = $conn_obj->query("SET NAMES 'utf8'");

    if ($conn_obj->connect_error) {
        header($_SERVER['SERVER_PROTOCOL'] . ' 500 Internal Server Error', true, 500);
        die("Connection failed: " . $conn_obj->connect_error);
    }
    else {
        echo "Connected successfully \r\n";
    }
    return $conn_obj;
}

function create_DB_table($conn_obj, $table) {
    $sql = "CREATE TABLE IF NOT EXISTS $table (
        id INT(6) UNSIGNED AUTO_INCREMENT PRIMARY KEY,
        reg_date TIMESTAMP
    )";

    if ($conn_obj->query($sql) == TRUE) {
         echo "Table successfully created \r\n";
     }
     else {
         header($_SERVER['SERVER_PROTOCOL'] . ' 500 Internal Server Error', true, 500);
         die("Table creation failed: " . $conn_obj->error);

     }
}

function addColumnVarchar($conn_obj, $table, $column) {    
    $query = "SHOW COLUMNS FROM $table LIKE '$column'";    
    $result = mysqli_query($conn_obj, $query);      
    $num = mysqli_num_rows($result);    
    if ($num <= 0) {        
        $q_addColumn = "ALTER TABLE $table ADD `$column` VARCHAR(255)";        
        if ($resultOfAddColumnAttempt = mysqli_query($conn_obj, $q_addColumn)) { // returns true if successful                      
        } else {
            echo"There was a problem adding columns to your DB.\r\n";
        }
    } else {
        
    }
    echo"\r\n";
 
}

function addColumnFloat($conn_obj, $table, $column) {    
    $query = "SHOW COLUMNS FROM $table LIKE '$column'"; 
    $result = mysqli_query($conn_obj, $query);    
    $num = mysqli_num_rows($result);    
    if ($num <= 0) {        
        $q_addColumn = "ALTER TABLE $table ADD `$column` FLOAT";        
        if ($resultOfAddColumnAttempt = mysqli_query($conn_obj, $q_addColumn)) { // returns true if successful
                    
        } else {
           
        }
    } else {
       
    }
    echo"\r\n";
}

    
function insert_DB($conn_obj, $table, $column, $value) { // column should already have backticks from array pushes
    $query = "INSERT INTO $table ($column)
        VALUES ($value)";    
    $result = mysqli_query($conn_obj, $query);    
    if( $result == TRUE) {
        echo "Records inserted successfully!";
    } else {
        header($_SERVER['SERVER_PROTOCOL'] . ' 500 Internal Server Error', true, 500);
        die("Records insertion failed: " . $conn_obj->error);
    }
}

//connect to the database and create table
$conn_obj = connect_DB($servername, $username, $password, $database);
create_DB_table($conn_obj, $table);

$columnArr = array();
$valueArr = array();

if (!is_null($name)){
    addColumnVarchar($conn_obj, $table, 'name');
    array_push($columnArr, "name");
    array_push($valueArr, $name);
}
if (!is_null($email)){
    addColumnVarchar($conn_obj, $table, 'email');
    array_push($columnArr, "email");
    array_push($valueArr, $email);
}
if (!is_null($points)){
    addColumnFloat($conn_obj, $table, 'points');
    array_push($columnArr, "points");
    array_push($valueArr, (int) $points);
}
if (!is_null($percentage)){
    addColumnFloat($conn_obj, $table, 'percentage');
    array_push($columnArr, "percentage");
    array_push($valueArr, $percentage/100);
}
if (!is_null($winningPersonality)){
    addColumnVarchar($conn_obj, $table, 'winningPersonality');
    array_push($columnArr, "winningPersonality");
    array_push($valueArr, $winningPersonality);
}
if (!is_null($frequency)){
    $frequencyString = implode(",", $frequency);
    addColumnVarchar($conn_obj, $table, 'frequency');
    array_push($columnArr, "frequency");
    array_push($valueArr, $frequencyString);
}
if (!is_null($userAnswers)){
    foreach ($userAnswers as $ua) {
        //Prevents program from breaking when users enter punctuation
        $ua['answer'] = mysqli_real_escape_string($conn_obj, $ua['answer']);
        addColumnVarchar($conn_obj, $table, $ua['qID']);
        // backticks added because qID might be a number, and when using 
        // numbers as column names in queries, they must be surrounded with backticks
        array_push($columnArr, "`{$ua['qID']}`"); 
        array_push($valueArr, wordwrap($ua['answer'], 60, "\n", false));
    }
}


$column = implode(",", $columnArr);
$value = "'".implode("','", $valueArr)."'";

insert_DB($conn_obj, $table, $column, $value);

$conn_obj->close();

