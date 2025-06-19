import { Component, OnInit, OnDestroy } from '@angular/core';
import { CommonModule } from '@angular/common';
import { MatCardModule } from '@angular/material/card';
import { MatChipsModule } from '@angular/material/chips';
import { MatButtonModule } from '@angular/material/button';
import { MatIconModule } from '@angular/material/icon';
import {
  EntrantesService,
  Entrante,
} from '../../../services/entrantes.service';
import { LanguageService } from '../../../services/languaje.service';
import { AutoTranslateService } from '../../../services/AutoTranslateService';
import { Subscription } from 'rxjs';
import { TranslateModule } from '@ngx-translate/core';

@Component({
  selector: 'app-entrantes',
  standalone: true,
  imports: [
    CommonModule,
    MatCardModule,
    MatChipsModule,
    MatButtonModule,
    MatIconModule,
    TranslateModule,
  ],
  templateUrl: './entrantes.component.html',
  styleUrls: ['./entrantes.component.scss'],
})
export class EntrantesComponent implements OnInit, OnDestroy {
  entrantes: Entrante[] = [];
  private entrantesOriginales: Entrante[] = [];
  private languageSubscription?: Subscription;
  isTranslating = false;

  constructor(
    private entrantesService: EntrantesService,
    private languageService: LanguageService,
    private autoTranslateService: AutoTranslateService
  ) {}

  ngOnInit(): void {
    this.cargarEntrantes();

    this.languageSubscription = this.languageService.language$.subscribe(
      async (lang) => {
        console.log('Cambio de idioma en EntrantesComponent:', lang);
        if (this.entrantesOriginales.length > 0) {
          await this.traducirDatos(lang);
        }
      }
    );
  }

  ngOnDestroy(): void {
    this.languageSubscription?.unsubscribe();
  }

  private cargarEntrantes(): void {
    this.entrantesService.getEntrantes().subscribe({
      next: async (entrantes) => {
        this.entrantesOriginales = entrantes.map((e) => ({
          ...e,
        }));

        const currentLang = this.languageService.getCurrentLanguage();
        await this.traducirDatos(currentLang);
      },
      error: (error) => {
        console.error('Error al cargar entrantes:', error);
      },
    });
  }

  private async traducirDatos(lang: string): Promise<void> {
    console.log('Traduciendo entrantes a:', lang);

    if (lang === 'es') {
      this.entrantes = [...this.entrantesOriginales];
      this.isTranslating = false;
      return;
    }

    try {
      this.isTranslating = true;

      const promesas = this.entrantesOriginales.map(async (entrante) => {
        const [nombreTraducido, descripcionTraducida] = await Promise.all([
          this.autoTranslateService.translate(entrante.nombre, lang),
          this.autoTranslateService.translate(entrante.descripcion, lang),
        ]);

        return {
          ...entrante,
          nombre: nombreTraducido,
          descripcion: descripcionTraducida,
        };
      });

      this.entrantes = await Promise.all(promesas);
      console.log('Traducción de entrantes completada');
    } catch (error) {
      console.error('Error en la traducción:', error);
      this.entrantes = [...this.entrantesOriginales];
    } finally {
      this.isTranslating = false;
    }
  }

  getImagenUrl(nombreArchivo: string): string {
    return `http://127.0.0.1:8000/uploads/productoImg/${nombreArchivo}`;
  }

  onVerDetalles(entrante: Entrante): void {
    console.log('Ver detalles de:', entrante);
  }
}
