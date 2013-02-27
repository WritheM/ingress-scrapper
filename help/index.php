<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Transitional//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd">
<html xmlns="http://www.w3.org/1999/xhtml" xml:lang="en" lang="en">
<head>

<meta http-equiv="Content-Type" content="text/html; charset=utf-8" />
<title>WritheM Ingress Scrapper API Documentation</title>

<link rel='stylesheet' type='text/css' media='all' href='/css/userguide.css' />

<meta http-equiv='expires' content='-1' />
<meta http-equiv= 'pragma' content='no-cache' />
<meta name='robots' content='all' />

</head>
<body>

<!-- START NAVIGATION -->
<div id="nav"><div id="nav_inner"></div></div>
<div id="nav2"><a name="top">&nbsp;</a></div>
<div id="masthead">
<table cellpadding="0" cellspacing="0" border="0" style="width:100%">
<tr>
<td>
        <h1>WritheM Ingress Scrapper API Documentation</h1>
      </td>
</tr>
</table>
</div>
<!-- END NAVIGATION -->


<!-- START BREADCRUMB -->
<table cellpadding="0" cellspacing="0" border="0" style="width:100%;display:none;">
<tr>
<td id="searchbox"><form method="get" action="http://www.google.com/search"><input type="hidden" name="as_sitesearch" id="as_sitesearch" value="nasbox.writhem.com/" />Search&nbsp; <input type="text" class="input" style="width:200px;" name="q" id="q" size="31" maxlength="255" value="" />&nbsp;<input type="submit" class="submit" name="sa" value="Go" /></form></td>
</tr>
</table>
<!-- END BREADCRUMB -->

<br clear="all" />


<!-- START CONTENT -->
<div id="content">
	
<h1>Overview</h1>

<ul>
	<li><a href="#accesskeys">Access Keys</a></li>
	<li><a href="#requests">Requests</a></li>
	<ul>
		<li><a href="#available">Avialable API Requests</a></li>
		<li><a href="#details">Detailed Explanations</a></li>
		<ul>
			<li><a href="#view_players">view_players</a></li>
			<li><a href="#view_faction">view_faction</a></li>
            <li><a href="#player">player</a></li>
            <li><a href="#portal">portal</a></li>
            <li><a href="#deploydestroy">deploy / destroy</a></li>
            <li><a href="#linkedbreak">linked / break</a></li>
			<li><a href="#decayed">decayed</a></li>
            <li><a href="#controlliberate">control / liberate</a></li>
            <li><a href="#captured">captured</a></li>
			<li><a href="#chat">chat</a></li>
			<li><a href="#pmoddestroy">pmoddestroy</a></li>
		</ul>
	</ul>
	<li><a href="#appendix">Appendix</a></li>
	<ul>
		<li><a href="#playerObject">#1 Player Object</a></li>
		<li><a href="#portalObject">#2 Portal Object</a></li>
		<li><a href="#modObject">#3 Mod Object</a></li>
		<li><a href="#resonatorObject">#4 Resonator Object</a></li>
		<li><a href="#linkObject">#5 Link Object</a></li>
		<li><a href="#controlFieldObject">#6 Control Field Object</a></li>
		<li><a href="#chatObject">#7 Chat Object</a></li>
		<li><a href="#regionObject">#8 Region Object</a></li>
		<li><a href="#resources">External Resources</a></li>
	</ul>
</ul>


<a name="accesskeys"></a>
<h1>Access Keys</h1>

<p>Something on the Keys</p>

<p class="important"><strong>Important:</strong>&nbsp; Old Keys are not valid anymore, you need to request a re-add of them.</p>
  <p>To request an access key please email me <script type="text/javascript">var user = "&#109;&#105;&#99;&#104;&#97;&#101;&#108;";var domain = "&#119;&#114;&#105;&#116;&#104;&#101;&#109;&#46;&#99;&#111;&#109;";var mail = user + "&#64;" + domain;var message = "&#109;&#105;&#99;&#104;&#97;&#101;&#108;&#64;&#119;&#114;&#105;&#116;&#104;&#101;&#109;&#46;&#99;&#111;&#109;";document.write("<a"+" "+"href=\"&#109;&#97;&#105;&#108;&#116;&#111;&#58;"+mail+"\">"+message+"</a>");</script> with a breif 
    description of why you need a key (whats it used for?)</p>
<br clear="all" />

<a name="requests"></a>
<h1>Requests</h1>

<p>The API is queried via following url:</p>

  <code><strong>http://ingress.writhem.com/api/?</strong>PARAMS</code> 
  <table cellpadding="0" cellspacing="1" border="0" style="width:100%" class="tableborder">
    <tr> 
      <th>Parameter</th>
      <th>Description</th>
      <th>Required</th>
    </tr>
    <tr> 
      <td class="td"><strong>key</strong></td>
      <td class="td">Your API-Key you received from wm.</td>
      <td class="td">Yes</td>
    </tr>
    <tr> 
      <td class="td"><strong>table</strong></td>
      <td class="td">The database table you want to hit. see <a href="#available">Available Requests</a></td>
      <td class="td">Yes</td>
    </tr>
  </table>

  <p>You will have to send a simple HTTP GET Request to the given URL with your 
    Key, and the table Parameter. As Result you 
    will receive a HTTP Response with variable HTTP-Status:</p>

<table cellpadding="0" cellspacing="1" border="0" style="width:100%" class="tableborder">
<tr>
	<th>Status</th>
	<th>Description</th>
</tr>
<tr>
	<td class="td">200</td>
	<td class="td">Your Request was successful. The Body contains your Results.</td>
</tr>
<tr>
	<td class="td">201</td>
	<td class="td">Your Request was successful. We have updated the database with your results.</td>
</tr>
<tr>
        <td class="td">204</td>
        <td class="td">Your Request was successful. There is no data to be listed (no results found).</td>
