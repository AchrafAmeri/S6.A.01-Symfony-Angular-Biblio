import { Component, inject, OnInit, signal } from '@angular/core';
import { ApiService } from '../../services/api-service';
import { CommonModule } from '@angular/common';

@Component({
  selector: 'app-admin-dashboard',
  imports: [CommonModule],
  templateUrl: './admin-dashboard.html',
  styleUrl: './admin-dashboard.css'
})
export class AdminDashboard implements OnInit {
  private apiService = inject(ApiService);
  stats = signal<any>(null);

  ngOnInit() {
    this.apiService.getStats().subscribe(data => this.stats.set(data));
  }
}
