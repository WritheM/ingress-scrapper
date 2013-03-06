<?php session_start();
require 'config/config.php'; ?>
<!DOCTYPE html>
<html>
  <head>
    <script src="//code.jquery.com/jquery-latest.min.js" type="text/javascript"></script>
    <script type="text/javascript" src="/js/bootstrap.js"></script>
    <link rel="stylesheet" type="text/css" href="http://fonts.googleapis.com/css?family=Coda">
    <link rel="stylesheet" type="text/css" href="/css/bootstrap.css">
    <link rel="stylesheet" type="text/css" href="/css/misc.css">
    <title><?php echo $cfg['site']['title']; ?></title>

  </head>
  <body>

<?php
if (isset($_GET['logout'])) {
    session_destroy();
    header('location: /');
}
if (isset($_GET['clear']))
    $_SESSION['fail'] = 0;
    
try {
    $conn = "mysql:host={$cfg['db']['host']};dbname={$cfg['db']['dbase']}";
    $db = new PDO($conn, $cfg['db']['user'], $cfg['db']['pass']);
} catch (PDOException $e) {
    printf("<div id=\"fail_connect\">\n  <error details=\"%s\" />\n</div>\n", $e->getMessage());
    exit();
}

if (false) {
    if(isset($_SESSION['key']) && $_SESSION['key'] != null)
        echo "sessionkey\n";
    if(isset($_POST['key']) && $_POST['key'] != null)
        echo "postkey\n";
    if(isset($_POST['email']) && $_POST['email'] != null)
        echo "email\n";
    if(isset($_POST['password']) && $_POST['password'] != null)
        echo "password\n";
    if(isset($_POST['confirm']) && $_POST['confirm'] != null)
        echo "confirm\n";
    if(isset($_GET['afterlogin']) && $_GET['afterlogin'] != null)
        echo "redirect\n";
}
    
$form['key'] = (isset($_POST['key']) && $_POST['key'] != null ? $_POST['key'] : null);
$form['email'] = (isset($_POST['email']) && $_POST['email'] != null ? $_POST['email'] : null);
$form['password'] = (isset($_POST['password']) && $_POST['password'] != null ? $_POST['password'] : null);
$form['confirm'] = (isset($_POST['confirm']) && $_POST['confirm'] != null ? $_POST['confirm'] : null);
$form['redirect'] = (isset($_GET['afterlogin']) && $_GET['afterlogin'] != null ? $_GET['afterlogin'] : null);
$user = array();
dbHit($form, $user, $db);

if ($user['key'] != null)
{
    $_SESSION['key'] = $user['key'];
    echo "    <h3 class=\"offset1\">Welcome, {$user['name']}! </h3>\n";
    echo "    <p class=\"offset2\">Please provide your new contact information below to change it</p>\n";
}
else 
{
    echo "    <h3 class=\"offset1\">A valid user account is required to proceed.</h3>\n";
    echo "    <p class=\"offset2\">Please provide your API key to get started or Log in to continue.</p>\n";
}

