import { Component, Input } from '@angular/core';
import { Race } from './../race';

@Component({
  selector: 'app-results-renderer',
  templateUrl: './results-renderer.component.html',
  styleUrls: ['./results-renderer.component.css'],

})
export class ResultsRendererComponent {

  @Input()
  races: Race[];

  constructor() { }
}
