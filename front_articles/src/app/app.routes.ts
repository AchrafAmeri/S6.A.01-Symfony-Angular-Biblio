import { Routes } from '@angular/router';
import { Home } from './components/home/home';
import { LivresList } from './components/livres-list/livres-list';
import { Profil } from './components/profil/profil';
import { Login } from './components/login/login';
import { authGuard, adminGuard } from './guards/auth-guard';
import { CategoriesList } from './components/categories-list/categories-list';
import { LivreDetail } from './components/livre-detail/livre-detail';
import { AuteursList } from './components/auteurs-list/auteurs-list';
import { AuteurDetail } from './components/auteur-detail/auteur-detail';
import { AdminDashboard } from './components/admin-dashboard/admin-dashboard';

export const routes: Routes = [
  { path: '', component: Home },
  { path: 'livres', component: LivresList },
  { path: 'livres/:id', component: LivreDetail },
  { path: 'auteurs', component: AuteursList },
  { path: 'auteurs/:id', component: AuteurDetail },
  { path: 'categories', component: CategoriesList },
  { path: 'admin/dashboard', component: AdminDashboard, canActivate: [adminGuard] },
  { path: 'profil', component: Profil, canActivate: [authGuard] },
  { path: 'login', component: Login }
];
