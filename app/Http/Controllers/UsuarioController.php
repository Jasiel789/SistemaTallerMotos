<?php

namespace App\Http\Controllers;

use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\Rule;
use Illuminate\Validation\Rules\Password;

class UsuarioController extends Controller
{
    public function index()
    {
        $usuarios = User::orderByDesc('rol') // dueño primero
            ->orderBy('name')
            ->get();

        return view('usuarios.index', compact('usuarios'));
    }

    public function create()
    {
        return view('usuarios.create', ['usuario' => new User(['rol' => 'empleado', 'activo' => true])]);
    }

    public function store(Request $request)
    {
        $data = $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'email' => ['required', 'email', 'max:255', 'unique:users,email'],
            'rol' => ['required', Rule::in(['dueno', 'empleado'])],
            'password' => ['required', 'confirmed', Password::defaults()],
        ]);

        $usuario = new User([
            'name' => $data['name'],
            'email' => $data['email'],
            'password' => Hash::make($data['password']),
        ]);
        $usuario->rol = $data['rol'];
        $usuario->activo = true;
        $usuario->save();

        return redirect()->route('usuarios.index')->with('status', 'Usuario creado.');
    }

    public function edit(User $usuario)
    {
        return view('usuarios.edit', compact('usuario'));
    }

    public function update(Request $request, User $usuario)
    {
        $data = $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'email' => ['required', 'email', 'max:255', Rule::unique('users', 'email')->ignore($usuario->id)],
            'rol' => ['required', Rule::in(['dueno', 'empleado'])],
            'password' => ['nullable', 'confirmed', Password::defaults()],
        ]);

        $usuario->name = $data['name'];
        $usuario->email = $data['email'];

        if (! empty($data['password'])) {
            $usuario->password = Hash::make($data['password']);
        }

        if ($usuario->id === $request->user()->id) {
            // Para no quedarte fuera del sistema: no puedes quitarte el rol ni desactivarte
            $usuario->rol = 'dueno';
            $usuario->activo = true;
        } else {
            $usuario->rol = $data['rol'];
            $usuario->activo = $request->boolean('activo');
        }

        $usuario->save();

        return redirect()->route('usuarios.index')->with('status', 'Usuario actualizado.');
    }
}
