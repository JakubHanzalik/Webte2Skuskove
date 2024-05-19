import { Component, OnInit } from '@angular/core';
import { FormsModule } from '@angular/forms';
import { HttpClientModule } from '@angular/common/http';
import { UserService } from '../services/users.service';
import { User } from '../models/user.model';
import { UserUpdate } from '../models/user-update.model';
import { CommonModule } from '@angular/common';
import { Router } from '@angular/router';
import { MatIconModule } from '@angular/material/icon';
import { MatDialog } from '@angular/material/dialog';
import { UserDialogComponent } from '../user-dialog/user-dialog.component';
import { HttpClient } from '@angular/common/http';

@Component({
  selector: 'app-users',
  templateUrl: './users.component.html',
  styleUrls: ['./users.component.css'],
  standalone: true,
  imports: [
    CommonModule,
    FormsModule,
    HttpClientModule,
    MatIconModule
  ],
  providers: [UserService]
})
export class UsersComponent implements OnInit {

  users: User[] = [];
  newUser: Partial<User> = {};
  selectedUser: User | null = null;
  loggedInUser: User | null = null;
  newPassword: string = '';
  confirmPassword: string = '';

  constructor(
    private userService: UserService,
    private router: Router,
    private dialog: MatDialog,
    private http: HttpClient
  ) {}

  ngOnInit(): void {
    this.loadUsers();
    this.getLoggedInUser();
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

  getLoggedInUser(): void {
    const userCookie = document.cookie.split('; ').find(row => row.startsWith('user='));
    if (userCookie) {
      this.loggedInUser = JSON.parse(decodeURIComponent(userCookie.split('=')[1]));
    }
  }

  openDialog(title: string, message: string): void {
    const dialogRef = this.dialog.open(UserDialogComponent, {
      width: '300px',
      data: { title: title, message: message }
    });

    dialogRef.afterClosed().subscribe(result => {
      console.log('The dialog was closed');
      this.loadUsers();
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
          this.selectedUser = { ...user, id: userId };
        },
        error: (error) => console.error('Error fetching user details:', error)
      });
    } else {
      console.error('User ID is undefined.');
    }
  }

  updateUser(user: User): void {
    if (user.id !== undefined) {
      const updatedUserData: UserUpdate = {
        password: user.password,
        name: user.name,
        surname: user.surname,
        role: user.role
      };

      this.userService.updateUser(user.id, updatedUserData).subscribe({
        next: (updatedUser) => {
          console.log('User updated:', updatedUser);
          this.selectedUser = null;
          this.loadUsers();
          this.openDialog('Update Successful', 'User updated successfully!');
        },
        error: (error) => {
          console.error('Error updating user:', error);
          this.openDialog('Update Failed', 'Failed to update user.');
        }
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
      this.updateUser(user as User);
    } else {
      this.createUser(user);
    }
  }

  createUser(user: User): void {
    this.userService.createUser(user).subscribe({
      next: (newUser) => {
        console.log('User created successfully', newUser);
        this.resetNewUser();
        this.loadUsers();
        this.openDialog('Created Successful', 'User created successfully!');
      },
      error: (err) => {
        console.error('Error creating user:', err);
        this.openDialog('Creation Failed', 'Failed to create user.');
      }
    });
  }

  onDeleteUser(id: number | undefined): void {
    if (id === undefined) {
      console.error('Attempted to delete a user without a valid ID.');
      return;
    }

    const dialogRef = this.dialog.open(UserDialogComponent, {
      width: '300px',
      data: {
        title: 'Confirm Deletion',
        message: 'Are you sure you want to delete this user?',
        confirmable: true
      }
    });

    dialogRef.afterClosed().subscribe(result => {
      if (result === 'confirm') {
        this.userService.deleteUser(id).subscribe({
          next: () => {
            console.log('User deleted successfully');
            this.users = this.users.filter(user => user.id !== id);
            this.openDialog('Deletion Successful', 'User deleted successfully!');
          },
          error: (error) => {
            console.error('Error deleting user:', error);
            this.openDialog('Deletion Failed', 'Failed to delete user.');
          }
        });
      }
    });
  }

  cancelEdit(): void {
    this.selectedUser = null;
  }

  addUser(): void {
    if (!this.newUser.username || !this.newUser.password || !this.newUser.name || !this.newUser.surname || this.newUser.role === undefined) {
      this.openDialog('Validation Error', 'Please fill in all fields.');
      return;
    }

    if (this.newUser.role !== 0 && this.newUser.role !== 1) {
      this.openDialog('Validation Error', 'Role must be either 0 or 1.');
      return;
    }

    this.createUser(this.newUser as User);
  }

  changePassword(): void {
    if (this.newPassword !== this.confirmPassword) {
      this.openDialog('Validation Error', 'Passwords do not match.');
      return;
    }

    const changePasswordData = {
      password: this.newPassword
    };

    this.http.post('/api/change-password', changePasswordData).subscribe({
      next: () => {
        this.openDialog('Password Change Successful', 'Your password has been changed successfully!');
        this.newPassword = '';
        this.confirmPassword = '';
      },
      error: (err) => {
        console.error('Error changing password:', err);
        this.openDialog('Password Change Failed', 'Failed to change password.');
      }
    });
  }

  private resetNewUser(): void {
    this.newUser = {};
  }
}
