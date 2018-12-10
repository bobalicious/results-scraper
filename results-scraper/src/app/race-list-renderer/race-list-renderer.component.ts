import { Component, EventEmitter, Input, Output } from '@angular/core';

@Component({
  selector: 'app-race-list-renderer',
  templateUrl: './race-list-renderer.component.html',
  styleUrls: ['./race-list-renderer.component.css']
})
export class RaceListRendererComponent {

	@Input()
	races       : Race[];

	@Output()
	raceSelected = new EventEmitter<Race>();

//	selectedRace: Race;

	constructor() { }

	handleClickedRace( race : Race ) {
		this.raceSelected.emit( race );
//		selectedRace = race;

		console.log( 'race was clicked' );
		console.log( race );
	}
}
