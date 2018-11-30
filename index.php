<pre>
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

	if ( $GET['mode'] == 'races' ) {
		$return = readRaces( 0 ) );
	}

	if ( $GET['mode'] == 'athletes' ) {
		$return = readAthletes( 0 );
	}

	if ( $GET['mode'] == 'athleteResults' ) {
		$athleteId = $GET['id'];
		$return = readAthleteResults( 0, $athleteId );
	}

	echo( json_encode( $return ) );

?>
</pre>