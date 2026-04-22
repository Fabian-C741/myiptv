<?php
namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\User;
use App\Models\DeviceSession;
use Illuminate\Support\Facades\Hash;

class UserAdminController extends Controller
{
    /**
     * Vista Web: Lista usuarios para el panel de administración.
     */
    public function indexWeb(Request $request)
    {
        $query = User::withCount(['devices', 'profiles'])
            ->withCount(['deviceSessions as active_sessions'])
            ->orderBy('created_at', 'desc');

        if ($request->has('search')) {
            $s = $request->search;
            $query->where(fn($q) => $q->where('name', 'like', "%$s%")->orWhere('email', 'like', "%$s%"));
        }

        $users = $query->paginate(20);
        return view('admin.users.index', compact('users'));
    }

    /**
     * Lista usuarios con paginación y búsqueda opcional.
     */
    public function index(Request $request)
    {
        $query = User::withCount(['devices', 'profiles'])
            ->withCount(['deviceSessions as active_sessions'])
            ->orderBy('created_at', 'desc');

        if ($request->has('search')) {
            $s = $request->search;
            $query->where(fn($q) => $q->where('name', 'like', "%$s%")->orWhere('email', 'like', "%$s%"));
        }

        if ($request->has('status')) {
            $query->where('status', $request->status);
        }

        return response()->json($query->paginate(50));
    }

    /**
     * Crea un nuevo usuario (solo desde el panel admin).
     */
    public function store(Request $request)
    {
        $request->validate([
            'name'        => 'required|string|max:255',
            'email'       => 'required|email|unique:users',
            'password'    => 'required|min:6',
            'max_devices' => 'integer|min:1|max:20'
        ]);

        $user = User::create([
            'name'        => $request->name,
            'email'       => $request->email,
            'password'    => Hash::make($request->password),
            'max_devices' => $request->max_devices ?? config('ott.default_max_devices', 3),
            'status'      => 'active'
        ]);

        return response()->json($user, 201);
    }

    /**
     * Actualiza el límite de dispositivos u otros campos del usuario.
     */
    public function update(Request $request, $id)
    {
        $request->validate([
            'max_devices' => 'sometimes|integer|min:1|max:20',
            'name'        => 'sometimes|string|max:255',
        ]);

        $user = User::findOrFail($id);
        $user->update($request->only(['name', 'max_devices']));

        return response()->json(['message' => 'Usuario actualizado', 'user' => $user]);
    }

    /**
     * Activa o suspende un usuario.
     * Si se suspende, revoca todos sus tokens activos.
     */
    public function updateStatus(Request $request, $id)
    {
        $request->validate(['status' => 'required|in:active,suspended']);
        $user = User::findOrFail($id);
        $user->update(['status' => $request->status]);

        if ($request->status === 'suspended') {
            $user->tokens()->delete();
            $user->devices()->update(['is_active' => false]);
        }

        return response()->json(['message' => 'Estado modificado', 'user' => $user]);
    }

    /**
     * Cierra todas las sesiones activas de un usuario sin suspenderlo.
     * El usuario puede volver a iniciar sesión normalmente.
     */
    public function closeSessions($id)
    {
        $user = User::findOrFail($id);
        $user->tokens()->delete();
        $user->devices()->update(['is_active' => false]);
        DeviceSession::where('user_id', $user->id)->delete();

        return response()->json(['message' => 'Todas las sesiones del usuario han sido cerradas']);
    }

    /**
     * Vista Web: Formulario para crear un nuevo cliente.
     */
    public function create()
    {
        return view('admin.users.create');
    }

    /**
     * Procesa la creación de un nuevo cliente desde la web.
     */
    public function storeWeb(Request $request)
    {
        $request->validate([
            'name'        => 'required|string|max:255',
            'email'       => 'required|email|unique:users',
            'password'    => 'required|min:6',
            'max_devices' => 'integer|min:1|max:20'
        ]);

        User::create([
            'name'        => $request->name,
            'email'       => $request->email,
            'password'    => Hash::make($request->password),
            'max_devices' => $request->max_devices ?? 3,
            'status'      => 'active'
        ]);

        return redirect()->route('admin.users')->with('success', 'Cliente creado exitosamente.');
    }

    /**
     * Vista Web: Formulario para editar un cliente.
     */
    public function edit($id)
    {
        $user = User::findOrFail($id);
        return view('admin.users.edit', compact('user'));
    }

    /**
     * Procesa la actualización de un cliente desde la web.
     */
    public function updateWeb(Request $request, $id)
    {
        $user = User::findOrFail($id);
        
        $request->validate([
            'name'        => 'required|string|max:255',
            'email'       => 'required|email|unique:users,email,'.$id,
            'max_devices' => 'integer|min:1|max:20',
            'password'    => 'nullable|min:6'
        ]);

        $data = $request->only(['name', 'email', 'max_devices']);
        if ($request->filled('password')) {
            // El modelo User tiene cast 'hashed' en password,
            // por lo que NO se debe llamar Hash::make() manualmente.
            $data['password'] = $request->password;
        }

        $user->update($data);

        return redirect()->route('admin.users')->with('success', 'Cliente actualizado correctamente.');
    }

    /**
     * Activa o suspende un usuario desde el panel Web.
     */
    public function updateStatusWeb(Request $request, $id)
    {
        $request->validate(['status' => 'required|in:active,suspended']);
        $user = User::findOrFail($id);
        $user->update(['status' => $request->status]);

        if ($request->status === 'suspended') {
            $user->tokens()->delete();
            $user->devices()->update(['is_active' => false]);
            DeviceSession::where('user_id', $user->id)->delete();
        }

        return redirect()->back()->with('success', 'Usuario actualizado correctamente.');
    }
}