if (isset($user['alert_msg']) && isset($user['alert_status']) && isset($user['alert_title'])) {
    echo "    <div class=\"alert alert-{$user['alert_status']} span6 offset1\">\n";
    echo "      <button type=\"button\" class=\"close\" data-dismiss=\"alert\">&times;</button>\n";
    echo "      <h4>{$user['alert_title']}</h4>\n";
    echo "      {$user['alert_msg']}\n";
    echo "    </div>\n";
    echo "    <div class=\"clearfix\"></div>\n";
}
?>    
<!-- COMMON CONTENT START -->
    <form method="POST" class="form-horizontal offset1">
      <div class="control-group" id="divKey">
        <label class="control-label" for="inputKey">Key</label>
        <div class="controls">
          <input class="input-xlarge" type="text" id="inputKey" placeholder="API Key"<?echo ($user['key']!=null?'disabled value="'.$user['key'].'"':' name="key"')?>>
        </div>
      <div class="offset2"><br/>or</div>
      </div>
      <div class="control-group">
        <label class="control-label" for="email">Email</label>
        <div class="controls">
          <input type="email" name="email" id="email" placeholder="Email" value="<?=$user['email']?>">
        </div>
      </div>
      <div class="control-group">
        <label class="control-label" for="password">Password</label>
        <div class="controls">
          <input type="password" pattern="(?=.*\d)(?=.*[a-z])\w{5,15}" name="password" id="password" placeholder="Password" onchange="
              this.setCustomValidity(this.validity.patternMismatch ? 'Invalid password format\nPassword must contain between 5 and 15 characters, including text and at least 1 number' : '');
            ">
        </div>
      </div>
      <?if ($user['key']!=null) {?>
      <div class="control-group" id="confirm">
        <label class="control-label" for="confirm">Password Again</label>
        <div class="controls">
          <input type="password" pattern="(?=.*\d)(?=.*[a-z])\w{5,15}" name="confirm" id="confirm" placeholder="Confirm It" onchange="
              this.setCustomValidity(this.validity.patternMismatch ? 'Does not match\nPlease enter the same Password as above' : '');
              if(this.checkValidity()) form.password.pattern = this.value;
            ">
        </div>
      </div>
      <?}?>
      <div class="control-group">
        <div class="controls btn-group">
          <button type="submit" class="btn"><?=($user['key']==null?'Sign In':'Update')?></button>
          <a class="btn<?if($user['key']==null) echo " disabled"?>" href="?logout">Sign Out</a>
        </div>
      </div>
    </form>
    
    <div id="offline" style="position:absolute;top:50px;right:50px;display:none;">
      <!-- Map Loading Spinner -->
      <div class="img_outerwheel">
        <div class="img_innerwheel">
        </div>
      </div>
      <div id="map_spinner_text">
        Upgrading System... 
      </div>
    </div>
       
    <?if ($user['key'] != null) { ?>
    <div class="offset2">
        <a class="btn btn-mini" href="chat.php<?=($user['key']!=null?"?key={$user['key']}":"")?>">
            <i class="icon icon-comment"></i>
            &nbsp;Chat Logs
        </a>
        <a class="btn btn-mini" href="/help/">
            <i class="icon icon-briefcase"></i>
            &nbsp;API Documentation
        </a>
        <a class="btn btn-mini" href="https://github.com/WritheM/ingress-scrapper/issues">
            <i class="icon icon-warning-sign"></i>
            &nbsp;Issues / Suggestions
        </a>
    </div>
    <? } ?>
<!-- END CONTENT -->
  <div id="footer" style="
                    width: 100%;
                    bottom: 0px; z-order:-1">

           <a href="./api">API</a>
           &nbsp;&middot;&nbsp; Copyright &#169; 2013 &nbsp;&middot;&nbsp; <a href="http://writhem.com/">WritheM Web Solutions.</a>
  </div>

  <?
  echo $cfg['analytics'];
  ?>
</body>
</html>

<?php
// functions and junk

