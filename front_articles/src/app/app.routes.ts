import { Routes } from '@angular/router';
import { Home } from './components/home/home';
import { CategoriesList } from './components/categories-list/categories-list';
import { ArticlesList } from './components/articles-list/articles-list';
import { AddArticle } from './components/add-article/add-article';

export const routes: Routes = [
  { path: '', component: Home },
  { path: 'categories', component: CategoriesList },
  { path: 'articles', component: ArticlesList },
  { path: 'add-article', component: AddArticle },
];
