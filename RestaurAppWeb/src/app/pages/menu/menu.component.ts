import { Component } from '@angular/core';
import { BebidasComponent } from '../carta/bebidas/bebidas.component';
import { PrimerosComponent } from '../carta/primeros/primeros.component';
import { EntrantesComponent } from '../carta/entrantes/entrantes.component';
import { SegundosComponent } from '../carta/segundos/segundos.component';
import { PostresComponent } from '../carta/postres/postres.component';

@Component({
  selector: 'app-menu',
  imports: [
    BebidasComponent,
    EntrantesComponent,
    SegundosComponent,
    PrimerosComponent,
    PostresComponent,
  ],
  templateUrl: './menu.component.html',
  styleUrl: './menu.component.scss',
  standalone: true,
})
export class MenuComponent {}
