<?php

namespace App\Http\Controllers\Api;

use Illuminate\Http\Request;
use Examyou\RestAPI\ApiResponse;
use NlpTools\Similarity\CosineSimilarity;
use NlpTools\Tokenizers\WhitespaceTokenizer;
use App\Http\Controllers\Api\AuthController;
use App\Models\Supplier;
use App\Http\Controllers\Api\OnlineOrdersController;
use App\Http\Controllers\Api\ReportController;
use App\Models\QuestionAnswer;
use App\Models\QuestionSimilarity;
use App\Models\UserFaq;
use App\Models\Customer;
use App\Models\Order;
use App\Models\OrderItem;
use Carbon\Carbon;
use App\Http\Controllers\ApiBaseController;
use App\Models\Product;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;


class chatbotController extends ApiBaseController
{
    protected $AuthController;
    protected $OnlineOrdersController;
    protected $ReportController;

    public function __construct(AuthController $AuthController, OnlineOrdersController $OnlineOrdersController, ReportController $ReportController)
    {
        $this->AuthController = $AuthController;
        $this->OnlineOrdersController = $OnlineOrdersController;
        $this->ReportController = $ReportController;
    }

    // auth('api')->user()->email

    public function Question($question)
    {

        $question = QuestionAnswer::where('question', 'like', '%' . $question . '%')
        ->pluck('question')->toArray();
        return response()->json([
            'questions' => $question
        ]);
    }

