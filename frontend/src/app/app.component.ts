import { Component, NgModule } from '@angular/core';
import { RouterModule, RouterOutlet } from '@angular/router';
import { MaterialModule } from './material';
import { MenuComponent } from './menu/menu.component';
import { SideMenuComponent } from './side-menu/side-menu.component';
import { HttpClientModule } from '@angular/common/http';
import { CommonModule } from '@angular/common';
import { routes } from './app.routes';
import { AuthenticationService } from './authentication.service';
@Component({
  selector: 'app-root',
  standalone: true,
  imports: [
    HttpClientModule,
    RouterModule,
    CommonModule,
    RouterOutlet,
    MenuComponent,
    SideMenuComponent

  ],
  templateUrl: './app.component.html',
  styleUrl: './app.component.css',
  providers: [ AuthenticationService ]
})
export class AppComponent {
  title = 'www-ise';
}
/*
@NgModule({
  imports: [
    MaterialModule
  ],
  declarations: [
    MenuComponent
  ]
})*/
