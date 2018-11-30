<?php

//ini_set('display_startup_errors',1);
//ini_set('display_errors',1);
//error_reporting(-1);
//require_once('../lib/functions.php');
//require 'navbar.php';

define( 'RACES_BASE_URL'   , 'http://www.thepowerof10.info/results/resultslookup.aspx' );
define( 'ATHLETES_BASE_URL', 'http://www.thepowerof10.info/athletes/athleteslookup.aspx?club=Queens+Park+Harriers' );


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

function readRaces( $debug ) {

	$races = array();
	$text  = getRemoteData( RACES_BASE_URL );

	$rowExpr = '~<tr.*>([\S\s]*)</tr>~mU';
	preg_match_all( $rowExpr, $text, $matches );
	$rows = $matches[0];

	if ( $debug > 1 ) {
		echo( 'Found ' . count($rows) . ' race records<br/>' );
	}

	foreach ( $rows as $row ) {

		// Expected cells
		// 0 - Date - E.g. Sun 25 Nov 2018
		// 1 - Race Name, including link - e.g. <a href="https://xipgroc.cat/ca/curses/JeanBouin2018/10k/resultats" target="_blank">
		// 2 - Meeting name, including link - e.g. <a href="/fixtures/meeting.aspx?meetingid=267513">Barcelona, ESP</a>
		// 3 - Meeting type - e.g. Road
		// 4 - Results status, including link - e.g. <a href="/results/results.aspx?meetingid=267513">Complete</a>
		// 5 - Submit results link, with image - e.g. <a href="/submit/submitmeeting.aspx?meetingid=267513" title="submit results"><img src="/images/pot/email.gif" border="0" /></a>

		$cellExpr= '/<td.*?>([\S\s]*?)<\/td>/gmUx';
		preg_match_all( $cellExpr, $row, $matches );

		$cells = $matches[1];

		if( count( $cells ) != 6 ) {
			if ( $debug > 1 ) {
				echo( "Skipping row, not a race record. Should contain 6 cells. Actually contains " . count( $cells ) . "<br/>" );
			}
			continue;
		}

	}

}

/*


$conn = initDatabase();


$findMemberStmt1 = $conn->prepare('SELECT id, powerOf10 FROM members WHERE powerOf10=? AND year>=?') or safeDie('Failed to prepare statement'.mysqli_error($conn));
$findMemberStmt2 = $conn->prepare('SELECT id, name, DATE_FORMAT(dob,"%Y-%m-%d") AS "dob" , powerOf10 FROM members WHERE UPPER(name) LIKE ? AND year>=?') or safeDie('Failed to prepare statement'.mysqli_error($conn));
$updateMemberStatement = $conn->prepare('UPDATE members SET powerOf10=?, gender=? WHERE id=? AND (powerOf10 IS NULL OR powerOf10="")') or safeDie('Failed to prepare statement'.mysqli_error($conn));				

$count = 0;
$athletes = readAthletes($debug);
$year = SUBS_YEAR;
foreach ($athletes as $athlete) {
	
	echo('Looking for athlete with id '.$athlete['id']."<br>\n");
	// Make sure that we have stored the power of 10 id against the athlete's records
	$findMemberStmt1->bind_param('ss',$athlete['id'],$year) or safeDie('Failed to bind parameter '. mysqli_error($conn));
	$findMemberStmt1->execute() or safeDie('Failed to execute statement '.mysqli_error($conn));
	$res = $findMemberStmt1->get_result();
	$row = $res->fetch_assoc();
	$res->close();
	if (!$row) {
		echo('Looking for athlete with like '.htmlentities($athlete['pattern']).'<br>');
		$pattern = $athlete['pattern'];
		$ym1 = $year-1;
		$findMemberStmt2->bind_param('ss',$pattern,$ym1 )  or safeDie('Failed to bind parameter '. mysqli_error($conn));
		$findMemberStmt2->execute() or safeDie('Failed to execute statement '.mysqli_error($conn));
		$res = $findMemberStmt2->get_result();
		$foundAthlete = false;
		while ($row = $res->fetch_assoc()) {
			if ($athlete['dob']=='0000-00-00' || $row['dob']=='0000-00-00' || $athlete['dob']==$row['dob']) {
				echo ("Updating athlete ".htmlentities( $athlete['name']."<br>" ));
				$foundAthlete = true;
				$gender = $athlete['gender']=='M' ? 'M' : 'F';
				$updateMemberStatement->bind_param('sss', $athlete['id'], $gender, $row['id']);
				$updateMemberStatement->execute() or safeDie('Failed to update row '.mysqli_error($conn));
			} else {
				echo('<p>Mismatch on dob '.$athlete['dob']." vs ". $row['dob']);
			}
		}
		if (!$foundAthlete) {
			echo('<p><b>Athlete '.htmlescape( $athlete['name'] ).' not found in members table</b></p>');
			continue;
		}
		$res->close();
	}
		
	$count++;
}

$findMemberStmt1->close();
$findMemberStmt2->close();
$updateMemberStatement->close();

$y = date("Y");
$res= $conn->query("SELECT DISTINCT( powerOf10 ) FROM members WHERE year>=$y-1") or safeDie("Failed to query database");
while ($row = $res->fetch_assoc()) {
	$powerOf10 = $row['powerOf10'];
	if (!empty($powerOf10)) {
		powerOf10Download($debug, $conn, $powerOf10);
	}
}

$conn->close();
*/

?>
