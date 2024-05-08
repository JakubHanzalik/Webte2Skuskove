import { Component, OnInit, OnDestroy } from '@angular/core';

import SwaggerUI from 'swagger-ui';

@Component({
  selector: 'app-swagger',
  standalone : true,
  templateUrl: './swagger.component.html',
  styleUrls: ['./swagger.component.scss'],
  imports:[ ]
})
export class SwaggerComponent implements OnInit, OnDestroy {
  private swaggerUI: any;

  constructor() { }

  ngOnInit(): void {
    this.swaggerUI = SwaggerUI({
      url: '/api/swagger',  
      dom_id: '#swagger-container'
    });
  }

  ngOnDestroy() {
    if (this.swaggerUI && typeof this.swaggerUI.cleanup === 'function') {
      this.swaggerUI.cleanup();
    }
  }
  
}
