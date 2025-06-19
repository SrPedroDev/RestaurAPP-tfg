import { Component, HostListener } from '@angular/core';
import { Router } from '@angular/router';
import { MatIconModule } from '@angular/material/icon';
import { CommonModule } from '@angular/common';
import { TranslateModule } from '@ngx-translate/core';
import { LanguageService } from '../../services/languaje.service';
@Component({
  selector: 'app-header',
  standalone: true,
  imports: [MatIconModule, CommonModule, TranslateModule],
  templateUrl: './header.component.html',
  styleUrls: ['./header.component.scss'],
})
export class HeaderComponent {
  private menuOpen = false;
  public isScrolled = false;

  constructor(
    private router: Router,
    private languageService: LanguageService
  ) {}

  @HostListener('window:scroll', [])
  onWindowScroll() {
    this.isScrolled = window.scrollY > 50;
  }

  toggleMenu() {
    this.menuOpen = !this.menuOpen;
  }

  isMenuOpen() {
    return this.menuOpen;
  }

  navigateToSection(section: string) {
    this.menuOpen = false;
    this.router.navigate(['/']).then(() => {
      setTimeout(() => this.scrollToElement(section), 100);
    });
  }

  private scrollToElement(sectionId: string): void {
    const element = document.getElementById(sectionId);
    if (element) {
      const headerHeight = 80;
      const topPosition = element.offsetTop - headerHeight;
      window.scrollTo({
        top: topPosition,
        behavior: 'smooth',
      });
    }
  }

  getCurrentLanguageName(): string {
    const currentLang = this.languageService.getCurrentLanguage();
    return currentLang === 'es' ? 'ESP' : 'ENG';
  }

  toggleLanguage(): void {
    const currentLang = this.languageService.getCurrentLanguage();
    const newLang = currentLang === 'es' ? 'en' : 'es';
    this.languageService.changeLanguage(newLang);
  }
}
