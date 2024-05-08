export class User {
    constructor(
      public id?: number,
      public username: string = '',
      public password: string = '',
      public name: string = '',
      public surname: string = '',
      public role: number = 0
    ) {}
  }