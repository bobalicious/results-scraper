import { Component, OnInit } from '@angular/core';

import { Race } from './race';
import { ResultsService} from './results.service';

@Component({
  selector: 'app-root',
  templateUrl: './app.component.html',
  styleUrls: ['./app.component.css']
})
export class AppComponent implements OnInit {
 
	/*
	Task list:

		* Done - Mechanism for selecting a race
		* Done - Once a race is selected, get the race from the current array, call for the results and render them

		* Find out why recent results aren't being returned.

		Finding the right race:
			* Pass filters into the race search API

		Rendering the results:
			* Filter the runners to only QPH runners (enter in input)
			* Add a position in category
			* Add a 'load next page' loop

		General things
			* Fix the 'get race' API race-subname
			* Consider the paging thing
			* Load up the Run Britian results

		Stretch goals
			* Try to put on an indicator that a race has a QP runner in the first page

	*/


	races          : Race[];
	selectableRaces: Race[];
	selectedRace   : Race;

	constructor( private resultsService: ResultsService ) { }

	ngOnInit() {
		console.log( 'init' );
		this.getRaces();
	}

	onRaceSelected( race : Race ) {
		this.selectedRace = race;
		this.getResults();
	}

	getRaces() {
		this.resultsService.getRaces()
			.subscribe( races => this.selectableRaces = races );
	}

	getResults() {
		this.resultsService.getResults( this.selectedRace.MeetingId )
	 		.subscribe( results => {
									this.races = results.map( raceToProcess => {
																			  	let race           = new Race();
																			  	race.MeetingName   = raceToProcess['Name'];
																			  	race.Results       = raceToProcess['Results'];
																			  	race.RawDate       = this.selectedRace.RawDate;
																				race.VenueName     = this.selectedRace.VenueName;
																				race.MeetingId     = this.selectedRace.MeetingId;
																				race.MeetingType   = this.selectedRace.MeetingType;
																				race.ResultsStatus = this.selectedRace.ResultsStatus;
																				race.RaceFullName  = this.selectedRace.RaceFullName;
																				race.RaceSubName   = "4.5MXC SW";
																				console.log( 'went round once' );
																				return race;
																			 	});
							  	});
	}

}
