import { Injectable } from '@angular/core';
import { Subject } from 'rxjs';

@Injectable({
  providedIn: 'root'
})
export class NavbarService {
  public visible: Subject<boolean> = new Subject<boolean>();
  constructor() {
    this.visible.next(true);
   }
   hide(){
    this.visible.next(false);
   }
   show(){
    this.visible.next(true);
   }
}
