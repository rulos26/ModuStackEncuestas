<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Spatie\Permission\Models\Permission;
use Spatie\Permission\Models\Role;

class roleSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $Superadmin = Role::create(['name' => 'Superadmin']);
        $admin = Role::create(['name' => 'Admin']);
        $cliente = Role::create(['name' => 'Cliente']);
        //$Paseador= Role::create(['name' => 'Paseador']);
        Permission::create(['name' => 'home'])->syncRoles([$Superadmin, $admin, $cliente ]); // pagina principal

        //Permission::create(['name' => 'departamentos.index'])->syncRoles([$Superadmin, $admin]);
        //Permission::create(['name' => 'departamentos.create'])->syncRoles([$Superadmin]);
        //Permission::create(['name' => 'departamentos.show'])->syncRoles([$Superadmin, $admin]);
        //Permission::create(['name' => 'departamentos.edit'])->syncRoles([$Superadmin, $admin]);
        //Permission::create(['name' => 'departamentos.destroy'])->syncRoles([$Superadmin]);
        // permisos paises
        /* Permission::create(['name' => 'paises.index'])->syncRoles([$Superadmin, $admin]);
        Permission::create(['name' => 'paises.create'])->syncRoles([$Superadmin]);
        Permission::create(['name' => 'paises.show'])->syncRoles([$Superadmin, $admin]);
        Permission::create(['name' => 'paises.edit'])->syncRoles([$Superadmin, $admin]);
        Permission::create(['name' => 'paises.destroy'])->syncRoles([$Superadmin]);
 */

        // permisos departamentos
        /*  Permission::create(['name' => 'departamentos.index'])->syncRoles([$Superadmin, $admin]);
         Permission::create(['name' => 'departamentos.create'])->syncRoles([$Superadmin]);
         Permission::create(['name' => 'departamentos.show'])->syncRoles([$Superadmin, $admin]);
         Permission::create(['name' => 'departamentos.edit'])->syncRoles([$Superadmin, $admin]);
         Permission::create(['name' => 'departamentos.destroy'])->syncRoles([$Superadmin]); */

        // permisos municipios
        /* Permission::create(['name' => 'municipios.index'])->syncRoles([$Superadmin, $admin]);
        Permission::create(['name' => 'municipios.create'])->syncRoles([$Superadmin]);
        Permission::create(['name' => 'municipios.show'])->syncRoles([$Superadmin, $admin]);
        Permission::create(['name' => 'municipios.edit'])->syncRoles([$Superadmin, $admin]);
        Permission::create(['name' => 'municipios.destroy'])->syncRoles([$Superadmin]); */

        // permisos usuarios
        /*     Permission::create(['name' => 'users.index'])->syncRoles([$Superadmin, $admin]);
            Permission::create(['name' => 'users.create'])->syncRoles([$Superadmin]);
            Permission::create(['name' => 'users.show'])->syncRoles([$Superadmin, $admin]);
            Permission::create(['name' => 'users.edit'])->syncRoles([$Superadmin, $admin]);
            Permission::create(['name' => 'users.destroy'])->syncRoles([$Superadmin]);
            Permission::create(['name' => 'users.roles'])->syncRoles([$Superadmin]); */

        // permisos   protecion de datos personales
        /*    Permission::create(['name' => 'data-habeas.index'])->syncRoles([$Superadmin, $admin]);
           Permission::create(['name' => 'data-habeas.create'])->syncRoles([$Superadmin]);
           Permission::create(['name' => 'data-habeas.show'])->syncRoles([$Superadmin, $admin]);
           Permission::create(['name' => 'data-habeas.edit'])->syncRoles([$Superadmin]);
           Permission::create(['name' => 'data-habeas.destroy'])->syncRoles([$Superadmin]);
           Permission::create(['name' => 'data-habeas.roles'])->syncRoles([$Superadmin]); */

        /* //permisos terminos y condicones
        Permission::create(['name' => 'data-terminos.index'])->syncRoles([$Superadmin, $admin]);
        Permission::create(['name' => 'data-terminos.create'])->syncRoles([$Superadmin]);
        Permission::create(['name' => 'data-terminos.show'])->syncRoles([$Superadmin, $admin]);
        Permission::create(['name' => 'data-terminos.edit'])->syncRoles([$Superadmin]);
        Permission::create(['name' => 'data-terminos.destroy'])->syncRoles([$Superadmin]);
        Permission::create(['name' => 'data-terminos.roles'])->syncRoles([$Superadmin]);

        //permisos dias festivos
        Permission::create(['name' => 'festivos.index'])->syncRoles([$Superadmin, $admin]);
        Permission::create(['name' => 'festivos.create'])->syncRoles([$Superadmin]);
        Permission::create(['name' => 'festivos.show'])->syncRoles([$Superadmin, $admin]);
        Permission::create(['name' => 'festivos.edit'])->syncRoles([$Superadmin, $admin]);
        Permission::create(['name' => 'festivos.destroy'])->syncRoles([$Superadmin]);
        Permission::create(['name' => 'festivos.roles'])->syncRoles([$Superadmin]);

        //permisos profesiones
        Permission::create(['name' => 'profesiones.index'])->syncRoles([$Superadmin, $admin]);
        Permission::create(['name' => 'profesiones.create'])->syncRoles([$Superadmin]);
        Permission::create(['name' => 'profesiones.show'])->syncRoles([$Superadmin, $admin]);
        Permission::create(['name' => 'profesiones.edit'])->syncRoles([$Superadmin, $admin]);
        Permission::create(['name' => 'profesiones.destroy'])->syncRoles([$Superadmin]);
        Permission::create(['name' => 'profesiones.roles'])->syncRoles([$Superadmin]);

        //permisos tipos-contrato
        Permission::create(['name' => 'tipos-contrato.index'])->syncRoles([$Superadmin, $admin]);
        Permission::create(['name' => 'tipos-contrato.create'])->syncRoles([$Superadmin]);
        Permission::create(['name' => 'tipos-contrato.show'])->syncRoles([$Superadmin, $admin]);
        Permission::create(['name' => 'tipos-contrato.edit'])->syncRoles([$Superadmin, $admin]);
        Permission::create(['name' => 'tipos-contrato.destroy'])->syncRoles([$Superadmin]);

        //permisos tipo de jornada
        Permission::create(['name' => 'tipos-jornada.index'])->syncRoles([$Superadmin, $admin]);
        Permission::create(['name' => 'tipos-jornada.create'])->syncRoles([$Superadmin]);
        Permission::create(['name' => 'tipos-jornada.show'])->syncRoles([$Superadmin, $admin]);
        Permission::create(['name' => 'tipos-jornada.edit'])->syncRoles([$Superadmin, $admin]);
        Permission::create(['name' => 'tipos-jornada.destroy'])->syncRoles([$Superadmin]);

        //permisos tipos de bancos
        Permission::create(['name' => 'tipos-bancos.index'])->syncRoles([$Superadmin, $admin]);
        Permission::create(['name' => 'tipos-bancos.create'])->syncRoles([$Superadmin]);
        Permission::create(['name' => 'tipos-bancos.show'])->syncRoles([$Superadmin, $admin]);
        Permission::create(['name' => 'tipos-bancos.edit'])->syncRoles([$Superadmin, $admin]);
        Permission::create(['name' => 'tipos-bancos.destroy'])->syncRoles([$Superadmin]);

        //permisos tipo de cuentas
        Permission::create(['name' => 'tipos-cuenta.index'])->syncRoles([$Superadmin, $admin]);
        Permission::create(['name' => 'tipos-cuenta.create'])->syncRoles([$Superadmin]);
        Permission::create(['name' => 'tipos-cuenta.show'])->syncRoles([$Superadmin, $admin]);
        Permission::create(['name' => 'tipos-cuenta.edit'])->syncRoles([$Superadmin, $admin]);
        Permission::create(['name' => 'tipos-cuenta.destroy'])->syncRoles([$Superadmin]);

        //permisos tipos de entidades
        Permission::create(['name' => 'tipos-entidads.index'])->syncRoles([$Superadmin, $admin]);
        Permission::create(['name' => 'tipos-entidads.create'])->syncRoles([$Superadmin]);
        Permission::create(['name' => 'tipos-entidads.show'])->syncRoles([$Superadmin, $admin]);
        Permission::create(['name' => 'tipos-entidads.edit'])->syncRoles([$Superadmin, $admin]);
        Permission::create(['name' => 'tipos-entidads.destroy'])->syncRoles([$Superadmin]);

        //permisos logs
        Permission::create(['name' => 'logs.index'])->syncRoles([$Superadmin, $admin]);
        Permission::create(['name' => 'logs.create'])->syncRoles([$Superadmin]);
        Permission::create(['name' => 'logs.show'])->syncRoles([$Superadmin, $admin]);
        Permission::create(['name' => 'logs.edit'])->syncRoles([$Superadmin, $admin]);
        Permission::create(['name' => 'logs.destroy'])->syncRoles([$Superadmin]);

        //permisos ruta  anexos
        Permission::create(['name' => 'anexos.index'])->syncRoles([$Superadmin, $admin]);
        Permission::create(['name' => 'anexos.create'])->syncRoles([$Superadmin]);
        Permission::create(['name' => 'anexos.show'])->syncRoles([$Superadmin, $admin]);
        Permission::create(['name' => 'anexos.edit'])->syncRoles([$Superadmin]);
        Permission::create(['name' => 'anexos.destroy'])->syncRoles([$Superadmin]);

        //permisos contratos
        Permission::create(['name' => 'contratos.index'])->syncRoles([$Superadmin, $admin]);
        Permission::create(['name' => 'contratos.create'])->syncRoles([$Superadmin]);
        Permission::create(['name' => 'contratos.show'])->syncRoles([$Superadmin, $admin]);
        Permission::create(['name' => 'contratos.edit'])->syncRoles([$Superadmin]);
        Permission::create(['name' => 'contratos.destroy'])->syncRoles([$Superadmin]);

        //permisos firmas
        Permission::create(['name' => 'firmas.index'])->syncRoles([$Superadmin, $admin]);
        Permission::create(['name' => 'firmas.create'])->syncRoles([$Superadmin]);
        Permission::create(['name' => 'firmas.show'])->syncRoles([$Superadmin, $admin]);
        Permission::create(['name' => 'firmas.edit'])->syncRoles([$Superadmin]);
        Permission::create(['name' => 'firmas.destroy'])->syncRoles([$Superadmin]);

        //permisos token de sguridad
        Permission::create(['name' => 'tokens.index'])->syncRoles([$Superadmin, $admin]);
        Permission::create(['name' => 'tokens.create'])->syncRoles([$Superadmin]);
        Permission::create(['name' => 'tokens.show'])->syncRoles([$Superadmin, $admin]);
        Permission::create(['name' => 'tokens.edit'])->syncRoles([$Superadmin]);
        Permission::create(['name' => 'tokens.destroy'])->syncRoles([$Superadmin]);

        //permisos menus
        Permission::create(['name' => 'config.menu'])->syncRoles([$Superadmin, $admin]);
        Permission::create(['name' => 'avisos.menu'])->syncRoles([$Superadmin, $admin]);
        Permission::create(['name' => 'calendario.menu'])->syncRoles([$Superadmin, $admin]);
        Permission::create(['name' => 'contrato.menu'])->syncRoles([$Superadmin, $admin]);
        Permission::create(['name' => 'bancos.menu'])->syncRoles([$Superadmin, $admin]);
        Permission::create(['name' => 'reportes.menu'])->syncRoles([$Superadmin, $admin]);
        Permission::create(['name' => 'patch.menu'])->syncRoles([$Superadmin, $admin]);
        Permission::create(['name' => 'usuarios.menu'])->syncRoles([$Superadmin, $admin]);
        Permission::create(['name' => 'ubicacion.menu'])->syncRoles([$Superadmin, $admin]);
        Permission::create(['name' => 'messages.menu'])->syncRoles([$Superadmin, $admin]);
        Permission::create(['name' => 'modulos.menu'])->syncRoles([$Superadmin, $admin]);
Permission::create(['name' => 'registro.menu'])->syncRoles([$cliente]);

        //permisos Mensajes
        Permission::create(['name' => 'messages.index'])->syncRoles([$Superadmin, $admin]);
        Permission::create(['name' => 'messages.create'])->syncRoles([$Superadmin]);
        Permission::create(['name' => 'messages.show'])->syncRoles([$Superadmin, $admin]);
        Permission::create(['name' => 'messages.edit'])->syncRoles([$Superadmin]);
        Permission::create(['name' => 'messages.destroy'])->syncRoles([$Superadmin]);

        //permisos modulo
        Permission::create(['name' => 'modulos.index'])->syncRoles([$Superadmin, $admin]);
        Permission::create(['name' => 'modulos.create'])->syncRoles([$Superadmin]);
        Permission::create(['name' => 'modulos.show'])->syncRoles([$Superadmin, $admin]);
        Permission::create(['name' => 'modulos.edit'])->syncRoles([$Superadmin]);
        Permission::create(['name' => 'modulos.destroy'])->syncRoles([$Superadmin]);


        //permisos tipo de mensaje
        Permission::create(['name' => 'tipos-messages.index'])->syncRoles([$Superadmin, $admin]);
        Permission::create(['name' => 'tipos-messages.create'])->syncRoles([$Superadmin]);
        Permission::create(['name' => 'tipos-messages.show'])->syncRoles([$Superadmin, $admin]);
        Permission::create(['name' => 'tipos-messages.edit'])->syncRoles([$Superadmin]);
        Permission::create(['name' => 'tipos-messages.destroy'])->syncRoles([$Superadmin]);

        //permisos perfiles
        Permission::create(['name' => 'Perfil.index'])->syncRoles([$Superadmin, $admin]);
        Permission::create(['name' => 'Perfil.create'])->syncRoles([$Superadmin]);
        Permission::create(['name' => 'Perfil.show'])->syncRoles([$Superadmin, $admin]);
        Permission::create(['name' => 'Perfil.edit'])->syncRoles([$Superadmin]);
        Permission::create(['name' => 'Perfil.destroy'])->syncRoles([$Superadmin]);

        //permisos pqrds
        Permission::create(['name' => 'pqrds.index'])->syncRoles([$Superadmin, $admin,$cliente]);
        Permission::create(['name' => 'pqrds.create'])->syncRoles([$Superadmin,$cliente]);
        Permission::create(['name' => 'pqrds.show'])->syncRoles([$Superadmin, $admin, $cliente]);
        Permission::create(['name' => 'pqrds.edit'])->syncRoles([$Superadmin]);
        Permission::create(['name' => 'pqrds.destroy'])->syncRoles([$Superadmin]);
        Permission::create(['name' => 'pqrds.menu'])->syncRoles([$Superadmin,$cliente, $admin]); */
    }
}
