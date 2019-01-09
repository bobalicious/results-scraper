import { Result } from './result';

export class Race {
	RawDate         : string;
	MeetingName     : string;
	VenueName       : string;
	MeetingId       : string;
	MeetingType     : string;
	ResultsStatus   : string;
	RaceFullName    : string;
	RaceSubName     : string;
	Results         : Result[];
	FilteredResults : Result[];

	showClub        : true;

	constructor() {
	}

	filterResults( clubFilter: string ) {
		this.showClub = clubFilter?false:true;
		this.FilteredResults = this.Results.filter( ( element ) => { return !clubFilter || element.Club.toLowerCase().includes( clubFilter.toLowerCase() ) } );
	}
}