import { Injectable } from '@angular/core';
import { Observable, of } from 'rxjs';
import { Result } from './result';
import { Race } from './race';
import { HttpClient } from '@angular/common/http';


@Injectable({
	providedIn: 'root'
})
export class ResultsService {

//	baseUrl    = 'https://results-scraper.herokuapp.com/';
	baseUrl    = 'http://localhost:8080/';


	resultsUrl = this.baseUrl + '?mode=raceResults'; // &id=261986';
	racesUrl   = this.baseUrl + '?mode=races';

	constructor( private http: HttpClient ) { }

	getRaces() : Observable<Race[]> {
		return this.http.get<Race[]>( this.racesUrl );
	}

	getResults( meetingId : string ) : Observable<Result[]> {
		return this.http.get<Result[]>( this.resultsUrl + '&id=' + meetingId );
	}

}
