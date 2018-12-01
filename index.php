<?php
	error_reporting(E_ALL);
	
	set_time_limit ( 60*60 ); // 1 hour 
	$debug = 0;

	require_once( 'powerof10.php' );
	require_once( 'keyfunctions.php' );
/*
	$athletes = readAthletes( $debug );
echo( 'about to read athlete results' );
	var_dump( readAthleteResults( 2, $athletes[0] ) );
echo( 'done' );
*/

	$return = [ 'error' => 'A valid mode was not specified' ];

	if ( isset( $_GET['mode'] ) ) {

		if ( $_GET['mode'] == 'races' ) {
			$return = readRaces( 0 );
		}

		if ( $_GET['mode'] == 'athletes' ) {
			$return = readAthletes( 0 );
		}

		if ( $_GET['mode'] == 'athleteResults' ) {
			$athleteId = $_GET['id'];
			$return = readAthleteResults( 0, $athleteId );
		}

		if ( $_GET['mode'] == 'raceResults' ) {
			$meetingId = $_GET['id'];
			$return = readResults( 0, $meetingId );
		}

	}

	if ( isset( $_GET['format'] ) && $_GET['format'] == 'raw' ) {
		echo( "<pre>\r\n" );
		var_dump( $return );
		echo( '</pre>' );
	} else {
		echo( json_encode( $return ) );
	}
