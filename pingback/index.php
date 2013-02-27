<?php
// activate full error reporting
error_reporting(E_ALL & E_STRICT & E_WARNING);

include 'xmpphp/XMPP.php';
require '../config/config.php';
$conn = new XMPPHP_XMPP($cfg['xmpp']['server'], $cfg['xmpp']['port'], $cfg['xmpp']['email'], $cfg['xmpp']['pass'], $cfg['xmpp']['client'], $cfg['xmpp']['domain'], $printlog=false, $loglevel=XMPPHP_Log::LEVEL_VERBOSE);

$pingback_type = (isset($_POST['type']) ? $_POST['type'] : null);
$pingback_json = (isset($_POST['json']) ? $_POST['json'] : null);
$pingback_object = (isset($pingback_json) ? json_decode($pingback_json,true) : null);

if(isset($_POST['debug']))
    echo date("n/j-H:i T",strtotime($pingback_object['datetime']));

mysql_connect("memoria", "ingress", "insecureP@ss")
  or die("<div id=\"fail_connect\">\n  <error details=\"".mysql_error()."\" />\n</div>\n");
mysql_select_db("ingress")
  or die("<div id=\"fail_select_db\">\n  <error details=\"".mysql_error()."\" />\n</div>\n");
  
$query = sprintf("SELECT id, needle, contact, method FROM `alerts` WHERE haystack = '%s';", $pingback_type);

if (!$result = mysql_query($query)) {
    die("<div id=\"fail_alerts\">\n  <error details=\"".mysql_error()."\" />\n</div>\n");
}

$search_strings = array();
if (mysql_num_rows($result) > 0) {
    while($row = mysql_fetch_array($result)) {
        $search_string = array('id'=>$row['id'], 
            'needle'=>$row['needle'], 
            'contact'=>$row['contact'], 
            'method'=>$row['method']);
        array_push ($search_strings, $search_string);
    }
}

{ // prince's island park 
    if ($pingback_type == 'deploy') 
    { 

        $portalsonpip = array('27dada6afd2848268c32a9d2d3605a0e.11',    
                'aedbf6cab3f742478b0b7f35d18c8a7e.12',
                '59f510313d864be5981f60a22e7577d2.12',
                '415e4da03b0b484da93396d2116be6e9.12',
                '333d47d7254843cbaea49fdda4be018c.12',
                'd64c022787ba421098fdaf62eac3a664.11');
        foreach ($portalsonpip as $portal) 
        {
            if($pingback_object['res'] != 'L1' && strstr($pingback_object['portal'][0]['guid'],$portal)) 
            { // check the db if we've previously alerted for this logid.
                $query = sprintf("SELECT COUNT(id) as count FROM `alerts_log` WHERE alert = '0' AND guid = '%s';", $pingback_object['guid']);

                if (!$result = mysql_query($query)) {
                    break;
                }

                while($row = mysql_fetch_array($result))     
                { 
                    if ($row['count'] < 1) 
                    { // send the message
                        $message = "A deploy entry just triggered a rule in ingress calgary.\n" .
                            "It looks like someone deployed a resonator on Prince's Island Park above L1\n" .
                            "The entire contents of the deploy log is :\n" .
                            "[".date("n/j-H:i T",strtotime($pingback_object['datetime']))."] ". substr($pingback_object['user']['0']['faction'],0,3) . "-{$pingback_object['user'][0]['name']} deployed a {$pingback_object['res']} on {$pingback_object['portal'][0]['name']}";
                        try {
                            $conn->connect();
                            $conn->processUntil('session_start');
                            $conn->presence();
                            $conn->message('michael@writhem.com', $message);
                            $conn->disconnect();
                            //echo "message sent\n";
                            
                            { // update the db that we've sent it... 
                                $query = sprintf("INSERT INTO `ingress`.`alerts_log` (`alert`, `guid`) VALUES (0, '%s');",
                                    $pingback_object['guid']);
                                $result = mysql_query($query);

                                if (is_resource($result) && mysql_num_rows($result) > 0) {
                                    header(':', true, 204);
                                    echo "<div id=\"success\">\n  <success details=\"notified\" />\n</div>\n";
                                }   
                            }
                        } catch(XMPPHP_Exception $e) {
                            //echo $e->getMessage();
                        }
                    }
                }
                
            }
        }
    }
}

