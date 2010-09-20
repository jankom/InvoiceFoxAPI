<?php

class Browser
{

	function redirect($location)
	//i: redirect using the header command and exit
	{
		if (strtolower(substr($location, 0, 4)) == 'http')
		{
			header("Location: $location");
			exit;
		}
		else
		{
			$host  = $_SERVER['HTTP_HOST'];
			$uri  = rtrim(dirname($_SERVER['PHP_SELF']), '/\\');
			header("Location: http://$host$uri/" . htmlspecialchars_decode($location));
			exit;
		}
	}

    function redirectDelayed($location, $delay, $text='Redirecting...', $other=array())
    //i: redirect using the header command and exit
    {
        $title = isset($other['title']) ? $other['title'] : 'Redirecting...';
        $bodyParams = isset($other['bodyParams']) ? $other['bodyParams'] : '';
        $divParams = isset($other['divParams']) ? $other['divParams'] : '';
        $autoLink = isset($other['autoLink']) ? $other['autoLink'] : false;
        $autoLinkTemplate = isset($other['autoLinkTemplate']) ? $other['autoLinkTemplate'] : 
                "<br/><br/><a href='%%'>Click here to go now</a>";
        $location2 = isset($other['location2']) ? $other['location2'] : '';
        $location3 = isset($other['location3']) ? $other['location3'] : '';
        $location4 = isset($other['location4']) ? $other['location4'] : '';
        $location5 = isset($other['location5']) ? $other['location5'] : '';
        $location6 = isset($other['location6']) ? $other['location6'] : '';
        
        if ($autoLink)
        {        
            $autoLinkTemplate = str_replace('%delay%', $delay, $autoLinkTemplate);
            $text .= str_replace('%location%', $location, $autoLinkTemplate);
            if ($location2) $text = str_replace('%location2%', $location2, $text);
            if ($location3) $text = str_replace('%location3%', $location3, $text);
            if ($location4) $text = str_replace('%location4%', $location4, $text);
            if ($location5) $text = str_replace('%location5%', $location5, $text);
            if ($location6) $text = str_replace('%location6%', $location6, $text);
        }
        
        echo "<html>
            <head>
            <title>$title</title>
            <META HTTP-EQUIV='Refresh'
                        CONTENT='$delay; URL=$location'>
            </head>
            <body $bodyParams>
                <div $divParams>
                    $text
                </div>
            </body>
            </html>
        ";
        exit();
    }
 

}

?>