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

	showClub        : boolean = true;
	hasResults      : boolean = false;

	constructor() {
	}

	filterResults( clubFilter: string ) {
		this.showClub        = clubFilter?false:true;
		this.FilteredResults = this.Results.filter( ( element ) => { return !clubFilter || element.Club.toLowerCase().includes( clubFilter.toLowerCase() ) } );
		this.hasResults      = (this.FilteredResults.length > 0);
	}
}