<?php

namespace App\Console\Commands;

use App\Models\Role;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;

class RemoveExternalClientRole extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'role:remove-external-client';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Elimina el rol Cliente Externo y sus relaciones';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        $roleName = 'Cliente Externo';

        $role = Role::where('name', $roleName)->first();

        if (!$role) {
            $this->error("❌ El rol '{$roleName}' no existe.");
            return Command::FAILURE;
        }

        DB::transaction(function () use ($role) {

            // Eliminar relaciones en la tabla pivote
            DB::table('role_user')
                ->where('role_id', $role->id)
                ->delete();

            // Eliminar el rol
            $role->delete();
        });

        $this->info("✅ Rol '{$roleName}' eliminado correctamente.");
        return Command::SUCCESS;
    }
}