    public function Answer(Request $request)
    {


        $company = company(true);
        // Get the question from the request
        $question = $request->input('question');

        $currencies =$company["currency"]["symbol"];
        $currencies_position =$company["currency"]["position"] ;


        // Switch case based on the question
        switch ($question) {
            case "Top Selling 10 products?":
                $answer = QuestionAnswer::where('question', $question)->first();
                $data = UserFaq::where('email',)
                    ->where('question_id', $answer->id)
                    ->update(['count' => DB::raw('IFNULL(count, 0) + 1')]);
                if ($data == 0) {
                    UserFaq::create([
                        'email' => auth('api')->user()->email,
                        'question_id' => $answer->id,
                        'count' => 1,
                    ]);
                };
                if ($answer) {
                    $similarQuestions = QuestionSimilarity::where('question_id', $answer->id)
                        ->with('relatedQuestion')
                        ->get()
                        ->pluck('relatedQuestion.question')
                        ->toArray();
                    $getproduct = $this->AuthController->getTopProducts();
                    $labels = $getproduct['labels'];
                    $tableData = [];
                    if (!empty($labels)) {
                        foreach ($labels as $key => $label) {
                            $tableData[] = [
                                'S.no' => $key + 1,
                                'Label' => $label,
                            ];
                        }
                    } else {
                        $answerResponse = $answer->other_answers;
                    }
                    if (!empty($tableData)) {
                        $tableHtml = '<table>';
                        $tableHtml .= '<tr><th>S.no</th><th>Product</th></tr>';
                        foreach ($tableData as $row) {
                            $tableHtml .= '<tr>';
                            $tableHtml .= '<td>' . $row['S.no'] . '</td>';
                            $tableHtml .= '<td>' . $row['Label'] . '</td>';
                            $tableHtml .= '</tr>';
                        }
                        $tableHtml .= '</table>';
                        $answerResponse = $answer->answer . $tableHtml;
                    }
                    return ApiResponse::make([
                        'answer' => $answerResponse,
                        'recommendedQuestions' => $similarQuestions
                    ]);
                }
                break;

            case "What's our total expenses today?":
                $answer = QuestionAnswer::where('question', $question)->first();
                $data = UserFaq::where('email', auth('api')->user()->email)
                    ->where('question_id', $answer->id)
                    ->update(['count' => DB::raw('IFNULL(count, 0) + 1')]);

                if ($data == 0) {
                    UserFaq::create([
                        'email' => auth('api')->user()->email,
                        'question_id' => $answer->id,
                        'count' => 1,
                    ]);
                }


                if ($answer) {
                    $similarQuestions = QuestionSimilarity::where('question_id', $answer->id)
                        ->with('relatedQuestion')
                        ->get()
                        ->pluck('relatedQuestion.question')
                        ->toArray();

                    $todayDate = Carbon::today();

                    $todaydate = $request->merge([
                        'dates' => [Carbon::today()->startOfDay()->toDateString(), Carbon::today()->startOfDay()->toDateString()]
                    ]);
                    $todayExpense = $this->AuthController->getStatsData($todaydate);

                    $yesterdayDate = $todayDate->subDay();
                    $yesterdaydate = $request->merge([
                        'dates' => [$yesterdayDate->toDateString(), $yesterdayDate->toDateString()]
                    ]);
                    $yesterdayExpense = $this->AuthController->getStatsData($yesterdaydate);

                    if ($currencies_position == "front") {
                        $todayTotalExpense = $currencies . " " . $todayExpense['totalExpenses'] + $todayExpense['paymentSent'];
                        $yesterdayTotalExpense = $currencies . " " . $yesterdayExpense['totalExpenses'] + $yesterdayExpense['paymentSent'];
                    } else {
                        $todayTotalExpense = $todayExpense['totalExpenses'] + $todayExpense['paymentSent'] . ' ' . $currencies;
                        $yesterdayTotalExpense = $yesterdayExpense['totalExpenses'] + $yesterdayExpense['paymentSent'] . ' ' . $currencies;
                    }

                    $answerResponse = '';

                    if ($todayTotalExpense == 0) {
                        $answerResponse = $answer->other_answers;
                    }else if ($todayTotalExpense >= $yesterdayTotalExpense) {
                        $answerResponse = explode(' - ', $answer->answer)[0] . '<p>Today Total Expense: <strong>' . $todayTotalExpense . '</strong><p>yesterday Total Expense: <strong>' . $yesterdayTotalExpense . '</strong></p>';
                    } else if ($todayTotalExpense < $yesterdayTotalExpense) {
                        $answerResponse = explode(' - ', $answer->answer)[1] . '<p>Today Total Expense: <strong>' . $todayTotalExpense . '</strong><p>yesterday Total Expense: <strong>' . $yesterdayTotalExpense . '</strong></p>';
                    }

                    return ApiResponse::make([
                        'answer' => $answerResponse,
                        'recommendedQuestions' => $similarQuestions
                    ]);
                }
                break;

            case "How many sales done today?":
                $answer = QuestionAnswer::where('question', $question)->first();

                $data = UserFaq::where('email', auth('api')->user()->email)
                    ->where('question_id', $answer->id)
                    ->update(['count' => DB::raw('IFNULL(count, 0) + 1')]);

                if ($data == 0) {
                    UserFaq::create([
                        'email' => auth('api')->user()->email,
                        'question_id' => $answer->id,
                        'count' => 1,
                    ]);
                }

                if ($answer) {
                    $similarQuestions = QuestionSimilarity::where('question_id', $answer->id)
                        ->with('relatedQuestion')
                        ->get()
                        ->pluck('relatedQuestion.question')
                        ->toArray();

                    // $todaydate = $request->merge([
                    //     'dates' => [Carbon::today()->startOfDay()->toDateString(), Carbon::today()->startOfDay()->toDateString()]
                    // ]);

                    $start_date = Carbon::today()->startOfDay()->toDateString();
                    $end_date = Carbon::today()->endOfDay()->toDateString();

                    $todaySales = Order::where('order_type', 'sales')
                                    ->whereDate('order_date','>=', $start_date)
                                    ->whereDate('order_date', '<=',$end_date)
                                    ->count();

                    // $todaySales = $this->AuthController->getStockHistoryStatsData($todaydate);

                    $answerResponse = '';

                    // if ($todaySales['totalSales'] > 0) {
                    //     $answerResponse = $answer->answer . ' Total sales done today is ' . $todaySales['totalSales'];
                    // } else if ($todaySales['totalSales'] == 0) {
                    //     $answerResponse = $answer->other_answers;
                    // }
                    if ($todaySales > 0) {
                        $answerResponse = $answer->answer . '<p> Total sales done today is <strong>' . $todaySales . '</strong></p>';
                    } else if ($todaySales == 0) {
                        $answerResponse = $answer->other_answers;
                    }

                    return ApiResponse::make([
                        'answer' => $answerResponse,
                        'recommendedQuestions' => $similarQuestions
                    ]);
                }
                break;

            case "Sales comparison yesterday & today?":
                $answer = QuestionAnswer::where('question', $question)->first();

                $data = UserFaq::where('email', auth('api')->user()->email)
                    ->where('question_id', $answer->id)
                    ->update(['count' => DB::raw('IFNULL(count, 0) + 1')]);

                if ($data == 0) {
                    UserFaq::create([
                        'email' => auth('api')->user()->email,
                        'question_id' => $answer->id,
                        'count' => 1,
                    ]);
                }

                if ($answer) {
                    $similarQuestions = QuestionSimilarity::where('question_id', $answer->id)
                        ->with('relatedQuestion')
                        ->get()
                        ->pluck('relatedQuestion.question')
                        ->toArray();

                    $Date = Carbon::today();
                    $yesterdayDate = $Date->subDay();
                    $dates = $yesterdayDate->toDateString();
                    $today = $request->merge([
                        'dates' => [Carbon::today()->toDateString(), Carbon::today()->toDateString()]
                    ]);

                    $todaySales =  $this->AuthController->getStatsData($today);

                    $yesterday = $request->merge([
                        'dates' => [$dates, $dates]
                    ]);

                    $yesterdaySales = $this->AuthController->getStatsData($yesterday);

                    $answerResponse = '';

                    if ($todaySales['totalSales'] == 0) {
                        $answerResponse = $answer->other_answers;
                    }else if ($todaySales['totalSales'] < $yesterdaySales['totalSales'] && $todaySales['totalSales'] > 0) {
                        if ($currencies_position == "front"){
                            $answerResponse = $answer->answer . '<p> today sales: <strong>' . $currencies . " " . $todaySales['totalSales'] . '</strong>,</p>' . '<p> yesterday sales: <strong>' . $currencies . " " . $yesterdaySales['totalSales'] . '</strong> </p><p>not going good </p>';
                        }
                        else {
                            $answerResponse = $answer->answer . '<p> today sales: <strong>' . $todaySales['totalSales'] . ' ' . $currencies. '</strong>,</p>' . '<p> yesterday sales: <strong>' . $yesterdaySales['totalSales'] . ' ' . $currencies . '</strong> </p><p>not going good </p>';
                        }

                    } else if ($todaySales['totalSales'] > $yesterdaySales['totalSales']) {
                        if ($currencies_position == "front"){
                            $answerResponse = $answer->answer . '<p> today sales: <strong>' . $currencies . " " . $todaySales['totalSales'] . '</strong>,</p>' . '<p> yesterday sales: <strong>' . $currencies . " " . $yesterdaySales['totalSales'] . '</strong> </p><p>going good </p>';
                        }
                        else {
                            $answerResponse = $answer->answer . '<p> today sales: <strong>' . $todaySales['totalSales'] . ' ' . $currencies . '</strong>,</p>' . '<p> yesterday sales: <strong>' . $yesterdaySales['totalSales'] . ' ' . $currencies . '</strong> </p><p>going good </p>';
                        }
                    }


                    return ApiResponse::make([
                        'answer' => $answerResponse,
                        'recommendedQuestions' => $similarQuestions
                    ]);
                }
                break;

            case "How is my online sales today?":
                $answer = QuestionAnswer::where('question', $question)->first();


                $data = UserFaq::where('email', auth('api')->user()->email)
                    ->where('question_id', $answer->id)
                    ->update(['count' => DB::raw('IFNULL(count, 0) + 1')]);

                if ($data == 0) {
                    UserFaq::create([
                        'email' => auth('api')->user()->email,
                        'question_id' => $answer->id,
                        'count' => 1,
                    ]);
                }

                if ($answer) {
                    $similarQuestions = QuestionSimilarity::where('question_id', $answer->id)
                        ->with('relatedQuestion')
                        ->get()
                        ->pluck('relatedQuestion.question')
                        ->toArray();

                    $currentDate = Carbon::now()->format('Y-m-d');
                    $warehouse = warehouse()->id;

                    $todaySales = DB::table('orders')->where('order_type', "online-orders")
                        ->where('warehouse_id', $warehouse)
                        ->whereRaw('DATE(order_date) = ?', [$currentDate])
                        ->sum('total');
                    $previousSales = DB::table('orders')->where('order_type', "online-orders")
                        ->where('warehouse_id', $warehouse)
                        ->whereRaw('DATE(order_date) != ?', [$currentDate])
                        ->sum('total');

                    $answerResponse = '';
                    if ($todaySales > $previousSales) {
                        $answerResponse = $answer->answer;
                    } else if ($todaySales < $previousSales || $todaySales == 0) {
                        $answerResponse = $answer->other_answers;
                    }

                    return ApiResponse::make([
                        'answer' => $answerResponse,
                        'recommendedQuestions' => $similarQuestions
                    ]);
                }
                break;

            case "How many Suppliers do we have?":
                $answer = QuestionAnswer::where('question', $question)->first();

                // $warehouse = warehouse();
                //to get suplliers belong to warehouse
                // $warehouseId = 1;
                // $totalSuppliers = Supplier::whereHas('warehouse', function ($query) use ($warehouseId) {
                //     $query->where('id', $warehouseId);
                // })->count();



                $data = UserFaq::where('email', auth('api')->user()->email)
                    ->where('question_id', $answer->id)
                    ->update(['count' => DB::raw('IFNULL(count, 0) + 1')]);

                if ($data == 0) {
                    UserFaq::create([
                        'email' => auth('api')->user()->email,
                        'question_id' => $answer->id,
                        'count' => 1,
                    ]);
                }

                if ($answer) {
                    $similarQuestions = QuestionSimilarity::where('question_id', $answer->id)
                        ->with('relatedQuestion')
                        ->get()
                        ->pluck('relatedQuestion.question')
                        ->toArray();

                    $totalSuppliers = Supplier::count();
                    $answerResponse = '';
                    if ($totalSuppliers > 0) {
                        $modifiedAnswer = $answer->answer;
                        $replace = str_replace('$X', $totalSuppliers, $modifiedAnswer);
                        $answerResponse = $replace;
                    } else if ($totalSuppliers == 0) {
                        $answerResponse = $answer->other_answers;
                    }

                    return ApiResponse::make([
                        'answer' => $answerResponse,
                        'recommendedQuestions' => $similarQuestions
                    ]);
                }
                break;

            case "Top 5 Suppliers?":
                $answer = QuestionAnswer::where('question', $question)->first();
                $data = UserFaq::where('email', auth('api')->user()->email)
                    ->where('question_id', $answer->id)
                    ->update(['count' => DB::raw('IFNULL(count, 0) + 1')]);
                if ($data == 0) {
                    UserFaq::create([
                        'email' => auth('api')->user()->email,
                        'question_id' => $answer->id,
                        'count' => 1,
                    ]);
                }
                if ($answer) {
                    $similarQuestions = QuestionSimilarity::where('question_id', $answer->id)
                        ->with('relatedQuestion')
                        ->get()
                        ->pluck('relatedQuestion.question')
                        ->toArray();
                    $topSuppliers = Supplier::with('details.user')
                        ->leftJoin('user_details', 'users.id', '=', 'user_details.user_id')
                        ->orderBy('user_details.paid_amount', 'desc')
                        ->take(5)
                        ->pluck('name');

                    $answerResponse = '';
                    if ($topSuppliers->isNotEmpty()) {
                        $answerResponse = $answer->answer;
                        $supplierTable = '<table>';
                        $supplierTable .= '<thead><tr><th>S.no</th><th>Supplier Name</th></tr></thead>';
                        $supplierTable .= '<tbody>';
                        $counter = 1;
                        foreach ($topSuppliers as $supplier) {
                            $supplierTable .= '<tr><td>' . $counter . '</td><td>' . $supplier . '</td></tr>';
                            $counter++;
                        }
                        $supplierTable .= '</tbody>';
                        $supplierTable .= '</table>';
                        $answerResponse = $answer->answer . $supplierTable;
                    } else {
                        $answerResponse = $answer->other_answers;
                    }
                    return ApiResponse::make([
                        'answer' => $answerResponse,
                        'recommendedQuestions' => $similarQuestions
                    ]);
                }
                break;

            case "Profit & Loss for this month?":
                $answer = QuestionAnswer::where('question', $question)->first();
                // $warehouse = warehouse();
                $data = UserFaq::where('email', auth('api')->user()->email)
                    ->where('question_id', $answer->id)
                    ->update(['count' => DB::raw('IFNULL(count, 0) + 1')]);
                if ($data == 0) {
                    UserFaq::create([
                        'email' => auth('api')->user()->email,
                        'question_id' => $answer->id,
                        'count' => 1,
                    ]);
                }
                if ($answer) {
                    $similarQuestions = QuestionSimilarity::where('question_id', $answer->id)
                        ->with('relatedQuestion')
                        ->get()
                        ->pluck('relatedQuestion.question')
                        ->toArray();
                    $month = $request->merge([
                        'dates' => [Carbon::now()->startOfMonth()->toDateString(), Carbon::today()->toDateString()]
                    ]);
                    $monthlyprofit = $this->ReportController->profitLoss($month);
                    $data = collect();
                    $data->push($monthlyprofit);
                    $profit = $data[0]->original['data']['profit'];
                    $answerResponse = '';

                    if ($currencies_position == "front"){
                        $replacement = $currencies . " " . implode(',', (array)$profit);
                    }
                    else {
                        $replacement = implode(',', (array)$profit) . ' ' . $currencies;
                    }

                    if ($profit > 0) {
                        $answerResponse = str_replace(['$X', '$Y'], [$replacement, '0'], $answer->answer);
                    } else if ($data[0]->original['data']['profit'] < 0) {
                        $answerResponse = str_replace(['$Y', '$X'], [$replacement, '0'], $answer->answer);
                    } else {
                        $answerResponse = $answer->other_answers;
                    }
                    return ApiResponse::make([
                        'answer' => $answerResponse,
                        'recommendedQuestions' => $similarQuestions
                    ]);
                }
                break;

            case "How many customers we have till now?":
                $answer = QuestionAnswer::where('question', $question)->first();
                // $warehouse = warehouse();
                $data = UserFaq::where('email', auth('api')->user()->email)
                    ->where('question_id', $answer->id)
                    ->update(['count' => DB::raw('IFNULL(count, 0) + 1')]);
                if ($data == 0) {
                    UserFaq::create([
                        'email' => auth('api')->user()->email,
                        'question_id' => $answer->id,
                        'count' => 1,
                    ]);
                }
                if ($answer) {
                    $similarQuestions = QuestionSimilarity::where('question_id', $answer->id)
                        ->with('relatedQuestion')
                        ->get()
                        ->pluck('relatedQuestion.question')
                        ->toArray();

                    $customerCount = Customer::count();

                    $sale = Customer::with('customerss')
                        ->get()
                        ->pluck('customerss.total')
                        ->sum();
                    if ($currencies_position == "front"){
                        $sales = $currencies . " " . $sale;
                    }
                    else {
                        $sales = $sale . ' ' . $currencies;
                    }


                    $currentDate = date('Y-m-d');
                    $answerResponse = '';
                    if ($customerCount > 0) {
                        $answerResponse = $answer->answer . "<p> As of <strong>" . $currentDate . "</strong>, the total number of customers is <strong>" . $customerCount . "</strong>." . " total sales value is <strong>" . $sales . '.</strong></p>';
                    } else if ($customerCount >= 0) {
                        $answerResponse = $answer->other_answers . "<p> As of <strong>" . $currentDate . "</strong>, the total number of customers is <strong>" . $customerCount . "</strong>." . " total sales value is <strong>" . $sales . '.</strong></p>';
                    }
                    return ApiResponse::make([
                        'answer' => $answerResponse,
                        'recommendedQuestions' => $similarQuestions
                    ]);
                }

                break;

            case "How many new customers walk in today?":
                $answer = QuestionAnswer::where('question', $question)->first();
                // $warehouse = warehouse();
                $data = UserFaq::where('email', auth('api')->user()->email)
                    ->where('question_id', $answer->id)
                    ->update(['count' => DB::raw('IFNULL(count, 0) + 1')]);
                if ($data == 0) {
                    UserFaq::create([
                        'email' => auth('api')->user()->email,
                        'question_id' => $answer->id,
                        'count' => 1,
                    ]);
                }
                if ($answer) {
                    $similarQuestions = QuestionSimilarity::where('question_id', $answer->id)
                        ->with('relatedQuestion')
                        ->get()
                        ->pluck('relatedQuestion.question')
                        ->toArray();

                    $today = Carbon::today()->format('Y-m-d');
                    $walkincustomer = DB::table('users')->where('is_walkin_customer', 1)
                        ->whereDate('created_at', '=', $today)->count();

                    $answerResponse = '';
                    if ($walkincustomer > 0) {
                        $answerResponse = $answer->answer . " You have " . $walkincustomer . " walk in customer(s) today.";;
                    } else if ($walkincustomer <= 0) {
                        $answerResponse = $answer->other_answers;
                    }
                    return ApiResponse::make([
                        'answer' => $answerResponse,
                        'recommendedQuestions' => $similarQuestions
                    ]);
                }
                break;


            case "Show me a list of products that are in short supply.":
                $answer = QuestionAnswer::where('question', $question)->first();
                $data = UserFaq::where('email', auth('api')->user()->email)
                    ->where('question_id', $answer->id)
                    ->update(['count' => DB::raw('IFNULL(count, 0) + 1')]);
                if ($data == 0) {
                    UserFaq::create([
                        'email' => auth('api')->user()->email,
                        'question_id' => $answer->id,
                        'count' => 1,
                    ]);
                }
                if ($answer) {
                    $similarQuestions = QuestionSimilarity::where('question_id', $answer->id)
                        ->with('relatedQuestion')
                        ->get()
                        ->pluck('relatedQuestion.question')
                        ->toArray();
                    $stockQuantity = $this->AuthController->getStockAlerts();
                    $stockQuantity = $stockQuantity->toArray();
                    // Sort the array by Current Stock in descending order
                    $sortColumn = 'current_stock';
                    usort($stockQuantity, function ($a, $b) use ($sortColumn) {
                        return $b[$sortColumn] <=> $a[$sortColumn];
                    });
                    if (!empty($stockQuantity)) {
                        $htmlTable = '<table><thead><tr><th>S.No</th><th>Product Name</th><th>Current Stock</th></tr></thead><tbody>';
                        $serialNumber = 1;
                        foreach ($stockQuantity as $product) {
                            $productName = $product['product_name'];
                            $currentStock = $product['current_stock'];
                            $htmlTable .= '<tr>';
                            $htmlTable .= '<td>' . $serialNumber . '</td>';
                            $htmlTable .= '<td>' . $productName . '</td>';
                            $htmlTable .= '<td>' . $currentStock . '</td>';
                            $htmlTable .= '</tr>';
                            $serialNumber++;
                        }
                        $htmlTable .= '</tbody></table>';
                        $answerResponse = $answer->answer . $htmlTable;
                    } else {
                        $answerResponse = $answer->other_answers;
                    }
                    return ApiResponse::make([
                        'answer' => $answerResponse,
                        'recommendedQuestions' => $similarQuestions
                    ]);
                }
                break;

            case "Totally how many active product we have?":
                $answer = QuestionAnswer::where('question', $question)->first();
                // $warehouse = warehouse();
                $data = UserFaq::where('email', auth('api')->user()->email)
                    ->where('question_id', $answer->id)
                    ->update(['count' => DB::raw('IFNULL(count, 0) + 1')]);
                if ($data == 0) {
                    UserFaq::create([
                        'email' => auth('api')->user()->email,
                        'question_id' => $answer->id,
                        'count' => 1,
                    ]);
                }
                if ($answer) {
                    $similarQuestions = QuestionSimilarity::where('question_id', $answer->id)
                        ->with('relatedQuestion')
                        ->get()
                        ->pluck('relatedQuestion.question')
                        ->toArray();


                    $answerResponse = '';
                    $data = Product::with('details')->get();
                    $activeProducts = Product::with('details')->get();

                    $activeproductcount = 0;

                    foreach ($data as $product) {
                        if ($product->details->status === 'in_stock') {
                            $activeproductcount++;
                        }
                    }

                    $answerResponse = '';

                    if ($activeproductcount) {
                        $answerResponse =  str_replace('$X', $activeproductcount , $answer->answer);
                    } else if ($activeproductcount <= 0) {
                        $answerResponse = $answer->other_answers;
                    }
                    return ApiResponse::make([
                        'answer' => $answerResponse,
                        'recommendedQuestions' => $similarQuestions
                    ]);
                }




            case "What's my outstanding need to pay?":

                $answer = QuestionAnswer::where('question', $question)->first();

                // $warehouse = warehouse();
                $data = UserFaq::where('email', auth('api')->user()->email)
                    ->where('question_id', $answer->id)
                    ->update(['count' => DB::raw('IFNULL(count, 0) + 1')]);
                if ($data == 0) {
                    UserFaq::create([
                        'email' => auth('api')->user()->email,
                        'question_id' => $answer->id,
                        'count' => 1,
                    ]);
                }

                if ($answer) {
                    $similarQuestions = QuestionSimilarity::where('question_id', $answer->id)
                        ->with('relatedQuestion')
                        ->get()
                        ->pluck('relatedQuestion.question')
                        ->toArray();

                    $orders = Order::where('invoice_number', 'like', '%PUR%')->where('due_amount','>',0)->orderBy('due_amount', 'desc')->get(['invoice_number', 'due_amount']);
                    $orderstotal = Order::sum('due_amount');
                    $answerResponse = '';

                    if (count($orders) > 0 && $orderstotal > 0) {
                        $answers = '<table>';
                        $answers .= '<tr><th>S.No</th><th>Invoice Number</th><th>Due Amount</th></tr>';

                        $sNo = 1;
                        foreach ($orders as $order) {
                            $answers .= '<tr>';
                            $answers .= '<td>' . $sNo . '</td>';
                            $answers .= '<td>' . $order->invoice_number . '</td>';
                            $answers .= '<td>' . $order->due_amount . '</td>';
                            $answers .= '</tr>';

                            $sNo++;
                        }

                        $answers .= '</table>';
                        $splitanswers = explode('-', $answer->answer);
                        // $answerResponse = $splitanswers[0] .$answers;
                        // $currency =
                        $answerResponse = $splitanswers[1] . ' ' . $answers;
                    } else {
                        $answerResponse = $answer->other_answers;
                    }

                    return ApiResponse::make([
                        'answer' => $answerResponse,
                        'recommendedQuestions' => $similarQuestions
                    ]);
                }
                break;

            case "What's my outstanding need to receive?":
                $answer = QuestionAnswer::where('question', $question)->first();
                // $warehouse = warehouse();
                $data = UserFaq::where('email', auth('api')->user()->email)
                    ->where('question_id', $answer->id)
                    ->update(['count' => DB::raw('IFNULL(count, 0) + 1')]);
                if ($data == 0) {
                    UserFaq::create([
                        'email' => auth('api')->user()->email,
                        'question_id' => $answer->id,
                        'count' => 1,
                    ]);
                }
                if ($answer) {
                    $similarQuestions = QuestionSimilarity::where('question_id', $answer->id)
                        ->with('relatedQuestion')
                        ->get()
                        ->pluck('relatedQuestion.question')
                        ->toArray();


                    $answerResponse = '';
                    $orders = Order::where(function ($query) {
                        $query->where('payment_status', 'partially_paid')
                            ->orWhere('payment_status', 'unpaid');
                    })
                        ->where('invoice_number', 'not like', '%PUR%')
                        ->orderBy('due_amount', 'desc')
                        ->get(['invoice_number', 'due_amount']);

                    $table = '';

                    if (!$orders->isEmpty()) {

                        $table = '<table>';
                        $table .= '<thead><tr><th>S.NO</th><th>Invoice Number</th><th>Due Amount</th></tr></thead>';
                        $table .= '<tbody>';
                        foreach ($orders as $index => $order) {
                            $table .= '<tr>';
                            $table .= '<td>' . ($index + 1) . '</td>';
                            $table .= '<td>' . $order->invoice_number . '</td>';
                            $table .= '<td>' . $order->due_amount . '</td>';
                            $table .= '</tr>';
                        }
                        $table .= '</tbody>';
                        $table .= '</table>';
                  
                        $answerResponse = $answer->other_answers . $table;
                    } else {
                       
                        $answerResponse = $answer->answer;
                    }

                    return ApiResponse::make([
                        'answer' => $answerResponse,
                        'recommendedQuestions' => $similarQuestions
                    ]);
                }
                break;


            case "Current stock worth?":

                $answer = QuestionAnswer::where('question', $question)->first();
                // $warehouse = warehouse();
                $data = UserFaq::where('email', auth('api')->user()->email)
                    ->where('question_id', $answer->id)
                    ->update(['count' => DB::raw('IFNULL(count, 0) + 1')]);
                if ($data == 0) {
                    UserFaq::create([
                        'email' => auth('api')->user()->email,
                        'question_id' => $answer->id,
                        'count' => 1,
                    ]);
                }
                if ($answer) {
                    $similarQuestions = QuestionSimilarity::where('question_id', $answer->id)
                        ->with('relatedQuestion')
                        ->get()
                        ->pluck('relatedQuestion.question')
                        ->toArray();

                    $product = Product::with('details')->get();
                    $name = array();
                    foreach($product as $product){
                        $stock_quantity = $product['details']['current_stock'];
                        $sales_price = $product['details']['sales_price'];
                        array_push($name, $stock_quantity * $sales_price);
                    }

                    $sum_amount = array_sum($name);

                    // $sum_amount = $product->sum(function ($product) {
                    //     return $product->details->purchase_price;
                    // });

                    if ($currencies_position == "front"){
                        $sum = $currencies . " " . $sum_amount;
                    }
                    else {
                        $sum = $sum_amount . ' ' . $currencies;
                    }

                    $answerResponse = '';

                    if ($sum > 0) {
                        $positiveAnswer = str_replace('$X', $sum, $answer->answer);
                        // $positiveAnswer = $answer->answer . ' your current stock worth is ' . $sum ;
                        $answerResponse = $positiveAnswer;
                    } else if ($sum <= 0) {
                        $answerResponse = $answer->other_answers;
                    }
                    return ApiResponse::make([
                        'answer' => $answerResponse,
                        'recommendedQuestions' => $similarQuestions
                    ]);
                }
                break;

            case "How is my business today?":

                $answer = QuestionAnswer::where('question', $question)->first();
                // $warehouse = warehouse();
                $data = UserFaq::where('email', auth('api')->user()->email)
                    ->where('question_id', $answer->id)
                    ->update(['count' => DB::raw('IFNULL(count, 0) + 1')]);
                if ($data == 0) {
                    UserFaq::create([
                        'email' => auth('api')->user()->email,
                        'question_id' => $answer->id,
                        'count' => 1,
                    ]);
                }
                if ($answer) {
                    $similarQuestions = QuestionSimilarity::where('question_id', $answer->id)
                        ->with('relatedQuestion')
                        ->get()
                        ->pluck('relatedQuestion.question')
                        ->toArray();

                    $answerResponse = '';
                    $startDate = Carbon::today()->startOfDay();
                    $endDate = Carbon::today()->endOfDay();
                    $orderDetails = Order::with('items')->where('order_type', 'sales')->whereDate('order_date', '>=', $startDate)->whereDate('order_date', '<=', $endDate)->get();
                    $orderCount = Order::where('order_type', 'sales')->whereDate('order_date', '>=', $startDate)->whereDate('order_date', '<=', $endDate)->count();

                    $TotalOrderCosts = Order::where('order_type', 'sales')->whereDate('order_date', '>=', $startDate)->whereDate('order_date', '<=', $endDate)->sum('total');

                    if ($currencies_position == "front"){
                        $TotalOrderCost = $currencies . " " .$TotalOrderCosts;
                    }
                    else {
                        $TotalOrderCost = $TotalOrderCosts . " " . $currencies ;
                    }

                    $answerResponse = '';
                    if ($orderCount > 0) {
                        $answerResponse = $answer->answer . '<p> Your total order cost is <strong>' . $TotalOrderCost . '</strong> your total number of order today is <strong>' . $orderCount . '</strong></p>';
                    } else if ($orderCount <= 0) {
                        $answerResponse = $answer->other_answers;
                    }
                    return ApiResponse::make([
                        'answer' => $answerResponse,
                        'recommendedQuestions' => $similarQuestions
                    ]);
                }
                break;

            case "What's our business purchases value?":

                $answer = QuestionAnswer::where('question', $question)->first();
                // $warehouse = warehouse();
                $data = UserFaq::where('email', auth('api')->user()->email)
                    ->where('question_id', $answer->id)
                    ->update(['count' => DB::raw('IFNULL(count, 0) + 1')]);
                if ($data == 0) {
                    UserFaq::create([
                        'email' => auth('api')->user()->email,
                        'question_id' => $answer->id,
                        'count' => 1,
                    ]);
                }

                if ($answer) {
                    $similarQuestions = QuestionSimilarity::where('question_id', $answer->id)
                        ->with('relatedQuestion')
                        ->get()
                        ->pluck('relatedQuestion.question')
                        ->toArray();

                    // $startDate = Carbon::today()->startOfDay();
                    // $endDate = Carbon::today()->endOfDay();
                    $todayPurchaseValues = Order::where('order_type', 'purchases')
                        // ->whereDate('order_date', '>=', $startDate)
                        // ->whereDate('order_date', '<=', $endDate)
                        ->sum('total');


                        if ($currencies_position == "front"){
                            $todayPurchaseValue = $currencies . " " . $todayPurchaseValues;
                        }
                        else {
                            $todayPurchaseValue = $todayPurchaseValues. ' ' . $currencies;
                        }
                    $answerResponse = '';

                    if ($todayPurchaseValues > 0) {
                        $modifiedAnswer = str_replace('$X', $todayPurchaseValue, $answer->answer);

                        $answerResponse = $modifiedAnswer;
                    } else {

                        $answerResponse = $answer->other_answers;
                    }

                    return ApiResponse::make([
                        'answer' => $answerResponse,
                        'recommendedQuestions' => $similarQuestions
                    ]);
                }
                break;


            case "What's our customers average billing today?":

                $answer = QuestionAnswer::where('question', $question)->first();
                // $warehouse = warehouse();
                $data = UserFaq::where('email', auth('api')->user()->email)
                    ->where('question_id', $answer->id)
                    ->update(['count' => DB::raw('IFNULL(count, 0) + 1')]);
                if ($data == 0) {
                    UserFaq::create([
                        'email' => auth('api')->user()->email,
                        'question_id' => $answer->id,
                        'count' => 1,
                    ]);
                }
                if ($answer) {
                    $similarQuestions = QuestionSimilarity::where('question_id', $answer->id)
                        ->with('relatedQuestion')
                        ->get()
                        ->pluck('relatedQuestion.question')
                        ->toArray();
                    $yesterdayStartDate = Carbon::yesterday()->startOfDay();
                    $yesterdayEndDate = Carbon::yesterday()->endOfDay();
                    $todayStartDate = Carbon::today()->startOfDay();
                    $todayEndDate = Carbon::today()->endOfDay();

                    $YesterdayPurchaseValue = Order::where('order_type', 'sales')
                        ->whereDate('order_date', '>=', $yesterdayStartDate)
                        ->whereDate('order_date', '<=', $yesterdayEndDate)
                        ->get(['total']);

                    $totalSumYesterday = $YesterdayPurchaseValue->sum('total');
                    $totalCountYesterday = $YesterdayPurchaseValue->count();
                    $averageTotalYesterday = ($totalCountYesterday > 0) ? $totalSumYesterday / $totalCountYesterday : 0;

                    $todayPurchaseValue = Order::where('order_type', 'sales')
                        ->whereDate('order_date', '>=', $todayStartDate)
                        ->whereDate('order_date', '<=', $todayEndDate)
                        ->get(['total']);

                    $totalSumToday = $todayPurchaseValue->sum('total');
                    $totalCountToday = $todayPurchaseValue->count();
                    $averageTotalTodays = ($totalCountToday > 0) ? $totalSumToday / $totalCountToday : 0;
                    if ($currencies_position == "front"){
                        $averageTotalToday = $currencies . " " .$averageTotalTodays;
                    }
                    else {
                        $averageTotalToday = $averageTotalTodays. ' ' . $currencies;
                    }
                    $answerResponse = '';
                    $splitAnswer = explode('-', $answer->answer);

                    if ($averageTotalYesterday >= $averageTotalToday && !$averageTotalToday == 0) {
                        $modifiedAnswerToday = str_replace('$X', $averageTotalToday, $splitAnswer[0]);
                        $answerResponse = $modifiedAnswerToday;
                    } else if ($averageTotalYesterday < $averageTotalToday && !$averageTotalToday == 0) {
                        $modifiedAnswerToday = str_replace('$X', $averageTotalToday, $splitAnswer[1]);
                        $answerResponse = $modifiedAnswerToday;
                    } else if ($averageTotalToday == 0) {
                        $answerResponse = $answer->other_answers;
                    }


                    return ApiResponse::make([
                        'answer' => $answerResponse,
                        'recommendedQuestions' => $similarQuestions
                    ]);
                }
                break;

            case "What's our total sales and purchase value today?":

                $answer = QuestionAnswer::where('question', $question)->first();

                $data = UserFaq::where('email', auth('api')->user()->email)
                    ->where('question_id', $answer->id)
                    ->update(['count' => DB::raw('IFNULL(count, 0) + 1')]);
                if ($data == 0) {
                    UserFaq::create([
                        'email' => auth('api')->user()->email,
                        'question_id' => $answer->id,
                        'count' => 1,
                    ]);
                }
                if ($answer) {
                    $similarQuestions = QuestionSimilarity::where('question_id', $answer->id)
                        ->with('relatedQuestion')
                        ->get()
                        ->pluck('relatedQuestion.question')
                        ->toArray();

                    $startDate = Carbon::today()->startOfDay();
                    $endDate = Carbon::today()->endOfDay();
                    $todaySales = Order::where('order_type', 'sales')
                        ->whereDate('order_date', '>=', $startDate)
                        ->whereDate('order_date', '<=', $endDate)
                        ->sum('total');
                    $todayPurchase =  Order::where('order_type', 'purchases')
                        ->whereDate('order_date', '>=', $startDate)
                        ->whereDate('order_date', '<=', $endDate)
                        ->sum('total');

                    if ($currencies_position == "front"){
                        $totalSalesAndPurchaseValue = $currencies . " " .$todaySales + $todayPurchase;
                    }
                    else {
                        $totalSalesAndPurchaseValue = $todaySales + $todayPurchase. ' ' . $currencies;
                    }
                    $answerResponse = '';
                    if ($totalSalesAndPurchaseValue > 0) {
                        $modifiedAnswer = str_replace('$X', $totalSalesAndPurchaseValue, $answer->answer);
                        $answerResponse = $modifiedAnswer;
                    } else if ($totalSalesAndPurchaseValue <= 0) {
                        $answerResponse = $answer->other_answers;
                    }


                    return ApiResponse::make([
                        'answer' => $answerResponse,
                        'recommendedQuestions' => $similarQuestions
                    ]);
                }
                break;

            case "Today our transaction split up?":

                $answer = QuestionAnswer::where('question', $question)->first();
                // $warehouse = warehouse();
                $data = UserFaq::where('email', auth('api')->user()->email)
                    ->where('question_id', $answer->id)
                    ->update(['count' => DB::raw('IFNULL(count, 0) + 1')]);
                if ($data == 0) {
                    UserFaq::create([
                        'email' => auth('api')->user()->email,
                        'question_id' => $answer->id,
                        'count' => 1,
                    ]);
                }
                if ($answer) {
                    $similarQuestions = QuestionSimilarity::where('question_id', $answer->id)
                        ->with('relatedQuestion')
                        ->get()
                        ->pluck('relatedQuestion.question')
                        ->toArray();

                    $answerResponse = '';

                    return ApiResponse::make([
                        'answer' => $answerResponse,
                        'recommendedQuestions' => $similarQuestions
                    ]);
                }
                break;

                case "Today our transaction split up?":

                    $answer = QuestionAnswer::where('question', $question)->first();
                    // $warehouse = warehouse();
                    $data = UserFaq::where('email', auth('api')->user()->email)
                        ->where('question_id', $answer->id)
                        ->update(['count' => DB::raw('IFNULL(count, 0) + 1')]);
                    if ($data == 0) {
                        UserFaq::create([
                            'email' => auth('api')->user()->email,
                            'question_id' => $answer->id,
                            'count' => 1,
                        ]);
                    }
                    if ($answer) {
                        $similarQuestions = QuestionSimilarity::where('question_id', $answer->id)
                            ->with('relatedQuestion')
                            ->get()
                            ->pluck('relatedQuestion.question')
                            ->toArray();

                        $answerResponse = '';

                        return ApiResponse::make([
                            'answer' => $answerResponse,
                            'recommendedQuestions' => $similarQuestions
                        ]);
                    }
                    break;
            default:
                return ApiResponse::make([
                    'answer' => 'No answer found for the given question.'
                ], 404);
                break;
        }
    }
}

