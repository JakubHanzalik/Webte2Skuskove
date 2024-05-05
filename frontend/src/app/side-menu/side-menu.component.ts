import { Component } from '@angular/core';
import { AuthenticationService } from '../authentication.service';
import { RouterModule, RouterOutlet } from '@angular/router';

@Component({
  selector: 'app-side-menu',
  standalone: true,
  imports: [
    RouterOutlet,
    RouterModule
   ],
  templateUrl: './side-menu.component.html',
  styleUrl: './side-menu.component.css'
})
export class SideMenuComponent {
  constructor(public auth: AuthenticationService){}
}
