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

$region = (isset($_GET['region']) ? $_GET['region'] : 1);
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
            $region = $row['id'];
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
        
        foreach ($package as $p) {
            foreach($p as $v) {
                print_r($v);
                
                // if ($v[2].plext.markup[0][1].plain == "Your ")
                // {
                // /*  if (json[2].plext.markup[0][1].plain == "Your ") {
                      // console.log("ignoring log for a 'Your' entry."); */
                    // // ignore this entry... who cares about my person stuff? nobody!
                // } 
                // elseif ($v[2].plext.markup[1][1].plain == " deployed an ") 
                // {
                // /*  } else if (json[2].plext.markup[1][1].plain == " deployed an ") {
                      // pguid = json[2].plext.markup[0][1].guid;
                      // var res = json[2].plext.markup[2][1].plain;
                      // var port = json[2].plext.markup[4][1].guid;
                      // var writhem_temp = "key="+WRITHEMAPIKEY+"&method=save&table=deploy";
                      // writhem_temp = writhem_temp + "&logid=" + json[0];
                      // writhem_temp = writhem_temp + "&ts=" + new Date(json[1]).toJSON();
                      // writhem_temp = writhem_temp + "&user=" + pguid;
                      // writhem_temp = writhem_temp + "&res=" + res;
                      // writhem_temp = writhem_temp + "&portal=" + port;
                      // //console.log("hitting writhem api with : "+writhem_temp);
                      // $('#writhem_logs').load(WRITHEMAPIURL,writhem_temp); */
                // }
                // elseif ($v[2].plext.markup[1][1].plain == " destroyed an ")
                // {
                // /*  } else if (json[2].plext.markup[1][1].plain == " destroyed an ") {
                      // pguid = json[2].plext.markup[0][1].guid;
                      // var res = json[2].plext.markup[2][1].plain;
                      // var port = json[2].plext.markup[4][1].guid;
                      // var writhem_temp = "key="+WRITHEMAPIKEY+"&method=save&table=destroy";
                      // writhem_temp = writhem_temp + "&logid=" + json[0];
                      // writhem_temp = writhem_temp + "&ts=" + new Date(json[1]).toJSON();
                      // writhem_temp = writhem_temp + "&user=" + pguid;
                      // writhem_temp = writhem_temp + "&res=" + res;
                      // writhem_temp = writhem_temp + "&portal=" + port;
                      // //console.log("hitting writhem api with : "+writhem_temp);
                      // $('#writhem_logs').load(WRITHEMAPIURL,writhem_temp);*/
                // }
                // elseif ($v[2].plext.markup[1][1].plain == " destroyed the Link ")
                // {
                // /*  } else if (json[2].plext.markup[1][1].plain == " destroyed the Link ") {
                      // pguid = json[2].plext.markup[0][1].guid;
                      // var writhem_temp = "key="+WRITHEMAPIKEY+"&method=save&table=break";
                      // writhem_temp = writhem_temp + "&logid=" + json[0];
                      // writhem_temp = writhem_temp + "&ts=" + new Date(json[1]).toJSON();
                      // writhem_temp = writhem_temp + "&user=" + pguid;
                      // writhem_temp = writhem_temp + "&portal1=" + json[2].plext.markup[2][1].guid;
                      // writhem_temp = writhem_temp + "&portal2=" + json[2].plext.markup[4][1].guid;
                      // //console.log("hitting writhem api with : "+writhem_temp);
                      // $('#writhem_logs').load(WRITHEMAPIURL,writhem_temp);*/
                // }
                // elseif ($v[2].plext.markup[1][1].plain == " linked ")
                // {
                // /*  } else if (json[2].plext.markup[1][1].plain == " linked ") {
                      // pguid = json[2].plext.markup[0][1].guid;
                      // var writhem_temp = "key="+WRITHEMAPIKEY+"&method=save&table=linked";
                      // writhem_temp = writhem_temp + "&logid=" + json[0];
                      // writhem_temp = writhem_temp + "&ts=" + new Date(json[1]).toJSON();
                      // writhem_temp = writhem_temp + "&user=" + pguid;
                      // writhem_temp = writhem_temp + "&portal1=" + json[2].plext.markup[2][1].guid;
                      // writhem_temp = writhem_temp + "&portal2=" + json[2].plext.markup[4][1].guid;
                      // //console.log("hitting writhem api with : "+writhem_temp);
                      // $('#writhem_logs').load(WRITHEMAPIURL,writhem_temp);*/
                // }
                // elseif ($v[2].plext.markup[1][1].plain == " captured ")
                // {
                // /*  } else if (json[2].plext.markup[1][1].plain == " captured ") {
                      // pguid = json[2].plext.markup[0][1].guid;
                      // var writhem_temp = "key="+WRITHEMAPIKEY+"&method=save&table=captured";
                      // writhem_temp = writhem_temp + "&logid=" + json[0];
                      // writhem_temp = writhem_temp + "&ts=" + new Date(json[1]).toJSON();
                      // writhem_temp = writhem_temp + "&user=" + pguid;
                      // writhem_temp = writhem_temp + "&portal=" + json[2].plext.markup[2][1].guid;
                      // //console.log("hitting writhem api with : "+writhem_temp);
                      // $('#writhem_logs').load(WRITHEMAPIURL,writhem_temp);*/
                // }
                // elseif ($v[2].plext.markup[1][1].plain == " destroyed a Control Field @")
                // {
                // /*  } else if (json[2].plext.markup[1][1].plain == " destroyed a Control Field @") {
                      // pguid = json[2].plext.markup[0][1].guid;
                      // var writhem_temp = "key="+WRITHEMAPIKEY+"&method=save&table=liberate";
                      // writhem_temp = writhem_temp + "&logid=" + json[0];
                      // writhem_temp = writhem_temp + "&ts=" + new Date(json[1]).toJSON();
                      // writhem_temp = writhem_temp + "&user=" + pguid;
                      // writhem_temp = writhem_temp + "&portal=" + json[2].plext.markup[2][1].guid;
                      // writhem_temp = writhem_temp + "&mus=" + json[2].plext.markup[4][1].plain;
                      // //console.log("hitting writhem api with : "+writhem_temp);
                      // $('#writhem_logs').load(WRITHEMAPIURL,writhem_temp);*/
                // }
                // elseif ($v[2].plext.markup[1][1].plain == " created a Control Field @")
                // {
                // /*  } else if (json[2].plext.markup[1][1].plain == " created a Control Field @") {
                      // pguid = json[2].plext.markup[0][1].guid;
                      // var writhem_temp = "key="+WRITHEMAPIKEY+"&method=save&table=control";
                      // writhem_temp = writhem_temp + "&logid=" + json[0];
                      // writhem_temp = writhem_temp + "&ts=" + new Date(json[1]).toJSON();
                      // writhem_temp = writhem_temp + "&user=" + pguid;
                      // writhem_temp = writhem_temp + "&portal=" + json[2].plext.markup[2][1].guid;
                      // writhem_temp = writhem_temp + "&mus=" + json[2].plext.markup[4][1].plain;
                      // //console.log("hitting writhem api with : "+writhem_temp);
                      // $('#writhem_logs').load(WRITHEMAPIURL,writhem_temp);*/
                // }
                // elseif ($v[2].plext.markup[0][1].plain == "The Link ")
                // {
                // /*  } else if (json[2].plext.markup[0][1].plain == "The Link ") {
                      // pguid = json[2].plext.markup[0][1].guid;
                      // var writhem_temp = "key="+WRITHEMAPIKEY+"&method=save&table=decayed";
                      // writhem_temp = writhem_temp + "&logid=" + json[0];
                      // writhem_temp = writhem_temp + "&ts=" + new Date(json[1]).toJSON();
                      // writhem_temp = writhem_temp + "&portal1=" + json[2].plext.markup[2][1].guid;
                      // writhem_temp = writhem_temp + "&portal2=" + json[2].plext.markup[4][1].guid;
                      // //console.log("hitting writhem api with : "+writhem_temp);
                      // $('#writhem_logs').load(WRITHEMAPIURL,writhem_temp);*/
                // }
                // elseif ($v[2].plext.markup[1][1].plain == " destroyed a ")
                // {
                // /*  } else if (json[2].plext.markup[1][1].plain == " destroyed a ") { //portal mod - destroy
                      // pguid = json[2].plext.markup[0][1].guid;
                      // var writhem_temp = "key="+WRITHEMAPIKEY+"&method=save&table=pmoddestroy";
                      // writhem_temp = writhem_temp + "&logid=" + json[0];
                      // writhem_temp = writhem_temp + "&ts=" + new Date(json[1]).toJSON();
                      // writhem_temp = writhem_temp + "&user=" + json[2].plext.markup[0][1].guid;
                      // writhem_temp = writhem_temp + "&portal=" + json[2].plext.markup[4][1].guid;
                      // writhem_temp = writhem_temp + "&mod=" + json[2].plext.markup[2][1].plain;
                      // //console.log("hitting writhem api with : "+writhem_temp);
                      // $('#writhem_logs').load(WRITHEMAPIURL,writhem_temp);*/
                // }
                // else 
                // { // this method is here just to catch new features as they are added to ingress/intel
                    // $message = "looks like a new and unsupported method has been implemented into the intel website. Here are the details:\n\n".$_GET['object']."\n\n";
                    // ob_start();
                    // print_r(json_decode(urldecode($_GET['object'])));
                    // $message .= ob_get_clean();
                    // mail($cfg['site']['contact'], $cfg['site']['title'], $message);

                    // header(':', true, 501);
                    // printf("<div id=\"fail\">\n  <error details=\"unsupported intel ingress object for the save method\" />\n</div>\n");
                // }
            }
        }
        /*if ($_GET['table'] == 'email' && isset($_GET['object'])) 
        { // this method is here just to catch new features as they are added to ingress/intel
            $message = "looks like a new and unsupported method has been implemented into the intel website. Here are the details:\n\n".$_GET['object']."\n\n";
            ob_start();
            print_r(json_decode(urldecode($_GET['object'])));
            $message .= ob_get_clean();
            mail("michael@writhem.com", 'Calgary Ingress API Alerter', $message);

            header(':', true, 501);
            printf("<div id=\"fail\">\n  <error details=\"unsupported intel ingress object for the save method\" />\n</div>\n");
        }*/
        /*elseif ($_GET['table'] == 'portal') 
        {
            { // build the query
                $parms = array();
                $query = "INSERT INTO `ingress`.`portals` (`guid`, `address`, `latE6`, `lngE6`, `name`, `team`, `region`) VALUES (:guid, :address, :latE6, :lngE6, :name, :team, :region)
    ON DUPLICATE KEY UPDATE `team`=:team, `region`=:region;";
                $parms[] = array(':guid',$_GET['guid']);
                $parms[] = array(':address',$_GET['address']);
                $parms[] = array(':latE6',$_GET['latE6']);
                $parms[] = array(':lngE6',$_GET['lngE6']);
                $parms[] = array(':name',$_GET['name']);
                $parms[] = array(':team',$_GET['team']);
                $parms[] = array(':region',(int)$region);
                    
                $stmt = $db->prepare($query);
                foreach($parms as $parm) {
                    $stmt->bindValue($parm[0], $parm[1]);    
                }
            }

            { // execute the update
                
                try 
                {
                    $stmt->execute();
                    if ($stmt->rowCount() > 0) 
                    {
                        header(':', true, 201);
                        echo "<div id=\"success\">\n  <success details=\"pmoddestroy log updated\" />\n</div>\n";
                    } 
                    else
                    {
                        if ($debug)
                        {
                            print_r($parms);
                            print_r($stmt);
                        }                
                        header(':', true, 206);
                        echo "<div id=\"fail_insert\">\n  <error details=\"entry may already exist in its provided state.\" />\n</div>\n";
                    }
                }
                catch (PDOException $e)
                {
                    header(':', true, 500);
                    printf("<div id=\"fail_query\">\n  <error details=\"%s\" />\n</div>\n", $e->getMessage());
                    exit();
                }
            }
        }*/
        /*elseif ($_GET['table'] == 'player') 
        {
            { // build the query
                $parms = array();
                $query = "INSERT INTO `ingress`.`players` (`guid`, `name`, `team`, `region`) VALUES (:guid, :name, :team, :region)
    ON DUPLICATE KEY UPDATE `name`=:name, team`=:team, `region`=:region;";
                $parms[] = array(':guid',$_GET['guid']);
                $parms[] = array(':name',$_GET['name']);
                $parms[] = array(':team',$_GET['team']);
                $parms[] = array(':region',(int)$region);
                    
                $stmt = $db->prepare($query);
                foreach($parms as $parm) {
                    $stmt->bindValue($parm[0], $parm[1]);    
                }
            }

            { // execute the update
                
                try 
                {
                    $stmt->execute();
                    if ($stmt->rowCount() > 0) 
                    {
                        header(':', true, 201);
                        echo "<div id=\"success\">\n  <success details=\"pmoddestroy log updated\" />\n</div>\n";
                    } 
                    else
                    {
                        if ($debug)
                        {
                            print_r($parms);
                            print_r($stmt);
                        }                
                        header(':', true, 206);
                        echo "<div id=\"fail_insert\">\n  <error details=\"entry may already exist in its provided state.\" />\n</div>\n";
                    }
                }
                catch (PDOException $e)
                {
                    header(':', true, 500);
                    printf("<div id=\"fail_query\">\n  <error details=\"%s\" />\n</div>\n", $e->getMessage());
                    exit();
                }
            }
        }*/
        /*elseif ($_GET['table'] == 'deploy' || $_GET['table'] == 'destroy') 
        {
            { // build the query
                $parms = array();
                $query = sprintf("INSERT INTO `ingress`.`%s_log` (`guid`, `datetime`, `user`, `portal`, `res`, `region`) VALUES (:logid, :datetime, :user, :portal, :res, :region)
                ON DUPLICATE KEY UPDATE `datetime`=:datetime, `res`=:res, `region=:region;",
                $_GET['table']);
                $parms[] = array(':logid',$_GET['logid']);
                $parms[] = array(':datetime',strtotime($_GET['ts']));
                $parms[] = array(':user',$_GET['user']);
                $parms[] = array(':portal',$_GET['portal']);
                $parms[] = array(':res',$_GET['res']);
                $parms[] = array(':region',$region);
                    
                $stmt = $db->prepare($query);
                foreach($parms as $parm) {
                    $stmt->bindValue($parm[0], $parm[1]);    
                }
            }
            
            { // execute the update
                
                try 
                {
                    $stmt->execute();
                    if ($stmt->rowCount() > 0) 
                    {
                        header(':', true, 201);
                        echo "<div id=\"success\">\n  <success details=\"pmoddestroy log updated\" />\n</div>\n";
                    } 
                    else
                    {
                        if ($debug)
                        {
                            print_r($parms);
                            print_r($stmt);
                        }                
                        header(':', true, 206);
                        echo "<div id=\"fail_insert\">\n  <error details=\"entry may already exist in its provided state.\" />\n</div>\n";
                    }
                }
                catch (PDOException $e)
                {
                    header(':', true, 500);
                    printf("<div id=\"fail_query\">\n  <error details=\"%s\" />\n</div>\n", $e->getMessage());
                    exit();
                }
            }
        
            { // build a pingback object
                $user = getPlayerObject($db, null, $_GET['user']);
                $portal = getPortalObject($db, $_GET['portal']);
                $regionObject = getRegionObject($db,$region);
                $pingback_object = array('guid'=>$_GET['logid'], 
                    'datetime'=>$_GET['ts'], 
                    'user'=>$user, 
                    'portal'=>$portal,
                    'res'=>$_GET['res'],
                    'region'=>$regionObject);
                $pingback_type = $_GET['table'];
            }
        }*/
        /*elseif ($_GET['table'] == 'break' || $_GET['table'] == 'linked') 
        {
            { // build the query
                $parms = array();
                $query = sprintf("INSERT INTO `ingress`.`%s_log` (`guid`, `datetime`, `user`, `portal1`, `portal2`, `region`) VALUES (:logid, :datetime, :user, :portal1, :portal2, :region)
                ON DUPLICATE KEY UPDATE `datetime`=:datetime, `region`=:region;",
                $_GET['table']);
                $parms[] = array(':logid',$_GET['logid']);
                $parms[] = array(':datetime',strtotime($_GET['ts']));
                $parms[] = array(':user',$_GET['user']);
                $parms[] = array(':portal1',$_GET['portal1']);
                $parms[] = array(':portal2',$_GET['portal2']);
                $parms[] = array(':region',$region);
                    
                $stmt = $db->prepare($query);
                foreach($parms as $parm) {
                    $stmt->bindValue($parm[0], $parm[1]);    
                }
            }
            
            { // execute the update
                
                try 
                {
                    $stmt->execute();
                    if ($stmt->rowCount() > 0) 
                    {
                        header(':', true, 201);
                        echo "<div id=\"success\">\n  <success details=\"pmoddestroy log updated\" />\n</div>\n";
                    } 
                    else
                    {
                        if ($debug)
                        {
                            print_r($parms);
                            print_r($stmt);
                        }                
                        header(':', true, 206);
                        echo "<div id=\"fail_insert\">\n  <error details=\"entry may already exist in its provided state.\" />\n</div>\n";
                    }
                }
                catch (PDOException $e)
                {
                    header(':', true, 500);
                    printf("<div id=\"fail_query\">\n  <error details=\"%s\" />\n</div>\n", $e->getMessage());
                    exit();
                }
            }
        
            { // build a pingback object
                $user = getPlayerObject($db, null, $_GET['user']);
                $portals = getPortalObject($db, array($_GET['portal1'],$_GET['portal1']));
                $regionObject = getRegionObject($db,$region);
                $pingback_object = array('guid'=>$_GET['logid'], 
                    'datetime'=>$_GET['ts'], 
                    'user'=>$user, 
                    'portals'=>$portals,
                    'region'=>$regionObject);
                $pingback_type = $_GET['table'];
            }
        }*/
        /*elseif ($_GET['table'] == 'decayed') 
        {
            { // build the query
                $parms = array();
                $query = "INSERT INTO `ingress`.`decay_log` (`guid`, `datetime`, `portal1`, `portal2`, `region`) VALUES (:logid, :datetime, :portal1, :portal2, :region)
                ON DUPLICATE KEY UPDATE `datetime`='%s', `region`=%d;";
                $parms[] = array(':logid',$_GET['logid']);
                $parms[] = array(':datetime',strtotime($_GET['ts']));
                $parms[] = array(':portal1',$_GET['portal1']);
                $parms[] = array(':portal2',$_GET['portal2']);
                $parms[] = array(':region',$region);
                    
                $stmt = $db->prepare($query);
                foreach($parms as $parm) {
                    $stmt->bindValue($parm[0], $parm[1]);    
                }
            }
            
            { // execute the update
                
                try 
                {
                    $stmt->execute();
                    if ($stmt->rowCount() > 0) 
                    {
                        header(':', true, 201);
                        echo "<div id=\"success\">\n  <success details=\"pmoddestroy log updated\" />\n</div>\n";
                    } 
                    else
                    {
                        if ($debug)
                        {
                            print_r($parms);
                            print_r($stmt);
                        }                
                        header(':', true, 206);
                        echo "<div id=\"fail_insert\">\n  <error details=\"entry may already exist in its provided state.\" />\n</div>\n";
                    }
                }
                catch (PDOException $e)
                {
                    header(':', true, 500);
                    printf("<div id=\"fail_query\">\n  <error details=\"%s\" />\n</div>\n", $e->getMessage());
                    exit();
                }
            }
        
            { // build a pingback object
                $portals = getPortalObject($db, array($_GET['portal1'],$_GET['portal1']));
                $regionObject = getRegionObject($db,$region);
                $pingback_object = array('guid'=>$_GET['logid'], 
                    'datetime'=>$_GET['ts'], 
                    'portals'=>$portals,
                    'region'=>$regionObject);
                $pingback_type = $_GET['table'];
            }
        }*/
        /*elseif ($_GET['table'] == 'liberate' || $_GET['table'] == 'control') 
        {
            { // build the query
                $parms = array();
                $query = sprintf("INSERT INTO `ingress`.`%s_log` (`guid`, `datetime`, `user`, `portal`, `mus`, `region`) VALUES (:logid, :datetime, :user, :portal, :mus, :region)
                ON DUPLICATE KEY UPDATE `datetime`=:datetime, `region`=:region;",
                $_GET['table']);
                $parms[] = array(':logid',$_GET['logid']);
                $parms[] = array(':datetime',strtotime($_GET['ts']));
                $parms[] = array(':user',$_GET['user']);
                $parms[] = array(':portal',$_GET['portal']);
                $parms[] = array(':mus',$_GET['mus']);
                $parms[] = array(':region',$region);
                    
                $stmt = $db->prepare($query);
                foreach($parms as $parm) {
                    $stmt->bindValue($parm[0], $parm[1]);    
                }
            }
            
            { // execute the update
                
                try 
                {
                    $stmt->execute();
                    if ($stmt->rowCount() > 0) 
                    {
                        header(':', true, 201);
                        echo "<div id=\"success\">\n  <success details=\"pmoddestroy log updated\" />\n</div>\n";
                    } 
                    else
                    {
                        if ($debug)
                        {
                            print_r($parms);
                            print_r($stmt);
                        }                
                        header(':', true, 206);
                        echo "<div id=\"fail_insert\">\n  <error details=\"entry may already exist in its provided state.\" />\n</div>\n";
                    }
                }
                catch (PDOException $e)
                {
                    header(':', true, 500);
                    printf("<div id=\"fail_query\">\n  <error details=\"%s\" />\n</div>\n", $e->getMessage());
                    exit();
                }
            }
        
            { // build a pingback object
                $user = getPlayerObject($db, null, $_GET['user']);
                $portal = getPortalObject($db, $_GET['portal']);
                $regionObject = getRegionObject($db,$region);
                $pingback_object = array('guid'=>$_GET['logid'], 
                    'datetime'=>$_GET['ts'], 
                    'user'=>$user, 
                    'portal'=>$portal,
                    'mus'=>$_GET['mus'],
                    'region'=>$regionObject);
                $pingback_type = $_GET['table'];
            }
        }*/
        /*elseif ($_GET['table'] == 'captured') 
        {
            { // build the query
                $parms = array();
                $query = sprintf("INSERT INTO `ingress`.`capture_log` (`guid`, `datetime`, `user`, `portal`, `region`) VALUES (:logid, :datetime, :user, :portal, :region) 
                ON DUPLICATE KEY UPDATE `datetime`=:datetime, `region`=:region;");
                $parms[] = array(':logid',$_GET['logid']);
                $parms[] = array(':datetime',strtotime($_GET['ts']));
                $parms[] = array(':user',$_GET['user']);
                $parms[] = array(':portal',$_GET['portal']);
                $parms[] = array(':region',$region);
                    
                $stmt = $db->prepare($query);
                foreach($parms as $parm) {
                    $stmt->bindValue($parm[0], $parm[1]);    
                }
            }
            
            { // execute the update
                
                try 
                {
                    $stmt->execute();
                    if ($stmt->rowCount() > 0) 
                    {
                        header(':', true, 201);
                        echo "<div id=\"success\">\n  <success details=\"pmoddestroy log updated\" />\n</div>\n";
                    } 
                    else
                    {
                        if ($debug)
                        {
                            print_r($parms);
                            print_r($stmt);
                        }                
                        header(':', true, 206);
                        echo "<div id=\"fail_insert\">\n  <error details=\"entry may already exist in its provided state.\" />\n</div>\n";
                    }
                }
                catch (PDOException $e)
                {
                    header(':', true, 500);
                    printf("<div id=\"fail_query\">\n  <error details=\"%s\" />\n</div>\n", $e->getMessage());
                    exit();
                }
            }
        
            { // build a pingback object
                $user = getPlayerObject($db, null, $_GET['user']);
                $portal = getPortalObject($db, $_GET['portal']);
                $regionObject = getRegionObject($db,$region);
                $pingback_object = array('guid'=>$_GET['logid'], 
                    'datetime'=>$_GET['ts'], 
                    'user'=>$user, 
                    'portal'=>$portal,
                    'region'=>$regionObject);
                $pingback_type = $_GET['table'];
            }
        }
        */
        /*elseif ($_GET['table'] == 'chat') 
        {
            { // build the query
                $parms = array();
                $query = "INSERT INTO `ingress`.`chat_log` (`guid`, `datetime`, `user`, `text`, `secure`, `region`) VALUES (:logid, :datetime, :user, :text, :secure, :region) 
                ON DUPLICATE KEY UPDATE `secure`=:secure, `datetime`=:datetime, `region`=:region";
                $parms[] = array(':logid',$_GET['guid']);
                $parms[] = array(':datetime',strtotime($_GET['ts']));
                $parms[] = array(':user',$_GET['user']);
                $parms[] = array(':text',$_GET['text']);
                $parms[] = array(':secure',($_GET['secure'] == 'true' ? 1 : 0));
                $parms[] = array(':region',$region);
                    
                $stmt = $db->prepare($query);
                foreach($parms as $parm) {
                    $stmt->bindValue($parm[0], $parm[1]);    
                }
            }
            
            { // execute the update
                
                try 
                {
                    $stmt->execute();
                    if ($stmt->rowCount() > 0) 
                    {
                        header(':', true, 201);
                        echo "<div id=\"success\">\n  <success details=\"pmoddestroy log updated\" />\n</div>\n";
                    } 
                    else
                    {
                        if ($debug)
                        {
                            print_r($parms);
                            print_r($stmt);
                        }                
                        header(':', true, 206);
                        echo "<div id=\"fail_insert\">\n  <error details=\"entry may already exist in its provided state.\" />\n</div>\n";
                    }
                }
                catch (PDOException $e)
                {
                    header(':', true, 500);
                    printf("<div id=\"fail_query\">\n  <error details=\"%s\" />\n</div>\n", $e->getMessage());
                    exit();
                }
            }

            { // build a pingback object
                $user = getPlayerObject($db, null, $_GET['user']);
                $regionObject = getRegionObject($db,$region);
                $pingback_object = array('guid'=>$_GET['guid'], 
                    'datetime'=>$_GET['ts'], 
                    'user'=>$user, 
                    'channel'=>($_GET['secure'] == 1 ? $user[0]['faction'] : "PUBLIC"),
                    'text'=>$_GET['text'],
                    'region'=>$regionObject);
                $pingback_type = $_GET['table'];
            }
        }*/
        /*elseif ($_GET['table'] == 'pmoddestroy') 
        {
            { // build the query
                $parms = array();
                $query = sprintf("INSERT INTO `ingress`.`%s_log` (`guid`, `datetime`, `user`, `portal`, `mod`, `region`) VALUES (:logid, :datetime, :userid, :portalid, :mod, :region)
                    ON DUPLICATE KEY UPDATE `datetime`=:datetime, `mod`=:mod, `region`=:region;",
                    $_GET['table']);
                $parms[] = array(':logid',$_GET['logid']);
                $parms[] = array(':datetime',strtotime($_GET['ts']));
                $parms[] = array(':userid',$_GET['user']);
                $parms[] = array(':portalid',$_GET['portal']);
                $parms[] = array(':mod',$_GET['mod']);
                $parms[] = array(':region',$region);
                    
                $stmt = $db->prepare($query);
                foreach($parms as $parm) {
                    $stmt->bindValue($parm[0], $parm[1]);    
                }
            }
            
            { // execute the update
                
                try 
                {
                    $stmt->execute();
                    if ($stmt->rowCount() > 0) 
                    {
                        header(':', true, 201);
                        echo "<div id=\"success\">\n  <success details=\"pmoddestroy log updated\" />\n</div>\n";
                    } 
                    else
                    {
                        if ($debug)
                        {
                            print_r($parms);
                            print_r($stmt);
                        }                
                        header(':', true, 206);
                        echo "<div id=\"fail_insert\">\n  <error details=\"entry may already exist in its provided state.\" />\n</div>\n";
                    }
                }
                catch (PDOException $e)
                {
                    header(':', true, 500);
                    printf("<div id=\"fail_query\">\n  <error details=\"%s\" />\n</div>\n", $e->getMessage());
                    exit();
                }
            }
        
            { // build a pingback object
                $user = getPlayerObject($db, null, $_GET['user']);
                $portal = getPortalObject($db, $_GET['portal']);
                $pingback_object = array('guid'=>$_GET['logid'], 
                    'datetime'=>$_GET['ts'], 
                    'user'=>$user, 
                    'portal'=>$portal,
                    'mod'=>$_GET['mod'],
                    'region'=>getRegionObject($db,$region));
                $pingback_type = 'pmoddestroy';
            }
        }*/
        /*else
        {
            header(':', true, 400);
            printf("<div id=\"fail\">\n  <error details=\"unsupported table for the save method\" />\n</div>\n");
        } */
        
        if ($pingback_object && $pingback_type) 
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
        
    } // --------------------

}
else
{  // BAD REQUEST
    header(':', true, 400);
    echo "bad api request! help <a href=\"../help/\">here</a>\n";
}


