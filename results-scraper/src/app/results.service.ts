import { Injectable } from '@angular/core';
import { Observable, of } from 'rxjs';
import { Result } from './result';
import { HttpClient } from '@angular/common/http';

@Injectable({
	providedIn: 'root'
})
export class ResultsService {

	resultsUrl = 'https://results-scraper.herokuapp.com/?mode=raceResults'; // &id=261986';
	racesUrl   = 'https://results-scraper.herokuapp.com/?mode=races';

	constructor( private http: HttpClient ) { }

	getRaces() : Observable<Race[]> {
		return this.http.get( this.racesUrl );
	}

	getResults( meetingId : string ) : Observable<Result[]> {
		return this.http.get( this.resultsUrl + '&id=' + meetingId );
	}

}
