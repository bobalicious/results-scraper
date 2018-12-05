import { async, ComponentFixture, TestBed } from '@angular/core/testing';

import { ResultsRendererComponent } from './results-renderer.component';

describe('ResultsRendererComponent', () => {
  let component: ResultsRendererComponent;
  let fixture: ComponentFixture<ResultsRendererComponent>;

  beforeEach(async(() => {
    TestBed.configureTestingModule({
      declarations: [ ResultsRendererComponent ]
    })
    .compileComponents();
  }));

  beforeEach(() => {
    fixture = TestBed.createComponent(ResultsRendererComponent);
    component = fixture.componentInstance;
    fixture.detectChanges();
  });

  it('should create', () => {
    expect(component).toBeTruthy();
  });
});
