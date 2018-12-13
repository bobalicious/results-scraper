<?php

define( 'RACES_BASE_URL'   , 'http://www.thepowerof10.info/results/resultslookup.aspx' );
define( 'ATHLETES_BASE_URL', 'http://www.thepowerof10.info/athletes/athleteslookup.aspx?club=Queens+Park+Harriers' );


define( 'RESULTS_BASE_URL'             , 'https://www.thepowerof10.info/results/results.aspx' );	   // meetingid
define( 'RESULTS_BASE_URL_RUN_BRITAIN' , 'https://www.runbritainrankings.com/results/results.aspx' );  // meetingid

/**
 *   Read the data for each athlete from the power of 10. Returns an array of athlete objects.
 */
function readAthletes( $debug ) {

	$text = getRemoteData( ATHLETES_BASE_URL );

	$athletes = array();

	$rowExpr  = '/s*(<td.*?>.*?<\/td>)+\s*/';     // assumes that a single row appears on a single line
	preg_match_all( $rowExpr, $text, $matches );

	$rows = $matches[0];

	foreach ( $rows as $row ) {

		$cellExpr= '/<td.*?>(.*?)<\/td>/';
		preg_match_all( $cellExpr, $row, $matches );

		$cells = $matches[1];

		if( count( $cells ) != 9) {
			if ( $debug > 1 ) {
				echo( "Skipping row, not an athlete record. Should contain 9 cells. Actually contains " . count( $cells ) . "<br/>" );
			}
			continue;
		}

		$profileCell = $cells[8];

		$linkExpr= '/<a href="(.*?)">.*/';
		preg_match_all( $linkExpr, $profileCell, $matches );

		if ( count( $matches[1] ) < 1 ) {
			if ( $debug > 1 ) {
				echo( "Skipping row, not an athlete record. Should contain href at cell 8.<br/>" );
			}
			continue;
		}

		$firstName     = $cells[0];
		$secondName    = $cells[1];
		$trackCategory = $cells[2];
		$roadCategory  = $cells[3];
		$xcCategory    = $cells[4];
		$gender        = $cells[5];
		
		if ( empty( $dob ) || $dob=='&nbsp;' ) {
			$dob = '';
		}

		$link    = $matches[1][0];
		$name    = trim($firstName.' '.$secondName);
		$pattern = strtoupper( substr( $name, 0, 1 ) ) . '%' . strtoupper( $secondName );
		
		$idExpr= '/.*?athleteid=(\d+).*?/';
		preg_match($idExpr,$link,$matches);
		$id = $matches[1];
		
		$athlete = array(
			'name'          => $name,
			'link'          => $link,
			'gender'        => $gender,
			'trackCategory' => $trackCategory,
			'roadCategory'  => $roadCategory,
			'xcCategory'    => $xcCategory,
			'pattern'       => $pattern );

		array_push( $athletes, $athlete );
	}

	return $athletes;
}

