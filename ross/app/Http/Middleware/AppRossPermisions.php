<?php
namespace App\Http\Middleware;

use Closure;
use Illuminate\Support\Facades\Auth;
use App\Models\Role;

class AppRossPermisions
{
    public function handle($request, Closure $next, $guard=null)
    {
        if( Auth::guard($guard)->check() and Auth::check() ){ 
            # -------------------------------------------
            # Aquí el usuario esta logueado al sistema, ahora hay que validar autorización de acceso.
            if( Role::authorizePermission() ) {
                return $next($request); #El Usuario tiene permisos.
            }else{
                return redirect('denied'); #El Usuario NO tiene acceso por ACL. 
            }
        }else{
			#Necesita iniciar sesión.
            return redirect('login'); 
        }
    }
}
