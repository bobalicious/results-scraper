import { Component, OnInit } from '@angular/core';

import { Race } from './race';
import { ResultsService } from './results.service';
import { SearchCriteria } from './search-criteria';

@Component({
  selector: 'app-root',
  templateUrl: './app.component.html',
  styleUrls: ['./app.component.css']
})
export class AppComponent implements OnInit {
 
	/*
	Task list:

		* Sort out deployment to Heroku

		* Put on a loading indicator
		* Find more recent race results that do not show
		* lowercase all the race and result properties
		* Order the races properly

		Rendering the results:
			* Add a position in category

		Stretch goals
			* Try to put on an indicator that a race has a QP runner in the first page

	*/

	races          : Race[];
	selectableRaces: Race[];
	selectedRace   : Race;

	searchCriteria : SearchCriteria;

	constructor( private resultsService: ResultsService ) { }

	ngOnInit() {
		console.log( 'init' );
		this.searchCriteria = new SearchCriteria();
		this.searchCriteria.clubFilter = 'Queens Park';	
		this.getRaces();
	}

	onRaceSelected( race : Race ) {
		this.selectedRace = race;
		this.getResults();
	}

	handleClickedSearch() {
		this.getRaces();
	}

	handleClickedFilter() {
		this.filterResults();
	}

	getRaces() {
		this.resultsService.getRaces( this.searchCriteria )
			.subscribe( races => this.selectableRaces = races );
	}

	getResults() {
		this.races     = [];
		this.getResultsFromPage( 1 );
	}

	getResultsFromPage( pageNumber ) {

		console.log( 'Loading results from page: ' + pageNumber );
		this.resultsService.getResults( this.selectedRace.MeetingId, pageNumber )
	 		.subscribe( results => {
	 								if ( results.length > 0 ) {
	 									pageNumber++;
	 									this.addResults( results );
	 									this.getResultsFromPage( pageNumber );
	 								} else {
										console.log( 'Run out of results to load' );
	 								}
							  	});

	}

	addResults( results ) {

		let newRaces = results.map( raceToProcess => {
												  	let race           = new Race();
												  	race.MeetingName   = raceToProcess['Name'];
												  	race.Results       = raceToProcess['Results'];
												  	race.RawDate       = this.selectedRace.RawDate;
													race.VenueName     = this.selectedRace.VenueName;
													race.MeetingId     = this.selectedRace.MeetingId;
													race.MeetingType   = this.selectedRace.MeetingType;
													race.ResultsStatus = this.selectedRace.ResultsStatus;
													race.RaceFullName  = this.selectedRace.RaceFullName;
													race.RaceSubName   = raceToProcess['Name'];
													return race;
												 	});

		newRaces.forEach( ( thisNewRace, key) => {

			let existingRaces = this.races.filter( ( thisExistingRace ) =>  { return ( thisExistingRace.RaceSubName === thisNewRace.RaceSubName ) } );

			if ( existingRaces.length > 0 ) {
				existingRaces[0].Results = existingRaces[0].Results.concat( thisNewRace.Results );
			} else {
				this.races.push( thisNewRace );
			}
		})

		this.filterResults();

	}

	filterResults() {
		this.races && this.races.forEach( ( element, key ) => { element.filterResults( this.searchCriteria.clubFilter ) } );
	}
}