</tr>
<tr>
        <td class="td">206</td>
        <td class="td">Your Request was successful. The data may already exist in the database.</td>
</tr>
<tr>
	<td class="td">400</td>
	<td class="td">Your Request was malformed.</td>
</tr>
<tr>
	<td class="td">401</td>
	  <td class="td">Your maximum Queries has been exceeded. Contact us for increasing 
        your Limits.</td>
</tr>
<tr>
	<td class="td">403</td>
	<td class="td">Your Key isn't valid. The Body will contain more Information.</td>
</tr>
<tr>
	<td class="td">500</td>
	<td class="td">An internal Error occured. You did nothing wrong ;)</td>
</tr>
<tr>
	<td class="td">501</td>
	<td class="td">This method is not yet implemented. ... coming soon.</td>
</tr>
<tr>
        
      <td class="td">503</td>
        <td class="td">The API backend is down, usualy it will be up again after 2-5min.</td>
</tr>

</table>

<a name="available"></a>
<h2>Avialable Tables</h2>

  <ul>
    <li><a href="#view_players">view_players</a> via query</li>
    <li><a href="#view_faction">view_faction</a> via query</li>
    <li><a href="#player">player</a> via query save</li>
    <li><a href="#portal">portal</a> via query or save</li>
    <li><a href="#chat">chat</a> via query or save</li>
    <li><a href="#deploydestroy">deploy</a> via query or save</li>
    <li><a href="#deploydestroy">destroy</a> via query or save</li>
    <li><a href="#linkedbreak">linked</a> via query or save</li>
    <li><a href="#linkedbreak">break</a> via query or save</li>
    <li><a href="#controlliberate">liberate</a> via query or save</li>
    <li><a href="#controlliberate">control</a> via query or save</li>
    <li><a href="#captured">captured</a> via query or save</li>
    <li><a href="#decayed">decayed</a> via save</li>
    <li><a href="#pmoddestroy">pmoddestroy</a> via query or save</li>
  </ul>

<a name="details"></a>
<h2>Detailed Explanations</h2>

<p>Here you will find Explanations to each of our available Requests.</p>

<a name="view_players"></a>
  <h3>view_players</h3>
  <p>Very simular to the <a href="#playerObject">Player Objects</a>, however this view will allow
  you to view the highest level resonator deployed by this player. This should give you a rough
  idea of what level this player might be.</p>
  <p>Additional arguments are as follows:</p>
  <table cellpadding="0" cellspacing="1" border="0" style="width:100%" class="tableborder">
    <tr> 
      <th>Parameter</th>
      <th>Description</th>
      <th>Required</th>
    </tr>
    <tr> 
      <td class="td">faction</td>
      <td class="td">Either a 1 (RESISTENCE), a 2 (ENLIGHTENED), or a 3 (UNCLAIMED)</td>
      <td class="td">No</td>
    </tr>
    <tr> 
      <td class="td">player</td>
      <td class="td">filter to show just 1 player</td>
      <td class="td">No</td>
    </tr>
  </table>
  <p>This is a view, not an object. therefore it does not conform to the listed object formats in the appendex.</p>
  <h4>Request:</h4>

  <code> $ curl -D - "http://ingress.writhem.com/api/?key=<#YOUR_KEY_HERE#>&table=view_players&player=pironic" 
  </code> 
  <h4>Response:</h4>

  <code> HTTP/1.1 200 OK<br>
  Server: Apache/2.2.22 (Ubuntu)<br>
  X-Powered-By: PHP/5.3.10-1ubuntu3.4<br>
  Access-Control-Allow-Origin: http://www.ingress.com<br>
  Vary: Accept-Encoding<br>
  Content-Type: text/html<br>
  Content-Length: 111<br>
  Accept-Ranges: bytes<br>
  Date: Sat, 09 Feb 2013 18:54:38 GMT<br>
  X-Varnish: 1857835409<br>
  Age: 0<br>
  Via: 1.1 varnish<br>
  Connection: keep-alive<br>
  X-Served-By: nasbox<br>
  X-Cache: MISS<br>
  X-Cache-Hits: 0<br>
  [<br>
  &nbsp;&nbsp;{<br>
  &nbsp;&nbsp;&nbsp;&nbsp;&quot;guid&quot;:&quot;10a71e0c7e82473787712b2d484a82d1.c&quot;,<br>
  &nbsp;&nbsp;&nbsp;&nbsp;&quot;name&quot;:&quot;pironic&quot;,<br>
  &nbsp;&nbsp;&nbsp;&nbsp;&quot;faction&quot;:&quot;ENLIGHTENED&quot;,<br>
  &nbsp;&nbsp;&nbsp;&nbsp;&quot;highestDeployed&quot;:null<br>
  &nbsp;&nbsp;}<br>
  ]</code> 
  
