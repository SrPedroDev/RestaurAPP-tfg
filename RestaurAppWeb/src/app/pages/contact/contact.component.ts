import {
  ChangeDetectionStrategy,
  Component,
  ElementRef,
  ViewChild,
  ChangeDetectorRef,
  NgZone,
} from '@angular/core';
import { CommonModule } from '@angular/common';
import { TranslateModule } from '@ngx-translate/core';

@Component({
  selector: 'app-contact',
  standalone: true,
  imports: [CommonModule, TranslateModule],
  templateUrl: './contact.component.html',
  styleUrl: './contact.component.scss',
  changeDetection: ChangeDetectionStrategy.OnPush,
})
export class ContactComponent {
  @ViewChild('contactForm') contactForm!: ElementRef<HTMLFormElement>;
  @ViewChild('submitButton') submitButton!: ElementRef<HTMLButtonElement>;

  showSuccessMessage = false;

  constructor(private cdr: ChangeDetectorRef, private ngZone: NgZone) {}

  onSubmit(event: Event): void {
    event.preventDefault();

    const submitButton = this.submitButton.nativeElement;
    const originalButtonText = submitButton.innerHTML;

    submitButton.innerHTML =
      '<span class="btn-text">Enviando...</span><span class="btn-icon">⏳</span>';
    submitButton.disabled = true;

    const form = this.contactForm.nativeElement;
    const formData = new FormData(form);

    if (!formData.has('_captcha')) {
      formData.append('_captcha', 'false');
    }
    if (!formData.has('_next')) {
      formData.append('_next', 'false');
    }
    if (!formData.has('_subject')) {
      formData.append('_subject', 'Nuevo mensaje desde la web del restaurante');
    }

    fetch(form.action, {
      method: form.method,
      body: formData,
      headers: {
        Accept: 'application/json',
      },
    })
      .then((response) => {
        if (response.ok) {
          this.ngZone.run(() => {
            this.showSuccessMessage = true;
            form.reset();
            submitButton.innerHTML = originalButtonText;
            submitButton.disabled = false;
            this.cdr.detectChanges();

            setTimeout(() => {
              this.showSuccessMessage = false;
              this.cdr.detectChanges();
            }, 8000);
          });
        } else {
          throw new Error('Error en el envío');
        }
      })
      .catch((error) => {
        console.error('Error al enviar el formulario:', error);
        this.ngZone.run(() => {
          submitButton.innerHTML = originalButtonText;
          submitButton.disabled = false;
          this.cdr.detectChanges();

          alert(
            'Hubo un error al enviar el mensaje. Por favor, inténtalo de nuevo.'
          );
        });
      });
  }
}
