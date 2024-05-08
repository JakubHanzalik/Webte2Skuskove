import { Routes, RouterModule, Router } from '@angular/router';
import { NgModule } from '@angular/core';
import { HomeComponent } from './home/home.component';
import { LoginComponent } from './login/login.component';
import { RegisterComponent } from './register/register.component';
import { MenuComponent } from './menu/menu.component';
import { SwaggerComponent } from './swagger/swagger.component';
import { TutorialComponent } from './tutorial/tutorial.component';
import { QuestionComponent } from './question/question.component';
import { QuestionsComponent } from './questions/questions.component';
import { SubjectComponent } from './subjects/subjects.component';
import { UsersComponent } from './users/users.component';
import { Error404Component } from './error404/error404.component';
export const routes: Routes = [
  { path: '', component: HomeComponent, pathMatch: 'full'},
  { path: 'login', component: LoginComponent },
  { path: 'register', component: RegisterComponent },
  { path: 'menu' , component: MenuComponent },
  { path: 'swagger' , component: SwaggerComponent },
  { path: 'tutorial' , component: TutorialComponent },
  { path: 'users' , component: UsersComponent },
  { path: 'subjects' , component: SubjectComponent },
  { path: 'error404', component: Error404Component },
  { path: 'questions' , component: QuestionsComponent },
  { path: ':id' , component: QuestionComponent },
  // to do errors a dalsie cesty
  { path: '**' , redirectTo: '' }
];

@NgModule({
  imports: [RouterModule.forRoot(routes)],
  exports: [RouterModule]
})

export class AppRoutingModule {}