<a name="view_faction"></a>
  <p>Allows you to view the current status of each faction. Specifically viewing the controlled
  portals and member counts for each.</p>
  <h3>view_faction</h3>
  <p>Additional arguments are as follows:</p>
  <table cellpadding="0" cellspacing="1" border="0" style="width:100%" class="tableborder">
    <tr> 
      <th>Parameter</th>
      <th>Description</th>
      <th>Required</th>
    </tr>
    <tr> 
      <td class="td">faction</td>
      <td class="td">Either a 1 (RESISTENCE), a 2 (ENLIGHTENED), or a 3 (UNCLAIMED)</td>
      <td class="td">No</td>
    </tr>
  </table>
  <p>This is a view, not an object. therefore it does not conform to the listed object formats in the appendex.</p>
  <h4>Request:</h4>

  <code> $ curl -D - "http://ingress.writhem.com/api/?key=<#YOUR_KEY_HERE#>&table=view_faction" 
  </code> 
  <h4>Response:</h4>

  <code> HTTP/1.1 200 OK<br>
  Server: Apache/2.2.22 (Ubuntu)<br>
  X-Powered-By: PHP/5.3.10-1ubuntu3.4<br>
  Access-Control-Allow-Origin: http://www.ingress.com<br>
  Vary: Accept-Encoding<br>
  Content-Type: text/html<br>
  Content-Length: 111<br>
  Accept-Ranges: bytes<br>
  Date: Sat, 09 Feb 2013 18:54:38 GMT<br>
  X-Varnish: 1857835409<br>
  Age: 0<br>
  Via: 1.1 varnish<br>
  Connection: keep-alive<br>
  X-Served-By: nasbox<br>
  X-Cache: MISS<br>
  X-Cache-Hits: 0<br>
  [<br>
  &nbsp;&nbsp;{<br>
  &nbsp;&nbsp;&nbsp;&nbsp;&quot;guid&quot;:&quot;1&quot;,<br> 
  &nbsp;&nbsp;&nbsp;&nbsp;&quot;name&quot;:&quot;RESISTANCE&quot;,<br>
  &nbsp;&nbsp;&nbsp;&nbsp;&quot;portalCount&quot;:&quot;93&quot;,<br>
  &nbsp;&nbsp;&nbsp;&nbsp;&quot;playerCount&quot;:&quot;60&quot;<br>
  &nbsp;&nbsp;},<br>
  &nbsp;&nbsp;{<br>
  &nbsp;&nbsp;&nbsp;&nbsp;&quot;guid&quot;:&quot;2&quot;,<br> 
  &nbsp;&nbsp;&nbsp;&nbsp;&quot;name&quot;:&quot;ENLIGHTENED&quot;,<br>
  &nbsp;&nbsp;&nbsp;&nbsp;&quot;portalCount&quot;:&quot;37&quot;,<br>
  &nbsp;&nbsp;&nbsp;&nbsp;&quot;playerCount&quot;:&quot;76&quot;<br>
  &nbsp;&nbsp;},<br>
  &nbsp;&nbsp;...<br>
  ]</code> 
  
<a name="player"></a>
  <h3>player</h3>
  <p>Allows you to search for a particular player, or list all players.</p>
  <p>Additional arguments are as follows:</p>
  <table cellpadding="0" cellspacing="1" border="0" style="width:100%" class="tableborder">
    <tr> 
      <th>Parameter</th>
      <th>Description</th>
      <th>Required</th>
    </tr>
    <tr> 
      <td class="td">guids</td>
      <td class="td">comma seperated list of guids to filter</td>
      <td class="td">No</td>
    </tr>
    <tr> 
      <td class="td">name</td>
      <td class="td">filter to show just 1 player by name</td>
      <td class="td">No</td>
    </tr>
  </table>
  <p>This will return an Array of <a href="#playerObject">Player Objects</a> that match your query.</p>

    
<a name="portal"></a>
  <h3>portal</h3>
  <p>Allows you to search for a particular portal, an array of portals, or list all portals.</p>
  <p>Additional arguments are as follows:</p>
  <table cellpadding="0" cellspacing="1" border="0" style="width:100%" class="tableborder">
    <tr> 
      <th>Parameter</th>
      <th>Description</th>
      <th>Required</th>
    </tr>
    <tr> 
      <td class="td">guids</td>
      <td class="td">comma seperated list of guids to filter for</td>
      <td class="td">No</td>
    </tr>
  </table>
  <p>This will return an Array of <a href="#portalObject">Portal Objects</a> that match your query.</p>

    
<a name="deploydestroy"></a>
<h3>deploy / destroy</h3>
  <p>Allows you to search for a particular resonator that has either been destoryed or deployed.</p>
  <p>Additional arguments are as follows:</p>
  <table cellpadding="0" cellspacing="1" border="0" style="width:100%" class="tableborder">
    <tr> 
      <th>Parameter</th>
      <th>Description</th>
      <th>Required</th>
    </tr>
    <tr> 
      <td class="td">portals</td>
      <td class="td">A comma deliminated list of portal guids to filter to.</td>
      <td class="td">No</td>
    </tr>
    <tr> 
      <td class="td">datetime</td>
      <td class="td">Will return the most recent events to happen before this timestamp.</td>
      <td class="td">No</td>
    </tr>
    <tr> 
      <td class="td">limit</td>
      <td class="td">Default = 10; Max = 50; Used to specify the number of results returned.</td>
      <td class="td">No</td>
    </tr>
  </table>
  <p>This will return an Array of <a href="#resonatorObject">Resonator Objects</a> that match your query or the last 10 results.</p>


<a name="linkedbreak"></a>
<h3>linked / break</h3>
  <p>Allows you to search for a particular portal link that has either been established or broken.</p>
  <p>Additional arguments are as follows:</p>
  <table cellpadding="0" cellspacing="1" border="0" style="width:100%" class="tableborder">
    <tr> 
      <th>Parameter</th>
      <th>Description</th>
      <th>Required</th>
    </tr>
    <tr> 
      <td class="td">portals</td>
      <td class="td">A comma deliminated list of portal guids to filter to.</td>
      <td class="td">No</td>
    </tr>
    <tr> 
      <td class="td">datetime</td>
      <td class="td">Will return the most recent events to happen before this timestamp.</td>
      <td class="td">No</td>
    </tr>
    <tr> 
      <td class="td">limit</td>
      <td class="td">Default = 10; Max = 20; Used to specify the number of results returned.</td>
      <td class="td">No</td>
    </tr>
  </table>
  <p>This will return an Array of <a href="#linkObject">Link Objects</a> that match your query or the last 10 results.</p>

  
