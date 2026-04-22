<?php
namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use App\Models\Profile;

class ParentalControl
{
    /**
     * Adjunta el perfil actual al request para que
     * los controladores puedan verificar is_kid sin repetir lógica.
     */
    public function handle(Request $request, Closure $next)
    {
        $profileId = $request->header('Profile-Id');
        
        if ($profileId) {
            $profile = Profile::where('id', $profileId)
                ->where('user_id', $request->user()->id)
                ->first();
            
            if ($profile) {
                $request->merge(['_profile' => $profile]);
            }
        }

        return $next($request);
    }
}
