import { ComponentFixture, TestBed } from '@angular/core/testing';

import { EntrantesComponent } from './entrantes.component';

describe('EntrantesComponent', () => {
  let component: EntrantesComponent;
  let fixture: ComponentFixture<EntrantesComponent>;

  beforeEach(async () => {
    await TestBed.configureTestingModule({
      imports: [EntrantesComponent]
    })
    .compileComponents();

    fixture = TestBed.createComponent(EntrantesComponent);
    component = fixture.componentInstance;
    fixture.detectChanges();
  });

  it('should create', () => {
    expect(component).toBeTruthy();
  });
});
