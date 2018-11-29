<pre>
<?php

	set_time_limit ( 60*60 ); // 1 hour 
	$debug = 0;


	require_once( 'powerof10.php' );
	require_once( 'keyfunctions.php' );

	var_dump( readAthletes( $debug ) );

?>
</pre>