///////////////////////////// FUNCTIONS /////////////////////////////
function getPlayerObject(&$db, $name=null, $guids=null, $region=null) 
{
    $users = array();
    { // build the query
        $parms = array();
        $query = "SELECT players.guid, players.name, teams.name as faction, players.region FROM `players`
            LEFT JOIN teams ON players.team=teams.id
            WHERE 1=1";
            
        if (isset($guids)) 
        {
            if (is_array($guids)) 
            {
                $query .= "\nAND (players.guid = :pid0";
                $parms[] = array(':pid0',$guids[0]);
                if (count($guids) > 1) 
                {
                    for ($n = 1;$n < count($guids);$n++) 
                    {
                        $query .= "\nOR players.guid = :pid{$n}";
                        $parms[] = array(":pid{$n}",$guids[$n]);
                    }
                }
                $query .= ")";
            } 
            else 
            {
                $query .= "\nAND players.guid = :pid";
                $parms[] = array(':pid',$guids);
            }
        } 
        if (isset($name)) 
        {
            $query .= "\nAND players.name = :pname";
            $parms[] = array(':pname',$name);
        }
        if (isset($region)) 
        {
            $query .= "\nAND region = :regionid";
            $parms[] = array(':regionid',$region);
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
      
    { // populate our player object / return
        if ($stmt->rowCount() > 0) 
        {
            while($row = $stmt->fetch()) {
                $user = array('guid'=>$row['guid'], 
                    'name'=>$row['name'], 
                    'faction'=>$row['faction'],
                    'region'=>getRegionObject($db, $row['region']));
                array_push ($users, $user);
            }
            return $users;
        } 
        else 
        {
            return false;
        }
    }
}

function getPortalObject(&$db, $guids=null, $region=null) 
{
    $portals = array();
    $parms = array();
    $query = "SELECT portals.guid, portals.address, portals.latE6, portals.lngE6, portals.name, teams.name as faction, portals.region, portals.lastupdate FROM `portals`
        LEFT JOIN teams ON portals.team=teams.id
        WHERE 1=1";
        
    if (isset($guids)) 
    {
        if (is_array($guids)) 
        {
            $query .= "\nAND (portals.guid = :pid0";
            $parms[] = array(':pid0',$guids[0]);
            if (count($guids) > 1) 
            {
                for ($n = 1;$n < count($guids);$n++) 
                {
                    $query .= "\nOR portals.guid = :pid{$n}";
                    $parms[] = array(":pid{$n}",$guids[$n]);
                }
            }
            $query .= ")";
        } 
        else 
        {
            $query .= "\nAND portals.guid = :pid";
            $parms[] = array(':pid',$guids);
        }
    } 
    if (isset($region)) 
    {
        $query .= "\nAND region = :regionid";
        $parms[] = array(':regionid',$region);
    }

    $stmt = $db->prepare($query);
    foreach($parms as $parm) {
        $stmt->bindValue($parm[0], $parm[1]);    
    }
    // print_r($parms);
    // echo "query=" . $stmt->queryString;

    try 
    {
        $stmt->execute();
    } 
    catch (PDOException $e)
    {
        return("<div id=\"fail_query\">\n  <error details=\"%s\" />\n</div>\n" % $e->getMessage());
    }
      
    if ($stmt->rowCount() > 0) 
    {
        while($row = $stmt->fetch()) 
        {
            $portal = array('guid'=>$row['guid'], 
                'name'=>$row['name'], 
                'address'=>$row['address'], 
                'latE6'=>(int)$row['latE6'], 
                'lngE6'=>(int)$row['lngE6'], 
                'name'=>$row['name'], 
                'faction'=>$row['faction'],
                'region'=>getRegionObject($db, $row['region']),
                'lastupdate'=>strtotime($row['lastupdate']),
                );
            array_push ($portals, $portal);
        }
        return $portals;
    } 
    else 
    {
        return false;
    }
}

function getRegionObject(&$db, $guids=null) 
{
    $regions = array();
    $parms = array();
    $query = "SELECT id, name FROM `regions`
        WHERE 1=1";
    if (isset($guids)) 
    {
        if (is_array($guids)) 
        {
            $query .= "\nAND (id = :id0";
            $parms[] = array(':id0',$guids[0]);
            if (count($guids) > 1) 
            {
                for ($n = 1;$n < count($guids);$n++) 
                {
                    $query .= "\nOR id = :id{$n}";
                    $parms[] = array(":id{$n}",$guids[$n]);
                }
            }
            $query .= ")";
        } 
        else 
        {
            $query .= "\nAND id = :id";
            $parms[] = array(':id',$guids);
        }
    } 
    
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
      
    if ($stmt->rowCount() > 0) 
    {
        while($row = $stmt->fetch()) 
        {
            $region = array('guid'=>(int)$row['id'], 
                'name'=>$row['name']);
            array_push ($regions, $region);
        }
        //echo "query=" . $query;
    } 
    else 
    {
        return false;
    }
        return $regions;
}

function getChatObject(&$db, $channel=null, $before=null, $after=null, $limit=null, $region=null) 
{
    $chats = array();
    { // build the query

        $parms = array();
        $query = sprintf("SELECT chat_log.guid, chat_log.datetime, players.guid as user, teams.name AS channel, chat_log.text, chat_log.region, chat_log.secure
            FROM chat_log
            LEFT JOIN players ON chat_log.user = players.guid 
            INNER JOIN teams ON players.team = teams.id
            WHERE 1=1");
        if (isset($channel) && $channel != 0) 
        {
            $query .= "\nAND teams.id = :teamid 
                AND chat_log.secure = 1";
            $parms[] = array(':teamid',$channel);
        } 
        // else 
        // {
            // $query .= "\nAND chat_log.secure = 0";
        // }
        if (isset($region)) 
        {
            $query .= "\nAND chat_log.region = :regionid";
            $parms[] = array(':regionid',$region);
        }
            
        if (isset($before))
        {
            $query .= "\nAND chat_log.datetime <= :datetime";
            $parms[] = array(':datetime',(int)$before);
        } elseif (isset($after))
        {
            $query .= "\nAND chat_log.datetime >= :datetime";
            $parms[] = array(':datetime',(int)$after);
        }
        $query .= "\nORDER BY datetime desc";
        if (isset($limit) && $limit <= 50)
        {
            $query .= "\nLIMIT 0,:max";
            $parms[] = array(':max',$limit);
        }
        else
        {
            $query .= "\nLIMIT 0,50";
        }
    }
    
    { // set up the statement / execute
        $stmt = $db->prepare($query);
        foreach($parms as $parm) {
            $stmt->bindValue($parm[0], $parm[1]);    
        }
         // print_r($stmt);
         // print_r($parms);
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
        if ($stmt->rowCount() > 0) 
        {
            while($row = $stmt->fetch()) {
                $chat = array('guid'=>$row['guid'], 
                    'datetime'=>(int)$row['datetime'], 
                    'user'=>getPlayerObject($db, null, $row['user']), 
                    'channel'=>($row['secure'] == 1 ? $row['channel'] : "PUBLIC"),
                    'text'=>$row['text'],
                    'region'=>getRegionObject($db, $row['region']));
                array_push ($chats, $chat);
            }
            //echo json_encode($chats);
            //echo "query=" . $query;
            return $chats;
        } 
        else 
        {
            return false;
        }
    }
}

function getResonatorObject(&$db, $table, $portals=null, $datetime=null, $limit=null) 
{
    if ($table != "destroy") $table = "deploy";
    $resonators = array();
    { // build the query
        $parms = array();
        $query = sprintf("SELECT resonatorLog.guid, resonatorLog.user, resonatorLog.portal, resonatorLog.res, resonatorLog.datetime
            FROM `%s_log` AS resonatorLog
            WHERE 1=1", $table); // Normally i'd say no to this, but its a boolean table value. 
            // it can only be destroy or deploy.
        
        if (isset($portals)) 
        {
            if (is_array($portals)) 
            {
                $query .= "\nAND (resonatorLog.portal = :portal0";
                $parms[] = array(':portal0',$portals[0]);
                if (count($portals) > 1) 
                {
                    for ($n = 1;$n < count($portals);$n++) 
                    {
                        $query .= "\nOR resonatorLog.portal = :portal{$n}";
                        $parms[] = array(":portal{$n}",$portals[$n]);
                    }
                }
                $query .= ")";
            } 
            else 
            {
                $query .= "\nAND resonatorLog.portal = :portal";
                $parms[] = array(':portal',$portals);
            }
        } 

        if (isset($datetime)) 
        {
            $query .= "\nAND resonatorLog.datetime <= :datetime";
            $parms[] = array(':datetime',(int)$datetime);
        }
        $query .= "\nORDER BY datetime desc";
        if (isset($limit) && $limit <= 50)
        {
            $query .= "\nLIMIT 0,:max";
            $parms[] = array(':max',$limit);
        }
        else
        {
            $query .= "\nLIMIT 0,50";
        }
    }
    
    { // set up the statement / execute
        $stmt = $db->prepare($query);
        foreach($parms as $parm) {
            $stmt->bindValue($parm[0], $parm[1]);    
        }
        // print_r($stmt);
        // print_r($parms);
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
        if ($stmt->rowCount() > 0) 
        {
            while($row = $stmt->fetch()) {
                $resonator = array('guid'=>$row['guid'], 
                    'user'=>getPlayerObject($db, null, $row['user']), 
                    'portal'=>getPortalObject($db, $row['portal']), 
                    'res'=>$row['res'],
                    'datetime'=>(int)$row['datetime']); 
                array_push ($resonators, $resonator);
            }
            return $resonators;
        } 
        else 
        {
            return false; //array('error'=>'no results', 'query'=>$query);
        }
    }
}

function getModObject(&$db, $portals=null, $datetime=null, $limit=null) 
{
    $mods = array();
    { // build the query
        $parms = array();
        $query = "SELECT modLog.guid, modLog.user, modLog.portal, modLog.mod, modLog.datetime
            FROM `pmoddestroy_log` AS modLog
            WHERE 1=1"; 
        
        if (isset($portals)) 
        {
            if (is_array($portals)) 
            {
                $query .= "\nAND (modLog.portal = :portal0";
                $parms[] = array(':portal0',$portals[0]);
                if (count($portals) > 1) 
                {
                    for ($n = 1;$n < count($portals);$n++) 
                    {
                        $query .= "\nOR modLog.portal = :portal{$n}";
                        $parms[] = array(":portal{$n}",$portals[$n]);
                    }
                }
                $query .= ")";
            } 
            else 
            {
                $query .= "\nAND modLog.portal = :portal";
                $parms[] = array(':portal',$portals);
            }
        } 

        if (isset($datetime)) 
        {
            $query .= "\nAND modLog.datetime <= :datetime";
            $parms[] = array(':datetime',(int)$datetime);
        }
        $query .= "\nORDER BY datetime desc";
        if (isset($limit) && $limit <= 50)
        {
            $query .= "\nLIMIT 0,:max";
            $parms[] = array(':max',$limit);
        }
        else
        {
            $query .= "\nLIMIT 0,50";
        }
    }
    
    { // set up the statement / execute
        $stmt = $db->prepare($query);
        foreach($parms as $parm) {
            $stmt->bindValue($parm[0], $parm[1]);    
        }
        // print_r($stmt);
        // print_r($parms);
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
        if ($stmt->rowCount() > 0) 
        {
            while($row = $stmt->fetch()) {
                $mod = array('guid'=>$row['guid'], 
                    'user'=>getPlayerObject($db, null, $row['user']), 
                    'portal'=>getPortalObject($db, $row['portal']), 
                    'mod'=>$row['mod'],
                    'datetime'=>(int)$row['datetime']); 
                array_push ($mods, $mod);
            }
            return $mods;
        } 
        else 
        {
            return false; //array('error'=>'no results', 'query'=>$query);
        }
    }
}

function getLinkObject(&$db, $table, $portals=null, $datetime=null, $limit=null) 
{
    if ($table != "break") {
        $table = "linked";
    }
    $links = array();
    { // build the query
        $parms = array();
        $query = sprintf("SELECT linkLog.guid, linkLog.user, linkLog.portal1, linkLog.portal2, linkLog.datetime 
            FROM `%s_log` AS linkLog
            WHERE 1=1", $table);
            
        if (isset($portals)) 
        {
            if (is_array($portals)) 
            {
                $query .= "\nAND ((linkLog.portal1 = :portal0 OR linkLog.portal2 = :portal0)";
                $parms[] = array(':portal0',$portals[0]);
                if (count($portals) > 1) 
                {
                    for ($n = 1;$n < count($portals);$n++) 
                    {
                        $query .= "\nOR (linkLog.portal1 = :portal{$n} OR linkLog.portal2 =  :portal{$n})";
                        $parms[] = array(":portal{$n}",$portals[$n]);
                    }
                }
                $query .= ")";
            } 
            else 
            {
                $query .= "\nAND (linkLog.portal1 = :portal OR linkLog.portal2 = :portal)";
                $parms[] = array(':portal',$portals);
            }
        } 

        if (isset($datetime)) 
        {
            $query .= "\nAND linkLog.datetime <= :datetime";
            $parms[] = array(':datetime',(int)$datetime);
        }
        $query .= "\nORDER BY datetime desc";
        if (isset($limit) && $limit <= 20)
        {
            $query .= "\nLIMIT 0,:max";
            $parms[] = array(':max',$limit);
        }
        else
        {
            $query .= "\nLIMIT 0,10";
        }
    }
    
    { // set up the statement / execute
        $stmt = $db->prepare($query);
        foreach($parms as $parm) {
            $stmt->bindValue($parm[0], $parm[1]);    
        }
        // print_r($stmt);
        // print_r($parms);
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
        if ($stmt->rowCount() > 0) 
        {
            while($row = $stmt->fetch()) {
                $link = array('guid'=>$row['guid'], 
                    'user'=>getPlayerObject($db, null, $row['user']), 
                    'portals'=>getPortalObject($db, array($row['portal1'],$row['portal2'])), 
                    'datetime'=>(int)$row['datetime']); 
                array_push ($links, $link);
            }
            return $links;
        } 
        else 
        {
            return false; //array('error'=>'no results', 'query'=>$query);
        }
    }
}

function getControlFieldObject(&$db, $table, $portals=null, $datetime=null, $limit=null) 
{
    if ($table != "liberate") {
        $table = "control";
    }
    $fields = array();
    { // build the query
        $parms = array();
        $query = sprintf("SELECT fieldLog.guid, fieldLog.user, fieldLog.portal, fieldLog.mus, fieldLog.datetime 
        FROM `%s_log` AS fieldLog
        WHERE 1=1", $table);
        
        if (isset($portals)) 
        {
            if (is_array($portals)) 
            {
                $query .= "\nAND (fieldLog.portal = :portal0";
                $parms[] = array(':portal0',$portals[0]);
                if (count($portals) > 1) 
                {
                    for ($n = 1;$n < count($portals);$n++) 
                    {
                        $query .= "\nOR fieldLog.portal = :portal{$n}";
                        $parms[] = array(":portal{$n}",$portals[$n]);
                    }
                }
                $query .= ")";
            } 
            else 
            {
                $query .= "\nAND fieldLog.portal = :portal";
                $parms[] = array(':portal',$portals);
            }
        } 

        if (isset($datetime)) 
        {
            $query .= "\nAND fieldLog.datetime <= :datetime";
            $parms[] = array(':datetime',(int)$datetime);
        }
        $query .= "\nORDER BY datetime desc";
        if (isset($limit) && $limit <= 20)
        {
            $query .= "\nLIMIT 0,:max";
            $parms[] = array(':max',$limit);
        }
        else
        {
            $query .= "\nLIMIT 0,10";
        }
    }

    { // set up the statement / execute
        $stmt = $db->prepare($query);
        foreach($parms as $parm) {
            $stmt->bindValue($parm[0], $parm[1]);    
        }
        // print_r($stmt);
        // print_r($parms);
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
        if ($stmt->rowCount() > 0) 
        {
            while($row = $stmt->fetch()) {
                $field = array('guid'=>$row['guid'], 
                    'user'=>getPlayerObject($db, null, $row['user']), 
                    'portals'=>getPortalObject($db, $row['portal']), 
                    'mus'=>$row['mus'], 
                    'datetime'=>(int)$row['datetime']); 
                array_push ($fields, $field);
            }
            return $fields;
        } 
        else 
        {
            return false; //array('error'=>'no results', 'query'=>$query);
        }
    }
}

function getCaptureObject(&$db, $portals=null, $datetime=null, $limit=null)
{
    $controls = array();
    { // build the query
        $parms = array();
        $query = "SELECT captureLog.guid, captureLog.user, captureLog.portal, captureLog.datetime 
        FROM `capture_log` AS captureLog
        WHERE 1=1";
        if (isset($portals)) 
        {
            if (is_array($portals)) 
            {
                $query .= "\nAND (captureLog.portal = :portal0";
                $parms[] = array(':portal0',$portals[0]);
                if (count($portals) > 1) 
                {
                    for ($n = 1;$n < count($portals);$n++) 
                    {
                        $query .= "\nOR captureLog.portal = :portal{$n}";
                        $parms[] = array(":portal{$n}",$portals[$n]);
                    }
                }
                $query .= ")";
            } 
            else 
            {
                $query .= "\nAND captureLog.portal = :portal";
                $parms[] = array(':portal',$portals);
            }
        } 
        if (isset($datetime)) 
        {
            $query .= "\nAND captureLog.datetime <= :datetime";
            $parms[] = array(':datetime',(int)$datetime);
        }
        $query .= "\nORDER BY datetime desc";
        if (isset($limit) && $limit <= 20)
        {
            $query .= "\nLIMIT 0,:max";
            $parms[] = array(':max',$limit);
        }
        else
        {
            $query .= "\nLIMIT 0,10";
        }
    }

    { // set up the statement / execute
        $stmt = $db->prepare($query);
        foreach($parms as $parm) {
            $stmt->bindValue($parm[0], $parm[1]);    
        }
        // print_r($stmt);
        // print_r($parms);
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
        if ($stmt->rowCount() > 0) 
        {
            while($row = $stmt->fetch()) {
                $resonator = array('guid'=>$row['guid'], 
                    'user'=>getPlayerObject($db, null, $row['user']), 
                    'portals'=>getPortalObject($db, $row['portal']), 
                    'datetime'=>(int)$row['datetime']); 
                array_push ($controls, $resonator);
            }
            return $controls;
        } 
        else 
        {
            return false; //array('error'=>'no results', 'query'=>$query);
        }
    }
}
?>