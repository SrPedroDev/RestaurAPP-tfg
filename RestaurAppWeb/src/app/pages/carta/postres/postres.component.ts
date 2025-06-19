import { Component, OnInit, OnDestroy } from '@angular/core';
import { CommonModule } from '@angular/common';
import { MatCardModule } from '@angular/material/card';
import { MatChipsModule } from '@angular/material/chips';
import { MatButtonModule } from '@angular/material/button';
import { MatIconModule } from '@angular/material/icon';
import { PostresService, Postre } from '../../../services/postres.service';
import { LanguageService } from '../../../services/languaje.service';
import { AutoTranslateService } from '../../../services/AutoTranslateService';
import { Subscription } from 'rxjs';
import { TranslateModule } from '@ngx-translate/core';

@Component({
  selector: 'app-postres',
  standalone: true,
  imports: [
    CommonModule,
    MatCardModule,
    MatChipsModule,
    MatButtonModule,
    MatIconModule,
    TranslateModule,
  ],
  templateUrl: './postres.component.html',
  styleUrls: ['./postres.component.scss'],
})
export class PostresComponent implements OnInit, OnDestroy {
  postres: Postre[] = [];
  private postresOriginales: Postre[] = [];
  private languageSubscription?: Subscription;
  isTranslating = false;

  constructor(
    private postresService: PostresService,
    private languageService: LanguageService,
    private autoTranslateService: AutoTranslateService
  ) {}

  ngOnInit(): void {
    this.cargarPostres();

    this.languageSubscription = this.languageService.language$.subscribe(
      async (lang) => {
        console.log('Cambio de idioma en PostresComponent:', lang);
        if (this.postresOriginales.length > 0) {
          await this.traducirDatos(lang);
        }
      }
    );
  }

  ngOnDestroy(): void {
    this.languageSubscription?.unsubscribe();
  }

  private cargarPostres(): void {
    this.postresService.getPostres().subscribe({
      next: async (postres) => {
        this.postresOriginales = postres.map((p) => ({
          ...p,
        }));

        const currentLang = this.languageService.getCurrentLanguage();
        await this.traducirDatos(currentLang);
      },
      error: (error) => {
        console.error('Error al cargar postres:', error);
      },
    });
  }

  private async traducirDatos(lang: string): Promise<void> {
    console.log('Traduciendo postres a:', lang);

    if (lang === 'es') {
      this.postres = [...this.postresOriginales];
      this.isTranslating = false;
      return;
    }

    try {
      this.isTranslating = true;

      const promesas = this.postresOriginales.map(async (postre) => {
        const [nombreTraducido, descripcionTraducida] = await Promise.all([
          this.autoTranslateService.translate(postre.nombre, lang),
          this.autoTranslateService.translate(postre.descripcion, lang),
        ]);

        return {
          ...postre,
          nombre: nombreTraducido,
          descripcion: descripcionTraducida,
        };
      });

      this.postres = await Promise.all(promesas);
      console.log('Traducción de postres completada');
    } catch (error) {
      console.error('Error en la traducción:', error);
      this.postres = [...this.postresOriginales];
    } finally {
      this.isTranslating = false;
    }
  }

  onVerDetalles(postre: Postre): void {
    console.log('Ver detalles de:', postre);
  }
}
