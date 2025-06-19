import { Injectable } from '@angular/core';
import { HttpClient, HttpParams } from '@angular/common/http';
import { Observable, map } from 'rxjs';

export interface Entrante {
  tags: any;
  id: number;
  nombre: string;
  descripcion: string;
  precio: string;
  imagen: string;
  categoriaNombre: string;
}

@Injectable({
  providedIn: 'root',
})
export class EntrantesService {
  private apiUrl = 'http://127.0.0.1:8001/api/productos';
  private imagenBaseUrl = 'http://127.0.0.1:8001/uploads/productoImg/';

  constructor(private http: HttpClient) {}

  getEntrantes(categoriaNombre: string = 'Entrantes'): Observable<Entrante[]> {
    const params = new HttpParams().set('categoria', categoriaNombre);

    return this.http.get<Entrante[]>(this.apiUrl, { params }).pipe(
      map((entrantes) =>
        entrantes.map((producto) => ({
          ...producto,
          imagen: this.imagenBaseUrl + producto.imagen,
        }))
      )
    );
  }
}
