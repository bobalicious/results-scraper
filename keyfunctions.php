<?php

date_default_timezone_set('Europe/London');

function htmlescape( $string) {
	return htmlentities($string, ENT_QUOTES);
}


function safeDie( $message ) {
	die( htmlescape( $message ));
}

/**
 *  Writes an error message with a back button and end the page.
 */
function echoError( $message ) {
	echo ("<h1>Error</h1>");
	echo( $message );
	echo ("<p><button onclick='window.history.back();'>Go Back</button></p>");
	echo ("<noscript><p style='color:red'>OOPS! It seems you don't have Javascript enabled on your browser. We strongly recommend you enable it for our website to work correctly.
	    You will have to use the back button on your Browser instead of our Go Back button</p>");
	echo ("</body>");
	echo ("</html>");
}

/**
 *  Throw an exception
 */
function throwException( $msg ) {
	throw new Exception($msg);
}



/* Reformat an SQL date to a british date */
function sqlToBritish($dateString, $short=true) {
	$list = explode('-', $dateString);
	if (count($list)<3) {
		return false;
	}
    list($year, $month, $day) = $list;
    $day = intval($day);
    $month = intval($month);
    $year = intval($year);
    if (!($day>=1 && $day<=31 && $month>=1 && $month<=12 && $year>=1 && $year<=9999)) {
        return false;
    }
    if ($short) {
        return sprintf( '%02d/%02d/%04d', $day,$month,$year);
    } else {
    	$months = array("January", "February", "March", "April", "May", "June", "July", "August", "September", "October", "November", "December");
    	return sprintf( '%d %s %04d', $day, $months[$month-1], $year );
    }
}

function startsWith($haystack, $needle) {
    // search backwards starting from haystack length characters from the end
    $haystack=strtoupper($haystack);
    $needle=strtoupper($needle);
    return $needle === "" || strrpos($haystack, $needle, -strlen($haystack)) !== FALSE;
}

function endsWith($haystack, $needle) {
    // search forward starting from end minus needle length characters
    return $needle === "" || (($temp = strlen($haystack) - strlen($needle)) >= 0 && strpos($haystack, $needle, $temp) !== FALSE);
}

/* Reformat a british date to an SQL date */
function britishToSql($dateString) {
	$l= preg_split('@[./ ]@', $dateString);
	if (count($l)<3) {
		return '0000-00-00';
	}
    list($day, $month, $year) = $l; 
    if (startsWith($month,"Jan")) {
    	$month = 1;
    }
    if (startsWith($month,"Feb")) {
    	$month = 2;
    }
    if (startsWith($month,"Mar")) {
    	$month = 3;
    }
    if (startsWith($month,"Apr")) {
    	$month = 4;
    }
    if (startsWith($month,"May")) {
    	$month = 5;
    }
    if (startsWith($month,"Jun")) {
    	$month = 6;
    }
    if (startsWith($month,"Jul")) {
    	$month = 7;
    }
    if (startsWith($month,"Aug")) {
    	$month = 8;
    }
    if (startsWith($month,"Sep")) {
    	$month = 9;
    }
    if (startsWith($month,"Oct")) {
    	$month = 10;
    }
    if (startsWith($month,"Nov")) {
    	$month = 11;
    }
    if (startsWith($month,"Dec")) {
    	$month = 12;
    }
    $day = intval($day);
    $month = intval($month);
    $year = intval($year);    
    if ($day>=1 && $day<=31 && $month>=1 && $month<=12 && $year>=1 && $year<=9999) {
    	$currentYear = date("Y");
    	$currentCentury = $currentYear - ($currentYear % 100);
		if ($year<100) {
			$year += $currentCentury;
			if ($year>$currentYear+10) {
				$year -= 100;
			}
		}    	
		return sprintf( '%04d-%02d-%02d', $year,$month,$day);         
    }
	return '0000-00-00';        	    
}



/**
 *  Read data from a URL
 */
function getRemoteData($url, $post_paramtrs = false) {
    $c = curl_init();
    curl_setopt($c, CURLOPT_URL, $url);
    curl_setopt($c, CURLOPT_RETURNTRANSFER, 1);
    if ($post_paramtrs) {
        curl_setopt($c, CURLOPT_POST, TRUE);
        curl_setopt($c, CURLOPT_POSTFIELDS, "var1=bla&" . $post_paramtrs);
    } curl_setopt($c, CURLOPT_SSL_VERIFYHOST, false);
    curl_setopt($c, CURLOPT_SSL_VERIFYPEER, false);
    curl_setopt($c, CURLOPT_USERAGENT, "Mozilla/5.0 (Windows NT 6.1; rv:33.0) Gecko/20100101 Firefox/33.0");
    curl_setopt($c, CURLOPT_COOKIE, 'CookieName1=Value;');
    curl_setopt($c, CURLOPT_MAXREDIRS, 10);
    $follow_allowed = ( ini_get('open_basedir') || ini_get('safe_mode')) ? false : true;
    if ($follow_allowed) {
        curl_setopt($c, CURLOPT_FOLLOWLOCATION, 1);
    }curl_setopt($c, CURLOPT_CONNECTTIMEOUT, 9);
    curl_setopt($c, CURLOPT_REFERER, $url);
    curl_setopt($c, CURLOPT_TIMEOUT, 60);
    curl_setopt($c, CURLOPT_AUTOREFERER, true);
    curl_setopt($c, CURLOPT_ENCODING, 'gzip,deflate');
    $data = curl_exec($c);
    $status = curl_getinfo($c);
    curl_close($c);
    preg_match('/(http(|s)):\/\/(.*?)\/(.*\/|)/si', $status['url'], $link);
    $data = preg_replace('/(src|href|action)=(\'|\")((?!(http|https|javascript:|\/\/|\/)).*?)(\'|\")/si', '$1=$2' . $link[0] . '$3$4$5', $data);
    $data = preg_replace('/(src|href|action)=(\'|\")((?!(http|https|javascript:|\/\/)).*?)(\'|\")/si', '$1=$2' . $link[1] . '://' . $link[3] . '$3$4$5', $data);
    if ($status['http_code'] == 200) {
        return $data;
    } elseif ($status['http_code'] == 301 || $status['http_code'] == 302) {
        if (!$follow_allowed) {
            if (empty($redirURL)) {
                if (!empty($status['redirect_url'])) {
                    $redirURL = $status['redirect_url'];
                }
            } if (empty($redirURL)) {
                preg_match('/(Location:|URI:)(.*?)(\r|\n)/si', $data, $m);
                if (!empty($m[2])) {
                    $redirURL = $m[2];
                }
            } if (empty($redirURL)) {
                preg_match('/href\=\"(.*?)\"(.*?)here\<\/a\>/si', $data, $m);
                if (!empty($m[1])) {
                    $redirURL = $m[1];
                }
            } if (!empty($redirURL)) {
                $t = debug_backtrace();
                return call_user_func($t[0]["function"], trim($redirURL), $post_paramtrs);
            }
        }
    } return "ERRORCODE22 with $url!!<br/>Last status codes<b/>:" . json_encode($status) . "<br/><br/>Last data got<br/>:$data";
}


/**
 *   Power of 10 functions
 */
 


/**
 *   Convert a time from the power of ten into seconds
 */
function powerOf10ToSeconds( $powerOf10Time ) {
	$parts = preg_split('/:/',$powerOf10Time );
	$secs = 0;
	for ($i=0; $i<count($parts); $i++) {
		$secs = 60*$secs+floatval($parts[$i]);
	}
	return $secs;
}



/**
 *  Compare two result objects
 */
function resultCmp($a, $b) {
    if ($a['date'] == $b['date']) {
        return 0;
    }
    return ($a['date'] < $b['date']) ? -1 : 1;
}
 
/**
 *  Reads the results for a given athlete from the power of 10. Returns an array of result objetcts.
 */
function readAthleteResults($debug, $powerOf10Id) {

	//	$disciplines = getDisciplines();
	$results = array();

	if ($debug) {
		echo ('<p>Process data for '.htmlescape($powerOf10Id).'</p>');
	}
    flush();
	$link = "http://www.thepowerof10.info/athletes/profile.aspx?athleteid=".intval($powerOf10Id);
	$text = getRemoteData($link);
	//echo( htmlentities($text));
	
	$rowExpr = '@\s*(<td.*?>.*?</td>)+\s*@';
	preg_match_all($rowExpr,$text,$matches);
	$rows = $matches[0];		
	foreach ($rows as $row) {
		$cellExpr= '@<td.*?>(<a.*?>)?(.*?)(</a>)?</td>@';
		preg_match_all($cellExpr,$row,$matches);
		$cells = $matches[2];
		if(count($cells)!=12) {
			if ($debug>1) {
				echo("Skipping row, not a race result, should contain 12 cells<br/>");
			}
			continue;
		}		
		$distance = $cells[0];
		if (startsWith($distance,'<b')) {
			if ($debug>1) {
				echo("Skipping row - table heading<br/>");
			}
			continue;
		}
		$result = array(
						'discipline'  => trim($cells[0]),
						'performance' => trim($cells[1]),
						'chip'        => trim($cells[4]),
						'pos'         => intval(trim($cells[5])),
						'catPos'      => intval(trim($cells[8])),
						'venue'       => trim($cells[9]),
						'event'       => trim($cells[10]),
						'date'        => $cells[11],
						'pb'          => '0'
						);

		if (empty($result['pos'])) $result['pos']=null;
		if (empty($result['catPos'])) $result['catPos']=null;
		$perf = $result['performance'];
		if (!empty($result['chip'])) {
			$perf = $result['chip'];
		}
		$result['seconds']=powerOf10ToSeconds($perf);
		
		array_push($results,$result);		
	}	
	
	usort( $results, 'resultCmp' ); 
	
	$pbs = array();
	for ($i=0; $i<count($results); $i++) {
		$result = $results[$i];
		$performance = $result['seconds'];
		if (!($performance>0)) {
			continue;
		}
		$discipline = $result['discipline'];
		/*
		if (key_exists($discipline,$disciplines)) {
			if (!key_exists($discipline,$pbs )
					|| $performance<$pbs[$discipline]) {
				$pbs[$discipline]=$performance;
				$results[$i]['pb'] = true;
			}
		}
		*/
	}
	return $results;
}

/**
 *   Download the results for a given athlete from the power of 10
 */
function powerOf10Download( $debug, $conn, $powerOf10Id) {
	$results = readAthleteResults($debug, $powerOf10Id);
	if (count($results)==0) {
		return;
	}

	$fromDate=date("Y")."-01-01";
	
	// Read the known results
	$knownResults = array();
	$stmt = $conn->prepare('SELECT CONCAT(athleteId, event, DATE_FORMAT( date, "%Y-%m-%d")) AS "key" FROM powerof10
						  WHERE date>=? AND athleteId=?') or safeDie('Failed to prepare statement '.mysqli_error($conn));;
	$stmt->bind_param('ss',$fromDate,$powerOf10Id) or safeDie('Failed to bind params '.mysqli_error($conn));
	$stmt->execute() or safeDie('Failed to execute statement '.mysqli_error($conn));
	$res = $stmt->get_result();
	while ($row = $res->fetch_assoc()) {
		$knownResults[ $row['key']] =  'true';
	}
	$res->close();
	$stmt->close();
	
	// process the results for the athlete
	$stmt = $conn->prepare('INSERT INTO powerof10
					(
					   athleteId,
					   discipline,
					   performance,
					   chip,
					   seconds,
					   pos,
					   catPos,
					   venue,
					   event,
					   date,
					   pb
					) VALUES
					(?,?,?,?,?,?,?,?,?,?,?)') or safeDie('Failed to prepare statement '.mysqli_error($conn));	
	
	foreach ($results as $result) {		
		$key = $powerOf10Id . $result['event'] . $result['date'];
		if ($result['date']<$fromDate || key_exists($key,$knownResults)) {
			if ($debug) {
				echo ('Skipping '.htmlentities($result['event'])." ".htmlentities($result['date'])."<br>");
			}
			continue;			
		}
		
		$stmt->bind_param( "sssssssssss", 
			$powerOf10Id,
			$result['discipline'],
			$result['performance'],
			$result['chip'],
			$result['seconds'],
			$result['pos'],
			$result['catPos'],
			$result['venue'],
			$result['event'],
			$result['date'],
			$result['pb'] ) or safeDie('Failed to bind parameters '.mysqli_error($conn));
		if ($debug) {			
			echo (htmlentities($result['event'])." ".htmlentities($result['date'])."<br>");
		}			
		$stmt->execute() or safeDie('Invalid value: '. $conn->error);
		
		//fixupPowerof10Event($conn, $result['date'], $memberId, $result['seconds'], $result['event'],$result['venue'], $result['discipline'] );		
	}
	$stmt->close();
}

/**
 *  When submitting a new result from an automated feed, if a member has the same performance but
 *  with different event information, update the event information in the database to match
 *  the official event info from the power of 10
 */
function fixupPowerof10Event( $conn, $sqlDate, $memberId, $perfSecs, $event, $venue, $discipline ) {
	$queryStmt = $conn->prepare("SELECT result.event, result.venue, result.discipline, result.date
		FROM members AS member, powerof10 AS result
		WHERE  (
		       member.powerOf10=result.athleteId		       
		       OR result.memberId = member.id
		       )
		       AND result.date=? AND member.id=? AND result.seconds=?")
		or safeDie('Failed to prepare statement '.$conn->error);
	$updateStatement = $conn->prepare("UPDATE powerof10 SET
								event=?, venue=?, discipline=?
								WHERE
								event=? AND venue=? AND discipline=? AND date=?") or safeDie('Failed to prepare statement '.$conn->error);
	$queryStmt->bind_param("sss", $sqlDate, $memberId, $perfSecs)
		or safeDie('Failed to bind parameters');
	$queryStmt->execute() or safeDie('Failed to execute SQL'. $conn->error);	
	$result=$queryStmt->get_result();
	while ($row = $result->fetch_assoc()) {
		$oldEvent = $row['event'];
		$oldVenue = $row['venue'];
		$oldDiscipline = $row['discipline'];
		$updateStatement->bind_param("sssssss",$event,$venue,$discipline,$oldEvent, $oldVenue, $oldDiscipline, $sqlDate);
		$updateStatement->execute();
	}	
	$updateStatement->close();
	$queryStmt->close();
}



/** 
 *  Mark results in the database as pending sending out in an email
 */
function markResultsAsPending($conn) {
	$conn->query('UPDATE powerof10 SET postedState=1 WHERE postedState IS NULL OR postedState=0')
		or safeDie('Failed to update powerof10 database table '.$conn->error);
}

/** 
 *   Mark pending results as sent out
 */
function markResultsAsSent($conn) {
	$conn->query('UPDATE powerof10 SET postedState=2 WHERE postedState=1')
		or safeDie('Failed to update powerof10 database table'.$conn->error);	
}

?>