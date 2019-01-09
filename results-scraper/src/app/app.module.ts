import { NgModule }         from '@angular/core';
import { BrowserModule }    from '@angular/platform-browser';
import { HttpClientModule } from '@angular/common/http';
import { FormsModule } from '@angular/forms';

import { AppComponent } from './app.component';
import { ResultsRendererComponent } from './results-renderer/results-renderer.component';
import { ResultsService } from './results.service';
import { RaceListRendererComponent } from './race-list-renderer/race-list-renderer.component';
import { OrdinalPipe } from './ordinal.pipe';


@NgModule({
  declarations: [
    AppComponent,
    ResultsRendererComponent,
    RaceListRendererComponent,
    OrdinalPipe,
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
