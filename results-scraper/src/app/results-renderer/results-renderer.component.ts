import { Component, OnInit } from '@angular/core';
import { RESULTS } from '../mock-results';

@Component({
  selector: 'app-results-renderer',
  templateUrl: './results-renderer.component.html',
  styleUrls: ['./results-renderer.component.css'],

})
export class ResultsRendererComponent implements OnInit {

  results = RESULTS;

  constructor() { }

  ngOnInit() {
  	console.log( 'init' );
  }

}
