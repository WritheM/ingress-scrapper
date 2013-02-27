<?php
require 'config/config.php';

echo "<p>";
mysql_connect($cfg['db']['host'], $cfg['db']['user'], $cfg['db']['pass'])
  or die("<div id=\"fail_connect\">\n  <error details=\"".mysql_error()."\" />\n</div>\n");
mysql_select_db($cfg['db']['dbase'])
  or die("<div id=\"fail_select_db\">\n  <error details=\"".mysql_error()."\" />\n</div>\n");

if (isset($_GET['key'])) {
    $query = sprintf("UPDATE `api` SET `hits` = `hits`+1 WHERE `api`.`key` = '%s';",
            $_GET['key']);
        $result = mysql_query($query)
          or die("<div id=\"fail_key\">\n  <error details=\"".mysql_error()."\" />\n</div>\n");
    if (mysql_affected_rows() < 1) {
        die("<div id=\"fail_key\">\n  <error details=\"bad api key\" />\n</div>\n");
    }
} else {
?>
Unrecognized User, please provide your api-key below:
<form name="form1" method="get">
<input default="key" name="key" type="text" size=32>
  <button text="go" type="submit">login</button> 
</form>
<p><a href="./api">API</a> &nbsp;&middot;&nbsp; Copyright &#169; 2013 &nbsp;&middot;&nbsp; <a href="http://writhem.com/">WritheM Web Solutions.</a></p>
<?php
die();
}

