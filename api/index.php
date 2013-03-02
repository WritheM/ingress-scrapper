<?php
header('Access-Control-Allow-Origin: https://www.ingress.com');
header('Connection', 'keep-alive');
if (isset($_GET['debug']))
{
    ini_set('display_errors','On');
    error_reporting(E_ALL);
    $debug = true;
}
else 
{
    $debug = false;
}

require '../config/config.php';
require 'objects.php';

$region = (isset($_GET['region']) ? (int)$_GET['region'] : (int)1);
$maint_mode = true;

try {
    $conn = "mysql:host={$cfg['db']['host']};dbname={$cfg['db']['dbase']}";
    $db = new PDO($conn, $cfg['db']['user'], $cfg['db']['pass']);
} catch (PDOException $e) {
    header(':', true, 503);
    printf("<div id=\"fail_connect\">\n  <error details=\"%s\" />\n</div>\n", $e->getMessage());
    exit();
}
    
if (isset($_GET['key']) && isset($_GET['table'])) 
{
    { // we were hit for info with a key, verify its legit... 

        $stmt = $db->prepare("UPDATE `api` SET `hits` = `hits`+1 WHERE `api`.`key` = :key;");
        $stmt->bindValue(':key',$_GET['key']);
            
        // foreach($db->query('SELECT * from FOO') as $row) {
            // print_r($row);
        // }
        
        if ($stmt->execute() && $stmt->rowCount() > 0) 
        {
            //header(':', true, 200);
            if (isset($_GET['debug']))
                echo "  42:key accepted\n";
        } 
        else
        {
            header(':', true, 403);
            die("<div id=\"fail_key\">\n  <error details=\"bad api key\" />\n</div>\n");
        }
    }
    
    { // QUERY STUFF
    
        if ($_GET['table'] == 'view_players') 
        {
            $users = array();
            $parms = array();
            $query = "SELECT * \n"
                   . "FROM ( \n"
                   . "  SELECT players.guid, players.name, teams.name AS faction, players.region, deploy_log.res AS highestDeployed \n"
                   . "  FROM players \n"
                   . "  LEFT JOIN deploy_log ON deploy_log.user = players.guid \n"
                   . "  INNER JOIN teams ON players.team = teams.id \n"
                   . "  WHERE 1=1\n";
            if (isset($_GET['faction'])) {
                $query .= "  AND players.team = :faction \n";
                $parms[] = array(':faction',($_GET['faction'] == 1 ? 1 : 2));
            }
            if (isset($_GET['player'])) {
                $query .= "  AND players.name = :name \n";
                $parms[] = array(':name',(isset($_GET['player']) ? $_GET['player'] : ""));
            }
            $query .=  "  AND players.region = :region \n";
            $parms[] = array(':region',$region);
            $query .= "  ORDER BY deploy_log.res DESC \n"
                   . ") AS rawr \n"
                   . "GROUP BY name \n"
                   . "ORDER BY highestDeployed DESC;";
                   
            $stmt = $db->prepare($query);
            foreach($parms as $parm) {
                $stmt->bindValue($parm[0], $parm[1]);    
            }
            
            try 
            {
                $stmt->execute();
            } 
            catch (PDOException $e)
            {
                header(':', true, 500);
                printf("<div id=\"fail_query\">\n  <error details=\"%s\" />\n</div>\n", $e->getMessage());
                exit();
            }

            while ($row = $stmt->fetch()) 
            {
                $user = array('guid'=>$row[0], 
                    'name'=>$row[1], 
                    'faction'=>$row[2],
                    'region'=>getRegionObject($db, $row[3]),
                    'highestDeployed'=>$row[4]);
                array_push ($users, $user);            
            }

            if ($stmt->rowCount() > 0) {
                header(':', true, 200);
                echo json_encode($users);
            } else {
                header(':', true, 204);
            }
        }
        else if ($_GET['table'] == 'view_faction') 
        {
            $factions = array();
            { // build the query
                $parms = array();
                $query = "SELECT portalTable.id, portalTable.faction, portalTable.region, portalTable.portalCount, playerTable.playerCount
                FROM (SELECT teams.id, teams.name as faction, portals.region, COUNT(portals.guid) as portalCount
                    FROM teams
                    LEFT JOIN portals ON teams.id=portals.team
                    WHERE region = :regionid
                    GROUP BY id) as portalTable
                LEFT JOIN (SELECT teams.id, COUNT(players.guid) as playerCount 
                    FROM teams
                    LEFT JOIN players ON teams.id=players.team
                    WHERE region = :regionid
                    GROUP BY id) as playerTable ON portalTable.id = playerTable.id";
                $parms[] = array(':regionid',$region);
                if (isset($_GET['faction']))
                {
                    $query .= "\nWHERE portalTable.id = :faction";
                    $parms[] = array(':faction',$_GET['faction']);
                }
            }

            { // set up the statement / execute
                $stmt = $db->prepare($query);
                foreach($parms as $parm) {
                    $stmt->bindValue($parm[0], $parm[1]);    
                }

                try 
                {
                    $stmt->execute();
                } 
                catch (PDOException $e)
                {
                    return("<div id=\"fail_query\">\n  <error details=\"%s\" />\n</div>\n" % $e->getMessage());
                }
            }
            
            { // populate the object / return
                while($row = $stmt->fetch()) {
                    $faction = array('guid'=>$row['id'], 
                        'name'=>$row['faction'], 
                        'portalCount'=>(int)$row['portalCount'], 
                        'playerCount'=>(int)$row['playerCount']);
                    array_push ($factions, $faction);
                }
                echo json_encode($factions);
            }
        }
        else if ($_GET['table'] == 'view_mu') 
        {
            $factions = array();
            { // build the query
                $parms = array();
                $query = "SELECT teams.id, teams.name as faction, captured.totalMUs as capturedMU, liberated.totalMUs as liberatedMU
                    FROM (
                        SELECT players.team, sum(liberate_log.mus) as totalMUs FROM `liberate_log`
                        LEFT JOIN players ON players.guid=liberate_log.user
                        WHERE liberate_log.region = :regionid
                        GROUP BY team) 
                    AS liberated
                    LEFT JOIN (
                        SELECT players.team, sum(control_log.mus) as totalMUs FROM `control_log`
                        LEFT JOIN players ON players.guid=control_log.user
                        WHERE control_log.region = :regionid
                        GROUP BY team) 
                    AS captured ON liberated.team=captured.team
                    LEFT JOIN teams ON teams.id=liberated.team";
                $parms[] = array(':regionid',$region);
                if (isset($_GET['faction']))
                {
                    $query .= "\nWHERE portalTable.id = :faction";
                    $parms[] = array(':faction',$_GET['faction']);
                }
            }

            { // set up the statement / execute
                $stmt = $db->prepare($query);
                foreach($parms as $parm) {
                    $stmt->bindValue($parm[0], $parm[1]);    
                }

                try 
                {
                    $stmt->execute();
                } 
                catch (PDOException $e)
                {
                    return("<div id=\"fail_query\">\n  <error details=\"%s\" />\n</div>\n" % $e->getMessage());
                }
            }
            
            { // populate the object / return
                while($row = $stmt->fetch()) {
                    $faction = array('guid'=>$row['id'], 
                        'name'=>$row['faction'], 
                        'capturedMU'=>$row['capturedMU'], 
                        'liberatedMU'=>$row['liberatedMU']);
                    array_push ($factions, $faction);
                }
                $factions[0]['currentMU']=$factions[0]['capturedMU']-$factions[1]['liberatedMU']+40000;
                $factions[1]['currentMU']=$factions[1]['capturedMU']-$factions[0]['liberatedMU']+40000;
               echo json_encode($factions);
            }
        }
        else if ($_GET['table'] == 'decayed') 
        {
            header(':', true, 501);
        /*
            $factions = array();
            { // build the query
                $parms = array();
                $query = "SELECT teams.id, teams.name as faction, captured.totalMUs as capturedMU, liberated.totalMUs as liberatedMU
                    FROM (
                        SELECT players.team, sum(liberate_log.mus) as totalMUs FROM `liberate_log`
                        LEFT JOIN players ON players.guid=liberate_log.user
                        WHERE liberate_log.region = :regionid
                        GROUP BY team) 
                    AS liberated
                    LEFT JOIN (
                        SELECT players.team, sum(control_log.mus) as totalMUs FROM `control_log`
                        LEFT JOIN players ON players.guid=control_log.user
                        WHERE control_log.region = :regionid
                        GROUP BY team) 
                    AS captured ON liberated.team=captured.team
                    LEFT JOIN teams ON teams.id=liberated.team";
                $parms[] = array(':regionid',$region);
                if (isset($_GET['faction']))
                {
                    $query .= "\nWHERE portalTable.id = :faction";
                    $parms[] = array(':faction',$_GET['faction']);
                }
            }

            { // set up the statement / execute
                $stmt = $db->prepare($query);
                foreach($parms as $parm) {
                    $stmt->bindValue($parm[0], $parm[1]);    
                }

                try 
                {
                    $stmt->execute();
                } 
                catch (PDOException $e)
                {
                    return("<div id=\"fail_query\">\n  <error details=\"%s\" />\n</div>\n" % $e->getMessage());
                }
            }
            
            { // populate the object / return
                while($row = $stmt->fetch()) {
                    $faction = array('guid'=>$row['id'], 
                        'name'=>$row['faction'], 
                        'capturedMU'=>$row['capturedMU'], 
                        'liberatedMU'=>$row['liberatedMU']);
                    array_push ($factions, $faction);
                }
                $factions[0]['currentMU']=$factions[0]['capturedMU']-$factions[1]['liberatedMU']+40000;
                $factions[1]['currentMU']=$factions[1]['capturedMU']-$factions[0]['liberatedMU']+40000;
               echo json_encode($factions);
            }
            */
        }
        else if ($_GET['table'] == 'chat') 
        {
            echo json_encode(getChatObject($db, 
            (isset($_GET['channel']) ? $_GET['channel'] : null), 
            (isset($_GET['before']) ? $_GET['before'] : null), 
            (isset($_GET['after']) ? $_GET['after'] : null), 
            (isset($_GET['limit']) ? $_GET['limit'] : null), 
            (isset($_GET['region']) ? $_GET['region'] : null)));
        }
        else if ($_GET['table'] == 'player') 
        {
            $guids = null;
            if (isset($_GET['guids'])) {
                $guids = explode(',',$_GET['guids']);
            }
            echo json_encode(getPlayerObject($db, 
            (isset($_GET['name']) ? $_GET['name'] : null), 
            $guids, 
            (isset($_GET['region']) ? $_GET['region'] : null)));
        }
        else if ($_GET['table'] == 'portal') 
        {
            $guids = null;
            if (isset($_GET['guids'])) {
                $guids = explode(',',$_GET['guids']);
            } elseif (isset($_GET['guid'])) {
                $guids = $_GET['guid'];
            }
            echo json_encode(getPortalObject($db, $guids, 
            (isset($_GET['region']) ? $_GET['region'] : null)));
        }
        else if ($_GET['table'] == 'destroy') 
        {
            $guids = null;
            if (isset($_GET['portals'])) {
                $guids = explode(',',$_GET['portals']);
            }
            echo json_encode(getResonatorObject($db, 'destroy', $guids, 
            (isset($_GET['datetime']) ? $_GET['datetime'] : null), 
            (isset($_GET['limit']) ? $_GET['limit'] : null)));
        }
        else if ($_GET['table'] == 'deploy') 
        {
            $guids = null;
            if (isset($_GET['portals'])) {
                $guids = explode(',',$_GET['portals']);
            }
            echo json_encode(getResonatorObject($db,'deploy', $guids, 
            (isset($_GET['datetime']) ? $_GET['datetime'] : null), 
            (isset($_GET['limit']) ? $_GET['limit'] : null)));
        }
        else if ($_GET['table'] == 'linked') 
        {
            $guids = null;
            if (isset($_GET['portals'])) {
                $guids = explode(',',$_GET['portals']);
            }
            echo json_encode(getLinkObject($db, 'linked', $guids, 
            (isset($_GET['datetime']) ? $_GET['datetime'] : null), 
            (isset($_GET['limit']) ? $_GET['limit'] : null)));
        }
        else if ($_GET['table'] == 'break') 
        {
            $guids = null;
            if (isset($_GET['portals'])) {
                $guids = explode(',',$_GET['portals']);
            }
            echo json_encode(getLinkObject($db, 'break', $guids, 
            (isset($_GET['datetime']) ? $_GET['datetime'] : null), 
            (isset($_GET['limit']) ? $_GET['limit'] : null)));
        }
        else if ($_GET['table'] == 'captured') 
        {
            $guids = null;
            if (isset($_GET['portals'])) {
                $guids = explode(',',$_GET['portals']);
            }
            echo json_encode(getCaptureObject($db, $guids, 
            (isset($_GET['datetime']) ? $_GET['datetime'] : null), 
            (isset($_GET['limit']) ? $_GET['limit'] : null)));
        }
        else if ($_GET['table'] == 'control') 
        {
            $guids = null;
            if (isset($_GET['portals'])) {
                $guids = explode(',',$_GET['portals']);
            }
            echo json_encode(getControlFieldObject($db, 'captured', $guids, 
            (isset($_GET['datetime']) ? $_GET['datetime'] : null), 
            (isset($_GET['limit']) ? $_GET['limit'] : null)));
        }
        else if ($_GET['table'] == 'liberate') 
        {
            $guids = null;
            if (isset($_GET['portals'])) {
                $guids = explode(',',$_GET['portals']);
            }
            echo json_encode(getControlFieldObject($db, 'liberate', $guids, 
            (isset($_GET['datetime']) ? $_GET['datetime'] : null), 
            (isset($_GET['limit']) ? $_GET['limit'] : null)));
        }
        else if ($_GET['table'] == 'pmoddestroy') 
        {
            $guids = null;
            if (isset($_GET['portals'])) {
                $guids = explode(',',$_GET['portals']);
            }
            echo json_encode(getModObject($db, $guids, 
            (isset($_GET['datetime']) ? $_GET['datetime'] : null), 
            (isset($_GET['limit']) ? $_GET['limit'] : null)));
        }
        else if ($_GET['table'] == 'dev') 
        {
            $guids = null;
            if (isset($_GET['portals'])) {
                $guids = explode(',',$_GET['portals']);
            }
            echo json_encode(getModObject($db, $guids, 
            (isset($_GET['datetime']) ? $_GET['datetime'] : null), 
            (isset($_GET['limit']) ? $_GET['limit'] : null)));
        }
        else 
        { // BAD TABLE
            header(':', true, 400);
            printf("<div id=\"fail\">\n  <error details=\"unsupported table for the query method\" />\n</div>\n");
        }
    }    

}
else if (isset($_POST['key']) && isset($_POST['package']))
{
    { // we were hit by a scrapper, verify its legit... 
        $stmt = $db->prepare("SELECT id, name FROM regions WHERE scrapper = :key;");
        $stmt->bindParam(':key',$_POST['key']);

        $stmt->execute();
        while ($row = $stmt->fetch()) {
            $region = (int)$row['id'];
        }
        
        if ($stmt->rowCount() > 0) 
        {
            header(':', true, 200);
            if (isset($_POST['debug']))
                echo "  67:key accepted\n";
        } 
        else
        {
            header(':', true, 403);
            die("<div id=\"fail_key\">\n  <error details=\"bad api key\" />\n</div>\n");
        }
    }
    
    $package = $_POST['package'];
    
    { // SAVE STUFF
        $pingback_object = null;
        $pingback_type = null;
        
        $items = 0;
        foreach ($package as $p) {
            foreach($p as $v) {
                $items++;
                
                if ($v[2]['plext']['markup'][0][1]['plain'] == "Your ")
                {
                    // ignore this entry... who cares about my personal stuff? nobody!
                } 
                elseif ($v[2]['plext']['markup'][1][1]['plain'] == " deployed an ") 
                { // deploy a resonator
                    { // parse the data
                        $guid = $v[0];
                        if ($v[1] > 4294967295) // maximum valid datetime in s, so it must be ms
                            $datetime = $v[1] / 1000; //convert it!
                        else 
                            $datetime = $v[1];
                        
                        $player = $v[2]['plext']['markup'][0][1];
                        $res = $v[2]['plext']['markup'][2][1]['plain'];
                        $portal = $v[2]['plext']['markup'][4][1];
                    }
                    
                    { // create the temporary objects
                                                
                        $resonator = array(
                            'guid'=>$guid,
                            'user'=>$player['guid'],
                            'portal'=>$portal['guid'],
                            'res'=>$res,
                            'datetime'=>(int)$datetime,
                            'region'=>(int)$region
                        );
                        
                        $player['region'] = $region;
                        
                        $portal['latE6'] = (int)$portal['latE6'];
                        $portal['lngE6'] = (int)$portal['lngE6'];
                        $portal['region'] = $region;
                    }
                    
                    { // save the objects
                        $response = savePlayerObject($db, $player);
                        //header(':', true, $response['code']);
                        printf("<div id=\"%s\">\n  <details=\"%s\" />\n</div>\n", $response['class'], $response['detail']);
                        if ($response['class'] == 'fail_object') var_dump($response['debug']);
                        
                        $response = savePortalObject($db, $portal);
                        //header(':', true, $response['code']);
                        printf("<div id=\"%s\">\n  <details=\"%s\" />\n</div>\n", $response['class'], $response['detail']);
                        
                        $response = saveResonatorObject($db, 'deploy', $resonator); 
                        header(':', true, $response['code']);
                        printf("<div id=\"%s\">\n  <details=\"%s\" />\n</div>\n", $response['class'], $response['detail']);
                    }
                    
                    { // build a pingback
                        $user = getPlayerObject($db, null, $player['guid']);
                        $regionObject = getRegionObject($db,$region);
                        $pingback_object = array('guid'=>$guid, 
                            'user'=>$user, 
                            'portal'=>$portal,
                            'res'=>$res,
                            'datetime'=>(int)$datetime, 
                            'region'=>$regionObject);
                        $pingback_type = 'deploy';
                    }

                }
                elseif ($v[2]['plext']['markup'][1][1]['plain'] == " destroyed an ")
                {
                /*  } else if (json[2].plext.markup[1][1].plain == " destroyed an ") {
                      pguid = json[2].plext.markup[0][1].guid;
                      var res = json[2].plext.markup[2][1].plain;
                      var port = json[2].plext.markup[4][1].guid;
                      var writhem_temp = "key="+WRITHEMAPIKEY+"&method=save&table=destroy";
                      writhem_temp = writhem_temp + "&logid=" + json[0];
                      writhem_temp = writhem_temp + "&ts=" + new Date(json[1]).toJSON();
                      writhem_temp = writhem_temp + "&user=" + pguid;
                      writhem_temp = writhem_temp + "&res=" + res;
                      writhem_temp = writhem_temp + "&portal=" + port;
                      //console.log("hitting writhem api with : "+writhem_temp);
                      $('#writhem_logs').load(WRITHEMAPIURL,writhem_temp);*/
                }
                elseif ($v[2]['plext']['markup'][1][1]['plain'] == " destroyed the Link ")
                {
                /*  } else if (json[2].plext.markup[1][1].plain == " destroyed the Link ") {
                      pguid = json[2].plext.markup[0][1].guid;
                      var writhem_temp = "key="+WRITHEMAPIKEY+"&method=save&table=break";
                      writhem_temp = writhem_temp + "&logid=" + json[0];
                      writhem_temp = writhem_temp + "&ts=" + new Date(json[1]).toJSON();
                      writhem_temp = writhem_temp + "&user=" + pguid;
                      writhem_temp = writhem_temp + "&portal1=" + json[2].plext.markup[2][1].guid;
                      writhem_temp = writhem_temp + "&portal2=" + json[2].plext.markup[4][1].guid;
                      //console.log("hitting writhem api with : "+writhem_temp);
                      $('#writhem_logs').load(WRITHEMAPIURL,writhem_temp);*/
                }
                elseif ($v[2]['plext']['markup'][1][1]['plain'] == " linked ")
                {
                /*  } else if (json[2].plext.markup[1][1].plain == " linked ") {
                      pguid = json[2].plext.markup[0][1].guid;
                      var writhem_temp = "key="+WRITHEMAPIKEY+"&method=save&table=linked";
                      writhem_temp = writhem_temp + "&logid=" + json[0];
                      writhem_temp = writhem_temp + "&ts=" + new Date(json[1]).toJSON();
                      writhem_temp = writhem_temp + "&user=" + pguid;
                      writhem_temp = writhem_temp + "&portal1=" + json[2].plext.markup[2][1].guid;
                      writhem_temp = writhem_temp + "&portal2=" + json[2].plext.markup[4][1].guid;
                      //console.log("hitting writhem api with : "+writhem_temp);
                      $('#writhem_logs').load(WRITHEMAPIURL,writhem_temp);*/
                }
                elseif ($v[2]['plext']['markup'][1][1]['plain'] == " captured ")
                {
                /*  } else if (json[2].plext.markup[1][1].plain == " captured ") {
                      pguid = json[2].plext.markup[0][1].guid;
                      var writhem_temp = "key="+WRITHEMAPIKEY+"&method=save&table=captured";
                      writhem_temp = writhem_temp + "&logid=" + json[0];
                      writhem_temp = writhem_temp + "&ts=" + new Date(json[1]).toJSON();
                      writhem_temp = writhem_temp + "&user=" + pguid;
                      writhem_temp = writhem_temp + "&portal=" + json[2].plext.markup[2][1].guid;
                      //console.log("hitting writhem api with : "+writhem_temp);
                      $('#writhem_logs').load(WRITHEMAPIURL,writhem_temp);*/
                }
                elseif ($v[2]['plext']['markup'][1][1]['plain'] == " destroyed a Control Field @")
                {
                /*  } else if (json[2].plext.markup[1][1].plain == " destroyed a Control Field @") {
                      pguid = json[2].plext.markup[0][1].guid;
                      var writhem_temp = "key="+WRITHEMAPIKEY+"&method=save&table=liberate";
                      writhem_temp = writhem_temp + "&logid=" + json[0];
                      writhem_temp = writhem_temp + "&ts=" + new Date(json[1]).toJSON();
                      writhem_temp = writhem_temp + "&user=" + pguid;
                      writhem_temp = writhem_temp + "&portal=" + json[2].plext.markup[2][1].guid;
                      writhem_temp = writhem_temp + "&mus=" + json[2].plext.markup[4][1].plain;
                      //console.log("hitting writhem api with : "+writhem_temp);
                      $('#writhem_logs').load(WRITHEMAPIURL,writhem_temp);*/
                }
                elseif ($v[2]['plext']['markup'][1][1]['plain'] == " created a Control Field @")
                {
                /*  } else if (json[2].plext.markup[1][1].plain == " created a Control Field @") {
                      pguid = json[2].plext.markup[0][1].guid;
                      var writhem_temp = "key="+WRITHEMAPIKEY+"&method=save&table=control";
                      writhem_temp = writhem_temp + "&logid=" + json[0];
                      writhem_temp = writhem_temp + "&ts=" + new Date(json[1]).toJSON();
                      writhem_temp = writhem_temp + "&user=" + pguid;
                      writhem_temp = writhem_temp + "&portal=" + json[2].plext.markup[2][1].guid;
                      writhem_temp = writhem_temp + "&mus=" + json[2].plext.markup[4][1].plain;
                      //console.log("hitting writhem api with : "+writhem_temp);
                      $('#writhem_logs').load(WRITHEMAPIURL,writhem_temp);*/
                }
                elseif ($v[2]['plext']['markup'][0][1]['plain'] == "The Link ")
                {
                /*  } else if (json[2].plext.markup[0][1].plain == "The Link ") {
                      pguid = json[2].plext.markup[0][1].guid;
                      var writhem_temp = "key="+WRITHEMAPIKEY+"&method=save&table=decayed";
                      writhem_temp = writhem_temp + "&logid=" + json[0];
                      writhem_temp = writhem_temp + "&ts=" + new Date(json[1]).toJSON();
                      writhem_temp = writhem_temp + "&portal1=" + json[2].plext.markup[2][1].guid;
                      writhem_temp = writhem_temp + "&portal2=" + json[2].plext.markup[4][1].guid;
                      //console.log("hitting writhem api with : "+writhem_temp);
                      $('#writhem_logs').load(WRITHEMAPIURL,writhem_temp);*/
                }
                elseif ($v[2]['plext']['markup'][1][1]['plain'] == " destroyed a ")
                {
                /*  } else if (json[2].plext.markup[1][1].plain == " destroyed a ") { //portal mod - destroy
                      pguid = json[2].plext.markup[0][1].guid;
                      var writhem_temp = "key="+WRITHEMAPIKEY+"&method=save&table=pmoddestroy";
                      writhem_temp = writhem_temp + "&logid=" + json[0];
                      writhem_temp = writhem_temp + "&ts=" + new Date(json[1]).toJSON();
                      writhem_temp = writhem_temp + "&user=" + json[2].plext.markup[0][1].guid;
                      writhem_temp = writhem_temp + "&portal=" + json[2].plext.markup[4][1].guid;
                      writhem_temp = writhem_temp + "&mod=" + json[2].plext.markup[2][1].plain;
                      //console.log("hitting writhem api with : "+writhem_temp);
                      $('#writhem_logs').load(WRITHEMAPIURL,writhem_temp);*/
                }
                elseif ($v[2]['plext']['plextType'] == "PLAYER_GENERATED"
                    && ((isset($v[2]['plext']['markup'][2][0])
                    && $v[2]['plext']['markup'][2][0] == 'TEXT')
                    || (isset($v[2]['plext']['markup'][1][0])
                    && $v[2]['plext']['markup'][1][0] == 'TEXT')))
                { // chat
                    { // parse the data
                        $guid = $v[0];
                        if ($v[1] > 4294967295) // maximum valid datetime in s, so it must be ms
                            $datetime = $v[1] / 1000; //convert it!
                        else 
                            $datetime = $v[1];
                        if ($v[2]['plext']['markup'][1][0] == 'TEXT') 
                        { // public
                            $secure = false;
                            $player = $v[2]['plext']['markup'][0][1];
                            $text = $v[2]['plext']['markup'][1][1];
                        }
                        else
                        { // secure
                            $secure = true;
                            $player = $v[2]['plext']['markup'][1][1];
                            $text = $v[2]['plext']['markup'][2][1];
                        }
                    }
                    
                    { // create the temporary objects
                        
                        $chat = array(
                            'guid'=>$guid,
                            'datetime'=>(int)$datetime,
                            'user'=>$player['guid'],
                            'text'=>$text['plain'],
                            'secure'=>$secure,
                            'region'=>$region
                        );
                        
                        $player['name'] = $player['plain'];
                        $player['region'] = $region;
                    }
                    
                    { // save the objects
                        $response = savePlayerObject($db, $player);
                        //header(':', true, $response['code']);
                        printf("<div id=\"%s\">\n  <details=\"%s\" />\n</div>\n", $response['class'], $response['detail']);
                        
                        $response = saveChatObject($db, $chat);
                        header(':', true, $response['code']);
                        printf("<div id=\"%s\">\n  <details=\"%s\" />\n</div>\n", $response['class'], $response['detail']);
                    }
                    
                    { // build a pingback
                        $user = getPlayerObject($db, null, $player['guid']);
                        $regionObject = getRegionObject($db,$region);
                        $pingback_object = array('guid'=>$guid, 
                            'datetime'=>(int)$datetime, 
                            'user'=>$user, 
                            'channel'=>($secure ? $user[0]['faction'] : "PUBLIC"),
                            'text'=>$text,
                            'region'=>$regionObject);
                        $pingback_type = 'chat';
                    }
                }
                else 
                { // this method is here just to catch new features as they are added to ingress/intel
                    $message = "looks like a new and unsupported method has been implemented into the intel website. Here are the details:\n\n";
                    ob_start();
                    print_r($v);
                    $message .= ob_get_clean();
                    echo $message;
                    // mail($cfg['site']['contact'], $cfg['site']['title'], $message);

                    //header(':', true, 501);
                    //printf("<div id=\"fail\">\n  <error details=\"unsupported intel ingress object for the save method\" />\n</div>\n");
                }
                
                if ($pingback_object && $pingback_type && !$maint_mode) 
                {
                    { // build the query... wow that was easy. ha!
                        $query = "SELECT url, region FROM `pingback`;";
                    }

                    { // set up the statement / execute
                        $stmt = $db->prepare($query);

                        try 
                        {
                            $stmt->execute();
                        } 
                        catch (PDOException $e)
                        {
                            return("<div id=\"fail_pingback\">\n  <error details=\"%s\" />\n</div>\n" % $e->getMessage());
                        }
                    }
                    
                    if ($debug)
                    {
                        printf("the package is: %s\n", json_encode($pingback_object));
                    }
                    { // populate our player object / return
                        if ($stmt->rowCount() > 0) 
                        {
                            while($row = $stmt->fetch()) 
                            {
                                if ($row['region'] == $pingback_object['region'][0]['guid']) 
                                {
                                    if ($debug) 
                                    {
                                        printf("normally hitting %s with the package\n", $row['url']);
                                    }
                                    $url = $row['url'];
                                    $vars = 'json=' . json_encode($pingback_object);
                                    $vars .= '&type=' . $pingback_type;
                                    
                                    if (isset($_GET['debug']))
                                        $vars .= '&debug=true';

                                    $ch = curl_init( $url );
                                    curl_setopt( $ch, CURLOPT_POST, 1);
                                    curl_setopt( $ch, CURLOPT_POSTFIELDS, $vars);
                                    curl_setopt( $ch, CURLOPT_FOLLOWLOCATION, 1);
                                    curl_setopt( $ch, CURLOPT_HEADER, 0);
                                    curl_setopt( $ch, CURLOPT_RETURNTRANSFER, 1);

                                    $response = curl_exec( $ch );
                                    if (isset($_GET['debug']))
                                        echo $response; // hide the response.
                                }            
                            }
                        } 
                    }
                }

            }
        }
        printf("\n%d items parsed and saved by the WritheM API Scrapper", $items);
        
        
    } // --------------------

}
else
{  // BAD REQUEST
    header(':', true, 400);
    echo "bad api request! help <a href=\"../help/\">here</a>\n";
}

?>