if (isset($_GET['debug']))
    echo "test";

foreach ($search_strings as $needle) {
    //printf("looking for '%s' in the object\n", $needle['needle']);
    $message = null;
    
    // methods to search the text for a match
    if ($pingback_type == 'chat') 
    { 
        if(strstr($pingback_object['text'],$needle['needle']) || 
        preg_match($needle['needle'], $pingback_object['text'], $matches)) {
            //echo "simple text located... \n";
            $message = "A chat entry just triggered a rule in ingress calgary.\n" .
                "The search criteria that was met was looking for {$needle['needle']}\n" .
                "In the case that this was found, we have been instructed to contact: {$needle['contact']} via {$needle['method']}\n" .
                "The entire contents of the chat entry is :\n" .
                "[".date("n/j-H:i T",strtotime($pingback_object['datetime']))."] ". substr($pingback_object['channel'],0,3) . "-{$pingback_object['user'][0]['name']} : {$pingback_object['text']}";
        }
    } 
    elseif ($pingback_type == 'chat') 
    {
        if(strstr($pingback_object['text'],$needle['needle']) || 
        preg_match($needle['needle'], $pingback_object['text'], $matches)) {
            //echo "simple text located... \n";
            $message = "A chat entry just triggered a rule in ingress calgary.\n" .
                "The search criteria that was met was looking for {$needle['needle']}\n" .
                "In the case that this was found, we have been instructed to contact: {$needle['contact']} via {$needle['method']}\n" .
                "The entire contents of the chat entry is :\n" .
                "[".date("n/j-H:i T",strtotime($pingback_object['datetime']))."] ". substr($pingback_object['channel'],0,3) . "-{$pingback_object['user'][0]['name']} : {$pingback_object['text']}";
        }

    } 
    else 
    {
        if(strstr($pingback_json,$needle['needle']) || 
        preg_match($needle['needle'], $pingback_json, $matches)) {
            //echo "simple text located... \n";
            $message = "A {$pingback_type}-log entry just triggered a rule in ingress calgary.\n" .
                "The search criteria that was met was looking for {$needle['needle']}\n" .
                "In the case that this was found, we have been instructed to contact: {$needle['contact']} via {$needle['method']}\n" .
                "The entire contents of the log entry is :\n" .
                "[".date("n/j-H:i T",strtotime($pingback_object['datetime']))."] {$pingback_object['user'][0]['name']} : {$pingback_json}";
        }
    }
    //echo $message;
        
    $query = sprintf("SELECT COUNT(id) as count FROM `alerts_log` WHERE alert = %d AND guid = '%s';", 
    $needle['id'],
    $pingback_object['guid']);

    if (!$result = mysql_query($query)) {
        break;
    }

    while($row = mysql_fetch_array($result))     
    { 
        if ($row['count'] < 1) 
        { // send the message
        
            #Use XMPPHP_Log::LEVEL_VERBOSE to get more logging for error reports
            #If this doesn't work, are you running 64-bit PHP with < 5.2.6?
            if ($message && $needle['method'] == 'xmpp') 
            {
                try {
                    $conn->connect();
                    $conn->processUntil('session_start');
                    $conn->presence();
                    $conn->message($needle['contact'], $message);
                    $conn->disconnect();
                    //echo "message sent\n";
                    
                } catch(XMPPHP_Exception $e) {
                    //echo $e->getMessage();
                }
            } 
            elseif ($message && $needle['method'] == 'email') 
            {
                mail($needle['contact'], 'Calgary Ingress API Alerter', $message);
            }
            
            { // update the db that we've sent it... 
                $query = sprintf("INSERT INTO `ingress`.`alerts_log` (`alert`, `guid`) VALUES (%d, '%s');",
                    $needle['id'],
                    $pingback_object['guid']);
                $result = mysql_query($query);

                if (is_resource($result) && mysql_num_rows($result) > 0) {
                    header(':', true, 204);
                    echo "<div id=\"success\">\n  <success details=\"notified\" />\n</div>\n";
                }   
            }
        }
        
    }
}
?>