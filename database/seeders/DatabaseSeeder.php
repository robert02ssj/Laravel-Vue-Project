<?php

namespace Database\Seeders;

use App\Models\Category;
use App\Models\Reservation;
use App\Models\Resource;
use App\Models\SiteSetting;
use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;
use Spatie\Permission\Models\Role;

class DatabaseSeeder extends Seeder
{
    public function run(): void
    {
        // Roles
        $admin   = Role::firstOrCreate(['name' => 'admin']);
        $manager = Role::firstOrCreate(['name' => 'manager']);
        $user    = Role::firstOrCreate(['name' => 'user']);

        // Usuarios
        $adminUser = User::firstOrCreate(['email' => 'admin@reservas.es'], [
            'name'     => 'Administrador',
            'password' => Hash::make('password'),
        ]);
        $adminUser->assignRole('admin');

        $managerUser = User::firstOrCreate(['email' => 'gestor@reservas.es'], [
            'name'     => 'Gestor',
            'password' => Hash::make('password'),
        ]);
        $managerUser->assignRole('manager');

        $testUser = User::firstOrCreate(['email' => 'usuario@reservas.es'], [
            'name'     => 'Usuario Demo',
            'password' => Hash::make('password'),
        ]);
        $testUser->assignRole('user');

        // Categorías
        $salas = Category::firstOrCreate(['slug' => 'salas'], [
            'name'        => 'Salas de reuniones',
            'description' => 'Salas equipadas para reuniones y conferencias',
            'icon'        => 'mdi-office-building',
            'color'       => '#3B82F6',
            'active'      => true,
        ]);

        $equipos = Category::firstOrCreate(['slug' => 'equipos'], [
            'name'        => 'Equipamiento',
            'description' => 'Equipos y herramientas reservables',
            'icon'        => 'mdi-laptop',
            'color'       => '#10B981',
            'active'      => true,
        ]);

        $instalaciones = Category::firstOrCreate(['slug' => 'instalaciones'], [
            'name'        => 'Instalaciones deportivas',
            'description' => 'Pistas y zonas deportivas',
            'icon'        => 'mdi-soccer-field',
            'color'       => '#F59E0B',
            'active'      => true,
        ]);

        // Recursos
        $r1 = Resource::firstOrCreate(['slug' => 'sala-a'], [
            'category_id'   => $salas->id,
            'name'          => 'Sala A',
            'description'   => 'Sala principal con proyector y videoconferencia',
            'location'      => 'Planta 1 - Ala Norte',
            'capacity'      => 10,
            'active'        => true,
            'open_time'     => '08:00',
            'close_time'    => '20:00',
            'slot_duration' => 60,
            'price_per_slot' => 0,
            'available_days' => [1, 2, 3, 4, 5],
        ]);

        $r2 = Resource::firstOrCreate(['slug' => 'sala-b'], [
            'category_id'   => $salas->id,
            'name'          => 'Sala B',
            'description'   => 'Sala pequeña para reuniones de hasta 6 personas',
            'location'      => 'Planta 1 - Ala Sur',
            'capacity'      => 6,
            'active'        => true,
            'open_time'     => '09:00',
            'close_time'    => '18:00',
            'slot_duration' => 30,
            'price_per_slot' => 0,
            'available_days' => [1, 2, 3, 4, 5],
        ]);

        $r3 = Resource::firstOrCreate(['slug' => 'portatil-1'], [
            'category_id'    => $equipos->id,
            'name'           => 'Portátil 1',
            'description'    => 'Portátil de alto rendimiento para presentaciones',
            'location'       => 'Almacén TIC',
            'capacity'       => 1,
            'active'         => true,
            'open_time'      => '08:00',
            'close_time'     => '20:00',
            'slot_duration'  => 120,
            'price_per_slot' => 0,
            'available_days' => [1, 2, 3, 4, 5],
        ]);

        $r4 = Resource::firstOrCreate(['slug' => 'pista-padel'], [
            'category_id'    => $instalaciones->id,
            'name'           => 'Pista de Pádel',
            'description'    => 'Pista de pádel exterior con iluminación',
            'location'       => 'Pabellón exterior',
            'capacity'       => 4,
            'active'         => true,
            'open_time'      => '08:00',
            'close_time'     => '22:00',
            'slot_duration'  => 90,
            'price_per_slot' => 5,
            'available_days' => [1, 2, 3, 4, 5, 6, 0],
        ]);

        // Reserva de ejemplo
        Reservation::firstOrCreate(
            ['user_id' => $testUser->id, 'resource_id' => $r1->id, 'start_time' => now()->addDays(1)->setHour(10)->setMinute(0)->setSecond(0)],
            [
                'end_time'    => now()->addDays(1)->setHour(11)->setMinute(0)->setSecond(0),
                'status'      => 'confirmed',
                'notes'       => 'Reunión de equipo semanal',
                'total_price' => 0,
            ]
        );

        // Ajustes del sitio
        $defaults = [
            ['key' => 'site_name',        'value' => 'Sistema de Reservas',   'type' => 'string', 'group' => 'general'],
            ['key' => 'site_description', 'value' => 'Gestión de recursos compartidos', 'type' => 'string', 'group' => 'general'],
            ['key' => 'primary_color',    'value' => '#3B82F6',               'type' => 'color',  'group' => 'appearance'],
            ['key' => 'secondary_color',  'value' => '#10B981',               'type' => 'color',  'group' => 'appearance'],
            ['key' => 'font_family',      'value' => 'Inter',                 'type' => 'string', 'group' => 'appearance'],
            ['key' => 'logo_url',         'value' => '',                      'type' => 'string', 'group' => 'appearance'],
            ['key' => 'allow_registration', 'value' => 'true',               'type' => 'boolean', 'group' => 'general'],
            ['key' => 'require_confirmation', 'value' => 'true',             'type' => 'boolean', 'group' => 'reservations'],
            ['key' => 'max_days_advance',  'value' => '30',                  'type' => 'integer', 'group' => 'reservations'],
        ];

        foreach ($defaults as $setting) {
            SiteSetting::set($setting['key'], $setting['value'], $setting['type'], $setting['group']);
        }
    }
}
