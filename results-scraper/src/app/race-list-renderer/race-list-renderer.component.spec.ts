import { async, ComponentFixture, TestBed } from '@angular/core/testing';

import { RaceListRendererComponent } from './race-list-renderer.component';

describe('RaceListRendererComponent', () => {
  let component: RaceListRendererComponent;
  let fixture: ComponentFixture<RaceListRendererComponent>;

  beforeEach(async(() => {
    TestBed.configureTestingModule({
      declarations: [ RaceListRendererComponent ]
    })
    .compileComponents();
  }));

  beforeEach(() => {
    fixture = TestBed.createComponent(RaceListRendererComponent);
    component = fixture.componentInstance;
    fixture.detectChanges();
  });

  it('should create', () => {
    expect(component).toBeTruthy();
  });
});