// $loggedUser = user();
// $warehouse = warehouse();
// $company = company();


        //*****  text_string cossine similarity check *****

        // // Example sentences
        // $sentenceA = "The sun is shining brightly";
        // $sentenceB = "The sun is radiating intense light";

        // // Get the sentences from the request
        // $sentence1 =  $sentenceA;
        // $sentence2 =  $sentenceB;

        // // Tokenize the sentences using the WhitespaceTokenizer
        // $tokenizer = new WhitespaceTokenizer();
        // $tokens1 = $tokenizer->tokenize($sentence1);
        // $tokens2 = $tokenizer->tokenize($sentence2);

        // // Create a new instance of the CosineSimilarity class
        // $cosineSimilarity = new CosineSimilarity();

        // // Calculate the cosine similarity
        // $similarity = $cosineSimilarity->similarity($tokens1, $tokens2);
        // $highThreshold = 0.7;
        // $moderateThreshold = 0.3;

        // // Determine the similarity level based on the threshold values
        // $similarityLevel = '';

        // if ($similarity >= $highThreshold) {
        //     $similarityLevel = 'high';
        // } elseif ($similarity >= $moderateThreshold) {
        //     $similarityLevel = 'moderate';
        // } else {
        //     $similarityLevel = 'low';
        // }

        // // Return the similarity level and score
        // return response()->json([
        //     'similarityLevel' => $similarityLevel,
        //     'similarityScore' => $similarity,
        // ]);
