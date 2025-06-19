import { Injectable } from '@angular/core';
import { HttpClient } from '@angular/common/http';
import { firstValueFrom } from 'rxjs';

@Injectable({
  providedIn: 'root',
})
export class AutoTranslateService {
  // API base de Apertium
  private apertiumApiUrl = 'https://www.apertium.org/apy';
  private cache = new Map<string, string>();

  constructor(private http: HttpClient) {
    console.log('AutoTranslateService inicializado con Apertium');
  }

  //funcion que se encargará de traducir
  async translate(
    text: string,
    targetLang: string,
    sourceLang = 'es'
  ): Promise<string> {
    console.log(`=== TRANSLATE REQUEST ===`);
    console.log('Texto:', text);
    console.log('De:', sourceLang, 'A:', targetLang);

    if (!text || text.trim() === '') {
      console.log('Texto vacío, retornando original');
      return text;
    }

    const key = `${text}_${sourceLang}_${targetLang}`;
    if (this.cache.has(key)) {
      const cached = this.cache.get(key)!;
      console.log('Encontrado en cache:', cached);
      return cached;
    }

    try {
      const langpair = `${sourceLang}|${targetLang}`;
      const encodedText = encodeURIComponent(text);
      const url = `${this.apertiumApiUrl}/translate?langpair=${langpair}&q=${encodedText}`;

      console.log('Enviando request a Apertium:', url);

      const response = await firstValueFrom(this.http.get<any>(url));

      console.log('Respuesta de Apertium:', response);

      const translatedText = response?.responseData?.translatedText || text;

      console.log('Texto traducido:', translatedText);

      this.cache.set(key, translatedText);
      return translatedText;
    } catch (error) {
      console.error('Error en traducción automática:', error);
      console.error('URL:', this.apertiumApiUrl);
      return text;
    }
  }

  clearCache(): void {
    console.log('Limpiando cache de traducciones');
    this.cache.clear();
  }

  //comprobamos que la api esté en funcionamiento
  async testConnection(): Promise<boolean> {
    try {
      const testText = 'Hola mundo';
      const result = await this.translate(testText, 'en');
      console.log('Test de conectividad exitoso:', result);
      return result.toLowerCase().includes('hello');
    } catch {
      console.error('Test de conectividad falló');
      return false;
    }
  }
}
