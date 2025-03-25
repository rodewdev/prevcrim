<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\File;

class RegionComunaSeeder extends Seeder
{
    public function run(): void
    {
        $json = File::get(storage_path("app/comunas-regiones.json"));
        $data = json_decode($json, true);

        if (json_last_error() !== JSON_ERROR_NONE) {
            throw new \RuntimeException("Error decoding JSON: " . json_last_error_msg());
        }

        foreach ($data['regiones'] as $regionData) {
            $regionId = DB::table('regiones')->insertGetId([
                'nombre' => $regionData['region']
            ]);

            foreach ($regionData['comunas'] as $comuna) {
                DB::table('comunas')->insert([
                    'nombre' => $comuna,
                    'region_id' => $regionId
                ]);
            }
        }
    }
}
