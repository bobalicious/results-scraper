<pre>
<?php
	error_reporting(E_ALL);
	
	set_time_limit ( 60*60 ); // 1 hour 
	$debug = 1;

	require_once( 'powerof10.php' );
	require_once( 'keyfunctions.php' );

	$athletes = readAthletes( $debug );

	var_dump( readAthleteResults( 2, $athletes[0] ) );

?>
</pre>