import { Component, inject, signal, OnInit } from '@angular/core';
import { ActivatedRoute } from '@angular/router';
import { DatePipe } from '@angular/common';
import { ApiService } from '../../services/api-service';
import { Article } from '../../models/article';

@Component({
  selector: 'app-articles-list',
  imports: [DatePipe],
  templateUrl: './articles-list.html',
})
export class ArticlesList implements OnInit {
  private apiService = inject(ApiService);
  private route = inject(ActivatedRoute);
  articles = signal<Article[]>([]);

  ngOnInit() {
    this.apiService.getArticles().subscribe((data) => {
      const categorieId = this.route.snapshot.queryParams['categorie'];
      if (categorieId) {
        this.articles.set(data.filter((a) => a.categorie?.id === +categorieId));
      } else {
        this.articles.set(data);
      }
    });
  }
}
