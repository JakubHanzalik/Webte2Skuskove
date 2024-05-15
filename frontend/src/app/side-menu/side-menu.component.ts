import { ChangeDetectorRef, Component, ElementRef, OnDestroy, OnInit, Renderer2 } from '@angular/core';
import { Subscription } from 'rxjs';
import { AuthenticationService } from '../services/authentication.service';
import { Router, RouterModule, RouterOutlet } from '@angular/router';
import { MatToolbarModule } from '@angular/material/toolbar';
import { MatIconModule } from '@angular/material/icon';
import { HttpClientModule } from '@angular/common/http';
import { MatListModule } from '@angular/material/list';
@Component({
  selector: 'app-side-menu',
  standalone: true,
  imports: [
    RouterOutlet,
    RouterModule,
    MatToolbarModule,
    MatIconModule,
    HttpClientModule,
    MatListModule
  ],
  templateUrl: './side-menu.component.html',
  styleUrls: ['./side-menu.component.css']
})
export class SideMenuComponent implements OnInit, OnDestroy {
  isLoggedIn = false;
  private authSubscription!: Subscription;


  constructor( private cdr: ChangeDetectorRef,private authService: AuthenticationService, private router: Router, private renderer: Renderer2, private el: ElementRef) {}

  ngOnInit(): void {
    this.authService.isLoggedIn$.subscribe(isLoggedIn => {
      const loginEl = this.el.nativeElement.querySelector('#login-item');
      const registerEl = this.el.nativeElement.querySelector('#register-item');
      const logoutEl = this.el.nativeElement.querySelector('#logout-item');
      const tutorialEl = this.el.nativeElement.querySelector('#tutorial-item');
      const questionsEl = this.el.nativeElement.querySelector('#question-item');
      const userEl = this.el.nativeElement.querySelector('#user-item');
      const subjectEl = this.el.nativeElement.querySelector('#subject-item');
      const swaggerEl = this.el.nativeElement.querySelector('#swagger-item');
      this.renderer.setStyle(tutorialEl, 'display', isLoggedIn ? 'none' : 'block');
      this.renderer.setStyle(questionsEl, 'display', isLoggedIn ? 'block' : 'none');
      this.renderer.setStyle(userEl, 'display', isLoggedIn ? 'block' : 'none');
      this.renderer.setStyle(subjectEl, 'display', isLoggedIn ? 'block' : 'none');
      this.renderer.setStyle(swaggerEl, 'display', isLoggedIn ? 'block' : 'none');
      this.renderer.setStyle(loginEl, 'display', isLoggedIn ? 'none' : 'block');
      this.renderer.setStyle(registerEl, 'display', isLoggedIn ? 'none' : 'block');
      this.renderer.setStyle(logoutEl, 'display', isLoggedIn ? 'block' : 'none');
      this.cdr.detectChanges();
    });
  }

  logout(): void {
    this.authService.logout().subscribe({
      next: () => {
        console.log('[SideMenuComponent] Logout successful.');
      },
      error: (error) => {
        console.error('[SideMenuComponent] Logout failed:', error);
      }
    });
  }

  ngOnDestroy(): void {
    if (this.authSubscription) {
      this.authSubscription.unsubscribe();
    }
  }
}