//en este componente, se presentan los componentes que se van a usar en la aplicacion: en landing-page, el contacto...

import { Component } from '@angular/core';
import { HeaderComponent } from '../shared/header/header.component';
import { RouterOutlet } from '@angular/router';
import { CommonModule } from '@angular/common';
import { FooterComponent } from '../shared/footer/footer.component';
import { ContactComponent } from '../pages/contact/contact.component';
import { LandpageComponent } from '../pages/landpage/landpage.component';
import { AboutComponent } from '../pages/about/about.component';
import { ReservationsComponent } from '../pages/reservations/reservations.component';
import { RouterModule } from '@angular/router';

@Component({
  selector: 'app-layout',
  imports: [
    HeaderComponent,
    CommonModule,
    FooterComponent,
    ContactComponent,
    LandpageComponent,
    AboutComponent,
    ReservationsComponent,
    RouterOutlet,
  ],
  templateUrl: './layout.component.html',
  styleUrl: './layout.component.scss',
  standalone: true,
})
export class LayoutComponent {}
