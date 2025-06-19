import { Injectable } from '@angular/core';
import { HttpClient, HttpParams } from '@angular/common/http';
import { Observable, map } from 'rxjs';

export interface Segundo {
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
export class SegundosService {
  private apiUrl = 'http://127.0.0.1:8001/api/productos';
  private imagenBaseUrl = 'http://127.0.0.1:8001/uploads/productoImg/';

  constructor(private http: HttpClient) {}

  getSegundos(categoriaNombre: string = 'Segundos'): Observable<Segundo[]> {
    const params = new HttpParams().set('categoria', categoriaNombre);

    return this.http.get<Segundo[]>(this.apiUrl, { params }).pipe(
      map((segundos) =>
        segundos.map((producto) => ({
          ...producto,
          imagen: this.imagenBaseUrl + producto.imagen,
        }))
      )
    );
  }
}