function readRaces( $debug, $startDate, $endDate, $eventSearch, $venueSearch ) {

	$searchUrl = RACES_BASE_URL;
	$searchParameters = array();

	if ( $startDate ) {
		$searchParameters[] = 'datefrom=' . urlencode( $startDate );
	}

	if ( $endDate ) {
		$searchParameters[] = 'dateto=' . urlencode( $endDate );
	}

	if ( $eventSearch ) {
		$searchParameters[] = 'title=*' . urlencode( $eventSearch ) . '*';
	}

	if ( $venueSearch ) {
		$searchParameters[] = 'venue=*' . urlencode( $venueSearch ) . '*';
	}

	if ( $searchParameters ) {
		$searchUrl .= '?' . join( $searchParameters, '&' );
	}

	$text  = getRemoteData( $searchUrl );

	$races = array();

	$rowExpr = '~<tr.*>([\S\s]*)</tr>~mU';
	preg_match_all( $rowExpr, $text, $matches );
	$rows = $matches[0];

	if ( $debug > 1 ) {
		echo( 'Found ' . count($rows) . ' race records<br/>' );
	}

	foreach ( $rows as $row ) {

		// Expected cells
		// 0 - Date - E.g. Sun 25 Nov 2018
		// 1 - Meeting Name, including link - e.g. <a href="https://xipgroc.cat/ca/curses/JeanBouin2018/10k/resultats" target="_blank">
		// 2 - Venue name, including link - e.g. <a href="/fixtures/meeting.aspx?meetingid=267513">Barcelona, ESP</a>
		// 3 - Meeting type - e.g. Road
		// 4 - Results status, including link - e.g. <a href="/results/results.aspx?meetingid=267513">Complete</a>
		// 5 - Submit results link, with image - e.g. <a href="/submit/submitmeeting.aspx?meetingid=267513" title="submit results"><img src="/images/pot/email.gif" border="0" /></a>

		$cellExpr= '~<td.*?>([\S\s]*?)<\/td>~m';
		preg_match_all( $cellExpr, $row, $matches );

		$cells = $matches[1];

		if( count( $cells ) != 6 ) {
			if ( $debug > 1 ) {
				echo( "Skipping row, not a race record. Should contain 6 cells. Actually contains " . count( $cells ) . "<br/>" );
			}
			continue;
		}

		if ( $cells[0] == 'Date' ) {
			if ( $debug > 1 ) {
				echo( "Skipping row, not a race record. The first cell contains the header for the column<br/>" );
			}
			continue;

		}

		$rawVenueName = $cells[2];

		$thisRace = array();
		$thisRace['RawDate']       = $cells[0];
		$thisRace['MeetingName']   = getTextFromLink( trim( $cells[1] ) );
		$thisRace['VenueName']     = getTextFromLink( $rawVenueName );
		$thisRace['MeetingId']     = getMeetingIdFromLink( $rawVenueName );
		$thisRace['MeetingType']   = $cells[3];
		$thisRace['ResultsStatus'] = getTextFromLink( $cells[4] );
		$thisRace['RaceFullName']  = $thisRace['MeetingName'] . ' (' . $thisRace['VenueName'] . ') - ' . $thisRace['MeetingType'];  

		$races[] = $thisRace;

	}

	return $races;

}

// TODO: needs to load multiple pages - or does it?
function readResults( $debug, $meetingId ) {

	$results    = array();
	$pageNumber = 1;
	$getResults = true;

	while( $getResults ) {

		$newResults = readResultsPage( $debug, $meetingId, $pageNumber );

		if ( count( $newResults ) > 0 ) {
			// TODO: merge the results
			$results = $newResults;
			$pageNumber++;
			$getResults = false;
		} else {
			$getResults = false;
		}

		if ( $pageNumber > 5 ) {
			$getResults = false;
		}

	}

	return $results;
}

function readResultsPage( $debug, $meetingId, $pageNumber ) { 

	$formats = array( new P10Format1()
					, new P10Format2()
					, new P10Format3()
					, new P10Format4()
					, new P10Format5()
					, new P10Format6()
					, new P10Format7()
					, new RunBritainFormat()
					, new RunBritainFormat2()
					);

	$results = array();
	$url     = RESULTS_BASE_URL . '?meetingid=' . $meetingId . '&pagenum=' . $pageNumber;

	if ( $debug > 1 ) {
		echo( "Loading URL: " . $url . "<br/>" );
	}

	$text    = getRemoteData( $url );

	$rowExpr = '~<tr.*>([\S\s]*)</tr>~mU';
	preg_match_all( $rowExpr, $text, $matches );
	$rows = $matches[0];

	if ( $debug > 1 ) {
		echo( 'Found ' . count($rows) . ' result records<br/>' );
	}

	$format = null;
	foreach ( $rows as $row ) {

		// TODO: needs to deal with different events - does it?  It might already

		$cellExpr= '~<td.*?>([\S\s]*?)<\/td>~m';
		preg_match_all( $cellExpr, $row, $matches );

		$cells = $matches[1];

		if ( count( $cells ) == 1 && strpos( $cells[0], '<b>' ) !== false ) {

			$raceName = getRaceNameFromText( $cells[0] );

			if ( $debug > 1 ) {
				echo( "Found a race name cell.<br/>" );
			}

			if ( isset( $thisRace['Name'] ) && count( $thisRace['Results'] ) > 0 ) {
				$results[] = $thisRace;
			}

			$thisRace['Name']    = $raceName;
			$thisRace['Results'] = array();
			continue;
		}

		if ( $cells[0] === '<b>Pos</b>' || ( isset( $cells[1] ) && $cells[1] === '<b>Pos</b>' ) ) {

			// Is a header row, so a new formatter may be required
			$format = null;

			foreach ( $formats as $thisFormat ) {
				if ( $thisFormat->headerRowMatchesFormat( $cells ) ) {
					$format = $thisFormat;
				}
			}

			if ( $debug > 1 ) {
				if ( $format ) {
					echo( "Used header row to define format as:".get_class( $format )."<br/>" );
				} else {
					echo( "Could not find a format that matches the row<br/>" );
				}
			}

			continue;
		}

		if ( $format && $format->isResultsRow( $cells ) ) {
			$thisRace['Results'][] = $format->getResultsFromRow( $cells );
		}

	}

	if ( isset( $thisRace['Name'] ) && count( $thisRace['Results'] ) > 0 ) {
		$results[] = $thisRace;
	}
	return $results;
}


