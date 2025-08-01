<?php
namespace App\Controllers;
use Core\Auth;
use function view, redirect, flash;

class AuthController {
    public function showLogin(){
        if (\Core\Auth::check()) {
            flash('info','Sesión activa','Ya has iniciado sesión');
            return redirect('/dashboard');
        }
        return view('auth/login');
    }

    public function login(){
        $reason = '';
        $ok = Auth::attempt($_POST['name'] ?? '', $_POST['password'] ?? '', $reason);

        switch ($reason){
            case 'ok':
                flash('success','Bienvenido','Acceso correcto');
                return redirect('/dashboard');
            case 'active':
                flash('warning','Sesión abierta','Tu cuenta ya tiene una sesión activa. Si no la reconoces, contacta al administrador.');
                break;
            case 'blocked':
                flash('error','Acceso restringuido','Tu cuenta está bloqueada o el usuario es incorrecto. Si necesitas ayuda, contacta al administrador.');
                break;
            default:
                flash('error','Credenciales inválidas','Contraseña incorrecta');
                break;
        }
        return redirect('/');
    }

    public function logout(){
        flash('info','Sesión cerrada','Has salido correctamente');
        Auth::logout();
        return redirect('/');
    }
}