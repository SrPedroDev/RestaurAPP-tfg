import { ComponentFixture, TestBed } from '@angular/core/testing';

import { PrimerosComponent } from './primeros.component';

describe('PrimerosComponent', () => {
  let component: PrimerosComponent;
  let fixture: ComponentFixture<PrimerosComponent>;

  beforeEach(async () => {
    await TestBed.configureTestingModule({
      imports: [PrimerosComponent]
    })
    .compileComponents();

    fixture = TestBed.createComponent(PrimerosComponent);
    component = fixture.componentInstance;
    fixture.detectChanges();
  });

  it('should create', () => {
    expect(component).toBeTruthy();
  });
});
