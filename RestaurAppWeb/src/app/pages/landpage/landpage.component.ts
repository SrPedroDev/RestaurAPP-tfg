import { Component } from '@angular/core';
import { MatIcon } from '@angular/material/icon';
import { MatIconModule } from '@angular/material/icon';
import { MatDividerModule } from '@angular/material/divider';
import { MatButtonModule } from '@angular/material/button';
import { AboutComponent } from '../about/about.component';
import { ContactComponent } from '../contact/contact.component';
import { TranslateService } from '@ngx-translate/core';
import { TranslateModule } from '@ngx-translate/core';
import { RouterModule } from '@angular/router';

@Component({
  selector: 'app-landpage',
  imports: [
    MatButtonModule,
    MatDividerModule,
    MatIconModule,
    MatIcon,
    AboutComponent,
    ContactComponent,
    TranslateModule,
    RouterModule,
  ],
  templateUrl: './landpage.component.html',
  styleUrl: './landpage.component.scss',
  standalone: true,
})
export class LandpageComponent {
  openMenuInNewTab(): void {
    window.open('/menu', '_blank');
  }
}