function getCell( $cellText ) {

	$cellText = trim( $cellText );

	if ( $cellText == '&nbsp;' ) {
		return '';
	}
	return $cellText;

}

// will also return if the text is outside the link (happens in some meeting names)
function getTextFromLink( $linkText ) {

    $regEx = '~^<a.*>(.*)</a>~U';

    preg_match( $regEx, $linkText, $matches );

	if ( isset( $matches[1] ) ) {
	    return $matches[1];
	}
	
	$linkStart = strpos( $linkText, '<a href' );

	if ( $linkStart ) {
		return trim( substr( $linkText, 0, $linkStart ) );
	}

	return trim( $linkText );
}

function getMeetingIdFromLink( $linkText ) {

	$regEx = '~meetingid=([^"]*)~';

    preg_match( $regEx, $linkText, $matches );

	if ( isset( $matches[1] ) ) {
	    return $matches[1];
	}

    return '';
}

function getRaceNameFromText( $raceNameText ) {

	$regEx = '~<b>(.*)</b>~';

    preg_match( $regEx, $raceNameText, $matches );

	if ( isset( $matches[1] ) ) {
	    return $matches[1];
	}

    return $raceNameText;
}

function textContainsTime( $sText ) {
	$regEx = '~[0-9][:.][0-9]~';
	return preg_match( $regEx, $sText );
}

class P10Format1 {

	function headerRowMatchesFormat( $cells ) {
		if ( count( $cells ) !== 11 ) {
			return false;
		}
		
		return ( $cells[4] == '<b>Name</b>' );
	}

	function isResultsRow( $cells ) {
		return ( count( $cells ) == 11 );
	}

	function getResultsFromRow( $cells ) {
		// https://www.thepowerof10.info/results/results.aspx?meetingid=253252
		// Expected cells
		// 0 - Position
		// 1 - MW ?
		// 2 - AC ?
		// 3 - Perf (time)
		// 4 - Name - sometimes has a link
		// 5 - AG (group)
		// 6 - Gender
		// 7 - Year
		// 8 - Coach
		// 9 - Club
		$thisResult['Position'] = getCell( $cells[0] );
		$thisResult['Mw']       = getCell( $cells[1] );
		$thisResult['Ac']       = getCell( $cells[2] );
		$thisResult['Time']     = getCell( $cells[3] );
		$thisResult['Name']     = getTextFromLink( $cells[4] );
		$thisResult['Group']    = getCell( $cells[5] );
		$thisResult['Club']     = getCell( $cells[9] );
		return $thisResult;
	}
}

class P10Format2 {

	function isResultsRow( $cells ) {
		return ( count( $cells ) == 11 );
	}

	function headerRowMatchesFormat( $cells ) {
		if ( count( $cells ) !== 11 ) {
			return false;
		}
		
		return ( $cells[2] == '<b>Name</b>' );
	}

	function getResultsFromRow( $cells ) {
		// E.g. https://www.thepowerof10.info/results/results.aspx?meetingid=268712
		// 0 - Position
		// 1 - Perf (time)
		// 2 - Name - sometimes has a link
		// 3 - Unknown (only seen blank)
		// 4 - AG (group)
		// 5 - Gender
		// 6 - Coach
		// 7 - Club
		// 8 - SB
		// 9 - PB
		$thisResult['Position'] = getCell( $cells[0] );
		$thisResult['Mw']       = '';
		$thisResult['Ac']       = '';
		$thisResult['Time']     = getCell( $cells[1] );
		$thisResult['Name']     = getTextFromLink( $cells[2] );
		$thisResult['Group']    = getCell( $cells[4] );
		$thisResult['Club']     = getCell( $cells[7] );
		return $thisResult;
	}

}

