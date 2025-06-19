import { Component, OnInit, OnDestroy } from '@angular/core';
import { CommonModule } from '@angular/common';
import { MatCardModule } from '@angular/material/card';
import { MatChipsModule } from '@angular/material/chips';
import { MatButtonModule } from '@angular/material/button';
import { MatIconModule } from '@angular/material/icon';
import { MatProgressSpinnerModule } from '@angular/material/progress-spinner';
import { BebidasService, Bebida } from '../../../services/bebidas.service';
import { LanguageService } from '../../../services/languaje.service';
import { AutoTranslateService } from '../../../services/AutoTranslateService';
import { Subscription } from 'rxjs';
import { TranslateModule } from '@ngx-translate/core';

@Component({
  selector: 'app-bebidas',
  standalone: true,
  imports: [
    CommonModule,
    MatCardModule,
    MatChipsModule,
    MatButtonModule,
    MatIconModule,
    MatProgressSpinnerModule,
    TranslateModule,
  ],
  templateUrl: './bebidas.component.html',
  styleUrls: ['./bebidas.component.scss'],
})
export class BebidasComponent implements OnInit, OnDestroy {
  bebidas: Bebida[] = [];
  private bebidasOriginales: Bebida[] = [];
  private languageSubscription?: Subscription;
  isTranslating = false;

  constructor(
    private bebidasService: BebidasService,
    private languageService: LanguageService,
    private autoTranslateService: AutoTranslateService
  ) {}

  ngOnInit(): void {
    // cargar datos
    this.cargarBebidas();

    // suscribirse solo a cambios de idioma
    this.languageSubscription = this.languageService.language$.subscribe(
      async (lang) => {
        console.log('Cambio de idioma en BebidasComponent:', lang);
        if (this.bebidasOriginales.length > 0) {
          await this.traducirDatos(lang);
        }
      }
    );
  }

  ngOnDestroy(): void {
    this.languageSubscription?.unsubscribe();
  }

  private cargarBebidas(): void {
    this.bebidasService.getBebidas().subscribe({
      next: async (bebidas) => {
        this.bebidasOriginales = bebidas.map((e) => ({
          ...e,
        }));

        // aplicar traducción inicial
        const currentLang = this.languageService.getCurrentLanguage();
        await this.traducirDatos(currentLang);
      },
      error: (error) => {
        console.error('Error al cargar bebidas:', error);
      },
    });
  }

  private async traducirDatos(lang: string): Promise<void> {
    console.log('Traduciendo bebidas a:', lang);

    // si es español, mostrar datos originales inmediatamente
    if (lang === 'es') {
      this.bebidas = [...this.bebidasOriginales];
      this.isTranslating = false;
      return;
    }

    try {
      this.isTranslating = true;

      const promesasTraduccion = this.bebidasOriginales.map(async (bebida) => {
        const [nombreTraducido, descripcionTraducida] = await Promise.all([
          this.autoTranslateService.translate(bebida.nombre, lang),
          this.autoTranslateService.translate(bebida.descripcion, lang),
        ]);

        return {
          ...bebida,
          nombre: nombreTraducido,
          descripcion: descripcionTraducida,
        };
      });

      const bebidasTraducidas = await Promise.all(promesasTraduccion);
      this.bebidas = bebidasTraducidas;
      console.log('Traducción de bebidas completada');
    } catch (error) {
      console.error('Error en la traducción:', error);
      // en caso de error, mostrar datos originales
      this.bebidas = [...this.bebidasOriginales];
    } finally {
      this.isTranslating = false;
    }
  }

  getImagenUrl(nombreArchivo: string): string {
    return `http://127.0.0.1:8000/uploads/productoImg/${nombreArchivo}`;
  }

  onVerDetalles(bebida: Bebida): void {
    console.log('Ver detalles de:', bebida);
  }
}
