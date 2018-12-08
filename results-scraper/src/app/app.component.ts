import { Component, OnInit } from '@angular/core';

import { Race } from './race';
import { ResultsService} from './results.service';

@Component({
  selector: 'app-root',
  templateUrl: './app.component.html',
  styleUrls: ['./app.component.css']
})
export class AppComponent implements OnInit {
 
	races: Race[];

	constructor( private resultsService: ResultsService ) { }

	ngOnInit() {
		this.getResults();
		console.log( 'init' );
	}

	getResults() {
		this.resultsService.getResults()
	 		.subscribe( results => {

									this.races = results.map( raceToProcess => {
																			  	let race           = new Race();
																			  	race.MeetingName   = raceToProcess['Name'];
																			  	race.Results       = raceToProcess['Results'];
																			  	race.RawDate       = "Wed 5 Dec 2018";
																				race.VenueName     = "Grangemouth";
																				race.MeetingId     = "265286";
																				race.MeetingType   = "Indoor";
																				race.ResultsStatus = "&nbsp;";
																				race.RaceFullName  = "Grangemouth Open Graded Meeting (Grangemouth) - Indoor";
																				race.RaceSubName   = "4.5MXC SW";
																				console.log( 'went round once' );
																				return race;
																			 	});
							  	});
	}

}
