import { Injectable } from '@angular/core';
import { TranslateService } from '@ngx-translate/core';
import { BehaviorSubject } from 'rxjs';

@Injectable({
  providedIn: 'root',
})
export class LanguageService {
  private languageSubject = new BehaviorSubject<string>(
    this.getSavedLanguage()
  );
  language$ = this.languageSubject.asObservable();

  constructor(private translateService: TranslateService) {
    const savedLang = this.getSavedLanguage();
    this.translateService.setDefaultLang('es');
    this.translateService.use(savedLang);
  }

  changeLanguage(language: string) {
    this.translateService.use(language);
    localStorage.setItem('language', language);
    this.languageSubject.next(language);
  }

  getCurrentLanguage(): string {
    return this.translateService.currentLang || 'es';
  }

  private getSavedLanguage(): string {
    return localStorage.getItem('language') || 'es';
  }
}
