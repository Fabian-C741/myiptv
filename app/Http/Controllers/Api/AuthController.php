<?php
namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use App\Models\User;
use App\Models\Device;
use App\Services\TokenService;
use Stevebauman\Location\Facades\Location;

class AuthController extends Controller
{
    protected $tokenService;
    
    public function __construct(TokenService $tokenService)
    {
        $this->tokenService = $tokenService;
    }

    public function login(Request $request)
    {
        $request->validate([
            'email' => 'required|email',
            'password' => 'required',
            'device_id' => 'required|string',
            'device_name' => 'nullable|string',
            'device_type' => 'nullable|string',
        ]);

        $user = User::where('email', $request->email)->first();

        if (!$user || !Hash::check($request->password, $user->password)) {
            return response()->json(['message' => 'Credenciales inválidas'], 401);
        }

        if ($user->status === 'suspended') {
            return response()->json(['message' => 'Cuenta suspendida'], 403);
        }

        // Check device limit
        $activeDevicesCount = Device::where('user_id', $user->id)->where('is_active', true)->count();
        $device = Device::firstOrNew(['user_id' => $user->id, 'device_id' => $request->device_id]);
        
        if (!$device->exists && $activeDevicesCount >= $user->max_devices) {
            return response()->json([
                'message' => 'Límite de dispositivos alcanzado.',
                'code' => 'DEVICE_LIMIT_REACHED'
            ], 403);
        }

        // GeoIP
        $ip = $request->ip();
        $position = Location::get($ip);
        
        $device->fill([
            'device_name' => $request->device_name ?? 'Dispositivo ' . $request->device_id,
            'device_type' => $request->device_type,
            'ip_address' => $ip,
            'country' => $position ? $position->countryName : null,
            'city' => $position ? $position->cityName : null,
            'last_access' => now(),
            'is_active' => true
        ])->save();

        $token = $this->tokenService->createDeviceToken($user, $device);

        return response()->json([
            'user' => [
                'id' => $user->id,
                'name' => $user->name,
                'email' => $user->email,
                'role' => $user->role, // superadmin, suporte, user
                'max_devices' => $user->max_devices
            ],
            'token' => $token,
            'device_info' => $device
        ], 200);
    }

    public function logout(Request $request)
    {
        $request->user()->currentAccessToken()->delete();
        // Optionals: update DeviceSession based on current token
        return response()->json(['message' => 'Sesión cerrada correctamente']);
    }

    /**
     * Registro comentado para usos futuros.
     */
    /*
    public function register(Request $request)
    {
        $request->validate([
            'name' => 'required|string|max:255',
            'email' => 'required|string|email|max:255|unique:users',
            'password' => 'required|string|min:8',
        ]);

        $user = User::create([
            'name' => $request->name,
            'email' => $request->email,
            'password' => Hash::make($request->password),
            'max_devices' => 3
        ]);

        return response()->json(['message' => 'Usuario registrado exitosamente'], 201);
    }
    */
}