<a name="decayed"></a>
<h3>decayed</h3>
  <p>Allows you to search for a particular instance of link decay..</p>
  <p>No additional search paramaters are available yet.</p>
  <!--<p>Additional arguments are as follows:</p>
  <table cellpadding="0" cellspacing="1" border="0" style="width:100%" class="tableborder">
    <tr> 
      <th>Parameter</th>
      <th>Description</th>
      <th>Required</th>
    </tr>
    <tr> 
      <td class="td">guid</td>
      <td class="td">Default is 0 (Public), 1 (Resistance), or 2 (Enlightened)</td>
      <td class="td">No</td>
    </tr>
    <tr> 
      <td class="td">portal</td>
      <td class="td">Will return the most recent events to happen before this timestamp.</td>
      <td class="td">No</td>
    </tr>
    <tr> 
      <td class="td">limit</td>
      <td class="td">Can not be set more than 50, but can reduce the number of results returned.</td>
      <td class="td">No</td>
    </tr>
  </table>-->
  <p>This will return an Array of <a href="#linkObject">Link Objects</a> that match your query or the last 50 results. The user will be null.</p>

<a name="controlliberate"></a>
<h3>control / liberate</h3>
  <p>Allows you to search for a particular control field that has either been established or broken.</p>
  <p>Additional arguments are as follows:</p>
  <table cellpadding="0" cellspacing="1" border="0" style="width:100%" class="tableborder">
    <tr> 
      <th>Parameter</th>
      <th>Description</th>
      <th>Required</th>
    </tr>
    <tr> 
      <td class="td">portals</td>
      <td class="td">A comma deliminated list of portal guids to filter to.</td>
      <td class="td">No</td>
    </tr>
    <tr> 
      <td class="td">datetime</td>
      <td class="td">Will return the most recent events to happen before this timestamp.</td>
      <td class="td">No</td>
    </tr>
    <tr> 
      <td class="td">limit</td>
      <td class="td">Default = 10; Max = 20; Used to specify the number of results returned.</td>
      <td class="td">No</td>
    </tr>
  </table>
  <p>This will return an Array of <a href="#controlFieldObject">Control Field Objects</a> that match your query or the last 10 results.</p>


<a name="captured"></a>
<h3>captured</h3>
  <p>Allows you to search for a particular capture log of a portal.</p>
  <p>Additional arguments are as follows:</p>
  <table cellpadding="0" cellspacing="1" border="0" style="width:100%" class="tableborder">
    <tr> 
      <th>Parameter</th>
      <th>Description</th>
      <th>Required</th>
    </tr>
    <tr> 
      <td class="td">portals</td>
      <td class="td">A comma deliminated list of portal guids to filter to.</td>
      <td class="td">No</td>
    </tr>
    <tr> 
      <td class="td">datetime</td>
      <td class="td">Will return the most recent events to happen before this timestamp.</td>
      <td class="td">No</td>
    </tr>
    <tr> 
      <td class="td">limit</td>
      <td class="td">Default = 10; Max = 20; Used to specify the number of results returned.</td>
      <td class="td">No</td>
    </tr>
  </table>
  <p>This will return an Array of <a href="#linkObject">Link Objects</a> that match your query or the last 10 results. This is not a typo... A Link of only 1 portal is a capture.</p>


<a name="chat"></a>
<h3>chat</h3>
  <p>Allows you to search for a particular player, or list all players.</p>
  <p>Additional arguments are as follows:</p>
  <table cellpadding="0" cellspacing="1" border="0" style="width:100%" class="tableborder">
    <tr> 
      <th>Parameter</th>
      <th>Description</th>
      <th>Required</th>
    </tr>
    <tr> 
      <td class="td">channel</td>
      <td class="td">Default is 0 (Public), 1 (Resistance), or 2 (Enlightened)</td>
      <td class="td">No</td>
    </tr>
    <tr> 
      <td class="td">datetime</td>
      <td class="td">Will return the most recent events to happen before this timestamp.</td>
      <td class="td">No</td>
    </tr>
    <tr> 
      <td class="td">limit</td>
      <td class="td">Can not be set more than 50, but can reduce the number of results returned.</td>
      <td class="td">No</td>
    </tr>
  </table>
  <p>This will return an Array of <a href="#chatObject">Chat Objects</a> that match your query or the last 50 results.</p>


<a name="pmoddestroy"></a>
<h3>pmoddestroy</h3>
  <p>Allows you to search for a particular log entry of a Portal Mod being destroyed.</p>
  <p>Additional arguments are as follows:</p>
  <table cellpadding="0" cellspacing="1" border="0" style="width:100%" class="tableborder">
    <tr> 
      <th>Parameter</th>
      <th>Description</th>
      <th>Required</th>
    </tr>
    <tr> 
      <td class="td">portals</td>
      <td class="td">A list of comma delaminated portal guids to search for.</td>
      <td class="td">No</td>
    </tr>
    <tr> 
      <td class="td">datetime</td>
      <td class="td">Will return the most recent events to happen before this timestamp.</td>
      <td class="td">No</td>
    </tr>
    <tr> 
      <td class="td">limit</td>
      <td class="td">Can not be set more than 50, but can reduce the number of results returned.</td>
      <td class="td">No</td>
    </tr>
  </table>
  <p>This will return an Array of <a href="#modObject">Mod Objects</a> that match your query or the last 50 results.</p>


<a name="appendix"></a> 
<h1>Appendix</h1>

<p>Here you find some more Information on some Functions and Objects.</p>

