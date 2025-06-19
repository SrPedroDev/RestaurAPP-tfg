import { ComponentFixture, TestBed } from '@angular/core/testing';

import { SegundosComponent } from './segundos.component';

describe('SegundosComponent', () => {
  let component: SegundosComponent;
  let fixture: ComponentFixture<SegundosComponent>;

  beforeEach(async () => {
    await TestBed.configureTestingModule({
      imports: [SegundosComponent]
    })
    .compileComponents();

    fixture = TestBed.createComponent(SegundosComponent);
    component = fixture.componentInstance;
    fixture.detectChanges();
  });

  it('should create', () => {
    expect(component).toBeTruthy();
  });
});
