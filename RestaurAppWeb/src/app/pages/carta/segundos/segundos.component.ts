import { Component, OnInit, OnDestroy } from '@angular/core';
import { CommonModule } from '@angular/common';
import { MatCardModule } from '@angular/material/card';
import { MatChipsModule } from '@angular/material/chips';
import { MatButtonModule } from '@angular/material/button';
import { MatIconModule } from '@angular/material/icon';
import { MatProgressSpinnerModule } from '@angular/material/progress-spinner';
import { SegundosService, Segundo } from '../../../services/segundos.service';
import { LanguageService } from '../../../services/languaje.service';
import { AutoTranslateService } from '../../../services/AutoTranslateService';
import { Router } from '@angular/router';
import { Subscription } from 'rxjs';
import { TranslateModule } from '@ngx-translate/core';

@Component({
  selector: 'app-segundos',
  standalone: true,
  imports: [
    CommonModule,
    MatCardModule,
    MatChipsModule,
    MatButtonModule,
    MatIconModule,
    TranslateModule,
  ],
  templateUrl: './segundos.component.html',
  styleUrls: ['./segundos.component.scss'],
})
export class SegundosComponent implements OnInit, OnDestroy {
  segundos: Segundo[] = [];
  private segundosOriginales: Segundo[] = [];
  private languageSubscription?: Subscription;
  isTranslating = false;

  constructor(
    private segundosService: SegundosService,
    private languageService: LanguageService,
    private autoTranslateService: AutoTranslateService,
    private router: Router
  ) {}

  ngOnInit(): void {
    // cargar datos
    this.cargarSegundos();

    // suscribirse solo a cambios de idioma
    this.languageSubscription = this.languageService.language$.subscribe(
      async (lang) => {
        console.log('Cambio idioma en SegundosComponent:', lang);
        if (this.segundosOriginales.length > 0) {
          await this.traducirDatos(lang);
        }
      }
    );
  }

  ngOnDestroy(): void {
    this.languageSubscription?.unsubscribe();
  }

  private cargarSegundos(): void {
    this.segundosService.getSegundos().subscribe({
      next: async (segundos) => {
        this.segundosOriginales = segundos.map((s) => ({
          ...s,
        }));

        // aplicar traducción inicial
        const currentLang = this.languageService.getCurrentLanguage();
        await this.traducirDatos(currentLang);
      },
      error: (error) => {
        console.error('Error al cargar segundos:', error);
      },
    });
  }

  private async traducirDatos(lang: string): Promise<void> {
    console.log('Traduciendo segundos a:', lang);

    // si es español, mostrar datos originales inmediatamente
    if (lang === 'es') {
      this.segundos = [...this.segundosOriginales];
      this.isTranslating = false;
      return;
    }

    try {
      this.isTranslating = true;

      const promesasTraduccion = this.segundosOriginales.map(
        async (segundo) => {
          const [nombreTraducido, descripcionTraducida] = await Promise.all([
            this.autoTranslateService.translate(segundo.nombre, lang),
            this.autoTranslateService.translate(segundo.descripcion, lang),
          ]);

          return {
            ...segundo,
            nombre: nombreTraducido,
            descripcion: descripcionTraducida,
          };
        }
      );

      const segundosTraducidos = await Promise.all(promesasTraduccion);

      this.segundos = segundosTraducidos;

      console.log('Traducción completada');
    } catch (error) {
      console.error('Error en traducción:', error);
      // en caso de error, mostrar datos originales
      this.segundos = [...this.segundosOriginales];
    } finally {
      this.isTranslating = false;
    }
  }

  getImagenUrl(nombreArchivo: string): string {
    return `http://127.0.0.1:8000/uploads/productoImg/${nombreArchivo}`;
  }

  onVerDetalles(segundo: Segundo): void {
    console.log('Ver detalles de:', segundo);
  }
}