<a name="playerObject"></a>
  <h2>#1 Player Object</h2>

  <code> {<br>
  &nbsp;&nbsp;&quot;guid&quot;:&quot;10a71e0c7e82473787712b2d484a82d1.c&quot;,<br>
  &nbsp;&nbsp;&quot;name&quot;:&quot;pironic&quot;,<br>
  &nbsp;&nbsp;&quot;faction&quot;:&quot;ENLIGHTENED&quot;,<br>
  &nbsp;&nbsp;&quot;region&quot;:<br>
  &nbsp;&nbsp;&nbsp;&nbsp;[{<br>
  &nbsp;&nbsp;&nbsp;&quot;guid&quot;:1,<br>
  &nbsp;&nbsp;&nbsp;&quot;name&quot;:&quot;calgary&quot;<br>
 &nbsp;&nbsp;&nbsp;&nbsp;}]<br>
  }
  </code> 
  <table cellpadding="0" cellspacing="1" border="0" style="width:100%" class="tableborder">
    <tr> 
      <th>Key</th>
      <th>Value</th>
      <th>Description</th>
    </tr>
    <tr> 
      <td class="td"><strong>guid</strong></td>
      <td class="td">String</td>
      <td class="td">Unique hash id of the user.</td>
    </tr>
    <tr> 
      <td class="td"><strong>name</strong></td>
      <td class="td">String</td>
      <td class="td">Common human readable user name</td>
    </tr>
    <tr> 
      <td class="td"><strong>faction</strong></td>
      <td class="td">String</td>
      <td class="td">either ENLIGHTENED or RESISTENCE</td>
    </tr>
    <tr> 
      <td class="td"><strong>region</strong></td>
      <td class="td">Array(Region Object)</td>
      <td class="td">A <a href="#regionObject">Region Object</a> associated with this log entry</td>
    </tr>
  </table>
  
  <a name="portalObject"></a>
  <h2>#2 Portal Object</h2>

  <code> {<br>
  &nbsp;&nbsp;&quot;guid&quot;:&quot;44597c6f102b45179337008390009ea3.11&quot;,<br>
  &nbsp;&nbsp;&quot;name&quot;:&quot;Lighthouse&quot;,<br>
  &nbsp;&nbsp;&quot;address&quot;:&quot;Country Village Park Northeast, Calgary, 
  AB T3K 0T3, Canada&quot;,<br>
  &nbsp;&nbsp;&quot;latE6&quot;:&quot;51160725&quot;,<br>
  &nbsp;&nbsp;&quot;lngE6&quot;:&quot;-114056946&quot;,<br>
  &nbsp;&nbsp;&quot;faction&quot;:&quot;ENLIGHTENED&quot;,<br>
  &nbsp;&nbsp;&quot;region&quot;:<br>
  &nbsp;&nbsp;&nbsp;&nbsp;[{<br>
  &nbsp;&nbsp;&nbsp;&nbsp;&quot;guid&quot;:1,<br>
  &nbsp;&nbsp;&nbsp;&nbsp;&quot;name&quot;:&quot;calgary&quot;<br>
  &nbsp;&nbsp;&nbsp;&nbsp;}],<br>
  &nbsp;&nbsp;&quot;lastupdate&quot;:1360526631<br>
  }</code> 
  <table cellpadding="0" cellspacing="1" border="0" style="width:100%" class="tableborder">
    <tr> 
      <th>Key</th>
      <th>Value</th>
      <th>Description</th>
    </tr>
    <tr> 
      <td class="td"><strong>guid</strong></td>
      <td class="td">String</td>
      <td class="td">Unique hash id of the user.</td>
    </tr>
    <tr> 
      <td class="td"><strong>name</strong></td>
      <td class="td">String</td>
      <td class="td">Common human readable portal name</td>
    </tr>
    <tr> 
      <td class="td"><strong>address</strong></td>
      <td class="td">String</td>
      <td class="td">The human readable postmail address of the portal</td>
    </tr>
    <tr> 
      <td class="td"><strong>latE6</strong></td>
      <td class="td">int</td>
      <td class="td">The E6 Latitude coordinates of the portal</td>
    </tr>
    <tr> 
      <td class="td"><strong>lngE6</strong></td>
      <td class="td">int</td>
      <td class="td">The E6 Longitude coordinates of the portal</td>
    </tr>
    <tr> 
      <td class="td"><strong>faction</strong></td>
      <td class="td">String</td>
      <td class="td">The human readable faction controlling this portal.</td>
    </tr>
    <tr> 
      <td class="td"><strong>region</strong></td>
      <td class="td">Array(Region Object)</td>
      <td class="td">A <a href="#regionObject">Region Object</a> associated with this log entry</td>
    </tr>
    <tr> 
      <td class="td"><strong>lastupdate</strong></td>
      <td class="td">datetime</td>
      <td class="td">MySQL Last modified of the portal. This is null if there 
        is only 1 entry of the portal existing in the database. As soon as its 
        contested once, this date is updated.</td>
    </tr>
  </table>

