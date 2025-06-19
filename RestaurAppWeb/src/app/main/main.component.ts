import { Component } from '@angular/core';
import { LandpageComponent } from '../pages/landpage/landpage.component';
import { AboutComponent } from '../pages/about/about.component';
import { ContactComponent } from '../pages/contact/contact.component';

@Component({
  selector: 'app-main',
  imports: [LandpageComponent, AboutComponent, ContactComponent],
  templateUrl: './main.component.html',
  styleUrl: './main.component.scss',
})
export class MainComponent {}
