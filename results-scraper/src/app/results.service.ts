import { Injectable } from '@angular/core';
import { Observable, of } from 'rxjs';
import { Result } from './result';
import { Race } from './race';
import { SearchCriteria } from './search-criteria';
import { HttpClient, HttpParams } from '@angular/common/http';
import { environment } from '../environments/environment';

@Injectable({
	providedIn: 'root'
})
export class ResultsService {

	baseUrl    = environment.resutsServiceBaseUrl;

	resultsUrl = this.baseUrl + 'api.php?mode=raceResults';
	racesUrl   = this.baseUrl + 'api.php?mode=races';

	constructor( private http: HttpClient ) { }

	getRaces( searchCriteria : SearchCriteria ) : Observable<Race[]> {

	    let params = new HttpParams();

	    params = ( searchCriteria.dateFrom            ? params.set( 'datefrom'    , searchCriteria.dateFrom            ) : params );
	    params = ( searchCriteria.dateTo              ? params.set( 'dateto'      , searchCriteria.dateTo              ) : params );
	    params = ( searchCriteria.venue               ? params.set( 'venue'       , searchCriteria.venue               ) : params );
	    params = ( searchCriteria.excludeVenuesFilter ? params.set( 'venuesFilter', searchCriteria.excludeVenuesFilter ) : params );
	    params = ( searchCriteria.meeting             ? params.set( 'meeting'     , searchCriteria.meeting             ) : params );

	    // TODO: order by?

		return this.http.get<Race[]>( this.racesUrl, { params: params } );
	}

	getResults( meetingId : string, page : string ) : Observable<Result[]> {
		return this.http.get<Result[]>( this.resultsUrl + '&id=' + meetingId + '&page=' + page );
	}
}