<a name="modObject"></a>
  <h2>#3 Mod Object</h2>
  
  <code> {<br>
  &nbsp;&nbsp;&quot;guid&quot;:&quot;2e42043d766b4ae48d66a619b3497f90.d&quot;,<br>
  &nbsp;&nbsp;&quot;user&quot;:<br>
  &nbsp;&nbsp;&nbsp;&nbsp;[{<br>
  &nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&quot;guid&quot;:&quot;10a71e0c7e82473787712b2d484a82d1.c&quot;,<br>
  &nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&quot;name&quot;:&quot;pironic&quot;,<br>
  &nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&quot;faction&quot;:&quot;ENLIGHTENED&quot;,<br>
  &nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&quot;region&quot;:<br>
  &nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;[{<br>
  &nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&quot;guid&quot;:1,<br>
  &nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&quot;name&quot;:&quot;calgary&quot;<br>
  &nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;}]<br>
  &nbsp;&nbsp;&nbsp;&nbsp;}],<br>
  &nbsp;&nbsp;&quot;portal&quot;:<br>
  &nbsp;&nbsp;&nbsp;&nbsp;[{<br>
  &nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&quot;guid&quot;:&quot;44597c6f102b45179337008390009ea3.11&quot;,<br>
  &nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&quot;name&quot;:&quot;Lighthouse&quot;,<br>
  &nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&quot;address&quot;:&quot;Country Village 
  Park Northeast, Calgary, AB T3K 0T3, Canada&quot;,<br>
  &nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&quot;latE6&quot;:51160725,<br>
  &nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&quot;lngE6&quot;:-114056946,<br>
  &nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&quot;team&quot;:&quot;ENLIGHTENED&quot;,<br>
  &nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&quot;lastupdate&quot;:null,<br>
  &nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&quot;region&quot;:<br>
  &nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;[{<br>
  &nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&quot;guid&quot;:1,<br>
  &nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&quot;name&quot;:&quot;calgary&quot;<br>
  &nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;}]<br>
  &nbsp;&nbsp;&nbsp;&nbsp;}],<br>
  &nbsp;&nbsp;&quot;mod&quot;:&quot;Common&quot;,<br>
  &nbsp;&nbsp;&quot;datetime&quot;:1360435393<br>
  }</code> 
  <table cellpadding="0" cellspacing="1" border="0" style="width:100%" class="tableborder">
    <tr> 
      <th>Key</th>
      <th>Value</th>
      <th>Description</th>
    </tr>
    <tr> 
      <td class="td"><strong>guid</strong></td>
      <td class="td">String</td>
      <td class="td">Unique hash id of the log entry.</td>
    </tr>
    <tr> 
      <td class="td"><strong>user</strong></td>
      <td class="td">Array(Player Object)</td>
      <td class="td">A <a href="#playerObject">Player Object</a> that initaited this log entry</td>
    </tr>
    <tr> 
      <td class="td"><strong>portal</strong></td>
      <td class="td">Array(Portal Object)</td>
      <td class="td">A <a href="#portalObject">Portal Object</a> continaining the portal affected by this 
        Resonator</td>
    </tr>
    <tr> 
      <td class="td"><strong>res</strong></td>
      <td class="td">String</td>
      <td class="td">The resonator level.</td>
    </tr>
    <tr> 
      <td class="td"><strong>datetime</strong></td>
      <td class="td">timestamp</td>
      <td class="td">UTC timestamp of the event. this is in the format of seconds 
        since unix epoc.</td>
    </tr>
  </table>

<a name="resonatorObject"></a>
  <h2>#4 Resonator Object</h2>

  <code> {<br>
  &nbsp;&nbsp;&quot;guid&quot;:&quot;2e42043d766b4ae48d66a619b3497f90.d&quot;,<br>
  &nbsp;&nbsp;&quot;user&quot;:<br>
  &nbsp;&nbsp;&nbsp;&nbsp;[{<br>
  &nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&quot;guid&quot;:&quot;10a71e0c7e82473787712b2d484a82d1.c&quot;,<br>
  &nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&quot;name&quot;:&quot;pironic&quot;,<br>
  &nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&quot;faction&quot;:&quot;ENLIGHTENED&quot;,<br>
  &nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&quot;region&quot;:<br>
  &nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;[{<br>
  &nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&quot;guid&quot;:1,<br>
  &nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&quot;name&quot;:&quot;calgary&quot;<br>
  &nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;}]<br>
  &nbsp;&nbsp;&nbsp;&nbsp;}],<br>
  &nbsp;&nbsp;&quot;portal&quot;:<br>
  &nbsp;&nbsp;&nbsp;&nbsp;[{<br>
  &nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&quot;guid&quot;:&quot;44597c6f102b45179337008390009ea3.11&quot;,<br>
  &nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&quot;name&quot;:&quot;Lighthouse&quot;,<br>
  &nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&quot;address&quot;:&quot;Country Village 
  Park Northeast, Calgary, AB T3K 0T3, Canada&quot;,<br>
  &nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&quot;latE6&quot;:51160725,<br>
  &nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&quot;lngE6&quot;:-114056946,<br>
  &nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&quot;team&quot;:&quot;ENLIGHTENED&quot;,<br>
  &nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&quot;region&quot;:<br>
  &nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;[{<br>
  &nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&quot;guid&quot;:1,<br>
  &nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&quot;name&quot;:&quot;calgary&quot;<br>
  &nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;}],<br>
  &nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&quot;lastupdate&quot;:null<br>
  &nbsp;&nbsp;&nbsp;&nbsp;}],<br>
  &nbsp;&nbsp;&quot;res&quot;:&quot;L3&quot;,<br>
  &nbsp;&nbsp;&quot;datetime&quot;:1360435393<br>
  }</code> 
  <table cellpadding="0" cellspacing="1" border="0" style="width:100%" class="tableborder">
    <tr> 
      <th>Key</th>
      <th>Value</th>
      <th>Description</th>
    </tr>
    <tr> 
      <td class="td"><strong>guid</strong></td>
      <td class="td">String</td>
      <td class="td">Unique hash id of the log entry.</td>
    </tr>
    <tr> 
      <td class="td"><strong>user</strong></td>
      <td class="td">Array(Player Object)</td>
      <td class="td">A <a href="#playerObject">Player Object</a> that initaited this log entry</td>
    </tr>
    <tr> 
      <td class="td"><strong>portal</strong></td>
      <td class="td">Array(Portal Object)</td>
      <td class="td">A <a href="#portalObject">Portal Object</a> continaining the portal affected by this 
        Resonator</td>
    </tr>
    <tr> 
      <td class="td"><strong>res</strong></td>
      <td class="td">String</td>
      <td class="td">The resonator level.</td>
    </tr>
    <tr> 
      <td class="td"><strong>datetime</strong></td>
      <td class="td">timestamp</td>
      <td class="td">UTC timestamp of the event. this is in the format of seconds 
        since unix epoc.</td>
    </tr>
  </table>

