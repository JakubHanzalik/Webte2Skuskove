import { Component, OnInit } from '@angular/core';
import { FormsModule } from '@angular/forms';  
import { HttpClientModule } from '@angular/common/http';
import { UserService } from '../services/users.service';  
import { User } from '../models/user.model';
import { CommonModule } from '@angular/common';
import { Router } from '@angular/router';
import { MatIconModule } from '@angular/material/icon';

@Component({
  selector: 'app-users',
  templateUrl: './users.component.html',
  styleUrls: ['./users.component.css'],
  standalone: true,
  imports: [
    CommonModule,
    FormsModule,  
    HttpClientModule ,
    MatIconModule
  ],
  providers: [UserService]
})
export class UsersComponent implements OnInit {

  users: User[] = [];
  newUser: Partial<User> = {};
  selectedUser: User | null = null;
  

  constructor(private userService: UserService, private router: Router) {}

  ngOnInit(): void {
    this.loadUsers();
  }

  loadUsers(): void {
    this.userService.getUsers().subscribe({
      next: (users) => {
        console.log('Fetched users:', users);  
        this.users = users;
      },
      error: (error) => console.error('Error fetching users:', error)
    });
  }

  editUser(user: User): void {
    if (user.id !== undefined) {
        this.selectedUser = { ...user };
        this.loadUserDetails(user.id);
    } else {
        console.error('User ID is undefined.');
    }
  }
  
  

  loadUserDetails(userId: number | undefined): void {
    if (userId !== undefined) {
        this.userService.getUserById(userId).subscribe({
            next: (user) => {
                console.log('User details:', user);
                this.selectedUser = { ...user , id: userId};
          
            },
            error: (error) => console.error('Error fetching user details:', error)
        });
    } else {
        console.error('User ID is undefined.');
    }
}


updateUser(user: User): void {
  if (user.id !== undefined) {
      this.userService.updateUser(user.id, user).subscribe({
          next: (updatedUser) => {
              console.log('User updated:', updatedUser);
              this.selectedUser = null;
              window.location.reload();
          },
          error: (error) => console.error('Error updating user:', error)
      });
  } else {
      console.error('Attempted to update a user without a valid ID');
  }
}



  
  navigateToUser(userId: number | undefined): void {
    if (userId !== undefined) {
        this.router.navigate(['/user', userId]);
    } else {
        console.error('User ID is undefined.');
    }
}

  saveUser(user: User): void {
    if (user.id) {
      this.userService.updateUser(user.id, user).subscribe({
        next: () => {
          console.log('User updated successfully');
          this.loadUsers(); 
        },
        error: (err) => console.error('Error updating user:', err)
      });
    } else {
      this.userService.createUser(user).subscribe({
        next: (newUser) => {
          console.log('User created successfully', newUser);
          this.users.push(newUser);
        },
        error: (err) => console.error('Error creating user:', err)
      });
    }
  }

  onDeleteUser(id: number | undefined): void {
    if (id !== undefined) {
      this.userService.deleteUser(id).subscribe({
        next: () => {
          console.log('User deleted successfully');
          this.users = this.users.filter(user => user.id !== id);
        },
        error: (error) => console.error('Error deleting user:', error)
      });
    } else {
      console.error('Attempted to delete a user without a valid ID.');
    }
  }
  
  cancelEdit(): void {
    this.selectedUser = null;

}

  addUser(): void {
    if (!this.newUser.username || !this.newUser.password || !this.newUser.name || !this.newUser.surname || this.newUser.role === undefined) {
      console.error('Please fill in all fields for the new user.');
      return;
    }
  
    if (this.newUser.role !== 0 && this.newUser.role !== 1) {
      console.error('Role must be either 0 or 1.');
      return;
    }
    this.userService.createUser(this.newUser as User).subscribe({
      next: (newUser) => {
        console.log('User created successfully', newUser);
        this.loadUsers(); 
        this.newUser = {}; 
      },
      error: (err) => console.error('Error creating user:', err)
    });
  }
  
  
  
  
  
}
