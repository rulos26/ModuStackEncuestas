<?php

namespace App\Exports;

use Illuminate\Contracts\View\View;
use Maatwebsite\Excel\Concerns\FromCollection;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\ShouldAutoSize;

class UsersExport implements FromCollection, WithHeadings, ShouldAutoSize
{
    protected $users;
    public function __construct($users)
    {
        $this->users = $users;
    }
    public function collection()
    {
        return $this->users->map(function($user) {
            return [
                'ID' => $user->id,
                'Nombre' => $user->name,
                'Email' => $user->email,
                'Rol' => $user->role,
                'Creado' => $user->created_at,
            ];
        });
    }
    public function headings(): array
    {
        return ['ID', 'Nombre', 'Email', 'Rol', 'Creado'];
    }
}