// will process a login, or update of db then update the passed $user object for rendering.
function dbHit($form, &$user, &$db) {
    // make sure the user isn't locked out of db calls.
    if (checkFail($user))
        return false;
        
    if ($_SESSION['key'] != null && $form['email'] != null && $form['password'] != null && $form['confirm'] != null && $form['password'] === $form['confirm'])
    { // change password and email then return, dont continue in this function.
        if (dbHit(null, $user, $db)) 
        { // the session appears valid, process the update
            $stmt = $db->prepare("UPDATE `api` SET `email` = :email, `password` = md5(:password) WHERE `key` = :key");
            $stmt->bindValue(':key',$_SESSION['key']);
            $stmt->bindValue(':email',$form['email']);
            $stmt->bindValue(':password',$form['password']);
                        
            if ($stmt->execute()) 
            { // executed successfully
                $user['key'] = $_SESSION['key'];
                $user['name'] = $_SESSION['name'];
                $user['email'] = $form['email'];
                $user['password'] = true;
                
                $user['alert_status'] = "success";
                $user['alert_title'] = "Awesome!";
                $user['alert_msg'] = "Thank you for updating your account. We got your new details updated in the database.";

                if (isset($form['redirect']) && $user['email'] != null && $user['password']) 
                    header("location: {$form['redirect']}?key={$user['key']}");
                
                return true;
            }
            else 
            { // should never happen, just additional error checking. db working?
                $user['alert_status'] = "error";
                $user['alert_title'] = "uhhh...";
                $user['alert_msg'] = "well that's not supposed to happen... i had some trouble updating the db.";
                
                return false;
            }
        }
        else
        { // invalid session, dont update anything!
            $user['alert_status'] = "error";
            $user['alert_title'] = "uhhh...";
            $user['alert_msg'] = "well that's not supposed to happen... seems we have an invaild session and i can't update anything.";
            
            return false;
        }
    }
    else if (($form['key'] != null || $_SESSION['key'] != null) && $form['confirm'] == null) 
    { // prepare for login via key, first by session then by form.
        $stmt = $db->prepare("SELECT `key`, `name`, `email`, `password` FROM `api` WHERE `key` = :key;UPDATE `api` SET `hits` = `hits`+1 WHERE `key` = :key; ");
        if (isset($_SESSION['key']))
            $stmt->bindValue(':key',$_SESSION['key']);
        elseif(isset($form['key']))
            $stmt->bindValue(':key',$_POST['key']);
    }
    else if ($form['email'] != null && $form['email'] != null && $form['confirm'] == null) 
    { // prepare for login via email/pass
        $stmt = $db->prepare("SELECT `key`, `name`, `email`, `password` FROM `api` WHERE `email` = :email AND `password` = md5(:password);UPDATE `api` SET `hits` = `hits`+1 WHERE `email` = :email AND `password` = md5(:password); ");
        $stmt->bindValue(':email',$form['email']);
        $stmt->bindValue(':password',$form['password']);
    }
    
    if (isset($stmt)) 
    { // process the prepared login
        $stmt->execute();
        if ($stmt->rowCount() > 0) 
        { // user is valid, populate the $user object or redirect if required.
            while ($row = $stmt->fetch()) 
            { // there can be only 1...
                $user['key'] = $row['key'];
                $user['name'] = $row['name'];
                $user['email'] = $row['email'];
                $user['password'] = ($row['password'] != null ? true : false);
            }

            $_SESSION['key'] = $user['key'];
            $_SESSION['name'] = $user['name'];
            $_SESSION['fail'] = 0;
            checkFail($user);
            
            // redirect if required, and user info is updated.
            if (isset($form['redirect']) && $user['email'] != null && $user['password']) 
                header("location: {$form['redirect']}?key={$user['key']}");
            else 
                return true;
        } 
        else
        { // ha, that's not a valid user... break the $user, increase the fail counter, and warn the user
            $user = null;
            $_SESSION['fail']++;
            checkFail($user);
            return false;
        }
    }
    return false;
}

function checkFail(&$user) {
    if ($_SESSION['fail'] >= 5)
    {
        $user['alert_status'] = "error";
        $user['alert_title'] = "Error";
        $user['alert_msg'] = "Your account is currently locked. To request an unlock, you can wait 30 minutes or email admin at writhem dot com";
        return true;
    } 
    elseif ($_SESSION['fail'] > 0) 
    {
        $user['alert_status'] = "alert";
        $user['alert_title'] = "Warning";
        $user['alert_msg'] = "Incorrect Login attempt {$_SESSION['fail']} of 5. You will be unable to login after you have exceeded login attempts.";
    }
    else
    {
        $user['alert_status'] = null;
    }
        
    return false;
}
?>
