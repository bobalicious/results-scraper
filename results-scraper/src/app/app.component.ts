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
 
	races          : Race[];
	selectableRaces: Race[];
	selectedRace   : Race;

	searchCriteria : SearchCriteria;

	constructor( private resultsService: ResultsService ) { }

	ngOnInit() {
		this.searchCriteria = new SearchCriteria();
		this.searchCriteria.clubFilter          = 'Queens Park';
		this.searchCriteria.excludeVenuesFilter = 'USA,ESP';

//		this.getRaces();
	}

	racesReturned() {
		return ( this.selectableRaces && this.selectableRaces.length > 0 );
	}

	onRaceSelected( race : Race ) {
		this.selectedRace = race;
		this.getResults();
	}

	handleClickedSearchForRaces() {
		this.getRaces();
	}

	handleClickedSearchForRunners() {
		this.checkForRunnersInAnyRace( this.selectableRaces );
	}

	getRaces() {
		this.resultsService.getRaces( this.searchCriteria )
			.subscribe( races => {
									this.selectableRaces = races;
//									this.checkForRunnersInAnyRace( races );
								 } );
	}

	checkForRunnersInAnyRace( races ) {
		races && races.length && this.checkForRunnersInThisRace( races[0], races.slice( 1 ) );
	}

	checkForRunnersInThisRace( race, remainingRaces ) {

		let racesWeAreLookingFor = this.selectableRaces.filter( thisSelectableRace => { return ( thisSelectableRace.MeetingId == race.MeetingId ) } );

		if ( racesWeAreLookingFor ) {
			racesWeAreLookingFor.forEach( thisRaceWeAreLookingFor => { thisRaceWeAreLookingFor.searchingForMatchingRunners = true } );
			this.getResultsFromPage( race.MeetingId
								   , ( results, willSearchForMore )  => {
														   					let foundRunner = this.checkForRunnerInResults( race.MeetingId, results );
														   					if ( foundRunner || !willSearchForMore ) {
																				racesWeAreLookingFor.forEach( thisRaceWeAreLookingFor => { thisRaceWeAreLookingFor.searchingForMatchingRunners = false } );														   						
															   					this.checkForRunnersInAnyRace( remainingRaces );
														   					}
														   					return !foundRunner;
																	    }
								   , 1, 5 );			
		}
	}

	checkForRunnerInResults( meetingId, results ) {

		let foundRunner = false;

		if ( results ) {
			let newRaces = results.map( raceToProcess => {
													  	let race           = new Race();
													  	race.MeetingId     = meetingId;
													  	race.MeetingName   = raceToProcess['Name'];
													  	race.Results       = raceToProcess['Results'];
														race.RaceSubName   = raceToProcess['Name'];
														return race;
													 	});

			newRaces.forEach( ( raceWithPotentialRunners, key ) => {
														raceWithPotentialRunners.filterResults( this.searchCriteria.clubFilter );
														if ( raceWithPotentialRunners.hasResults ) {
															let racesWithRunners = this.selectableRaces.filter( thisSelectableRace => { return ( thisSelectableRace.MeetingId == raceWithPotentialRunners.MeetingId ) } );
															racesWithRunners.forEach( thisRaceWithRunners => ( thisRaceWithRunners.hasRunnersMatchingFilter = true ) );
															racesWithRunners && ( foundRunner = true );
														}
													} );
		}
		return foundRunner;
	}

	getResults() {
		this.races     = [];
		this.getResultsFromPage( this.selectedRace.MeetingId, results => { this.addResults( results, true ); return true; }, null, null );
	}

	getResultsFromPage( meetingId, resultsCallback, pageNumber, maximumPages ) {

		pageNumber || ( pageNumber = 1 );

		this.resultsService.getResults( meetingId, pageNumber )
	 		.subscribe( results => {
	 									let willSearchForMore = ( results.length > 0 && ( !maximumPages || pageNumber < maximumPages ) );
	 									pageNumber++;
	 									! resultsCallback( results, willSearchForMore ) && ( willSearchForMore = false );

	 									willSearchForMore && this.getResultsFromPage( meetingId, resultsCallback, pageNumber, maximumPages );
								  	});
	}

	addResults( results, willSearchForMore ) {

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

		newRaces.forEach( ( thisNewRace, key ) => {

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
