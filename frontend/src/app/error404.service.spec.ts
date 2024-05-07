import { TestBed } from '@angular/core/testing';

import { Error404Service } from './error404.service';

describe('Error404Service', () => {
  let service: Error404Service;

  beforeEach(() => {
    TestBed.configureTestingModule({});
    service = TestBed.inject(Error404Service);
  });

  it('should be created', () => {
    expect(service).toBeTruthy();
  });
});