<a name="linkObject"></a>
  <h2>#5 Link Object</h2>

  <code> {<br>
  &nbsp;&nbsp;&quot;guid&quot;:&quot;2e42043d766b4ae48d66a619b3497f90.d&quot;,<br>
  &nbsp;&nbsp;&quot;user&quot;:<br>
  &nbsp;&nbsp;&nbsp;&nbsp;[{<br>
  &nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&quot;guid&quot;:&quot;10a71e0c7e82473787712b2d484a82d1.c&quot;,<br>
  &nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&quot;name&quot;:&quot;pironic&quot;,<br>
  &nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&quot;faction&quot;:&quot;ENLIGHTENED&quot;,<br>
  &nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&quot;region&quot;:<br>
  &nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;[{<br>
  &nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&quot;guid&quot;:1,<br>
  &nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&quot;name&quot;:&quot;calgary&quot;<br>
  &nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;}]<br>
  &nbsp;&nbsp;&nbsp;&nbsp;}],<br>
  &nbsp;&nbsp;&quot;portals&quot;:<br>
  &nbsp;&nbsp;&nbsp;&nbsp;[{<br>
  &nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&quot;guid&quot;:&quot;44597c6f102b45179337008390009ea3.11&quot;,<br>
  &nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&quot;name&quot;:&quot;Lighthouse&quot;,<br>
  &nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&quot;address&quot;:&quot;Country Village 
  Park Northeast, Calgary, AB T3K 0T3, Canada&quot;,<br>
  &nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&quot;latE6&quot;:51160725,<br>
  &nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&quot;lngE6&quot;:-114056946,<br>
  &nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&quot;team&quot;:&quot;ENLIGHTENED&quot;,<br>
  &nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&quot;region&quot;:<br>
  &nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;[{<br>
  &nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&quot;guid&quot;:1,<br>
  &nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&quot;name&quot;:&quot;calgary&quot;<br>
  &nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;}],<br>
  &nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&quot;lastupdate&quot;:null<br>
  &nbsp;&nbsp;&nbsp;&nbsp;},{<br>
  &nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&quot;guid&quot;:&quot;44597c6f102b45179337008390009ea3.11&quot;,<br>
  &nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&quot;name&quot;:&quot;Lighthouse&quot;,<br>
  &nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&quot;address&quot;:&quot;Country Village 
  Park Northeast, Calgary, AB T3K 0T3, Canada&quot;,<br>
  &nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&quot;latE6&quot;:51160725,<br>
  &nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&quot;lngE6&quot;:-114056946,<br>
  &nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&quot;team&quot;:&quot;ENLIGHTENED&quot;,<br>
  &nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&quot;region&quot;:<br>
  &nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;[{<br>
  &nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&quot;guid&quot;:1,<br>
  &nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&quot;name&quot;:&quot;calgary&quot;<br>
  &nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;}],<br>
  &nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&quot;lastupdate&quot;:null<br>
  &nbsp;&nbsp;&nbsp;&nbsp;}],<br>
  &nbsp;&nbsp;&quot;datetime&quot;:1360435393<br>
  }</code> 
  <table cellpadding="0" cellspacing="1" border="0" style="width:100%" class="tableborder">
    <tr> 
      <th>Key</th>
      <th>Value</th>
      <th>Description</th>
    </tr>
    <tr> 
      <td class="td"><strong>guid</strong></td>
      <td class="td">String</td>
      <td class="td">Unique hash id of the log entry.</td>
    </tr>
    <tr> 
      <td class="td"><strong>user</strong></td>
      <td class="td">Array(Player Object)</td>
      <td class="td">A <a href="#playerObject">Player Object</a> that initaited this 
        log entry</td>
    </tr>
    <tr> 
      <td class="td"><strong>portals</strong></td>
      <td class="td">Array(Portal Object)</td>
      <td class="td">An Array of <a href="#portalObject">Portal Objects</a> continaining 
        the portals affected by this link</td>
    </tr>
    <tr> 
      <td class="td"><strong>datetime</strong></td>
      <td class="td">timestamp</td>
      <td class="td">UTC timestamp of the event. this is in the format of seconds 
        since unix epoc.</td>
    </tr>
  </table>

  
<a name="controlFieldObject"></a>
  <h2>#6 Control Field Object</h2>

  <code> {<br>
  &nbsp;&nbsp;&quot;guid&quot;:&quot;2e42043d766b4ae48d66a619b3497f90.d&quot;,<br>
  &nbsp;&nbsp;&quot;user&quot;:<br>
  &nbsp;&nbsp;&nbsp;&nbsp;[{<br>
  &nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&quot;guid&quot;:&quot;10a71e0c7e82473787712b2d484a82d1.c&quot;,<br>
  &nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&quot;name&quot;:&quot;pironic&quot;,<br>
  &nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&quot;faction&quot;:&quot;ENLIGHTENED&quot;,<br>
  &nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&quot;region&quot;:<br>
  &nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;[{<br>
  &nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&quot;guid&quot;:1,<br>
  &nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&quot;name&quot;:&quot;calgary&quot;<br>
  &nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;}]<br>
  &nbsp;&nbsp;&nbsp;&nbsp;}],<br>
  &nbsp;&nbsp;&quot;portal&quot;:<br>
  &nbsp;&nbsp;&nbsp;&nbsp;[{<br>
  &nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&quot;guid&quot;:&quot;f6efbb0220804fff8435a232cca7c226.11&quot;,<br>
  &nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&quot;name&quot;:&quot;Lighthouse&quot;,<br>
  &nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&quot;address&quot;:&quot;Country Village 
  Park Northeast, Calgary, AB T3K 0T3, Canada&quot;,<br>
  &nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&quot;latE6&quot;:51160725,<br>
  &nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&quot;lngE6&quot;:-114056946,<br>
  &nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&quot;team&quot;:&quot;ENLIGHTENED&quot;,<br>
  &nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&quot;region&quot;:<br>
  &nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;[{<br>
  &nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&quot;guid&quot;:1,<br>
  &nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&quot;name&quot;:&quot;calgary&quot;<br>
  &nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;}],<br>
  &nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&quot;lastupdate&quot;:null<br>
  &nbsp;&nbsp;&nbsp;&nbsp;}],<br>
  &nbsp;&nbsp;&quot;mus&quot;:&quot;24171&quot;,<br>
  &nbsp;&nbsp;&quot;datetime&quot;:1360435393<br>
  }</code> 
  <table cellpadding="0" cellspacing="1" border="0" style="width:100%" class="tableborder">
    <tr> 
      <th>Key</th>
      <th>Value</th>
      <th>Description</th>
    </tr>
    <tr> 
      <td class="td"><strong>guid</strong></td>
      <td class="td">String</td>
      <td class="td">Unique hash id of the log entry.</td>
    </tr>
    <tr> 
      <td class="td"><strong>user</strong></td>
      <td class="td">Array(Player Object)</td>
      <td class="td">A <a href="#playerObject">Player Object</a> that initaited this 
        log entry</td>
    </tr>
    <tr> 
      <td class="td"><strong>portals</strong></td>
      <td class="td">Array(Portal Object)</td>
      <td class="td">An Array of <a href="#portalObject">Portal Objects</a> continaining 
        the portals affected by this link</td>
    </tr>
    <tr> 
      <td class="td"><strong>datetime</strong></td>
      <td class="td">datetime</td>
      <td class="td">UTC timestamp of the event. this is in the format of seconds 
        since unix epoc.</td>
    </tr>
  </table>

  
