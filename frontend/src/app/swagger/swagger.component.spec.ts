import { ComponentFixture, TestBed } from '@angular/core/testing';

import { SwaggerComponent } from './swagger.component';

describe('SwaggerComponent', () => {
  let component: SwaggerComponent;
  let fixture: ComponentFixture<SwaggerComponent>;

  beforeEach(async () => {
    await TestBed.configureTestingModule({
      imports: [SwaggerComponent]
    })
    .compileComponents();
    
    fixture = TestBed.createComponent(SwaggerComponent);
    component = fixture.componentInstance;
    fixture.detectChanges();
  });

  it('should create', () => {
    expect(component).toBeTruthy();
  });
});
