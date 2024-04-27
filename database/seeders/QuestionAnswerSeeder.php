<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\QuestionAnswer;

class QuestionAnswerSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        $data = [
            [
                'id' => 'a9fbb3d8-75ab-43c0-9719-34c16fb9fa8d',
                'question' => 'How is my business today?',
                'answer' => '<p>According to our records, commerce is booming. The sales information is shown below.</p>',
                'other_answers' => '<p>It appears that your business has not yet begun. Please verify to see if the solutions are being used by your staff.</p>'
            ],
            [
                'id' => 'b8d52445-941c-4db3-9f5c-27664a2e6241',
                'question' => 'How many sales done today?',
                'answer' => '<p> According to our records, commerce is booming. The sales information is shown below.</p>',
                'other_answers' => '<p>It appears that your business has not yet begun. Please verify to see if the solutions are being used by your staff.</p>'
            ],
            [
                'id' => 'e893b7c4-3a18-40ef-96c3-9f5c0c7eb67a',
                'question' => 'What\'s our business purchases value?',
                'answer' => '<p>Your total purchase value is <strong>$X</strong>.</p>',
                'other_answers' => '<p>According to your transaction, no purchases were made today. Please keep an eye on your inventory and make purchases based on your sales.</p>'
            ],
            [
                'id' => 'c9769363-6e06-47f1-b117-c0d1f5e06a9b',
                'question' => 'What\'s our customers average billing today?',
                'answer' => '<p>Your customer\'s average buy value is <strong>$X</strong> based on the transaction. This is a good result in comparison to yesterday. Continue to increase the value of rock in your business.</p> - <p>Your customer\'s average buy value is <strong>$X</strong> based on the transaction. This is lower than the previous day\'s outcome. Maintain your focus on increasing the value you provide.</p> ',
                'other_answers' => '<p>Your sales have not yet begun based on the transaction. Please double-check your staff to ensure that everything is running well.</p> '
            ],
            [
                'id' => 'f481f118-92ad-4e7b-9a8d-8d0d3e5922c6',
                'question' => 'Show me a list of products that are in short supply.',
                'answer' => '<p>Thank you for the excellent question, which is critical to your business. Please see the following information:</p>',
                'other_answers' => '<p>You are fantastic, and you have complete control over your company\'s goods inventory. Please review the sales report to ensure that your business is running smoothly.</p>'
            ],
            [
                'id' => 'f7101ff0-5f5a-4d4d-8c59-6e682ade73b4',
                'question' => 'Top Selling 10 products?',
                'answer' => '<p>The top 10 selling products, according to historical data derived from your sales, are: </p>',
                'other_answers' => '<p>Oh oh! Given that there is now nothing to display in the top 10 selling products, your historical sales data indicates that you have not yet started any new sales.</p>'
            ],
            [
                'id' => 'd1ee414e-f3d4-43b0-912e-22a94835a380',
                'question' => 'How many customers we have till now?',
                'answer' => '<p>According to the data, you have excellent and have a large base of customers. Please read the following information to find out your customer numbers.</p>',
                'other_answers' => '<p>The data shows that your sales numbers are weak and that you have fewer prospects overall. Review the information below to increase the number of your customers.</p>'
            ],
            [
                'id' => 'a08090d1-cfe6-4493-a7a1-66c66f134779',
                'question' => 'How many new customers walk in today?',
                'answer' => '<p>Huray! The data indicates that today an impressive amount of customers have walked through. The facts are shown below.</p>',
                'other_answers' => '<p>Oh sorry! Since no customers came in today, there are no numbers to display below.</p>'
            ],
            [
                'id' => '5d1de3f0-8a1e-4377-9679-ccbc758fb0fb',
                'question' => 'What\'s our total sales and purchase value today?',
                'answer' => '<p>As per the historical data, you have a great deal of sales and purchase value right now. Your current total for taken together purchases and sales are: <strong>$X</strong>.</p>',
                'other_answers' => '<p>In part to not having any worth evidence to show as a result, neither sales nor purchases were conducted today according to earlier information.</p>'
            ],
            [
                'id' => '1f05a2c2-75ae-4285-958f-733f8241a568',
                'question' => 'What\'s our total expenses today?',
                'answer' => '<p>It indicates that you have spent a higher amount today that was indicated by earlier financial records. Please review your exact spending for today.</p> - <p>It indicates that you have spent little today compared to the earlier information records would you please review your specific spending for today?</p>',
                'other_answers' => '<p>The most recent information records indicate that you didn\'t spend any expenses today, thus there won\'t be any expense records to display at the moment.</p>'
            ],
            [
                'id' => '66f45c6c-4649-4484-937a-7a621db26272',
                'question' => 'What\'s my outstanding need to pay?',
                'answer' => '<p>Based on the information we have on hand, you still owe the following outstanding payments, which should be paid first on a priority basis.</p> - <p>Your unpaid balance is to be paid in the following amount of money, according to the information I have.</p>',
                'other_answers' => '<p>According to the details that I possess, your balance of unpaid debt is nil; you currently owe nothing.'
            ],
            [
                'id' => '790be622-77a3-43b2-ae33-33504a631aa2',
                'question' => 'What\'s my outstanding need to receive?',
                'answer' => '<p>This page has already been updated with all of the money that was paid by your vendors, which has already been received. So, there are currently no details to display in order to receive outstanding.</p>',
                'other_answers' => '<p>Based on the payment inputs I have, the remainder of this value is still due to you. For the outstanding you need to receive, kindly check the below details.</p>'
            ],
            [
                'id' => '6d6b6f4f-015b-4b2a-b6df-6c1055df25be',
                'question' => 'Totally how many active product we have?',
                'answer' => '<p>Total active product currently we have: <strong>$X</strong>.</p>',
                'other_answers' => 'Sorry! The previous data indicated that none of the products are active for display below.</p>'
            ],
            [
                'id' => '876c8c86-4a61-4488-8b16-c7b3c6f21f70',
                'question' => 'Sales comparison yesterday & today?',
                'answer' => '<p>Here are the details of yesterday\'s and today\'s sales comparison.</p>',
                'other_answers' => '<p>Oh no! The sales comparison data for yesterday and today cannot be displayed due to insufficient information. After the data has been updated, kindly try again.</p>'
            ],
            [
                'id' => '1287a4df-586b-4e6a-a38e-19ab7e22e350',
                'question' => 'Current stock worth?',
                'answer' => '<p>Based on the latest inventory data, your current stock worth is <strong>$X</strong>.</p>',
                'other_answers' => '<p>Your current stock is estimated to be worth <strong>$X</strong>, according to the most recent inventory report.</p>'
            ],
            [
                'id' => '3bfcf1c2-897e-4bc5-bef0-32da6c5b071f',
                'question' => 'How is my online sales today?',
                'answer' => '<p>Fantabulous! Compared to previously updated information, your online sales are strong today.</p>',
                'other_answers' => '<p>Today, your online sales have been exceptional, resulting in <strong>$X</strong> in revenue according to the latest statistics.</p>'
            ],
            [
                'id' => 'c192b54e-d4f0-4843-89ab-4a8a32d40ad3',
                'question' => 'How many Suppliers do we have?',
                'answer' => '<p>Records keeping demonstrates that for our suppliers, there are <strong>$X</strong> </p>',
                'other_answers' => '<p>Unfortunately, you need to update the information to know how many suppliers we currently have.</p>'
            ],
            [
                'id' => '46b8a1a2-992a-4865-890d-503679b47db1',
                'question' => 'Top 5 Suppliers?',
                'answer' => '<p>The statistics we have indicate that the top 5 vendors will be as follows.</p>',
                'other_answers' => '<p>The top 5 suppliers to present here, based on the data we have, require the necessary information updated. Enter the information again, please try again </p>'
            ],
            [
                'id' => 'f0f29f5d-c13e-4a8c-af33-7a9ce45a2c77',
                'question' => 'Profit & Loss for this month?',
                'answer' => '<p>Huray! According to the prior information, we have attained <strong>$X</strong> of profit this month.Oops! According to the prior information, this month, we have attained a loss of <strong>$Y</strong> </p>',
                'other_answers' => '<p>Please add the appropriate information in order to calculate and present the statistics of the profit or loss for this month.</p>'
            ],
            [
                'id' => 'c3086411-52d1-46fe-9b79-4d91dce17346',
                'question' => 'Today our transaction split up?',
                'answer' => '<p>According to our records, commerce is booming. The sales information is shown below.</p>',
                'other_answers' => '<p>It appears that your business has not yet begun. Please verify to see if the solutions are being used by your staff.</p>'
            ]
        ];

        foreach ($data as $entry) {
            QuestionAnswer::create($entry);
        }
    }
}

