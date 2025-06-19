import { Injectable } from '@angular/core';
import { HttpClient, HttpParams } from '@angular/common/http';
import { Observable, map } from 'rxjs';

export interface Primero {
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
export class PrimerosService {
  private apiUrl = 'http://127.0.0.1:8001/api/productos';
  private imagenBaseUrl = 'http://127.0.0.1:8001/uploads/productoImg/';

  constructor(private http: HttpClient) {}

  getPrimeros(categoriaNombre: string = 'Primeros'): Observable<Primero[]> {
    const params = new HttpParams().set('categoria', categoriaNombre);

    return this.http.get<Primero[]>(this.apiUrl, { params }).pipe(
      map((primeros) =>
        primeros.map((producto) => ({
          ...producto,
          imagen: this.imagenBaseUrl + producto.imagen,
        }))
      )
    );
  }
}
