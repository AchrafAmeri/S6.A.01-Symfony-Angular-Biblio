import { Component, inject, signal, OnInit } from '@angular/core';
import { RouterLink } from '@angular/router';
import { ApiService } from '../../services/api-service';
import { Categorie } from '../../models/categorie';

@Component({
  selector: 'app-categories-list',
  imports: [RouterLink],
  templateUrl: './categories-list.html',
})
export class CategoriesList implements OnInit {
  private apiService = inject(ApiService);
  categories = signal<Categorie[]>([]);

  ngOnInit() {
    this.apiService.getCategories().subscribe((data) => {
      this.categories.set(data);
    });
  }
}
