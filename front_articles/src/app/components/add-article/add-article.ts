import { Component, inject, signal, OnInit } from '@angular/core';
import { FormsModule } from '@angular/forms';
import { Router } from '@angular/router';
import { ApiService } from '../../services/api-service';
import { Categorie } from '../../models/categorie';

@Component({
  selector: 'app-add-article',
  imports: [FormsModule],
  templateUrl: './add-article.html',
})
export class AddArticle implements OnInit {
  private apiService = inject(ApiService);
  private router = inject(Router);
  categories = signal<Categorie[]>([]);

  titre = '';
  contenu = '';
  categorieId = '';
  publie = false;

  ngOnInit() {
    this.apiService.getCategories().subscribe((data) => {
      this.categories.set(data);
    });
  }

  onSubmit() {
    const article = {
      titre: this.titre,
      contenu: this.contenu,
      categorie_id: +this.categorieId,
      publie: this.publie,
    };
    this.apiService.createArticle(article).subscribe(() => {
      this.router.navigate(['/articles']);
    });
  }
}