if (isset($_GET['player'])) {
    echo "<table cellpadding=\"1\" border=\"1\">\n";
  
    $query = sprintf("SELECT * "
       . "FROM ( "
       . "  SELECT players.name, players.guid, teams.name AS faction, deploy_log.res AS highestDeployed "
       . "  FROM players "
       . "  LEFT JOIN deploy_log ON deploy_log.user = players.guid "
       . "  INNER JOIN teams ON players.team = teams.id "
       . "  WHERE players.name = '%s' "
       . "  ORDER BY deploy_log.res DESC "
       . ") AS rawr "
       . "GROUP BY name ",
       $_GET['player']);
    $result = mysql_query($query)
      or die("<div id=\"fail_query\">\n  <error details=\"".mysql_error()."\" />\n</div>\n");
    if (is_resource($result) && mysql_num_rows($result) > 0) {
        while($row = mysql_fetch_array($result)) {
            echo "  <tr>\n    <td>Common Name</td>\n   <td>{$row['name']}</td>\n  </tr>\n";
            echo "  <tr>\n    <td>guid</td>\n   <td>{$row['guid']}</td>\n  </tr>\n";
            echo "  <tr>\n    <td>Faction</td>\n   <td>{$row['faction']}</td>\n  </tr>\n";
            echo "  <tr>\n    <td>Highest Deployed Resonator</td>\n   <td>{$row['highestDeployed']}</td>\n  </tr>\n";
        }
    } else {
        die( "  <tr>\n    <td>player not found</td>\n  </tr>\n");
    }
    
    
    $query = sprintf("SELECT highestDestroyed "
       . "FROM ( "
       . "  SELECT players.name, destroy_log.res AS highestDestroyed "
       . "  FROM players "
       . "  LEFT JOIN destroy_log ON destroy_log.user = players.guid "
       . "  WHERE players.name = '%s' "
       . "  ORDER BY destroy_log.res DESC "
       . ") AS rawr "
       . "GROUP BY name ",
       $_GET['player']);
    $result = mysql_query($query)
      or die("<div id=\"fail_query\">\n  <error details=\"".mysql_error()."\" />\n</div>\n");
    while($row = mysql_fetch_array($result)) {
        echo "  <tr>\n    <td>Highest Destroyed Resonator</td>\n   <td>{$row['highestDestroyed']}</td>\n  </tr>\n";
    }
    
    
    $query = sprintf("SELECT players.name, liberated.mus_freed, controlled.mus_enslaved
       FROM players 
       LEFT JOIN ( 
         SELECT liberate_log.user, liberate_log.mus AS mus_freed
         FROM liberate_log 
         ORDER BY liberate_log.mus DESC 
       ) AS liberated ON liberated.user= players.guid
       LEFT JOIN ( 
         SELECT control_log.user, control_log.mus AS mus_enslaved
         FROM control_log
         ORDER BY control_log.mus DESC 
       ) AS controlled ON controlled.user= players.guid
       WHERE players.name = '%s'
       GROUP BY name ",
            $_GET['player']);
    $result = mysql_query($query)
      or die("<div id=\"fail_query\">\n  <error details=\"".mysql_error()."\" />\n</div>\n");
    while($row = mysql_fetch_array($result)) {
        echo "  <tr>\n    <td>Most MUs Captured at once</td>\n   <td>{$row['mus_enslaved']}</td>\n  </tr>\n";
        echo "  <tr>\n    <td>Most MUs Freed at once</td>\n   <td>{$row['mus_freed']}</td>\n  </tr>\n";
    }
    
    
    $query = sprintf("SELECT players.name, captures.captures, breaks.breaks, links.linked
       FROM players 
       LEFT JOIN ( 
         SELECT user, COUNT(guid) AS captures
         FROM capture_log
         GROUP BY user
       ) AS captures ON captures.user= players.guid
       LEFT JOIN ( 
         SELECT user, COUNT(guid) AS breaks
         FROM break_log
         GROUP BY user
       ) AS breaks ON breaks.user= players.guid
       LEFT JOIN ( 
         SELECT user, COUNT(guid) AS linked
         FROM linked_log
         GROUP BY user
       ) AS links ON links.user= players.guid
       WHERE players.name = '%s' 
       GROUP BY name ",
            $_GET['player']);
    $result = mysql_query($query)
      or die("<div id=\"fail_query\">\n  <error details=\"".mysql_error()."\" />\n</div>\n");
    while($row = mysql_fetch_array($result)) {
        echo "  <tr>\n    <td>Portal Links Broken</td>\n   <td>{$row['breaks']}</td>\n  </tr>\n";
        echo "  <tr>\n    <td>Portal Links Established</td>\n   <td>{$row['linked']}</td>\n  </tr>\n";
        echo "  <tr>\n    <td>Portals Captured</td>\n   <td>{$row['captures']}</td>\n  </tr>\n";
    }


    $query = sprintf("SELECT COUNT(chat_log.guid) as chatLines FROM chat_log
            INNER JOIN players ON chat_log.user=players.guid
            WHERE players.name = '%s'",
            $_GET['player']);
    $result = mysql_query($query)
      or die("<div id=\"fail_query\">\n  <error details=\"".mysql_error()."\" />\n</div>\n");
    while($row = mysql_fetch_array($result)) {
        echo "  <tr>\n    <td>Chat Lines</td>\n   <td>{$row['chatLines']}</td>\n  </tr>\n";
    }
    
    
    echo "  </tr>\n</table>";
} else {
    ?>
    <table cellspacing="1" cellpadding="5">
      <tr align=center>
        <td colspan=2>Top 10 Players (by resonators deployed)</td>
      </tr>
      <tr align=center>
        <td>RESISTANCE</td>
        <td>ENLIGHTENED</td>
      </tr>
      <tr>
        <td><table cellpadding="1" border="1">
      <tr>
        <td>Name</td>
        <td>Highest</td>
      </tr>
    <?php
    $query = sprintf("SELECT * "
           . "FROM ( "
           . "  SELECT players.name, teams.name AS faction, deploy_log.res AS highestDeployed "
           . "  FROM players "
           . "  LEFT JOIN deploy_log ON deploy_log.user = players.guid "
           . "  INNER JOIN teams ON players.team = teams.id "
           . "  WHERE teams.id = 1 "
           . "  ORDER BY deploy_log.res DESC "
           . ") AS rawr "
           . "GROUP BY name "
           . "ORDER BY highestDeployed DESC "
           . "LIMIT 0,10");
    $result = mysql_query($query)
      or die("<div id=\"fail_query\">\n  <error details=\"".mysql_error()."\" />\n</div>\n");
    while($row = mysql_fetch_array($result)) {
        echo "  <tr>\n";
        echo "    <td><a href=\"?key={$_GET['key']}&player={$row['name']}\">{$row['name']}</a></td>\n";
        echo "    <td>{$row['highestDeployed']}</td>\n";
        echo "  </tr>\n";
    }
    ?>
    </table></td>
        <td><table cellpadding="1" border="1">
      <tr>
        <td>Name</td>
        <td>Highest</td>
      </tr>
    <?php
    $query = sprintf("SELECT * "
           . "FROM ( "
           . "  SELECT players.name, teams.name AS faction, deploy_log.res AS highestDeployed "
           . "  FROM players "
           . "  LEFT JOIN deploy_log ON deploy_log.user = players.guid "
           . "  INNER JOIN teams ON players.team = teams.id "
           . "  WHERE teams.id = 2 "
           . "  ORDER BY deploy_log.res DESC "
           . ") AS rawr "
           . "GROUP BY name "
           . "ORDER BY highestDeployed DESC "
           . "LIMIT 0,10");
    $result = mysql_query($query)
      or die("<div id=\"fail_query\">\n  <error details=\"".mysql_error()."\" />\n</div>\n");
    while($row = mysql_fetch_array($result)) {
        echo "  <tr>\n";
        echo "    <td><a href=\"?key={$_GET['key']}&player={$row['name']}\">{$row['name']}</a></td>\n";
        echo "    <td>{$row['highestDeployed']}</td>\n";
        echo "  </tr>\n";
    }
    ?>
    </table></td>
      </tr>
    </table>
    
<hr width=335 align=left>
<table cellspacing="1" cellpadding="5">
  <tr align=center> 
    <td colspan=2>Top 10 Players (by resonators destroyed)</td>
  </tr>
  <tr align=center> 
    <td>RESISTANCE</td>
    <td>ENLIGHTENED</td>
  </tr>
  <tr> 
    <td>
      <table cellpadding="1" border="1">
        <tr> 
          <td>Name</td>
          <td>Highest</td>
        </tr>
        <?php
    $query = sprintf("SELECT * "
           . "FROM ( "
           . "  SELECT players.name, teams.name AS faction, destroy_log.res AS highestDestroyed "
           . "  FROM players "
           . "  LEFT JOIN destroy_log ON destroy_log.user = players.guid "
           . "  INNER JOIN teams ON players.team = teams.id "
           . "  WHERE teams.id = 1 "
           . "  ORDER BY destroy_log.res DESC "
           . ") AS rawr "
           . "GROUP BY name "
           . "ORDER BY highestDestroyed DESC "
           . "LIMIT 0,10");
    $result = mysql_query($query)
      or die("<div id=\"fail_query\">\n  <error details=\"".mysql_error()."\" />\n</div>\n");
    while($row = mysql_fetch_array($result)) {
        echo "  <tr>\n";
        echo "    <td><a href=\"?key={$_GET['key']}&player={$row['name']}\">{$row['name']}</a></td>\n";
        echo "    <td>{$row['highestDestroyed']}</td>\n";
        echo "  </tr>\n";
    }
    ?>
      </table>
    </td>
    <td>
      <table cellpadding="1" border="1">
        <tr> 
          <td>Name</td>
          <td>Highest</td>
        </tr>
        <?php
    $query = sprintf("SELECT * "
           . "FROM ( "
           . "  SELECT players.name, teams.name AS faction, destroy_log.res AS highestDestroyed "
           . "  FROM players "
           . "  LEFT JOIN destroy_log ON destroy_log.user = players.guid "
           . "  INNER JOIN teams ON players.team = teams.id "
           . "  WHERE teams.id = 2 "
           . "  ORDER BY destroy_log.res DESC "
           . ") AS rawr "
           . "GROUP BY name "
           . "ORDER BY highestDestroyed DESC "
           . "LIMIT 0,10");
    $result = mysql_query($query)
      or die("<div id=\"fail_query\">\n  <error details=\"".mysql_error()."\" />\n</div>\n");
    while($row = mysql_fetch_array($result)) {
        echo "  <tr>\n";
        echo "    <td><a href=\"?key={$_GET['key']}&player={$row['name']}\">{$row['name']}</a></td>\n";
        echo "    <td>{$row['highestDestroyed']}</td>\n";
        echo "  </tr>\n";
    }
    ?>
      </table>
    </td>
  </tr>
</table>
<hr width=335 align=left>
<table cellspacing="1" cellpadding="5">
  <tr align=center> 
    <td colspan=2>Top 10 Control Fields</td>
  </tr>
  <tr align=center> 
    <td>Established</td>
    <td>Destroyed</td>
  </tr>
  <tr> 
    <td>
      <table cellpadding="1" border="1">
        <tr> 
          <td>Name</td>
          <td>Faction</td>
          <td>MUs</td>
        </tr>
        <?php
    $query = sprintf("SELECT players.name, teams.name as faction, control_log.mus FROM `control_log` 
            LEFT JOIN `players` ON players.guid = control_log.user
            LEFT JOIN `teams` ON players.team = teams.id
            ORDER BY mus DESC
            LIMIT 0,10");
    $result = mysql_query($query)
      or die("<div id=\"fail_query\">\n  <error details=\"".mysql_error()."\" />\n</div>\n");
    while($row = mysql_fetch_array($result)) {
        echo "  <tr>\n";
        echo "    <td><a href=\"?key={$_GET['key']}&player={$row['name']}\">{$row['name']}</a></td>\n";
        echo "    <td>{$row['faction']}</td>\n";
        echo "    <td>{$row['mus']}</td>\n";
        echo "  </tr>\n";
    }
    ?>
      </table>
    </td>
    <td>
      <table cellpadding="1" border="1">
        <tr>
          <td>Name</td>
          <td>Faction</td>
          <td>MUs</td>
        </tr>
        <?php
    $query = sprintf("SELECT players.name, teams.name as faction, liberate_log.mus FROM `liberate_log` 
            LEFT JOIN `players` ON players.guid = liberate_log.user
            LEFT JOIN `teams` ON players.team = teams.id
            ORDER BY mus DESC
            LIMIT 0,10");
    $result = mysql_query($query)
      or die("<div id=\"fail_query\">\n  <error details=\"".mysql_error()."\" />\n</div>\n");
    while($row = mysql_fetch_array($result)) {
        echo "  <tr>\n";
        echo "    <td><a href=\"?key={$_GET['key']}&player={$row['name']}\">{$row['name']}</a></td>\n";
        echo "    <td>{$row['faction']}</td>\n";
        echo "    <td>{$row['mus']}</td>\n";
        echo "  </tr>\n";
    }
    ?>
      </table>
    </td>
  </tr>
</table>
<hr width=335 align=left>
    <table border=1 cellspacing="1" cellpadding="5">
      <tr align=center>
        <td colspan=3>Stats</td>
      </tr>
      <tr>
        <td></td>
        <td>Resistance </td>
        <td>Enlightened</td>
      </tr>
      <tr>
        <td>Players</td>
    <?php
    $query = sprintf("SELECT COUNT(players.guid) as playerCount, teams.name as faction FROM players
        LEFT JOIN teams ON players.team=teams.id
        GROUP BY team");
    $result = mysql_query($query)
      or die("<div id=\"fail_query\">\n  <error details=\"".mysql_error()."\" />\n</div>\n");
    while($row = mysql_fetch_array($result)) {
         echo "   <td>{$row['playerCount']}</td>\n";
    }
    ?>
      </tr>
      <tr>
        <td>Portals</td>
    <?php
    $query = sprintf("SELECT COUNT(portals.guid) as portalCount, teams.name as faction FROM portals
        LEFT JOIN teams ON portals.team=teams.id
        GROUP BY team");
    $result = mysql_query($query)
      or die("<div id=\"fail_query\">\n  <error details=\"".mysql_error()."\" />\n</div>\n");
    while($row = mysql_fetch_array($result)) {
         echo "   <td>{$row['portalCount']}</td>\n";
    }
    ?>
      </tr>
    </table>
    <?
}
echo "</p><p><a href=\"./api\">API</a> &nbsp;&middot;&nbsp; Copyright &#169; 2013 &nbsp;&middot;&nbsp; <a href=\"http://writhem.com/\">WritheM Web Solutions.</a></p>
";
echo $cfg['analytics'];
?>