<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class QuestionSimilaritySeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        DB::table('question_similarity')->insert([
            [
                'question_id' =>'f7101ff0-5f5a-4d4d-8c59-6e682ade73b4',
                'related_question_id' => '876c8c86-4a61-4488-8b16-c7b3c6f21f70',
            ],
            [
                'question_id' => '876c8c86-4a61-4488-8b16-c7b3c6f21f70',
                'related_question_id' => 'f7101ff0-5f5a-4d4d-8c59-6e682ade73b4',
            ],
            [
                'question_id' => 'd1ee414e-f3d4-43b0-912e-22a94835a380',
                'related_question_id' => 'a08090d1-cfe6-4493-a7a1-66c66f134779',
            ],
            [
                'question_id' => 'a08090d1-cfe6-4493-a7a1-66c66f134779',
                'related_question_id' => 'd1ee414e-f3d4-43b0-912e-22a94835a380',
            ],
            [
                'question_id' => '5d1de3f0-8a1e-4377-9679-ccbc758fb0fb',
                'related_question_id' => '1f05a2c2-75ae-4285-958f-733f8241a568',
            ],
            [
                'question_id' => '5d1de3f0-8a1e-4377-9679-ccbc758fb0fb',
                'related_question_id' => '66f45c6c-4649-4484-937a-7a621db26272',
            ],
            [
                'question_id' => '5d1de3f0-8a1e-4377-9679-ccbc758fb0fb',
                'related_question_id' => '790be622-77a3-43b2-ae33-33504a631aa2',
            ],
            [
                'question_id' => '5d1de3f0-8a1e-4377-9679-ccbc758fb0fb',
                'related_question_id' => 'f0f29f5d-c13e-4a8c-af33-7a9ce45a2c77',
            ],
            [
                'question_id' => '1f05a2c2-75ae-4285-958f-733f8241a568',
                'related_question_id' => '5d1de3f0-8a1e-4377-9679-ccbc758fb0fb',
            ],
            [
                'question_id' => '1f05a2c2-75ae-4285-958f-733f8241a568',
                'related_question_id' => '66f45c6c-4649-4484-937a-7a621db26272',
            ],
            [
                'question_id' => '1f05a2c2-75ae-4285-958f-733f8241a568',
                'related_question_id' => '790be622-77a3-43b2-ae33-33504a631aa2',
            ],
            [
                'question_id' => '1f05a2c2-75ae-4285-958f-733f8241a568',
                'related_question_id' => 'f0f29f5d-c13e-4a8c-af33-7a9ce45a2c77',
            ],
            [
                'question_id' => '66f45c6c-4649-4484-937a-7a621db26272',
                'related_question_id' => '5d1de3f0-8a1e-4377-9679-ccbc758fb0fb',
            ],
            [
                'question_id' => '66f45c6c-4649-4484-937a-7a621db26272',
                'related_question_id' => '1f05a2c2-75ae-4285-958f-733f8241a568',
            ],
            [
                'question_id' => '66f45c6c-4649-4484-937a-7a621db26272',
                'related_question_id' => '790be622-77a3-43b2-ae33-33504a631aa2',
            ],
            [
                'question_id' => '66f45c6c-4649-4484-937a-7a621db26272',
                'related_question_id' => 'f0f29f5d-c13e-4a8c-af33-7a9ce45a2c77',
            ],
            [
                'question_id' => '790be622-77a3-43b2-ae33-33504a631aa2',
                'related_question_id' => '5d1de3f0-8a1e-4377-9679-ccbc758fb0fb',
            ],
            [
                'question_id' => '790be622-77a3-43b2-ae33-33504a631aa2',
                'related_question_id' => '1f05a2c2-75ae-4285-958f-733f8241a568',
            ],
            [
                'question_id' => '790be622-77a3-43b2-ae33-33504a631aa2',
                'related_question_id' => '66f45c6c-4649-4484-937a-7a621db26272',
            ],
            [
                'question_id' => '790be622-77a3-43b2-ae33-33504a631aa2',
                'related_question_id' => 'f0f29f5d-c13e-4a8c-af33-7a9ce45a2c77',
            ],
            [
                'question_id' => 'f0f29f5d-c13e-4a8c-af33-7a9ce45a2c77',
                'related_question_id' => '5d1de3f0-8a1e-4377-9679-ccbc758fb0fb',
            ],
            [
                'question_id' => 'f0f29f5d-c13e-4a8c-af33-7a9ce45a2c77',
                'related_question_id' => '1f05a2c2-75ae-4285-958f-733f8241a568',
            ],
            [
                'question_id' => 'f0f29f5d-c13e-4a8c-af33-7a9ce45a2c77',
                'related_question_id' => '66f45c6c-4649-4484-937a-7a621db26272',
            ],
            [
                'question_id' => 'f0f29f5d-c13e-4a8c-af33-7a9ce45a2c77',
                'related_question_id' => '790be622-77a3-43b2-ae33-33504a631aa2',
            ],
            [
                'question_id' => '6d6b6f4f-015b-4b2a-b6df-6c1055df25be',
                'related_question_id' => '1287a4df-586b-4e6a-a38e-19ab7e22e350',
            ],
            [
                'question_id' => '1287a4df-586b-4e6a-a38e-19ab7e22e350',
                'related_question_id' => '6d6b6f4f-015b-4b2a-b6df-6c1055df25be',
            ],
            [
                'question_id' => 'c192b54e-d4f0-4843-89ab-4a8a32d40ad3',
                'related_question_id' => '46b8a1a2-992a-4865-890d-503679b47db1',
            ],
            [
                'question_id' => '46b8a1a2-992a-4865-890d-503679b47db1',
                'related_question_id' => 'c192b54e-d4f0-4843-89ab-4a8a32d40ad3',
            ],
            [
                'question_id' => 'f481f118-92ad-4e7b-9a8d-8d0d3e5922c6',
                'related_question_id' => '6d6b6f4f-015b-4b2a-b6df-6c1055df25be',
            ],
            [
                'question_id' => '6d6b6f4f-015b-4b2a-b6df-6c1055df25be',
                'related_question_id' => 'f481f118-92ad-4e7b-9a8d-8d0d3e5922c6',
            ],
            [
                'question_id' => '3bfcf1c2-897e-4bc5-bef0-32da6c5b071f',
                'related_question_id' => '1f05a2c2-75ae-4285-958f-733f8241a568',
            ],
            [
                'question_id' => '1f05a2c2-75ae-4285-958f-733f8241a568',
                'related_question_id' => '3bfcf1c2-897e-4bc5-bef0-32da6c5b071f',
            ],
            [
                'question_id' => 'f7101ff0-5f5a-4d4d-8c59-6e682ade73b4',
                'related_question_id' => 'a9fbb3d8-75ab-43c0-9719-34c16fb9fa8d',
            ],
            [
                'question_id' => 'f7101ff0-5f5a-4d4d-8c59-6e682ade73b4',
                'related_question_id' => 'b8d52445-941c-4db3-9f5c-27664a2e6241',
            ],
            [
                'question_id' => 'f7101ff0-5f5a-4d4d-8c59-6e682ade73b4',
                'related_question_id' => 'c9769363-6e06-47f1-b117-c0d1f5e06a9b',
            ],
            [
                'question_id' => 'f7101ff0-5f5a-4d4d-8c59-6e682ade73b4',
                'related_question_id' => 'e893b7c4-3a18-40ef-96c3-9f5c0c7eb67a',
            ],
            [
                'question_id' => '876c8c86-4a61-4488-8b16-c7b3c6f21f70',
                'related_question_id' => 'a9fbb3d8-75ab-43c0-9719-34c16fb9fa8d',
            ],
            [
                'question_id' => '876c8c86-4a61-4488-8b16-c7b3c6f21f70',
                'related_question_id' => 'b8d52445-941c-4db3-9f5c-27664a2e6241',
            ],
            [
                'question_id' => '876c8c86-4a61-4488-8b16-c7b3c6f21f70',
                'related_question_id' => 'c9769363-6e06-47f1-b117-c0d1f5e06a9b',
            ],
            [
                'question_id' => '876c8c86-4a61-4488-8b16-c7b3c6f21f70',
                'related_question_id' => 'e893b7c4-3a18-40ef-96c3-9f5c0c7eb67a',
            ],
            [
                'question_id' => 'a9fbb3d8-75ab-43c0-9719-34c16fb9fa8d',
                'related_question_id' => 'f7101ff0-5f5a-4d4d-8c59-6e682ade73b4',
            ],
            [
                'question_id' => 'a9fbb3d8-75ab-43c0-9719-34c16fb9fa8d',
                'related_question_id' => '876c8c86-4a61-4488-8b16-c7b3c6f21f70',
            ],
            [
                'question_id' => 'a9fbb3d8-75ab-43c0-9719-34c16fb9fa8d',
                'related_question_id' => 'b8d52445-941c-4db3-9f5c-27664a2e6241',
            ],
            [
                'question_id' => 'a9fbb3d8-75ab-43c0-9719-34c16fb9fa8d',
                'related_question_id' => 'c9769363-6e06-47f1-b117-c0d1f5e06a9b',
            ],
            [
                'question_id' => 'a9fbb3d8-75ab-43c0-9719-34c16fb9fa8d',
                'related_question_id' => 'e893b7c4-3a18-40ef-96c3-9f5c0c7eb67a',
            ],
            [
                'question_id' => 'b8d52445-941c-4db3-9f5c-27664a2e6241',
                'related_question_id' => 'f7101ff0-5f5a-4d4d-8c59-6e682ade73b4',
            ],
            [
                'question_id' => 'b8d52445-941c-4db3-9f5c-27664a2e6241',
                'related_question_id' => '876c8c86-4a61-4488-8b16-c7b3c6f21f70',
            ],
            [
                'question_id' => 'b8d52445-941c-4db3-9f5c-27664a2e6241',
                'related_question_id' => 'a9fbb3d8-75ab-43c0-9719-34c16fb9fa8d',
            ],
            [
                'question_id' => 'b8d52445-941c-4db3-9f5c-27664a2e6241',
                'related_question_id' => 'c9769363-6e06-47f1-b117-c0d1f5e06a9b',
            ],
            [
                'question_id' => 'b8d52445-941c-4db3-9f5c-27664a2e6241',
                'related_question_id' => 'e893b7c4-3a18-40ef-96c3-9f5c0c7eb67a',
            ],
            [
                'question_id' => 'c9769363-6e06-47f1-b117-c0d1f5e06a9b',
                'related_question_id' => 'f7101ff0-5f5a-4d4d-8c59-6e682ade73b4',
            ],
            [
                'question_id' => 'c9769363-6e06-47f1-b117-c0d1f5e06a9b',
                'related_question_id' => '876c8c86-4a61-4488-8b16-c7b3c6f21f70',
            ],
            [
                'question_id' => 'c9769363-6e06-47f1-b117-c0d1f5e06a9b',
                'related_question_id' => 'a9fbb3d8-75ab-43c0-9719-34c16fb9fa8d',
            ],
            [
                'question_id' => 'c9769363-6e06-47f1-b117-c0d1f5e06a9b',
                'related_question_id' => 'b8d52445-941c-4db3-9f5c-27664a2e6241',
            ],
            [
                'question_id' => 'c9769363-6e06-47f1-b117-c0d1f5e06a9b',
                'related_question_id' => 'e893b7c4-3a18-40ef-96c3-9f5c0c7eb67a',
            ],
            [
                'question_id' => 'e893b7c4-3a18-40ef-96c3-9f5c0c7eb67a',
                'related_question_id' => 'f7101ff0-5f5a-4d4d-8c59-6e682ade73b4',
            ],
            [
                'question_id' => 'e893b7c4-3a18-40ef-96c3-9f5c0c7eb67a',
                'related_question_id' => '876c8c86-4a61-4488-8b16-c7b3c6f21f70',
            ],
            [
                'question_id' => 'e893b7c4-3a18-40ef-96c3-9f5c0c7eb67a',
                'related_question_id' => 'a9fbb3d8-75ab-43c0-9719-34c16fb9fa8d',
            ],
            [
                'question_id' => 'e893b7c4-3a18-40ef-96c3-9f5c0c7eb67a',
                'related_question_id' => 'b8d52445-941c-4db3-9f5c-27664a2e6241',
            ],
            [
                'question_id' => 'e893b7c4-3a18-40ef-96c3-9f5c0c7eb67a',
                'related_question_id' => 'c9769363-6e06-47f1-b117-c0d1f5e06a9b',
            ],
            [
                'question_id' => 'c3086411-52d1-46fe-9b79-4d91dce17346',
                'related_question_id' => '5d1de3f0-8a1e-4377-9679-ccbc758fb0fb',
            ],
            [
                'question_id' => 'c3086411-52d1-46fe-9b79-4d91dce17346',
                'related_question_id' => '1f05a2c2-75ae-4285-958f-733f8241a568',
            ],
            [
                'question_id' => 'c3086411-52d1-46fe-9b79-4d91dce17346',
                'related_question_id' => '66f45c6c-4649-4484-937a-7a621db26272',
            ],
            [
                'question_id' => 'c3086411-52d1-46fe-9b79-4d91dce17346',
                'related_question_id' => '790be622-77a3-43b2-ae33-33504a631aa2',
            ],
            [
                'question_id' => 'c3086411-52d1-46fe-9b79-4d91dce17346',
                'related_question_id' => 'f0f29f5d-c13e-4a8c-af33-7a9ce45a2c77',
            ],
            [
                'question_id' => '5d1de3f0-8a1e-4377-9679-ccbc758fb0fb',
                'related_question_id' => 'c3086411-52d1-46fe-9b79-4d91dce17346',
            ],
            [
                'question_id' => '1f05a2c2-75ae-4285-958f-733f8241a568',
                'related_question_id' => 'c3086411-52d1-46fe-9b79-4d91dce17346',
            ],
            [
                'question_id' => '66f45c6c-4649-4484-937a-7a621db26272',
                'related_question_id' => 'c3086411-52d1-46fe-9b79-4d91dce17346',
            ],
            [
                'question_id' => '790be622-77a3-43b2-ae33-33504a631aa2',
                'related_question_id' => 'c3086411-52d1-46fe-9b79-4d91dce17346',
            ],
            [
                'question_id' => 'f0f29f5d-c13e-4a8c-af33-7a9ce45a2c77',
                'related_question_id' => 'c3086411-52d1-46fe-9b79-4d91dce17346',
            ]
        ]);
    }
}
