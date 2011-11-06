<!doctype html>
<!--[if lt IE 7]> <html class="no-js ie6 oldie" lang="en"> <![endif]-->
<!--[if IE 7]>    <html class="no-js ie7 oldie" lang="en"> <![endif]-->
<!--[if IE 8]>    <html class="no-js ie8 oldie" lang="en"> <![endif]-->
<!--[if gt IE 8]><!--> <html class="no-js" lang="en"> <!--<![endif]-->
<head>
    <meta charset="utf-8">
    <meta http-equiv="X-UA-Compatible" content="IE=edge,chrome=1">

    <title>GoldSource Server Info</title>
    
    <meta name="description" content="">
    <meta name="author" content="Gentle Software Solutions | http://www.gentle.ro">
    <meta name="viewport" content="width=device-width,initial-scale=1">

    <link rel="stylesheet" href="assets/css/style.css" media="screen, projection">
    <!--[if lte IE 8]>
    <link rel="stylesheet" href="assets/css/ie.css" media="screen, projection">
    <![endif]-->
  
    <script src="assets/js/modernizr-2.0.6.min.js"></script>
</head>

<body>
  
    <header>
        <h1>GoldSource Server Info</h1>
    </header>

    <div id="container" role="main">
        <div class="col-3 form last">
            <form id="global" method="post" action="javascript:void(0);" autocomplete="off">
                <fieldset>	
                    <label for="address">IP</label>
                    <input type="text" name="address" id="address" placeholder="Server IP / DNS" title="Enter server IP" class="required">
                    <span class="address result hide clear"></span>
                    
                    <label for="port">Port</label>
                    <input type="text" name="port" id="port" placeholder="Server port" title="Enter server port" value="27015" class="required">
                    <span class="port result hide clear"></span>
                    
                    <input type="submit" name="check" class="button" id="check" value="Check" />                
                </fieldset>
            </form>
        </div>
        
        <div class="response processing hide"></div>
        
        <div class="details hide">
            <div class="col-3 last"><h2 id="hostname">server hostname</h2></div>
            <div class="col-1">
                <table class="server_data">
                    <tr><td class="strong">Address:</td> <td id="address" class="loading last">-</td></tr>
                    <tr><td class="strong">Map:</td> <td id="map" class="loading last">-</td></tr>
                    <tr><td class="strong">Mod:</td> <td id="desc" class="loading last">-</td></tr>
                    <tr><td class="strong">Protocol:</td> <td id="protocol" class="loading last">-</td></tr>
                    <tr><td class="strong">Dedicated:</td> <td id="type" class="loading last">-</td></tr>
                    <tr><td class="strong">Password:</td> <td id="password" class="loading last">-</td></tr>
                    <tr><td class="strong">OS:</td> <td id="os" class="loading last">-</td></tr>
                    <tr><td class="strong">Ping:</td> <td id="ping" class="loading last">-</td></tr>
                    <tr><td class="strong">Players:</td> <td id="players" class="loading last">-</td></tr>
                </table>
            </div>
            
            <div class="col-2 geomap last"></div>
            
            <div class="divider"></div>
            
            <div class="col-3 last">
                <table class="players_data">
                    <thead>
                        <tr>
                            <td class="strong center">Nickname</td> 
                            <td class="strong center" style="width: 40px;">Online</td>
                            <td class="strong center last" style="width: 30px;">Score</td>
                        </tr>
                    </thead>
                    <tbody>
                        <tr><td colspan="3" class="loading center last">-</td></tr>
                    </tbody>
                </table>
            </div>
        </div> <!-- end .details -->
    </div>
    
    <div class="push"></div>
    
    <footer>
        <div class="left"></div>
        <div class="right"></div>
        <p>
            IP Geolocation by <a href="http://freegeoip.net">freegeoip.net</a><br />
            Powered by <a href="http://www.gentle.ro/proiecte/goldsource-server-info/" title="by Gentle Software Solutions">GoldSource Server Info</a><br />
        </p>
    </footer>
    
    <script src="http://ajax.googleapis.com/ajax/libs/jquery/1.6.2/jquery.min.js"></script>
    <script>window.jQuery || document.write('<script src="assets/js/jquery/jquery-1.6.2.min.js"><\/script>')</script>
    
    <script defer src="assets/js/main.js"></script>
  
    <!--[if lt IE 7 ]>
        <script src="http://ajax.googleapis.com/ajax/libs/chrome-frame/1.0.3/CFInstall.min.js"></script>
        <script>window.attachEvent('onload',function(){CFInstall.check({mode:'overlay'})})</script>
    <![endif]-->
</body>
</html>
