import { NgModule }         from '@angular/core';
import { BrowserModule }    from '@angular/platform-browser';
import { HttpClientModule } from '@angular/common/http';
import { FormsModule } from '@angular/forms';

import { AppComponent } from './app.component';
import { ResultsRendererComponent } from './results-renderer/results-renderer.component';
import { ResultsService } from './results.service';
import { RaceListRendererComponent } from './race-list-renderer/race-list-renderer.component';


@NgModule({
  declarations: [
    AppComponent,
    ResultsRendererComponent,
    RaceListRendererComponent,
  ],
  imports: [
    BrowserModule,
    HttpClientModule,
    FormsModule
  ],
  providers: [],
  bootstrap: [AppComponent]
})
export class AppModule { }
