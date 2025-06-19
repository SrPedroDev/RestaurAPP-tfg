import { Routes } from '@angular/router';
import { LayoutComponent } from './layout/layout.component';

export const routes: Routes = [
  {
    path: '',
    component: LayoutComponent,
    children: [
      {
        path: '',
        loadComponent: () =>
          import('./main/main.component').then((m) => m.MainComponent),
      },
      {
        path: 'contact',
        loadComponent: () =>
          import('./pages/contact/contact.component').then(
            (m) => m.ContactComponent
          ),
      },
      {
        path: 'about',
        loadComponent: () =>
          import('./pages/about/about.component').then((m) => m.AboutComponent),
      },
      {
        path: 'landpage',
        loadComponent: () =>
          import('./pages/landpage/landpage.component').then(
            (m) => m.LandpageComponent
          ),
      },
      {
        path: 'menu',
        loadComponent: () =>
          import('./pages/menu/menu.component').then((m) => m.MenuComponent),
        children: [
          {
            path: 'entrantes',
            loadComponent: () =>
              import('./pages/carta/entrantes/entrantes.component').then(
                (m) => m.EntrantesComponent
              ),
          },
          {
            path: 'primeros',
            loadComponent: () =>
              import('./pages/carta/primeros/primeros.component').then(
                (m) => m.PrimerosComponent
              ),
          },
          {
            path: 'segundos',
            loadComponent: () =>
              import('./pages/carta/segundos/segundos.component').then(
                (m) => m.SegundosComponent
              ),
          },
          {
            path: 'postres',
            loadComponent: () =>
              import('./pages/carta/postres/postres.component').then(
                (m) => m.PostresComponent
              ),
          },
          {
            path: 'bebidas',
            loadComponent: () =>
              import('./pages/carta/bebidas/bebidas.component').then(
                (m) => m.BebidasComponent
              ),
          },
        ],
      },
    ],
  },
  {
    path: '**',
    redirectTo: '',
  },
];
