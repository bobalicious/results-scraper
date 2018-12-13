<?php
	header("Access-Control-Allow-Origin: *"); 

	error_reporting(E_ALL);
	
	set_time_limit ( 60*60 ); // 1 hour 
	$debug = 0;

	require_once( 'powerof10.php' );
	require_once( 'keyfunctions.php' );

	$return = [ 'error' => 'A valid mode was not specified' ];

	if ( isset( $_GET['mode'] ) ) {

		if ( $_GET['mode'] == 'races' ) {

			$startDate   = '';
			$endDate     = '';
			$eventSearch = '';
			$venueSearch = '';

			if ( isset( $_GET['startdate'] ) ) {
				$startDate = $_GET['startdate'];
			}

			if ( isset( $_GET['enddate'] ) ) {
				$endDate = $_GET['enddate'];
			}

			if ( isset( $_GET['event'] ) ) {
				$eventSearch = $_GET['event'];
			}

			if ( isset( $_GET['venue'] ) ) {
				$venueSearch = $_GET['venue'];
			}

			$return = readRaces( $debug, $startDate, $endDate, $eventSearch, $venueSearch );
		}

		if ( $_GET['mode'] == 'athletes' ) {
			$return = readAthletes( $debug );
		}

		if ( $_GET['mode'] == 'athleteResults' ) {
			$athleteId = $_GET['id'];
			$return = readAthleteResults( $debug, $athleteId );
		}

		if ( $_GET['mode'] == 'raceResults' ) {
			$meetingId = $_GET['id'];
			$return = readResults( $debug, $meetingId );
		}

	}

	if ( isset( $_GET['format'] ) && $_GET['format'] == 'raw' ) {
		echo( "<pre>\r\n" );
		var_dump( $return );
		echo( '</pre>' );
	} else {
		echo( json_encode( $return ) );
	}