class P10Format3 {

	function isResultsRow( $cells ) {
		return ( count( $cells ) == 10 );
	}

	function headerRowMatchesFormat( $cells ) {

		if ( count( $cells ) !== 10 ) {
			return false;
		}
		
		return ( $cells[3] == '<b>Name</b>' );
	}

	function getResultsFromRow( $cells ) {
		// E.g. https://www.thepowerof10.info/results/results.aspx?meetingid=252950
		// 0 - Position
		// 1 - AC
		// 2 - Perf (time)
		// 3 - Name - sometimes has a link
		// 4 - AG (group)
		// 5 - Gender
		// 6 - Year
		// 7 - Coach
		// 8 - Club
		$thisResult['Position'] = getCell( $cells[0] );
		$thisResult['Mw']       = '';
		$thisResult['Ac']       = getCell( $cells[1] );
		$thisResult['Time']     = getCell( $cells[2] );
		$thisResult['Name']     = getTextFromLink( $cells[3] );
		$thisResult['Group']    = getCell( $cells[4] );
		$thisResult['Club']     = getCell( $cells[8] );
		return $thisResult;
	}
}

class P10Format4 {

	function isResultsRow( $cells ) {
		return ( count( $cells ) == 12 );
	}

	function headerRowMatchesFormat( $cells ) {
		if ( count( $cells ) !== 12 ) {
			return false;
		}
		
		return ( $cells[3] == '<b>Name</b>' );
	}

	function getResultsFromRow( $cells ) {
		// E.g. https://www.thepowerof10.info/results/results.aspx?meetingid=268703
		// 0 - Position
		// 1 - Perf (time)
		// 2 - Unknown (only seen A)
		// 3 - Name - sometimes has a link
		// 4 - Unknown (only seen blank)
		// 5 - AG (group)
		// 6 - Gender
		// 7 - Coach
		// 8 - Club
		// 9 - SB
		// 10 - PB
		$thisResult['Position'] = getCell( $cells[0] );
		$thisResult['Mw']       = '';
		$thisResult['Ac']       = '';
		$thisResult['Time']     = getCell( $cells[1] );
		$thisResult['Name']     = getTextFromLink( $cells[3] );
		$thisResult['Group']    = getCell( $cells[5] );
		$thisResult['Club']     = getCell( $cells[8] );
		return $thisResult;
	}
}

class P10Format5 {

	function isResultsRow( $cells ) {
		return ( count( $cells ) == 9 );
	}

	function headerRowMatchesFormat( $cells ) {
		if ( count( $cells ) !== 9 ) {
			return false;
		}
		
		return ( $cells[3] == '<b>Name</b>' );
	}

	function getResultsFromRow( $cells ) {
		// E.g. https://www.thepowerof10.info/results/results.aspx?meetingid=269693
		// 0 - Position
		// 1 - MW
		// 2 - Perf (time)
		// 3 - Name - sometimes has a link
		// 4 - AG (group)
		// 5 - Gender
		// 6 - Coach
		// 7 - Club
		$thisResult['Position'] = getCell( $cells[0] );
		$thisResult['Mw']       = getCell( $cells[1] );
		$thisResult['Ac']       = '';
		$thisResult['Time']     = getCell( $cells[2] );
		$thisResult['Name']     = getTextFromLink( $cells[3] );
		$thisResult['Group']    = getCell( $cells[4] );
		$thisResult['Club']     = getCell( $cells[7] );
		return $thisResult;
	}
}

class P10Format6 {

	function isResultsRow( $cells ) {
		return ( count( $cells ) == 12 );
	}

	function headerRowMatchesFormat( $cells ) {
		if ( count( $cells ) !== 12 ) {
			return false;
		}
		return ( $cells[2] == '<b>Name</b>' );
	}

