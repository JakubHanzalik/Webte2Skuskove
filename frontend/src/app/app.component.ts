import { Component, NgModule } from '@angular/core';
import { RouterOutlet } from '@angular/router';
import { MaterialModule } from './material';
import { MenuComponent } from './menu/menu.component';
@Component({
  selector: 'app-root',
  standalone: true,
  imports: [RouterOutlet],
  templateUrl: './app.component.html',
  styleUrl: './app.component.css'
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
