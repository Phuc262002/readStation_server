<?php

namespace App\Http\Controllers\Api\CheckSchedule;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;

class RemindReturnBookController extends Controller
{
    public function remindReturnBook(Request $request)
    {
        $data = $request->all();
        $data['message'] = 'Remind return book successfully';
        return response()->json($data, 200);
    }
}
