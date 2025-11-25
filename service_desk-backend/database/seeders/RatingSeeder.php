<?php

namespace Database\Seeders;

use App\Models\Rating;
use Illuminate\Database\Seeder;

class RatingSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $ratings = [
            [
                'nama_rating'        => 'Bintang 1',
                'nilai_rating'       => 1,
                'rating_description' => 'Sangat Buruk',
            ],
            [
                'nama_rating'        => 'Bintang 2',
                'nilai_rating'       => 2,
                'rating_description' => 'Buruk',
            ],
            [
                'nama_rating'        => 'Bintang 3',
                'nilai_rating'       => 3,
                'rating_description' => 'Standar',
            ],
            [
                'nama_rating'        => 'Bintang 4',
                'nilai_rating'       => 4,
                'rating_description' => 'Baik',
            ],
            [
                'nama_rating'        => 'Bintang 5',
                'nilai_rating'       => 5,
                'rating_description' => 'Sangat Baik',
            ],
        ];

        foreach ($ratings as $rating) {
            Rating::create($rating);
        }
    }
}
