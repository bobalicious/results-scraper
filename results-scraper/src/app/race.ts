import { Result } from './result';

export class Race {
	RawDate      : string;
	MeetingName  : string;
	VenueName    : string;
	MeetingId    : string;
	MeetingType  : string;
	ResultsStatus: string;
	RaceFullName : string;
	RaceSubName  : string;
	Results      : Result[];
}