<?php
	header("Access-Control-Allow-Origin: *"); 

	error_reporting(E_ALL);
	
	set_time_limit ( 60*60 ); // 1 hour 
	$debug = 0;

	require_once( 'powerof10.php' );
	require_once( 'keyfunctions.php' );

	$return = [ 'error' => 'A valid mode was not specified - did you mean to go to index.html instead?' ];

	if ( isset( $_GET['mode'] ) ) {

		if ( $_GET['mode'] == 'races' ) {

			$startDate     = '';
			$endDate       = '';
			$meetingSearch = '';
			$meetingFilter = '';
			$venueSearch   = '';

			if ( isset( $_GET['datefrom'] ) ) {
				$startDate = $_GET['datefrom'];
			}

			if ( isset( $_GET['dateto'] ) ) {
				$endDate = $_GET['dateto'];
			}

			if ( isset( $_GET['meeting'] ) ) {
				$meetingSearch = $_GET['meeting'];
			}

			if ( isset( $_GET['venue'] ) ) {
				$venueSearch = $_GET['venue'];
			}


			if ( isset( $_GET['venuesFilter'] ) ) {
				$venuesFilter = $_GET['venuesFilter'];
			}

			$return = readRaces( $debug, $startDate, $endDate, $meetingSearch, $venueSearch, $venuesFilter );
		}

		if ( $_GET['mode'] == 'athletes' ) {
			$return = readAthletes( $debug );
		}

		if ( $_GET['mode'] == 'athleteResults' ) {
			$athleteId = $_GET['id'];
			$return = readAthleteResults( $debug, $athleteId );
		}

		if ( $_GET['mode'] == 'raceResults' ) {
			$meetingId  = $_GET['id'];
			$pageNumber = isset( $_GET['page'] ) ? $_GET['page'] : 1;
			$return = readResults( $debug, $meetingId, $pageNumber );
		}

	}

	if ( isset( $_GET['format'] ) && $_GET['format'] == 'raw' ) {
		echo( "<pre>\r\n" );
		var_dump( $return );
		echo( '</pre>' );
	} else {
		echo( json_encode( $return ) );
	}
