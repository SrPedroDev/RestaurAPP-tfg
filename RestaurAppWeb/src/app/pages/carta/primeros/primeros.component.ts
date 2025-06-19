import { Component, OnInit, OnDestroy } from '@angular/core';
import { CommonModule } from '@angular/common';
import { MatCardModule } from '@angular/material/card';
import { MatChipsModule } from '@angular/material/chips';
import { MatButtonModule } from '@angular/material/button';
import { MatIconModule } from '@angular/material/icon';
import { PrimerosService, Primero } from '../../../services/primeros.service';
import { LanguageService } from '../../../services/languaje.service';
import { AutoTranslateService } from '../../../services/AutoTranslateService';
import { Subscription } from 'rxjs';
import { TranslateModule } from '@ngx-translate/core';

@Component({
  selector: 'app-primeros',
  standalone: true,
  imports: [
    CommonModule,
    MatCardModule,
    MatChipsModule,
    MatButtonModule,
    MatIconModule,
    TranslateModule,
  ],
  templateUrl: './primeros.component.html',
  styleUrls: ['./primeros.component.scss'],
})
export class PrimerosComponent implements OnInit, OnDestroy {
  primeros: Primero[] = [];
  private primerosOriginales: Primero[] = [];
  private languageSubscription?: Subscription;
  isTranslating = false;

  constructor(
    private primerosService: PrimerosService,
    private languageService: LanguageService,
    private autoTranslateService: AutoTranslateService
  ) {}

  ngOnInit(): void {
    this.cargarPrimeros();

    this.languageSubscription = this.languageService.language$.subscribe(
      async (lang) => {
        console.log('Cambio de idioma en PrimerosComponent:', lang);
        if (this.primerosOriginales.length > 0) {
          await this.traducirDatos(lang);
        }
      }
    );
  }

  ngOnDestroy(): void {
    this.languageSubscription?.unsubscribe();
  }

  private cargarPrimeros(): void {
    this.primerosService.getPrimeros().subscribe({
      next: async (primeros) => {
        this.primerosOriginales = primeros.map((p) => ({
          ...p,
        }));

        const currentLang = this.languageService.getCurrentLanguage();
        await this.traducirDatos(currentLang);
      },
      error: (error) => {
        console.error('Error al cargar primeros:', error);
      },
    });
  }

  private async traducirDatos(lang: string): Promise<void> {
    console.log('Traduciendo primeros a:', lang);

    if (lang === 'es') {
      this.primeros = [...this.primerosOriginales];
      this.isTranslating = false;
      return;
    }

    try {
      this.isTranslating = true;

      const promesas = this.primerosOriginales.map(async (primero) => {
        const [nombreTraducido, descripcionTraducida] = await Promise.all([
          this.autoTranslateService.translate(primero.nombre, lang),
          this.autoTranslateService.translate(primero.descripcion, lang),
        ]);

        return {
          ...primero,
          nombre: nombreTraducido,
          descripcion: descripcionTraducida,
        };
      });

      this.primeros = await Promise.all(promesas);
      console.log('Traducción de primeros completada');
    } catch (error) {
      console.error('Error en la traducción:', error);
      this.primeros = [...this.primerosOriginales];
    } finally {
      this.isTranslating = false;
    }
  }

  onVerDetalles(primero: Primero): void {
    console.log('Ver detalles de:', primero);
  }
}