	function getResultsFromRow( $cells ) {
		// E.g. https://www.thepowerof10.info/results/results.aspx?meetingid=265226
		// 0 - Position
		// 1 - Perf (time)
		// 2 - Name - sometimes has a link
		// 3 - Unknown (only seen blank)
		// 4 - AG (group)
		// 5 - Gender
		// 6 - Year
		// 7 - Coach
		// 8 - Club
		// 9 - SB
		// 10 - PB
		$thisResult['Position'] = getCell( $cells[0] );
		$thisResult['Mw']       = '';
		$thisResult['Ac']       = '';
		$thisResult['Time']     = getCell( $cells[1] );
		$thisResult['Name']     = getTextFromLink( $cells[2] );
		$thisResult['Group']    = getCell( $cells[4] );
		$thisResult['Club']     = getCell( $cells[8] );
		return $thisResult;
	}
}

class P10Format7 {

	function isResultsRow( $cells ) {
		return ( count( $cells ) == 10 );
	}

	function headerRowMatchesFormat( $cells ) {
		if ( count( $cells ) !== 10 ) {
			return false;
		}
		return ( $cells[4] == '<b>Name</b>' );
	}

	function getResultsFromRow( $cells ) {
		// E.g. https://www.thepowerof10.info/results/results.aspx?meetingid=253007
		// 0 - Position
		// 1 - MW
		// 2 - AC
		// 3 - Perf
		// 4 - Name - sometimes has a link
		// 5 - AG (group)
		// 6 - Gender
		// 7 - Coach
		// 8 - Club
		$thisResult['Position'] = getCell( $cells[0] );
		$thisResult['Mw']       = getCell( $cells[1] );
		$thisResult['Ac']       = getCell( $cells[2] );
		$thisResult['Time']     = getCell( $cells[3] );
		$thisResult['Name']     = getTextFromLink( $cells[4] );
		$thisResult['Group']    = getCell( $cells[5] );
		$thisResult['Club']     = getCell( $cells[8] );
		return $thisResult;
	}
}

class RunBritainFormat {

	function isResultsRow( $cells ) {
		return ( count( $cells ) == 16 );
	}

	function headerRowMatchesFormat( $cells ) {
		if ( count( $cells ) !== 16 ) {			
			return false;
		}
		
		return ( $cells[6] == '<b>Name</b>' );
	}

	function getResultsFromRow( $cells ) {
		// E.g. https://www.runbritainrankings.com/results/results.aspx?meetingid=232067
		// 0 - Checkbox
		// 1 - Position
		// 2 - Time
		// 3 - Blank
		// 4 - Blank
		// 5 - Blank
		// 6 - Name
		// 7 - PB / SB
		// 8 - Group
		// 9 - Gender
		// 10 - Club
		$thisResult['Position'] = getCell( $cells[1] );
		$thisResult['Mw']       = '';
		$thisResult['Ac']       = '';
		$thisResult['Time']     = getCell( $cells[2] );
		$thisResult['Name']     = getTextFromLink( $cells[6] );
		$thisResult['Group']    = getCell( $cells[8] );
		$thisResult['Club']     = getCell( $cells[10] );
		return $thisResult;
	}
}

class RunBritainFormat2 {

	function isResultsRow( $cells ) {
		return ( count( $cells ) == 17 );
	}

	function headerRowMatchesFormat( $cells ) {
		if ( count( $cells ) !== 17 ) {			
			return false;
		}
		
		return ( $cells[7] == '<b>Name</b>' );
	}

	function getResultsFromRow( $cells ) {
		// E.g. ???
		// 0 - Checkbox
		// 1 - Position
		// 2 - Time (Gun)
		// 3 - Time (Chip)
		// 4 - Blank
		// 5 - Blank
		// 6 - Blank
		// 7 - Name
		// 8 - PB / SB
		// 9 - Group
		// 10 - Gender
		// 11 - Club
		$thisResult['Position'] = getCell( $cells[1] );
		$thisResult['Mw']       = '';
		$thisResult['Ac']       = '';
		$thisResult['Time']     = getCell( $cells[3] );
		$thisResult['Name']     = getTextFromLink( $cells[7] );
		$thisResult['Group']    = getCell( $cells[9] );
		$thisResult['Club']     = getCell( $cells[11] );
		return $thisResult;
	}	
}

?>
