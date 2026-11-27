<?php

namespace App\Support;

use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use LogicException;

class AdminAccess
{
    public function adminRoleName(?int $userId): ?string
    {
        if (!$userId || !Schema::hasTable('usuarios') || !Schema::hasTable('rol_usuario')) {
            return null;
        }

        $roles = collect();

        if (Schema::hasColumn('usuarios', 'id_rol_usuario')) {
            $legacyRoles = DB::table('usuarios')
                ->join('rol_usuario', 'usuarios.id_rol_usuario', '=', 'rol_usuario.id_rol')
                ->where('usuarios.id', $userId);

            if (Schema::hasColumn('usuarios', 'deleted_at')) {
                $legacyRoles->whereNull('usuarios.deleted_at');
            }
            if (Schema::hasColumn('rol_usuario', 'deleted_at')) {
                $legacyRoles->whereNull('rol_usuario.deleted_at');
            }

            $roles = $roles->merge($legacyRoles->pluck('rol_usuario.rol_nombre'));
        }

        if (Schema::hasTable('usuario_roles')) {
            $pivotRoles = DB::table('usuario_roles')
                ->join('usuarios', 'usuario_roles.fk_id_usuario', '=', 'usuarios.id')
                ->join('rol_usuario', 'usuario_roles.fk_id_rol', '=', 'rol_usuario.id_rol')
                ->where('usuario_roles.fk_id_usuario', $userId);

            if (Schema::hasColumn('usuarios', 'deleted_at')) {
                $pivotRoles->whereNull('usuarios.deleted_at');
            }
            if (Schema::hasColumn('rol_usuario', 'deleted_at')) {
                $pivotRoles->whereNull('rol_usuario.deleted_at');
            }

            $roles = $roles->merge($pivotRoles->pluck('rol_usuario.rol_nombre'));
        }

        return $roles->first(function ($roleName) {
            return in_array(mb_strtolower(trim((string) $roleName)), ['admin', 'administrador'], true);
        });
    }

    public function isAdmin(?int $userId): bool
    {
        return $this->adminRoleName($userId) !== null;
    }

    public function record(int $userId, string $action): void
    {
        $user = DB::table('usuarios')->where('id', $userId)->first(['nombre']);
        $roleName = $this->adminRoleName($userId);

        if (!$user || !$roleName) {
            throw new LogicException('Solo se pueden registrar acciones de usuarios administradores.');
        }

        DB::table('admin_activity_logs')->insert([
            'id_usuario' => $userId,
            'nombre_usuario' => $user->nombre,
            'rol_usuario' => $roleName,
            'accion' => $action,
            'momento' => now(),
        ]);
    }
}