<a name="chatObject"></a>
  <h2>#7 Chat Object</h2>

  <code>
  {<br>
  &nbsp;&nbsp;&quot;guid&quot;:&quot;33e862c9589346b3aecd1faf838f3ab2.d&quot;,<br>
  &nbsp;&nbsp;&quot;datetime&quot;:1360436289,<br>
  &nbsp;&nbsp;&quot;user&quot;:<br>
  &nbsp;&nbsp;&nbsp;&nbsp;[{<br>
  &nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&quot;guid&quot;:&quot;10a71e0c7e82473787712b2d484a82d1.c&quot;,<br>
  &nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&quot;name&quot;:&quot;pironic&quot;,<br>
  &nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&quot;faction&quot;:&quot;ENLIGHTENED&quot;,<br>
  &nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&quot;region&quot;:<br>
  &nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;[{<br>
  &nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&quot;guid&quot;:1,<br>
  &nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&quot;name&quot;:&quot;calgary&quot;<br>
  &nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;}]<br>
  &nbsp;&nbsp;&nbsp;&nbsp;}],<br>
  &nbsp;&nbsp;&quot;channel&quot;:&quot;PUBLIC&quot;<br>
  &nbsp;&nbsp;&quot;text&quot;:&quot;Np, if you need some more, let me know.&quot;,<br>
  &nbsp;&nbsp;&quot;region&quot;:<br>
  &nbsp;&nbsp;&nbsp;&nbsp;[{<br>
  &nbsp;&nbsp;&nbsp;&nbsp;&quot;guid&quot;:1,<br>
  &nbsp;&nbsp;&nbsp;&nbsp;&quot;name&quot;:&quot;calgary&quot;<br>
  &nbsp;&nbsp;&nbsp;&nbsp;}]<br>
  }
  </code> 
  <table cellpadding="0" cellspacing="1" border="0" style="width:100%" class="tableborder">
    <tr> 
      <th>Key</th>
      <th>Value</th>
      <th>Description</th>
    </tr>
    <tr> 
      <td class="td"><strong>guid</strong></td>
      <td class="td">String</td>
      <td class="td">Unique hash id of the log entry.</td>
    </tr>
    <tr> 
      <td class="td"><strong>datetime</strong></td>
      <td class="td">datetime</td>
      <td class="td">UTC timestamp of the event. this is in the format of seconds 
        since unix epoc.</td>
    </tr>
    <tr> 
      <td class="td"><strong>user</strong></td>
      <td class="td">Array(Player Object)</td>
      <td class="td">An Array containing the <a href="#playerObject">Player Object</a> that 
      initaited this log entry</td>
    </tr>
    <tr> 
      <td class="td"><strong>channel</strong></td>
      <td class="td">String</td>
      <td class="td">The channel this was spoken to either 'PUBLIC', 'RESISTANCE', or 'ENLIGHTENED'</td>
    </tr>
    <tr> 
      <td class="td"><strong>text</strong></td>
      <td class="td">String</td>
      <td class="td">What was said by the user in this log entry.</td>
    </tr>
    <tr> 
      <td class="td"><strong>region</strong></td>
      <td class="td">Array(Region Object)</td>
      <td class="td">A <a href="#regionObject">Region Object</a> associated with this log entry</td>
    </tr>
  </table>

<a name="regionObject"></a>
  <h2>#8 Region Object</h2>

  <code>
  {<br>
  &nbsp;&nbsp;&quot;guid&quot;:1,<br>
  &nbsp;&nbsp;&quot;name&quot;:&quot;calgary&quot;<br>
  }
  </code> 
  <table cellpadding="0" cellspacing="1" border="0" style="width:100%" class="tableborder">
    <tr> 
      <th>Key</th>
      <th>Value</th>
      <th>Description</th>
    </tr>
    <tr> 
      <td class="td"><strong>guid</strong></td>
      <td class="td">String</td>
      <td class="td">Unique id of the log entry.</td>
    </tr>
    <tr> 
      <td class="td"><strong>name</strong></td>
      <td class="td">String</td>
      <td class="td">A human readable name of the region that is captured</td>
    </tr>
  </table>


<a name="resources"></a>
  <h2>External Resources</h2>

<p>Here you find a list of resources that could be useful using this API:</p>

  <ul>
    <li><a href="http://www.json.org/index.html">www.json.org:</a> A lot of implementations 
      of JSON in variable Languages.</li>
  </ul>


</div>
<!-- END CONTENT -->


<div id="footer">
<p>
<a href="#top">Top of Page</a>&nbsp;&nbsp;&nbsp;&middot;&nbsp;&nbsp;
</p>

<p>Copyright &#169; 2013 &nbsp;&middot;&nbsp; <a href="http://writhem.com/">WritheM Web Solutions.</a></p>
</div>
<?php
include '../config/config.php';
echo $cfg['analytics'];
?>
</body>
</html>
